<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Coupons (with product / category scope) and flash sales.
     * Coupon usages are created after the orders table.
     */
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('description', 255)->nullable();
            $table->string('type', 20);
            $table->decimal('value', 12, 2);
            $table->decimal('min_subtotal', 12, 2)->nullable();
            $table->decimal('max_discount', 12, 2)->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('usage_limit_per_customer')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->string('scope', 20)->default('all');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('coupon_product', function (Blueprint $table) {
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            $table->primary(['coupon_id', 'product_id']);
            $table->index('product_id');
        });

        Schema::create('category_coupon', function (Blueprint $table) {
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();

            $table->primary(['category_id', 'coupon_id']);
            $table->index('coupon_id');
        });

        Schema::create('flash_sales', function (Blueprint $table) {
            $table->id();
            $table->string('title', 150);
            $table->string('slug', 191)->unique();
            $table->string('description', 500)->nullable();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'starts_at', 'ends_at']);
        });

        Schema::create('flash_sale_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flash_sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            // Null means the deal applies to every variant of the product.
            $table->foreignId('product_variant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('discount_type', 20);
            $table->decimal('discount_value', 12, 2);
            $table->unsignedInteger('quantity_limit')->nullable();
            $table->unsignedInteger('sold_quantity')->default(0);
            $table->unsignedSmallInteger('per_order_limit')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['flash_sale_id', 'product_id', 'product_variant_id'], 'flash_sale_product_unique');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flash_sale_products');
        Schema::dropIfExists('flash_sales');
        Schema::dropIfExists('category_coupon');
        Schema::dropIfExists('coupon_product');
        Schema::dropIfExists('coupons');
    }
};
