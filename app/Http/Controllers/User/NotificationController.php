<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class NotificationController extends Controller
{
    /**
     * Fetch notifications specifically for the Division / End-User.
     * Focuses heavily on the status updates of their RIS requests.
     */
    public function fetch(Request $request)
    {
        $user = auth()->user();
        $notifications = [];

        // Failsafe: If no user is logged in, return empty immediately
        if (!$user) {
            return response()->json(['success' => false, 'notifications' => []]);
        }

        // 1. Identify the correct RIS table dynamically
        $risTable = null;
        if (Schema::hasTable('requests')) {
            $risTable = 'requests';
        } elseif (Schema::hasTable('ris')) {
            $risTable = 'ris';
        } elseif (Schema::hasTable('ris_requests')) {
            $risTable = 'ris_requests';
        }

        // 2. Fetch recently updated RIS requests safely
        if ($risTable) {
            $columns = Schema::getColumnListing($risTable);
            
            // Check exactly which columns exist in your specific database
            $hasStatus = in_array('status', $columns);
            $hasUpdatedAt = in_array('updated_at', $columns);
            $hasUserId = in_array('user_id', $columns);
            $hasDivision = in_array('division', $columns);
            $hasDepartment = in_array('department', $columns);
            $hasRisNo = in_array('ris_no', $columns);

            if ($hasStatus) {
                $query = DB::table($risTable);
                
                // Safe closure: passing variables using 'use'
                $query->where(function($q) use ($user, $hasUserId, $hasDivision, $hasDepartment) {
                    if ($hasUserId) {
                        $q->where('user_id', $user->id);
                    } elseif ($hasDivision) {
                        $q->where('division', $user->department ?? 'Unassigned');
                    } elseif ($hasDepartment) {
                        $q->where('department', $user->department ?? 'Unassigned');
                    } else {
                        // Failsafe to prevent returning all records if no ownership column exists
                        $q->where('id', '<', 0); 
                    }
                });

                // Ensure we don't grab Pending requests
                $query->where('status', '!=', 'Pending')
                      ->where('status', '!=', 'pending');

                // Sort by date if it exists, otherwise fallback to ID
                if ($hasUpdatedAt) {
                    $query->orderBy('updated_at', 'desc');
                } else {
                    $query->orderBy('id', 'desc');
                }

                $recentRequests = $query->limit(10)->get();

                foreach ($recentRequests as $ris) {
                    $statusLower = strtolower($ris->status);
                    
                    // Set dynamic icons and titles based on the Admin's decision
                    $icon = 'fas fa-info-circle text-primary';
                    $title = 'Request Updated';
                    
                    if (str_contains($statusLower, 'approv')) {
                        $icon = 'fas fa-check-circle text-success';
                        $title = 'RIS Approved!';
                    } elseif (str_contains($statusLower, 'declin') || str_contains($statusLower, 'reject') || str_contains($statusLower, 'cancel')) {
                        $icon = 'fas fa-times-circle text-danger';
                        $title = 'RIS Declined / Cancelled';
                    } elseif (str_contains($statusLower, 'issu') || str_contains($statusLower, 'releas') || str_contains($statusLower, 'acquir')) {
                        $icon = 'fas fa-box-open text-info';
                        $title = 'Items Ready / Issued';
                    }

                    // Safely parse timestamps
                    $timeString = ($hasUpdatedAt && isset($ris->updated_at)) 
                        ? Carbon::parse($ris->updated_at)->diffForHumans() 
                        : 'Recently';
                        
                    $timestamp = ($hasUpdatedAt && isset($ris->updated_at)) 
                        ? strtotime($ris->updated_at) 
                        : $ris->id;

                    $uniqueNotifId = 'ris_' . $ris->id . '_' . $timestamp;
                    
                    // Safely grab RIS number
                    $risIdentifier = ($hasRisNo && isset($ris->ris_no)) ? $ris->ris_no : ('#' . $ris->id);

                    $notifications[] = [
                        'id' => $uniqueNotifId,
                        'type' => 'ris_update',
                        'title' => $title,
                        'message' => "Your request $risIdentifier is now marked as: " . $ris->status,
                        'time' => $timeString,
                        'icon' => $icon,
                        'url' => url('/user/ris/' . $ris->id)
                    ];
                }
            }
        }

        // Return the JSON payload to the JavaScript Notification Drawer
        return response()->json([
            'success' => true,
            'notifications' => $notifications
        ]);
    }
}