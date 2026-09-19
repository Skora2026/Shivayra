<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\SubCategoryDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\SubCategory\CreateRequest;
use App\Http\Requests\SubCategory\UpdateRequest;
use App\Services\CategoryService;
use App\Services\SubCategoryService;
use Illuminate\Http\Request;

class SubCategoryController extends Controller
{
    protected $subCategoryService;

    protected $categoryService;

    /**
     * Create a new Controller instance.
     */
    public function __construct(SubCategoryService $subCategoryService, CategoryService $categoryService)
    {
        $this->subCategoryService = $subCategoryService;
        $this->categoryService = $categoryService;
    }

    /**
     * Render the sub-categories grid using Yajra DataTables.
     */
    public function index(SubCategoryDataTable $dataTable)
    {
        return $dataTable->render('admin.sub-categories.index');
    }

    /**
     * Show the sub-category creation form.
     */
    public function create()
    {
        $categories = $this->categoryService->getAllData();

        return view('admin.sub-categories.create', compact('categories'));
    }

    /**
     * Store a newly created sub-category.
     */
    public function store(CreateRequest $request)
    {
        $data = $this->subCategoryService->getDataFromRequest($request);
        $data['image'] = $this->subCategoryService->handleImageUpload($request);

        $this->subCategoryService->addData($data);

        return redirect()->route('admin.sub-categories.index')
            ->with('success', 'Sub-Category created successfully.');
    }

    /**
     * Show the edit sub-category form.
     */
    public function edit($id)
    {
        $subCategory = $this->subCategoryService->getDataById($id);
        $categories = $this->categoryService->getAllData();

        return view('admin.sub-categories.edit', compact('subCategory', 'categories'));
    }

    /**
     * Update an existing sub-category.
     */
    public function update(UpdateRequest $request, $id)
    {
        $subCategory = $this->subCategoryService->getDataById($id);
        $data = $this->subCategoryService->getDataFromRequest($request);
        $data['image'] = $this->subCategoryService->handleImageUpload($request, $subCategory->image);

        $this->subCategoryService->updateData($id, $data);

        return redirect()->route('admin.sub-categories.index')
            ->with('success', 'Sub-Category updated successfully.');
    }

    /**
     * Delete an existing sub-category.
     */
    public function destroy($id)
    {
        $subCategory = $this->subCategoryService->getDataById($id);

        if ($subCategory && $subCategory->image) {
            \Storage::disk('public')->delete($subCategory->image);
        }

        $this->subCategoryService->deleteData($id);

        return redirect()->route('admin.sub-categories.index')
            ->with('success', 'Sub-Category deleted successfully.');
    }

    /**
     * AJAX endpoint: Get sub-categories by category ID.
     */
    public function byCategoryAjax(Request $request, $category_id)
    {
        $subCategories = $this->subCategoryService->getByCategoryId((int) $category_id);

        return response()->json($subCategories);
    }
}
