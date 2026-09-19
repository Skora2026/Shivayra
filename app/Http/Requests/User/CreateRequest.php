<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class CreateRequest extends FormRequest
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
        return [
            'first_name' => 'required|string|max:50',
            'last_name' => 'nullable|string|max:50',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', Password::min(8)],
            'status' => 'nullable|string',
        ];
    }

    /**
     * Custom message mapping.
     */
    public function messages()
    {
        return [
            'role_id.required' => 'The role field is required',
            'role_id.exists' => 'The selected role must exist in the roles table',
        ];
    }
}
