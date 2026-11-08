<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\ShippingMethod;
use App\Models\ShippingRate;
use App\Models\ShippingZone;
use Illuminate\Database\Seeder;

/**
 * Default zones (Inside / Outside Dhaka), a standard delivery method, starting
 * charges and Bangladesh's 64 districts. Charges are starting values only and are
 * managed from the admin panel afterwards; existing rates are never overwritten.
 */
class ShippingSeeder extends Seeder
{
    /**
     * @var array<string, list<array{0: string, 1: string}>>
     */
    private const DISTRICTS = [
        'Dhaka' => [
            ['Dhaka', 'ঢাকা'], ['Faridpur', 'ফরিদপুর'], ['Gazipur', 'গাজীপুর'], ['Gopalganj', 'গোপালগঞ্জ'],
            ['Kishoreganj', 'কিশোরগঞ্জ'], ['Madaripur', 'মাদারীপুর'], ['Manikganj', 'মানিকগঞ্জ'],
            ['Munshiganj', 'মুন্সিগঞ্জ'], ['Narayanganj', 'নারায়ণগঞ্জ'], ['Narsingdi', 'নরসিংদী'],
            ['Rajbari', 'রাজবাড়ী'], ['Shariatpur', 'শরীয়তপুর'], ['Tangail', 'টাঙ্গাইল'],
        ],
        'Chattogram' => [
            ['Bandarban', 'বান্দরবান'], ['Brahmanbaria', 'ব্রাহ্মণবাড়িয়া'], ['Chandpur', 'চাঁদপুর'],
            ['Chattogram', 'চট্টগ্রাম'], ["Cox's Bazar", 'কক্সবাজার'], ['Cumilla', 'কুমিল্লা'], ['Feni', 'ফেনী'],
            ['Khagrachhari', 'খাগড়াছড়ি'], ['Lakshmipur', 'লক্ষ্মীপুর'], ['Noakhali', 'নোয়াখালী'],
            ['Rangamati', 'রাঙ্গামাটি'],
        ],
        'Rajshahi' => [
            ['Bogura', 'বগুড়া'], ['Chapainawabganj', 'চাঁপাইনবাবগঞ্জ'], ['Joypurhat', 'জয়পুরহাট'],
            ['Naogaon', 'নওগাঁ'], ['Natore', 'নাটোর'], ['Pabna', 'পাবনা'], ['Rajshahi', 'রাজশাহী'],
            ['Sirajganj', 'সিরাজগঞ্জ'],
        ],
        'Khulna' => [
            ['Bagerhat', 'বাগেরহাট'], ['Chuadanga', 'চুয়াডাঙ্গা'], ['Jashore', 'যশোর'], ['Jhenaidah', 'ঝিনাইদহ'],
            ['Khulna', 'খুলনা'], ['Kushtia', 'কুষ্টিয়া'], ['Magura', 'মাগুরা'], ['Meherpur', 'মেহেরপুর'],
            ['Narail', 'নড়াইল'], ['Satkhira', 'সাতক্ষীরা'],
        ],
        'Barishal' => [
            ['Barguna', 'বরগুনা'], ['Barishal', 'বরিশাল'], ['Bhola', 'ভোলা'], ['Jhalokati', 'ঝালকাঠি'],
            ['Patuakhali', 'পটুয়াখালী'], ['Pirojpur', 'পিরোজপুর'],
        ],
        'Sylhet' => [
            ['Habiganj', 'হবিগঞ্জ'], ['Moulvibazar', 'মৌলভীবাজার'], ['Sunamganj', 'সুনামগঞ্জ'], ['Sylhet', 'সিলেট'],
        ],
        'Rangpur' => [
            ['Dinajpur', 'দিনাজপুর'], ['Gaibandha', 'গাইবান্ধা'], ['Kurigram', 'কুড়িগ্রাম'],
            ['Lalmonirhat', 'লালমনিরহাট'], ['Nilphamari', 'নীলফামারী'], ['Panchagarh', 'পঞ্চগড়'],
            ['Rangpur', 'রংপুর'], ['Thakurgaon', 'ঠাকুরগাঁও'],
        ],
        'Mymensingh' => [
            ['Jamalpur', 'জামালপুর'], ['Mymensingh', 'ময়মনসিংহ'], ['Netrokona', 'নেত্রকোনা'], ['Sherpur', 'শেরপুর'],
        ],
    ];

    public function run(): void
    {
        $inside = ShippingZone::query()->firstOrCreate(
            ['code' => 'inside_dhaka'],
            ['name' => 'Inside Dhaka', 'description' => 'Dhaka district', 'sort_order' => 1],
        );

        $outside = ShippingZone::query()->firstOrCreate(
            ['code' => 'outside_dhaka'],
            ['name' => 'Outside Dhaka', 'description' => 'All other districts', 'sort_order' => 2],
        );

        $standard = ShippingMethod::query()->firstOrCreate(
            ['code' => 'standard'],
            ['name' => 'Standard Delivery', 'sort_order' => 1],
        );

        ShippingRate::query()->firstOrCreate(
            ['shipping_zone_id' => $inside->id, 'shipping_method_id' => $standard->id],
            ['charge' => '60.00', 'min_delivery_days' => 1, 'max_delivery_days' => 2],
        );

        ShippingRate::query()->firstOrCreate(
            ['shipping_zone_id' => $outside->id, 'shipping_method_id' => $standard->id],
            ['charge' => '120.00', 'min_delivery_days' => 2, 'max_delivery_days' => 5],
        );

        foreach (self::DISTRICTS as $division => $districts) {
            foreach ($districts as [$name, $nameBn]) {
                District::query()->firstOrCreate(
                    ['name' => $name],
                    [
                        'name_bn' => $nameBn,
                        'division' => $division,
                        'shipping_zone_id' => $name === 'Dhaka' ? $inside->id : $outside->id,
                    ],
                );
            }
        }
    }
}
