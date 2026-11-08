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
        'Desktop' => ['desktop', ['Gaming PC' => ['gaming-pc', false], 'Brand PC' => ['desktop', false], 'Mini PC' => ['mini-pc', false], 'All-in-One PC' => ['all-in-one', false]], true],
        'Laptop' => ['laptop', ['Gaming Laptop' => ['laptop', false], 'Ultrabook' => ['laptop', false], 'AI Laptop' => ['ai-laptop', true]], true],
        'Component' => ['cpu', ['Processor' => ['processor', true], 'Motherboard' => ['motherboard', true], 'Graphics Card' => ['graphics-card', true], 'RAM' => ['ram', true], 'SSD' => ['ssd', true], 'Power Supply' => ['power-supply', false], 'Casing' => ['casing', false]]],
        'Monitor' => ['monitor', [], true],
        'Power' => ['power', ['UPS' => ['ups', false], 'Power Station' => ['power-station', false]]],
        'Phone' => ['smartphone', ['Smartphone' => ['smartphone', true], 'Feature Phone' => ['feature-phone', false]]],
        'Tablet' => ['tablet', []],
        'Office Equipment' => ['printer', ['Printer' => ['printer', true], 'Projector' => ['projector', true], 'Scanner' => ['scanner', false]]],
        'Camera' => ['camera', []],
        'Security' => ['cctv', ['CC Camera' => ['cctv', false]]],
        'Networking' => ['router', ['Router' => ['router', true], 'Switch' => ['switch', false]]],
        'Software' => ['software', []],
        'Server & Storage' => ['server', []],
        'Accessories' => ['headphone', ['Headphone' => ['headphone', false], 'Keyboard' => ['keyboard', false], 'Mouse' => ['mouse', false], 'Speaker' => ['speaker', false]]],
        'Gadget' => ['smartwatch', ['Smart Watch' => ['smartwatch', false], 'Earbuds' => ['earbuds', false]]],
        'Gaming' => ['gamepad', ['Gaming Console' => ['gamepad', false], 'Gaming Chair' => ['chair', false]]],
        'TV' => ['television', ['Television' => ['television', true]]],
        'Appliance' => ['appliance', ['Air Conditioner' => ['air-conditioner', true], 'Air Fryer' => ['air-fryer', true], 'Refrigerator' => ['refrigerator', false]]],
    ];

    /**
     * Category slug => specification slugs (in display order; select/number ones become filters).
     *
     * @var array<string, list<string>>
     */
    private const CATEGORY_SPECIFICATIONS = [
        'monitor' => ['screen-size', 'resolution', 'refresh-rate', 'panel-type', 'curved-display', 'ports', 'warranty-details'],
        'laptop' => ['processor-brand', 'processor-model', 'generation', 'ram', 'ram-type', 'storage-capacity', 'storage-type', 'screen-size', 'resolution', 'refresh-rate', 'graphics-chipset', 'wi-fi', 'bluetooth', 'weight', 'warranty-details'],
        'desktop' => ['processor-brand', 'processor-model', 'ram', 'storage-capacity', 'storage-type', 'graphics-chipset', 'video-memory', 'form-factor', 'wi-fi', 'warranty-details'],
        'processor' => ['processor-brand', 'processor-model', 'generation', 'cores', 'threads', 'warranty-details'],
        'graphics-card' => ['graphics-chipset', 'video-memory', 'video-memory-type', 'memory-clock', 'memory-bus', 'cuda-cores', 'bus-interface', 'directx', 'opengl', 'max-resolution', 'multi-display', 'ports', 'power-consumption', 'recommended-psu', 'power-connector', 'dimensions', 'warranty-details'],
        'casing' => ['form-factor', 'color', 'warranty-details'],
        'smartphone' => ['screen-size', 'resolution', 'refresh-rate', 'panel-type', 'processor-brand', 'processor-model', 'ram', 'storage-capacity', 'rear-camera', 'front-camera', 'battery-capacity', 'fast-charging', 'warranty-details'],
        'headphone' => ['connection-type', 'driver-size', 'surround-sound', 'color', 'warranty-details'],
        'speaker' => ['connection-type', 'output-power', 'bluetooth', 'battery-capacity', 'warranty-details'],
        'smart-watch' => ['screen-size', 'panel-type', 'bluetooth', 'battery-capacity', 'warranty-details'],
        'earbuds' => ['connection-type', 'driver-size', 'bluetooth', 'battery-capacity', 'color', 'warranty-details'],
        'television' => ['screen-size', 'resolution', 'refresh-rate', 'panel-type', 'ports', 'warranty-details'],
        'air-conditioner' => ['cooling-capacity', 'inverter', 'warranty-details'],
        'power-station' => ['capacity', 'output-power', 'ports', 'warranty-details'],
    ];

    /**
     * @var list<array{0: string, 1: ?string}>
     */
    private const BRANDS = [
        ['Redragon', 'https://www.redragonzone.com'], ['Lenovo', 'https://www.lenovo.com'], ['PNY', 'https://www.pny.com'],
        ['LG', 'https://www.lg.com'], ['ASUS', 'https://www.asus.com'], ['JBL', 'https://www.jbl.com'],
        ['ARZOPA', null], ['KOSPET', null], ['MSI', 'https://www.msi.com'], ['AMD', 'https://www.amd.com'],
        ['Unika', null], ['Maxcool', null], ['Samsung', 'https://www.samsung.com'], ['Haier', 'https://www.haier.com'],
        ['Intel', 'https://www.intel.com'], ['Gigabyte', 'https://www.gigabyte.com'], ['Zotac', 'https://www.zotac.com'],
        ['TP-Link', 'https://www.tp-link.com'], ['Anker', 'https://www.anker.com'], ['Xiaomi', 'https://www.mi.com'],
        ['Dahua', 'https://www.dahuasecurity.com'], ['TCL', 'https://www.tcl.com'],
        ['Apple', 'https://www.apple.com'], ['Google', 'https://store.google.com'], ['HONOR', 'https://www.honor.com'],
        ['Amazfit', 'https://www.amazfit.com'], ['CMF by Nothing', 'https://nothing.tech'], ['Haylou', null],
        ['Kieslect', null], ['SoundPEATS', null], ['QCY', null],
    ];

    /**
     * name, category slug, brand, regular price, sale price, stock, model, warranty, flags, specs.
     * Prices are sample values for development.
     *
     * @var list<array<string, mixed>>
     */
    private const PRODUCTS = [
        // Flash sale items (sale price is applied by the flash sale, not the variant).
        ['name' => 'Redragon H320 Lamia RGB Virtual 7.1 Surround Sound Gaming Headset', 'category' => 'headphone', 'brand' => 'redragon', 'price' => '3600', 'stock' => 25, 'model' => 'H320', 'warranty' => '1 Year', 'flash' => '2949',
            'specs' => ['connection-type' => 'Wired', 'driver-size' => '53', 'surround-sound' => 'Virtual 7.1', 'color' => 'Black']],
        ['name' => 'Redragon H130 Pelias Wired Gaming Headphone', 'category' => 'headphone', 'brand' => 'redragon', 'price' => '1450', 'stock' => 40, 'model' => 'H130', 'warranty' => '6 Months', 'flash' => '999',
            'specs' => ['connection-type' => 'Wired', 'driver-size' => '50', 'color' => 'Black']],
        ['name' => 'Redragon H231 Scream Wired RGB Gaming Headphone (White)', 'category' => 'headphone', 'brand' => 'redragon', 'price' => '2150', 'stock' => 18, 'model' => 'H231', 'warranty' => '1 Year', 'flash' => '1620',
            'specs' => ['connection-type' => 'Wired', 'driver-size' => '50', 'color' => 'White']],
        ['name' => 'Lenovo G24-20 24 Inch FHD 144Hz IPS Gaming Monitor', 'category' => 'monitor', 'brand' => 'lenovo', 'price' => '27500', 'stock' => 12, 'model' => 'G24-20', 'warranty' => '3 Years', 'flash' => '17500',
            'specs' => ['screen-size' => '23.8', 'resolution' => '1920x1080 (FHD)', 'refresh-rate' => '144Hz', 'panel-type' => 'IPS', 'curved-display' => false, 'ports' => 'HDMI 1.4, DisplayPort 1.2']],
        ['name' => 'Lenovo L24-4e 23.8 Inch 100Hz Full HD IPS Monitor (Black)', 'category' => 'monitor', 'brand' => 'lenovo', 'price' => '15500', 'stock' => 20, 'model' => 'L24-4e', 'warranty' => '3 Years', 'flash' => '12500',
            'specs' => ['screen-size' => '23.8', 'resolution' => '1920x1080 (FHD)', 'refresh-rate' => '100Hz', 'panel-type' => 'IPS', 'curved-display' => false]],
        ['name' => 'Lenovo L24i-40 23.8 Inch IPS FHD 100Hz Professional Monitor', 'category' => 'monitor', 'brand' => 'lenovo', 'price' => '20900', 'stock' => 9, 'model' => 'L24i-40', 'warranty' => '3 Years', 'flash' => '15500',
            'specs' => ['screen-size' => '23.8', 'resolution' => '1920x1080 (FHD)', 'refresh-rate' => '100Hz', 'panel-type' => 'IPS', 'curved-display' => false]],

        // Latest products.
        ['name' => 'PNY GeForce RTX 5060 Ti 8GB EPIC-X RGB OC Graphics Card', 'category' => 'graphics-card', 'brand' => 'pny', 'price' => '75000', 'sale' => '73000', 'stock' => 6, 'model' => 'VCG5060T8TFXXPB1-O', 'warranty' => '3 Years', 'latest' => true,
            'specs' => ['graphics-chipset' => 'NVIDIA GeForce RTX 5060 Ti', 'video-memory' => '8', 'video-memory-type' => 'GDDR7', 'ports' => '3x DisplayPort 2.1b, 1x HDMI 2.1b']],
        ['name' => 'LG 34U640B-B 34-inch WQHD UltraWide Curved Monitor', 'category' => 'monitor', 'brand' => 'lg', 'price' => '75000', 'sale' => '71000', 'stock' => 4, 'model' => '34U640B-B', 'warranty' => '3 Years', 'latest' => true,
            'specs' => ['screen-size' => '34', 'resolution' => '3440x1440 (WQHD)', 'refresh-rate' => '100Hz', 'panel-type' => 'VA', 'curved-display' => true]],
        ['name' => 'ASUS NUC 15 Pro Core 5 210H Wi-Fi 7 BE202 Mini PC', 'category' => 'mini-pc', 'brand' => 'asus', 'price' => '70000', 'sale' => '62000', 'stock' => 5, 'model' => 'NUC 15 Pro', 'warranty' => '3 Years', 'latest' => true,
            'specs' => ['processor-brand' => 'Intel', 'processor-model' => 'Core 5 210H', 'form-factor' => 'Mini PC', 'wi-fi' => 'Wi-Fi 7 (BE202)']],
        ['name' => 'JBL PartyBox 720 800W Bluetooth Party Speaker', 'category' => 'speaker', 'brand' => 'jbl', 'price' => '118800', 'sale' => '102500', 'stock' => 3, 'model' => 'PartyBox 720', 'warranty' => '1 Year', 'latest' => true,
            'specs' => ['connection-type' => 'Wired & Wireless', 'output-power' => '800', 'bluetooth' => '5.4']],
        ['name' => 'ARZOPA Z1FC G1 Game Max 16.1-inch 144Hz Portable Gaming Monitor', 'category' => 'monitor', 'brand' => 'arzopa', 'price' => '18500', 'sale' => '17500', 'stock' => 7, 'model' => 'Z1FC', 'warranty' => '1 Year', 'latest' => true,
            'specs' => ['screen-size' => '16.1', 'resolution' => '1920x1080 (FHD)', 'refresh-rate' => '144Hz', 'panel-type' => 'IPS', 'curved-display' => false]],
        ['name' => 'KOSPET TANK T3 ULTRA 2 1.43 Inch AMOLED GPS BT Calling Smartwatch', 'category' => 'smart-watch', 'brand' => 'kospet', 'price' => '10499', 'sale' => '9790', 'stock' => 15, 'model' => 'TANK T3 ULTRA 2', 'warranty' => '1 Year', 'latest' => true,
            'specs' => ['screen-size' => '1.43', 'panel-type' => 'AMOLED', 'bluetooth' => '5.3', 'battery-capacity' => '480']],

        // Best sellers.
        ['name' => 'AMD Ryzen 7 7700 MSI PRO B650M-P 16GB RAM 500GB SSD RTX 5070 Gaming PC', 'category' => 'gaming-pc', 'brand' => 'amd', 'price' => '245000', 'sale' => '237000', 'stock' => 2, 'model' => 'GD-R7-7700-5070', 'warranty' => '3 Years', 'best' => true,
            'specs' => ['processor-brand' => 'AMD', 'processor-model' => 'Ryzen 7 7700', 'ram' => '16', 'storage-capacity' => '512GB', 'storage-type' => 'NVMe SSD', 'graphics-chipset' => 'NVIDIA GeForce RTX 5070', 'video-memory' => '12', 'form-factor' => 'Micro ATX']],
        ['name' => 'Unika Radeon RX 580 BLIZZARDS 8GD5 V2 Graphics Card', 'category' => 'graphics-card', 'brand' => 'unika', 'price' => '19500', 'sale' => '17800', 'stock' => 11, 'model' => 'RX 580 BLIZZARDS 8GD5 V2', 'warranty' => '2 Years', 'best' => true,
            'specs' => ['graphics-chipset' => 'AMD Radeon RX 580', 'video-memory' => '8', 'video-memory-type' => 'GDDR5']],
        ['name' => 'MSI PRO MP243L E14 23.8" 144Hz IPS FHD Gaming Monitor', 'category' => 'monitor', 'brand' => 'msi', 'price' => '14500', 'sale' => '12400', 'stock' => 22, 'model' => 'PRO MP243L E14', 'warranty' => '3 Years', 'best' => true,
            'specs' => ['screen-size' => '23.8', 'resolution' => '1920x1080 (FHD)', 'refresh-rate' => '144Hz', 'panel-type' => 'IPS', 'curved-display' => false]],
        ['name' => 'PNY GeForce RTX 5060 8GB OC Dual Fan Graphics Card', 'category' => 'graphics-card', 'brand' => 'pny', 'price' => '61000', 'sale' => '56000', 'stock' => 8, 'model' => 'VCG50608DFXPB1-O', 'warranty' => '3 Years', 'best' => true,
            'specs' => ['graphics-chipset' => 'NVIDIA GeForce RTX 5060', 'video-memory' => '8', 'video-memory-type' => 'GDDR7']],
        ['name' => 'MSI PRO MP251 E14L 24.5 inch FHD 144Hz IPS Gaming Monitor', 'category' => 'monitor', 'brand' => 'msi', 'price' => '20000', 'sale' => '12800', 'stock' => 14, 'model' => 'PRO MP251 E14L', 'warranty' => '3 Years', 'best' => true,
            'specs' => ['screen-size' => '24.5', 'resolution' => '1920x1080 (FHD)', 'refresh-rate' => '144Hz', 'panel-type' => 'IPS', 'curved-display' => false]],
        ['name' => 'Maxcool Inferno MC5 Micro ATX Gaming Computer Case', 'category' => 'casing', 'brand' => 'maxcool', 'price' => '3800', 'sale' => '2800', 'stock' => 30, 'model' => 'Inferno MC5', 'warranty' => '1 Year', 'best' => true,
            'specs' => ['form-factor' => 'Micro ATX', 'color' => 'Black']],

        // Other categories.
        ['name' => 'AMD Ryzen 7 7700 Processor', 'category' => 'processor', 'brand' => 'amd', 'price' => '34500', 'stock' => 10, 'model' => '100-100000592BOX', 'warranty' => '3 Years', 'featured' => true,
            'specs' => ['processor-brand' => 'AMD', 'processor-model' => 'Ryzen 7 7700', 'generation' => 'Ryzen 7000', 'cores' => '8', 'threads' => '16']],
        ['name' => 'Haier HSU-19GravityInv Pro X6 1.5 Ton Inverter Air Conditioner', 'category' => 'air-conditioner', 'brand' => 'haier', 'price' => '78000', 'sale' => '72900', 'stock' => 6, 'model' => 'HSU-19GravityInv Pro X6', 'warranty' => '10 Years Compressor', 'featured' => true,
            'specs' => ['cooling-capacity' => '1.5 Ton', 'inverter' => true]],
        ['name' => 'Samsung 55U8510 55 Inch Crystal UHD 4K Smart TV', 'category' => 'television', 'brand' => 'samsung', 'price' => '92000', 'sale' => '86500', 'stock' => 4, 'model' => 'UA55U8510', 'warranty' => '2 Years',
            'specs' => ['screen-size' => '55', 'resolution' => '3840x2160 (4K UHD)', 'refresh-rate' => '60Hz']],
        ['name' => 'Anker SOLIX C1000 Gen 2 Portable Power Station', 'category' => 'power-station', 'brand' => 'anker', 'price' => '115000', 'stock' => 0, 'model' => 'SOLIX C1000 Gen 2', 'warranty' => '5 Years',
            'specs' => ['capacity' => '1024Wh', 'output-power' => '2000']],
        ['name' => 'Gigabyte GeForce RTX 5070 Ti WINDFORCE OC SFF 16G Graphics Card', 'category' => 'graphics-card', 'brand' => 'gigabyte', 'price' => '124000', 'sale' => '118000', 'stock' => 5, 'model' => 'GV-N507TWF3OC-16GD', 'warranty' => '3 Years', 'latest' => true,
            'specs' => [
                'graphics-chipset' => 'NVIDIA GeForce RTX 5070 Ti', 'video-memory' => '16', 'video-memory-type' => 'GDDR7',
                'memory-clock' => '28 Gbps', 'memory-bus' => '256', 'cuda-cores' => '8960', 'bus-interface' => 'PCI-E 5.0',
                'directx' => 'DirectX 12 Ultimate', 'opengl' => 'OpenGL 4.6', 'max-resolution' => '7680x4320', 'multi-display' => '4',
                'ports' => '3x DisplayPort 2.1b, 1x HDMI 2.1b', 'power-consumption' => '300', 'recommended-psu' => '750',
                'power-connector' => '1x 16-pin', 'dimensions' => '304 x 126 x 50 mm', 'warranty-details' => '3 Years',
            ],
            'description' => '<h2>Built for 4K gaming and AI workloads</h2>'
                .'<p>The GeForce RTX 5070 Ti WINDFORCE OC SFF pairs NVIDIA\'s Blackwell architecture with 16GB of GDDR7 memory running at 28 Gbps, in a small-form-factor friendly design.</p>'
                .'<h3>Features</h3>'
                .'<ul><li><strong>8960 CUDA cores</strong> for high-speed rendering and AI processing</li>'
                .'<li><strong>16GB GDDR7</strong> memory with a 256-bit bus</li>'
                .'<li><strong>PCI-E 5.0</strong> interface</li>'
                .'<li><strong>8K output</strong> over DisplayPort 2.1b and HDMI 2.1b</li>'
                .'<li><strong>WINDFORCE cooling</strong> for quiet, stable performance</li></ul>'
                .'<p>A 750W power supply with a 16-pin connector is recommended.</p>'],

        // Remaining homepage cards (names match DemoStorefront so every card links to a real product page).
        ['name' => 'Lenovo L24i-4A 23.8 Inch FHD IPS 100Hz Monitor', 'category' => 'monitor', 'brand' => 'lenovo', 'price' => '16500', 'stock' => 10, 'model' => 'L24i-4A', 'warranty' => '3 Years', 'flash' => '14200',
            'specs' => ['screen-size' => '23.8', 'resolution' => '1920x1080 (FHD)', 'refresh-rate' => '100Hz', 'panel-type' => 'IPS', 'curved-display' => false]],
        ['name' => 'Samsung 50U8000F 50 Inch Crystal UHD 4K Smart TV', 'category' => 'television', 'brand' => 'samsung', 'price' => '64000', 'stock' => 5, 'model' => 'UA50U8000F', 'warranty' => '2 Years', 'flash' => '58900',
            'specs' => ['screen-size' => '50', 'resolution' => '3840x2160 (4K UHD)', 'refresh-rate' => '60Hz']],
        ['name' => 'TCL 32S5K 32 Inch HD Android Smart TV', 'category' => 'television', 'brand' => 'tcl', 'price' => '21500', 'stock' => 8, 'model' => '32S5K', 'warranty' => '2 Years', 'flash' => '18900',
            'specs' => ['screen-size' => '32', 'resolution' => '1366x768 (HD)', 'refresh-rate' => '60Hz']],
        ['name' => 'PNY GeForce RTX 5080 16GB GDDR7 ARGB EPIC-X RGB OC Graphics Card', 'category' => 'graphics-card', 'brand' => 'pny', 'price' => '175000', 'sale' => '168000', 'stock' => 3, 'model' => 'VCG508016TFXXPB1-O', 'warranty' => '3 Years', 'latest' => true,
            'specs' => ['graphics-chipset' => 'NVIDIA GeForce RTX 5080', 'video-memory' => '16', 'video-memory-type' => 'GDDR7']],
        ['name' => 'ZOTAC GAMING GeForce RTX 5080 AMP Extreme INFINITY Graphics Card', 'category' => 'graphics-card', 'brand' => 'zotac', 'price' => '180000', 'sale' => '172000', 'stock' => 2, 'model' => 'ZT-B50800B-10P', 'warranty' => '3 Years', 'latest' => true,
            'specs' => ['graphics-chipset' => 'NVIDIA GeForce RTX 5080', 'video-memory' => '16', 'video-memory-type' => 'GDDR7']],
        ['name' => 'Haier HSU-19GRAVITY INV PRO X6 1.5 Ton Inverter AC', 'category' => 'air-conditioner', 'brand' => 'haier', 'price' => '74000', 'sale' => '68500', 'stock' => 5, 'model' => 'HSU-19GRAVITY INV PRO X6', 'warranty' => '10 Years Compressor', 'latest' => true,
            'specs' => ['cooling-capacity' => '1.5 Ton', 'inverter' => true]],
        ['name' => 'AMD Ryzen 7 9700X Gaming PC', 'category' => 'gaming-pc', 'brand' => 'amd', 'price' => '206000', 'sale' => '198000', 'stock' => 3, 'model' => 'GD-R7-9700X', 'warranty' => '3 Years', 'best' => true,
            'specs' => ['processor-brand' => 'AMD', 'processor-model' => 'Ryzen 7 9700X', 'ram' => '32', 'storage-capacity' => '1TB', 'storage-type' => 'NVMe SSD', 'form-factor' => 'Micro ATX']],
        ['name' => 'ASUS TUF Gaming VG249QM5F 23.8 Inch FHD 210Hz Monitor', 'category' => 'monitor', 'brand' => 'asus', 'price' => '23500', 'sale' => '21500', 'stock' => 9, 'model' => 'VG249QM5F', 'warranty' => '3 Years', 'best' => true,
            'specs' => ['screen-size' => '23.8', 'resolution' => '1920x1080 (FHD)', 'refresh-rate' => '210Hz', 'panel-type' => 'IPS', 'curved-display' => false]],
        ['name' => 'ASUS TUF Gaming VG27AQL5F 27 Inch QHD 210Hz Monitor', 'category' => 'monitor', 'brand' => 'asus', 'price' => '39000', 'sale' => '36500', 'stock' => 6, 'model' => 'VG27AQL5F', 'warranty' => '3 Years', 'best' => true,
            'specs' => ['screen-size' => '27', 'resolution' => '2560x1440 (QHD)', 'refresh-rate' => '210Hz', 'panel-type' => 'IPS', 'curved-display' => false]],
        ['name' => 'ASUS VA249QGS-W 23.8 Inch FHD 144Hz IPS Monitor', 'category' => 'monitor', 'brand' => 'asus', 'price' => '16800', 'sale' => '15200', 'stock' => 12, 'model' => 'VA249QGS-W', 'warranty' => '3 Years', 'best' => true,
            'specs' => ['screen-size' => '23.8', 'resolution' => '1920x1080 (FHD)', 'refresh-rate' => '144Hz', 'panel-type' => 'IPS', 'curved-display' => false]],
        ['name' => 'Dahua DHI-LM27-B221W 27 Inch FHD Monitor', 'category' => 'monitor', 'brand' => 'dahua', 'price' => '15500', 'sale' => '13900', 'stock' => 10, 'model' => 'DHI-LM27-B221W', 'warranty' => '3 Years', 'best' => true,
            'specs' => ['screen-size' => '27', 'resolution' => '1920x1080 (FHD)', 'curved-display' => false]],
        ['name' => 'MSI PRO MP225 E12VL 21.45 Inch FHD 120Hz Monitor', 'category' => 'monitor', 'brand' => 'msi', 'price' => '11000', 'sale' => '9800', 'stock' => 15, 'model' => 'PRO MP225 E12VL', 'warranty' => '3 Years', 'best' => true,
            'specs' => ['screen-size' => '21.45', 'resolution' => '1920x1080 (FHD)', 'refresh-rate' => '120Hz', 'curved-display' => false]],
        ['name' => 'PNY GeForce RTX 5070 Ti OC 16GB Graphics Card', 'category' => 'graphics-card', 'brand' => 'pny', 'price' => '122000', 'sale' => '116000', 'stock' => 4, 'model' => 'VCG5070T16TFXPB1-O', 'warranty' => '3 Years', 'best' => true,
            'specs' => ['graphics-chipset' => 'NVIDIA GeForce RTX 5070 Ti', 'video-memory' => '16', 'video-memory-type' => 'GDDR7']],
        ['name' => 'PNY GeForce RTX 5080 OC Triple Fan 16GB Graphics Card', 'category' => 'graphics-card', 'brand' => 'pny', 'price' => '170000', 'sale' => '162000', 'stock' => 3, 'model' => 'VCG508016TFXPB1-O', 'warranty' => '3 Years', 'best' => true,
            'specs' => ['graphics-chipset' => 'NVIDIA GeForce RTX 5080', 'video-memory' => '16', 'video-memory-type' => 'GDDR7']],
        ['name' => 'ZOTAC GAMING GeForce RTX 5080 SOLID CORE OC Graphics Card', 'category' => 'graphics-card', 'brand' => 'zotac', 'price' => '166000', 'sale' => '158000', 'stock' => 3, 'model' => 'ZT-B50800J-10P', 'warranty' => '3 Years', 'best' => true,
            'specs' => ['graphics-chipset' => 'NVIDIA GeForce RTX 5080', 'video-memory' => '16', 'video-memory-type' => 'GDDR7']],
        ['name' => 'ZOTAC GeForce RTX 5070 Twin Edge OC Graphics Card', 'category' => 'graphics-card', 'brand' => 'zotac', 'price' => '86500', 'sale' => '82000', 'stock' => 5, 'model' => 'ZT-B50700H-10M', 'warranty' => '3 Years', 'best' => true,
            'specs' => ['graphics-chipset' => 'NVIDIA GeForce RTX 5070', 'video-memory' => '12', 'video-memory-type' => 'GDDR7']],
        ['name' => 'Gigabyte AORUS PRIME 5 AMD Ryzen 7 9700X AMD B850 32GB RAM 1TB SSD RTX 5070 Ti Gaming PC', 'category' => 'brand-pc', 'brand' => 'gigabyte', 'price' => '365000', 'sale' => '345000', 'stock' => 2, 'model' => 'AORUS PRIME 5', 'warranty' => '3 Years', 'featured' => true,
            'specs' => ['processor-brand' => 'AMD', 'processor-model' => 'Ryzen 7 9700X', 'ram' => '32', 'storage-capacity' => '1TB', 'storage-type' => 'NVMe SSD', 'graphics-chipset' => 'NVIDIA GeForce RTX 5070 Ti', 'video-memory' => '16']],
        ['name' => 'PNY GeForce RTX 5080 OC 16GB GDDR7 Triple Fan Graphics Card', 'category' => 'graphics-card', 'brand' => 'pny', 'price' => '199000', 'sale' => '191000', 'stock' => 3, 'model' => 'VCG508016TFXPB1-O-T', 'warranty' => '3 Years', 'featured' => true,
            'specs' => ['graphics-chipset' => 'NVIDIA GeForce RTX 5080', 'video-memory' => '16', 'video-memory-type' => 'GDDR7']],
        ['name' => 'AMD Ryzen 7 7700 MSI PRO B650M-P 16GB RAM 500GB SSD RTX 5070 Gaming PC Build', 'category' => 'gaming-pc', 'brand' => 'amd', 'price' => '245000', 'sale' => '237000', 'stock' => 2, 'model' => 'GD-R7-7700-5070-B', 'warranty' => '3 Years', 'featured' => true,
            'specs' => ['processor-brand' => 'AMD', 'processor-model' => 'Ryzen 7 7700', 'ram' => '16', 'storage-capacity' => '512GB', 'storage-type' => 'NVMe SSD', 'graphics-chipset' => 'NVIDIA GeForce RTX 5070', 'video-memory' => '12', 'form-factor' => 'Micro ATX']],
        ['name' => 'Gigabyte GeForce RTX 5070 Ti WINDFORCE OC SFF 16G GDDR7 Graphics Card', 'category' => 'graphics-card', 'brand' => 'gigabyte', 'price' => '199000', 'sale' => '188000', 'stock' => 3, 'model' => 'GV-N507TWF3OC-16GD-G7', 'warranty' => '3 Years', 'featured' => true,
            'specs' => ['graphics-chipset' => 'NVIDIA GeForce RTX 5070 Ti', 'video-memory' => '16', 'video-memory-type' => 'GDDR7']],
        ['name' => 'PNY GeForce RTX 5070 Ti OC 16GB Triple Fan Graphics Card', 'category' => 'graphics-card', 'brand' => 'pny', 'price' => '190000', 'sale' => '167999', 'stock' => 4, 'model' => 'VCG5070T16TFXPB1-O-T', 'warranty' => '3 Years', 'featured' => true,
            'specs' => ['graphics-chipset' => 'NVIDIA GeForce RTX 5070 Ti', 'video-memory' => '16', 'video-memory-type' => 'GDDR7']],
        ['name' => 'PNY GeForce RTX 5080 16GB GDDR7 ARGB EPIC-X RGB OC Triple Fan Graphics Card', 'category' => 'graphics-card', 'brand' => 'pny', 'price' => '225000', 'sale' => '222000', 'stock' => 2, 'model' => 'VCG508016TFXXPB1-O-T', 'warranty' => '3 Years', 'featured' => true,
            'specs' => ['graphics-chipset' => 'NVIDIA GeForce RTX 5080', 'video-memory' => '16', 'video-memory-type' => 'GDDR7']],

        // Homepage category carousels: mobile phones, smart watches and earbuds.
        ['name' => 'Apple iPhone 17 Pro 256GB Cosmic Orange', 'category' => 'smartphone', 'brand' => 'apple', 'price' => '175000', 'sale' => '165000', 'stock' => 4, 'model' => 'iPhone 17 Pro', 'warranty' => '1 Year',
            'specs' => ['screen-size' => '6.3', 'refresh-rate' => '120Hz', 'panel-type' => 'OLED', 'processor-brand' => 'Apple', 'processor-model' => 'A19 Pro', 'ram' => '12', 'storage-capacity' => '256GB', 'rear-camera' => '48MP + 48MP + 48MP', 'front-camera' => '18MP']],
        ['name' => 'Samsung Galaxy S26 Ultra 12GB/256GB Cobalt Violet', 'category' => 'smartphone', 'brand' => 'samsung', 'price' => '169999', 'sale' => '158000', 'stock' => 5, 'model' => 'Galaxy S26 Ultra', 'warranty' => '1 Year',
            'specs' => ['screen-size' => '6.9', 'refresh-rate' => '120Hz', 'panel-type' => 'AMOLED', 'processor-brand' => 'Qualcomm', 'ram' => '12', 'storage-capacity' => '256GB', 'battery-capacity' => '5000']],
        ['name' => 'Apple iPhone 17 256GB Lavender', 'category' => 'smartphone', 'brand' => 'apple', 'price' => '125000', 'sale' => '118000', 'stock' => 6, 'model' => 'iPhone 17', 'warranty' => '1 Year',
            'specs' => ['screen-size' => '6.3', 'refresh-rate' => '120Hz', 'panel-type' => 'OLED', 'processor-brand' => 'Apple', 'processor-model' => 'A19', 'ram' => '8', 'storage-capacity' => '256GB', 'rear-camera' => '48MP + 48MP', 'front-camera' => '18MP']],
        ['name' => 'Google Pixel 9 Pro 16GB/256GB Obsidian', 'category' => 'smartphone', 'brand' => 'google', 'price' => '112000', 'sale' => '98500', 'stock' => 3, 'model' => 'Pixel 9 Pro', 'warranty' => '1 Year',
            'specs' => ['screen-size' => '6.3', 'refresh-rate' => '120Hz', 'panel-type' => 'OLED', 'processor-model' => 'Google Tensor G4', 'ram' => '16', 'storage-capacity' => '256GB']],
        ['name' => 'HONOR X7d 8GB/256GB Smartphone', 'category' => 'smartphone', 'brand' => 'honor', 'price' => '26999', 'sale' => '23999', 'stock' => 12, 'model' => 'X7d', 'warranty' => '1 Year',
            'specs' => ['ram' => '8', 'storage-capacity' => '256GB']],
        ['name' => 'Apple Watch Series 11 GPS 46mm Aluminum Case with Sport Band', 'category' => 'smart-watch', 'brand' => 'apple', 'price' => '56000', 'sale' => '52500', 'stock' => 4, 'model' => 'Watch Series 11', 'warranty' => '1 Year',
            'specs' => ['panel-type' => 'OLED']],
        ['name' => 'Amazfit Active 2 Square AMOLED GPS Smart Watch', 'category' => 'smart-watch', 'brand' => 'amazfit', 'price' => '19900', 'sale' => '17500', 'stock' => 7, 'model' => 'Active 2 Square', 'warranty' => '1 Year',
            'specs' => ['panel-type' => 'AMOLED']],
        ['name' => 'CMF by Nothing Watch Pro 2 AMOLED Bluetooth Calling Smart Watch', 'category' => 'smart-watch', 'brand' => 'cmf-by-nothing', 'price' => '8999', 'sale' => '7490', 'stock' => 10, 'model' => 'Watch Pro 2', 'warranty' => '1 Year',
            'specs' => ['panel-type' => 'AMOLED']],
        ['name' => 'Haylou Solar Pro AMOLED Bluetooth Calling Smart Watch', 'category' => 'smart-watch', 'brand' => 'haylou', 'price' => '6500', 'sale' => '5490', 'stock' => 14, 'model' => 'Solar Pro', 'warranty' => '1 Year',
            'specs' => ['panel-type' => 'AMOLED']],
        ['name' => 'Kieslect KS3 AMOLED Bluetooth Calling Smart Watch', 'category' => 'smart-watch', 'brand' => 'kieslect', 'price' => '5990', 'sale' => '4790', 'stock' => 16, 'model' => 'KS3', 'warranty' => '1 Year',
            'specs' => ['panel-type' => 'AMOLED']],
        ['name' => 'Apple AirPods 4 Wireless Earbuds with USB-C Charging Case', 'category' => 'earbuds', 'brand' => 'apple', 'price' => '18500', 'sale' => '16500', 'stock' => 8, 'model' => 'AirPods 4', 'warranty' => '1 Year',
            'specs' => ['connection-type' => 'Wireless', 'color' => 'White']],
        ['name' => 'SoundPEATS Life ANC Wireless Earbuds', 'category' => 'earbuds', 'brand' => 'soundpeats', 'price' => '4290', 'sale' => '3490', 'stock' => 20, 'model' => 'Life ANC', 'warranty' => '6 Months',
            'specs' => ['connection-type' => 'Wireless', 'color' => 'Black']],
        ['name' => 'Xiaomi Redmi Buds 6 ANC TWS Earbuds', 'category' => 'earbuds', 'brand' => 'xiaomi', 'price' => '3990', 'sale' => '3490', 'stock' => 18, 'model' => 'Redmi Buds 6', 'warranty' => '6 Months',
            'specs' => ['connection-type' => 'Wireless', 'color' => 'Black']],
        ['name' => 'Anker Soundcore R50i NC Wireless Earbuds', 'category' => 'earbuds', 'brand' => 'anker', 'price' => '3990', 'sale' => '3290', 'stock' => 22, 'model' => 'Soundcore R50i NC', 'warranty' => '18 Months',
            'specs' => ['connection-type' => 'Wireless', 'color' => 'Black']],
        ['name' => 'QCY AilyBuds Pro+ ANC Wireless Earbuds', 'category' => 'earbuds', 'brand' => 'qcy', 'price' => '3200', 'sale' => '2690', 'stock' => 25, 'model' => 'AilyBuds Pro+', 'warranty' => '6 Months',
            'specs' => ['connection-type' => 'Wireless', 'color' => 'Blue']],
    ];

    public function run(InventoryService $inventory): void
    {
        $admin = User::query()->where('email', env('ADMIN_EMAIL', 'admin@example.com'))->first();

        $categories = $this->seedCategories();
        $brands = $this->seedBrands();
        $this->mapCategorySpecifications($categories);
        $specifications = Specification::query()->with('options')->get()->keyBy('slug');
        $attributes = $this->seedAttributes();

        $flashItems = [];

        foreach (self::PRODUCTS as $position => $data) {
            $slug = Str::slug($data['name']);

            if (Product::withTrashed()->where('slug', $slug)->exists()) {
                continue;
            }

            $product = Product::query()->create([
                'type' => ProductType::Simple,
                'name' => $data['name'],
                'slug' => $slug,
                'sku' => 'GD-'.str_pad((string) ($position + 1001), 5, '0', STR_PAD_LEFT),
                'model' => $data['model'],
                'brand_id' => $brands[$data['brand']]->id,
                'category_id' => $categories[$data['category']]->id,
                'short_description' => $data['name'].'. '.$data['warranty'].' official warranty.',
                'description' => $data['description'] ?? null,
                'warranty' => $data['warranty'],
                'is_featured' => $data['featured'] ?? false,
                'is_latest' => $data['latest'] ?? false,
                'is_best_seller' => $data['best'] ?? false,
                'status' => ProductStatus::Active,
                'published_at' => now()->subDays(count(self::PRODUCTS) - $position),
                'search_keywords' => $data['model'],
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

            if (isset($data['flash'])) {
                $flashItems[] = [$product, $data['flash']];
            }
        }

        $this->seedVariableProducts($categories, $brands, $specifications, $attributes, $inventory, $admin);
        $this->seedFlashSale($flashItems);
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
                'name' => 'Samsung Galaxy A56 5G Smartphone', 'category' => 'smartphone', 'brand' => 'samsung',
                'sku' => 'GD-PH-A56', 'model' => 'SM-A566E', 'warranty' => '1 Year',
                'specs' => ['screen-size' => '6.7', 'resolution' => '1080x2340 (FHD+)', 'refresh-rate' => '120Hz', 'panel-type' => 'Super AMOLED', 'processor-brand' => 'Samsung Exynos', 'processor-model' => 'Exynos 1580', 'rear-camera' => '50MP + 12MP + 5MP', 'front-camera' => '12MP', 'battery-capacity' => '5000', 'fast-charging' => '45W'],
                'variants' => [
                    [['ram' => '8GB', 'storage' => '128GB', 'color' => 'Awesome Graphite'], '52999', null, 8],
                    [['ram' => '8GB', 'storage' => '256GB', 'color' => 'Awesome Graphite'], '57999', '55999', 5],
                    [['ram' => '8GB', 'storage' => '256GB', 'color' => 'Awesome Lightgray'], '57999', '55999', 3],
                    [['ram' => '12GB', 'storage' => '256GB', 'color' => 'Awesome Graphite'], '62999', null, 0],
                ],
            ],
            [
                'name' => 'Lenovo IdeaPad Slim 3 15IRH10 Core i5 13th Gen 15.3" Laptop', 'category' => 'laptop', 'brand' => 'lenovo',
                'sku' => 'GD-LP-IPS3', 'model' => '15IRH10', 'warranty' => '2 Years',
                'specs' => ['processor-brand' => 'Intel', 'processor-model' => 'Core i5-13420H', 'generation' => '13th Gen', 'ram-type' => 'DDR5', 'storage-type' => 'NVMe SSD', 'screen-size' => '15.3', 'resolution' => '1920x1080 (FHD)', 'refresh-rate' => '60Hz', 'graphics-chipset' => 'Intel UHD Graphics', 'wi-fi' => 'Wi-Fi 6', 'bluetooth' => '5.2', 'weight' => '1.59 kg'],
                'variants' => [
                    [['ram' => '16GB', 'storage' => '512GB', 'color' => 'Cloud Grey'], '78500', '74900', 6],
                    [['ram' => '16GB', 'storage' => '1TB', 'color' => 'Cloud Grey'], '84500', null, 4],
                    [['ram' => '24GB', 'storage' => '1TB', 'color' => 'Abyss Blue'], '92000', null, 2],
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
     * @param  list<array{0: Product, 1: string}>  $items
     */
    private function seedFlashSale(array $items): void
    {
        if ($items === []) {
            return;
        }

        $sale = FlashSale::query()->firstOrCreate(['slug' => 'weekly-flash-sale'], [
            'title' => 'Weekly Flash Sale',
            'description' => 'Limited time deals',
            'starts_at' => now()->startOfHour(),
            'ends_at' => now()->addDays(7)->startOfHour(),
            'is_active' => true,
        ]);

        // Only newly created products reach here, so re-seeding just appends them to the existing sale.
        $offset = $sale->items()->count();

        foreach ($items as $position => [$product, $price]) {
            $sale->items()->create([
                'product_id' => $product->id,
                'discount_type' => FlashSaleDiscountType::FixedPrice,
                'discount_value' => $price,
                'quantity_limit' => 10,
                'per_order_limit' => 2,
                'sort_order' => $offset + $position + 1,
            ]);
        }
    }
}
