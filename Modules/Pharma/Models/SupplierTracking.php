<?php

namespace Modules\Pharma\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierTracking extends Model
{
    protected $table = 'pharma_supplier_trackings';

    protected $fillable = [
        'medicine_id',
        'working_date',
        'supplier_name',
        'supplier_representative',
        'area',
        'import_price',
        'cost_price',
        'selling_price',
        'invoice_price',
        'price_difference',
        'difference_percent',
        'profit_percent',
        'cost_invoice_percent',
        'committed_quantity',
        'unit',
        'deposit_amount',
        'start_date',
        'end_date',
        'contract_url',
        'status',
        'note',
    ];

    protected $casts = [
        'working_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'import_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'invoice_price' => 'decimal:2',
        'price_difference' => 'decimal:2',
        'difference_percent' => 'decimal:2',
        'profit_percent' => 'decimal:2',
        'cost_invoice_percent' => 'decimal:2',
        'committed_quantity' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
    ];

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class, 'medicine_id');
    }
}