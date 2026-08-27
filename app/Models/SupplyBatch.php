<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplyBatch extends Model
{
    protected $guarded = [];

    protected $casts = [
        'dr_date' => 'date',
        'unit_price' => 'decimal:2',
    ];

    public function supply()
    {
        return $this->belongsTo(Supply::class);
    }

    public function poItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class, 'po_item_id');
    }

    public function allocations()
    {
        return $this->hasMany(SupplyRequestAllocation::class);
    }

    /** Batches with stock left, ordered oldest-first for FIFO issuance */
    public function scopeAvailableFifo($query, int $supplyId)
    {
        return $query->where('supply_id', $supplyId)
            ->where('remaining_qty', '>', 0)
            ->orderBy('dr_date')
            ->orderBy('id');
    }
}
