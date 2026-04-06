<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\IcsRequest;
use Illuminate\Http\Request;

class IcsController extends Controller
{
    public function create()
    {
        // Auto-generate ICS Number
        $yearMonth = date('Y-m');
        $count = IcsRequest::whereMonth('created_at', date('m'))->count() + 1;
        $icsNumber = 'SPLV-' . $yearMonth . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        return view('user.ics.create', compact('icsNumber'));
    }

    public function store(Request $request)
    {
        // Format the dynamic items array
        $items = [];
        $itemCount = count($request->qty ?? []);
        
        for ($i = 0; $i < $itemCount; $i++) {
            if (!empty($request->qty[$i]) || !empty($request->desc[$i])) {
                $items[] = [
                    'qty' => $request->qty[$i],
                    'unit' => $request->unit[$i],
                    'desc' => $request->desc[$i],
                    'inv_no' => $request->inv_no[$i],
                    'est_life' => $request->est_life[$i],
                    'unit_cost' => $request->unit_cost[$i],
                    'total_cost' => $request->total_cost[$i],
                ];
            }
        }

        // Save to Database
        IcsRequest::create([
            'ics_no' => $request->ics_no,
            'fund_cluster' => $request->fund_cluster,
            'category' => $request->item_category ?? 'Value',
            'sig_received_from_name' => $request->sig_from_name,
            'sig_received_from_pos' => $request->sig_from_pos,
            'sig_from_date' => $request->sig_from_date,
            'sig_received_by_name' => $request->sig_by_name,
            'sig_received_by_pos' => $request->sig_by_pos,
            'sig_by_date' => $request->sig_by_date,
            'status' => 'Pending',
            'items_json' => $items,
        ]);

        return redirect('/user/dashboard')->with('msg', 'ICS Form successfully submitted!');
    }
}