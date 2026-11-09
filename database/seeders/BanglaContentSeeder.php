<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\Banner;
use App\Models\Category;
use App\Models\FlashSale;
use App\Models\HomeSection;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Offer;
use App\Models\Page;
use App\Models\Product;
use App\Models\ServiceHighlight;
use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use App\Models\Specification;
use App\Models\SpecificationGroup;
use App\Models\SpecificationOption;
use App\Services\CatalogCache;
use App\Services\SettingsService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Throwable;

/**
 * Bangla text for the catalog and CMS content the store launched with, matched
 * by slug, code or English wording. Only empty "_bn" columns are filled, so
 * translations typed in the admin are never overwritten and the seeder can be
 * run again safely: php artisan db:seed --class=BanglaContentSeeder
 */
class BanglaContentSeeder extends Seeder
{
    /**
     * @var array<string, string>
     */
    private const CATEGORIES = [
        'smart-watch' => 'স্মার্ট ওয়াচ',
        'headphones' => 'হেডফোন',
        'earbuds' => 'ইয়ারবাডস',
        'over-ear-headphones' => 'ওভার-ইয়ার হেডফোন',
        'speaker' => 'স্পিকার',
        'powerbanks' => 'পাওয়ার ব্যাংক',
        'charger-cable' => 'চার্জার ও ক্যাবল',
        'smart-phone' => 'স্মার্টফোন',
    ];

    /**
     * @var array<string, string>
     */
    private const SPECIFICATION_GROUPS = [
        'display' => 'ডিসপ্লে',
        'processor' => 'প্রসেসর',
        'memory' => 'মেমোরি',
        'storage' => 'স্টোরেজ',
        'graphics' => 'গ্রাফিক্স',
        'connectivity' => 'কানেক্টিভিটি',
        'audio' => 'অডিও',
        'battery' => 'ব্যাটারি',
        'camera' => 'ক্যামেরা',
        'physical' => 'বাহ্যিক বৈশিষ্ট্য',
        'power' => 'পাওয়ার',
        'warranty' => 'ওয়ারেন্টি',
    ];

    /**
     * @var array<string, string>
     */
    private const SPECIFICATIONS = [
        'screen-size' => 'স্ক্রিন সাইজ',
        'resolution' => 'রেজোলিউশন',
        'refresh-rate' => 'রিফ্রেশ রেট',
        'panel-type' => 'প্যানেলের ধরন',
        'curved-display' => 'কার্ভড ডিসপ্লে',
        'processor-brand' => 'প্রসেসর ব্র্যান্ড',
        'processor-model' => 'প্রসেসর মডেল',
        'generation' => 'জেনারেশন',
        'cores' => 'কোর',
        'threads' => 'থ্রেড',
        'ram' => 'র‍্যাম',
        'ram-type' => 'র‍্যামের ধরন',
        'storage-capacity' => 'স্টোরেজ ক্ষমতা',
        'storage-type' => 'স্টোরেজের ধরন',
        'graphics-chipset' => 'গ্রাফিক্স চিপসেট',
        'video-memory' => 'ভিডিও মেমোরি',
        'video-memory-type' => 'ভিডিও মেমোরির ধরন',
        'memory-clock' => 'মেমোরি ক্লক',
        'memory-bus' => 'মেমোরি বাস',
        'cuda-cores' => 'CUDA কোর',
        'bus-interface' => 'বাস ইন্টারফেস',
        'directx' => 'DirectX',
        'opengl' => 'OpenGL',
        'max-resolution' => 'সর্বোচ্চ রেজোলিউশন',
        'multi-display' => 'মাল্টি ডিসপ্লে',
        'wi-fi' => 'ওয়াই-ফাই',
        'bluetooth' => 'ব্লুটুথ',
        'ports' => 'পোর্ট',
        'connection-type' => 'সংযোগের ধরন',
        'driver-size' => 'ড্রাইভার সাইজ',
        'surround-sound' => 'সারাউন্ড সাউন্ড',
        'output-power' => 'আউটপুট পাওয়ার',
        'battery-capacity' => 'ব্যাটারি ক্ষমতা',
        'fast-charging' => 'ফাস্ট চার্জিং',
        'rear-camera' => 'পেছনের ক্যামেরা',
        'front-camera' => 'সামনের ক্যামেরা',
        'color' => 'রং',
        'dimensions' => 'মাপ',
        'weight' => 'ওজন',
        'form-factor' => 'ফর্ম ফ্যাক্টর',
        'capacity' => 'ক্ষমতা',
        'cooling-capacity' => 'কুলিং ক্ষমতা',
        'inverter' => 'ইনভার্টার',
        'power-consumption' => 'বিদ্যুৎ খরচ',
        'recommended-psu' => 'প্রস্তাবিত PSU',
        'power-connector' => 'পাওয়ার কানেক্টর',
        'warranty-details' => 'ওয়ারেন্টির বিবরণ',
    ];

