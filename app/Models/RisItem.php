<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RisItem extends Model
{
    use HasFactory;

    protected $table = 'ris_items';
    public $timestamps = false;

    protected $fillable = [
        'ris_id', 'stock_no', 'unit', 'description', 
        'req_quantity', 'stock_avail', 'issue_quantity', 'remarks'
    ];

    public function request()
    {
        return $this->belongsTo(RisRequest::class, 'ris_id');
    }
}