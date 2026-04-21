<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\IcsRequest;
use App\Models\SystemSetting; // <--- ADDED
use Illuminate\Http\Request;

class IcsController extends Controller
{
    public function create()
    {
        $yearMonth = date('Y-m');

        // Fetch current sequence settings
        $seqPar = SystemSetting::firstOrCreate(['key' => 'seq_par_no'], ['value' => '1'])->value;
        $seqSphv = SystemSetting::firstOrCreate(['key' => 'seq_sphv_no'], ['value' => '1'])->value;
        $seqSplv = SystemSetting::firstOrCreate(['key' => 'seq_splv_no'], ['value' => '1'])->value;

        // Pre-format the numbers for the View to toggle between
        $parNumber = 'PAR-' . $yearMonth . '-' . str_pad($seqPar, 4, '0', STR_PAD_LEFT);
        $sphvNumber = 'SPHV-' . $yearMonth . '-' . str_pad($seqSphv, 4, '0', STR_PAD_LEFT);
        $splvNumber = 'SPLV-' . $yearMonth . '-' . str_pad($seqSplv, 4, '0', STR_PAD_LEFT);

        return view('user.ics.create', compact('parNumber', 'sphvNumber', 'splvNumber'));
    }

    public function store(Request $request)
    {
        $category = $request->item_category ?? 'Low - Valued';
        $yearMonth = date('Y-m');

        // 1. Determine which prefix and setting key to use based on Category
        if ($category === 'PPE') {
            $settingKey = 'seq_par_no';
            $prefix = 'PAR-';
        } elseif ($category === 'High - Valued') {
            $settingKey = 'seq_sphv_no';
            $prefix = 'SPHV-';
        } else {
            $settingKey = 'seq_splv_no';
            $prefix = 'SPLV-';
        }

        // 2. Fetch the latest setting directly before saving
        $seqSetting = SystemSetting::firstOrCreate(['key' => $settingKey], ['value' => '1']);
        $currentNumber = (int) $seqSetting->value;
        $sequenceFormatted = str_pad($currentNumber, 4, '0', STR_PAD_LEFT);
        $generatedNo = $prefix . $yearMonth . '-' . $sequenceFormatted;

        // 3. Failsafe against duplicates
        while (IcsRequest::where('ics_no', $generatedNo)->exists()) {
            $currentNumber++;
            $sequenceFormatted = str_pad($currentNumber, 4, '0', STR_PAD_LEFT);
            $generatedNo = $prefix . $yearMonth . '-' . $sequenceFormatted;
        }

        // 4. Increment the setting for the next person
        $seqSetting->update(['value' => $currentNumber + 1]);

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
            'ics_no' => $generatedNo,
            'fund_cluster' => $request->fund_cluster,
            'category' => $category,
            'sig_received_from_name' => $request->sig_from_name,
            'sig_received_from_pos' => $request->sig_from_pos,
            'sig_from_date' => $request->sig_from_date,
            'sig_received_by_name' => $request->sig_by_name,
            'sig_received_by_pos' => $request->sig_by_pos,
            'sig_by_date' => $request->sig_by_date,
            'status' => 'Pending',
            'items_json' => $items,
        ]);

        return redirect('/user/dashboard')->with('msg', 'Equipment Request successfully submitted! Assigned ID: ' . $generatedNo);
    }
}