<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Supply;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class DashboardController extends Controller
{
    // Helper method to dynamically find the correct date column in your DB
    private function getTransactionDateColumn()
    {
        if (!Schema::hasTable('transactions')) return null;
        $columns = Schema::getColumnListing('transactions');
        
        if (in_array('transaction_date', $columns)) return 'transaction_date';
        if (in_array('date_time', $columns)) return 'date_time';
        return 'created_at';
    }

    public function index()
    {
        $user = auth()->user();
        $user_name = $user ? $user->firstname : 'Admin';

        // 1. Basic Counters
        $total_assets = Asset::sum('quantity') ?? 0;
        $total_supplies = Supply::sum('quantity') ?? 0;
        $total_users = User::count() ?? 0;
        
        $assetItemCount = Asset::count() ?? 0;
        $supplyItemCount = Supply::count() ?? 0;

        // 2. Dynamic Low Stock Logic
        $lowStockCount = Supply::whereRaw('quantity <= COALESCE(low_stock_threshold, 10)')->count();
        $lowStockItems = Supply::whereRaw('quantity <= COALESCE(low_stock_threshold, 10)')
            ->orderBy('quantity', 'asc')
            ->limit(6)
            ->get();

        // 3. RIS Logic & Chart Data
        $risTable = null;
        if (Schema::hasTable('requests')) $risTable = 'requests';
        elseif (Schema::hasTable('ris')) $risTable = 'ris';
        elseif (Schema::hasTable('ris_requests')) $risTable = 'ris_requests';

        $approved_requests = 0;
        $recent_requests = collect();
        $risStatusLabels = ['Pending', 'Approved', 'Issued', 'Declined'];
        $risStatusData = [0, 0, 0, 0];

        if ($risTable) {
            $columns = Schema::getColumnListing($risTable);
            $statusCol = in_array('status', $columns) ? 'status' : null;

            if ($statusCol) {
                $approved_requests = DB::table($risTable)->where(DB::raw('LOWER('.$statusCol.')'), 'like', '%approv%')->count();
                $recent_requests = DB::table($risTable)->orderBy('id', 'desc')->limit(6)->get();

                $statuses = DB::table($risTable)->select($statusCol, DB::raw('count(*) as total'))->groupBy($statusCol)->get();
                foreach ($statuses as $st) {
                    $statusLower = strtolower($st->{$statusCol});
                    if (str_contains($statusLower, 'pending') || str_contains($statusLower, 'forward')) $risStatusData[0] += $st->total;
                    elseif (str_contains($statusLower, 'approv')) $risStatusData[1] += $st->total;
                    elseif (str_contains($statusLower, 'issu')) $risStatusData[2] += $st->total;
                    elseif (str_contains($statusLower, 'declin') || str_contains($statusLower, 'reject') || str_contains($statusLower, 'cancel')) $risStatusData[3] += $st->total;
                }
            }
        }

        // 4. Initial 7-Day Trend Chart Data
        $dates = [];
        for ($i = 6; $i >= 0; $i--) {
            $dates[] = Carbon::today()->subDays($i)->format('M d');
        }
        
        $stockInData = array_fill(0, 7, 0);
        $stockOutData = array_fill(0, 7, 0);

        // Fetch the dynamic column (transaction_date)
        $dateCol = $this->getTransactionDateColumn();

        if ($dateCol) {
            $txs = DB::table('transactions')->where($dateCol, '>=', Carbon::today()->subDays(6)->startOfDay())->get();
            foreach ($txs as $tx) {
                $txDate = Carbon::parse($tx->{$dateCol})->format('M d');
                $idx = array_search($txDate, $dates);
                if ($idx !== false) {
                    if (stripos($tx->transaction_type, 'in') !== false) $stockInData[$idx] += $tx->quantity;
                    elseif (stripos($tx->transaction_type, 'out') !== false) $stockOutData[$idx] += $tx->quantity;
                }
            }
        }

        return view('admin.dashboard', compact(
            'user_name', 'total_assets', 'total_supplies', 'approved_requests', 'total_users', 'recent_requests',
            'assetItemCount', 'supplyItemCount', 'dates', 'stockInData', 'stockOutData', 'risStatusLabels', 'risStatusData', 'lowStockCount', 'lowStockItems'
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

        // Fetch the dynamic column (transaction_date)
        $dateCol = $this->getTransactionDateColumn();

        if ($dateCol) {
            $txs = DB::table('transactions')->whereBetween($dateCol, [$startDate, $endDate])->get();
            foreach ($txs as $tx) {
                $format = ($groupBy === 'day') ? 'M d' : 'M Y';
                $txDate = Carbon::parse($tx->{$dateCol})->format($format);
                
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