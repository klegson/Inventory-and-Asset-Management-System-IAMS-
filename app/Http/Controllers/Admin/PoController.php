<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Support\Facades\DB;

class PoController extends Controller
{
    // Load Table with Search & Filter
    public function index(Request $request)
    {
        $user_name = auth()->user() ? auth()->user()->firstname : 'Admin';
        
        $query = PurchaseOrder::with('items');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('po_no', 'like', "%{$search}%")
                  ->orWhere('supplier_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status_filter') && $request->status_filter !== 'All') {
            $query->where('status', $request->status_filter);
        }

        $sort = $request->input('sort', 'date_desc');
        if ($sort === 'supplier_asc') {
            $query->orderBy('supplier_name', 'asc');
        } elseif ($sort === 'supplier_desc') {
            $query->orderBy('supplier_name', 'desc');
        } elseif ($sort === 'date_asc') {
            $query->orderBy('po_date', 'asc'); 
        } else {
            $query->orderBy('po_date', 'desc');
        }

        $purchaseOrders = $query->get();
        return view('admin.po.index', compact('purchaseOrders', 'user_name'));
    }

    // Save a new PO
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            // AUTO CALCULATE STATUS BASED ON CHECKBOXES
            $totalItems = count($request->items ?? []);
            $deliveredItems = 0;
            foreach ($request->items ?? [] as $item) {
                if (!empty($item['is_delivered']) && ($item['is_delivered'] === true || $item['is_delivered'] === 'true')) {
                    $deliveredItems++;
                }
            }

            $calculatedStatus = 'Pending';
            if ($totalItems > 0) {
                if ($deliveredItems == 0) $calculatedStatus = 'Pending';
                elseif ($deliveredItems == $totalItems) $calculatedStatus = 'Complete';
                else $calculatedStatus = 'Partial';
            }

            $po = PurchaseOrder::create([
                'entity_name' => $request->entity_name,
                'po_no' => $request->po_no,
                'supplier_name' => $request->supplier_name,
                'supplier_address' => $request->supplier_address,
                'po_date' => $request->po_date,
                'procurement_mode' => $request->procurement_mode,
                
                'auth_official' => $request->auth_official,
                'auth_official_designation' => $request->auth_official_designation,
                
                'chief_accountant' => $request->chief_accountant,
                'chief_accountant_designation' => $request->chief_accountant_designation,
                
                'place_of_delivery' => $request->place_of_delivery,
                'date_of_delivery' => $request->date_of_delivery,
                'delivery_term' => $request->delivery_term,
                'payment_term' => $request->payment_term,
                'total_amount' => $request->total_amount,
                'status' => $calculatedStatus, 
            ]);

            foreach ($request->items as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'unit' => $item['unit'],
                    'description' => $item['description'],
                    'qty' => $item['qty'],
                    'unit_cost' => $item['cost'],
                    'amount' => $item['qty'] * $item['cost'],
                    'is_delivered' => (!empty($item['is_delivered']) && ($item['is_delivered'] === true || $item['is_delivered'] === 'true'))
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Purchase Order successfully created.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    // Fetch PO Data for Editing/Previewing
    public function show($id)
    {
        $po = PurchaseOrder::with('items')->findOrFail($id);
        return response()->json($po);
    }

    // Update existing PO
    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            // AUTO CALCULATE STATUS BASED ON CHECKBOXES
            $totalItems = count($request->items ?? []);
            $deliveredItems = 0;
            foreach ($request->items ?? [] as $item) {
                if (!empty($item['is_delivered']) && ($item['is_delivered'] === true || $item['is_delivered'] === 'true')) {
                    $deliveredItems++;
                }
            }

            $calculatedStatus = 'Pending';
            if ($totalItems > 0) {
                if ($deliveredItems == 0) $calculatedStatus = 'Pending';
                elseif ($deliveredItems == $totalItems) $calculatedStatus = 'Complete';
                else $calculatedStatus = 'Partial';
            }

            $po = PurchaseOrder::findOrFail($id);
            $po->update([
                'entity_name' => $request->entity_name,
                'po_no' => $request->po_no,
                'supplier_name' => $request->supplier_name,
                'supplier_address' => $request->supplier_address,
                'po_date' => $request->po_date,
                'procurement_mode' => $request->procurement_mode,
                
                'auth_official' => $request->auth_official,
                'auth_official_designation' => $request->auth_official_designation,
                
                'chief_accountant' => $request->chief_accountant,
                'chief_accountant_designation' => $request->chief_accountant_designation,
                
                'place_of_delivery' => $request->place_of_delivery,
                'date_of_delivery' => $request->date_of_delivery,
                'delivery_term' => $request->delivery_term,
                'payment_term' => $request->payment_term,
                'total_amount' => $request->total_amount,
                'status' => $calculatedStatus,
            ]);

            // Wipe old items and recreate new ones
            $po->items()->delete();

            foreach ($request->items as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'unit' => $item['unit'],
                    'description' => $item['description'],
                    'qty' => $item['qty'],
                    'unit_cost' => $item['cost'],
                    'amount' => $item['qty'] * $item['cost'],
                    'is_delivered' => (!empty($item['is_delivered']) && ($item['is_delivered'] === true || $item['is_delivered'] === 'true'))
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Purchase Order successfully updated.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        $po = PurchaseOrder::findOrFail($id);
        $po->delete(); 
        return redirect()->back()->with('success', 'Purchase Order Deleted');
    }
}