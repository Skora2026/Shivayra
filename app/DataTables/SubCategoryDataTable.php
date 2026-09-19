<?php

namespace App\DataTables;

use App\Models\SubCategory;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class SubCategoryDataTable extends DataTable
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
                return $row->category ? '<span class="badge" style="background:rgba(0,71,39,0.1);color:#004727;font-weight:600;">'.e($row->category->name).'</span>' : 'N/A';
            })
            ->editColumn('status', function ($row) {
                if ($row->status === 'active') {
                    return '<span class="badge badge-active rounded-pill px-3 py-1">Active</span>';
                }

                return '<span class="badge badge-inactive rounded-pill px-3 py-1">Inactive</span>';
            })
            ->addColumn('action', function ($row) {
                $editRoute = route('admin.sub-categories.edit', $row->id);
                $deleteRoute = route('admin.sub-categories.destroy', $row->id);

                return view('admin.layouts.partials.dataTable-action-button', compact('editRoute', 'deleteRoute'));
            })
            ->rawColumns(['image_preview', 'category_name', 'status', 'action'])
            ->setRowId('id');
    }

    /**
     * Get query source of dataTable.
     */
    public function query(SubCategory $model): QueryBuilder
    {
        $query = $model->newQuery()->with('category:id,name');

        if ($categoryId = request()->get('category_id')) {
            $query->where('category_id', $categoryId);
        }

        return $query;
    }

    /**
     * Optional method if you want to use html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('sub-categories-table')
            ->columns($this->getColumns())
            ->minifiedAjax('', 'data.category_id = $("#filter-category").val();')
            ->dom("<'search-bar-wrapper'f>r<'table-wrapper yajra-table-custom-class table-responsive'tr><'pagination-wrapper'p>")
            ->orderBy(2, 'asc')
            ->parameters([
                'processing' => false,
                'language' => [
                    'searchPlaceholder' => 'Search sub-categories...',
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
            Column::make('name')->title('Name'),
            Column::computed('category_name')
                ->title('Category')
                ->orderable(false),
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
        return 'SubCategories_'.date('YmdHis');
    }
}
