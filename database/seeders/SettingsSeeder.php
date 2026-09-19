<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Seed the singleton store-settings row so checkout, emails and the
     * layout use real configured values instead of scattered fallbacks.
     */
    public function run(): void
    {
        Setting::firstOrCreate([], [
            'site_name' => 'Shivayra',
            'email' => config('mail.from.address', 'hello@example.com'),
            'phone' => '+91 9876543210',
            'address' => 'Delhi, India',
            'tax_percent' => 5.0,
            'delivery_fee' => 60.0,
            'min_order_for_free_delivery' => 999.0,
            'is_cod_enabled' => true,
        ]);
    }
}
