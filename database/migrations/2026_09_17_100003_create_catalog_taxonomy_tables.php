<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Categories (adjacency list + materialized path), brands and tags.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            // Deleting a category that still has children is blocked; children must be moved first.
            $table->foreignId('parent_id')->nullable()->constrained('categories')->restrictOnDelete();
            $table->string('name', 150);
            $table->string('name_bn', 150)->nullable();
            $table->string('slug', 191)->unique();
            // Ancestor chain including self, e.g. "/3/12/" — lets a whole subtree load with one LIKE query.
            $table->string('path', 255)->default('/');
            $table->unsignedTinyInteger('depth')->default(0);
            $table->text('description')->nullable();
            $table->string('icon', 100)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('show_in_menu')->default(true);
            $table->string('seo_title', 255)->nullable();
            $table->string('seo_description', 500)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['parent_id', 'sort_order']);
            $table->index(['is_active', 'is_featured', 'sort_order']);
            $table->index('path');
        });

        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 191)->unique();
            $table->text('description')->nullable();
            $table->string('website', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('seo_title', 255)->nullable();
            $table->string('seo_description', 500)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'is_featured', 'sort_order']);
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 120)->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tags');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('categories');
    }
};
