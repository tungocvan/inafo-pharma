<?php

namespace Modules\WebsiteV2\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FlashSale extends Model
{
    protected $table = 'website_v2_flash_sales';

    protected $fillable = ['title', 'start_time', 'end_time', 'is_active'];

    protected $casts = ['start_time' => 'datetime', 'end_time' => 'datetime', 'is_active' => 'boolean'];

    public function items(): HasMany
    {
        return $this->hasMany(FlashSaleItem::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
