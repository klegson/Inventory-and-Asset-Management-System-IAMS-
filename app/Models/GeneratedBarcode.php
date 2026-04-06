<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneratedBarcode extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'item_id',
        'item_type',
        'barcode_code',
        'article',
        'generated_at'
    ];
}