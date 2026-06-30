<?php

namespace Modules\Inafo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeShelfProduct extends Model
{
    protected $table = 'inafo_home_shelf_products';

    protected $fillable = [
        'shelf_id',
        'product_id',
        'position',
    ];

    protected $casts = [
        'shelf_id' => 'integer',
        'product_id' => 'integer',
        'position' => 'integer',
    ];

    public function shelf(): BelongsTo
    {
        return $this->belongsTo(HomeShelf::class, 'shelf_id');
    }
}
