<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Transaction;
use App\Models\ActivityLog;
use App\Models\IcsRequest;
use App\Models\AssetCustody;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AssetService
{
    /**
     * Check if asset barcode already exists
     */
    public function barcodeDuplicate(string $barcode): ?Asset
    {
        return Asset::where('barcode_id', trim($barcode))->first();
    }

    /**
     * Store image file and return filename
     */
    protected function storeImage(Request $request, ?string $article = null): ?string
    {
        if (!$request->hasFile('image')) {
            return null;
        }

        $file = $request->file('image');
        $filename = time() . '_' . Str::slug($article ?? 'asset') . '.' . $file->getClientOriginalExtension();
        $file->storeAs('assets', $filename, 'public');

        return $filename;
    }

    /**
     * Create a new asset with transaction and activity log
     */
    public function create(array $data): Asset
    {
        $imageName = null;
        
        try {
            DB::beginTransaction();

            $acquisitionDate = $data['acquisition_date'];
            $year = date('Y', strtotime($acquisitionDate));
            $serialSetting = SystemSetting::firstOrCreate(
                ['key' => 'asset_serial_' . $year],
                ['value' => '0']
            );
            $baseSerial = (int) $serialSetting->value + 1;
            $serialSetting->update(['value' => $baseSerial]);

            // Handle image upload if provided as file
            if (isset($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
                $imageName = $this->storeImageFile($data['image'], $data['article'] ?? 'asset');
                $data['image'] = $imageName;
            }

            $members = [[
                'article' => $data['article'],
                'description' => $data['description'] ?? null,
                'model' => $data['model'] ?? null,
                'serial_number' => $data['serial_number'],
                'unit_value' => $data['unit_value'],
                'set_sequence' => ($data['unit_measure'] ?? 'Unit') === 'Set' ? 1 : null,
            ]];

            foreach ($data['set_items'] ?? [] as $index => $setItem) {
                $members[] = [
                    'article' => $setItem['article'],
                    'description' => $setItem['description'] ?? null,
                    'model' => $setItem['model'] ?? null,
                    'serial_number' => $setItem['serial_number'],
                    'unit_value' => $setItem['unit_value'],
                    'set_sequence' => $index + 2,
                ];
            }

            $createdAssets = [];
            foreach ($members as $member) {
                $accountGroup = $data['ppe_sub_major_account_group'] ?? null;
                $ledgerAccount = $data['general_ledger_account'] ?? null;
                $locationOffice = $data['location_office'] ?? null;
                $prefix = ($accountGroup === null && $ledgerAccount === null && $locationOffice === null)
                    ? 'HV'
                    : ($member['unit_value'] >= 50000 ? 'PPE' : ($member['unit_value'] >= 5000 ? 'HV' : 'LV'));
                $setSequence = $member['set_sequence'];

                $propertyNumber = implode('-', array_filter([
                    $prefix,
                    $year,
                    $accountGroup,
                    $ledgerAccount,
                    str_pad($baseSerial, 5, '0', STR_PAD_LEFT),
                    $setSequence === null ? null : str_pad($setSequence, 2, '0', STR_PAD_LEFT),
                    $locationOffice,
                ], static fn ($part) => $part !== null && $part !== ''));

                if ($accountGroup === null && $ledgerAccount === null && $locationOffice === null) {
                    $propertyNumber = $prefix . '-' . $year . '-' . date('m-d', strtotime($acquisitionDate)) . '-' . str_pad($baseSerial, 4, '0', STR_PAD_LEFT);
                }

                $asset = Asset::create([
                    'inventory_date' => now()->toDateString(),
                    'item_code' => $propertyNumber,
                    'barcode_id' => $propertyNumber,
                    'name' => $member['article'],
                    'article' => $member['article'],
                    'category' => $data['category'] ?? 'Assets',
                    'description' => $member['description'],
                    'model' => $member['model'],
                    'serial_number' => $member['serial_number'],
                    'acquisition_date' => $acquisitionDate,
                    'ppe_sub_major_account_group' => $data['ppe_sub_major_account_group'] ?? null,
                    'general_ledger_account' => $data['general_ledger_account'] ?? null,
                    'location_office' => $data['location_office'] ?? null,
                    'set_sequence' => $setSequence,
                    'unit_measure' => $data['unit_measure'] ?? 'Unit',
                    'person_accountable' => $data['person_accountable'] ?? null,
                    'validation_signatory' => $data['validation_signatory'] ?? null,
                    'supplier' => $data['supplier'] ?? null,
                    'unit_value' => $member['unit_value'],
                    'status' => $data['status'] ?? 'Serviceable',
                    'image' => $imageName
                ]);
                $createdAssets[] = $asset;

                Transaction::create([
                    'item_id' => $asset->id,
                    'item_type' => 'assets',
                    'transaction_type' => 'ADDED',
                    'quantity' => 1,
                    'supplier' => $data['supplier'] ?? null,
                    'transaction_date' => date('Y-m-d'),
                    'remarks' => 'Opening Balance / New Item',
                    'date_time' => now()
                ]);
            }

            $asset = $createdAssets[0];

            // Log activity
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'Created',
                'description' => count($createdAssets) > 1
                    ? "Added asset set: {$asset->article} (" . count($createdAssets) . ' items)'
                    : "Added new asset: {$asset->article}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            DB::commit();

            return $asset;
        } catch (\Exception $e) {
            DB::rollBack();

            if ($imageName && Storage::disk('public')->exists('assets/' . $imageName)) {
                Storage::disk('public')->delete('assets/' . $imageName);
            }

            throw $e;
        }
    }

    /**
     * Update an existing asset
     */
    public function update(Asset $asset, array $data): Asset
    {
        $oldImageName = $asset->image;
        $imageName = $oldImageName;
        $newImageStored = false;

        try {
            DB::beginTransaction();

            // Handle image update
            if (isset($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
                $imageName = $this->storeImageFile($data['image'], $data['article'] ?? $asset->article);
                $newImageStored = true;
                $data['image'] = $imageName;
            }

            $asset->update([
                'article' => $data['article'] ?? $asset->article,
                'category' => $data['category'] ?? $asset->category,
                'description' => $data['description'] ?? $asset->description,
                'model' => $data['model'] ?? $asset->model,
                'serial_number' => $data['serial_number'] ?? $asset->serial_number,
                'acquisition_date' => $data['acquisition_date'] ?? $asset->acquisition_date,
                'unit_measure' => $data['unit_measure'] ?? $asset->unit_measure,
                'person_accountable' => $data['person_accountable'] ?? $asset->person_accountable,
                'validation_signatory' => $data['validation_signatory'] ?? $asset->validation_signatory,
                'supplier' => $data['supplier'] ?? $asset->supplier,
                'unit_value' => $data['unit_value'] ?? $asset->unit_value,
                'status' => $data['status'] ?? $asset->status,
                'image' => $imageName
            ]);

            // Log activity
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'Updated',
                'description' => "Updated asset: {$asset->article}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            DB::commit();

            if ($newImageStored && $oldImageName && Storage::disk('public')->exists('assets/' . $oldImageName)) {
                Storage::disk('public')->delete('assets/' . $oldImageName);
            }

            return $asset;
        } catch (\Exception $e) {
            DB::rollBack();

            if ($newImageStored && $imageName && Storage::disk('public')->exists('assets/' . $imageName)) {
                Storage::disk('public')->delete('assets/' . $imageName);
            }

            throw $e;
        }
    }

    /**
     * Delete an asset
     */
    public function delete(Asset $asset): bool
    {
        try {
            DB::beginTransaction();

            if ($asset->image && Storage::disk('public')->exists('assets/' . $asset->image)) {
                Storage::disk('public')->delete('assets/' . $asset->image);
            }

            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'Deleted',
                'description' => "Deleted asset: {$asset->article}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            $asset->delete();

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get assignment information for an asset
     */
    public function getAssignmentInfo(Asset $asset): ?array
    {
        $activeCustody = AssetCustody::where('asset_id', $asset->id)
            ->whereNull('returned_at')
            ->latest('issued_at')
            ->first();

        if ($activeCustody) {
            return [
                'assigned_to' => $activeCustody->holder_name,
                'status' => $activeCustody->transaction_type,
                'request' => null,
            ];
        }

        if (AssetCustody::where('asset_id', $asset->id)->exists()) {
            return null;
        }

        $latestReq = IcsRequest::where('items_json', 'LIKE', '%"inv_no":"' . $asset->barcode_id . '"%')
            ->orderBy('created_at', 'desc')
            ->first();

        if ($latestReq) {
            $items = is_string($latestReq->items_json) ? json_decode($latestReq->items_json, true) : $latestReq->items_json;
            $asset_item = collect($items)->firstWhere('inv_no', $asset->barcode_id);
            
            return [
                'assigned_to' => ($asset_item['transfer_status'] ?? 'Active') === 'Active' ? $latestReq->sig_received_by_name : null,
                'status' => $asset_item['transfer_status'] ?? 'Active',
                'request' => $latestReq
            ];
        }

        return null;
    }

    /**
     * Helper to store image file
     */
    private function storeImageFile($file, string $prefix): string
    {
        $filename = time() . '_' . Str::slug($prefix) . '.' . $file->getClientOriginalExtension();
        $file->storeAs('assets', $filename, 'public');

        return $filename;
    }
}
