<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Supply;
use App\Models\User;
use App\Models\RisRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function dashboard()
    {
        // 1. Get the authenticated admin user
        $user = Auth::user();
        $user_name = $user ? $user->firstname . ' ' . $user->lastname : 'Admin';

        // 2. Fetch Total Assets
        $total_assets = Asset::sum('quantity');

        // 3. Fetch Total Supplies
        $total_supplies = Supply::sum('quantity');

        // 4. Fetch Total Registered Users
        $total_users = User::count();

        // 5. Fetch Approved Requests Count
        $approved_requests = RisRequest::where('status', 'Approved')->count();

        // 6. Fetch Recent RIS Requests - STRICTLY EXCLUDE 'Pending Staff Review'
        $recent_requests = RisRequest::where('status', '!=', 'Pending Staff Review')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'user_name', 
            'total_assets', 
            'total_supplies', 
            'total_users', 
            'approved_requests',
            'recent_requests'
        ));
    }
}