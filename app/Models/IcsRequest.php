<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IcsRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'ics_no', 'fund_cluster', 'category', 
        'sig_received_from_name', 'sig_received_from_pos', 'sig_from_date',
        'sig_received_by_name', 'sig_received_by_pos', 'sig_by_date',
        'status', 'items_json'
    ];

    protected $casts = [
        'items_json' => 'array',
    ];
}