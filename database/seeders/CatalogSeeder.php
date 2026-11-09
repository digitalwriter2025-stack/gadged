<?php

namespace Database\Seeders;

use App\Enums\FlashSaleDiscountType;
use App\Enums\InventoryTransactionType;
use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Enums\SpecificationInputType;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\FlashSale;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Specification;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Development catalog: category tree mirroring the reference navigation, brands,
 * variation attributes, category ↔ specification mapping, ~25 products (simple and
 * variable) with specifications and opening stock, and one running flash sale.
 * Idempotent — existing slugs are skipped. No images are attached.
 */
class CatalogSeeder extends Seeder
{
    /**
     * Top-level category => [icon, children => [name => [icon, featured]]]
     *
     * @var array<string, array{0: string, 1: array<string, array{0: string, 1: bool}>, 2?: bool}>
     */
    private const CATEGORIES = [
        'Smart Watch' => ['smartwatch', [], true],
        'Headphones' => ['headphone', ['Earbuds' => ['earbuds', true], 'Over-Ear Headphones' => ['headphone', false]], true],
        'Speaker' => ['speaker', [], true],
        'Powerbanks' => ['powerbank', [], true],
        'Charger & Cable' => ['charger', [], true],
        'Smart Phone' => ['smartphone', [], true],
    ];

    /**
     * Category slug => specification slugs (in display order; select/number ones become filters).
     *
     * @var array<string, list<string>>
     */
    private const CATEGORY_SPECIFICATIONS = [
        'smart-phone' => ['screen-size', 'resolution', 'refresh-rate', 'panel-type', 'processor-brand', 'processor-model', 'ram', 'storage-capacity', 'rear-camera', 'front-camera', 'battery-capacity', 'fast-charging', 'warranty-details'],
        'over-ear-headphones' => ['connection-type', 'driver-size', 'surround-sound', 'color', 'warranty-details'],
        'speaker' => ['connection-type', 'output-power', 'bluetooth', 'battery-capacity', 'color', 'weight', 'warranty-details'],
        'smart-watch' => ['screen-size', 'panel-type', 'bluetooth', 'battery-capacity', 'warranty-details'],
        'earbuds' => ['connection-type', 'driver-size', 'bluetooth', 'battery-capacity', 'color', 'warranty-details'],
        'powerbanks' => ['battery-capacity', 'output-power', 'fast-charging', 'ports', 'connection-type', 'color', 'weight', 'warranty-details'],
        'charger-cable' => ['output-power', 'fast-charging', 'ports', 'connection-type', 'warranty-details'],
    ];

    /**
     * @var list<array{0: string, 1: ?string}>
     */
    private const BRANDS = [
        ['Apple', 'https://www.apple.com'], ['Samsung', 'https://www.samsung.com'], ['Google', 'https://store.google.com'],
        ['HONOR', 'https://www.honor.com'], ['Xiaomi', 'https://www.mi.com'], ['Anker', 'https://www.anker.com'],
        ['JBL', 'https://www.jbl.com'], ['Amazfit', 'https://www.amazfit.com'], ['CMF by Nothing', 'https://nothing.tech'],
        ['KOSPET', null], ['Haylou', null], ['Kieslect', null], ['SoundPEATS', null], ['QCY', null], ['Redragon', 'https://www.redragonzone.com'],
    ];

