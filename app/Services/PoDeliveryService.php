<?php

namespace App\Services;

use App\Models\PoItemReferral;
use App\Models\PrReferral;
use App\Models\PurchaseOrderItem;
use App\Models\RisItem;
use App\Models\RisRequest;
use App\Models\SupplyBatch;
use App\Models\SupplyRequestAllocation;
use Illuminate\Support\Facades\DB;

class PoDeliveryService
{
    public function __construct(private SupplyService $supplyService)
    {
    }

    /**
     * Record a (possibly partial) delivery receipt against a PO line item.
     *
     * Expected $data keys:
     *  - po_item_id (required)
     *  - quantity (required, int > 0)
     *  - dr_number (required)
     *  - dr_date (required)
     *  - unit_price (optional, defaults to the PO item's unit_cost)
     *  - source_type (optional, defaults to the PO item's source_type)
     *  - requesting_office (required when source_type = direct_issuance)
     *  - ris_no (optional, auto-generated from dr_number when omitted)
     */
    public function recordDelivery(array $data): SupplyBatch
    {
        $quantity = (int) ($data['quantity'] ?? 0);
        if ($quantity < 1) {
            throw new \InvalidArgumentException('Delivered quantity must be a positive whole number.');
        }

        return DB::transaction(function () use ($data, $quantity): SupplyBatch {
            $poItem = PurchaseOrderItem::whereKey($data['po_item_id'])->lockForUpdate()->firstOrFail();

            if (!$poItem->supply_id) {
                throw new \DomainException('This PO item is not linked to a supply item yet.');
            }

            // Recompute delivered-so-far inside the lock to avoid a race with concurrent deliveries
            $delivered = (int) SupplyBatch::where('po_item_id', $poItem->id)->lockForUpdate()->sum('quantity');
            $remaining = (int) $poItem->qty - $delivered;

            if ($quantity > $remaining) {
                throw new \DomainException("Delivery quantity exceeds remaining undelivered quantity. Remaining: {$remaining}");
            }

            $sourceType = $data['source_type'] ?? $poItem->source_type ?? 'procurement_stock';
            $unitPrice = isset($data['unit_price']) && is_numeric($data['unit_price'])
                ? (float) $data['unit_price']
                : (float) $poItem->unit_cost;

            if ($sourceType === 'direct_issuance' && empty($data['requesting_office'])) {
                throw new \InvalidArgumentException('Requesting office is required for direct-issuance deliveries.');
            }

            $supply = $poItem->supply;

            $batch = SupplyBatch::create([
                'supply_id' => $supply->id,
                'po_item_id' => $poItem->id,
                'source_type' => $sourceType,
                'dr_number' => $data['dr_number'] ?? null,
                'dr_date' => $data['dr_date'] ?? null,
                'quantity' => $quantity,
                'remaining_qty' => $sourceType === 'direct_issuance' ? 0 : $quantity,
                'unit_price' => $unitPrice,
                'requesting_office' => $sourceType === 'direct_issuance' ? $data['requesting_office'] : null,
            ]);

            if ($sourceType === 'direct_issuance') {
                $referralLinks = PoItemReferral::where('po_item_id', $poItem->id)->get();

                if ($referralLinks->isNotEmpty()) {
                    $this->splitDeliveryAcrossReferrals($batch, $referralLinks);
                } else {
                    $this->issueDirectlyFromBatch($poItem, $batch, $data);
                }
            } else {
                // Goes into warehouse stock: update the running quantity / weighted-average unit value
                $this->supplyService->receiveSupply($supply, [
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'po_number' => $poItem->purchaseOrder->po_no ?? null,
                    'delivery_receipt' => $data['dr_number'] ?? null,
                    'office' => $poItem->purchaseOrder->place_of_delivery ?? null,
                    'transaction_date' => $data['dr_date'] ?? null,
                    'remarks' => "Received via DR {$data['dr_number']}",
                ]);
            }

            return $batch;
        });
    }

