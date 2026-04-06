<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Supply;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class BarcodeController extends Controller
{
    // --- VIEWS ---
    public function generator(Request $request)
    {
        $perPage = $request->input('per_page', 5);
        $page = $request->input('page', 1);
        
        // Grab search and category parameters from the URL
        $search = $request->input('search');
        $category = $request->input('category', 'all');

        $supplies = collect();
        $assets = collect();

        // 1. Fetch from Supplies (if category is 'all' or 'supply')
        if ($category === 'all' || $category === 'supply') {
            $supplyQuery = Supply::whereNotNull('barcode_id')
                ->select('id', 'barcode_id as barcode_code', 'article');
                
            // Apply Search Filter to Database
            if (!empty($search)) {
                $supplyQuery->where(function($q) use ($search) {
                    $q->where('article', 'LIKE', "%{$search}%")
                      ->orWhere('barcode_id', 'LIKE', "%{$search}%");
                });
            }

            $supplies = $supplyQuery->get()->map(function ($item) {
                $item->item_type = 'supply';
                $item->generated_at = null; 
                return $item;
            });
        }

        // 2. Fetch from Assets (if category is 'all' or 'asset')
        if ($category === 'all' || $category === 'asset') {
            $assetQuery = Asset::whereNotNull('barcode_id')
                ->select('id', 'barcode_id as barcode_code', 'article');
                
            // Apply Search Filter to Database
            if (!empty($search)) {
                $assetQuery->where(function($q) use ($search) {
                    $q->where('article', 'LIKE', "%{$search}%")
                      ->orWhere('barcode_id', 'LIKE', "%{$search}%");
                });
            }

            $assets = $assetQuery->get()->map(function ($item) {
                $item->item_type = 'asset';
                $item->generated_at = null; 
                return $item;
            });
        }

        // 3. Merge both lists and sort by ID descending (highest ID = newest added)
        $mergedBarcodes = $supplies->concat($assets)->sortByDesc('id')->values();

        // 4. Manually Paginate the merged collection
        $offset = ($page * $perPage) - $perPage;
        $itemsForCurrentPage = $mergedBarcodes->slice($offset, $perPage)->all();
        
        $barcodes = new LengthAwarePaginator(
            $itemsForCurrentPage, 
            $mergedBarcodes->count(), 
            $perPage, 
            $page, 
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Pass search and category variables back to the view so the inputs don't clear out
        return view('barcodes.generator', compact('barcodes', 'perPage', 'search', 'category'));
    }

    // --- AJAX / API ENDPOINTS ---
    
    // Process Scanner Input (STRICTLY SEPARATED)
    public function processScan(Request $request)
    {
        $barcode = trim($request->barcode);
        $qty = intval($request->qty);
        $mode = strtoupper($request->mode);
        $context = strtolower(trim($request->context ?? 'all'));
        $risNumber = trim($request->ris_number);

        $item = null;
        $table = '';

        // STRICT SEPARATION: Only search the database requested by the scanner context
        if ($context === 'supplies') {
            $item = Supply::where('barcode_id', $barcode)->first();
            $table = 'supplies';
        } elseif ($context === 'assets') {
            $item = Asset::where('barcode_id', $barcode)->first();
            $table = 'assets';
        } else {
            // Global fallback
            $item = Asset::where('barcode_id', $barcode)->first();
            $table = 'assets';
            if (!$item) {
                $item = Supply::where('barcode_id', $barcode)->first();
                $table = 'supplies';
            }
        }

        if ($item) {
            $new_stock = ($mode == 'IN') ? ($item->quantity + $qty) : ($item->quantity - $qty);

            if ($new_stock < 0) {
                return response()->json(['status' => 'error', 'message' => 'Insufficient Stock Available']);
            }

            $item->update(['quantity' => $new_stock]);

            $remarks = ($mode == 'OUT' && !empty($risNumber)) ? 'RIS: ' . $risNumber : 'Scanner';

            Transaction::create([
                'item_id' => $item->id,
                'item_type' => $table,
                'transaction_type' => $mode,
                'quantity' => $qty,
                'transaction_date' => date('Y-m-d'),
                'remarks' => $remarks
            ]);

            return response()->json([
                'status' => 'success',
                'item_name' => $item->article,
                'new_stock' => $new_stock,
                'barcode' => $barcode,
                'mode' => $mode,
                'qty' => $qty
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'Barcode not found in ' . ucfirst($context) . ' Inventory.']);
    }

    // Fetch Recent Scans for Modal
    public function recentScans(Request $request)
    {
        $context = $request->context ?? 'all';
        
        // Grab recent IN/OUT transactions
        $query = Transaction::where(function($q) {
            $q->where('remarks', 'Scanner')->orWhere('remarks', 'LIKE', 'RIS:%');
        })->orderBy('id', 'desc')->limit(10);

        if ($context == 'assets') {
            $query->where('item_type', 'assets');
        } elseif ($context == 'supplies') {
            $query->where('item_type', 'supplies');
        }

        $transactions = $query->get();
        $html = '';

        if ($transactions->count() > 0) {
            foreach ($transactions as $t) {
                $mode = strtoupper($t->transaction_type);
                $color = ($mode == 'IN') ? 'success' : 'danger';
                
                $itemName = 'Deleted Item';
                $barcode = 'N/A';
                
                if ($t->item_type == 'assets') {
                    $asset = Asset::find($t->item_id);
                    if ($asset) { $itemName = $asset->article; $barcode = $asset->barcode_id; }
                } else {
                    $supply = Supply::find($t->item_id);
                    if ($supply) { $itemName = $supply->article; $barcode = $supply->barcode_id; }
                }

                $html .= '<div class="list-group-item d-flex justify-content-between align-items-center bg-'.$color.' bg-opacity-10 border-start border-'.$color.' border-4 mb-2 shadow-sm rounded">
                            <div><div class="fw-bold text-dark">'.$itemName.'</div><small class="text-muted">'.$barcode.'</small></div>
                            <div class="text-end"><span class="badge bg-'.$color.'">'.$mode.' '.$t->quantity.'</span></div>
                          </div>';
            }
        }
        return response($html);
    }
}