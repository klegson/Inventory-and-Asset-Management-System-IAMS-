<?php

namespace App\Services;

use App\Models\PrReferral;
use App\Models\RisRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PrReferralService
{
    /**
     * Flag an RIS line item as having insufficient/no stock, referring it to BAC.
     *
     * Expected $data keys: ris_id, ris_item_id, supply_id, quantity_needed,
     * referred_by (optional, defaults to the authenticated user), pr_number (optional).
     */
    public function createReferral(array $data): PrReferral
    {
        return DB::transaction(function () use ($data): PrReferral {
            $referral = PrReferral::create([
                'ris_id' => $data['ris_id'],
                'ris_item_id' => $data['ris_item_id'],
                'supply_id' => $data['supply_id'],
                'quantity_needed' => $data['quantity_needed'],
                'referred_by' => $data['referred_by'] ?? Auth::id(),
                'referred_at' => now(),
                'pr_number' => $data['pr_number'] ?? null,
                'status' => 'referred',
            ]);

            RisRequest::whereKey($data['ris_id'])->update(['status' => 'pending_procurement']);

            return $referral;
        });
    }
}
