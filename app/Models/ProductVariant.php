<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'value_1',
        'value_2',
        'price',
        'sale_price',
        'stock',
        'image',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'stock' => 'integer',
    ];

    protected $appends = ['images', 'image_urls'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get all images for this variant as an array of paths.
     */
    public function getImagesAttribute(): array
    {
        $val = $this->attributes['image'] ?? null;
        if (empty($val)) {
            return [];
        }

        // Try to decode as JSON
        $decoded = json_decode($val, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        // Return as single item array if it's a plain string
        return [$val];
    }

    /**
     * Get all image URLs for this variant.
     */
    public function getImageUrlsAttribute(): array
    {
        $urls = [];
        foreach ($this->images as $img) {
            if ($img) {
                if (filter_var($img, FILTER_VALIDATE_URL)) {
                    $urls[] = $img;
                } elseif (\Storage::disk('public')->exists($img)) {
                    $urls[] = asset('storage/'.$img);
                }
            }
        }

        return $urls;
    }

    /**
     * Get variant image URL (backward compatibility, returns first image URL).
     */
    public function getImageUrlAttribute(): ?string
    {
        $urls = $this->image_urls;

        return ! empty($urls) ? $urls[0] : null;
    }
}
