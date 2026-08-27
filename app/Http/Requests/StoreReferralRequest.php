<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReferralRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ris_id' => ['required', 'integer', 'exists:ris_requests,id'],
            'ris_item_id' => ['required', 'integer', 'exists:ris_items,id'],
            'supply_id' => ['required', 'integer', 'exists:supplies,id'],
            'quantity_needed' => ['required', 'integer', 'min:1'],
            'pr_number' => ['nullable', 'string', 'max:255'],
        ];
    }
}
