<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Orders with full customer / shipping / pricing snapshots, their line items,
     * status history, notes, payments, refunds and coupon usages.
     *
     * Orders are never deleted — they are cancelled. Foreign keys to catalog rows
     * are nulled on delete because the snapshot columns carry the history.
     */
    public function up(): void
    {
        Schema::create('order_sequences', function (Blueprint $table) {
            $table->date('date')->primary();
            $table->unsignedInteger('last_number')->default(0);
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 32)->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 20)->default('pending');
            $table->string('payment_status', 20)->default('unpaid');
            $table->string('payment_method', 30);
            $table->string('source', 20)->default('web');

            // Customer + delivery snapshot.
            $table->string('customer_name', 150);
            $table->string('customer_phone', 20);
            $table->string('customer_email', 191)->nullable();
            $table->string('shipping_address', 500);
            $table->string('shipping_area', 150)->nullable();
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();
            $table->string('shipping_district', 100)->nullable();
            $table->foreignId('shipping_zone_id')->nullable()->constrained()->nullOnDelete();
            $table->string('shipping_zone_name', 100)->nullable();
            $table->foreignId('shipping_method_id')->nullable()->constrained()->nullOnDelete();
            $table->string('shipping_method_name', 100)->nullable();
            $table->text('customer_note')->nullable();

            // Totals, all recalculated server-side at placement.
            $table->decimal('subtotal', 12, 2);
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->string('coupon_code', 50)->nullable();
            $table->decimal('coupon_discount', 12, 2)->default(0);
            $table->decimal('manual_discount', 12, 2)->default(0);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('shipping_total', 12, 2)->default(0);
            $table->decimal('tax_total', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2);
            $table->decimal('paid_total', 12, 2)->default(0);
            $table->decimal('refunded_total', 12, 2)->default(0);
            $table->char('currency', 3)->default('BDT');

            // Fulfilment.
            $table->string('courier_name', 100)->nullable();
            $table->string('tracking_number', 100)->nullable();
            $table->string('cancellation_reason', 500)->nullable();
            $table->timestamp('payment_due_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            // Audit.
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->index(['payment_status', 'created_at']);
            $table->index('customer_phone');
            $table->index('created_at');
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('flash_sale_product_id')->nullable()->constrained()->nullOnDelete();

            // Snapshot at the moment of purchase.
            $table->string('product_name', 255);
            $table->string('sku', 64);
            $table->string('variant_name', 255)->nullable();
            $table->json('variant_options')->nullable();
            $table->string('image_path', 500)->nullable();
            $table->string('warranty', 100)->nullable();
            $table->decimal('regular_price', 12, 2);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('unit_discount', 12, 2)->default(0);
            $table->unsignedInteger('quantity');
            $table->decimal('line_total', 12, 2);
            $table->unsignedInteger('returned_quantity')->default(0);
            $table->timestamps();
        });

        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            // "status" (fulfilment) or "payment".
            $table->string('type', 20)->default('status');
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->text('note')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['order_id', 'created_at']);
        });

        Schema::create('order_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('body');
            $table->boolean('is_customer_visible')->default(false);
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->restrictOnDelete();
            $table->string('method', 30);
            $table->string('gateway', 50)->nullable();
            $table->decimal('amount', 12, 2);
            $table->char('currency', 3)->default('BDT');
            $table->string('status', 20)->default('pending');
            $table->string('transaction_id', 100)->nullable();
            $table->string('sender_number', 20)->nullable();
            // Sanitised gateway payload — never secrets.
            $table->json('meta')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Blocks reuse of the same bKash/Nagad TrxID across orders.
            $table->unique(['method', 'transaction_id']);
            $table->index(['status', 'created_at']);
        });

        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->restrictOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('method', 30)->nullable();
            $table->string('reference', 100)->nullable();
            $table->string('reason', 500);
            $table->boolean('restock')->default(false);
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->restrictOnDelete();
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_phone', 20);
            $table->decimal('discount_amount', 12, 2);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['coupon_id', 'user_id']);
            $table->index(['coupon_id', 'customer_phone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_usages');
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_notes');
        Schema::dropIfExists('order_status_histories');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('order_sequences');
    }
};
