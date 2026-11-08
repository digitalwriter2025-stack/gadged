<?php

namespace Tests\Feature\Inventory;

use App\Enums\InventoryTransactionType;
use App\Enums\StockStatus;
use App\Exceptions\InsufficientStockException;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use LogicException;
use Tests\TestCase;

class InventoryServiceTest extends TestCase
{
    use RefreshDatabase;

    private InventoryService $inventory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->inventory = app(InventoryService::class);
    }

    public function test_stock_in_increases_stock_and_writes_ledger_with_before_and_after(): void
    {
        $product = Product::factory()->priced('500', stock: 3)->create();
        $variant = $product->defaultVariant;
        $staff = User::factory()->create();

        $transaction = $this->inventory->adjust($variant, InventoryTransactionType::StockIn, 7, 'Supplier delivery', performedBy: $staff);

        $this->assertSame(10, $variant->fresh()->stock_quantity);
        $this->assertSame(10, $variant->stock_quantity);
        $this->assertSame(7, $transaction->quantity);
        $this->assertSame(3, $transaction->quantity_before);
        $this->assertSame(10, $transaction->quantity_after);
        $this->assertSame($product->id, $transaction->product_id);
        $this->assertSame('Supplier delivery', $transaction->reason);
        $this->assertSame($staff->id, $transaction->performed_by);
        $this->assertSame(10, $product->fresh()->total_stock);
    }

    public function test_directional_types_apply_their_own_sign(): void
    {
        $variant = Product::factory()->priced('500', stock: 10)->create()->defaultVariant;

        $this->inventory->adjust($variant, InventoryTransactionType::Damage, 2, 'Broken screen');
        $this->inventory->adjust($variant, InventoryTransactionType::StockOut, 3);
        $this->inventory->adjust($variant, InventoryTransactionType::Return, 1);
        $this->inventory->adjust($variant, InventoryTransactionType::Adjustment, -1, 'Stock count');

        $this->assertSame(5, $variant->fresh()->stock_quantity);
        $this->assertSame(
            [-2, -3, 1, -1],
            InventoryTransaction::query()->where('product_variant_id', $variant->id)->orderBy('id')->pluck('quantity')->all(),
        );
    }

    public function test_removing_more_than_available_is_refused_without_side_effects(): void
    {
        $variant = Product::factory()->priced('500', stock: 2)->create()->defaultVariant;

        try {
            $this->inventory->adjust($variant, InventoryTransactionType::StockOut, 3);
            $this->fail('Expected InsufficientStockException.');
        } catch (InsufficientStockException $exception) {
            $this->assertSame(2, $exception->available);
            $this->assertSame(3, $exception->requested);
        }

        $this->assertSame(2, $variant->fresh()->stock_quantity);
        $this->assertSame(0, InventoryTransaction::query()->count());
    }

    public function test_invalid_quantities_are_rejected(): void
    {
        $variant = Product::factory()->create()->defaultVariant;

        $this->expectException(InvalidArgumentException::class);

        $this->inventory->adjust($variant, InventoryTransactionType::StockIn, -5);
    }

    public function test_zero_adjustment_is_rejected(): void
    {
        $variant = Product::factory()->create()->defaultVariant;

        $this->expectException(InvalidArgumentException::class);

        $this->inventory->adjust($variant, InventoryTransactionType::Adjustment, 0);
    }

    public function test_set_stock_records_the_difference_and_ignores_no_ops(): void
    {
        $variant = Product::factory()->priced('500', stock: 10)->create()->defaultVariant;

        $transaction = $this->inventory->setStock($variant, 4, 'Physical count');

        $this->assertSame(-6, $transaction->quantity);
        $this->assertSame(InventoryTransactionType::Adjustment, $transaction->type);
        $this->assertSame(4, $variant->fresh()->stock_quantity);
        $this->assertNull($this->inventory->setStock($variant, 4, 'Recount'));
    }

    public function test_order_deduction_is_all_or_nothing(): void
    {
        $plenty = Product::factory()->priced('100', stock: 10)->create()->defaultVariant;
        $scarce = Product::factory()->priced('100', stock: 1)->create()->defaultVariant;
        $order = Order::factory()->create();

        try {
            $this->inventory->deductForOrder([$plenty->id => 2, $scarce->id => 2], $order);
            $this->fail('Expected InsufficientStockException.');
        } catch (InsufficientStockException $exception) {
            $this->assertSame($scarce->id, $exception->variantId);
        }

        $this->assertSame(10, $plenty->fresh()->stock_quantity);
        $this->assertSame(1, $scarce->fresh()->stock_quantity);
        $this->assertSame(0, InventoryTransaction::query()->count());
    }

    public function test_order_deduction_and_cancellation_restore_reference_the_order(): void
    {
        $product = Product::factory()->priced('100', stock: 3)->create();
        $variant = $product->defaultVariant;
        $order = Order::factory()->create();

        $this->inventory->deductForOrder([$variant->id => 3], $order);

        $product->refresh();
        $this->assertSame(0, $product->total_stock);
        $this->assertSame(StockStatus::OutOfStock, $product->stock_status);

        $this->inventory->restoreForOrder([$variant->id => 3], $order);

        $product->refresh();
        $this->assertSame(3, $variant->fresh()->stock_quantity);
        $this->assertSame(StockStatus::InStock, $product->stock_status);

        $ledger = InventoryTransaction::query()->where('reference_id', $order->id)->orderBy('id')->get();
        $this->assertSame(['order', 'order'], $ledger->pluck('reference_type')->all());
        $this->assertSame([InventoryTransactionType::Order, InventoryTransactionType::OrderCancellation], $ledger->pluck('type')->all());
    }

    public function test_restore_refuses_stock_removing_types(): void
    {
        $variant = Product::factory()->create()->defaultVariant;

        $this->expectException(InvalidArgumentException::class);

        $this->inventory->restoreForOrder([$variant->id => 1], Order::factory()->create(), InventoryTransactionType::Damage);
    }

    public function test_products_without_stock_management_are_not_deducted(): void
    {
        $variant = Product::factory()->unmanagedStock()->priced('100', stock: 0)->create()->defaultVariant;

        $transactions = $this->inventory->deductForOrder([$variant->id => 5], Order::factory()->create());

        $this->assertSame([], $transactions);
        $this->assertSame(0, $variant->fresh()->stock_quantity);
    }

    public function test_unknown_variants_are_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->inventory->deductForOrder([999999 => 1], Order::factory()->create());
    }

    public function test_stock_quantity_cannot_be_mass_assigned(): void
    {
        $variant = Product::factory()->priced('100', stock: 5)->create()->defaultVariant;

        try {
            $variant->fill(['stock_quantity' => 999]);
            $this->fail('Expected MassAssignmentException.');
        } catch (MassAssignmentException) {
            // Strict mode rejects the attribute outright instead of silently discarding it.
        }

        $this->assertSame(5, $variant->fresh()->stock_quantity);
    }

    public function test_database_refuses_negative_stock_even_outside_the_service(): void
    {
        $variant = Product::factory()->priced('100', stock: 1)->create()->defaultVariant;

        $this->expectException(QueryException::class);

        DB::table('product_variants')->where('id', $variant->id)->update(['stock_quantity' => DB::raw('stock_quantity - 5')]);
    }

    public function test_ledger_rows_are_append_only(): void
    {
        $variant = Product::factory()->create()->defaultVariant;
        $transaction = $this->inventory->adjust($variant, InventoryTransactionType::StockIn, 1);

        $this->expectException(LogicException::class);

        $transaction->update(['reason' => 'tampered']);
    }

    public function test_variant_of_deleted_product_keeps_its_history_loadable(): void
    {
        $product = Product::factory()->create();
        $transaction = $this->inventory->adjust($product->defaultVariant, InventoryTransactionType::StockIn, 1);

        $product->delete();

        $this->assertInstanceOf(Product::class, $transaction->fresh()->product);
        $this->assertInstanceOf(ProductVariant::class, $transaction->fresh()->variant);
    }
}
