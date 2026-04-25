<?php

namespace Database\Seeders;

use App\Models\Core\CompanyInfo;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        Artisan::call('make:filament-user', [
            '--name' => 'admin',
            '--email' => 'admin@admin.com',
            '--password' => 'admin',
            '--panel' => 'core',
        ]);

        CompanyInfo::create([
            'name' => 'Demo Company',
            'adresse' => 'Demo Address',
            'code_postal' => '00000',
            'ville' => 'Demo City',
            'pays' => 'Demo Pays',
            'siret' => '00000000000000',
            'ape' => '0000Z',
        ]);
    }
}
