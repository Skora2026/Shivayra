<?php

namespace App\Services;

use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerService extends BaseService
{
    /**
     * Create a new class instance.
     */
    public function __construct(Banner $model)
    {
        parent::__construct($model);
    }

    /**
     * Get request data
     */
    public function getDataFromRequest(Request $request): array
    {
        return $request->only([
            'badge',
            'title',
            'description',
            'button_text',
            'button_link',
            'secondary_button_text',
            'secondary_button_link',
            'sort_order',
            'status',
            'device_type',
        ]);
    }

    /**
     * Upload Banner Image
     */
    public function handleImageUpload(Request $request, ?string $oldImage = null, string $inputName = 'image'): ?string
    {
        if ($request->hasFile($inputName)) {
            // Delete old image if exists
            if ($oldImage && Storage::disk('public')->exists($oldImage)) {
                Storage::disk('public')->delete($oldImage);
            }

            return $request->file($inputName)->store('banner', 'public');
        }

        return $oldImage;
    }
}
