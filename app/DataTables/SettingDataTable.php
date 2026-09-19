<?php

namespace App\DataTables;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class SettingDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<Setting>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()

            ->editColumn('logo', function ($row) {
                return $row->logo
                    ? '<img src="'.asset('storage/'.$row->logo).'" width="60">'
                    : '-';
            })

            ->editColumn('favicon', function ($row) {
                return $row->favicon
                    ? '<img src="'.asset('storage/'.$row->favicon).'" width="40">'
                    : '-';
            })

            ->addColumn('action', function ($row) {
                $editRoute = route('admin.settings.edit', $row->id);
                $deleteRoute = route('admin.settings.destroy', $row->id);

                return view(
                    'admin.layouts.partials.dataTable-action-button',
                    compact('editRoute', 'deleteRoute')
                );
            })
            ->rawColumns(['logo', 'favicon', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Setting>
     */
    public function query(Setting $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('setting-table')
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
            Column::make('site_name')
                ->title('Site Name'),
            Column::make('email')
                ->title('Email'),
            Column::make('phone')
                ->title('Phone'),
            Column::make('whatsapp')
                ->title('Whatsapp'),
            Column::make('logo')
                ->title('Logo')
                ->addClass('text-center'),
            Column::make('favicon')
                ->title('Favicon')
                ->addClass('text-center'),
            Column::computed('action')
                ->title('Actions')
                ->exportable(false)
                ->printable(false)
                ->width(120)
                ->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Setting_'.date('YmdHis');
    }
}
