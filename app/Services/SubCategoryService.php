<?php

namespace App\Services;

use App\Models\SubCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SubCategoryService extends BaseService
{
    /**
     * Create a new class instance.
     */
    public function __construct(SubCategory $model)
    {
        parent::__construct($model);
    }

    /**
     * Extract validated fields from the request.
     */
    public function getDataFromRequest(Request $requestData): array
    {
        return $requestData->only([
            'category_id',
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
            if ($oldImage && Storage::disk('public')->exists($oldImage)) {
                Storage::disk('public')->delete($oldImage);
            }

            return $request->file('image')->store('sub-categories', 'public');
        }

        return $oldImage;
    }

    /**
     * Get sub-categories by category ID.
     *
     * @return Collection
     */
    public function getByCategoryId(int $categoryId)
    {
        return $this->model::where('category_id', $categoryId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }
}
