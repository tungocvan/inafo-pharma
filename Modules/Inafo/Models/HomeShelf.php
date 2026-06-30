<?php

namespace Modules\Inafo\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HomeShelf extends Model
{
    protected $table = 'inafo_home_shelves';

    protected $fillable = [
        'title',
        'slug',
        'type',
        'view_more_url',
        'banner_id',
        'product_limit',
        'position',
        'is_active',
    ];

    protected $casts = [
        'banner_id' => 'integer',
        'product_limit' => 'integer',
        'position' => 'integer',
        'is_active' => 'boolean',
    ];

    public function banner(): BelongsTo
    {
        return $this->belongsTo(HomeBanner::class, 'banner_id');
    }

    public function shelfProducts(): HasMany
    {
        return $this->hasMany(HomeShelfProduct::class, 'shelf_id')->orderBy('position');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
