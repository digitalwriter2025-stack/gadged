<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Products, their variants (every product has at least one), category / tag
     * pivots and specification values.
     *
     * Prices and stock live on product_variants. The price / stock / rating columns on
     * products are denormalized read models kept in sync by ProductAggregateService so
     * listing pages can filter and sort without joins.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20)->default('simple');
            $table->string('name', 255);
            $table->string('name_bn', 255)->nullable();
            $table->string('slug', 191)->unique();
            $table->string('sku', 64)->unique();
            $table->string('barcode', 64)->nullable()->unique();
            $table->string('model', 120)->nullable();
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            // Primary category (breadcrumbs, canonical URL). Also present in category_product.
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->text('short_description')->nullable();
            $table->text('short_description_bn')->nullable();
            $table->longText('description')->nullable();
            $table->longText('description_bn')->nullable();
            $table->string('warranty', 100)->nullable();
            $table->string('condition', 20)->default('new');
            $table->decimal('weight', 8, 3)->nullable()->comment('kg');
            $table->decimal('length', 8, 2)->nullable()->comment('cm');
            $table->decimal('width', 8, 2)->nullable()->comment('cm');
            $table->decimal('height', 8, 2)->nullable()->comment('cm');
            $table->boolean('manage_stock')->default(true);
            $table->string('stock_status', 20)->default('in_stock');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_latest')->default(false);
            $table->boolean('is_best_seller')->default(false);
            $table->boolean('flash_sale_eligible')->default(true);
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();

            // Denormalized read model (see ProductAggregateService).
            $table->decimal('min_price', 12, 2)->default(0);
            $table->decimal('max_price', 12, 2)->default(0);
            $table->decimal('regular_price', 12, 2)->default(0);
            $table->unsignedTinyInteger('discount_percent')->default(0);
            $table->unsignedInteger('total_stock')->default(0);
            $table->unsignedInteger('sales_count')->default(0);
            $table->unsignedInteger('view_count')->default(0);
            $table->decimal('rating_avg', 3, 2)->default(0);
            $table->unsignedInteger('rating_count')->default(0);

            $table->string('search_keywords', 500)->nullable();
            $table->string('seo_title', 255)->nullable();
            $table->string('seo_description', 500)->nullable();
            $table->string('canonical_url', 500)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'published_at']);
            $table->index(['category_id', 'status', 'min_price']);
            $table->index(['brand_id', 'status']);
            $table->index(['status', 'is_featured']);
            $table->index(['status', 'sales_count']);
            $table->fullText(['name', 'sku', 'model', 'search_keywords']);
        });

        Schema::create('category_product', function (Blueprint $table) {
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            $table->primary(['category_id', 'product_id']);
            $table->index('product_id');
        });

        Schema::create('product_tag', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();

            $table->primary(['product_id', 'tag_id']);
            $table->index('tag_id');
        });

        Schema::create('category_specification', function (Blueprint $table) {
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('specification_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_filterable')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->primary(['category_id', 'specification_id']);
            $table->index('specification_id');
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('sku', 64)->unique();
            $table->string('barcode', 64)->nullable()->unique();
            // Human label such as "8GB / 256GB / Black"; empty for a simple product's default variant.
            $table->string('name', 255)->nullable();
            // Sorted attribute_value ids ("4-9-17"), empty string for the default variant.
            $table->string('combination_key', 191)->default('');
            $table->decimal('price', 12, 2);
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->timestamp('sale_starts_at')->nullable();
            $table->timestamp('sale_ends_at')->nullable();
            $table->decimal('cost_price', 12, 2)->nullable();
            // Unsigned: the database itself refuses negative stock.
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->unsignedInteger('low_stock_threshold')->nullable();
            $table->decimal('weight', 8, 3)->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['product_id', 'combination_key']);
            $table->index(['product_id', 'is_active']);
            $table->index('stock_quantity');
        });

        Schema::create('attribute_value_product_variant', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            // An attribute value in use by a variant cannot be deleted.
            $table->foreignId('attribute_value_id')->constrained()->restrictOnDelete();

            $table->primary(['product_variant_id', 'attribute_value_id']);
            $table->index('attribute_value_id');
        });

        Schema::create('product_specification_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('specification_id')->constrained()->cascadeOnDelete();
            $table->foreignId('specification_option_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('value', 500)->nullable();
            // Parsed numeric value for range filters (e.g. 15.6 for "15.6 inch").
            $table->decimal('value_numeric', 14, 4)->nullable();

            $table->unique(['product_id', 'specification_id', 'specification_option_id'], 'product_spec_value_unique');
            $table->index(['specification_option_id', 'product_id'], 'product_spec_option_filter_index');
            $table->index(['specification_id', 'value_numeric'], 'product_spec_numeric_filter_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_specification_values');
        Schema::dropIfExists('attribute_value_product_variant');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('category_specification');
        Schema::dropIfExists('product_tag');
        Schema::dropIfExists('category_product');
        Schema::dropIfExists('products');
    }
};
