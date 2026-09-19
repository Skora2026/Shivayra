<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\ProductDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\CreateRequest;
use App\Http\Requests\Product\UpdateRequest;
use App\Models\Product;
use App\Services\CategoryService;
use App\Services\ProductService;
use App\Services\SubCategoryService;

class ProductController extends Controller
{
    protected $productService;

    protected $categoryService;

    protected $subCategoryService;

    /**
     * Create a new Controller instance.
     */
    public function __construct(
        ProductService $productService,
        CategoryService $categoryService,
        SubCategoryService $subCategoryService
    ) {
        $this->productService = $productService;
        $this->categoryService = $categoryService;
        $this->subCategoryService = $subCategoryService;
    }

    /**
     * Render the products grid using Yajra DataTables.
     */
    public function index(ProductDataTable $dataTable)
    {
        return $dataTable->render('admin.products.index');
    }

    /**
     * Show the product creation form.
     */
    public function create()
    {
        $categories = $this->categoryService->getAllData();
        $subCategories = collect();

        return view('admin.products.create', compact('categories', 'subCategories'));
    }

    /**
     * Store a newly created product.
     */
    public function store(CreateRequest $request)
    {
        $data = $this->productService->getDataFromRequest($request);
        $data['image'] = $this->productService->handleImageUpload($request);
        $data['gallery_images'] = $this->productService->handleGalleryUploads($request);

        // Unique slug regardless of name quirks ("Silver–Ring" vs "Silver Ring")
        $data['slug'] = Product::uniqueSlug($data['name']);

        // Set null if sub_category_id is empty
        if (empty($data['sub_category_id'])) {
            $data['sub_category_id'] = null;
        }

        $product = $this->productService->addData($data);

        // Save variants
        $this->productService->saveVariants($product, $request);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    /**
     * Show the edit product form.
     */
    public function edit($id)
    {
        $product = $this->productService->getDataById($id);
        $categories = $this->categoryService->getAllData();
        $subCategories = $product->category_id
            ? $this->subCategoryService->getByCategoryId($product->category_id)
            : collect();

        return view('admin.products.edit', compact('product', 'categories', 'subCategories'));
    }

    /**
     * Update an existing product.
     */
    public function update(UpdateRequest $request, $id)
    {
        $product = $this->productService->getDataById($id);
        $data = $this->productService->getDataFromRequest($request);
        $data['image'] = $this->productService->handleImageUpload($request, $product->image);

        $existingGallery = $product->gallery_images ?? [];
        $data['gallery_images'] = $this->productService->handleGalleryUploads($request, $existingGallery);

        if (empty($data['sub_category_id'])) {
            $data['sub_category_id'] = null;
        }

        $updatedProduct = $this->productService->updateData($id, $data);

        // Save variants
        $this->productService->saveVariants($updatedProduct, $request);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Delete an existing product.
     */
    public function destroy($id)
    {
        $product = $this->productService->getDataById($id);

        if ($product && $product->image) {
            \Storage::disk('public')->delete($product->image);
        }

        $this->productService->deleteData($id);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }
}
