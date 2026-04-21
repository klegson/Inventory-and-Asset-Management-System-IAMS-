<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Supply;
use App\Models\User;
use App\Models\RisRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $user_name = $user ? $user->firstname . ' ' . $user->lastname : 'Admin';

        // 1. Basic Counters
        $total_assets = Asset::sum('quantity') ?? 0;
        $total_supplies = Supply::sum('quantity') ?? 0;
        $total_users = User::count() ?? 0;
        $approved_requests = RisRequest::where('status', 'Approved')->count();

        // 2. Fetch Recent RIS Requests
        $recent_requests = RisRequest::where('status', '!=', 'Pending Staff Review')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // 3. NEW: Critical/Low Stock for Admins
        $lowStockItems = Supply::where('quantity', '<=', 10)
            ->orderBy('quantity', 'asc')
            ->limit(5)
            ->get();

        // 4. Fetch Item Types Count (For Doughnut Chart)
        $assetItemCount = Asset::count() ?? 0;
        $supplyItemCount = Supply::count() ?? 0;

        // 5. RIS Pie Chart Data
        $risStatusLabels = ['Pending Staff Review', 'Pending Approval', 'Approved', 'Issued', 'Declined'];
        $risStatusData = [0, 0, 0, 0, 0];
        
        $statuses = RisRequest::select('status', DB::raw('count(*) as total'))->groupBy('status')->get();
        foreach ($statuses as $st) {
            $statusLower = strtolower($st->status);
            
            if (str_contains($statusLower, 'pending')) {
                $risStatusData[0] += $st->total; // Pending Staff Review
            } elseif (str_contains($statusLower, 'forward')) {
                $risStatusData[1] += $st->total; // Forwarded to Admin (Pending Admin Approval)
            } elseif (str_contains($statusLower, 'approv')) {
                $risStatusData[2] += $st->total; // Approved
            } elseif (str_contains($statusLower, 'issu') || str_contains($statusLower, 'acquir')) {
                $risStatusData[3] += $st->total; // Issued / Acquired
            } elseif (str_contains($statusLower, 'declin') || str_contains($statusLower, 'reject') || str_contains($statusLower, 'cancel')) {
                $risStatusData[4] += $st->total; // Declined / Rejected / Cancelled
            }
        }

        // 6. Initial 7-Day Trend Chart Data
        $dates = [];
        for ($i = 6; $i >= 0; $i--) $dates[] = Carbon::today()->subDays($i)->format('M d');
        
        $stockInData = array_fill(0, 7, 0);
        $stockOutData = array_fill(0, 7, 0);

        if (Schema::hasTable('transactions')) {
            $txs = DB::table('transactions')->where('date_time', '>=', Carbon::today()->subDays(6)->startOfDay())->get();
            foreach ($txs as $tx) {
                $txDate = Carbon::parse($tx->date_time)->format('M d');
                $idx = array_search($txDate, $dates);
                if ($idx !== false) {
                    if (stripos($tx->transaction_type, 'in') !== false) $stockInData[$idx] += $tx->quantity;
                    elseif (stripos($tx->transaction_type, 'out') !== false) $stockOutData[$idx] += $tx->quantity;
                }
            }
        }

        return view('admin.dashboard', compact(
            'user_name', 'total_assets', 'total_supplies', 'total_users', 'approved_requests', 
            'recent_requests', 'lowStockItems', 'assetItemCount', 'supplyItemCount', 
            'dates', 'stockInData', 'stockOutData', 'risStatusLabels', 'risStatusData'
        ));
    }

    public function getChartData(Request $request)
    {
        $range = $request->query('range', '7days');
        $dates = [];
        $stockInData = [];
        $stockOutData = [];
        $now = Carbon::now();

        if ($range === '7days') {
            for ($i = 6; $i >= 0; $i--) { $d = $now->copy()->subDays($i)->format('M d'); $dates[] = $d; $stockInData[$d] = 0; $stockOutData[$d] = 0; }
            $startDate = $now->copy()->subDays(6)->startOfDay();
            $endDate = $now->copy()->endOfDay();
            $groupBy = 'day';
        } elseif ($range === '30days') {
            for ($i = 29; $i >= 0; $i--) { $d = $now->copy()->subDays($i)->format('M d'); $dates[] = $d; $stockInData[$d] = 0; $stockOutData[$d] = 0; }
            $startDate = $now->copy()->subDays(29)->startOfDay();
            $endDate = $now->copy()->endOfDay();
            $groupBy = 'day';
        } elseif ($range === 'this_year') {
            for ($i = 1; $i <= 12; $i++) { $m = Carbon::create($now->year, $i, 1)->format('M Y'); $dates[] = $m; $stockInData[$m] = 0; $stockOutData[$m] = 0; }
            $startDate = $now->copy()->startOfYear();
            $endDate = $now->copy()->endOfYear();
            $groupBy = 'month';
        } elseif ($range === 'last_year') {
            $lastYear = $now->year - 1;
            for ($i = 1; $i <= 12; $i++) { $m = Carbon::create($lastYear, $i, 1)->format('M Y'); $dates[] = $m; $stockInData[$m] = 0; $stockOutData[$m] = 0; }
            $startDate = Carbon::create($lastYear, 1, 1)->startOfDay();
            $endDate = Carbon::create($lastYear, 12, 31)->endOfDay();
            $groupBy = 'month';
        }

        if (Schema::hasTable('transactions')) {
            $txs = DB::table('transactions')->whereBetween('date_time', [$startDate, $endDate])->get();
            foreach ($txs as $tx) {
                $format = ($groupBy === 'day') ? 'M d' : 'M Y';
                $txDate = Carbon::parse($tx->date_time)->format($format);
                
                if (isset($stockInData[$txDate])) {
                    if (stripos($tx->transaction_type, 'in') !== false) $stockInData[$txDate] += $tx->quantity;
                    elseif (stripos($tx->transaction_type, 'out') !== false) $stockOutData[$txDate] += $tx->quantity;
                }
            }
        }

        return response()->json([
            'labels' => $dates,
            'stockIn' => array_values($stockInData),
            'stockOut' => array_values($stockOutData)
        ]);
    }
}