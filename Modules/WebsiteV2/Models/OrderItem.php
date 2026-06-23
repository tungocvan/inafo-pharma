<?php

namespace Modules\WebsiteV2\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Product\Models\Product;

class OrderItem extends Model
{
    protected $table = 'website_v2_order_items';

    protected $fillable = ['order_id', 'product_id', 'product_name', 'price', 'quantity', 'total', 'options'];

    protected $casts = ['price' => 'decimal:2', 'total' => 'decimal:2', 'options' => 'array'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
