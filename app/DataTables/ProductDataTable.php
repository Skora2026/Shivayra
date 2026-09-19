<?php

namespace App\DataTables;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ProductDataTable extends DataTable
{
    /**
     * Build DataTable class.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('image_preview', function ($row) {
                $url = $row->image_url;

                return '<img src="'.$url.'" alt="'.e($row->name).'" 
                    class="rounded" style="width:50px;height:50px;object-fit:cover;border:2px solid #e9ecef;">';
            })
            ->addColumn('category_name', function ($row) {
                return $row->category ? e($row->category->name) : 'N/A';
            })
            ->addColumn('sub_category_name', function ($row) {
                return $row->subCategory ? e($row->subCategory->name) : '<span class="text-muted fst-italic">None</span>';
            })
            ->addColumn('price_display', function ($row) {
                if ($row->sale_price) {
                    return '<span class="text-danger fw-bold">₹'.number_format($row->sale_price, 2).'</span>
                            <br><small class="text-muted text-decoration-line-through">₹'.number_format($row->price, 2).'</small>';
                }

                return '<span class="fw-bold">₹'.number_format($row->price, 2).'</span>';
            })
            ->addColumn('stock_display', function ($row) {
                $color = $row->stock > 10 ? 'success' : ($row->stock > 0 ? 'warning' : 'danger');

                return '<span class="badge bg-'.$color.'-subtle text-'.$color.' rounded-pill px-2">'.$row->stock.'</span>';
            })
            ->editColumn('status', function ($row) {
                if ($row->status === 'active') {
                    return '<span class="badge badge-active rounded-pill px-3 py-1">Active</span>';
                }

                return '<span class="badge badge-inactive rounded-pill px-3 py-1">Inactive</span>';
            })
            ->addColumn('action', function ($row) {
                $editRoute = route('admin.products.edit', $row->id);
                $deleteRoute = route('admin.products.destroy', $row->id);

                return view('admin.layouts.partials.dataTable-action-button', compact('editRoute', 'deleteRoute'));
            })
            ->rawColumns(['image_preview', 'category_name', 'sub_category_name', 'price_display', 'stock_display', 'status', 'action'])
            ->setRowId('id');
    }

    public function query(Product $model): QueryBuilder
    {
        $query = $model->newQuery()->with(['category:id,name', 'subCategory:id,name']);

        if ($categoryId = request()->get('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($subCategoryId = request()->get('sub_category_id')) {
            $query->where('sub_category_id', $subCategoryId);
        }

        return $query;
    }

    /**
     * Optional method if you want to use html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('products-table')
            ->columns($this->getColumns())
            ->minifiedAjax('', 'data.category_id = $("#filter-category").val(); data.sub_category_id = $("#filter-subcategory").val();')
            ->dom("<'search-bar-wrapper'f>r<'table-wrapper yajra-table-custom-class table-responsive'tr><'pagination-wrapper'p>")
            ->orderBy(2, 'asc')
            ->parameters([
                'processing' => false,
                'language' => [
                    'searchPlaceholder' => 'Search products...',
                ],
            ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::computed('DT_RowIndex')
                ->title('#')
                ->width(50)
                ->addClass('text-center'),
            Column::computed('image_preview')
                ->title('Image')
                ->width(70)
                ->addClass('text-center')
                ->orderable(false)
                ->searchable(false),
            Column::make('name')->title('Product Name'),
            Column::computed('category_name')
                ->title('Category')
                ->orderable(false),
            Column::computed('sub_category_name')
                ->title('Sub-Category')
                ->orderable(false),
            Column::computed('price_display')
                ->title('Price')
                ->orderable(false)
                ->addClass('text-center'),
            Column::computed('stock_display')
                ->title('Stock')
                ->orderable(false)
                ->addClass('text-center'),
            Column::make('status')->title('Status')->addClass('text-center'),
            Column::computed('action')
                ->title('Actions')
                ->exportable(false)
                ->printable(false)
                ->width(120)
                ->addClass('text-center'),
        ];
    }

    /**
     * Get filename for export.
     */
    protected function filename(): string
    {
        return 'Products_'.date('YmdHis');
    }
}
