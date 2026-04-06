<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'ends_with:@deped.gov.ph'],
            'password' => 'required'
        ], [
            'email.ends_with' => 'Only @deped.gov.ph emails are allowed to log in.'
        ]);

        // Attempt login and ensure status is 'Active'
        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
            'status' => 'Active'
        ];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            // --- STRICT ROLE-BASED REDIRECTION ---
            $role = strtolower(Auth::user()->role); // Ensure lowercase for matching
            
            if ($role === 'admin') {
                return redirect('/admin/dashboard'); // Forced redirect for Admin
            } elseif ($role === 'staff') {
                return redirect('/'); // Forced redirect for Staff
            } elseif ($role === 'frontuser') {
                return redirect('/user/dashboard'); // Forced redirect for End User
            }

            // Fallback just in case
            return redirect('/');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records or your account is inactive.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/login');
    }
}