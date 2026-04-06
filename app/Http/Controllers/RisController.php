<?php

namespace App\Http\Controllers;

use App\Models\RisRequest;
use App\Models\RisItem;
use App\Models\Supply; 
use Illuminate\Http\Request;

class RisController extends Controller
{
    public function index()
    {
        $requests = RisRequest::orderByRaw("FIELD(status, 'Pending Staff Review', 'Forwarded to Admin', 'Approved', 'Declined', 'Rejected', 'Cancelled') DESC")
                              ->orderBy('created_at', 'desc')
                              ->get();
                              
        return view('ris.index', compact('requests'));
    }

    public function review($id)
    {
        $req = RisRequest::with('items')->findOrFail($id);
        
        // Loop through and attach the live inventory stock using the unique Barcode!
        foreach ($req->items as $item) {
            $supply = Supply::where('barcode_id', $item->stock_no)->first();
            $item->current_stock = $supply ? $supply->quantity : 0;
        }

        return view('ris.review', compact('req'));
    }

    public function update(Request $request, $id)
    {
        $ris = RisRequest::findOrFail($id);

        $status = $ris->status; // Default to current status
        $msg = 'updated'; // Default message

        if ($request->action == 'forward') {
            $status = 'Forwarded to Admin';
            $msg = 'forwarded';
        } elseif ($request->action == 'return') {
            $status = 'Pending Staff Review'; // Revert back to staff
            $msg = 'returned';
        } else {
            // Save action - only set to pending if it hasn't been approved yet
            if ($ris->status != 'Approved') {
                $status = 'Pending Staff Review';
            }
        }

        $ris->update([
            'entity_name' => $request->entity_name,
            'division' => $request->division,
            'office' => $request->office,
            'fund_cluster' => $request->fund_cluster,
            'rcc' => $request->rcc,
            'purpose' => $request->purpose,
            
            'sig_requested_by' => $request->sig_requested_by,
            'sig_approved_by' => $request->sig_approved_by,
            'sig_issued_by' => $request->sig_issued_by,
            'sig_received_by' => $request->sig_received_by,
            
            'desig_requested' => $request->desig_requested,
            'desig_approved' => $request->desig_approved,
            'desig_issued' => $request->desig_issued,
            'desig_received' => $request->desig_received,
            
            'date_requested' => $request->date_requested ?: null,
            'date_approved' => $request->date_approved ?: null,
            'date_issued' => $request->date_issued ?: null,
            'date_received' => $request->date_received ?: null,
            
            'status' => $status
        ]);

        if ($request->has('item_id')) {
            $itemCount = count($request->item_id);
            for ($i = 0; $i < $itemCount; $i++) {
                $itemId = $request->item_id[$i] ?? 0;
                $stockNo = $request->stock_no[$i] ?? null;
                $desc = $request->description[$i] ?? null;
                $avail = $request->input("stock_avail_$i");

                if (!empty($stockNo) || !empty($desc)) {
                    $itemData = [
                        'ris_id' => $ris->id,
                        'stock_no' => $stockNo,
                        'unit' => $request->unit[$i] ?? null,
                        'description' => $desc,
                        'req_quantity' => $request->req_quantity[$i] ?? null,
                        'stock_avail' => $avail,
                        'issue_quantity' => $request->issue_quantity[$i] ?? null,
                        'remarks' => $request->remarks[$i] ?? null,
                    ];

                    if ($itemId > 0) {
                        RisItem::where('id', $itemId)->update($itemData);
                    } else {
                        RisItem::create($itemData);
                    }
                }
            }
        }

        return redirect('/ris')->with('msg', $msg);
    }
}