    /**
     * Option slug => Bangla value; technical values (60Hz, IPS, DDR5) stay as they are.
     *
     * @var array<string, string>
     */
    private const SPECIFICATION_OPTIONS = [
        'wired' => 'তারযুক্ত',
        'wireless' => 'ওয়্যারলেস',
        'wired-wireless' => 'তারযুক্ত ও ওয়্যারলেস',
        'tower' => 'টাওয়ার',
        'mini-pc' => 'মিনি পিসি',
        '1-ton' => '১ টন',
        '15-ton' => '১.৫ টন',
        '2-ton' => '২ টন',
    ];

    /**
     * @var array<string, string>
     */
    private const ATTRIBUTES = [
        'ram' => 'র‍্যাম',
        'storage' => 'স্টোরেজ',
        'color' => 'রং',
    ];

    /**
     * Product slug => Bangla name. Brand and model names stay in English so they still match what customers search for.
     *
     * @var array<string, string>
     */
    private const PRODUCTS = [
        'redragon-h320-lamia-rgb-virtual-71-surround-sound-gaming-headset' => 'Redragon H320 Lamia RGB ভার্চুয়াল 7.1 সারাউন্ড সাউন্ড গেমিং হেডসেট',
        'redragon-h130-pelias-wired-gaming-headphone' => 'Redragon H130 Pelias তারযুক্ত গেমিং হেডফোন',
        'redragon-h231-scream-wired-rgb-gaming-headphone-white' => 'Redragon H231 Scream তারযুক্ত RGB গেমিং হেডফোন (সাদা)',
        'jbl-partybox-720-800w-bluetooth-party-speaker' => 'JBL PartyBox 720 800W ব্লুটুথ পার্টি স্পিকার',
        'kospet-tank-t3-ultra-2-143-inch-amoled-gps-bt-calling-smartwatch' => 'KOSPET TANK T3 ULTRA 2 1.43 ইঞ্চি AMOLED GPS ব্লুটুথ কলিং স্মার্টওয়াচ',
        'apple-iphone-17-pro-256gb-cosmic-orange' => 'Apple iPhone 17 Pro 256GB কসমিক অরেঞ্জ',
        'samsung-galaxy-s26-ultra-12gb256gb-cobalt-violet' => 'Samsung Galaxy S26 Ultra 12GB/256GB কোবাল্ট ভায়োলেট',
        'apple-iphone-17-256gb-lavender' => 'Apple iPhone 17 256GB ল্যাভেন্ডার',
        'google-pixel-9-pro-16gb256gb-obsidian' => 'Google Pixel 9 Pro 16GB/256GB অবসিডিয়ান',
        'honor-x7d-8gb256gb-smartphone' => 'HONOR X7d 8GB/256GB স্মার্টফোন',
        'apple-watch-series-11-gps-46mm-aluminum-case-with-sport-band' => 'Apple Watch Series 11 GPS 46mm অ্যালুমিনিয়াম কেস ও স্পোর্ট ব্যান্ড',
        'amazfit-active-2-square-amoled-gps-smart-watch' => 'Amazfit Active 2 Square AMOLED GPS স্মার্ট ওয়াচ',
        'cmf-by-nothing-watch-pro-2-amoled-bluetooth-calling-smart-watch' => 'CMF by Nothing Watch Pro 2 AMOLED ব্লুটুথ কলিং স্মার্ট ওয়াচ',
        'haylou-solar-pro-amoled-bluetooth-calling-smart-watch' => 'Haylou Solar Pro AMOLED ব্লুটুথ কলিং স্মার্ট ওয়াচ',
        'kieslect-ks3-amoled-bluetooth-calling-smart-watch' => 'Kieslect KS3 AMOLED ব্লুটুথ কলিং স্মার্ট ওয়াচ',
        'apple-airpods-4-wireless-earbuds-with-usb-c-charging-case' => 'Apple AirPods 4 ওয়্যারলেস ইয়ারবাডস, USB-C চার্জিং কেসসহ',
        'soundpeats-life-anc-wireless-earbuds' => 'SoundPEATS Life ANC ওয়্যারলেস ইয়ারবাডস',
        'xiaomi-redmi-buds-6-anc-tws-earbuds' => 'Xiaomi Redmi Buds 6 ANC TWS ইয়ারবাডস',
        'anker-soundcore-r50i-nc-wireless-earbuds' => 'Anker Soundcore R50i NC ওয়্যারলেস ইয়ারবাডস',
        'qcy-ailybuds-pro-anc-wireless-earbuds' => 'QCY AilyBuds Pro+ ANC ওয়্যারলেস ইয়ারবাডস',
        'compact-ivory-10000mah-20w-fast-charging-power-bank' => 'কমপ্যাক্ট আইভরি 10000mAh 20W ফাস্ট চার্জিং পাওয়ার ব্যাংক',
        'premium-brushed-silver-20000mah-65w-pd-power-bank' => 'প্রিমিয়াম ব্রাশড সিলভার 20000mAh 65W PD পাওয়ার ব্যাংক',
        'premium-graphite-30000mah-140w-laptop-power-bank' => 'প্রিমিয়াম গ্রাফাইট 30000mAh 140W ল্যাপটপ পাওয়ার ব্যাংক',
        'premium-forest-green-20000mah-30w-power-bank' => 'প্রিমিয়াম ফরেস্ট গ্রিন 20000mAh 30W পাওয়ার ব্যাংক',
        'warm-white-5000mah-magnetic-wireless-power-bank' => 'ওয়ার্ম হোয়াইট 5000mAh ম্যাগনেটিক ওয়্যারলেস পাওয়ার ব্যাংক',
        'slim-matte-charcoal-10000mah-20w-power-bank' => 'স্লিম ম্যাট চারকোল 10000mAh 20W পাওয়ার ব্যাংক',
        'deep-navy-20000mah-225w-fast-charging-power-bank' => 'ডিপ নেভি 20000mAh 22.5W ফাস্ট চার্জিং পাওয়ার ব্যাংক',
        'premium-black-40000mah-100w-high-capacity-power-bank' => 'প্রিমিয়াম ব্ল্যাক 40000mAh 100W হাই-ক্যাপাসিটি পাওয়ার ব্যাংক',
        'premium-space-gray-25000mah-100w-laptop-power-bank' => 'প্রিমিয়াম স্পেস গ্রে 25000mAh 100W ল্যাপটপ পাওয়ার ব্যাংক',
        'lavender-10000mah-magnetic-wireless-power-bank' => 'ল্যাভেন্ডার 10000mAh ম্যাগনেটিক ওয়্যারলেস পাওয়ার ব্যাংক',
        'braided-usb-c-to-usb-c-60w-fast-charging-cable-1m' => 'ব্রেইডেড USB-C টু USB-C 60W ফাস্ট চার্জিং ক্যাবল 1m',
        'brushed-silver-usb-c-to-usb-a-30-otg-adapter' => 'ব্রাশড সিলভার USB-C টু USB-A 3.0 OTG অ্যাডাপ্টার',
        'white-usb-a-to-usb-c-3a-fast-charging-cable-1m' => 'সাদা USB-A টু USB-C 3A ফাস্ট চার্জিং ক্যাবল 1m',
        'compact-black-38w-dual-port-car-charger' => 'কমপ্যাক্ট কালো 38W ডুয়াল-পোর্ট কার চার্জার',
        'matte-black-usb-a-to-micro-usb-24a-charging-cable-1m' => 'ম্যাট কালো USB-A টু Micro-USB 2.4A চার্জিং ক্যাবল 1m',
        'matte-charcoal-35w-dual-port-wall-charger' => 'ম্যাট চারকোল 35W ডুয়াল-পোর্ট ওয়াল চার্জার',
        'satin-white-20w-usb-c-pd-wall-charger' => 'স্যাটিন হোয়াইট 20W USB-C PD ওয়াল চার্জার',
        'slim-15w-magnetic-wireless-charging-pad' => 'স্লিম 15W ম্যাগনেটিক ওয়্যারলেস চার্জিং প্যাড',
        'space-gray-5-in-1-usb-c-hub-with-4k-hdmi' => 'স্পেস গ্রে 5-ইন-1 USB-C হাব, 4K HDMIসহ',
        'white-usb-a-to-lightning-24a-charging-cable-1m' => 'সাদা USB-A টু Lightning 2.4A চার্জিং ক্যাবল 1m',
        'iphone-17-essentials-combo-iphone-17-airpods-4-20w-charger' => 'iPhone 17 এসেনশিয়ালস কম্বো: iPhone 17 + AirPods 4 + 20W চার্জার',
        'galaxy-power-combo-galaxy-s26-ultra-20000mah-65w-power-bank-usb-c-cable' => 'গ্যালাক্সি পাওয়ার কম্বো: Galaxy S26 Ultra + 20000mAh 65W পাওয়ার ব্যাংক + USB-C ক্যাবল',
        'travel-charging-combo-10000mah-power-bank-35w-wall-charger-car-charger-cable' => 'ট্রাভেল চার্জিং কম্বো: 10000mAh পাওয়ার ব্যাংক + 35W ওয়াল চার্জার + কার চার্জার + ক্যাবল',
        'fitness-combo-amazfit-active-2-smart-watch-soundpeats-life-anc-earbuds' => 'ফিটনেস কম্বো: Amazfit Active 2 স্মার্ট ওয়াচ + SoundPEATS Life ANC ইয়ারবাডস',
        'desk-setup-combo-5-in-1-usb-c-hub-wireless-charging-pad-braided-cable' => 'ডেস্ক সেটআপ কম্বো: 5-ইন-1 USB-C হাব + ওয়্যারলেস চার্জিং প্যাড + ব্রেইডেড ক্যাবল',
        'samsung-galaxy-a56-5g-smartphone' => 'Samsung Galaxy A56 5G স্মার্টফোন',
        'charcoal-10w-portable-bluetooth-speaker' => 'চারকোল 10W পোর্টেবল ব্লুটুথ স্পিকার',
        'elegant-cream-retro-15w-bluetooth-speaker' => 'এলিগ্যান্ট ক্রিম রেট্রো 15W ব্লুটুথ স্পিকার',
        'rugged-navy-20w-waterproof-portable-bluetooth-speaker' => 'রাগেড নেভি 20W ওয়াটারপ্রুফ পোর্টেবল ব্লুটুথ স্পিকার',
        'slim-black-40w-bluetooth-soundbar' => 'স্লিম কালো 40W ব্লুটুথ সাউন্ডবার',
        'premium-silver-gray-20w-smart-speaker-with-voice-assistant' => 'প্রিমিয়াম সিলভার-গ্রে 20W স্মার্ট স্পিকার, ভয়েস অ্যাসিস্ট্যান্টসহ',
        'matte-black-25w-rgb-gaming-bluetooth-speaker' => 'ম্যাট কালো 25W RGB গেমিং ব্লুটুথ স্পিকার',
        'portable-80w-party-speaker-with-blue-led-rings' => 'পোর্টেবল 80W পার্টি স্পিকার, নীল LED রিংসহ',
        'premium-olive-40w-portable-bluetooth-boombox-speaker' => 'প্রিমিয়াম অলিভ 40W পোর্টেবল ব্লুটুথ বুমবক্স স্পিকার',
        'walnut-50w-bluetooth-bookshelf-speaker-with-copper-woofer' => 'ওয়ালনাট 50W ব্লুটুথ বুকশেলফ স্পিকার, কপার উফারসহ',
    ];

