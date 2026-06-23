<?php

namespace Modules\WebsiteV2\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $table = 'website_v2_banners';

    protected $fillable = ['title', 'sub_title', 'image_desktop', 'image_mobile', 'link', 'btn_text', 'position', 'order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
