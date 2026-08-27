<?php

namespace App\Http\Requests;

use App\Models\PoItemReferral;
use App\Models\PrReferral;
use App\Models\PurchaseOrderItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class LinkPoReferralsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'po_item_id' => ['required', 'integer', 'exists:purchase_order_items,id'],
            'allocations' => ['required', 'array', 'min:1'],
            'allocations.*.pr_referral_id' => ['required', 'integer', 'exists:pr_referrals,id'],
            'allocations.*.quantity_allocated' => ['required', 'integer', 'min:1'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $poItem = PurchaseOrderItem::find($this->input('po_item_id'));
            $allocations = $this->input('allocations', []);

            if (!$poItem || !is_array($allocations)) {
                return;
            }

            $alreadyAllocated = (int) PoItemReferral::where('po_item_id', $poItem->id)->sum('quantity_allocated');
            $newTotal = array_sum(array_column($allocations, 'quantity_allocated'));

            if ($alreadyAllocated + $newTotal > (int) $poItem->qty) {
                $available = (int) $poItem->qty - $alreadyAllocated;
                $validator->errors()->add('allocations', "Total allocations exceed the PO item's ordered quantity. Available to allocate: {$available}");
            }

            foreach ($allocations as $index => $allocation) {
                $referral = PrReferral::find($allocation['pr_referral_id'] ?? null);
                if (!$referral) {
                    continue;
                }

                $remaining = (int) $referral->quantity_needed - $referral->getFulfilledQuantity();
                if ((int) ($allocation['quantity_allocated'] ?? 0) > $remaining) {
                    $validator->errors()->add(
                        "allocations.{$index}.quantity_allocated",
                        "Exceeds referral #{$referral->id}'s remaining quantity needed ({$remaining})."
                    );
                }
            }
        });
    }
}
