@extends('admin.layouts.app')

@section('styles')
<style>
    .kpi-card {
        background: #fff;
        border-radius: 4px;
        border: 1px solid rgba(176, 141, 87, 0.22);
        padding: 1.35rem 1.5rem;
        height: 100%;
        transition: box-shadow .2s ease, transform .2s ease;
    }
    .kpi-card:hover {
        box-shadow: 0 12px 28px rgba(64, 17, 31, 0.10);
        transform: translateY(-2px);
    }
    .kpi-label {
        font-size: .7rem;
        letter-spacing: .12em;
        text-transform: uppercase;
        color: #8C7355;
        font-weight: 600;
    }
    .kpi-value {
        font-size: 1.6rem;
        font-weight: 700;
        color: #1C1C1C;
        line-height: 1.15;
    }
    .kpi-sub {
        font-size: .75rem;
        color: #9a938a;
    }
    .kpi-icon {
        width: 46px; height: 46px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 50%;
        background: rgba(176, 141, 87, 0.12);
        color: #B08D57;
        font-size: 1.1rem;
        flex: 0 0 auto;
    }
    .panel {
        background: #fff;
        border: 1px solid rgba(176, 141, 87, 0.22);
        border-radius: 4px;
    }
    .panel-head {
        padding: 1rem 1.35rem;
        border-bottom: 1px solid rgba(176, 141, 87, 0.18);
        font-weight: 600;
        color: #40111F;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .5rem;
    }
    .panel-head a { font-size: .8rem; font-weight: 600; }
    .status-pill {
        display: inline-block;
        padding: .25rem .6rem;
        font-size: .7rem;
        font-weight: 600;
        letter-spacing: .04em;
        border-radius: 2px;
        text-transform: uppercase;
    }
    .st-pending    { background: #F6EBD9; color: #8a6a1f; }
    .st-processing { background: #EDE4F6; color: #5b3a86; }
    .st-completed  { background: #E5EFE8; color: #2F6B44; }
    .st-cancelled  { background: #F7E2E4; color: #93333d; }
    .pay-paid      { background: #E5EFE8; color: #2F6B44; }
    .pay-pending   { background: #F6EBD9; color: #8a6a1f; }
    .pay-failed    { background: #F7E2E4; color: #93333d; }
    .chart-wrap { position: relative; height: 260px; }
    .mini-img {
        width: 38px; height: 38px;
        object-fit: cover;
        border: 1px solid rgba(176, 141, 87, 0.25);
        background: #FAF7F2;
    }
    .stock-chip {
        display: inline-block;
        min-width: 34px;
        text-align: center;
        font-weight: 700;
        font-size: .78rem;
        padding: .15rem .45rem;
        border-radius: 2px;
    }
    /* Small screens: panel heads wrap instead of pushing links off-canvas */
    @media (max-width: 575.98px) {
        .panel-head {
            flex-wrap: wrap;
            row-gap: .25rem;
        }
        .panel-head a {
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    }
    .stock-low { background: #F7E2E4; color: #93333d; }
    .stock-out { background: #40111F; color: #F3EDE4; }
</style>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
    <div>
        <h2 class="fw-700 text-dark mb-1">Dashboard</h2>
        <p class="text-muted mb-0">Store performance at a glance — {{ now()->format('l, d M Y') }}</p>
    </div>
    <a href="{{ route('admin.orders.index') }}" class="btn btn-custom-primary">
        <i class="fa-solid fa-boxes-packing me-2"></i>Process Orders
        @if(($statusCounts['pending'] ?? 0) > 0)
            <span class="badge bg-warning text-dark ms-2">{{ $statusCounts['pending'] }}</span>
        @endif
    </a>
</div>

{{-- KPI ROW --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="kpi-label">Revenue Today</div>
                    <div class="kpi-value">₹{{ number_format($revenueToday, 0) }}</div>
                    <div class="kpi-sub">{{ $ordersToday }} order{{ $ordersToday == 1 ? '' : 's' }} today</div>
                </div>
                <div class="kpi-icon"><i class="fa-solid fa-indian-rupee-sign"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="kpi-label">This Week</div>
                    <div class="kpi-value">₹{{ number_format($revenueWeek, 0) }}</div>
                    <div class="kpi-sub">{{ $ordersWeek }} order{{ $ordersWeek == 1 ? '' : 's' }} this week</div>
                </div>
                <div class="kpi-icon"><i class="fa-solid fa-calendar-week"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="kpi-label">This Month</div>
                    <div class="kpi-value">₹{{ number_format($revenueMonth, 0) }}</div>
                    <div class="kpi-sub">paid, non-cancelled</div>
                </div>
                <div class="kpi-icon"><i class="fa-solid fa-chart-column"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="kpi-label">Avg Order Value</div>
                    <div class="kpi-value">₹{{ number_format($avgOrderValue, 0) }}</div>
                    <div class="kpi-sub">{{ $customersCount }} registered customers</div>
                </div>
                <div class="kpi-icon"><i class="fa-solid fa-bag-shopping"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    {{-- REVENUE TREND --}}
    <div class="col-lg-8">
        <div class="panel h-100">
            <div class="panel-head">
                <span><i class="fa-solid fa-arrow-trend-up me-2" style="color:#B08D57;"></i>Revenue — last 14 days</span>
                <span class="kpi-sub">₹ total / day</span>
            </div>
            <div class="p-3">
                <div class="chart-wrap">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- STATUS BREAKDOWN --}}
    <div class="col-lg-4">
        <div class="panel h-100">
            <div class="panel-head"><span><i class="fa-solid fa-chart-pie me-2" style="color:#B08D57;"></i>Order Status Mix</span></div>
            <div class="p-3">
                @foreach(['pending', 'processing', 'completed', 'cancelled'] as $st)
                    @php $n = $statusCounts[$st] ?? 0; $total = max(1, $statusCounts->sum()); @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="status-pill st-{{ $st }}">{{ ucfirst($st) }}</span>
                            <strong class="text-dark">{{ $n }}</strong>
                        </div>
                        <div style="height:6px;background:#F3EDE4;border-radius:2px;">
                            <div style="height:6px;width:{{ round($n / $total * 100) }}%;border-radius:2px;
                                background: {{ ['pending' => '#C9A96A', 'processing' => '#8C7355', 'completed' => '#5C1A2E', 'cancelled' => '#B9AFa5'][$st] }};"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- PENDING ORDERS --}}
    <div class="col-lg-7">
        <div class="panel h-100">
            <div class="panel-head">
                <span><i class="fa-solid fa-hourglass-half me-2" style="color:#B08D57;"></i>Awaiting Action</span>
                <a href="{{ route('admin.orders.index') }}" class="icon-inline">All orders <svg class="icon"><use href="#i-arrow"/></svg></a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr class="font-xs text-uppercase text-muted" style="font-size:.68rem;letter-spacing:.08em;">
                            <th class="ps-3">Order</th>
                            <th>Customer</th>
                            <th>Payment</th>
                            <th class="text-end pe-3">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingOrders as $o)
                            <tr>
                                <td class="ps-3">
                                    <a href="{{ route('admin.orders.show', $o->id) }}" class="fw-600 text-decoration-none" style="color:#5C1A2E;">
                                        {{ $o->order_number }}
                                    </a>
                                    <div class="kpi-sub">{{ $o->created_at->format('d M, h:i A') }}</div>
                                </td>
                                <td>{{ $o->user->name ?? $o->name }}</td>
                                <td><span class="status-pill pay-{{ strtolower($o->payment_status) }}">{{ ucfirst($o->payment_status) }}</span></td>
                                <td class="text-end pe-3 fw-600">₹{{ number_format($o->total, 0) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">
                                <i class="fa-regular fa-circle-check me-2"></i>No pending orders — all caught up!
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- LOW STOCK --}}
    <div class="col-lg-5">
        <div class="panel h-100">
            <div class="panel-head">
                <span><i class="fa-solid fa-triangle-exclamation me-2" style="color:#B08D57;"></i>Low Stock (≤ 5)</span>
                <a href="{{ route('admin.products.index') }}" class="icon-inline">Products <svg class="icon"><use href="#i-arrow"/></svg></a>
            </div>
            <div class="p-2">
                @forelse($lowStock as $p)
                    <a href="{{ route('admin.products.edit', $p->id) }}" class="d-flex align-items-center gap-3 text-decoration-none text-dark p-2 rounded hover-row">
                        <img src="{{ $p->image_url }}" class="mini-img" alt="">
                        <div class="flex-grow-1 text-truncate">
                            <div class="fw-600 text-truncate">{{ $p->name }}</div>
                            <div class="kpi-sub">{{ $p->stock == 0 ? 'Out of stock' : 'Only '.$p->stock.' left' }}</div>
                        </div>
                        <span class="stock-chip {{ $p->stock == 0 ? 'stock-out' : 'stock-low' }}">{{ $p->stock }}</span>
                    </a>
                @empty
                    <div class="text-center text-muted py-4">
                        <i class="fa-regular fa-circle-check me-2"></i>Every product is well stocked.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    (function () {
        const ctx = document.getElementById('revenueChart');
        if (!ctx || typeof Chart === 'undefined') return;
        const data = @json($trend->map(fn ($t) => $t['revenue']));
        const labels = @json($trend->map(fn ($t) => $t['label']));
        const grad = ctx.getContext('2d').createLinearGradient(0, 0, 0, 240);
        grad.addColorStop(0, 'rgba(92, 26, 46, 0.22)');
        grad.addColorStop(1, 'rgba(92, 26, 46, 0)');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    data,
                    borderColor: '#5C1A2E',
                    backgroundColor: grad,
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2,
                    pointRadius: 2.5,
                    pointBackgroundColor: '#B08D57',
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(176,141,87,0.12)' }, ticks: { callback: v => '₹' + (v >= 1000 ? (v / 1000) + 'k' : v), color: '#8C7355', font: { size: 11 } } },
                    x: { grid: { display: false }, ticks: { color: '#8C7355', font: { size: 10 }, maxTicksLimit: 7 } }
                }
            }
        });
    })();
</script>
@endsection
