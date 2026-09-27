<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        if (app()->environment('testing') || filter_var(env('SEED_DEMO_ACCOUNTS', false), FILTER_VALIDATE_BOOL)) {
            $this->call(AdminSeeder::class);
        }
    }
}
