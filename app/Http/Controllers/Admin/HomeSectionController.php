<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;

class HomeSectionController extends Controller
{
    /**
     * Control which products appear in the home-page Featured and Trending
     * sections and how many cards each section shows.
     */
    public function edit()
    {
        $setting = Setting::first();

        $products = Product::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'is_featured', 'is_trending', 'image', 'price', 'sale_price']);

        $featuredCount = Product::where('status', 'active')->where('is_featured', true)->count();
        $trendingCount = Product::where('status', 'active')->where('is_trending', true)->count();

        return view('admin.home-sections.edit', compact(
            'setting', 'products', 'featuredCount', 'trendingCount'
        ));
    }

    /**
     * Persist the section card limits (which products show is saved per-product
     * via the quick-toggle endpoint).
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'featured_limit' => 'required|integer|min:1|max:24',
            'trending_limit' => 'required|integer|min:1|max:24',
        ]);

        $setting = Setting::first();
        if (! $setting) {
            return redirect()->route('admin.home-sections.edit')
                ->with('error', 'Store settings row missing. Save Settings first.');
        }

        $setting->update($data);

        return redirect()->route('admin.home-sections.edit')
            ->with('success', 'Home sections updated — showing up to '.$data['featured_limit'].' featured and '.$data['trending_limit'].' trending cards.');
    }

    /**
     * Toggle a product's featured/trending flag from the section manager.
     */
    public function toggleProduct(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'section' => 'required|in:featured,trending',
            'value' => 'required|boolean',
        ]);

        $product = Product::findOrFail($data['product_id']);
        $column = $data['section'] === 'featured' ? 'is_featured' : 'is_trending';
        $product->update([$column => $data['value']]);

        return response()->json([
            'success' => true,
            'message' => $product->name.($data['value'] ? ' added to ' : ' removed from ').' '.$data['section'].'.',
        ]);
    }
}
