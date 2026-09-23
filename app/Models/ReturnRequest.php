<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnRequest extends Model
{
    protected $fillable = [
        'request_number',
        'order_id',
        'user_id',
        'order_item_id',
        'status',
        'reason',
        'admin_note',
        'requested_at',
        'resolved_at',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * Human-friendly request number: RET-8F3K2Q
     */
    public static function makeNumber(): string
    {
        do {
            $number = 'RET-'.strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
        } while (self::where('request_number', $number)->exists());

        return $number;
    }
}
