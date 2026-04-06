<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function index()
    {
        // Fetch newest assets first
        $assets = Asset::orderBy('id', 'desc')->get();
        return view('admin.assets.index', compact('assets'));
    }

    public function store(Request $request)
    {
        // For new assets, we usually set them to Serviceable by default, 
        // but we'll accept it if passed, or default it.
        $status = $request->status ?? 'Serviceable';

        Asset::create([
            'article' => $request->article,
            'description' => $request->description,
            'barcode_id' => $request->barcode_id,
            'unit_measure' => $request->unit_measure,
            'unit_value' => $request->unit_value,
            'quantity' => $request->initial_quantity,
            'supplier' => $request->supplier,
            'status' => $status
        ]);

        return redirect('/admin/assets')->with('msg', 'Asset successfully added!');
    }

    public function update(Request $request, $id)
    {
        $asset = Asset::findOrFail($id);

        $asset->update([
            'article' => $request->article,
            'description' => $request->description,
            'barcode_id' => $request->barcode_id,
            'unit_measure' => $request->unit_measure,
            'unit_value' => $request->unit_value,
            'supplier' => $request->supplier,
            'status' => $request->status
            // Note: Quantity is managed via transactions, so we don't overwrite it here
        ]);

        return redirect('/admin/assets')->with('msg', 'Asset successfully updated!');
    }

    public function destroy($id)
    {
        Asset::findOrFail($id)->delete();
        return redirect('/admin/assets')->with('msg', 'Asset successfully deleted!');
    }

    public function details($id)
    {
        $asset = Asset::findOrFail($id);
        return view('admin.assets.view_details', compact('asset'))->render();
    }
}