<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SystemSetting;

class SettingsController extends Controller
{
    public function index()
    {
        // Fetch all settings and format them into an easy key-value array
        $settings = SystemSetting::pluck('value', 'key')->toArray();
        
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        // The keys we expect from the form
        $keys = [
            'seq_stock_no', 
            'seq_ris_no', 
            'seq_par_no', 
            'seq_sphv_no', 
            'seq_splv_no'
        ];

        foreach ($keys as $key) {
            if ($request->has($key)) {
                SystemSetting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $request->input($key)]
                );
            }
        }

        return redirect()->back()->with('msg', 'Sequence settings updated successfully!');
    }
}