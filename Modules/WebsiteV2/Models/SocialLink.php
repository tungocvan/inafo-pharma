<?php

namespace Modules\WebsiteV2\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SocialLink extends Model
{
    protected $table = 'website_v2_social_links';

    protected $fillable = ['platform', 'name', 'url', 'icon_class', 'bg_color', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
