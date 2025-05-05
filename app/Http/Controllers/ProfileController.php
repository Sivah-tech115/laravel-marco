<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();

        return view('Admin.profile', ['user' => $user]);
    }

    public function update(Request $request)
    {
        if ($request->has('changeprofile')) {
            // 👉 Handle profile info update
            $request->validate([
                'fname' => 'required|string|max:255',
                'email' => 'required|email',
                'image' => 'nullable|image|mimes:jpg,png,jpeg|max:2048'
            ]);

            $user = auth()->user();
            $user->name = $request->fname;
            $user->email = $request->email;

            if ($request->hasFile('image')) {
                $filename = time() . '.' . $request->image->extension();
                $request->image->storeAs('', $filename, 'public'); // Save directly in the root of storage/app/publi
                $user->image = $filename;
            }

            $user->save();

            return back()->with('success', 'Profile updated successfully!');
        }

        if ($request->has('changepassbtn')) {
            // 👉 Handle password update
            $request->validate([
                'password' => 'required|string|min:8|confirmed',
            ]);

            $user = auth()->user();
            $user->password = bcrypt($request->password);
            $user->save();

            return back()->with('success', 'Password updated successfully!');
        }

        return back()->with('error', 'Invalid form submission.');
    }
}
