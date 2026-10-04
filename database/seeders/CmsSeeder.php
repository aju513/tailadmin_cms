<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class CmsSeeder extends Seeder
{
    public function run(): void
    {
        Menu::query()->firstOrCreate(['location' => 'header'], ['name' => 'Main Menu']);
        Menu::query()->firstOrCreate(['location' => 'footer'], ['name' => 'Footer Menu']);
        Menu::query()->firstOrCreate(['location' => 'important_links'], ['name' => 'Important Links Menu']);

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
