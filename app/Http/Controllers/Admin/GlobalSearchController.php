<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Asset;
use App\Models\Supply;

class GlobalSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = trim($request->get('q'));
        
        if (!$query || strlen($query) < 2) {
            return response()->json([]);
        }

        $results = [];
        $searchTerm = '%' . $query . '%';
        
        // Lock calculation to 10 items per page for perfect accuracy
        $perPage = 10; 

        // 1. SEARCH INVENTORY & TRANSACTIONS
        try {
            $supplies = Supply::where('article', 'LIKE', $searchTerm)
                ->orWhere('barcode_id', 'LIKE', $searchTerm)
                ->orWhere('description', 'LIKE', $searchTerm)
                ->limit(4)->get();
                
            foreach ($supplies as $item) {
                $position = Supply::where('id', '>=', $item->id)->count();
                $page = ceil($position / $perPage) ?: 1;

                $results[] = [
                    'type' => 'Supply',
                    'title' => $item->article,
                    'meta' => 'Barcode: ' . ($item->barcode_id ?? 'N/A'),
                    'url' => url('/admin/supplies?page=' . $page . '&per_page=' . $perPage)
                ];
            }
        } catch (\Exception $e) {}

        try {
            $assets = Asset::where('article', 'LIKE', $searchTerm)
                ->orWhere('barcode_id', 'LIKE', $searchTerm)
                ->orWhere('description', 'LIKE', $searchTerm)
                ->limit(4)->get();
                
            foreach ($assets as $item) {
                $position = Asset::where('id', '>=', $item->id)->count();
                $page = ceil($position / $perPage) ?: 1;

                $results[] = [
                    'type' => 'Asset',
                    'title' => $item->article,
                    'meta' => 'Barcode: ' . ($item->barcode_id ?? 'N/A'),
                    'url' => url('/admin/assets?page=' . $page . '&per_page=' . $perPage)
                ];
            }
        } catch (\Exception $e) {}
        
        try {
            if (Schema::hasTable('transactions')) {
                $transactions = DB::table('transactions')
                    ->where('remarks', 'LIKE', $searchTerm)
                    ->orWhere('transaction_type', 'LIKE', $searchTerm)
                    ->limit(3)->get();
                    
                foreach ($transactions as $tx) {
                    $position = DB::table('transactions')->where('id', '>=', $tx->id)->count();
                    $page = ceil($position / $perPage) ?: 1;

                    $results[] = [
                        'type' => 'Transaction',
                        'title' => strtoupper($tx->transaction_type) . ' - Qty: ' . $tx->quantity,
                        'meta' => 'Remarks: ' . $tx->remarks,
                        'url' => url('/admin/transactions?page=' . $page . '&per_page=' . $perPage)
                    ];
                }
            }
        } catch (\Exception $e) {}

        // 2. BULLETPROOF RIS SEARCH
        try {
            $risTable = null;
            if (Schema::hasTable('requests')) $risTable = 'requests';
            elseif (Schema::hasTable('ris')) $risTable = 'ris';
            elseif (Schema::hasTable('ris_requests')) $risTable = 'ris_requests';

            if ($risTable) {
                $columns = Schema::getColumnListing($risTable);
                $risCol = in_array('ris_no', $columns) ? 'ris_no' : (in_array('ris_number', $columns) ? 'ris_number' : 'id');
                $purposeCol = in_array('purpose', $columns) ? 'purpose' : (in_array('remarks', $columns) ? 'remarks' : null);

                $requests = DB::table($risTable)->where(function($q) use ($searchTerm, $risCol, $purposeCol) {
                    $q->where($risCol, 'LIKE', $searchTerm);
                    if ($purposeCol) {
                        $q->orWhere($purposeCol, 'LIKE', $searchTerm);
                    }
                })->limit(4)->get();
                
                foreach ($requests as $req) {
                    $position = DB::table($risTable)->where('id', '>=', $req->id)->count();
                    $page = ceil($position / $perPage) ?: 1;

                    $val = $req->{$risCol} ?? 'Pending Request';
                    $purp = $purposeCol ? ($req->{$purposeCol} ?? 'No purpose defined') : 'Request details';

                    $results[] = [
                        'type' => 'RIS Request',
                        'title' => 'RIS: ' . $val,
                        'meta' => 'Purpose: ' . substr($purp, 0, 35) . '...',
                        'url' => url('/admin/requests?page=' . $page . '&per_page=' . $perPage)
                    ];
                }
            }
        } catch (\Exception $e) {}

        return response()->json($results);
    }
}