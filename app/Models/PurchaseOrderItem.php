<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    protected $guarded = [];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function supply()
    {
        return $this->belongsTo(Supply::class);
    }

    public function batches()
    {
        return $this->hasMany(SupplyBatch::class, 'po_item_id');
    }

    public function referrals()
    {
        return $this->belongsToMany(PrReferral::class, 'po_item_referrals', 'po_item_id', 'pr_referral_id')
            ->withPivot('quantity_allocated')
            ->withTimestamps();
    }

    /** Sum of quantities delivered so far across all supply_batches for this line */
    public function getDeliveredQuantity(): int
    {
        return (int) $this->batches()->sum('quantity');
    }

    /** 'pending' | 'partial' | 'complete', derived from delivered vs ordered quantity */
    public function getDeliveryStatus(): string
    {
        $delivered = $this->getDeliveredQuantity();

        if ($delivered <= 0) {
            return 'pending';
        }

        return $delivered >= $this->qty ? 'complete' : 'partial';
    }

    /** Adds a delivered_quantity column via subquery, for listing/filtering by delivery status */
    public function scopeWithDeliveredQuantity($query)
    {
        return $query->selectRaw('purchase_order_items.*, (SELECT COALESCE(SUM(quantity), 0) FROM supply_batches WHERE supply_batches.po_item_id = purchase_order_items.id) as delivered_quantity');
    }

    /** Report: PO items whose derived delivery status matches pending/partial/complete */
    public static function reportByDeliveryStatus(string $status)
    {
        return static::withDeliveredQuantity()
            ->with('purchaseOrder', 'supply')
            ->get()
            ->filter(fn (self $item) => $item->getDeliveryStatus() === $status)
            ->values();
    }
}
