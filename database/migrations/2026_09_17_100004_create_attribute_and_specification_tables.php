<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Variation attributes (RAM, Storage, Color) and the specification engine
     * (groups → specifications → options).
     */
    public function up(): void
    {
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 120)->unique();
            $table->string('display_type', 20)->default('button');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained()->cascadeOnDelete();
            $table->string('value', 100);
            $table->string('slug', 120);
            $table->string('color_hex', 7)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['attribute_id', 'slug']);
        });

        Schema::create('specification_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('name_bn', 100)->nullable();
            $table->string('slug', 120)->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('specifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('specification_group_id')->constrained()->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('name_bn', 150)->nullable();
            $table->string('slug', 170)->unique();
            $table->string('unit', 20)->nullable();
            $table->string('input_type', 20)->default('text');
            $table->boolean('is_filterable')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['specification_group_id', 'sort_order']);
        });

        Schema::create('specification_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('specification_id')->constrained()->cascadeOnDelete();
            $table->string('value', 150);
            $table->string('slug', 170);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['specification_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('specification_options');
        Schema::dropIfExists('specifications');
        Schema::dropIfExists('specification_groups');
        Schema::dropIfExists('attribute_values');
        Schema::dropIfExists('attributes');
    }
};
