<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Reference data is always seeded (every seeder is idempotent).
     * The demo catalog is added only in local / testing environments.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            AdminUserSeeder::class,
            ShippingSeeder::class,
            SpecificationSeeder::class,
            ContentSeeder::class,
        ]);

        if (app()->environment(['local', 'testing'])) {
            $this->call([CatalogSeeder::class, OfferSeeder::class, DemoContentSeeder::class]);
        }
    }
}
