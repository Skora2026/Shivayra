<?php

namespace App\Http\Requests\User;

use App\Helpers\UserHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'old_password' => ['required', function ($attribute, $value, $fail) {
                if (! Hash::check($value, UserHelper::getLoggedInUser()->password)) {
                    $fail('The old password is incorrect.');
                }
            }],
            'password' => 'required|string|min:8',
            'confirm_password' => 'required|same:password',
        ];
    }
}
