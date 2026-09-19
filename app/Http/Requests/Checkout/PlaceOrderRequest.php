<?php

namespace App\Http\Requests\Checkout;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class PlaceOrderRequest extends FormRequest
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
            // Billing Details
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:15',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'pincode' => 'required|string|max:10',

            // Shipping Toggle
            'same_address' => 'nullable|boolean',

            // Shipping Details (Conditional if not same as billing)
            'shipping_name' => 'required_without:same_address|nullable|string|max:255',
            'shipping_email' => 'nullable|email|max:255',
            'shipping_phone' => 'nullable|string|max:15',
            'shipping_address' => 'required_without:same_address|nullable|string|max:500',
            'shipping_city' => 'required_without:same_address|nullable|string|max:100',
            'shipping_state' => 'required_without:same_address|nullable|string|max:100',
            'shipping_pincode' => 'required_without:same_address|nullable|string|max:10',

            // Payment and Cart Data
            'payment_method' => 'required|in:cod,razorpay',
            'cart_data' => 'required|string',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->input('payment_method') === 'cod') {
                $setting = Setting::first();
                $isCodEnabled = $setting ? (bool) $setting->is_cod_enabled : true;
                if (! $isCodEnabled) {
                    $validator->errors()->add('payment_method', 'Cash on Delivery (COD) is currently disabled. Please choose online payment.');
                }
            }
        });
    }
}
