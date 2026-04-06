<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return view('user.profile', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $data = [
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'designation' => $request->designation, // Assuming 'role/position' input maps to designation
            'email' => $request->email,
            'bio' => $request->bio,
            'theme_color' => $request->theme_color,
        ];

        // Handle Image Upload
        if ($request->hasFile('image')) {
            // Delete old image if it exists
            if ($user->image && file_exists(public_path('uploads/users/' . $user->image))) {
                unlink(public_path('uploads/users/' . $user->image));
            }
            
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('uploads/users'), $imageName);
            $data['image'] = $imageName;
        }

        $user->update($data);

        return redirect()->back()->with('msg', 'Profile updated successfully!');
    }
}