    /**
     * English warranty => [Bangla warranty, Bangla "... of warranty" form for sentences].
     *
     * @var array<string, array{0: string, 1: string}>
     */
    private const WARRANTIES = [
        '3 Months' => ['৩ মাস', '৩ মাসের'],
        '6 Months' => ['৬ মাস', '৬ মাসের'],
        '1 Year' => ['১ বছর', '১ বছরের'],
        '18 Months' => ['১৮ মাস', '১৮ মাসের'],
        '2 Years' => ['২ বছর', '২ বছরের'],
    ];

    /**
     * Short storefront texts (banner, section and highlight titles, menu labels), matched by their English wording.
     *
     * @var array<string, string>
     */
    private const PHRASES = [
        // Homepage sections
        'Featured Categories' => 'জনপ্রিয় ক্যাটাগরি',
        'Explore our wide range of gadgets' => 'আমাদের বিশাল গ্যাজেট সংগ্রহ দেখুন',
        'Explore our wide range of tech products' => 'আমাদের বিশাল টেক পণ্যের সংগ্রহ দেখুন',
        'Flash Sale' => 'ফ্ল্যাশ সেল',
        'Limited time deals' => 'সীমিত সময়ের অফার',
        'Trending Now' => 'এখন ট্রেন্ডিং',
        'The gadgets everyone is talking about this week' => 'এই সপ্তাহে সবাই যে গ্যাজেটগুলো নিয়ে কথা বলছে',
        'Mobile Phones' => 'মোবাইল ফোন',
        'Latest flagship and everyday smartphones from top brands' => 'সেরা ব্র্যান্ডের নতুন ফ্ল্যাগশিপ ও দৈনন্দিন স্মার্টফোন',
        'Smart Watches' => 'স্মার্ট ওয়াচ',
        'Track your fitness and stay connected on the go' => 'ফিটনেস ট্র্যাক করুন, চলার পথেও থাকুন কানেক্টেড',
        'Earbuds' => 'ইয়ারবাডস',
        'True wireless sound for music, calls and gaming' => 'গান, কল ও গেমিংয়ের জন্য ট্রু ওয়্যারলেস সাউন্ড',
        'Stay charged on the go with power banks and chargers from ৳199' => '৳১৯৯ থেকে পাওয়ার ব্যাংক ও চার্জার, চলার পথেও থাকুন ফুল চার্জে',
        'Combo Deals' => 'কম্বো অফার',
        'Bundle up and save more on gadgets that go together' => 'একসাথে মানানসই গ্যাজেট বান্ডেলে কিনে আরও সাশ্রয় করুন',
        'Featured Products' => 'বিশেষ পণ্য',
        'Discover our handpicked selection of premium tech products' => 'বাছাই করা প্রিমিয়াম টেক পণ্যগুলো দেখুন',
        'New and Noteworthy' => 'নতুন ও উল্লেখযোগ্য',
        'Explore the latest finds from innovative tech and trending product releases.' => 'উদ্ভাবনী প্রযুক্তি আর ট্রেন্ডিং নতুন পণ্যের সর্বশেষ সংগ্রহ দেখুন।',
        'Latest Products' => 'নতুন পণ্য',
        'Check out our newest arrivals' => 'আমাদের একদম নতুন পণ্যগুলো দেখুন',
        'Best Sellers' => 'সর্বাধিক বিক্রিত',
        'Top-rated products loved by our customers' => 'গ্রাহকদের প্রিয় সেরা রেটিংয়ের পণ্য',
        'All Product' => 'সব পণ্য',
        'All Products' => 'সব পণ্য',
        'Browse our complete collection' => 'আমাদের পুরো সংগ্রহ দেখুন',
        'Shop all' => 'সব দেখুন',
        'Shop now' => 'এখনই কিনুন',

        // Banners
        'Smartphone mega sale — save up to 25%' => 'স্মার্টফোন মেগা সেল — ২৫% পর্যন্ত সাশ্রয়',
        'Smart living essentials' => 'স্মার্ট জীবনের প্রয়োজনীয় সব',
        'Power bank offers: power for every day' => 'পাওয়ার ব্যাংক অফার: প্রতিদিনের পাওয়ার',
        'Combo deals: your everyday tech in one bundle' => 'কম্বো অফার: প্রতিদিনের টেক এক বান্ডেলে',
        'Message us for product, stock, price and delivery info' => 'পণ্য, স্টক, দাম ও ডেলিভারির তথ্যের জন্য আমাদের মেসেজ করুন',

        // Service highlights
        '0% EMI' => '০% ইএমআই',
        '24/7 Online Support' => '২৪/৭ অনলাইন সাপোর্ট',
        'No charge on card payment' => 'কার্ড পেমেন্টে কোনো চার্জ নেই',
        'Cash on delivery in 64 districts' => '৬৪ জেলায় ক্যাশ অন ডেলিভারি',

        // Footer menus and pages
        'Information' => 'তথ্য',
        'Policies' => 'নীতিমালা',
        'About Us' => 'আমাদের সম্পর্কে',
        'Affiliation' => 'অ্যাফিলিয়েশন',
        'Service Centers' => 'সার্ভিস সেন্টার',
        'EMI Information' => 'ইএমআই তথ্য',
        'Privacy Policy' => 'গোপনীয়তা নীতি',
        'Warranty Policy' => 'ওয়ারেন্টি নীতি',
        'Payment Terms' => 'পেমেন্টের শর্তাবলি',
        'Delivery Policy' => 'ডেলিভারি নীতি',
        'Terms & Conditions' => 'শর্তাবলি',
        'Refund and Return Policy' => 'রিফান্ড ও রিটার্ন নীতি',

        // Flash sales
        'Weekly Flash Sale' => 'সাপ্তাহিক ফ্ল্যাশ সেল',
    ];

