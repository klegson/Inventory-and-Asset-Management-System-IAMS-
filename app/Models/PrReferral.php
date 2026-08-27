<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrReferral extends Model
{
    protected $guarded = [];

    protected $casts = [
        'referred_at' => 'datetime',
    ];

    public function risRequest()
    {
        return $this->belongsTo(RisRequest::class, 'ris_id');
    }

    public function risItem()
    {
        return $this->belongsTo(RisItem::class, 'ris_item_id');
    }

    public function supply()
    {
        return $this->belongsTo(Supply::class);
    }

    public function poItems()
    {
        return $this->belongsToMany(PurchaseOrderItem::class, 'po_item_referrals', 'pr_referral_id', 'po_item_id')
            ->withPivot('quantity_allocated')
            ->withTimestamps();
    }

    public function scopePending($query)
    {
        return $query->where('status', 'referred');
    }

    /** Quantity already delivered against this referral's earmarked po_item shares */
    public function getFulfilledQuantity(): int
    {
        $batchIds = SupplyBatch::whereIn('po_item_id', $this->poItems()->pluck('purchase_order_items.id'))->pluck('id');

        if ($batchIds->isEmpty()) {
            return 0;
        }

        return (int) SupplyRequestAllocation::where('ris_item_id', $this->ris_item_id)
            ->whereIn('supply_batch_id', $batchIds)
            ->sum('quantity_allocated');
    }

    /** Pending referrals (referred/po_issued) grouped by supply, for PO-encoding consolidation */
    public static function reportPendingBySupply()
    {
        return static::whereIn('status', ['referred', 'po_issued'])
            ->with('supply', 'risRequest', 'risItem')
            ->orderBy('supply_id')
            ->get()
            ->groupBy('supply_id');
    }
}
