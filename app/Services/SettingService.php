<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingService extends BaseService
{
    /**
     * Create a new class instance.
     */
    public function __construct(Setting $model)
    {
        parent::__construct($model);
    }

    /**
     * Extract validated fields from the request.
     */
    public function getDataFromRequest(Request $requestData): array
    {
        $data = $requestData->only([
            'site_name',
            'logo',
            'favicon',
            'email',
            'phone',
            'alternate_phone',
            'whatsapp',
            'address',
            'facebook',
            'instagram',
            'twitter',
            'youtube',
            'tax_percent',
            'delivery_fee',
            'min_order_for_free_delivery',
        ]);

        // Checkbox semantics: the real settings form posts a marker input
        // alongside the checkbox, so "marker present + checkbox absent" means
        // the owner explicitly unchecked COD. Requests that omit the marker
        // entirely (API calls, partial saves, the OTP verify-path writes)
        // must NOT silently disable COD — preserve the stored value instead.
        if ($requestData->has('is_cod_enabled_submitted')) {
            $data['is_cod_enabled'] = $requestData->boolean('is_cod_enabled');
        }

        return $data;
    }

    /**
     * Handle image upload and return the stored path.
     */
    public function handleImageUpload(Request $request, string $fieldName = 'image', ?string $oldImage = null): ?string
    {
        if ($request->hasFile($fieldName)) {
            if ($oldImage && Storage::disk('public')->exists($oldImage)) {
                Storage::disk('public')->delete($oldImage);
            }

            return $request->file($fieldName)->store('settings', 'public');
        }

        return $oldImage;
    }
}
