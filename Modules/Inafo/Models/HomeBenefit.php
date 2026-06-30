<?php

namespace Modules\Inafo\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class HomeBenefit extends Model
{
    protected $table = 'inafo_home_benefits';

    protected $fillable = [
        'title',
        'description',
        'icon',
        'position',
        'is_active',
    ];

    protected $casts = [
        'position' => 'integer',
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
