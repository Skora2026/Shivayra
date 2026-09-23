<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'sub_category_id',
        'name',
        'slug',
        'image',
        'description',
        'price',
        'sale_price',
        'stock',
        'status',
        'size',
        'pattern',
        'occasion',
        'fabric',
        'color',
        'neckline',
        'is_new_arrival',
        'is_trending',
        'is_featured',
        'is_returnable',
        'variant_name_1',
        'variant_name_2',
        'specifications',
        'gallery_images',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'stock' => 'integer',
        'status' => 'string',
        'is_new_arrival' => 'boolean',
        'is_trending' => 'boolean',
        'is_featured' => 'boolean',
        'is_returnable' => 'boolean',
        'specifications' => 'array',
        'gallery_images' => 'array',
    ];

    /**
     * Auto-generate a unique slug before creating.
     *
     * "Silver Ring" and "Silver–Ring" both slug to "silver-ring" — without a
     * suffix the second insert dies on the unique index with a raw 500.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = static::uniqueSlug($product->name);
            }
        });

        // Renaming must NOT regenerate the slug: /product-detail/{slug} links
        // are bookmarked and search-indexed, and a silent change breaks them all.
        // The slug an item was created with is the slug it keeps.
    }

    /**
     * Build a slug from a name, suffixing -2, -3, … until it is unused.
     */
    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'product';
        $slug = $base;
        $i = 2;

        while (static::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    /**
     * Get the category this product belongs to.
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Get the sub-category this product belongs to.
     */
    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class, 'sub_category_id');
    }

    /**
     * Get the image URL accessor.
     */
    public function getImageUrlAttribute(): string
    {
        if (filter_var($this->image, FILTER_VALIDATE_URL)) {
            return $this->image;
        }
        if ($this->image && \Storage::disk('public')->exists($this->image)) {
            return asset('storage/'.$this->image);
        }

        return asset('images/placeholder.svg');
    }

    /**
     * Get the effective price (sale price if set, otherwise regular price).
     */
    public function getEffectivePriceAttribute(): float
    {
        return $this->sale_price ?? $this->price;
    }

    /**
     * Get variants for the product.
     */
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Approved customer reviews (verified purchases).
     */
    public function reviews()
    {
        return $this->hasMany(ProductReview::class)->approved()->latest();
    }
}
