<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table => [Bangla column => [type, length, English column it follows]].
     * Products, categories, specifications and districts already had name_bn
     * (and products the description pair); this adds the rest of the
     * customer-facing text. Every column is optional: an empty Bangla value
     * falls back to the English one.
     *
     * @var array<string, array<string, array{0: string, 1: ?int, 2: string}>>
     */
    private const COLUMNS = [
        'products' => [
            'warranty_bn' => ['string', 100, 'warranty'],
            'offer_text_bn' => ['string', 255, 'offer_text'],
            'faqs_bn' => ['json', null, 'faqs'],
            'seo_title_bn' => ['string', 255, 'seo_title'],
            'seo_description_bn' => ['string', 500, 'seo_description'],
        ],
        'categories' => [
            'description_bn' => ['text', null, 'description'],
            'seo_title_bn' => ['string', 255, 'seo_title'],
            'seo_description_bn' => ['string', 500, 'seo_description'],
        ],
        'attributes' => [
            'name_bn' => ['string', 100, 'name'],
        ],
        'attribute_values' => [
            'value_bn' => ['string', 100, 'value'],
        ],
        'specification_options' => [
            'value_bn' => ['string', 150, 'value'],
        ],
        'offers' => [
            'title_bn' => ['string', 150, 'title'],
            'summary_bn' => ['string', 255, 'summary'],
            'description_bn' => ['text', null, 'description'],
        ],
        'flash_sales' => [
            'title_bn' => ['string', 150, 'title'],
            'description_bn' => ['string', 500, 'description'],
        ],
        'banners' => [
            'title_bn' => ['string', 150, 'title'],
            'subtitle_bn' => ['string', 255, 'subtitle'],
            'cta_label_bn' => ['string', 50, 'cta_label'],
            'alt_text_bn' => ['string', 255, 'alt_text'],
        ],
        'home_sections' => [
            'title_bn' => ['string', 150, 'title'],
            'subtitle_bn' => ['string', 255, 'subtitle'],
        ],
        'service_highlights' => [
            'title_bn' => ['string', 100, 'title'],
            'subtitle_bn' => ['string', 150, 'subtitle'],
        ],
        'menus' => [
            'name_bn' => ['string', 100, 'name'],
        ],
        'menu_items' => [
            'label_bn' => ['string', 100, 'label'],
        ],
        'pages' => [
            'title_bn' => ['string', 150, 'title'],
            'content_bn' => ['longText', null, 'content'],
            'seo_title_bn' => ['string', 255, 'seo_title'],
            'seo_description_bn' => ['string', 500, 'seo_description'],
        ],
        'shipping_methods' => [
            'name_bn' => ['string', 100, 'name'],
            'description_bn' => ['string', 255, 'description'],
            'delivery_estimate_bn' => ['string', 100, 'delivery_estimate'],
        ],
        'shipping_zones' => [
            'name_bn' => ['string', 100, 'name'],
        ],
    ];

    public function up(): void
    {
        foreach (self::COLUMNS as $tableName => $columns) {
            Schema::table($tableName, function (Blueprint $table) use ($columns) {
                foreach ($columns as $column => [$type, $length, $after]) {
                    $definition = $length === null ? $table->{$type}($column) : $table->{$type}($column, $length);
                    $definition->nullable()->after($after);
                }
            });
        }
    }

    public function down(): void
    {
        foreach (self::COLUMNS as $tableName => $columns) {
            Schema::table($tableName, function (Blueprint $table) use ($columns) {
                $table->dropColumn(array_keys($columns));
            });
        }
    }
};
