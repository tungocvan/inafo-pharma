<?php

namespace Modules\Inafo\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class HomeBanner extends Model
{
    protected $table = 'inafo_home_banners';

    protected $fillable = [
        'placement',
        'title',
        'subtitle',
        'image_desktop_url',
        'image_mobile_url',
        'target_url',
        'button_label',
        'position',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected $casts = [
        'position' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where(function (Builder $query) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $query) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }
}
