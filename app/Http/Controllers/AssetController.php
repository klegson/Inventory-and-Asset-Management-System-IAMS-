<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Transaction;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function index()
    {
        $assets = Asset::orderBy('id', 'desc')->get();
        return view('assets.index', compact('assets'));
    }

    public function store(Request $request)
    {
        $asset = Asset::create([
            'barcode_id' => $request->barcode_id,
            'article' => $request->article,
            'description' => $request->description,
            'unit_measure' => $request->unit_measure,
            'supplier' => $request->supplier,
            'unit_value' => $request->unit_value,
            'status' => $request->status,
            'quantity' => $request->initial_quantity ?? 0,
        ]);

        Transaction::create([
            'item_id' => $asset->id,
            'item_type' => 'assets',
            'transaction_type' => 'Added',
            'quantity' => $request->initial_quantity ?? 0,
            'supplier' => $request->supplier,
            'transaction_date' => date('Y-m-d'),
            'remarks' => 'Opening Balance / New Item',
        ]);

        return redirect('/assets')->with('msg', 'saved');
    }

    public function update(Request $request, $id)
    {
        $asset = Asset::findOrFail($id);
        
        $asset->update([
            'barcode_id' => $request->barcode_id,
            'article' => $request->article,
            'description' => $request->description,
            'unit_measure' => $request->unit_measure,
            'supplier' => $request->supplier,
            'unit_value' => $request->unit_value,
            'status' => $request->status,
        ]);

        return redirect('/assets')->with('msg', 'saved');
    }

    public function destroy($id)
    {
        $asset = Asset::findOrFail($id);
        $asset->delete();

        Transaction::where('item_id', $id)->where('item_type', 'assets')->delete();

        return redirect('/assets')->with('msg', 'deleted');
    }

    public function stockTransaction(Request $request, $id)
    {
        $asset = Asset::findOrFail($id);
        $qty = $request->qty;
        $type = $request->transaction_type;

        if ($type == 'IN') {
            $asset->increment('quantity', $qty);
        } elseif ($type == 'OUT') {
            if ($asset->quantity >= $qty) {
                $asset->decrement('quantity', $qty);
            } else {
                return redirect('/assets')->with('msg', 'error_stock');
            }
        }

        Transaction::create([
            'item_id' => $id,
            'item_type' => 'assets',
            'transaction_type' => $type,
            'quantity' => $qty,
            'supplier' => $request->supplier,
            'transaction_date' => $request->transaction_date,
            'remarks' => $request->remarks,
        ]);

        return redirect('/assets')->with('msg', 'success');
    }
}