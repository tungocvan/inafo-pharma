<?php

namespace Modules\WebsiteV2\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateLevel extends Model
{
    protected $table = 'website_v2_affiliate_levels';

    protected $fillable = ['name', 'slug', 'min_revenue_required', 'is_default'];

    protected $casts = ['min_revenue_required' => 'decimal:2', 'is_default' => 'boolean'];
}
