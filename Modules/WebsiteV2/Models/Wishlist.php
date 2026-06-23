<?php

namespace Modules\WebsiteV2\Models;

use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    protected $table = 'website_v2_wishlists';

    protected $fillable = ['user_id', 'product_id'];
}
