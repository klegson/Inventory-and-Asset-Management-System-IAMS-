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
        'transaction_date',
        'remarks',
        'date_time'
    ];
}