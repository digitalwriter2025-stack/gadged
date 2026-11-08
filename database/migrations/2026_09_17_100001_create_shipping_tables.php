<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Shipping zones (Inside / Outside Dhaka, ...), delivery methods, the charge
     * for each zone + method pair, and the 64 districts mapped to a zone.
     */
    public function up(): void
    {
        Schema::create('shipping_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 50)->unique();
            $table->string('description', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 50)->unique();
            $table->string('description', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_zone_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shipping_method_id')->constrained()->cascadeOnDelete();
            $table->decimal('charge', 12, 2)->default(0);
            $table->decimal('free_shipping_threshold', 12, 2)->nullable();
            $table->unsignedTinyInteger('min_delivery_days')->nullable();
            $table->unsignedTinyInteger('max_delivery_days')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['shipping_zone_id', 'shipping_method_id']);
        });

        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('name_bn', 100)->nullable();
            $table->string('division', 50);
            $table->foreignId('shipping_zone_id')->constrained()->restrictOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('districts');
        Schema::dropIfExists('shipping_rates');
        Schema::dropIfExists('shipping_methods');
        Schema::dropIfExists('shipping_zones');
    }
};
