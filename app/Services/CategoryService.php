<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryService extends BaseService
{
    /**
     * Create a new class instance.
     */
    public function __construct(Category $model)
    {
        parent::__construct($model);
    }

    /**
     * Extract validated fields from the request.
     */
    public function getDataFromRequest(Request $requestData): array
    {
        return $requestData->only([
            'name',
            'description',
            'status',
        ]);
    }

    /**
     * Handle image upload and return the stored path.
     */
    public function handleImageUpload(Request $request, ?string $oldImage = null): ?string
    {
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($oldImage && Storage::disk('public')->exists($oldImage)) {
                Storage::disk('public')->delete($oldImage);
            }

            return $request->file('image')->store('categories', 'public');
        }

        return $oldImage;
    }
}
