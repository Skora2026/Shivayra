<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'title',
        'description',
        'badge',
        'button_text',
        'button_link',
        'secondary_button_text',
        'secondary_button_link',
        'image',
        'mobile_image',
        'device_type',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function getImageUrlAttribute(): string
    {
        if ($this->image) {
            if (filter_var($this->image, FILTER_VALIDATE_URL)) {
                return $this->image;
            }
            if (\Storage::disk('public')->exists($this->image)) {
                return asset('storage/'.$this->image);
            }
        }

        // Fallback to mobile image if desktop image is not set
        if ($this->mobile_image) {
            if (filter_var($this->mobile_image, FILTER_VALIDATE_URL)) {
                return $this->mobile_image;
            }
            if (\Storage::disk('public')->exists($this->mobile_image)) {
                return asset('storage/'.$this->mobile_image);
            }
        }

        return asset('images/placeholder.png');
    }

    /**
     * Get the mobile image URL accessor.
     */
    public function getMobileImageUrlAttribute(): string
    {
        if ($this->mobile_image) {
            if (filter_var($this->mobile_image, FILTER_VALIDATE_URL)) {
                return $this->mobile_image;
            }
            if (\Storage::disk('public')->exists($this->mobile_image)) {
                return asset('storage/'.$this->mobile_image);
            }
        }

        // Fallback to desktop image if mobile image is not set
        if ($this->image) {
            if (filter_var($this->image, FILTER_VALIDATE_URL)) {
                return $this->image;
            }
            if (\Storage::disk('public')->exists($this->image)) {
                return asset('storage/'.$this->image);
            }
        }

        return asset('images/placeholder.png');
    }

    /**
     * Scope for active banners.
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope for ordered banners.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('created_at', 'desc');
    }
}