    /**
     * name, category slug, brand (null when unbranded), regular price, sale price, stock, model,
     * warranty, flags (featured, latest, best, trending, flash), optional sku (default: by position)
     * and search keywords (default: model), combo contents for bundles, specs.
     * Prices are sample values for development.
     *
     * @var list<array<string, mixed>>
     */
    private const PRODUCTS = [
        ['name' => 'Redragon H320 Lamia RGB Virtual 7.1 Surround Sound Gaming Headset', 'category' => 'over-ear-headphones', 'brand' => 'redragon', 'price' => '3600', 'stock' => 25, 'model' => 'H320', 'warranty' => '1 Year', 'flash' => '2949',
            'specs' => ['connection-type' => 'Wired', 'driver-size' => '53', 'surround-sound' => 'Virtual 7.1', 'color' => 'Black']],
        ['name' => 'Redragon H130 Pelias Wired Gaming Headphone', 'category' => 'over-ear-headphones', 'brand' => 'redragon', 'price' => '1450', 'stock' => 40, 'model' => 'H130', 'warranty' => '6 Months', 'best' => true, 'flash' => '999',
            'specs' => ['connection-type' => 'Wired', 'driver-size' => '50', 'color' => 'Black']],
        ['name' => 'Redragon H231 Scream Wired RGB Gaming Headphone (White)', 'category' => 'over-ear-headphones', 'brand' => 'redragon', 'price' => '2150', 'stock' => 18, 'model' => 'H231', 'warranty' => '1 Year', 'flash' => '1620',
            'specs' => ['connection-type' => 'Wired', 'driver-size' => '50', 'color' => 'White']],
        ['name' => 'JBL PartyBox 720 800W Bluetooth Party Speaker', 'category' => 'speaker', 'brand' => 'jbl', 'price' => '118800', 'sale' => '102500', 'stock' => 3, 'model' => 'PartyBox 720', 'warranty' => '1 Year', 'trending' => true, 'latest' => true,
            'specs' => ['connection-type' => 'Wired & Wireless', 'output-power' => '800', 'bluetooth' => '5.4']],
        ['name' => 'KOSPET TANK T3 ULTRA 2 1.43 Inch AMOLED GPS BT Calling Smartwatch', 'category' => 'smart-watch', 'brand' => 'kospet', 'price' => '10499', 'sale' => '9790', 'stock' => 15, 'model' => 'TANK T3 ULTRA 2', 'warranty' => '1 Year', 'trending' => true, 'latest' => true,
            'specs' => ['screen-size' => '1.43', 'panel-type' => 'AMOLED', 'bluetooth' => '5.3', 'battery-capacity' => '480']],

        ['name' => 'Apple iPhone 17 Pro 256GB Cosmic Orange', 'category' => 'smart-phone', 'brand' => 'apple', 'price' => '175000', 'sale' => '165000', 'stock' => 4, 'model' => 'iPhone 17 Pro', 'warranty' => '1 Year', 'trending' => true,
            'specs' => ['screen-size' => '6.3', 'refresh-rate' => '120Hz', 'panel-type' => 'OLED', 'processor-brand' => 'Apple', 'processor-model' => 'A19 Pro', 'ram' => '12', 'storage-capacity' => '256GB', 'rear-camera' => '48MP + 48MP + 48MP', 'front-camera' => '18MP']],
        ['name' => 'Samsung Galaxy S26 Ultra 12GB/256GB Cobalt Violet', 'category' => 'smart-phone', 'brand' => 'samsung', 'price' => '169999', 'sale' => '158000', 'stock' => 5, 'model' => 'Galaxy S26 Ultra', 'warranty' => '1 Year', 'trending' => true,
            'specs' => ['screen-size' => '6.9', 'refresh-rate' => '120Hz', 'panel-type' => 'AMOLED', 'processor-brand' => 'Qualcomm', 'ram' => '12', 'storage-capacity' => '256GB', 'battery-capacity' => '5000']],
        ['name' => 'Apple iPhone 17 256GB Lavender', 'category' => 'smart-phone', 'brand' => 'apple', 'price' => '125000', 'sale' => '118000', 'stock' => 6, 'model' => 'iPhone 17', 'warranty' => '1 Year',
            'specs' => ['screen-size' => '6.3', 'refresh-rate' => '120Hz', 'panel-type' => 'OLED', 'processor-brand' => 'Apple', 'processor-model' => 'A19', 'ram' => '8', 'storage-capacity' => '256GB', 'rear-camera' => '48MP + 48MP', 'front-camera' => '18MP']],
        ['name' => 'Google Pixel 9 Pro 16GB/256GB Obsidian', 'category' => 'smart-phone', 'brand' => 'google', 'price' => '112000', 'sale' => '98500', 'stock' => 3, 'model' => 'Pixel 9 Pro', 'warranty' => '1 Year',
            'specs' => ['screen-size' => '6.3', 'refresh-rate' => '120Hz', 'panel-type' => 'OLED', 'processor-model' => 'Google Tensor G4', 'ram' => '16', 'storage-capacity' => '256GB']],
        ['name' => 'HONOR X7d 8GB/256GB Smartphone', 'category' => 'smart-phone', 'brand' => 'honor', 'price' => '26999', 'sale' => '23999', 'stock' => 12, 'model' => 'X7d', 'warranty' => '1 Year', 'best' => true,
            'specs' => ['ram' => '8', 'storage-capacity' => '256GB']],
        ['name' => 'Apple Watch Series 11 GPS 46mm Aluminum Case with Sport Band', 'category' => 'smart-watch', 'brand' => 'apple', 'price' => '56000', 'sale' => '52500', 'stock' => 4, 'model' => 'Watch Series 11', 'warranty' => '1 Year',
            'specs' => ['panel-type' => 'OLED']],
        ['name' => 'Amazfit Active 2 Square AMOLED GPS Smart Watch', 'category' => 'smart-watch', 'brand' => 'amazfit', 'price' => '19900', 'sale' => '17500', 'stock' => 7, 'model' => 'Active 2 Square', 'warranty' => '1 Year',
            'specs' => ['panel-type' => 'AMOLED']],
        ['name' => 'CMF by Nothing Watch Pro 2 AMOLED Bluetooth Calling Smart Watch', 'category' => 'smart-watch', 'brand' => 'cmf-by-nothing', 'price' => '8999', 'sale' => '7490', 'stock' => 10, 'model' => 'Watch Pro 2', 'warranty' => '1 Year', 'trending' => true, 'flash' => '6490',
            'specs' => ['panel-type' => 'AMOLED']],
        ['name' => 'Haylou Solar Pro AMOLED Bluetooth Calling Smart Watch', 'category' => 'smart-watch', 'brand' => 'haylou', 'price' => '6500', 'sale' => '5490', 'stock' => 14, 'model' => 'Solar Pro', 'warranty' => '1 Year', 'best' => true, 'flash' => '4590',
            'specs' => ['panel-type' => 'AMOLED']],
        ['name' => 'Kieslect KS3 AMOLED Bluetooth Calling Smart Watch', 'category' => 'smart-watch', 'brand' => 'kieslect', 'price' => '5990', 'sale' => '4790', 'stock' => 16, 'model' => 'KS3', 'warranty' => '1 Year',
            'specs' => ['panel-type' => 'AMOLED']],
        ['name' => 'Apple AirPods 4 Wireless Earbuds with USB-C Charging Case', 'category' => 'earbuds', 'brand' => 'apple', 'price' => '18500', 'sale' => '16500', 'stock' => 8, 'model' => 'AirPods 4', 'warranty' => '1 Year', 'trending' => true,
            'specs' => ['connection-type' => 'Wireless', 'color' => 'White']],
        ['name' => 'SoundPEATS Life ANC Wireless Earbuds', 'category' => 'earbuds', 'brand' => 'soundpeats', 'price' => '4290', 'sale' => '3490', 'stock' => 20, 'model' => 'Life ANC', 'warranty' => '6 Months',
            'specs' => ['connection-type' => 'Wireless', 'color' => 'Black']],
        ['name' => 'Xiaomi Redmi Buds 6 ANC TWS Earbuds', 'category' => 'earbuds', 'brand' => 'xiaomi', 'price' => '3990', 'sale' => '3490', 'stock' => 18, 'model' => 'Redmi Buds 6', 'warranty' => '6 Months', 'best' => true, 'flash' => '2890',
            'specs' => ['connection-type' => 'Wireless', 'color' => 'Black']],
        ['name' => 'Anker Soundcore R50i NC Wireless Earbuds', 'category' => 'earbuds', 'brand' => 'anker', 'price' => '3990', 'sale' => '3290', 'stock' => 22, 'model' => 'Soundcore R50i NC', 'warranty' => '18 Months', 'flash' => '2790',
            'specs' => ['connection-type' => 'Wireless', 'color' => 'Black']],
        ['name' => 'QCY AilyBuds Pro+ ANC Wireless Earbuds', 'category' => 'earbuds', 'brand' => 'qcy', 'price' => '3200', 'sale' => '2690', 'stock' => 25, 'model' => 'AilyBuds Pro+', 'warranty' => '6 Months', 'flash' => '2190',
            'specs' => ['connection-type' => 'Wireless', 'color' => 'Blue']],

        // Unbranded power banks.
        ['name' => 'Compact Ivory 10000mAh 20W Fast Charging Power Bank', 'category' => 'powerbanks', 'brand' => null, 'sku' => 'GD-PB-01', 'price' => '1890', 'sale' => '1590', 'stock' => 30, 'model' => 'PB-10K20', 'warranty' => '6 Months', 'best' => true,
            'keywords' => 'powerbank, portable charger, 10000mAh, ivory',
            'specs' => ['battery-capacity' => '10000', 'output-power' => '20', 'fast-charging' => 'PD 20W, QC 3.0', 'ports' => '1x USB-C, 1x USB-A', 'connection-type' => 'Wired', 'color' => 'Ivory', 'weight' => '210g']],
        ['name' => 'Premium Brushed Silver 20000mAh 65W PD Power Bank', 'category' => 'powerbanks', 'brand' => null, 'sku' => 'GD-PB-02', 'price' => '4490', 'sale' => '3890', 'stock' => 15, 'model' => 'PB-20K65', 'warranty' => '1 Year', 'flash' => '3290',
            'keywords' => 'powerbank, portable charger, 20000mAh, 65W, silver, aluminium',
            'specs' => ['battery-capacity' => '20000', 'output-power' => '65', 'fast-charging' => 'PD 3.0 65W, QC 3.0', 'ports' => '2x USB-C, 1x USB-A', 'connection-type' => 'Wired', 'color' => 'Silver', 'weight' => '420g']],
        ['name' => 'Premium Graphite 30000mAh 140W Laptop Power Bank', 'category' => 'powerbanks', 'brand' => null, 'sku' => 'GD-PB-03', 'price' => '8990', 'sale' => '7990', 'stock' => 8, 'model' => 'PB-30K140', 'warranty' => '1 Year', 'trending' => true, 'featured' => true,
            'keywords' => 'powerbank, laptop power bank, macbook charger, 30000mAh, 140W, digital display',
            'specs' => ['battery-capacity' => '30000', 'output-power' => '140', 'fast-charging' => 'PD 3.1 140W', 'ports' => '3x USB-C, 1x USB-A', 'connection-type' => 'Wired', 'color' => 'Graphite', 'weight' => '640g']],
        ['name' => 'Premium Forest Green 20000mAh 30W Power Bank', 'category' => 'powerbanks', 'brand' => null, 'sku' => 'GD-PB-04', 'price' => '3290', 'sale' => '2790', 'stock' => 18, 'model' => 'PB-20K30', 'warranty' => '1 Year',
            'keywords' => 'powerbank, portable charger, 20000mAh, green',
            'specs' => ['battery-capacity' => '20000', 'output-power' => '30', 'fast-charging' => 'PD 30W, QC 3.0', 'ports' => '1x USB-C, 2x USB-A', 'connection-type' => 'Wired', 'color' => 'Forest Green', 'weight' => '390g']],
        ['name' => 'Warm White 5000mAh Magnetic Wireless Power Bank', 'category' => 'powerbanks', 'brand' => null, 'sku' => 'GD-PB-05', 'price' => '2990', 'sale' => '2490', 'stock' => 20, 'model' => 'PB-5KMW', 'warranty' => '6 Months', 'flash' => '1990',
            'keywords' => 'powerbank, magsafe, magnetic, wireless charger, iphone, 5000mAh',
            'specs' => ['battery-capacity' => '5000', 'output-power' => '15', 'fast-charging' => '15W Magnetic Wireless, PD 20W', 'ports' => '1x USB-C', 'connection-type' => 'Wired & Wireless', 'color' => 'Warm White', 'weight' => '120g']],
        ['name' => 'Slim Matte Charcoal 10000mAh 20W Power Bank', 'category' => 'powerbanks', 'brand' => null, 'sku' => 'GD-PB-06', 'price' => '1990', 'sale' => '1690', 'stock' => 35, 'model' => 'PB-10KS', 'warranty' => '6 Months',
            'keywords' => 'powerbank, slim, portable charger, 10000mAh, black',
            'specs' => ['battery-capacity' => '10000', 'output-power' => '20', 'fast-charging' => 'PD 20W, QC 3.0', 'ports' => '1x USB-C, 1x USB-A', 'connection-type' => 'Wired', 'color' => 'Charcoal', 'weight' => '190g']],
        ['name' => 'Deep Navy 20000mAh 22.5W Fast Charging Power Bank', 'category' => 'powerbanks', 'brand' => null, 'sku' => 'GD-PB-07', 'price' => '2790', 'sale' => '2390', 'stock' => 25, 'model' => 'PB-20K22', 'warranty' => '1 Year', 'best' => true,
            'keywords' => 'powerbank, portable charger, 20000mAh, blue',
            'specs' => ['battery-capacity' => '20000', 'output-power' => '22.5', 'fast-charging' => 'PD 20W, QC 22.5W', 'ports' => '1x USB-C, 2x USB-A', 'connection-type' => 'Wired', 'color' => 'Navy Blue', 'weight' => '400g']],
        ['name' => 'Premium Black 40000mAh 100W High-Capacity Power Bank', 'category' => 'powerbanks', 'brand' => null, 'sku' => 'GD-PB-08', 'price' => '9990', 'sale' => '8790', 'stock' => 6, 'model' => 'PB-40K100', 'warranty' => '1 Year', 'featured' => true,
            'keywords' => 'powerbank, high capacity, laptop power bank, 40000mAh, 100W',
            'specs' => ['battery-capacity' => '40000', 'output-power' => '100', 'fast-charging' => 'PD 3.0 100W, QC 3.0', 'ports' => '2x USB-C, 2x USB-A', 'connection-type' => 'Wired', 'color' => 'Black', 'weight' => '820g']],
        ['name' => 'Premium Space Gray 25000mAh 100W Laptop Power Bank', 'category' => 'powerbanks', 'brand' => null, 'sku' => 'GD-PB-09', 'price' => '7490', 'sale' => '6590', 'stock' => 10, 'model' => 'PB-25K100', 'warranty' => '1 Year',
            'keywords' => 'powerbank, laptop power bank, macbook charger, 25000mAh, 100W, gray',
            'specs' => ['battery-capacity' => '25000', 'output-power' => '100', 'fast-charging' => 'PD 3.0 100W', 'ports' => '2x USB-C, 1x USB-A', 'connection-type' => 'Wired', 'color' => 'Space Gray', 'weight' => '560g']],
        ['name' => 'Lavender 10000mAh Magnetic Wireless Power Bank', 'category' => 'powerbanks', 'brand' => null, 'sku' => 'GD-PB-10', 'price' => '3490', 'sale' => '2890', 'stock' => 16, 'model' => 'PB-10KMW', 'warranty' => '1 Year', 'trending' => true,
            'keywords' => 'powerbank, magsafe, magnetic, wireless charger, iphone, 10000mAh, purple',
            'specs' => ['battery-capacity' => '10000', 'output-power' => '15', 'fast-charging' => '15W Magnetic Wireless, PD 20W', 'ports' => '1x USB-C', 'connection-type' => 'Wired & Wireless', 'color' => 'Lavender', 'weight' => '200g']],

        // Unbranded chargers, cables and adapters.
        ['name' => 'Braided USB-C to USB-C 60W Fast Charging Cable 1m', 'category' => 'charger-cable', 'brand' => null, 'sku' => 'GD-CC-01', 'price' => '690', 'sale' => '550', 'stock' => 60, 'model' => 'CB-CC60', 'warranty' => '6 Months',
            'keywords' => 'type c cable, usb c cable, charging cable, data cable, braided, 60W',
            'specs' => ['output-power' => '60', 'fast-charging' => 'PD 60W', 'ports' => 'USB-C to USB-C', 'connection-type' => 'Wired']],
        ['name' => 'Brushed Silver USB-C to USB-A 3.0 OTG Adapter', 'category' => 'charger-cable', 'brand' => null, 'sku' => 'GD-CC-02', 'price' => '450', 'sale' => '350', 'stock' => 50, 'model' => 'AD-OTG3', 'warranty' => '3 Months',
            'keywords' => 'otg, type c adapter, usb c to usb, converter, pendrive adapter',
            'specs' => ['ports' => 'USB-C (male) to USB-A 3.0 (female)', 'connection-type' => 'Wired']],
        ['name' => 'White USB-A to USB-C 3A Fast Charging Cable 1m', 'category' => 'charger-cable', 'brand' => null, 'sku' => 'GD-CC-03', 'price' => '350', 'sale' => '290', 'stock' => 80, 'model' => 'CB-AC3A', 'warranty' => '3 Months', 'best' => true,
            'keywords' => 'type c cable, usb c cable, charging cable, data cable',
            'specs' => ['output-power' => '15', 'fast-charging' => 'QC 3.0, 3A', 'ports' => 'USB-A to USB-C', 'connection-type' => 'Wired']],
        ['name' => 'Compact Black 38W Dual-Port Car Charger', 'category' => 'charger-cable', 'brand' => null, 'sku' => 'GD-CC-04', 'price' => '1290', 'sale' => '990', 'stock' => 30, 'model' => 'CC-38D', 'warranty' => '6 Months',
            'keywords' => 'car charger, car adapter, fast charger, usb c, 38W',
            'specs' => ['output-power' => '38', 'fast-charging' => 'PD 20W + QC 3.0 18W', 'ports' => '1x USB-C, 1x USB-A', 'connection-type' => 'Wired']],
        ['name' => 'Matte Black USB-A to Micro-USB 2.4A Charging Cable 1m', 'category' => 'charger-cable', 'brand' => null, 'sku' => 'GD-CC-05', 'price' => '250', 'sale' => '199', 'stock' => 80, 'model' => 'CB-AM24', 'warranty' => '3 Months',
            'keywords' => 'micro usb cable, android cable, charging cable, data cable',
            'specs' => ['output-power' => '12', 'fast-charging' => '2.4A', 'ports' => 'USB-A to Micro-USB', 'connection-type' => 'Wired']],
        ['name' => 'Matte Charcoal 35W Dual-Port Wall Charger', 'category' => 'charger-cable', 'brand' => null, 'sku' => 'GD-CC-06', 'price' => '1590', 'sale' => '1290', 'stock' => 35, 'model' => 'WC-35D', 'warranty' => '6 Months', 'trending' => true, 'flash' => '1050',
            'keywords' => 'wall charger, adapter, fast charger, usb c charger, 35W',
            'specs' => ['output-power' => '35', 'fast-charging' => 'PD 3.0, QC 3.0', 'ports' => '1x USB-C, 1x USB-A', 'connection-type' => 'Wired']],
        ['name' => 'Satin White 20W USB-C PD Wall Charger', 'category' => 'charger-cable', 'brand' => null, 'sku' => 'GD-CC-07', 'price' => '990', 'sale' => '790', 'stock' => 45, 'model' => 'WC-20C', 'warranty' => '6 Months', 'best' => true,
            'keywords' => 'wall charger, iphone charger, adapter, usb c charger, 20W',
            'specs' => ['output-power' => '20', 'fast-charging' => 'PD 20W', 'ports' => '1x USB-C', 'connection-type' => 'Wired']],
        ['name' => 'Slim 15W Magnetic Wireless Charging Pad', 'category' => 'charger-cable', 'brand' => null, 'sku' => 'GD-CC-08', 'price' => '1890', 'sale' => '1490', 'stock' => 20, 'model' => 'WP-15M', 'warranty' => '6 Months', 'trending' => true,
            'keywords' => 'wireless charger, magsafe, qi charger, charging pad, iphone',
            'specs' => ['output-power' => '15', 'fast-charging' => 'Qi 15W', 'ports' => '1x USB-C (input)', 'connection-type' => 'Wireless']],
        ['name' => 'Space Gray 5-in-1 USB-C Hub with 4K HDMI', 'category' => 'charger-cable', 'brand' => null, 'sku' => 'GD-CC-09', 'price' => '2490', 'sale' => '1990', 'stock' => 15, 'model' => 'HB-5C4K', 'warranty' => '6 Months',
            'keywords' => 'usb c hub, type c hub, multiport adapter, hdmi adapter, docking, macbook',
            'specs' => ['ports' => '1x HDMI 4K, 2x USB-A 3.0, SD, microSD', 'connection-type' => 'Wired']],
        ['name' => 'White USB-A to Lightning 2.4A Charging Cable 1m', 'category' => 'charger-cable', 'brand' => null, 'sku' => 'GD-CC-10', 'price' => '590', 'sale' => '450', 'stock' => 60, 'model' => 'CB-AL24', 'warranty' => '3 Months',
            'keywords' => 'lightning cable, iphone cable, charging cable, data cable',
            'specs' => ['output-power' => '12', 'fast-charging' => '2.4A', 'ports' => 'USB-A to Lightning', 'connection-type' => 'Wired']],

        // Unbranded speakers.
        ['name' => 'Charcoal 10W Portable Bluetooth Speaker', 'category' => 'speaker', 'brand' => null, 'sku' => 'GD-SP-01', 'price' => '1990', 'sale' => '1590', 'stock' => 30, 'model' => 'SP-10P', 'warranty' => '6 Months', 'best' => true,
            'keywords' => 'bluetooth speaker, portable speaker, mini speaker, wireless speaker, black',
            'specs' => ['connection-type' => 'Wireless', 'output-power' => '10', 'bluetooth' => '5.3', 'battery-capacity' => '1800', 'color' => 'Charcoal', 'weight' => '380g']],
        ['name' => 'Elegant Cream Retro 15W Bluetooth Speaker', 'category' => 'speaker', 'brand' => null, 'sku' => 'GD-SP-02', 'price' => '3490', 'sale' => '2890', 'stock' => 15, 'model' => 'SP-15R', 'warranty' => '6 Months', 'trending' => true,
            'keywords' => 'retro speaker, vintage speaker, bluetooth speaker, leather, cream, gold',
            'specs' => ['connection-type' => 'Wireless', 'output-power' => '15', 'bluetooth' => '5.3', 'battery-capacity' => '2400', 'color' => 'Cream', 'weight' => '720g']],
        ['name' => 'Rugged Navy 20W Waterproof Portable Bluetooth Speaker', 'category' => 'speaker', 'brand' => null, 'sku' => 'GD-SP-03', 'price' => '2990', 'sale' => '2490', 'stock' => 25, 'model' => 'SP-20W', 'warranty' => '1 Year', 'best' => true, 'flash' => '1990',
            'keywords' => 'bluetooth speaker, waterproof speaker, outdoor speaker, ipx7, blue',
            'specs' => ['connection-type' => 'Wireless', 'output-power' => '20', 'bluetooth' => '5.3', 'battery-capacity' => '3600', 'color' => 'Navy Blue', 'weight' => '560g']],
        ['name' => 'Slim Black 40W Bluetooth Soundbar', 'category' => 'speaker', 'brand' => null, 'sku' => 'GD-SP-04', 'price' => '4990', 'sale' => '4290', 'stock' => 12, 'model' => 'SB-40S', 'warranty' => '1 Year',
            'keywords' => 'soundbar, tv speaker, computer speaker, bluetooth soundbar, aux',
            'specs' => ['connection-type' => 'Wired & Wireless', 'output-power' => '40', 'bluetooth' => '5.3', 'battery-capacity' => '2000', 'color' => 'Black', 'weight' => '1.1kg']],
        ['name' => 'Premium Silver-Gray 20W Smart Speaker with Voice Assistant', 'category' => 'speaker', 'brand' => null, 'sku' => 'GD-SP-05', 'price' => '5990', 'sale' => '4990', 'stock' => 10, 'model' => 'SP-20S', 'warranty' => '1 Year', 'featured' => true,
            'keywords' => 'smart speaker, voice assistant, wifi speaker, home speaker, gray',
            'specs' => ['connection-type' => 'Wireless', 'output-power' => '20', 'bluetooth' => '5.3', 'battery-capacity' => '5200', 'color' => 'Silver-Gray', 'weight' => '950g']],
        ['name' => 'Matte Black 25W RGB Gaming Bluetooth Speaker', 'category' => 'speaker', 'brand' => null, 'sku' => 'GD-SP-06', 'price' => '3990', 'sale' => '3390', 'stock' => 18, 'model' => 'SP-25G', 'warranty' => '1 Year', 'trending' => true,
            'keywords' => 'gaming speaker, rgb speaker, pc speaker, bluetooth speaker, led',
            'specs' => ['connection-type' => 'Wired & Wireless', 'output-power' => '25', 'bluetooth' => '5.3', 'battery-capacity' => '3000', 'color' => 'Matte Black', 'weight' => '1.2kg']],
        ['name' => 'Portable 80W Party Speaker with Blue LED Rings', 'category' => 'speaker', 'brand' => null, 'sku' => 'GD-SP-07', 'price' => '9990', 'sale' => '8490', 'stock' => 8, 'model' => 'PS-80L', 'warranty' => '1 Year', 'trending' => true, 'featured' => true,
            'keywords' => 'party speaker, karaoke speaker, dj speaker, led speaker, dual woofer, mic input',
            'specs' => ['connection-type' => 'Wired & Wireless', 'output-power' => '80', 'bluetooth' => '5.3', 'battery-capacity' => '6000', 'color' => 'Black', 'weight' => '4.8kg']],
        ['name' => 'Premium Olive 40W Portable Bluetooth Boombox Speaker', 'category' => 'speaker', 'brand' => null, 'sku' => 'GD-SP-08', 'price' => '5490', 'sale' => '4690', 'stock' => 14, 'model' => 'SP-40B', 'warranty' => '1 Year', 'latest' => true,
            'keywords' => 'boombox, bluetooth speaker, outdoor speaker, portable speaker, green',
            'specs' => ['connection-type' => 'Wireless', 'output-power' => '40', 'bluetooth' => '5.3', 'battery-capacity' => '7200', 'color' => 'Olive Green', 'weight' => '1.6kg']],
        ['name' => 'Walnut 50W Bluetooth Bookshelf Speaker with Copper Woofer', 'category' => 'speaker', 'brand' => null, 'sku' => 'GD-SP-09', 'price' => '8490', 'sale' => '7490', 'stock' => 6, 'model' => 'BS-50W', 'warranty' => '1 Year', 'latest' => true,
            'keywords' => 'bookshelf speaker, wooden speaker, hifi speaker, home audio, walnut',
            'specs' => ['connection-type' => 'Wired & Wireless', 'output-power' => '50', 'bluetooth' => '5.3', 'color' => 'Walnut', 'weight' => '3.2kg']],

        // Combos: sold as one product at their own price and stock; 'combo' lists what is in the box (slug => quantity).
        ['name' => 'iPhone 17 Essentials Combo: iPhone 17 + AirPods 4 + 20W Charger', 'category' => 'smart-phone', 'brand' => null, 'sku' => 'GD-CB-01', 'price' => '144490', 'sale' => '132990', 'stock' => 3, 'model' => 'Combo iPhone 17 Essentials', 'warranty' => '1 Year', 'trending' => true,
            'keywords' => 'combo, bundle, iphone combo, airpods, charger',
            'combo' => ['apple-iphone-17-256gb-lavender' => 1, 'apple-airpods-4-wireless-earbuds-with-usb-c-charging-case' => 1, 'satin-white-20w-usb-c-pd-wall-charger' => 1],
            'specs' => []],
        ['name' => 'Galaxy Power Combo: Galaxy S26 Ultra + 20000mAh 65W Power Bank + USB-C Cable', 'category' => 'smart-phone', 'brand' => null, 'sku' => 'GD-CB-02', 'price' => '175179', 'sale' => '162990', 'stock' => 3, 'model' => 'Combo Galaxy Power', 'warranty' => '1 Year',
            'keywords' => 'combo, bundle, samsung combo, power bank, cable',
            'combo' => ['samsung-galaxy-s26-ultra-12gb256gb-cobalt-violet' => 1, 'premium-brushed-silver-20000mah-65w-pd-power-bank' => 1, 'braided-usb-c-to-usb-c-60w-fast-charging-cable-1m' => 1],
            'specs' => []],
        ['name' => 'Travel Charging Combo: 10000mAh Power Bank + 35W Wall Charger + Car Charger + Cable', 'category' => 'charger-cable', 'brand' => null, 'sku' => 'GD-CB-03', 'price' => '5120', 'sale' => '3990', 'stock' => 15, 'model' => 'Combo Travel Charging', 'warranty' => '6 Months', 'trending' => true,
            'keywords' => 'combo, bundle, travel kit, power bank, charger, car charger',
            'combo' => ['compact-ivory-10000mah-20w-fast-charging-power-bank' => 1, 'matte-charcoal-35w-dual-port-wall-charger' => 1, 'compact-black-38w-dual-port-car-charger' => 1, 'white-usb-a-to-usb-c-3a-fast-charging-cable-1m' => 1],
            'specs' => []],
        ['name' => 'Fitness Combo: Amazfit Active 2 Smart Watch + SoundPEATS Life ANC Earbuds', 'category' => 'smart-watch', 'brand' => null, 'sku' => 'GD-CB-04', 'price' => '24190', 'sale' => '19490', 'stock' => 6, 'model' => 'Combo Fitness', 'warranty' => '1 Year',
            'keywords' => 'combo, bundle, smart watch, earbuds, fitness, gym',
            'combo' => ['amazfit-active-2-square-amoled-gps-smart-watch' => 1, 'soundpeats-life-anc-wireless-earbuds' => 1],
            'specs' => []],
        ['name' => 'Desk Setup Combo: 5-in-1 USB-C Hub + Wireless Charging Pad + Braided Cable', 'category' => 'charger-cable', 'brand' => null, 'sku' => 'GD-CB-05', 'price' => '5070', 'sale' => '3990', 'stock' => 10, 'model' => 'Combo Desk Setup', 'warranty' => '6 Months',
            'keywords' => 'combo, bundle, desk setup, usb c hub, wireless charger, macbook',
            'combo' => ['space-gray-5-in-1-usb-c-hub-with-4k-hdmi' => 1, 'slim-15w-magnetic-wireless-charging-pad' => 1, 'braided-usb-c-to-usb-c-60w-fast-charging-cable-1m' => 1],
            'specs' => []],
    ];

