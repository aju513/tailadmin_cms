<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class CmsSeeder extends Seeder
{
    public function run(): void
    {
        Menu::query()->firstOrCreate(['location' => 'header'], ['name' => 'Header Navigation']);
        Menu::query()->firstOrCreate(['location' => 'footer'], ['name' => 'Footer Navigation']);

        foreach ([
            'site_name' => config('app.name'),
            'office_name' => '',
            'logo_url' => '',
            'phone' => '',
            'email' => '',
            'address' => '',
            'footer_text' => '',
        ] as $key => $value) {
            SiteSetting::query()->firstOrCreate(['key' => $key], ['value' => $value, 'type' => 'text']);
        }
    }
}
