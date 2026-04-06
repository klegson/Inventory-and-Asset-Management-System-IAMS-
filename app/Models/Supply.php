<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supply extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'barcode_id',
        'article',
        'description',
        'supplier',
        'unit_measure',
        'unit_value',
        'quantity',
        'status',
        'image'
    ];
}