    public function run(InventoryService $inventory): void
    {
        $admin = User::query()->where('email', env('ADMIN_EMAIL', 'admin@example.com'))->first();

        $categories = $this->seedCategories();
        $brands = $this->seedBrands();
        $this->mapCategorySpecifications($categories);
        $specifications = Specification::query()->with('options')->get()->keyBy('slug');
        $attributes = $this->seedAttributes();

        $comboContents = [];

        foreach (self::PRODUCTS as $position => $data) {
            $slug = Str::slug($data['name']);

            if (Product::withTrashed()->where('slug', $slug)->exists()) {
                continue;
            }

            $product = Product::query()->create([
                'type' => ProductType::Simple,
                'name' => $data['name'],
                'slug' => $slug,
                'sku' => $data['sku'] ?? 'GD-'.str_pad((string) ($position + 1001), 5, '0', STR_PAD_LEFT),
                'model' => $data['model'],
                'brand_id' => $data['brand'] !== null ? $brands[$data['brand']]->id : null,
                'category_id' => $categories[$data['category']]->id,
                'short_description' => $data['name'].'. '.$data['warranty'].' official warranty.',
                'description' => $data['description'] ?? null,
                'warranty' => $data['warranty'],
                'is_featured' => $data['featured'] ?? false,
                'is_latest' => $data['latest'] ?? false,
                'is_best_seller' => $data['best'] ?? false,
                'is_trending' => $data['trending'] ?? false,
                'is_combo' => isset($data['combo']),
                'status' => ProductStatus::Active,
                'published_at' => now()->subDays(count(self::PRODUCTS) - $position),
                'search_keywords' => $data['keywords'] ?? $data['model'],
                'created_by' => $admin?->id,
            ]);

            // Also list the product under the parent category.
            if ($parentId = $categories[$data['category']]->parent_id) {
                $product->categories()->syncWithoutDetaching([$parentId]);
            }

            $variant = $product->variants()->create([
                'sku' => $product->sku,
                'price' => $data['price'],
                'sale_price' => $data['sale'] ?? null,
                'cost_price' => bcmul($data['price'], '0.8', 2),
                'is_default' => true,
            ]);

            if ($data['stock'] > 0) {
                $inventory->adjust($variant, InventoryTransactionType::StockIn, $data['stock'], 'Opening stock', performedBy: $admin);
            }

            $this->setSpecifications($product, $data['specs'], $specifications);

            if (isset($data['combo'])) {
                $comboContents[] = [$product, $data['combo']];
            }
        }

        $this->seedVariableProducts($categories, $brands, $specifications, $attributes, $inventory, $admin);
        $this->seedFlashSale();
        $this->seedComboItems($comboContents);
    }

