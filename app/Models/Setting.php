<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    //
    protected $fillable = [
        'site_name',
        'logo',
        'favicon',
        'email',
        'phone',
        'alternate_phone',
        'whatsapp',
        'address',
        'facebook',
        'instagram',
        'twitter',
        'youtube',
        'tax_percent',
        'delivery_fee',
        'min_order_for_free_delivery',
        'is_cod_enabled',
        'return_window_days',
        'featured_limit',
        'trending_limit',
    ];

    protected $casts = [
        'tax_percent' => 'float',
        'delivery_fee' => 'float',
        'min_order_for_free_delivery' => 'float',
        'is_cod_enabled' => 'boolean',
        'return_window_days' => 'integer',
        'featured_limit' => 'integer',
        'trending_limit' => 'integer',
    ];
}
