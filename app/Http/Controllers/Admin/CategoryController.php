<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\CategoryDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Category\CreateRequest;
use App\Http\Requests\Category\UpdateRequest;
use App\Services\CategoryService;

class CategoryController extends Controller
{
    protected $categoryService;

    /**
     * Create a new Controller instance.
     */
    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * Render the categories grid using Yajra DataTables.
     */
    public function index(CategoryDataTable $dataTable)
    {
        return $dataTable->render('admin.categories.index');
    }

    /**
     * Show the category creation form.
     */
    public function create()
    {
        return view('admin.categories.create');
    }

    /**
     * Store a newly created category.
     */
    public function store(CreateRequest $request)
    {
        $data = $this->categoryService->getDataFromRequest($request);
        $data['image'] = $this->categoryService->handleImageUpload($request);

        $this->categoryService->addData($data);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Show the edit category form.
     */
    public function edit($id)
    {
        $category = $this->categoryService->getDataById($id);

        return view('admin.categories.edit', compact('category'));
    }

    /**
     * Update an existing category.
     */
    public function update(UpdateRequest $request, $id)
    {
        $category = $this->categoryService->getDataById($id);
        $data = $this->categoryService->getDataFromRequest($request);
        $data['image'] = $this->categoryService->handleImageUpload($request, $category->image);

        $this->categoryService->updateData($id, $data);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Delete an existing category.
     */
    public function destroy($id)
    {
        $category = $this->categoryService->getDataById($id);

        // Delete the image from storage if it exists
        if ($category && $category->image) {
            \Storage::disk('public')->delete($category->image);
        }

        $this->categoryService->deleteData($id);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
