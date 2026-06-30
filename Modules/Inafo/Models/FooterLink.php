<?php

namespace Modules\Inafo\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FooterLink extends Model
{
    protected $table = 'inafo_footer_links';

    protected $fillable = [
        'column_id',
        'label',
        'url',
        'position',
        'is_active',
    ];

    protected $casts = [
        'column_id' => 'integer',
        'position' => 'integer',
        'is_active' => 'boolean',
    ];

    public function column(): BelongsTo
    {
        return $this->belongsTo(FooterColumn::class, 'column_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
