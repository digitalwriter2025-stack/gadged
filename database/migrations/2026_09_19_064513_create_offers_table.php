<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Promotional offer campaigns (Offers / Mobile Deal pages) with the products they cover.
     */
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->string('title', 150);
            $table->string('slug', 191)->unique();
            $table->string('summary', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('availability', 100)->default('Online');
            $table->boolean('is_mobile_deal')->default(false);
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'starts_at', 'ends_at']);
        });

        Schema::create('offer_product', function (Blueprint $table) {
            $table->foreignId('offer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            $table->primary(['offer_id', 'product_id']);
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_product');
        Schema::dropIfExists('offers');
    }
};
