<?php

namespace Modules\WebsiteV2\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $table = 'website_v2_coupons';

    protected $fillable = ['code', 'description', 'type', 'value', 'min_order_value', 'usage_limit', 'usage_count', 'starts_at', 'expires_at', 'is_active'];

    protected $casts = [
        'value' => 'decimal:2',
        'min_order_value' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
