<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The seeder referenced Unsplash photo 1611085583191-a3b1a20d55d1 which
     * no longer exists (404), leaving two products with broken images. Point
     * each at a verified-live jewelry photo.
     */
    public function up(): void
    {
        DB::table('products')
            ->where('image', 'like', '%photo-1611085583191-a3b1a20d55d1%')
            ->where('id', 13)
            ->update(['image' => 'https://images.unsplash.com/photo-1617038220319-276d3cfab638?w=600']);

        DB::table('products')
            ->where('image', 'like', '%photo-1611085583191-a3b1a20d55d1%')
            ->where('id', 18)
            ->update(['image' => 'https://images.unsplash.com/photo-1602173574767-37ac01994b2a?w=600']);
    }

    public function down(): void
    {
        DB::table('products')->whereIn('id', [13, 18])->update([
            'image' => 'https://images.unsplash.com/photo-1611085583191-a3b1a20d55d1?w=600',
        ]);
    }
};
