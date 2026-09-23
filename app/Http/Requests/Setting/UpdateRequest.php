<?php

namespace App\Http\Requests\Setting;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
            // The site name feeds the storefront header, emails and footer — an
            // empty save must not silently wipe the store's identity.
            'site_name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
            'favicon' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'alternate_phone' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'facebook' => 'nullable|url|max:255',
            'instagram' => 'nullable|url|max:255',
            'twitter' => 'nullable|url|max:255',
            'youtube' => 'nullable|url|max:255',
            'tax_percent' => 'nullable|numeric|min:0|max:100',
            'delivery_fee' => 'nullable|numeric|min:0',
            'min_order_for_free_delivery' => 'nullable|numeric|min:0',
            'is_cod_enabled' => 'nullable|boolean',
        ];
    }
}
