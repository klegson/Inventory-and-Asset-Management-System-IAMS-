<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\RisRequest;
use App\Models\Supply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $firstName = trim($user->firstname);
        $lastName = trim($user->lastname);
        $department = trim($user->department);

        // BULLETPROOF QUERY: Matches the exact logic from the History page!
        // Finds if User ID matches OR Name matches OR Department/Division matches
        $allUserRequests = RisRequest::where(function($q) use ($user, $firstName, $lastName, $department) {
                $q->where('user_id', $user->id)
                  ->orWhere('sig_requested_by', 'LIKE', "%{$firstName}%")
                  ->orWhere('sig_requested_by', 'LIKE', "%{$lastName}%")
                  ->orWhere('office', 'LIKE', "%{$department}%")
                  ->orWhere('division', 'LIKE', "%{$department}%");
            })
            ->orderBy('created_at', 'desc')
            ->get(); // Fetch it immediately into a Collection

        // Count directly from the Collection
        $pendingCount = $allUserRequests->whereIn('status', ['Pending Staff Review', 'Forwarded to Admin', 'Pending'])->count();
        
        $approvedCount = $allUserRequests->where('status', 'Approved')->count();
        
        // Explicitly catches Rejected, Declined, and Cancelled
        $declinedCount = $allUserRequests->whereIn('status', ['Declined', 'Cancelled', 'Rejected'])->count();

        // Grab the top 5 for the Recent RIS Activity table
        $recentRis = $allUserRequests->take(5);

        return view('user.dashboard', compact(
            'user', 
            'pendingCount', 
            'approvedCount', 
            'declinedCount', 
            'recentRis'
        ));
    }

    public function supplyOverview()
    {
        // Fetch all active supplies from the database
        $supplies = Supply::where('status', 'Available')
                          ->orWhere('status', 'Low Stock')
                          ->orderBy('article', 'asc')
                          ->get();

        return view('user.supply_overview', compact('supplies'));
    }
}