    /**
     * Offer slug => [title, summary, description].
     *
     * @var array<string, array{0: string, 1: string, 2: string}>
     */
    private const OFFERS = [
        'mobile-deal' => ['মোবাইল ডিল', 'মোবাইল কিনুন, পান আকর্ষণীয় ছাড় ও ফ্রি ডেলিভারি!', "মোবাইল কিনুন, পান আকর্ষণীয় ছাড় ও ফ্রি ডেলিভারি!\n\nস্টক থাকা পর্যন্ত অফার প্রযোজ্য। শর্তাবলি প্রযোজ্য।"],
        'smartwatch-earbuds-deal' => ['স্মার্টওয়াচ ও ইয়ারবাডস ডিল', 'বিশেষ দামে স্মার্টওয়াচ ও ইয়ারবাডস লুফে নিন!', "বিশেষ দামে স্মার্টওয়াচ ও ইয়ারবাডস লুফে নিন!\n\nস্টক থাকা পর্যন্ত অফার প্রযোজ্য। শর্তাবলি প্রযোজ্য।"],
        'power-bank-charger-deal' => ['পাওয়ার ব্যাংক ও চার্জার ডিল', 'কম খরচে থাকুন ফুল চার্জে: পাওয়ার ব্যাংক, চার্জার ও ক্যাবলে সাশ্রয়!', "কম খরচে থাকুন ফুল চার্জে: পাওয়ার ব্যাংক, চার্জার ও ক্যাবলে সাশ্রয়!\n\nস্টক থাকা পর্যন্ত অফার প্রযোজ্য। শর্তাবলি প্রযোজ্য।"],
        'speaker-headphone-deal' => ['স্পিকার ও হেডফোন ডিল', 'স্পিকার ও হেডফোন কিনুন, পান আকর্ষণীয় ছাড়!', "স্পিকার ও হেডফোন কিনুন, পান আকর্ষণীয় ছাড়!\n\nস্টক থাকা পর্যন্ত অফার প্রযোজ্য। শর্তাবলি প্রযোজ্য।"],
    ];

