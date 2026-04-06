<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SupplyController extends Controller
{
    public function index(Request $request)
    {
        // Default to 5 items per page
        $perPage = $request->input('per_page', 10);
        $supplies = Supply::orderBy('id', 'desc')->paginate($perPage);
        
        return view('admin.supplies.index', compact('supplies', 'perPage'));
    }

    public function store(Request $request)
    {
        $status = ($request->initial_quantity > 0) ? 'Available' : 'Out of Stock';

        // Generate Unique Barcode (Format: SUP-YYYYMMDD-XXXX)
        $uniqueCode = strtoupper(Str::random(4));
        $generatedBarcode = 'SUP-' . date('Ymd') . '-' . $uniqueCode;

        // Handle Image Upload
        $imageName = null;
        if ($request->hasFile('image')) {
            $imageName = time() . '.' . $request->image->extension();
            $request->image->storeAs('public/supplies', $imageName);
        }

        Supply::create([
            'article' => $request->article,
            'description' => $request->description,
            'barcode_id' => $generatedBarcode, // Use the auto-generated barcode
            'unit_measure' => $request->unit_measure,
            'unit_value' => $request->unit_value,
            'quantity' => $request->initial_quantity,
            'supplier' => $request->supplier,
            'status' => $status,
            'image' => $imageName
        ]);

        return redirect('/admin/supplies')->with('msg', 'Supply successfully added with auto-generated barcode!');
    }

    public function update(Request $request, $id)
    {
        $supply = Supply::findOrFail($id);
        
        // Handle Image Update
        $imageName = $supply->image;
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($supply->image && Storage::exists('public/supplies/' . $supply->image)) {
                Storage::delete('public/supplies/' . $supply->image);
            }
            // Save new image
            $imageName = time() . '.' . $request->image->extension();
            $request->image->storeAs('public/supplies', $imageName);
        }

        $supply->update([
            'article' => $request->article,
            'description' => $request->description,
            'barcode_id' => $request->barcode_id,
            'unit_measure' => $request->unit_measure,
            'unit_value' => $request->unit_value,
            'supplier' => $request->supplier,
            'image' => $imageName
            // Note: Quantity is deliberately not updated here, matching the staff side
        ]);

        return redirect('/admin/supplies')->with('msg', 'Supply successfully updated!');
    }

    public function destroy($id)
    {
        $supply = Supply::findOrFail($id);
        
        // Delete associated image
        if ($supply->image && Storage::exists('public/supplies/' . $supply->image)) {
            Storage::delete('public/supplies/' . $supply->image);
        }
        
        $supply->delete();
        return redirect('/admin/supplies')->with('msg', 'Supply successfully deleted!');
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
            
            // The thumbnail inside the card (Now clickable with pointer cursor)
            $imageHtml = '<img src="' . $imageUrl . '" alt="Supply Image" style="width: 100%; height: 100%; object-fit: cover; cursor: pointer; transition: transform 0.2s;" onclick="document.getElementById(\'lightbox-'.$id.'\').style.display=\'flex\'" onmouseover="this.style.transform=\'scale(1.05)\'" onmouseout="this.style.transform=\'scale(1)\'">';
            
            // The hidden full-screen lightbox overlay
            $lightboxHtml = '
            <div id="lightbox-'.$id.'" style="display:none; position:fixed; z-index:9999; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); align-items:center; justify-content:center; flex-direction:column; backdrop-filter: blur(5px);" onclick="this.style.display=\'none\'">
                <span style="position:absolute; top:20px; right:30px; color:white; font-size:40px; cursor:pointer; font-weight:bold;">&times;</span>
                <img src="' . $imageUrl . '" style="max-width:90%; max-height:85vh; border-radius:8px; box-shadow:0 5px 25px rgba(0,0,0,0.5);">
                <div class="text-white mt-3 fw-bold fs-5">'.htmlspecialchars($supply->article).'</div>
            </div>';
        }

        // --- NEW: Generate Barcode Element ---
        $barcodeHtml = ($stockNo !== 'N/A') 
            ? '<svg id="barcode-modal-'.$id.'" class="barcode-render-modal" data-value="'.$stockNo.'"></svg>'
            : '<div class="fs-5 fw-bold text-dark">N/A</div>';

        // Return the HTML directly using Heredoc
        return <<<HTML
        {$lightboxHtml}
        
        <div class="modal-header d-block text-center border-0 p-3" style="background-color: #0b1c3f; border-top-left-radius: 10px; border-top-right-radius: 10px;">
            <h5 class="modal-title text-white fw-bold mb-0">Supply Details</h5>
        </div>
        
        <div class="modal-body px-4 pt-4 pb-0">
            <div class="d-flex align-items-center mb-4">
                <div class="me-4 border rounded d-flex justify-content-center align-items-center bg-light shadow-sm overflow-hidden position-relative" style="width: 100px; height: 100px; flex-shrink: 0;" title="Click to enlarge image">
                    {$imageHtml}
                </div>
                <div class="flex-grow-1">
                    <div class="text-muted small text-uppercase tracking-wide mb-1" style="font-size: 0.75rem;">STOCK ID:</div>
                    {$barcodeHtml}
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
}