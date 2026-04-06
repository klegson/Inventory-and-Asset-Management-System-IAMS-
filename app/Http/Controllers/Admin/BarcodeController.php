<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Supply;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class BarcodeController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 5);
        $page = $request->input('page', 1);

        // 1. Fetch from Supplies 
        $supplies = Supply::whereNotNull('barcode_id')
            ->select('id', 'barcode_id as barcode_code', 'article')
            ->get()
            ->map(function ($item) {
                $item->item_type = 'supply';
                $item->generated_at = null; 
                return $item;
            });

        // 2. Fetch from Assets 
        $assets = Asset::whereNotNull('barcode_id')
            ->select('id', 'barcode_id as barcode_code', 'article')
            ->get()
            ->map(function ($item) {
                $item->item_type = 'asset';
                $item->generated_at = null; 
                return $item;
            });

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

        return view('admin.barcodes.index', compact('barcodes', 'perPage'));
    }
}