<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\Actions\SendToSteadfastAction;
use App\Filament\Resources\Orders\Actions\SendToSteadfastBulkAction;
use App\Filament\Resources\Orders\Actions\UpdateOrderStatusAction;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Models\Order;
use App\Models\User;
use App\Services\SettingsService;
use App\Services\SteadfastCourier;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class SendOrderToSteadfastTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->superAdmin()->create());
        $this->enableSteadfast();
    }

    public function test_order_is_booked_with_steadfast_and_the_tracking_code_is_saved(): void
    {
        Http::fake(['portal.packzy.com/api/v1/create_order' => Http::response($this->createdConsignment('15BAEB8A'))]);
        $order = $this->orderWithItem([
            'status' => OrderStatus::Confirmed,
            'order_number' => 'GD261108-0001',
            'customer_name' => 'Lionel Ortega',
            'customer_phone' => '01712345678',
            'customer_email' => 'lionel@example.com',
            'shipping_address' => 'House 12, Road 3',
            'shipping_area' => 'Mirpur 10',
            'shipping_district' => 'Dhaka',
            'customer_note' => 'Call before delivery',
            'grand_total' => '1059.00',
        ]);

        Livewire::test(ViewOrder::class, ['record' => $order->order_number])
            ->callAction(SendToSteadfastAction::getDefaultName())
            ->assertNotified('Order sent to Steadfast');

        Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
            && $request->url() === 'https://portal.packzy.com/api/v1/create_order'
            && $request->hasHeader('Api-Key', 'api-key-123')
            && $request->hasHeader('Secret-Key', 'secret-key-456')
            && $request['invoice'] === 'GD261108-0001'
            && $request['recipient_name'] === 'Lionel Ortega'
            && $request['recipient_phone'] === '01712345678'
            && $request['recipient_email'] === 'lionel@example.com'
            && $request['recipient_address'] === 'House 12, Road 3, Mirpur 10, Dhaka'
            && (float) $request['cod_amount'] === 1059.0
            && $request['note'] === 'Call before delivery'
            && $request['item_description'] === 'Redragon H130 Headphone - Black x2');

        $order->refresh();
        $this->assertSame('Steadfast', $order->courier_name);
        $this->assertSame('15BAEB8A', $order->tracking_number);
        $this->assertSame(OrderStatus::Confirmed, $order->status);
    }

    public function test_only_the_unpaid_amount_is_collected_on_delivery(): void
    {
        $order = Order::factory()->create(['grand_total' => '1060.00', 'customer_phone' => '+880 1712-345678']);
        $order->forceFill(['paid_total' => '1060.00'])->save();

        $payload = app(SteadfastCourier::class)->consignmentPayload($order);

        $this->assertSame(0.0, $payload['cod_amount']);
        $this->assertSame('01712345678', $payload['recipient_phone']);
    }

    public function test_order_can_be_sent_from_the_list(): void
    {
        Http::fake(['portal.packzy.com/*' => Http::response($this->createdConsignment('ABC123'))]);
        $order = Order::factory()->status(OrderStatus::Confirmed)->create();

        Livewire::test(ListOrders::class)
            ->callAction(TestAction::make(SendToSteadfastAction::getDefaultName())->table($order))
            ->assertNotified('Order sent to Steadfast');

        $this->assertSame('ABC123', $order->refresh()->tracking_number);
    }

    public function test_steadfast_refusal_is_shown_and_the_order_is_left_unchanged(): void
    {
        Http::fake(['portal.packzy.com/*' => Http::response([
            'status' => 400,
            'errors' => ['invoice' => ['The invoice has already been taken.']],
        ], 422)]);
        $order = Order::factory()->status(OrderStatus::Confirmed)->create();

        Livewire::test(ViewOrder::class, ['record' => $order->order_number])
            ->callAction(SendToSteadfastAction::getDefaultName())
            ->assertNotified('Order not sent to Steadfast');

        $order->refresh();
        $this->assertNull($order->courier_name);
        $this->assertNull($order->tracking_number);
    }

    public function test_answer_without_a_tracking_code_counts_as_a_failure(): void
    {
        Http::fake(['portal.packzy.com/*' => Http::response(['status' => 400, 'message' => 'Recipient phone is invalid.'])]);
        $order = Order::factory()->create();

        $this->expectExceptionMessage('Recipient phone is invalid.');

        app(SteadfastCourier::class)->createConsignment($order);
    }

    public function test_sending_while_steadfast_is_switched_off_points_to_the_settings_and_leaves_the_order_alone(): void
    {
        app(SettingsService::class)->set('steadfast.enabled', false);
        app(SettingsService::class)->flush();
        Http::fake();
        $order = Order::factory()->create();

        Livewire::test(ListOrders::class)
            ->assertActionVisible(TestAction::make(SendToSteadfastAction::getDefaultName())->table($order))
            ->callAction(TestAction::make(SendToSteadfastAction::getDefaultName())->table($order))
            ->assertNotified('Steadfast is not set up');

        Http::assertNothingSent();
        $order->refresh();
        $this->assertSame(OrderStatus::Pending, $order->status);
        $this->assertNull($order->tracking_number);
    }

    public function test_confirming_an_order_books_it_with_steadfast(): void
    {
        Http::fake(['portal.packzy.com/api/v1/create_order' => Http::response($this->createdConsignment('SFR-CONFIRMED'))]);
        $order = Order::factory()->create();

        Livewire::test(ViewOrder::class, ['record' => $order->order_number])
            ->callAction(UpdateOrderStatusAction::getDefaultName(), ['status' => OrderStatus::Confirmed->value])
            ->assertHasNoFormErrors()
            ->assertNotified('Order confirmed and sent to Steadfast');

        $order->refresh();
        $this->assertSame(OrderStatus::Confirmed, $order->status);
        $this->assertSame('Steadfast', $order->courier_name);
        $this->assertSame('SFR-CONFIRMED', $order->tracking_number);
    }

    public function test_order_stays_confirmed_when_steadfast_refuses_it(): void
    {
        Http::fake(['portal.packzy.com/*' => Http::response(['status' => 400, 'message' => 'The recipient phone is invalid.'])]);
        $order = Order::factory()->create();

        Livewire::test(ViewOrder::class, ['record' => $order->order_number])
            ->callAction(UpdateOrderStatusAction::getDefaultName(), ['status' => OrderStatus::Confirmed->value])
            ->assertNotified('Order confirmed, but not sent to Steadfast');

        $order->refresh();
        $this->assertSame(OrderStatus::Confirmed, $order->status);
        $this->assertNull($order->tracking_number);
    }

    public function test_confirming_does_not_send_when_auto_send_is_off(): void
    {
        app(SettingsService::class)->set('steadfast.auto_send', false);
        app(SettingsService::class)->flush();
        Http::fake();
        $order = Order::factory()->create();

        Livewire::test(ViewOrder::class, ['record' => $order->order_number])
            ->callAction(UpdateOrderStatusAction::getDefaultName(), ['status' => OrderStatus::Confirmed->value])
            ->assertNotified('Order marked as Confirmed');

        Http::assertNothingSent();
        $this->assertSame(OrderStatus::Confirmed, $order->refresh()->status);
    }

    public function test_other_status_changes_do_not_send(): void
    {
        Http::fake();
        $order = Order::factory()->status(OrderStatus::Confirmed)->create();

        Livewire::test(ViewOrder::class, ['record' => $order->order_number])
            ->callAction(UpdateOrderStatusAction::getDefaultName(), ['status' => OrderStatus::Processing->value])
            ->assertNotified('Order marked as Processing');

        Http::assertNothingSent();
    }

    public function test_sending_a_pending_order_confirms_it_first(): void
    {
        Http::fake(['portal.packzy.com/*' => Http::response($this->createdConsignment('SF-PENDING'))]);
        $order = Order::factory()->create();

        Livewire::test(ListOrders::class)
            ->assertActionVisible(TestAction::make(SendToSteadfastAction::getDefaultName())->table($order))
            ->callAction(TestAction::make(SendToSteadfastAction::getDefaultName())->table($order))
            ->assertNotified('Order confirmed and sent to Steadfast');

        $order->refresh();
        $this->assertSame(OrderStatus::Confirmed, $order->status);
        $this->assertNotNull($order->confirmed_at);
        $this->assertSame('SF-PENDING', $order->tracking_number);
    }

    public function test_action_is_hidden_once_the_order_has_a_courier_or_has_shipped(): void
    {
        $tracked = Order::factory()->create(['courier_name' => 'Steadfast', 'tracking_number' => 'SF-1']);
        $shipped = Order::factory()->status(OrderStatus::Shipped)->create();

        Livewire::test(ViewOrder::class, ['record' => $tracked->order_number])
            ->assertActionHidden(SendToSteadfastAction::getDefaultName());

        Livewire::test(ViewOrder::class, ['record' => $shipped->order_number])
            ->assertActionHidden(SendToSteadfastAction::getDefaultName());
    }

    public function test_selected_orders_are_sent_together_and_refusals_are_reported(): void
    {
        Http::fake(fn (Request $request) => $request['invoice'] === 'GD-REFUSED'
            ? Http::response(['status' => 400, 'message' => 'The recipient phone is invalid.'])
            : Http::response($this->createdConsignment('SF-'.$request['invoice'])));
        $first = Order::factory()->create(['order_number' => 'GD-FIRST']);
        $second = Order::factory()->status(OrderStatus::Packed)->create(['order_number' => 'GD-SECOND']);
        $refused = Order::factory()->status(OrderStatus::Confirmed)->create(['order_number' => 'GD-REFUSED']);

        Livewire::test(ListOrders::class)
            ->selectTableRecords([$first, $second, $refused])
            ->callAction(TestAction::make(SendToSteadfastBulkAction::getDefaultName())->table()->bulk())
            ->assertNotified('2 orders sent to Steadfast, 1 not sent');

        $this->assertSame('SF-GD-FIRST', $first->refresh()->tracking_number);
        $this->assertSame(OrderStatus::Confirmed, $first->status);
        $this->assertSame('SF-GD-SECOND', $second->refresh()->tracking_number);
        $this->assertSame(OrderStatus::Packed, $second->status);
        $this->assertNull($refused->refresh()->tracking_number);
    }

    public function test_bulk_send_skips_orders_already_with_a_courier_or_shipped(): void
    {
        Http::fake(['portal.packzy.com/*' => Http::response($this->createdConsignment('SF-NEW'))]);
        $tracked = Order::factory()->status(OrderStatus::Confirmed)->create(['courier_name' => 'Steadfast', 'tracking_number' => 'SF-OLD']);
        $shipped = Order::factory()->status(OrderStatus::Shipped)->create();
        $fresh = Order::factory()->status(OrderStatus::Confirmed)->create();

        Livewire::test(ListOrders::class)
            ->selectTableRecords([$tracked, $shipped, $fresh])
            ->callAction(TestAction::make(SendToSteadfastBulkAction::getDefaultName())->table()->bulk())
            ->assertNotified('1 order sent to Steadfast');

        Http::assertSentCount(1);
        $this->assertSame('SF-OLD', $tracked->refresh()->tracking_number);
        $this->assertSame('SF-NEW', $fresh->refresh()->tracking_number);
    }

    public function test_bulk_send_is_hidden_when_steadfast_is_switched_off(): void
    {
        app(SettingsService::class)->set('steadfast.enabled', false);
        app(SettingsService::class)->flush();

        Livewire::test(ListOrders::class)
            ->assertActionHidden(TestAction::make(SendToSteadfastBulkAction::getDefaultName())->table()->bulk());
    }

    public function test_list_shows_courier_status_and_filters_by_it(): void
    {
        $sent = Order::factory()->create(['courier_name' => 'Steadfast', 'tracking_number' => 'SFR261108STC20E16ABD']);
        $notSent = Order::factory()->create();

        Livewire::test(ListOrders::class)
            ->assertSee('SFR261108STC20E16ABD')
            ->assertSee('Send to courier')
            ->filterTable('sent_to_courier', false)
            ->assertCanSeeTableRecords([$notSent])
            ->assertCanNotSeeTableRecords([$sent])
            ->filterTable('sent_to_courier', true)
            ->assertCanSeeTableRecords([$sent])
            ->assertCanNotSeeTableRecords([$notSent]);
    }

    private function enableSteadfast(): void
    {
        app(SettingsService::class)->setMany('steadfast', [
            'enabled' => true,
            'api_key' => SteadfastCourier::encryptKey('api-key-123'),
            'secret_key' => SteadfastCourier::encryptKey('secret-key-456'),
        ]);
        app(SettingsService::class)->flush();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function orderWithItem(array $attributes): Order
    {
        $order = Order::factory()->create($attributes);

        $order->items()->create([
            'product_name' => 'Redragon H130 Headphone',
            'variant_name' => 'Black',
            'sku' => 'GD-01002',
            'regular_price' => '499.50',
            'unit_price' => '499.50',
            'quantity' => 2,
            'line_total' => '999.00',
        ]);

        return $order;
    }

    /**
     * @return array<string, mixed>
     */
    private function createdConsignment(string $trackingCode): array
    {
        return [
            'status' => 200,
            'message' => 'Consignment has been created successfully.',
            'consignment' => [
                'consignment_id' => 1424107,
                'invoice' => 'GD261108-0001',
                'tracking_code' => $trackingCode,
                'status' => 'in_review',
            ],
        ];
    }
}