    /**
     * Shipping method / zone code => [name, description, delivery estimate].
     *
     * @var array<string, array{0: string, 1: string, 2: string}>
     */
    private const SHIPPING = [
        'inside_dhaka' => ['ঢাকার ভিতরে', 'শহরের ভিতরে আমাদের কুরিয়ার পার্টনার পৌঁছে দেবে', '১-২ কর্মদিবস'],
        'outside_dhaka' => ['ঢাকার বাইরে', 'বাংলাদেশের যেকোনো জায়গায় দেশব্যাপী ডেলিভারি', '২-৫ কর্মদিবস'],
    ];

    /**
     * Demo contact address shipped with the seeders => Bangla.
     *
     * @var array<string, string>
     */
    private const SETTINGS = [
        'Head Office: Your address here, Dhaka' => 'প্রধান কার্যালয়: আপনার ঠিকানা এখানে, ঢাকা',
    ];

    public function run(CatalogCache $cache, SettingsService $settings): void
    {
        $this->translateCatalog();
        $this->translateContent();
        $this->translateSettings($settings);

        $settings->flush();
        $cache->flush();
        $this->reindexProducts();
    }

    private function translateCatalog(): void
    {
        foreach (Category::query()->whereIn('slug', array_keys(self::CATEGORIES))->get() as $category) {
            $this->fillEmpty($category, ['name_bn' => self::CATEGORIES[$category->slug]]);
        }

        foreach (SpecificationGroup::query()->whereIn('slug', array_keys(self::SPECIFICATION_GROUPS))->get() as $group) {
            $this->fillEmpty($group, ['name_bn' => self::SPECIFICATION_GROUPS[$group->slug]]);
        }

        foreach (Specification::query()->whereIn('slug', array_keys(self::SPECIFICATIONS))->get() as $specification) {
            $this->fillEmpty($specification, ['name_bn' => self::SPECIFICATIONS[$specification->slug]]);
        }

        foreach (SpecificationOption::query()->whereIn('slug', array_keys(self::SPECIFICATION_OPTIONS))->get() as $option) {
            $this->fillEmpty($option, ['value_bn' => self::SPECIFICATION_OPTIONS[$option->slug]]);
        }

        foreach (Attribute::query()->whereIn('slug', array_keys(self::ATTRIBUTES))->get() as $attribute) {
            $this->fillEmpty($attribute, ['name_bn' => self::ATTRIBUTES[$attribute->slug]]);
        }

        foreach (Product::query()->withTrashed()->whereIn('slug', array_keys(self::PRODUCTS))->get() as $product) {
            $name = self::PRODUCTS[$product->slug];
            [$warranty, $warrantyPhrase] = self::WARRANTIES[$product->warranty] ?? [null, null];

            $this->fillEmpty($product, [
                'name_bn' => $name,
                'warranty_bn' => $warranty,
                // The launch catalog's summaries all read "{name}. {warranty} official warranty."
                'short_description_bn' => $warrantyPhrase !== null && $product->short_description === "{$product->name}. {$product->warranty} official warranty."
                    ? "{$name}। {$warrantyPhrase} অফিসিয়াল ওয়ারেন্টি।"
                    : null,
            ]);
        }
    }

