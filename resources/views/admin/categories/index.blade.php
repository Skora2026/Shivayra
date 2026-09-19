@extends('admin.layouts.app')

@section('styles')
<style>
    .page-header-card {
        background: linear-gradient(135deg, #004727 0%, #0A9051 100%);
        border-radius: 1.5rem;
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
        min-width: 240px;
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
    .yajra-table-custom-class tbody tr:hover { background: #f0fdf4; }
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
    <div class="d-flex justify-content-between align-items-center position-relative" style="z-index:1;">
        <div>
            <h1 class="fw-800 mb-1" style="font-size:1.75rem;">
                <i class="fa-solid fa-tags me-2 opacity-75"></i>
                {{ __('labels.all_categories') }}
            </h1>
            <p class="mb-0 opacity-75">Manage your product categories</p>
        </div>
        <a href="{{ route('admin.categories.create') }}" 
           class="btn btn-light fw-600 rounded-pill px-4 py-2 d-flex align-items-center gap-2"
           style="color:#004727;">
            <i class="fa-solid fa-plus"></i>
            {{ __('labels.create_new_category') }}
        </a>
    </div>
</div>

<div class="dt-card">
    <div class="dt-card-body">
        {{ $dataTable->table(['class' => 'table table-hover align-middle']) }}
    </div>
</div>
@endsection

@section('scripts')
{{ $dataTable->scripts(attributes: ['type' => 'module']) }}
<script>
    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function () {
        var tooltipEls = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipEls.map(el => new bootstrap.Tooltip(el));
    });
</script>
@endsection
