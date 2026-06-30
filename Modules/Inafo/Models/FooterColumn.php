<?php

namespace Modules\Inafo\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FooterColumn extends Model
{
    protected $table = 'inafo_footer_columns';

    protected $fillable = [
        'title',
        'position',
        'is_active',
    ];

    protected $casts = [
        'position' => 'integer',
        'is_active' => 'boolean',
    ];

    public function links(): HasMany
    {
        return $this->hasMany(FooterLink::class, 'column_id')->orderBy('position');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
