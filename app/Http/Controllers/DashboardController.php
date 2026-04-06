<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Supply;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Get the currently authenticated user
        $user = auth()->user(); 
        
        $firstname = $user ? $user->firstname : 'Personnel';
        $lastname = $user ? $user->lastname : '';
        
        $totalAssets = Asset::sum('quantity');
        $totalSupplies = Supply::sum('quantity');
        
        $lowStockCount = Supply::where('quantity', '<=', 10)->count();
        
        $lowStockItems = Supply::where('quantity', '<=', 10)
            ->orderBy('quantity', 'asc')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'firstname', 
            'lastname',
            'totalAssets', 
            'totalSupplies', 
            'lowStockCount', 
            'lowStockItems'
        ));
    }
}