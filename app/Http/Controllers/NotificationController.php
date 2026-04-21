<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Supply;
use Carbon\Carbon;

class NotificationController extends Controller
{
    public function fetch(Request $request)
    {
        $user = auth()->user();
        if (!$user) return response()->json(['error' => 'Unauthenticated'], 401);

        $role = trim(strtolower($user->role));
        $notifications = [];
        $perPage = 10; // Locked to match your pagination

        if (in_array($role, ['admin', 'staff'])) {
            try {
                // 1. LOW STOCK ALERTS (Always Unread)
                $lowStocks = Supply::where('quantity', '<=', 10)->get();
                foreach ($lowStocks as $item) {
                    // Calculate Exact Page
                    $position = Supply::where('id', '>=', $item->id)->count();
                    $page = ceil($position / $perPage) ?: 1;

                    $notifications[] = [
                        'id' => 'ls_' . $item->id,
                        'type' => 'low_stock',
                        'icon' => 'fas fa-exclamation-triangle text-warning',
                        'title' => 'Low Stock Alert',
                        'message' => "<strong>{$item->article}</strong> is running low ({$item->quantity} left).",
                        'time' => 'Action required',
                        'timestamp' => isset($item->updated_at) ? Carbon::parse($item->updated_at)->timestamp : 0,
                        'url' => url('/supplies?search=' . urlencode($item->barcode_id ?? $item->article) . '&page=' . $page . '&per_page=' . $perPage)
                    ];
                }

                // 2. NEW RIS REQUESTS ALERTS (Smart Status Check)
                $risTable = null;
                if (Schema::hasTable('requests')) $risTable = 'requests';
                elseif (Schema::hasTable('ris')) $risTable = 'ris';
                elseif (Schema::hasTable('ris_requests')) $risTable = 'ris_requests';

                if ($risTable) {
                    $columns = Schema::getColumnListing($risTable);
                    $risCol = in_array('ris_no', $columns) ? 'ris_no' : (in_array('ris_number', $columns) ? 'ris_number' : 'id');
                    $statusCol = in_array('status', $columns) ? 'status' : null;

                    if ($statusCol) {
                        $pendingRis = DB::table($risTable)
                            ->where(function($q) use ($statusCol) {
                                $q->where(DB::raw('LOWER('.$statusCol.')'), 'like', '%pending%')
                                  ->orWhere(DB::raw('LOWER('.$statusCol.')'), 'like', '%review%')
                                  ->orWhere(DB::raw('LOWER('.$statusCol.')'), 'like', '%submitted%');
                            })
                            ->orderBy('id', 'desc')->get();
                        
                        foreach ($pendingRis as $req) {
                            // Calculate Exact Page
                            $position = DB::table($risTable)->where('id', '>=', $req->id)->count();
                            $page = ceil($position / $perPage) ?: 1;

                            $time = isset($req->created_at) ? Carbon::parse($req->created_at)->diffForHumans() : 'Recently';
                            $val = $req->{$risCol} ?? 'New';
                            
                            $notifications[] = [
                                'id' => 'ris_' . $req->id,
                                'type' => 'ris',
                                'icon' => 'fas fa-file-signature text-primary',
                                'title' => 'New RIS Request',
                                'message' => "RIS No. <strong>{$val}</strong> requires your review.",
                                'time' => $time,
                                'timestamp' => isset($req->created_at) ? Carbon::parse($req->created_at)->timestamp : 0,
                                'url' => url('/ris?search=' . urlencode($val) . '&page=' . $page . '&per_page=' . $perPage)
                            ];
                        }
                    }
                }
            } catch (\Exception $e) {
                return response()->json(['error' => 'Database timeout or error'], 500);
            }
        }

        // SORT NOTIFICATIONS: Newest on top based on timestamp
        usort($notifications, function ($a, $b) {
            return $b['timestamp'] <=> $a['timestamp'];
        });

        return response()->json(['notifications' => $notifications]);
    }
}