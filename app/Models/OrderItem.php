<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_variant_id',
        'product_name',
        'price',
        'qty',
        'total',
    ];

    /**
     * Get the order this item belongs to.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product associated with this item.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the variant this item was sold as (nullable).
     */
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * The return request filed against this item (nullable).
     */
    public function returnRequest()
    {
        return $this->hasOne(ReturnRequest::class);
    }

    /**
     * Reviews written for this exact line item.
     */
    public function review()
    {
        return $this->hasOne(ProductReview::class);
    }
}
