<?php

namespace App\DataTables;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class OrderDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<Order>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->editColumn('order_number', function ($row) {
                return '<a href="'.route('admin.orders.show', $row->id).'" class="fw-600 text-decoration-none" style="color:#5C1A2E;">'.$row->order_number.'</a>';
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at->format('d M Y, h:i A');
            })
            ->editColumn('total', function ($row) {
                return '₹'.number_format($row->total, 2);
            })
            ->editColumn('payment_status', function ($row) {
                $status = strtolower($row->payment_status);
                if ($status === 'paid') {
                    return '<span class="badge bg-success">Paid</span>';
                } elseif ($status === 'failed') {
                    return '<span class="badge bg-danger">Failed</span>';
                }

                return '<span class="badge bg-warning text-dark">Pending</span>';
            })
            ->editColumn('order_status', function ($row) {
                $status = strtolower($row->order_status);

                // Labels mirror the customer's shipment timeline exactly.
                return match ($status) {
                    'processing' => '<span class="badge bg-info text-white">Processing</span>',
                    'shipped' => '<span class="badge bg-primary">Shipped</span>',
                    'completed', 'delivered' => '<span class="badge bg-success">Delivered</span>',
                    'cancelled' => '<span class="badge bg-danger">Cancelled</span>',
                    'returned' => '<span class="badge bg-secondary">Returned</span>',
                    'refunded' => '<span class="badge bg-success">Refunded</span>',
                    default => '<span class="badge bg-warning text-dark">Placed</span>',
                };
            })
            ->addColumn('action', function ($row) {
                return '<div class="d-flex gap-1 justify-content-center">'
                    .'<a href="'.route('admin.orders.show', $row->id).'" class="btn btn-sm btn-dark" title="View & manage"><i class="fa-solid fa-eye"></i></a>'
                    .'<a href="tel:'.$row->phone.'" class="btn btn-sm btn-outline-secondary" title="Call customer"><i class="fa-solid fa-phone"></i></a>'
                    .'</div>';
            })
            ->rawColumns(['order_number', 'payment_status', 'order_status', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Order>
     */
    public function query(Order $model): QueryBuilder
    {
        return $model->newQuery()
            ->select('orders.*')
            ->orderBy('id', 'desc');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('order-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
            ->selectStyleSingle()
            ->buttons([
                Button::make('excel'),
                Button::make('csv'),
                Button::make('pdf'),
                Button::make('print'),
                Button::make('reset'),
                Button::make('reload'),
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
            Column::make('order_number')->title('Order No'),
            Column::make('name')->title('Customer'),
            Column::make('total')->title('Total Amount'),
            Column::make('payment_method')->title('Method'),
            Column::make('payment_status')->title('Payment Status'),
            Column::make('order_status')->title('Order Status'),
            Column::make('created_at')->title('Placed At'),
            Column::computed('action')
                ->title('Actions')
                ->exportable(false)
                ->printable(false)
                ->width(100)
                ->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Order_'.date('YmdHis');
    }
}
