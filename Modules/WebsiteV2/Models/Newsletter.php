<?php

namespace Modules\WebsiteV2\Models;

use Illuminate\Database\Eloquent\Model;

class Newsletter extends Model
{
    protected $table = 'website_v2_newsletters';

    protected $fillable = ['email', 'is_subscribed'];

    protected $casts = ['is_subscribed' => 'boolean'];
}
