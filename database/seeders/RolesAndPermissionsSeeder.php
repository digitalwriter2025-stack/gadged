<?php

namespace Database\Seeders;

use App\Services\AccessControlService;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(AccessControlService $accessControl): void
    {
        $accessControl->sync();
    }
}
