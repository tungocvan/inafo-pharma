<?php

namespace Modules\WebsiteV2\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FooterLink extends Model
{
    protected $table = 'website_v2_footer_links';

    protected $fillable = ['footer_column_id', 'label', 'url', 'route_name', 'new_tab', 'sort_order', 'is_active'];

    protected $casts = ['new_tab' => 'boolean', 'is_active' => 'boolean'];

    public function column(): BelongsTo
    {
        return $this->belongsTo(FooterColumn::class, 'footer_column_id');
    }
}
