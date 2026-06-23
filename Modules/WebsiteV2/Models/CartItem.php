<?php

namespace Modules\WebsiteV2\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Product\Models\Product;

class CartItem extends Model
{
    protected $table = 'website_v2_cart_items';

    protected $fillable = ['cart_id', 'product_id', 'price', 'quantity', 'total'];

    protected $casts = ['price' => 'decimal:2', 'total' => 'decimal:2'];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
