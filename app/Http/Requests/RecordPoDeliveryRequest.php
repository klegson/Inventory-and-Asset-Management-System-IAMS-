<?php

namespace App\Http\Requests;

use App\Models\PurchaseOrderItem;
use App\Models\SupplyBatch;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class RecordPoDeliveryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'po_item_id' => ['required', 'integer', 'exists:purchase_order_items,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'dr_number' => ['required', 'string', 'max:255'],
            'dr_date' => ['required', 'date'],
            'source_type' => ['nullable', 'in:procurement_stock,direct_issuance'],
            'requesting_office' => ['required_if:source_type,direct_issuance', 'nullable', 'string', 'max:255'],
            'ris_no' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $poItemId = $this->input('po_item_id');
            $quantity = (int) $this->input('quantity');

            if (!$poItemId || $quantity < 1) {
                return;
            }

            $poItem = PurchaseOrderItem::find($poItemId);
            if (!$poItem) {
                return;
            }

            $delivered = (int) SupplyBatch::where('po_item_id', $poItem->id)->sum('quantity');
            $remaining = (int) $poItem->qty - $delivered;

            if ($quantity > $remaining) {
                $validator->errors()->add('quantity', "Delivery quantity exceeds remaining undelivered quantity. Remaining: {$remaining}");
            }
        });
    }
}