    /**
     * Records what is inside each newly created combo, once every bundled product exists.
     *
     * @param  list<array{0: Product, 1: array<string, int>}>  $combos  combo => [product slug => quantity]
     */
    private function seedComboItems(array $combos): void
    {
        foreach ($combos as [$combo, $contents]) {
            $productIds = Product::query()->whereIn('slug', array_keys($contents))->pluck('id', 'slug');

            foreach (array_keys($contents) as $order => $slug) {
                $combo->comboItems()->create([
                    'product_id' => $productIds[$slug],
                    'quantity' => $contents[$slug],
                    'sort_order' => $order + 1,
                ]);
            }
        }
    }

    /**
     * @return Collection<string, Category>
     */
    private function seedCategories(): Collection
    {
        $order = 0;

        foreach (self::CATEGORIES as $name => $definition) {
            [$icon, $children] = $definition;

            $parent = Category::query()->firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'icon' => $icon, 'sort_order' => ++$order, 'is_featured' => $definition[2] ?? false],
            );

            $childOrder = 0;

            foreach ($children as $childName => [$childIcon, $featured]) {
                Category::query()->firstOrCreate(
                    ['slug' => Str::slug($childName)],
                    [
                        'parent_id' => $parent->id,
                        'name' => $childName,
                        'icon' => $childIcon,
                        'sort_order' => ++$childOrder,
                        'is_featured' => $featured,
                    ],
                );
            }
        }

        return Category::query()->get()->keyBy('slug');
    }

    /**
     * @return Collection<string, Brand>
     */
    private function seedBrands(): Collection
    {
        foreach (self::BRANDS as $order => [$name, $website]) {
            Brand::query()->firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'website' => $website, 'sort_order' => $order + 1, 'is_featured' => $order < 12],
            );
        }

        return Brand::query()->get()->keyBy('slug');
    }

    /**
     * @param  Collection<string, Category>  $categories
     */
    private function mapCategorySpecifications(Collection $categories): void
    {
        $specificationIds = Specification::query()->pluck('id', 'slug');

        foreach (self::CATEGORY_SPECIFICATIONS as $categorySlug => $slugs) {
            $sync = [];

            foreach ($slugs as $order => $slug) {
                $sync[$specificationIds[$slug]] = ['sort_order' => $order + 1, 'is_filterable' => true];
            }

            $categories[$categorySlug]->specifications()->syncWithoutDetaching($sync);
        }
    }

    /**
     * @return array<string, Attribute>
     */
    private function seedAttributes(): array
    {
        $definitions = [
            'ram' => ['RAM', 'button', ['8GB', '12GB', '16GB', '24GB', '32GB']],
            'storage' => ['Storage', 'button', ['128GB', '256GB', '512GB', '1TB']],
            'color' => ['Color', 'swatch', ['Awesome Graphite' => '#3b3b3f', 'Awesome Lightgray' => '#d7d7d9', 'Cloud Grey' => '#9aa0a6', 'Abyss Blue' => '#2b3a55']],
        ];

        $attributes = [];

        foreach ($definitions as $slug => [$name, $display, $values]) {
            $attribute = Attribute::query()->firstOrCreate(['slug' => $slug], ['name' => $name, 'display_type' => $display]);

            $position = 0;

            foreach ($values as $key => $value) {
                [$label, $hex] = is_string($key) ? [$key, $value] : [$value, null];

                $attribute->values()->firstOrCreate(
                    ['slug' => Str::slug($label)],
                    ['value' => $label, 'color_hex' => $hex, 'sort_order' => ++$position],
                );
            }

            $attributes[$slug] = $attribute->load('values');
        }

        return $attributes;
    }

    /**
     * @param  Collection<string, Category>  $categories
     * @param  Collection<string, Brand>  $brands
     * @param  Collection<string, Specification>  $specifications
     * @param  array<string, Attribute>  $attributes
     */
    private function seedVariableProducts(
        Collection $categories,
        Collection $brands,
        Collection $specifications,
        array $attributes,
        InventoryService $inventory,
        ?User $admin,
    ): void {
        $products = [
            [
                'name' => 'Samsung Galaxy A56 5G Smartphone', 'category' => 'smart-phone', 'brand' => 'samsung',
                'sku' => 'GD-PH-A56', 'model' => 'SM-A566E', 'warranty' => '1 Year',
                'specs' => ['screen-size' => '6.7', 'resolution' => '1080x2340 (FHD+)', 'refresh-rate' => '120Hz', 'panel-type' => 'Super AMOLED', 'processor-brand' => 'Samsung Exynos', 'processor-model' => 'Exynos 1580', 'rear-camera' => '50MP + 12MP + 5MP', 'front-camera' => '12MP', 'battery-capacity' => '5000', 'fast-charging' => '45W'],
                'variants' => [
                    [['ram' => '8GB', 'storage' => '128GB', 'color' => 'Awesome Graphite'], '52999', null, 8],
                    [['ram' => '8GB', 'storage' => '256GB', 'color' => 'Awesome Graphite'], '57999', '55999', 5],
                    [['ram' => '8GB', 'storage' => '256GB', 'color' => 'Awesome Lightgray'], '57999', '55999', 3],
                    [['ram' => '12GB', 'storage' => '256GB', 'color' => 'Awesome Graphite'], '62999', null, 0],
                ],
            ],
        ];

        foreach ($products as $data) {
            $slug = Str::slug($data['name']);

            if (Product::withTrashed()->where('slug', $slug)->exists()) {
                continue;
            }

            $product = Product::query()->create([
                'type' => ProductType::Variable,
                'name' => $data['name'],
                'slug' => $slug,
                'sku' => $data['sku'],
                'model' => $data['model'],
                'brand_id' => $brands[$data['brand']]->id,
                'category_id' => $categories[$data['category']]->id,
                'short_description' => $data['name'].'. '.$data['warranty'].' official warranty.',
                'warranty' => $data['warranty'],
                'is_featured' => true,
                'is_latest' => true,
                'status' => ProductStatus::Active,
                'published_at' => now(),
                'search_keywords' => $data['model'],
                'created_by' => $admin?->id,
            ]);

            if ($parentId = $categories[$data['category']]->parent_id) {
                $product->categories()->syncWithoutDetaching([$parentId]);
            }

            foreach ($data['variants'] as $index => [$options, $price, $sale, $stock]) {
                /** @var list<AttributeValue> $values */
                $values = [];

                foreach ($options as $attributeSlug => $label) {
                    $values[] = $attributes[$attributeSlug]->values->firstWhere('slug', Str::slug($label));
                }

                $ids = array_map(fn (AttributeValue $value): int => $value->id, $values);
                sort($ids);

                /** @var ProductVariant $variant */
                $variant = $product->variants()->create([
                    'sku' => $data['sku'].'-'.($index + 1),
                    'name' => implode(' / ', array_values($options)),
                    'combination_key' => implode('-', $ids),
                    'price' => $price,
                    'sale_price' => $sale,
                    'cost_price' => bcmul($price, '0.85', 2),
                    'is_default' => $index === 0,
                    'sort_order' => $index + 1,
                ]);

                $variant->attributeValues()->sync($ids);

                if ($stock > 0) {
                    $inventory->adjust($variant, InventoryTransactionType::StockIn, $stock, 'Opening stock', performedBy: $admin);
                }
            }

            $this->setSpecifications($product, $data['specs'], $specifications);
        }
    }

    /**
     * @param  array<string, string|bool>  $values
     * @param  Collection<string, Specification>  $specifications
     */
    private function setSpecifications(Product $product, array $values, Collection $specifications): void
    {
        foreach ($values as $slug => $value) {
            $specification = $specifications[$slug];
            $columns = match ($specification->input_type) {
                SpecificationInputType::Select, SpecificationInputType::MultiSelect => [
                    'specification_option_id' => $this->optionId($specification, (string) $value),
                ],
                SpecificationInputType::Boolean => ['value' => $value ? 'Yes' : 'No'],
                SpecificationInputType::Number => ['value' => (string) $value, 'value_numeric' => (string) $value],
                SpecificationInputType::Text => ['value' => (string) $value],
            };

            $product->specificationValues()->create(['specification_id' => $specification->id] + $columns);
        }
    }

    private function optionId(Specification $specification, string $value): int
    {
        $option = $specification->options->firstWhere('slug', Str::slug($value));

        if ($option === null) {
            $option = $specification->options()->create(['value' => $value, 'slug' => Str::slug($value)]);
            $specification->options->push($option);
        }

        return $option->id;
    }

    /**
     * Puts every product with a 'flash' price into the running sale. Products already in the
     * sale are left untouched, so re-seeding only appends deals that were added to the list.
     */
    private function seedFlashSale(): void
    {
        $prices = collect(self::PRODUCTS)
            ->filter(fn (array $data): bool => isset($data['flash']))
            ->mapWithKeys(fn (array $data): array => [Str::slug($data['name']) => $data['flash']]);

        if ($prices->isEmpty()) {
            return;
        }

        $sale = FlashSale::query()->firstOrCreate(['slug' => 'weekly-flash-sale'], [
            'title' => 'Weekly Flash Sale',
            'description' => 'Limited time deals',
            'starts_at' => now()->startOfHour(),
            'ends_at' => now()->addDays(7)->startOfHour(),
            'is_active' => true,
        ]);

        $products = Product::query()->whereIn('slug', $prices->keys())->get()->keyBy('slug');
        $existing = $sale->items()->pluck('product_id')->all();
        $sortOrder = (int) $sale->items()->max('sort_order');

        foreach ($prices as $slug => $price) {
            $product = $products->get($slug);

            if ($product === null || in_array($product->id, $existing, true)) {
                continue;
            }

            $sale->items()->create([
                'product_id' => $product->id,
                'discount_type' => FlashSaleDiscountType::FixedPrice,
                'discount_value' => $price,
                'quantity_limit' => 10,
                'per_order_limit' => 2,
                'sort_order' => ++$sortOrder,
            ]);
        }
    }
}
