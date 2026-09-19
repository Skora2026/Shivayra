<?php

namespace App\Http\Requests\User;

use App\Helpers\UserHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

class UpdateUserProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $loggedInUser = UserHelper::getLoggedInUser()->id;

        return [
            'first_name' => 'required|string|max:50',
            'last_name' => 'nullable|string|max:50',
            'email' => 'required|email|unique:users,email,'.$loggedInUser,
            'profile_pic' => 'image|mimes:jpeg,png,jpg|nullable|max:2048',
            'current_password' => 'nullable|required_with:password|string',
            'password' => 'nullable|string|min:8|confirmed',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->filled('password')) {
                $user = UserHelper::getLoggedInUser();

                if (! $user || ! Hash::check($this->input('current_password', ''), $user->password)) {
                    $validator->errors()->add('current_password', 'Your current password is incorrect.');
                }
            }
        });
    }
}
