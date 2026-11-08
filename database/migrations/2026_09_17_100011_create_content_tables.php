<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * CMS: banners (hero slides + promotional banners), homepage sections,
     * service highlights, menus, static pages and key/value settings.
     */
    public function up(): void
    {
        // Hero slides are banners with placement "hero_main"; side and promo banners share the table.
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('placement', 30);
            $table->string('title', 150)->nullable();
            $table->string('subtitle', 255)->nullable();
            $table->string('cta_label', 50)->nullable();
            $table->string('url', 500)->nullable();
            $table->boolean('open_in_new_tab')->default(false);
            $table->string('alt_text', 255)->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['placement', 'is_active', 'sort_order']);
        });

        Schema::create('home_sections', function (Blueprint $table) {
            $table->id();
            $table->string('type', 30);
            $table->string('title', 150)->nullable();
            $table->string('subtitle', 255)->nullable();
            // Type-specific options, e.g. {"source":"latest","limit":12}.
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        // Hand-picked products / categories / brands for a section.
        Schema::create('home_section_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('home_section_id')->constrained()->cascadeOnDelete();
            $table->morphs('itemable');
            $table->unsignedInteger('sort_order')->default(0);

            $table->index(['home_section_id', 'sort_order']);
        });

        Schema::create('service_highlights', function (Blueprint $table) {
            $table->id();
            $table->string('icon', 100);
            $table->string('title', 100);
            $table->string('subtitle', 150)->nullable();
            $table->string('url', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('location', 50)->unique();
            $table->timestamps();
        });

        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('menu_items')->cascadeOnDelete();
            $table->string('label', 100);
            // Either a literal URL or a linked record (category, page, brand).
            $table->string('url', 500)->nullable();
            $table->nullableMorphs('linkable');
            $table->boolean('open_in_new_tab')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['menu_id', 'parent_id', 'sort_order']);
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title', 150);
            $table->string('slug', 191)->unique();
            $table->longText('content')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('seo_title', 255)->nullable();
            $table->string('seo_description', 500)->nullable();
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('group', 50);
            $table->string('key', 100);
            $table->json('value')->nullable();
            $table->timestamps();

            $table->unique(['group', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('menus');
        Schema::dropIfExists('service_highlights');
        Schema::dropIfExists('home_section_items');
        Schema::dropIfExists('home_sections');
        Schema::dropIfExists('banners');
    }
};
