@extends('admin.layouts.app')
@section('styles')
<style>
    .page-header-card {
        background: linear-gradient(135deg, #40111F 0%, #6E5A42 100%);
        border-radius: 4px;
        padding: 2rem 2.5rem;
        color: #fff;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }
    .page-header-card::before {
        content: '';
        position: absolute;
        top: -40px; right: -40px;
        width: 180px; height: 180px;
        border-radius: 50%;
        background: rgba(255,255,255,0.06);
    }
    .page-header-card::after {
        content: '';
        position: absolute;
        bottom: -60px; right: 80px;
        width: 250px; height: 250px;
        border-radius: 50%;
        background: rgba(255,255,255,0.04);
    }
    .dt-card {
        background: #fff;
        border-radius: 1.25rem;
        box-shadow: 0 4px 24px rgba(0,0,0,0.05);
        overflow: hidden;
    }
    .dt-card .dt-card-body { padding: 1.5rem; }
    .search-bar-wrapper { margin-bottom: 1.25rem; }
    .search-bar-wrapper .dataTables_filter label {
        display: flex; align-items: center; gap: 0.5rem; font-weight: 500;
    }
    .search-bar-wrapper .dataTables_filter input {
        border: 1.5px solid #e0e0e0;
        border-radius: 2rem;
        padding: 0.45rem 1rem;
        font-size: 0.9rem;
        outline: none;
        transition: border-color 0.2s;
        width: 100%;
        max-width: 240px;
    }
    .search-bar-wrapper .dataTables_filter input:focus { border-color: #0A9051; }
    .yajra-table-custom-class table { width: 100% !important; }
    .yajra-table-custom-class thead th {
        background: #f8f9fa;
        font-weight: 700;
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #495057;
        border-bottom: 2px solid #e9ecef;
        padding: 0.85rem 1rem;
        white-space: nowrap;
    }
    .yajra-table-custom-class tbody tr { transition: background 0.15s; }
    .yajra-table-custom-class tbody tr:hover { background: #FAF4EA; }
    .yajra-table-custom-class tbody td { padding: 0.85rem 1rem; vertical-align: middle; }
    .pagination-wrapper { margin-top: 1.25rem; display: flex; justify-content: flex-end; }
    .dataTables_info { color: #6c757d; font-size: 0.875rem; margin-top: 1rem; }
    table.dataTable thead .sorting::after,
    table.dataTable thead .sorting_asc::after,
    table.dataTable thead .sorting_desc::after { opacity: 0.6; }
</style>
@endsection

@section('content')
<div class="page-header-card">
    <div class="d-flex justify-content-between align-items-center position-relative flex-wrap gap-3" style="z-index:1;">
        <div>
            <h1 class="fw-800 mb-1" style="font-size:1.75rem;">
                <i class="fa-solid fa-cart-shopping me-2 opacity-75"></i>
                Order Management
            </h1>
            <p class="mb-0 opacity-75">Track, fulfil and reconcile every customer order</p>
        </div>
        <div class="d-flex gap-2 flex-wrap order-status-filter">
            {{-- Quick filters: client-facing labels; data-dt-search carries the RAW
                 order_status value behind each label (server-side LIKE filtered).
                 Returned/Refunded are returns-module states — categories of their
                 own, deliberately NOT folded into Delivered. --}}
            <a href="javascript:void(0);" class="btn btn-light btn-sm rounded-pill px-3 fw-600" data-dt-search="">All</a>
            <a href="javascript:void(0);" class="btn btn-outline-light btn-sm rounded-pill px-3 fw-600" data-dt-search="pending">Placed</a>
            <a href="javascript:void(0);" class="btn btn-outline-light btn-sm rounded-pill px-3 fw-600" data-dt-search="processing">Processing</a>
            <a href="javascript:void(0);" class="btn btn-outline-light btn-sm rounded-pill px-3 fw-600" data-dt-search="shipped">Shipped</a>
            <a href="javascript:void(0);" class="btn btn-outline-light btn-sm rounded-pill px-3 fw-600" data-dt-search="completed">Delivered</a>
            <a href="javascript:void(0);" class="btn btn-outline-light btn-sm rounded-pill px-3 fw-600" data-dt-search="cancelled">Cancelled</a>
            <a href="javascript:void(0);" class="btn btn-outline-light btn-sm rounded-pill px-3 fw-600" data-dt-search="returned">Returned</a>
            <a href="javascript:void(0);" class="btn btn-outline-light btn-sm rounded-pill px-3 fw-600" data-dt-search="refunded">Refunded</a>
        </div>
    </div>
</div>

<div class="dt-card">
    <div class="dt-card-body table-responsive">
        {{ $dataTable->table(['class' => 'table table-hover align-middle']) }}
    </div>
</div>
@endsection

@section('scripts')
{{ $dataTable->scripts(attributes: ['type' => 'module']) }}
<script>
    // Status quick-filter pills: server-side (Yajra) filters the RAW order_status
    // column via LIKE, so pills carry the raw value behind each client-facing
    // label. No regex flag — Yajra cannot translate regexes to SQL (matches nothing).
    document.querySelectorAll('.order-status-filter [data-dt-search]').forEach(function (pill) {
        pill.addEventListener('click', function () {
            document.querySelectorAll('.order-status-filter [data-dt-search]').forEach(function (p) {
                p.classList.remove('btn-light');
                p.classList.add('btn-outline-light');
            });
            pill.classList.remove('btn-outline-light');
            pill.classList.add('btn-light');
            if (!window.jQuery) return;
            const table = window.jQuery('#order-table').DataTable();
            table.column(6).search(pill.getAttribute('data-dt-search'), false, false).draw();
        });
    });
</script>
@endsection
