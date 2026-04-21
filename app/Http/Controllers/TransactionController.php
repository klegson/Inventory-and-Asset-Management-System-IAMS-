<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 5);

        // Replicating your PHP LEFT JOIN logic safely in Laravel
        $transactions = DB::table('transactions as t')
            ->leftJoin('assets as a', function($join) {
                $join->on('t.item_id', '=', 'a.id')
                     ->where('t.item_type', '=', 'assets');
            })
            ->leftJoin('supplies as s', function($join) {
                $join->on('t.item_id', '=', 's.id')
                     ->where('t.item_type', '=', 'supplies');
            })
            ->select(
                't.*',
                DB::raw('COALESCE(a.article, s.article) as item_name'),
                DB::raw('COALESCE(a.barcode_id, s.barcode_id) as item_code')
            )
            ->orderBy('t.date_time', 'desc')
            ->paginate($perPage); // Changed from get() to paginate()

        return view('transactions.index', compact('transactions', 'perPage'));
    }
}