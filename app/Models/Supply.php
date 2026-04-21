<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supply extends Model
{
    use HasFactory;

    // Tell Laravel not to look for created_at and updated_at columns
    public $timestamps = false;

    // Add the new column to your fillable array
    protected $fillable = [
        'barcode_id', 
        'article', 
        'description', 
        'supplier', 
        'unit_measure', 
        'unit_value', 
        'quantity', 
        'status', 
        'image',
        'low_stock_threshold'
    ];
}