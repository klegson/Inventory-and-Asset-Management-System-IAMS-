<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        
        // Return a 401 error if session drops, so the frontend DOES NOT wipe the UI
        if (!$user) return response()->json(['error' => 'Unauthenticated'], 401);

        $notifications = [];
        $perPage = 10; // Locked to match your pagination

        try {
            // 1. LOW STOCK ALERTS (Always Unread for Admin)
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
                    'url' => url('/admin/supplies?search=' . urlencode($item->barcode_id ?? $item->article) . '&page=' . $page . '&per_page=' . $perPage)
                ];
            }

            // 2. FORWARDED RIS REQUESTS ALERTS (Only shows if forwarded to Admin)
            $risTable = null;
            if (Schema::hasTable('requests')) $risTable = 'requests';
            elseif (Schema::hasTable('ris')) $risTable = 'ris';
            elseif (Schema::hasTable('ris_requests')) $risTable = 'ris_requests';

            if ($risTable) {
                $columns = Schema::getColumnListing($risTable);
                $risCol = in_array('ris_no', $columns) ? 'ris_no' : (in_array('ris_number', $columns) ? 'ris_number' : 'id');
                $statusCol = in_array('status', $columns) ? 'status' : null;

                if ($statusCol) {
                    // Smart query: ONLY matches statuses containing 'forwarded' 
                    $pendingRis = DB::table($risTable)
                        ->where(DB::raw('LOWER('.$statusCol.')'), 'like', '%forwarded%')
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
                            'title' => 'Forwarded RIS Request',
                            'message' => "RIS No. <strong>{$val}</strong> has been forwarded for your approval.",
                            'time' => $time,
                            'timestamp' => isset($req->created_at) ? Carbon::parse($req->created_at)->timestamp : 0,
                            'url' => url('/admin/requests?search=' . urlencode($val) . '&page=' . $page . '&per_page=' . $perPage)
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            // If the DB is busy/fails, return 500 so the frontend DOES NOT wipe the UI
            return response()->json(['error' => 'Database timeout or error'], 500);
        }

        // SORT NOTIFICATIONS: Newest on top based on timestamp
        usort($notifications, function ($a, $b) {
            return $b['timestamp'] <=> $a['timestamp'];
        });

        return response()->json(['notifications' => $notifications]);
    }
}