    /**
     * Direct-issuance DRs skip the warehouse: the batch is consumed in full,
     * immediately allocated to a dedicated RIS record for this delivery only.
     */
    private function issueDirectlyFromBatch(PurchaseOrderItem $poItem, SupplyBatch $batch, array $data): void
    {
        $risNo = $data['ris_no'] ?? ('DR-' . $data['dr_number']);

        $risRequest = RisRequest::firstOrCreate(
            ['ris_no' => $risNo],
            [
                'office' => $data['requesting_office'],
                'purpose' => "Direct issuance from PO {$poItem->purchaseOrder->po_no} / DR {$data['dr_number']}",
                'date_requested' => $data['dr_date'] ?? null,
                'status' => 'Issued',
            ]
        );

        $risItem = RisItem::create([
            'ris_id' => $risRequest->id,
            'stock_no' => $poItem->supply->barcode_id ?? null,
            'unit' => $poItem->unit,
            'description' => $poItem->description,
            'req_quantity' => $batch->quantity,
            'issue_quantity' => $batch->quantity,
            'remarks' => "Direct issuance, DR {$data['dr_number']}",
        ]);

        SupplyRequestAllocation::create([
            'ris_item_id' => $risItem->id,
            'supply_batch_id' => $batch->id,
            'quantity_allocated' => $batch->quantity,
        ]);
    }

    /**
     * Consolidated-PO fulfillment: split a delivered quantity across the referrals
     * earmarked against this po_item, proportional to each referral's quantity_allocated
     * (last referral absorbs the rounding remainder), crediting each ORIGINAL RIS item.
     */
    private function splitDeliveryAcrossReferrals(SupplyBatch $batch, \Illuminate\Support\Collection $referralLinks): void
    {
        $referralLinks = $referralLinks->values();
        $totalAllocated = (int) $referralLinks->sum('quantity_allocated');

        if ($totalAllocated < 1) {
            return;
        }

        $deliveredQty = $batch->quantity;
        $distributed = 0;
        $lastIndex = $referralLinks->count() - 1;
        $affectedRisIds = [];

        foreach ($referralLinks as $index => $link) {
            $referral = PrReferral::whereKey($link->pr_referral_id)->lockForUpdate()->first();
            if (!$referral) {
                continue;
            }

            $share = $index === $lastIndex
                ? $deliveredQty - $distributed
                : (int) floor($deliveredQty * $link->quantity_allocated / $totalAllocated);

            $distributed += $share;

            if ($share < 1) {
                continue;
            }

            SupplyRequestAllocation::create([
                'ris_item_id' => $referral->ris_item_id,
                'supply_batch_id' => $batch->id,
                'quantity_allocated' => $share,
            ]);

            $referral->update([
                'status' => $referral->getFulfilledQuantity() >= $referral->quantity_needed ? 'fulfilled' : 'po_issued',
            ]);

            $affectedRisIds[$referral->ris_id] = true;
        }

        foreach (array_keys($affectedRisIds) as $risId) {
            $this->syncRisStatusFromReferrals((int) $risId);
        }
    }

    /** Roll up an RIS's status from the state of all its (non-cancelled) referrals */
    private function syncRisStatusFromReferrals(int $risId): void
    {
        $referrals = PrReferral::where('ris_id', $risId)->where('status', '!=', 'cancelled')->get();

        if ($referrals->isEmpty()) {
            return;
        }

        $status = $referrals->every(fn (PrReferral $referral) => $referral->status === 'fulfilled')
            ? 'fulfilled'
            : 'partially_fulfilled';

        RisRequest::whereKey($risId)->update(['status' => $status]);
    }

    /**
     * Link a consolidated PO item to one or more pending referrals it will fulfill.
     *
     * $referralAllocations: array of ['pr_referral_id' => int, 'quantity_allocated' => int]
     */
    public function linkPoToReferrals(int $poItemId, array $referralAllocations): array
    {
        return DB::transaction(function () use ($poItemId, $referralAllocations): array {
            $poItem = PurchaseOrderItem::whereKey($poItemId)->lockForUpdate()->firstOrFail();

            $alreadyAllocated = (int) PoItemReferral::where('po_item_id', $poItem->id)->sum('quantity_allocated');
            $newTotal = array_sum(array_column($referralAllocations, 'quantity_allocated'));

            if ($alreadyAllocated + $newTotal > (int) $poItem->qty) {
                $available = (int) $poItem->qty - $alreadyAllocated;
                throw new \DomainException("Referral allocations exceed the PO item's ordered quantity. Available to allocate: {$available}");
            }

            $created = [];

            foreach ($referralAllocations as $allocation) {
                $referral = PrReferral::whereKey($allocation['pr_referral_id'])->lockForUpdate()->firstOrFail();

                $created[] = PoItemReferral::create([
                    'po_item_id' => $poItem->id,
                    'pr_referral_id' => $referral->id,
                    'quantity_allocated' => (int) $allocation['quantity_allocated'],
                ]);

                $referral->update(['status' => 'po_issued']);
            }

            return $created;
        });
    }
}
