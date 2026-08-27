<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplyRequestAllocation extends Model
{
    protected $guarded = [];

    public function risItem()
    {
        return $this->belongsTo(RisItem::class, 'ris_item_id');
    }

    public function supplyBatch()
    {
        return $this->belongsTo(SupplyBatch::class);
    }
}
