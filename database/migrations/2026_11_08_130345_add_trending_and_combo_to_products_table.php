<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Trending and combo flags on products, plus the products a combo bundles.
     *
     * A combo is sold as its own product (own price, stock and variant); combo_items
     * only records what is inside the box for display.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_trending')->default(false)->after('is_best_seller');
            $table->boolean('is_combo')->default(false)->after('is_trending');

            $table->index(['is_trending', 'status']);
            $table->index(['is_combo', 'status']);
        });

        Schema::create('combo_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('combo_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['combo_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('combo_items');

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['is_trending', 'status']);
            $table->dropIndex(['is_combo', 'status']);
            $table->dropColumn(['is_trending', 'is_combo']);
        });
    }
};
