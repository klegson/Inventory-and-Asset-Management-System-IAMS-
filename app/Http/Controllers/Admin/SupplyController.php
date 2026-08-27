<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supply;
use App\Models\Transaction;
use App\Models\PurchaseOrderItem;
use App\Models\ActivityLog;
use App\Services\SupplyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class SupplyController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        
        $query = Supply::select('supplies.*')
            ->selectRaw('(SELECT COALESCE(SUM(quantity), 0) FROM transactions WHERE transactions.item_id = supplies.id AND transactions.item_type = "supplies" AND transactions.transaction_type IN ("IN", "Added")) as total_input');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                                $q->where('article', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%");
            });
        }

        if ($request->filled('brand_filter') && $request->brand_filter !== 'All') {
            $query->where('brand', $request->brand_filter);
        }

        if ($request->filled('status_filter') && $request->status_filter !== 'All') {
            if ($request->status_filter === 'Available') {
                $query->whereColumn('quantity', '>', 'low_stock_threshold');
            } elseif ($request->status_filter === 'Low Stock') {
                $query->whereColumn('quantity', '<=', 'low_stock_threshold')
                      ->where('quantity', '>', 0);
            } elseif ($request->status_filter === 'Out of Stock') {
                $query->where('quantity', '<=', 0);
            }
        }

        $supplies = $query->orderBy('id', 'desc')->paginate($perPage);

        $brandOptions = Supply::whereNotNull('brand')->where('brand', '!=', '')->distinct()->orderBy('brand')->pluck('brand');

        // Sections (article) mapped to their existing classifications, for the multi-level supply dropdown
        $sections = Supply::whereNotNull('article')->where('article', '!=', '')
            ->orderBy('article')
            ->get(['article', 'classification'])
            ->groupBy('article')
            ->map(function ($group) {
                return $group->pluck('classification')->filter()->unique()->sort()->values();
            });

        $deliveredPoItems = collect();
        if (class_exists(PurchaseOrderItem::class)) {
            $existingSupplyDescriptions = Supply::pluck('description')->map(function($desc) {
                return strtolower(trim($desc));
            })->toArray();

            $rawPoItems = PurchaseOrderItem::with('purchaseOrder')
                ->whereHas('purchaseOrder', function($q) {
                    $q->where('po_type', 'Supply'); 
                })
                ->where('is_delivered', true)
                ->get();

            $deliveredPoItems = $rawPoItems->reject(function($item) use ($existingSupplyDescriptions) {
                return in_array(strtolower(trim($item->description)), $existingSupplyDescriptions);
            });
        }
        
        return view('admin.supplies.index', compact('supplies', 'perPage', 'deliveredPoItems', 'brandOptions', 'sections'));
    }

    public function store(Request $request)
    {
        $status = ($request->initial_quantity > 0) ? 'Available' : 'Out of Stock';

        if (!$request->has('force_save')) {
            $existing = Supply::where('article', trim($request->article))
                ->where('description', trim($request->description))
                ->where('unit_measure', trim($request->unit_measure))
                ->where('unit_value', $request->unit_value);
                
            if ($request->filled('supplier')) {
                $existing->where('supplier', trim($request->supplier));
            } else {
                $existing->where(function($q) {
                    $q->whereNull('supplier')->orWhere('supplier', '');
                });
            }

            if ($request->filled('brand')) {
                $existing->where('brand', trim($request->brand));
            } else {
                $existing->where(function($q) {
                    $q->whereNull('brand')->orWhere('brand', '');
                });
            }

            if ($request->filled('model')) {
                $existing->where('model', trim($request->model));
            } else {
                $existing->where(function($q) {
                    $q->whereNull('model')->orWhere('model', '');
                });
            }

            if ($request->filled('classification')) {
                $existing->where('classification', trim($request->classification));
            } else {
                $existing->where(function($q) {
                    $q->whereNull('classification')->orWhere('classification', '');
                });
            }

            $duplicate = $existing->first();

            if ($duplicate) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'status' => 'duplicate',
                        'existing_id' => $duplicate->id
                    ]);
                }
            }
        }

        $imageName = null;
        if ($request->hasFile('image')) {
            $imageName = time() . '.' . $request->image->extension();
            $request->image->storeAs('supplies', $imageName, 'public');
        }

        $supply = Supply::create([
            'article' => $request->article,
            'description' => $request->description,
            'brand' => $request->brand,
            'model' => $request->model,
            'classification' => $request->classification,
            'unit_measure' => $request->unit_measure,
            'unit_value' => $request->unit_value,
            'quantity' => $request->initial_quantity,
            'low_stock_threshold' => $request->low_stock_threshold ?? 10,
            'supplier' => $request->supplier,
            'status' => $status,
            'image' => $imageName
        ]);

        Transaction::create([
            'item_id' => $supply->id,
            'item_type' => 'supplies',
            'transaction_type' => 'Added',
            'quantity' => $request->initial_quantity ?? 0,
            'supplier' => $request->supplier,
            'transaction_date' => date('Y-m-d'),
            'remarks' => 'Opening Balance / New Item',
        ]);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'Created',
            'description' => "Added new supply item: {$supply->article}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['status' => 'success']);
        }

        return redirect('/admin/supplies')->with('msg', 'Supply successfully added!');
    }

    public function update(Request $request, $id)
    {
        $supply = Supply::findOrFail($id);
        
        $imageName = $supply->image;
        if ($request->hasFile('image')) {
            $oldImageName = $supply->image;
            $imageName = time() . '_' . bin2hex(random_bytes(8)) . '.' . $request->image->extension();
            $request->image->storeAs('supplies', $imageName, 'public');
        }

        $supply->update([
            'article' => $request->article,
            'description' => $request->description,
            'brand' => $request->brand,
            'model' => $request->model,
            'classification' => $request->classification,
            'unit_measure' => $request->unit_measure,
            'unit_value' => $request->unit_value,
            'quantity' => $request->quantity,
            'low_stock_threshold' => $request->low_stock_threshold ?? 10,
            'supplier' => $request->supplier,
            'status' => $request->status ?? 'Available',
            'image' => $imageName
        ]);

        if (isset($oldImageName) && $oldImageName && Storage::disk('public')->exists('supplies/' . $oldImageName)) {
            Storage::disk('public')->delete('supplies/' . $oldImageName);
        }

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'Updated',
            'description' => "Updated supply details: {$supply->article}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        return redirect('/admin/supplies')->with('msg', 'Supply successfully updated!');
    }

    public function destroy($id)
    {
        $supply = Supply::findOrFail($id);
        
        if ($supply->image && Storage::disk('public')->exists('supplies/' . $supply->image)) {
            Storage::disk('public')->delete('supplies/' . $supply->image);
        }
        
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'Deleted',
            'description' => "Deleted supply item: {$supply->article}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        $supply->delete();
        Transaction::where('item_id', $id)->where('item_type', 'supplies')->delete();

        return redirect('/admin/supplies')->with('msg', 'Supply successfully deleted!');
    }

    public function details($id)
    {
        $supply = Supply::find($id);

        if (!$supply) {
            return '<div class="p-4 text-center text-danger">Supply details not found.</div>';
        }

        $currentQty = intval($supply->quantity);
        $unitValue = floatval($supply->unit_value);
        $threshold = intval($supply->low_stock_threshold ?? 10);
        
        $formattedUnitValue = number_format($unitValue, 2);
        
        $totalInput = Transaction::where('item_id', $id)
                        ->where('item_type', 'supplies')
                        ->whereIn('transaction_type', ['IN', 'Added'])
                        ->sum('quantity');

        $totalInventory = max($totalInput, $currentQty);
        $formattedTotalValue = number_format($totalInventory * $unitValue, 2);

        $supplierName = !empty($supply->supplier) ? htmlspecialchars($supply->supplier) : 'N/A';
        $brandName = !empty($supply->brand) ? htmlspecialchars($supply->brand) : 'N/A';
        $modelName = !empty($supply->model) ? htmlspecialchars($supply->model) : 'N/A';

        $status_class = 'status-available bg-success text-white';
        $status_text = 'Available';
        $stockBarColor = 'bg-success';
        
        if ($currentQty == 0) {
            $status_class = 'status-out bg-danger text-white';
            $status_text = 'Out of Stock';
            $stockBarColor = 'bg-danger';
        } elseif ($currentQty <= $threshold) {
            $status_class = 'status-low bg-warning text-dark';
            $status_text = 'Low Stock';
            $stockBarColor = 'bg-warning';
        }

        $percentageLeft = $totalInventory > 0 ? round(($currentQty / $totalInventory) * 100) : 0;

        $imageHtml = '<i class="fas fa-image fa-2x text-muted"></i>';
        $lightboxHtml = '';
        
        if (!empty($supply->image) && file_exists(storage_path('app/public/supplies/' . $supply->image))) {
            $imageUrl = asset('storage/supplies/' . $supply->image);
            $imageHtml = '<img src="' . $imageUrl . '" alt="Supply Image" style="width: 100%; height: 100%; object-fit: cover; cursor: pointer; transition: transform 0.2s;" onclick="document.getElementById(\'lightbox-'.$id.'\').style.display=\'flex\'" onmouseover="this.style.transform=\'scale(1.05)\'" onmouseout="this.style.transform=\'scale(1)\'">';
            
            $lightboxHtml = '
            <div id="lightbox-'.$id.'" style="display:none; position:fixed; z-index:9999; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); align-items:center; justify-content:center; flex-direction:column; backdrop-filter: blur(5px);" onclick="this.style.display=\'none\'">
                <span style="position:absolute; top:20px; right:30px; color:white; font-size:40px; cursor:pointer; font-weight:bold;">&times;</span>
                <img src="' . $imageUrl . '" style="max-width:90%; max-height:85vh; border-radius:8px; box-shadow:0 5px 25px rgba(0,0,0,0.5);">
                <div class="text-white mt-3 fw-bold fs-5">'.htmlspecialchars($supply->article).'</div>
            </div>';
        }

        return <<<HTML
        {$lightboxHtml}
        <div class="modal-header d-block text-center border-0 p-3" style="background-color: #0b1c3f; border-top-left-radius: 10px; border-top-right-radius: 10px;">
            <h5 class="modal-title text-white fw-bold mb-0">Supply Overview</h5>
        </div>
        <div class="modal-body px-4 pt-4 pb-0">
            <div class="d-flex align-items-center mb-4">
                <div class="me-4 border rounded d-flex justify-content-center align-items-center bg-light shadow-sm overflow-hidden position-relative" style="width: 100px; height: 100px; flex-shrink: 0;" title="Click to enlarge image">{$imageHtml}</div>
                <div class="flex-grow-1"><div class="text-muted small text-uppercase tracking-wide mb-1" style="font-size: 0.75rem;">SUPPLY ITEM</div><div class="fw-bold text-dark">Consumable</div></div>
            </div>
            <div class="mb-4 bg-light rounded p-3 border">
                <div class="d-flex justify-content-between mb-1"><span class="text-muted small fw-bold text-uppercase">Inventory Status</span><span class="badge {$status_class}">{$status_text}</span></div>
                <div class="d-flex justify-content-between align-items-end mb-1 mt-2"><span class="fs-3 fw-bold text-dark" style="line-height: 1;">{$currentQty} <span class="fs-6 text-muted fw-normal">Remaining</span></span><span class="text-muted small fw-bold">/ {$totalInventory} Total</span></div>
                <div class="progress" style="height: 10px;"><div class="progress-bar {$stockBarColor}" role="progressbar" style="width: {$percentageLeft}%;" aria-valuenow="{$percentageLeft}" aria-valuemin="0" aria-valuemax="100"></div></div>
            </div>
            <div class="d-flex justify-content-between border-bottom py-2 mb-2"><span class="text-muted">Article:</span><span class="fw-bold text-dark">{$supply->article}</span></div>
            <div class="d-flex justify-content-between border-bottom py-2 mb-2"><span class="text-muted">Brand:</span><span class="fw-bold text-dark">{$brandName}</span></div>
            <div class="d-flex justify-content-between border-bottom py-2 mb-2"><span class="text-muted">Model:</span><span class="fw-bold text-dark">{$modelName}</span></div>
            <div class="d-flex justify-content-between border-bottom py-2 mb-2"><span class="text-muted">Description:</span><span class="fw-bold text-dark text-end" style="max-width: 65%;">{$supply->description}</span></div>
            <div class="d-flex justify-content-between border-bottom py-2 mb-2"><span class="text-muted">Unit Value:</span><span class="fw-bold text-dark">₱{$formattedUnitValue}</span></div>
            <div class="d-flex justify-content-between border-bottom py-2 mb-2"><span class="text-muted">Total Stock Value:</span><span class="fw-bold text-dark">₱{$formattedTotalValue}</span></div>
            <div class="d-flex justify-content-between border-bottom py-2 mb-2"><span class="text-muted">Low Stock Threshold:</span><span class="fw-bold text-dark">{$threshold}</span></div>
            <div class="d-flex justify-content-between border-bottom py-2 mb-4"><span class="text-muted">Supplier:</span><span class="fw-bold text-dark">{$supplierName}</span></div>
        </div>
        <div class="modal-footer border-0 pt-0 pb-4 px-4 justify-content-center bg-white"><button type="button" class="btn btn-outline-primary w-100 py-2 rounded-3 fw-bold" data-bs-dismiss="modal">Close Window</button></div>
HTML;
    }

    public function stockTransaction(Request $request, SupplyService $supplyService, $id)
    {
        $supply = Supply::findOrFail($id);

        try {
            $supplyService->processStockTransaction($supply, [
                'type' => $request->transaction_type,
                'quantity' => $request->qty,
                'supplier' => $request->supplier,
                'transaction_date' => $request->transaction_date,
                'remarks' => $request->remarks,
            ]);
        } catch (\InvalidArgumentException|\DomainException $e) {
            $message = str_starts_with($e->getMessage(), 'Insufficient stock')
                ? 'error_stock'
                : 'error_transaction';

            return redirect('/admin/supplies')->with('msg', $message);
        }

        return redirect('/admin/supplies')->with('msg', 'Supply stock updated successfully!');
    }
}