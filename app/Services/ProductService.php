<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductService extends BaseService
{
    /**
     * Create a new class instance.
     */
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    /**
     * Extract validated fields from the request.
     */
    public function getDataFromRequest(Request $requestData): array
    {
        $data = $requestData->only([
            'category_id',
            'sub_category_id',
            'name',
            'description',
            'status',
            'size',
            'pattern',
            'occasion',
            'fabric',
            'color',
            'neckline',
            'variant_name_1',
            'variant_name_2',
        ]);

        $data['is_new_arrival'] = $requestData->has('is_new_arrival');
        $data['is_trending'] = $requestData->has('is_trending');
        $data['is_featured'] = $requestData->has('is_featured');
        $data['is_returnable'] = $requestData->has('is_returnable');

        // Parse key-value specifications
        $specs = [];
        if ($requestData->has('spec_names')) {
            $names = $requestData->input('spec_names', []);
            $values = $requestData->input('spec_values', []);
            foreach ($names as $idx => $name) {
                if (! empty($name)) {
                    $specs[] = [
                        'name' => $name,
                        'value' => $values[$idx] ?? '',
                    ];
                }
            }
        }
        $data['specifications'] = $specs;

        // Calculate price and stock based on variants if provided
        $variants = $requestData->input('variants', []);
        $price = $requestData->input('price', 0);
        $salePrice = $requestData->input('sale_price', null);
        $stock = $requestData->input('stock', 0);

        if (! empty($variants) && is_array($variants)) {
            $minPrice = null;
            $correspondingSalePrice = null;
            $totalStock = 0;
            foreach ($variants as $var) {
                $p = isset($var['price']) ? (float) $var['price'] : 0;
                $sp = (isset($var['sale_price']) && $var['sale_price'] !== '') ? (float) $var['sale_price'] : null;
                $s = isset($var['stock']) ? (int) $var['stock'] : 0;
                $totalStock += $s;

                if ($minPrice === null || $p < $minPrice) {
                    $minPrice = $p;
                    $correspondingSalePrice = $sp;
                }
            }
            $price = $minPrice ?? 0;
            $salePrice = $correspondingSalePrice;
            $stock = $totalStock;
        }

        $data['price'] = $price;
        $data['sale_price'] = $salePrice;
        $data['stock'] = $stock;

        return $data;
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

            return $request->file('image')->store('products', 'public');
        }

        return $oldImage;
    }

    /**
     * Handle multiple gallery images upload and deletion of removed ones.
     */
    public function handleGalleryUploads(Request $request, array $existingImages = []): array
    {
        // The form marks itself as gallery-aware with a hidden flag. Without it we
        // leave the stored images untouched: an absent field is indistinguishable
        // from "every image was removed", and guessing "removed" deletes files.
        if (! $request->boolean('gallery_form')) {
            return $existingImages;
        }

        $existing = array_filter((array) $request->input('existing_gallery', []));

        // Delete removed files
        $removed = array_diff($existingImages, $existing);
        foreach ($removed as $r) {
            if (Storage::disk('public')->exists($r)) {
                Storage::disk('public')->delete($r);
            }
        }

        // Upload new files
        $uploaded = [];
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $file) {
                $uploaded[] = $file->store('products/gallery', 'public');
            }
        }

        return array_merge($existing, $uploaded);
    }

    /**
     * Save and sync product variants.
     */
    public function saveVariants(Product $product, Request $request)
    {
        $variantsData = $request->input('variants', []);
        $existingVariants = $product->variants;
        $keepIds = [];

        foreach ($variantsData as $index => $varData) {
            $variantId = $varData['id'] ?? null;

            // A submitted id must belong to THIS product — otherwise an edited
            // form could pull another product's variant into this one
            if ($variantId && ! $existingVariants->contains('id', $variantId)) {
                $variantId = null;
            }

            // Handle variant image uploads
            $existing = $varData['existing_images'] ?? [];

            // If it is an update, delete any variant images that have been removed
            $oldVariant = $variantId ? $existingVariants->find($variantId) : null;
            if ($oldVariant) {
                $oldImages = $oldVariant->images;
                $removedImages = array_diff($oldImages, $existing);
                foreach ($removedImages as $removed) {
                    if ($removed && Storage::disk('public')->exists($removed)) {
                        Storage::disk('public')->delete($removed);
                    }
                }
            }

            // Upload new variant images
            $uploaded = [];
            if ($request->hasFile("variants.{$index}.images")) {
                foreach ($request->file("variants.{$index}.images") as $file) {
                    $uploaded[] = $file->store('products/variants', 'public');
                }
            }

            // Merge existing and newly uploaded images
            $allImages = array_merge($existing, $uploaded);

            $imageValue = null;
            if (! empty($allImages)) {
                $imageValue = json_encode(array_values($allImages));
            }

            // Create or update variant
            $variant = $product->variants()->updateOrCreate(
                ['id' => $variantId],
                [
                    'name' => $varData['name'] ?? null,
                    'value_1' => $varData['value_1'] ?? null,
                    'value_2' => $varData['value_2'] ?? null,
                    'price' => $varData['price'] ?? 0,
                    'sale_price' => $varData['sale_price'] ?? null,
                    'stock' => $varData['stock'] ?? 0,
                    'image' => $imageValue,
                ]
            );
            $keepIds[] = $variant->id;
        }

        // Delete removed variants and all their associated images from disk
        foreach ($existingVariants as $oldVar) {
            if (! in_array($oldVar->id, $keepIds)) {
                $oldImages = $oldVar->images;
                foreach ($oldImages as $img) {
                    if ($img && Storage::disk('public')->exists($img)) {
                        Storage::disk('public')->delete($img);
                    }
                }
                $oldVar->delete();
            }
        }
    }

    /**
     * Delete a product along with every image it owns.
     *
     * The variants table cascades at the database level, but that only removes
     * rows — the uploaded files would survive as orphans, so collect them first.
     */
    public function deleteData(string $id)
    {
        $product = $this->model::with('variants')->find($id);

        if ($product) {
            $files = array_filter(array_merge(
                [$product->image],
                $product->gallery_images ?? [],
                $product->variants->flatMap(fn ($variant) => $variant->images)->all()
            ));

            if ($files) {
                Storage::disk('public')->delete(array_values($files));
            }
        }

        return parent::deleteData($id);
    }
}