    private function translateContent(): void
    {
        foreach (HomeSection::query()->get() as $section) {
            $this->fillEmpty($section, [
                'title_bn' => $this->phrase($section->title),
                'subtitle_bn' => $this->phrase($section->subtitle),
            ]);

            $ctaLabel = $this->phrase($section->setting('cta_label'));

            if ($ctaLabel !== null && blank($section->setting('cta_label_bn'))) {
                $section->forceFill(['settings' => [...($section->settings ?? []), 'cta_label_bn' => $ctaLabel]])->saveQuietly();
            }
        }

        foreach (Banner::query()->get() as $banner) {
            $this->fillEmpty($banner, [
                'title_bn' => $this->phrase($banner->title),
                'subtitle_bn' => $this->phrase($banner->subtitle),
                'cta_label_bn' => $this->phrase($banner->cta_label),
                'alt_text_bn' => $this->phrase($banner->alt_text),
            ]);
        }

        foreach (ServiceHighlight::query()->get() as $highlight) {
            $this->fillEmpty($highlight, [
                'title_bn' => $this->phrase($highlight->title),
                'subtitle_bn' => $this->phrase($highlight->subtitle),
            ]);
        }

        foreach (Menu::query()->get() as $menu) {
            $this->fillEmpty($menu, ['name_bn' => $this->phrase($menu->name)]);
        }

        foreach (MenuItem::query()->get() as $item) {
            $this->fillEmpty($item, ['label_bn' => $this->phrase($item->label)]);
        }

        foreach (Page::query()->get() as $page) {
            $title = $this->phrase($page->title);

            $this->fillEmpty($page, [
                'title_bn' => $title,
                // Demo placeholder text written by DemoContentSeeder.
                'content_bn' => $title !== null && str_starts_with((string) $page->content, "<p>This is the {$page->title} page.")
                    ? "<p>এটি {$title} পেজ। অ্যাডমিন প্যানেলের Content → Pages থেকে এই লেখাটি বদলে দিন।</p>"
                    : null,
            ]);
        }

        foreach (Offer::query()->whereIn('slug', array_keys(self::OFFERS))->get() as $offer) {
            [$title, $summary, $description] = self::OFFERS[$offer->slug];

            $this->fillEmpty($offer, ['title_bn' => $title, 'summary_bn' => $summary, 'description_bn' => $description]);
        }

        foreach (FlashSale::query()->get() as $flashSale) {
            $this->fillEmpty($flashSale, [
                'title_bn' => $this->phrase($flashSale->title),
                'description_bn' => $this->phrase($flashSale->description),
            ]);
        }

        foreach (ShippingMethod::query()->whereIn('code', array_keys(self::SHIPPING))->get() as $method) {
            [$name, $description, $estimate] = self::SHIPPING[$method->code];

            $this->fillEmpty($method, ['name_bn' => $name, 'description_bn' => $description, 'delivery_estimate_bn' => $estimate]);
        }

        foreach (ShippingZone::query()->whereIn('code', array_keys(self::SHIPPING))->get() as $zone) {
            $this->fillEmpty($zone, ['name_bn' => self::SHIPPING[$zone->code][0]]);
        }
    }

