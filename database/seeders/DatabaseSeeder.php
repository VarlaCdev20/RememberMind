<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        if (app()->environment('testing') || (app()->environment('local') && filter_var(env('SEED_SAMPLE_ACCOUNTS', false), FILTER_VALIDATE_BOOL))) {
            $this->call(AdminSeeder::class);
        }
    }
}
