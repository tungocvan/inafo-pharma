<?php

namespace Modules\WebsiteV2\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FooterColumn extends Model
{
    protected $table = 'website_v2_footer_columns';

    protected $fillable = ['title', 'slug', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function links(): HasMany
    {
        return $this->hasMany(FooterLink::class, 'footer_column_id')->orderBy('sort_order');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
