<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RisRequest extends Model
{
    use HasFactory;

    protected $table = 'ris_requests';
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'ris_no', 'entity_name', 'division', 'office', 'fund_cluster', 'rcc', 
        'purpose', 'sig_requested_by', 'sig_approved_by', 'sig_issued_by', 
        'sig_received_by', 'desig_requested', 'desig_approved', 'desig_issued', 
        'desig_received', 'date_requested', 'date_approved', 'date_issued', 
        'date_received', 'status'
    ];

    public function items()
    {
        return $this->hasMany(RisItem::class, 'ris_id');
    }
}