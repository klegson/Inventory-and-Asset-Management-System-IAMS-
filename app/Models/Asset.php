<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'barcode_id',
        'article',
        'description',
        'unit_measure',
        'supplier',
        'unit_value',
        'quantity',
        'status'
    ];
}