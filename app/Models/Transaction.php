<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'item_id',
        'item_type',
        'transaction_type',
        'quantity',
        'supplier',
        'po_number',
        'delivery_receipt',
        'office',
        'unit_price',
        'receipt_status',
        'transaction_date',
        'remarks',
        'date_time'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
    ];
}