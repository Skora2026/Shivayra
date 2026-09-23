<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('product');

        return [
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'nullable|exists:sub_categories,id',
            'name' => 'required|string|max:255|unique:products,name,'.$id,
            'description' => 'nullable|string|max:2000',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:3072',
            'price' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0|lt:price',
            'stock' => 'nullable|integer|min:0',
            'status' => 'required|in:active,inactive',
            'size' => 'nullable|string|max:50',
            'pattern' => 'nullable|string|max:100',
            'occasion' => 'nullable|string|max:100',
            'fabric' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:50',
            'neckline' => 'nullable|string|max:100',
            'is_new_arrival' => 'nullable|boolean',
            'is_trending' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'is_returnable' => 'nullable|boolean',
            'variant_name_1' => 'nullable|string|max:50',
            'variant_name_2' => 'nullable|string|max:50',
            'spec_names' => 'nullable|array',
            'spec_values' => 'nullable|array',
            'variants' => 'nullable|array',
            'variants.*.name' => 'nullable|string|max:255',
            'variants.*.price' => 'required|numeric|min:0',
            'variants.*.sale_price' => 'nullable|numeric|min:0|lt:variants.*.price',
            'variants.*.stock' => 'required|integer|min:0',
            'variants.*.value_1' => 'required_with:variants.*.price|string|max:255',
            'variants.*.value_2' => 'nullable|string|max:255',
            'variants.*.images' => 'nullable|array',
            'variants.*.images.*' => 'image|mimes:jpeg,jpg,png,webp|max:3072',
            'gallery' => 'nullable|array',
            'gallery.*' => 'image|mimes:jpeg,jpg,png,webp|max:3072',
        ];
    }

    public function messages(): array
    {
        return [
            'sale_price.lt' => 'Sale price must be less than the regular price.',
            'variants.*.sale_price.lt' => 'Variant sale price must be less than the variant price.',
        ];
    }
}
