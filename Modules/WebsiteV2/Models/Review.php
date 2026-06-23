<?php

namespace Modules\WebsiteV2\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $table = 'website_v2_reviews';

    protected $fillable = ['user_id', 'product_id', 'rating', 'comment', 'images', 'is_approved', 'is_verified_purchase', 'likes'];

    protected $casts = ['images' => 'array', 'is_approved' => 'boolean', 'is_verified_purchase' => 'boolean'];
}
