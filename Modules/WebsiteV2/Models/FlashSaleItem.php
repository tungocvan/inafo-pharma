<?php

namespace Modules\WebsiteV2\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlashSaleItem extends Model
{
    public $timestamps = false;

    protected $table = 'website_v2_flash_sale_items';

    protected $fillable = ['flash_sale_id', 'product_id', 'price', 'quantity', 'sold'];

    protected $casts = ['price' => 'decimal:2'];

    public function flashSale(): BelongsTo
    {
        return $this->belongsTo(FlashSale::class);
    }
}
