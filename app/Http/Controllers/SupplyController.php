<?php

namespace App\Http\Controllers;

use App\Models\Supply;
use App\Models\Transaction;
use Illuminate\Http\Request;

class SupplyController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        // Fetch the data paginated
        $supplies = Supply::orderBy('id', 'desc')->paginate($perPage);
        
        return view('supplies.index', compact('supplies', 'perPage'));
    }

    public function store(Request $request)
    {
        $imageName = null;

        // Check if a file was uploaded
        if ($request->hasFile('image')) {
            $imageName = time() . '_' . $request->file('image')->getClientOriginalName();
            $request->file('image')->storeAs('supplies', $imageName, 'public');
        }

        // AUTO-GENERATE BARCODE: SUP-YYYY-MM-XXXX
        $yearMonth = date('Y-m'); // e.g., 2026-02
        
        // Count how many supplies were created this month to generate the sequence
        $countThisMonth = Supply::where('barcode_id', 'like', 'SUP-' . $yearMonth . '-%')->count();
        $sequenceNumber = str_pad($countThisMonth + 1, 4, '0', STR_PAD_LEFT); // e.g., 0001
        
        $generatedBarcode = 'SUP-' . $yearMonth . '-' . $sequenceNumber;

        // Failsafe: Just in case that exact barcode exists (e.g. if a previous one was manually altered), 
        // keep incrementing the sequence until we find an available one.
        while (Supply::where('barcode_id', $generatedBarcode)->exists()) {
            $countThisMonth++;
            $sequenceNumber = str_pad($countThisMonth + 1, 4, '0', STR_PAD_LEFT);
            $generatedBarcode = 'SUP-' . $yearMonth . '-' . $sequenceNumber;
        }

        $supply = Supply::create([
            'barcode_id' => $generatedBarcode, // Use the auto-generated sequence barcode
            'article' => $request->article,
            'description' => $request->description,
            'supplier' => $request->supplier,
            'unit_measure' => $request->unit_measure,
            'unit_value' => $request->unit_value,
            'status' => $request->status ?? 'Available',
            'quantity' => $request->initial_quantity ?? 0,
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

        return redirect('/supplies')->with('msg', 'saved');
    }

    public function details($id)
    {
        $supply = Supply::find($id);

        if (!$supply) {
            return '<div class="p-4 text-center text-danger">Supply details not found.</div>';
        }

        $stockNo = !empty($supply->barcode_id) ? $supply->barcode_id : 'N/A';
        $qty = intval($supply->quantity);
        $unitValue = floatval($supply->unit_value);
        
        $formattedUnitValue = number_format($unitValue, 2);
        $formattedTotalValue = number_format($qty * $unitValue, 2);
        $supplierName = !empty($supply->supplier) ? htmlspecialchars($supply->supplier) : 'N/A';

        $status_class = 'status-available';
        $status_text = 'Available';
        if ($qty == 0) {
            $status_class = 'status-out text-danger';
            $status_text = 'Out of Stock';
        } elseif ($qty < 10) {
            $status_class = 'status-low text-warning';
            $status_text = 'Low Stock';
        }

        // Handle image path and Lightbox Logic
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

        // Generate Visual Barcode Graphic via Free API
        $barcodeVisual = '';
        if ($stockNo !== 'N/A') {
            $barcodeVisual = '<div class="mt-2"><img src="https://bwipjs-api.metafloor.com/?bcid=code128&text=' . urlencode($stockNo) . '&scale=2&height=10&includetext=true" alt="Barcode Graphic" style="max-width: 100%; mix-blend-mode: multiply;"></div>';
        }

        // Return the HTML directly using Heredoc
        return <<<HTML
        {$lightboxHtml}
        
        <div class="modal-header d-block text-center border-0 p-3" style="background-color: #0b1c3f; border-top-left-radius: 10px; border-top-right-radius: 10px;">
            <h5 class="modal-title text-white fw-bold mb-0">Supply Details</h5>
        </div>
        
        <div class="modal-body px-4 pt-4 pb-0">
            <div class="d-flex align-items-center mb-4">
                <div class="me-3 border rounded d-flex justify-content-center align-items-center bg-light shadow-sm overflow-hidden position-relative" style="width: 80px; height: 80px;" title="Click to enlarge image">
                    {$imageHtml}
                </div>
                <div>
                    <div class="text-muted small text-uppercase tracking-wide" style="font-size: 0.75rem;">STOCK ID:</div>
                    {$barcodeVisual}
                </div>
            </div>

            <div class="d-flex justify-content-between border-bottom py-2 mb-2">
                <span class="text-muted">Article:</span>
                <span class="fw-bold text-dark">{$supply->article}</span>
            </div>
            
            <div class="d-flex justify-content-between border-bottom py-2 mb-2">
                <span class="text-muted">Description:</span>
                <span class="fw-bold text-dark">{$supply->description}</span>
            </div>
            
            <div class="d-flex justify-content-between border-bottom py-2 mb-2">
                <span class="text-muted">Quantity:</span>
                <span class="fw-bold text-dark">{$qty} {$supply->unit_measure}</span>
            </div>
            
            <div class="d-flex justify-content-between border-bottom py-2 mb-2">
                <span class="text-muted">Unit Value:</span>
                <span class="fw-bold text-dark">₱{$formattedUnitValue}</span>
            </div>
            
            <div class="d-flex justify-content-between border-bottom py-2 mb-2">
                <span class="text-muted">Total Value:</span>
                <span class="fw-bold text-dark">₱{$formattedTotalValue}</span>
            </div>
            
            <div class="d-flex justify-content-between border-bottom py-2 mb-2">
                <span class="text-muted">Supplier:</span>
                <span class="fw-bold text-dark">{$supplierName}</span>
            </div>
            
            <div class="d-flex justify-content-between border-bottom py-3 mb-4">
                <span class="text-muted mt-1">Status:</span>
                <span class="badge {$status_class} rounded-pill px-3 py-2 border">{$status_text}</span>
            </div>
        </div>
        
        <div class="modal-footer border-0 pt-0 pb-4 px-4 justify-content-center">
            <button type="button" class="btn btn-outline-primary w-100 py-2 rounded-3" data-bs-dismiss="modal">Close</button>
        </div>
HTML;
    }

    public function update(Request $request, $id)
    {
        $supply = Supply::findOrFail($id);
        
        $dataToUpdate = [
            'barcode_id' => $request->barcode_id,
            'article' => $request->article,
            'description' => $request->description,
            'supplier' => $request->supplier,
            'unit_measure' => $request->unit_measure,
            'unit_value' => $request->unit_value,
            'quantity' => $request->quantity, // Automatically updates the quantity in the database!
            'status' => $request->status ?? 'Available',
        ];

        // Check if a new image was uploaded during edit
        if ($request->hasFile('image')) {
            $imageName = time() . '_' . $request->file('image')->getClientOriginalName();
            $request->file('image')->storeAs('supplies', $imageName, 'public');
            $dataToUpdate['image'] = $imageName; // Overwrite the old image with the new one
        }

        $supply->update($dataToUpdate);

        return redirect('/supplies')->with('msg', 'saved');
    }

    public function destroy($id)
    {
        $supply = Supply::findOrFail($id);
        $supply->delete();

        Transaction::where('item_id', $id)->where('item_type', 'supplies')->delete();

        return redirect('/supplies')->with('msg', 'deleted');
    }

    public function stockTransaction(Request $request, $id)
    {
        $supply = Supply::findOrFail($id);
        $qty = $request->qty;
        $type = $request->transaction_type;

        if ($type == 'IN') {
            $supply->increment('quantity', $qty);
        } elseif ($type == 'OUT') {
            if ($supply->quantity >= $qty) {
                $supply->decrement('quantity', $qty);
            } else {
                return redirect('/supplies')->with('msg', 'error_stock');
            }
        }

        Transaction::create([
            'item_id' => $id,
            'item_type' => 'supplies',
            'transaction_type' => $type,
            'quantity' => $qty,
            'supplier' => $request->supplier,
            'transaction_date' => $request->transaction_date,
            'remarks' => $request->remarks,
        ]);

        return redirect('/supplies')->with('msg', 'success');
    }
}