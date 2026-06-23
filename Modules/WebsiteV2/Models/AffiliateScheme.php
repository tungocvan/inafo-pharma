<?php

namespace Modules\WebsiteV2\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateScheme extends Model
{
    protected $table = 'website_v2_affiliate_schemes';

    protected $fillable = ['product_id', 'level_id', 'user_id', 'commission_type', 'percent_value', 'fixed_value', 'is_active'];

    protected $casts = ['percent_value' => 'decimal:2', 'fixed_value' => 'decimal:2', 'is_active' => 'boolean'];
}
