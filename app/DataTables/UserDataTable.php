<?php

namespace App\DataTables;

use App\Helpers\UserHelper;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class UserDataTable extends DataTable
{
    private $user;

    /**
     * Create a new DataTable instance.
     */
    public function __construct()
    {
        $this->user = UserHelper::getLoggedInUser();
    }

    /**
     * Build DataTable class.
     *
     * @param  QueryBuilder  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $user = $this->user;

        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('action', function ($row) use ($user) {
                // Map actions using admin user resource routes
                $editRoute = $user->can('user-edit') ? route('admin.users.edit', $row->id) : '';
                $deleteRoute = ($user->can('user-delete') && $user->id !== $row->id) ? route('admin.users.destroy', $row->id) : '';

                return view('admin.layouts.partials.dataTable-action-button', compact('editRoute', 'deleteRoute'));
            })
            ->editColumn('role', function ($row) {
                return $row->roles->pluck('name')->implode(', ') ?: __('labels.inactive');
            })
            ->editColumn('last_name', function ($row) {
                return $row->last_name ?: 'N/A';
            })
            ->editColumn('status', function ($row) {
                if (strtolower($row->status) === 'active') {
                    return '<span class="badge badge-active rounded-pill px-3 py-1.5">'.__('labels.active').'</span>';
                }

                return '<span class="badge badge-inactive rounded-pill px-3 py-1.5">'.__('labels.inactive').'</span>';
            })
            ->rawColumns(['status', 'action'])
            ->setRowId('id');
    }

    /**
     * Get query source of dataTable.
     */
    public function query(User $model): QueryBuilder
    {
        // Get all users eager loading roles
        return $model->newQuery()->with('roles:name');
    }

    /**
     * Optional method if you want to use html builder.
     */
    public function html(): HtmlBuilder
    {
        $dataTable = $this->builder()
            ->setTableId('users-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom("<'search-bar-wrapper'f>r<'table-wrapper yajra-table-custom-class table-responsive'tr><'pagination-wrapper'p>")
            ->orderBy(1, 'asc');

        return $dataTable->parameters([
            'processing' => false,
            'language' => [
                'searchPlaceholder' => __('labels.search_placeholder', ['default' => 'Search...']),
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
                ->title(__('labels.sl_no'))
                ->width(50)
                ->addClass('text-center'),
            Column::make('first_name')->title(__('labels.first_name')),
            Column::make('last_name')->title(__('labels.last_name')),
            Column::make('email')->title(__('labels.email')),
            Column::computed('role')->title(__('labels.role')),
            Column::make('status')->title(__('labels.status')),
            Column::computed('action')->title(__('labels.actions'))
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
        return 'User_'.date('YmdHis');
    }
}
