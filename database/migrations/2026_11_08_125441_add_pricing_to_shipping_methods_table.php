<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Shipping methods carry their own charge so the admin can offer any option
     * ("Inside Dhaka", "Outside Dhaka", "Express", ...) and the customer picks one
     * at checkout. Existing zone + method rates become methods of their own.
     */
    public function up(): void
    {
        Schema::table('shipping_methods', function (Blueprint $table) {
            $table->decimal('charge', 12, 2)->default(0)->after('description');
            $table->decimal('free_shipping_threshold', 12, 2)->nullable()->after('charge');
            $table->string('delivery_estimate', 100)->nullable()->after('free_shipping_threshold');
        });

        $rates = DB::table('shipping_rates')
            ->join('shipping_zones', 'shipping_zones.id', '=', 'shipping_rates.shipping_zone_id')
            ->join('shipping_methods', 'shipping_methods.id', '=', 'shipping_rates.shipping_method_id')
            ->where('shipping_rates.is_active', true)
            ->where('shipping_zones.is_active', true)
            ->where('shipping_methods.is_active', true)
            ->orderBy('shipping_zones.sort_order')
            ->orderBy('shipping_methods.sort_order')
            ->get([
                'shipping_rates.shipping_method_id', 'shipping_rates.charge', 'shipping_rates.free_shipping_threshold',
                'shipping_rates.min_delivery_days', 'shipping_rates.max_delivery_days',
                'shipping_zones.name as zone_name', 'shipping_zones.code as zone_code',
                'shipping_methods.name as method_name', 'shipping_methods.code as method_code',
            ]);

        $hasSeveralMethods = $rates->pluck('shipping_method_id')->unique()->count() > 1;

        foreach ($rates->values() as $position => $rate) {
            DB::table('shipping_methods')->updateOrInsert(
                ['code' => $hasSeveralMethods ? "{$rate->zone_code}_{$rate->method_code}" : $rate->zone_code],
                [
                    'name' => $hasSeveralMethods ? "{$rate->zone_name} ({$rate->method_name})" : $rate->zone_name,
                    'charge' => $rate->charge,
                    'free_shipping_threshold' => $rate->free_shipping_threshold,
                    'delivery_estimate' => $rate->min_delivery_days && $rate->max_delivery_days
                        ? "{$rate->min_delivery_days}-{$rate->max_delivery_days} working days"
                        : null,
                    'is_active' => true,
                    'sort_order' => $position,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }

        DB::table('shipping_methods')->whereIn('id', $rates->pluck('shipping_method_id')->unique())->update(['is_active' => false]);

        Schema::dropIfExists('shipping_rates');
    }

    public function down(): void
    {
        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_zone_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shipping_method_id')->constrained()->cascadeOnDelete();
            $table->decimal('charge', 12, 2)->default(0);
            $table->decimal('free_shipping_threshold', 12, 2)->nullable();
            $table->unsignedTinyInteger('min_delivery_days')->nullable();
            $table->unsignedTinyInteger('max_delivery_days')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['shipping_zone_id', 'shipping_method_id']);
        });

        Schema::table('shipping_methods', function (Blueprint $table) {
            $table->dropColumn(['charge', 'free_shipping_threshold', 'delivery_estimate']);
        });
    }
};