    private function translateSettings(SettingsService $settings): void
    {
        $address = $settings->get('contact.address');

        if (isset(self::SETTINGS[$address]) && blank($settings->get('contact.address_bn'))) {
            $settings->set('contact.address_bn', self::SETTINGS[$address]);
        }
    }

    /**
     * Sets only the Bangla columns that are still empty, without firing observers
     * (the caches are flushed once at the end).
     *
     * @param  array<string, ?string>  $values
     */
    private function fillEmpty(Model $model, array $values): void
    {
        $missing = array_filter(
            $values,
            fn (?string $value, string $column): bool => filled($value) && blank($model->getAttribute($column)),
            ARRAY_FILTER_USE_BOTH,
        );

        if ($missing !== []) {
            $model->forceFill($missing)->saveQuietly();
        }
    }

    private function phrase(mixed $english): ?string
    {
        return is_string($english) ? (self::PHRASES[trim($english)] ?? null) : null;
    }

    /**
     * Puts the Bangla names into the search index; skipped with a warning when the search engine is not reachable.
     */
    private function reindexProducts(): void
    {
        try {
            Product::query()->with(['brand:id,name', 'category:id,name'])->whereIn('slug', array_keys(self::PRODUCTS))->searchable();
        } catch (Throwable $exception) {
            $this->command?->warn('Search index not updated ('.$exception->getMessage().'). Run: php artisan scout:import "App\Models\Product"');
        }
    }
}
