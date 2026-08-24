<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Transaction;
use App\Models\PurchaseOrderItem; 
use App\Models\IcsRequest;
use App\Models\ActivityLog;
use App\Models\AssetCustody;
use App\Services\AssetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class AssetController extends Controller
{
    public function __construct(private AssetService $assetService)
    {
    }

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 5);
        $search = $request->input('search');
        $statusFilter = $request->input('status_filter', 'All');

        $query = Asset::query();

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('article', 'LIKE', "%{$search}%")
                  ->orWhere('barcode_id', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        if ($statusFilter !== 'All') {
            $query->where('status', $statusFilter);
        }

        $assets = $query->orderBy('id', 'desc')->paginate($perPage);

        $assets->getCollection()->transform(function ($asset) {
            $latestReq = IcsRequest::where('items_json', 'LIKE', '%"inv_no":"'.$asset->barcode_id.'"%')
                ->orderBy('created_at', 'desc')
                ->first();
                
            if ($latestReq) {
                $items = is_string($latestReq->items_json) ? json_decode($latestReq->items_json, true) : $latestReq->items_json;
                $asset_item = collect($items)->firstWhere('inv_no', $asset->barcode_id);
                $status = $asset_item['transfer_status'] ?? 'Active';
                
                $asset->assigned_to = ($status === 'Active') ? $latestReq->sig_received_by_name : null;
            } else {
                $asset->assigned_to = null;
            }
            return $asset;
        });

        $deliveredPoItems = collect();
        if (class_exists(PurchaseOrderItem::class)) {
            $existingAssetDescriptions = Asset::pluck('description')->map(function($desc) {
                return strtolower(trim($desc));
            });

            $rawPoItems = PurchaseOrderItem::with('purchaseOrder')
                ->whereHas('purchaseOrder', function($q) {
                    $q->where('po_type', 'Asset'); 
                })
                ->where('is_delivered', true)
                ->get();

            $deliveredPoItems = $rawPoItems->reject(function($item) use ($existingAssetDescriptions) {
                return in_array(strtolower(trim($item->description)), $existingAssetDescriptions->toArray());
            });
        }

        return view('admin.assets.index', compact('assets', 'perPage', 'deliveredPoItems'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'article' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'model' => ['nullable', 'string', 'max:255'],
            'serial_number' => ['required', 'string', 'max:255'],
            'acquisition_date' => ['required', 'date'],
            'unit_value' => ['required', 'numeric', 'min:0'],
            'unit_measure' => ['nullable', 'string', 'max:255'],
            'ppe_sub_major_account_group' => ['nullable', 'digits:2'],
            'general_ledger_account' => ['nullable', 'digits:2'],
            'location_office' => ['nullable', 'digits:2'],
            'set_items' => ['nullable', 'array'],
            'set_items.*.article' => ['required_with:set_items', 'string', 'max:255'],
            'set_items.*.description' => ['nullable', 'string'],
            'set_items.*.model' => ['nullable', 'string', 'max:255'],
            'set_items.*.serial_number' => ['required_with:set_items', 'string', 'max:255'],
            'set_items.*.unit_value' => ['required_with:set_items', 'numeric', 'min:0'],
            'person_accountable' => ['nullable', 'string', 'max:255'],
            'validation_signatory' => ['nullable', 'string'],
            'supplier' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:5120'],
        ]);

        $asset = $this->assetService->create([
            ...$validated,
            'image' => $request->file('image'),
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['status' => 'success']);
        }

        return redirect('/admin/assets')->with('msg', 'saved');
    }

    public function update(Request $request, $id)
    {
        $asset = Asset::findOrFail($id);
        $validated = $request->validate([
            'article' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'model' => ['nullable', 'string', 'max:255'],
            'serial_number' => ['required', 'string', 'max:255'],
            'acquisition_date' => ['required', 'date'],
            'unit_value' => ['required', 'numeric', 'min:0'],
            'unit_measure' => ['required', 'string', 'max:255'],
            'person_accountable' => ['nullable', 'string', 'max:255'],
            'validation_signatory' => ['nullable', 'string'],
            'supplier' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:5120'],
        ]);

        $asset = $this->assetService->update($asset, [
            ...$validated,
            'image' => $request->file('image'),
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['status' => 'success']);
        }

        return redirect('/admin/assets')->with('msg', 'saved');
    }

    public function destroy($id)
    {
        $asset = Asset::findOrFail($id);
        
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'Deleted',
            'description' => "Deleted asset: {$asset->article}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        $asset->delete();
        Transaction::where('item_id', $id)->where('item_type', 'assets')->delete();

        return redirect('/admin/assets')->with('msg', 'deleted');
    }

    public function details($id)
    {
        $asset = Asset::findOrFail($id);
        $custodyHistory = AssetCustody::where('asset_id', $asset->id)
            ->latest('issued_at')
            ->latest('id')
            ->get();
        $activeCustody = $custodyHistory->firstWhere('returned_at', null);
        
        $allAssignments = IcsRequest::where('items_json', 'LIKE', '%"inv_no":"'.$asset->barcode_id.'"%')
            ->orderBy('created_at', 'desc')
            ->get();
            
        $activeAssignment = null;
        $previousOwner = '<span class="text-muted">None</span>';
        $assignedTo = '<span class="text-muted">None</span>';
        $dateOfInventory = '<span class="text-muted">N/A</span>';

        $foundCurrentState = false;

        foreach ($allAssignments as $req) {
            $items = is_string($req->items_json) ? json_decode($req->items_json, true) : $req->items_json;
            $asset_item = collect($items)->firstWhere('inv_no', $asset->barcode_id);
            if (!$asset_item) continue;

            $transferStatus = $asset_item['transfer_status'] ?? 'Active';

            if (!$foundCurrentState) {
                $foundCurrentState = true;
                if ($transferStatus === 'Active') {
                    $activeAssignment = $req;
                    $assignedTo = $req->sig_received_by_name ?: 'Unknown';
                    $dateOfInventory = $asset_item['est_life'] ?? $req->created_at->format('M d, Y');
                } else {
                    $previousOwner = $req->sig_received_by_name ?: 'Unknown';
                }
            } else {
                if ($previousOwner === '<span class="text-muted">None</span>') {
                    $previousOwner = $req->sig_received_by_name ?: 'Unknown';
                }
            }
        }

        if ($custodyHistory->isNotEmpty()) {
            $previousCustody = $custodyHistory->first(fn ($custody) => $custody->returned_at !== null);
            $previousOwner = $previousCustody ? e($previousCustody->holder_name ?: 'Unknown') : '<span class="text-muted">None</span>';

            if ($activeCustody) {
                $activeAssignment = $activeCustody;
                $assignedTo = e($activeCustody->holder_name ?: 'Unknown');
                $dateOfInventory = $activeCustody->issued_at?->format('M d, Y') ?: 'N/A';
            } else {
                $assignedTo = '<span class="text-muted">In inventory</span>';
            }
        }

        $imageHtml = $asset->image 
            ? '<img src="'.asset('storage/assets/'.$asset->image).'" class="img-fluid rounded shadow-sm" style="max-height: 250px; object-fit: cover;">'
            : '<div class="bg-light rounded d-flex align-items-center justify-content-center border" style="height: 200px;"><i class="fas fa-laptop fa-4x text-muted opacity-25"></i></div>';
            
        if ($asset->status != 'Serviceable') {
            $statusBadge = '<span class="badge bg-danger px-3 py-2 fs-6">'.$asset->status.'</span>';
        } elseif ($activeAssignment) {
            $statusBadge = '<span class="badge px-3 py-2 fs-6 shadow-sm" style="background-color: #101954; color: white;"><i class="fas fa-user-check me-1"></i> Assigned</span>';
        } else {
            $statusBadge = '<span class="badge bg-success px-3 py-2 fs-6 shadow-sm"><i class="fas fa-check-circle me-1"></i> Available</span>';
        }

        $html = '
        <div class="modal-header bg-primary text-white border-0">
            <h5 class="modal-title fw-bold"><i class="fas fa-info-circle me-2"></i> Asset Details</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
            <div class="row align-items-center mb-4">
                <div class="col-md-5 text-center mb-3 mb-md-0">
                    '.$imageHtml.'
                </div>
                <div class="col-md-7">
                    <h3 class="fw-bold text-dark mb-1">'.$asset->article.'</h3>
                    <p class="text-muted mb-3">'.$asset->description.'</p>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        '.$statusBadge.'
                    </div>
                </div>
            </div>
            
            <div class="bg-light p-3 rounded text-center border mb-3">
                <span class="text-muted d-block small fw-bold text-uppercase mb-2">Property No. (QR Code)</span>
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data='.urlencode($asset->barcode_id).'" style="width: 120px; height: 120px;">
                <div class="font-monospace fw-bold mt-1 fs-5" style="letter-spacing: 2px; color: #101954;">'.$asset->barcode_id.'</div>
            </div>

            <div class="row g-3">
                <div class="col-6">
                    <div class="border rounded p-3 bg-white h-100 shadow-sm border-start border-4 border-primary">
                        <small class="text-muted d-block text-uppercase fw-bold mb-1" style="font-size: 0.7rem;">Person Accountable</small>
                        <span class="fw-semibold text-dark">'.$assignedTo.'</span>
                    </div>
                </div>
                <div class="col-6">
                    <div class="border rounded p-3 bg-white h-100 shadow-sm border-start border-4 border-secondary">
                        <small class="text-muted d-block text-uppercase fw-bold mb-1" style="font-size: 0.7rem;">Previous Holder</small>
                        <span class="fw-semibold text-dark">'.$previousOwner.'</span>
                    </div>
                </div>
                <div class="col-6">
                    <div class="border rounded p-3 bg-white h-100 shadow-sm border-start border-4 border-warning">
                        <small class="text-muted d-block text-uppercase fw-bold mb-1" style="font-size: 0.7rem;">Issued / Inventory Date</small>
                        <span class="fw-semibold text-dark">'.$dateOfInventory.'</span>
                    </div>
                </div>
                <div class="col-6">
                    <div class="border rounded p-3 bg-white h-100 shadow-sm">
                        <small class="text-muted d-block text-uppercase fw-bold mb-1" style="font-size: 0.7rem;">Supplier</small>
                        <span class="fw-semibold text-dark">'.($asset->supplier ?: 'N/A').'</span>
                    </div>
                </div>
                <div class="col-12">
                    <div class="border rounded p-3 bg-white h-100 shadow-sm">
                        <small class="text-muted d-block text-uppercase fw-bold mb-1" style="font-size: 0.7rem;">Unit Measure</small>
                        <span class="fw-semibold text-dark">'.($asset->unit_measure ?: 'N/A').'</span>
                    </div>
                </div>
                <div class="col-12">
                    <div class="border rounded p-3 bg-light shadow-sm d-flex justify-content-between align-items-center mt-2">
                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem;">Unit Value</small>
                        <span class="fw-bold text-success fs-4">₱ '.number_format($asset->unit_value, 2).'</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer bg-light border-0">
            <button type="button" class="btn btn-outline-primary px-4 fw-bold" onclick="openCustodyHistory('.$asset->id.')"><i class="fas fa-clock-rotate-left me-1"></i> Borrowing & Transfer History</button>
            <a href="'.url('/admin/assets/'.$asset->id.'/print-slip').'" target="_blank" rel="noopener" class="btn btn-primary px-4 fw-bold"><i class="fas fa-print me-1"></i> Print Asset Slip</a>
            <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Close</button>
        </div>';

        return response($html);
    }

    public function printSlip($id)
    {
        return view('assets.print-slip', [
            'asset' => Asset::findOrFail($id),
        ]);
    }

    public function custodyHistory($id)
    {
        return view('assets.custody-history', [
            'asset' => Asset::findOrFail($id),
            'history' => AssetCustody::where('asset_id', $id)->latest('issued_at')->latest('id')->get(),
        ]);
    }

    public function updateScanStatus(Request $request)
    {
        $asset = Asset::where('barcode_id', $request->barcode_id)->first();

        if (!$asset) {
            return response()->json(['status' => 'error', 'message' => 'Asset not found. Please check the barcode.']);
        }

        $newStatus = $request->status; 
        $asset->update(['status' => $newStatus]);

        $txType = $newStatus == 'Serviceable' ? 'IN' : 'OUT';
        $remarks = $newStatus == 'Serviceable' ? 'Returned (Serviceable)' : 'Marked as Defective/Unserviceable';

        if ($newStatus == 'Serviceable') {
            $activeAssignment = IcsRequest::where('items_json', 'LIKE', '%"inv_no":"'.$asset->barcode_id.'"%')
                ->orderBy('created_at', 'desc')
                ->first();

            if ($activeAssignment) {
                $itemsArray = is_string($activeAssignment->items_json) ? json_decode($activeAssignment->items_json, true) : $activeAssignment->items_json;
                $asset_item = collect($itemsArray)->firstWhere('inv_no', $asset->barcode_id);
                
                if (($asset_item['transfer_status'] ?? 'Active') === 'Active') {
                    foreach ($itemsArray as &$item) {
                        if (isset($item['inv_no']) && $item['inv_no'] == $asset->barcode_id) {
                            $item['transfer_status'] = 'Returned to Inventory';
                        }
                    }
                    $activeAssignment->update(['items_json' => $itemsArray]);
                }
            }
        }

        Transaction::create([
            'item_id' => $asset->id,
            'item_type' => 'assets',
            'transaction_type' => $txType,
            'quantity' => 1, 
            'transaction_date' => date('Y-m-d'),
            'remarks' => $remarks,
            'date_time' => now()
        ]);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'Updated',
            'description' => "Scanner updated asset status: {$asset->article} to {$newStatus}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "{$asset->article} has been updated to {$newStatus}.",
            'asset_name' => $asset->article,
            'new_state' => $newStatus
        ]);
    }
}