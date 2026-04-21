<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $guarded = [];

    protected $fillable = [
        'entity_name', 'po_no', 'supplier_name', 'supplier_address', 'po_date', 
        'procurement_mode', 'auth_official', 'chief_accountant', 'total_amount', 'status',
        'place_of_delivery', 'date_of_delivery', 'delivery_term', 'payment_term',
        'auth_official_designation', 'chief_accountant_designation' // <--- ADDED THESE
    ];

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }
}