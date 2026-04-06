<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 6);

        // Replicating PHP LEFT JOIN logic safely in Laravel
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
            ->paginate($perPage); 

        return view('admin.transactions.index', compact('transactions', 'perPage'));
    }
    
    // ADMIN EXCLUSIVE PRIVILEGE: Delete erroneous transaction logs
    public function destroy($id)
    {
        DB::table('transactions')->where('id', $id)->delete();
        return redirect('/admin/transactions')->with('msg', 'Transaction log permanently deleted.');
    }
}