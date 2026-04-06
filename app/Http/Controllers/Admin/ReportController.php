<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Supply;
use App\Models\RisRequest;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    // Main Reports Dashboard
    public function index()
    {
        return view('admin.reports.index');
    }

    // 1. Asset Stocks Report
    public function assetStocks()
    {
        $assets = Asset::orderBy('article', 'asc')->get();
        return view('admin.reports.asset_stocks', compact('assets'));
    }

    // 2. Supply Stocks Report
    public function supplyStocks()
    {
        $supplies = Supply::orderBy('article', 'asc')->get();
        return view('admin.reports.supply_stocks', compact('supplies'));
    }

    // 3. RIS List Report
    public function risList()
    {
        $requests = RisRequest::orderBy('created_at', 'desc')->get();
        return view('admin.reports.ris_list', compact('requests'));
    }

    // 4. Defective Assets Report
    public function defectiveAssets()
    {
        // Only fetch items marked as Unserviceable
        $assets = Asset::where('status', 'Unserviceable')->orderBy('article', 'asc')->get();
        return view('admin.reports.defective_assets', compact('assets'));
    }
}