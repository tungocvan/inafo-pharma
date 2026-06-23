<?php

namespace Modules\WebsiteV2\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'website_v2_settings';

    protected $fillable = ['key', 'value', 'group_name', 'type', 'label'];
}
