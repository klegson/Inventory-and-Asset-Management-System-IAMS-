<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RisRequest;
use App\Models\Supply;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RisController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 7);

        // STRICTLY EXCLUDE 'Pending Staff Review'
        // Prioritize 'Forwarded to Admin' at the top, then sort by newest date.
        $requests = RisRequest::where('status', '!=', 'Pending Staff Review')
                              ->orderByRaw("CASE WHEN status = 'Forwarded to Admin' THEN 1 ELSE 2 END")
                              ->orderBy('created_at', 'desc')
                              ->paginate($perPage);
                              
        return view('admin.ris.index', compact('requests', 'perPage'));
    }

    public function verify($id)
    {
        $req = RisRequest::with('items')->findOrFail($id);
        return view('admin.ris.verify_modal', compact('req'))->render();
    }

    public function update(Request $request, $id)
    {
        $ris = RisRequest::with('items')->findOrFail($id);
        $new_status = $request->new_status; // Grabs value from select dropdown OR direct button value
        
        // --- 1. AUTO-DEDUCT STOCKS IF NEWLY APPROVED ---
        if ($new_status == 'Approved' && $ris->status != 'Approved') {
            foreach ($ris->items as $item) {
                if ($item->issue_quantity > 0 && strtolower($item->stock_avail) == 'yes') {
                    
                    $supply = Supply::where('barcode_id', $item->stock_no)->first();
                    if (!$supply && !empty($item->description)) {
                         $supply = Supply::where('article', $item->description)->first();
                    }
                    
                    if ($supply) {
                        $supply->decrement('quantity', $item->issue_quantity); // Deduct Stock
                        
                        Transaction::create([
                            'item_id' => $supply->id,
                            'item_type' => 'supplies',
                            'transaction_type' => 'OUT',
                            'quantity' => $item->issue_quantity,
                            'supplier' => $supply->supplier,
                            'transaction_date' => now()->toDateString(),
                            'remarks' => 'RIS Auto-Release: ' . $ris->ris_no
                        ]);
                    }
                }
            }
        }

        // --- 2. AUTO-RESTORE STOCKS IF ADMIN REVOKES APPROVAL ---
        // If it was previously approved, and Admin is returning it to staff for corrections
        if ($new_status == 'Pending Staff Review' && $ris->status == 'Approved') {
            foreach ($ris->items as $item) {
                if ($item->issue_quantity > 0 && strtolower($item->stock_avail) == 'yes') {
                    
                    $supply = Supply::where('barcode_id', $item->stock_no)->first();
                    if (!$supply && !empty($item->description)) {
                         $supply = Supply::where('article', $item->description)->first();
                    }
                    
                    if ($supply) {
                        $supply->increment('quantity', $item->issue_quantity); // Give Stock Back
                        
                        Transaction::create([
                            'item_id' => $supply->id,
                            'item_type' => 'supplies',
                            'transaction_type' => 'IN',
                            'quantity' => $item->issue_quantity,
                            'supplier' => $supply->supplier,
                            'transaction_date' => now()->toDateString(),
                            'remarks' => 'RIS Revoked/Returned: ' . $ris->ris_no
                        ]);
                    }
                }
            }
        }

        // Update the status and handle the approval date appropriately
        $ris->update([
            'status' => $new_status,
            'date_approved' => $new_status == 'Approved' ? now()->toDateString() : ($new_status == 'Pending Staff Review' ? null : $ris->date_approved),
        ]);

        $msg = "RIS successfully updated to " . strtolower($new_status) . "!";
        return redirect('/admin/requests')->with('msg', $msg);
    }
}