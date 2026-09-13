@extends('admin.layouts.main')

@section('title', 'Sales Report')
@section('pageHeading', 'Sales Report')

@section('content')
<div class="sales-dashboard">
    <div class="dashboard-content__head">
        <h2 class="boldHeading">Sales Report</h2>

        <nav aria-label="breadcrumb">
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route(auth()->user()->dashboardRouteName()) }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Sales Report</li>
            </ul>
        </nav>
    </div>

    <div class="dashboard-card mb-4">
        <form action="{{ route('admin.reports.sales') }}" method="GET">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">From Date</label>
                    <input type="date" class="form-control" name="from_date" value="{{ $filters['from_date'] }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label">To Date</label>
                    <input type="date" class="form-control" name="to_date" value="{{ $filters['to_date'] }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select class="form-control js-select2" name="status">
                        <option value="">All</option>
                        <option value="completed" {{ $filters['status'] === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="draft" {{ $filters['status'] === 'draft' ? 'selected' : '' }}>Draft</option>
                    </select>
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="themeBtn w-100">Search</button>
                    <a href="{{ route('admin.reports.sales') }}" class="themeBtn themeBtn--white w-100 text-center">Reset</a>
                </div>
            </div>
        </form>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="account-card account-card--yellow">
                <div class="account-card__content">
                    <h3 class="account-card__title">Total Sales</h3>
                    <p class="metric-card__value">{{ $summary['total_sales'] }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="account-card account-card--teal">
                <div class="account-card__content">
                    <h3 class="account-card__title">Completed</h3>
                    <p class="metric-card__value">{{ $summary['completed'] }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="account-card account-card--red">
                <div class="account-card__content">
                    <h3 class="account-card__title">Draft</h3>
                    <p class="metric-card__value">{{ $summary['drafts'] }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="account-card account-card--yellow">
                <div class="account-card__content">
                    <h3 class="account-card__title">Grand Total</h3>
                    <p class="metric-card__value">{{ number_format((float) $summary['grand_total'], 2) }}</p>
                </div>
            </div>
        </div>

    </div>

    <div class="dashboard-card">
        <div class="sales-table-section">
            <div class="sales-table__header">
                <div class="sales-table__header-left">
                    <span class="fw-semibold">Filtered Sales Report</span>
                </div>
                <div class="sales-table__header-left">
                    <span class="sales-table__header-right me-3">Qty Sold: {{ $summary['qty_sold'] }}</span>
                    <a href="{{ route('admin.reports.sales.csv', request()->only('from_date', 'to_date', 'status')) }}" class="themeBtn themeBtn--green">
                        <i class="fas fa-file-csv me-1"></i> Export CSV
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table id="salesTable" data-laravel-pagination="true" class="table table-hover tables">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Receipt</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total Qty</th>
                            <th>Subtotal</th>
                            <th>Grand Total</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sales as $sale)
                            <tr>
                                <td>{{ $sales->firstItem() + $loop->index }}</td>
                                <td>{{ $sale->receipt_no }}</td>
                                <td>{{ $sale->customer_name }}</td>
                                <td>
                                    @foreach ($sale->items as $item)
                                        <div>{{ $item->product_name }} ({{ $item->quantity }})</div>
                                    @endforeach
                                </td>
                                <td>{{ $sale->items->sum('quantity') }}</td>
                                <td>{{ number_format((float) $sale->subtotal, 2) }}</td>
                                <td>{{ number_format((float) $sale->grand_total, 2) }}</td>
                                <td>
                                    <span class="badge {{ $sale->status === 'completed' ? 'active' : 'inActive' }}">
                                        {{ ucfirst($sale->status) }}
                                    </span>
                                </td>
                                <td>{{ $sale->sale_date?->format('d-M-Y') }}</td>
                                <td>
                                    <div class="account-action-links">
                                        <a href="{{ route('admin.sales.show', $sale) }}" class="btn btn-sm btn-outline-primary account-icon-btn" title="View Sale" aria-label="View Sale">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <form action="{{ route('admin.sales.destroy', $sale) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to remove this sale?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger account-icon-btn" title="Remove Sale" aria-label="Remove Sale">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">No sales found for selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $sales->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection
