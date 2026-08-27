<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PoItemReferral extends Model
{
    protected $guarded = [];

    public function poItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class, 'po_item_id');
    }

    public function prReferral()
    {
        return $this->belongsTo(PrReferral::class);
    }
}
