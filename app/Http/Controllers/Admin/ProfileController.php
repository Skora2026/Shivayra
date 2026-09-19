<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\UserHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateUserProfileRequest;
use App\Models\User;

class ProfileController extends Controller
{
    /**
     * Show the profile edit form.
     */
    public function edit()
    {
        $user = UserHelper::getLoggedInUser();

        return view('admin.profile.edit', compact('user'));
    }

    /**
     * Update the logged-in user profile.
     */
    public function update(UpdateUserProfileRequest $request)
    {
        /** @var User $user */
        $user = UserHelper::getLoggedInUser();

        $data = $request->only(['first_name', 'last_name', 'email']);
        $data['name'] = trim($data['first_name'].' '.($data['last_name'] ?? ''));

        // Handle profile picture upload if present
        if ($request->hasFile('profile_pic')) {
            // Delete old profile picture if exists
            if ($user->profile_pic) {
                UserHelper::deleteImages('profiles', basename($user->profile_pic));
            }
            $path = UserHelper::uploadImages($request->file('profile_pic'), 'profiles');
            $data['profile_pic'] = $path;
        }

        // Handle password change if filled (current password verified in the FormRequest)
        if ($request->filled('password')) {
            $data['password'] = $request->password; // hashed via model cast
        }

        $user->update($data);

        return redirect()->route('admin.dashboard')->with('success', 'Profile updated successfully.');
    }
}
