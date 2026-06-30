<?php

namespace Modules\Inafo\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PartnerLogo extends Model
{
    protected $table = 'inafo_partner_logos';

    protected $fillable = [
        'name',
        'logo_url',
        'target_url',
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
