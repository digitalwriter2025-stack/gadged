<?php

namespace Database\Seeders;

use App\Enums\SpecificationInputType as Input;
use App\Models\Specification;
use App\Models\SpecificationGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Specification groups and specifications for an electronics catalog.
 * Category ↔ specification mapping is done in CatalogSeeder.
 */
class SpecificationSeeder extends Seeder
{
    /**
     * group => [name, input type, unit, filterable, options]
     *
     * @var array<string, list<array{0: string, 1: Input, 2?: ?string, 3?: bool, 4?: list<string>}>>
     */
    public const GROUPS = [
        'Display' => [
            ['Screen Size', Input::Number, 'inch', true],
            ['Resolution', Input::Select, null, true, ['1366x768 (HD)', '1920x1080 (FHD)', '2560x1440 (QHD)', '3440x1440 (WQHD)', '3840x2160 (4K UHD)', '1080x2340 (FHD+)']],
            ['Refresh Rate', Input::Select, null, true, ['60Hz', '75Hz', '100Hz', '120Hz', '144Hz', '165Hz', '240Hz']],
            ['Panel Type', Input::Select, null, true, ['IPS', 'VA', 'TN', 'OLED', 'AMOLED', 'Super AMOLED']],
            ['Curved Display', Input::Boolean],
        ],
        'Processor' => [
            ['Processor Brand', Input::Select, null, true, ['Intel', 'AMD', 'Apple', 'MediaTek', 'Qualcomm', 'Samsung Exynos']],
            ['Processor Model', Input::Text],
            ['Generation', Input::Text],
            ['Cores', Input::Number, null, false],
            ['Threads', Input::Number, null, false],
        ],
        'Memory' => [
            ['RAM', Input::Number, 'GB', true],
            ['RAM Type', Input::Select, null, true, ['DDR4', 'DDR5', 'LPDDR4X', 'LPDDR5', 'LPDDR5X']],
        ],
        'Storage' => [
            ['Storage Capacity', Input::Select, null, true, ['128GB', '256GB', '512GB', '1TB', '2TB']],
            ['Storage Type', Input::Select, null, true, ['SSD', 'NVMe SSD', 'HDD', 'UFS', 'eMMC']],
        ],
        'Graphics' => [
            ['Graphics Chipset', Input::Text],
            ['Video Memory', Input::Number, 'GB', true],
            ['Video Memory Type', Input::Select, null, false, ['GDDR5', 'GDDR6', 'GDDR6X', 'GDDR7']],
            ['Memory Clock', Input::Text],
            ['Memory Bus', Input::Number, 'bit'],
            ['CUDA Cores', Input::Number],
            ['Bus Interface', Input::Text],
            ['DirectX', Input::Text],
            ['OpenGL', Input::Text],
            ['Max Resolution', Input::Text],
            ['Multi Display', Input::Number],
        ],
        'Connectivity' => [
            ['Wi-Fi', Input::Text],
            ['Bluetooth', Input::Text],
            ['Ports', Input::Text],
            ['Connection Type', Input::Select, null, true, ['Wired', 'Wireless', 'Wired & Wireless']],
        ],
        'Audio' => [
            ['Driver Size', Input::Number, 'mm'],
            ['Surround Sound', Input::Text],
            ['Output Power', Input::Number, 'W'],
        ],
        'Battery' => [
            ['Battery Capacity', Input::Number, 'mAh'],
            ['Fast Charging', Input::Text],
        ],
        'Camera' => [
            ['Rear Camera', Input::Text],
            ['Front Camera', Input::Text],
        ],
        'Physical' => [
            ['Color', Input::Text],
            ['Dimensions', Input::Text],
            ['Weight', Input::Text],
            ['Form Factor', Input::Select, null, true, ['ATX', 'Micro ATX', 'Mini ITX', 'Mini PC', 'Tower']],
        ],
        'Power' => [
            ['Capacity', Input::Text],
            ['Cooling Capacity', Input::Select, null, true, ['1 Ton', '1.5 Ton', '2 Ton']],
            ['Inverter', Input::Boolean],
            ['Power Consumption', Input::Number, 'W'],
            ['Recommended PSU', Input::Number, 'W'],
            ['Power Connector', Input::Text],
        ],
        'Warranty' => [
            ['Warranty Details', Input::Text],
        ],
    ];

    public function run(): void
    {
        $groupOrder = 0;

        foreach (self::GROUPS as $groupName => $specifications) {
            $group = SpecificationGroup::query()->firstOrCreate(
                ['slug' => Str::slug($groupName)],
                ['name' => $groupName, 'sort_order' => ++$groupOrder],
            );

            foreach ($specifications as $order => $definition) {
                [$name, $input] = $definition;

                $specification = Specification::query()->firstOrCreate(
                    ['slug' => Str::slug($name)],
                    [
                        'specification_group_id' => $group->id,
                        'name' => $name,
                        'input_type' => $input,
                        'unit' => $definition[2] ?? null,
                        'is_filterable' => $definition[3] ?? false,
                        'sort_order' => $order + 1,
                    ],
                );

                foreach ($definition[4] ?? [] as $position => $option) {
                    $specification->options()->firstOrCreate(
                        ['slug' => Str::slug($option)],
                        ['value' => $option, 'sort_order' => $position + 1],
                    );
                }
            }
        }
    }
}
