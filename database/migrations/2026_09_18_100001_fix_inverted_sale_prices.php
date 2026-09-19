<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * The storefront renders `sale_price` as the current price and `price` as
     * the struck-through original. Any row with sale_price > price therefore
     * displays a "discount" that costs more — swap the two columns so the
     * original is always the higher figure.
     */
    public function up(): void
    {
        $inverted = DB::table('products')
            ->whereNotNull('sale_price')
            ->whereColumn('sale_price', '>', 'price')
            ->get(['id', 'price', 'sale_price']);

        foreach ($inverted as $product) {
            DB::table('products')
                ->where('id', $product->id)
                ->update([
                    'price' => $product->sale_price,
                    'sale_price' => $product->price,
                ]);
        }
    }

    public function down(): void
    {
        // The swap is not meaningfully reversible (the original bad data is
        // arbitrary); nothing to restore.
    }
};
