<?php

namespace App\DataTables;

use App\Models\Category;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class CategoryDataTable extends DataTable
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
            ->editColumn('status', function ($row) {
                if ($row->status === 'active') {
                    return '<span class="badge badge-active rounded-pill px-3 py-1">Active</span>';
                }

                return '<span class="badge badge-inactive rounded-pill px-3 py-1">Inactive</span>';
            })
            ->addColumn('action', function ($row) {
                $editRoute = route('admin.categories.edit', $row->id);
                $deleteRoute = route('admin.categories.destroy', $row->id);

                return view('admin.layouts.partials.dataTable-action-button', compact('editRoute', 'deleteRoute'));
            })
            ->rawColumns(['image_preview', 'status', 'action'])
            ->setRowId('id');
    }

    /**
     * Get query source of dataTable.
     */
    public function query(Category $model): QueryBuilder
    {
        return $model->newQuery()->withCount(['subCategories', 'products']);
    }

    /**
     * Optional method if you want to use html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('categories-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom("<'search-bar-wrapper'f>r<'table-wrapper yajra-table-custom-class table-responsive'tr><'pagination-wrapper'p>")
            ->orderBy(2, 'asc')
            ->parameters([
                'processing' => false,
                'language' => [
                    'searchPlaceholder' => 'Search categories...',
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
        return 'Categories_'.date('YmdHis');
    }
}
