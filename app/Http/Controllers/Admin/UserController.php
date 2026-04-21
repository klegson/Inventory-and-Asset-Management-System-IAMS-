<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('id', 'desc')->get();
        return view('admin.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'image' => 'nullable|image|mimes:jpg,png,jpeg|max:2048'
        ]);

        $data = $request->except(['password', 'image', 'designation', 'employee_id']);
        
        // Hash the password for security
        $data['password'] = Hash::make($request->password); 
        
        // Add the remember token upon user creation
        $data['remember_token'] = Str::random(10);

        // Upload directly to public/uploads/users
        if ($request->hasFile('image')) {
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('uploads/users'), $imageName);
            $data['image'] = $imageName;
        }

        User::create($data);

        return redirect('/admin/users')->with('msg', 'User added successfully!');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'email' => 'required|email|unique:users,email,' . $id,
            'image' => 'nullable|image|mimes:jpg,png,jpeg|max:2048'
        ]);

        $data = $request->except(['password', 'image', 'designation', 'employee_id']);

        if ($request->filled('password')) {
            // Hash the new password if the admin provided one
            $data['password'] = Hash::make($request->password); 
        }

        // Upload directly to public/uploads/users
        if ($request->hasFile('image')) {
            // Delete old image from public folder
            if ($user->image && file_exists(public_path('uploads/users/' . $user->image))) {
                unlink(public_path('uploads/users/' . $user->image));
            }
            
            // Save new image
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('uploads/users'), $imageName);
            $data['image'] = $imageName;
        }

        $user->update($data);

        return redirect('/admin/users')->with('msg', 'User updated successfully!');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Delete associated profile picture from public folder
        if ($user->image && file_exists(public_path('uploads/users/' . $user->image))) {
            unlink(public_path('uploads/users/' . $user->image));
        }

        $user->delete();
        return redirect('/admin/users')->with('msg', 'User deleted successfully!');
    }

    public function details($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.view_details', compact('user'))->render();
    }
}