<?php

namespace Modules\WebsiteV2\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $table = 'website_v2_orders';

    protected $fillable = [
        'user_id',
        'order_code',
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_address',
        'note',
        'subtotal',
        'shipping_fee',
        'discount',
        'total',
        'coupon_code',
        'payment_method',
        'status',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(OrderHistory::class)->latest();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
