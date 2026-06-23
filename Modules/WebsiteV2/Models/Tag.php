<?php

namespace Modules\WebsiteV2\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $table = 'website_v2_tags';

    protected $fillable = ['name', 'slug'];
}
