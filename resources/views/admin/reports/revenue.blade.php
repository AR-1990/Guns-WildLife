@extends('admin.layouts.main')

@section('title', 'Revenue Report')
@section('pageHeading', 'Revenue Report')

@section('content')
<div class="sales-dashboard">
    <div class="dashboard-content__head">
        <h2 class="boldHeading">Revenue Report</h2>
        <nav aria-label="breadcrumb">
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route(auth()->user()->dashboardRouteName()) }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Revenue Report</li>
            </ul>
        </nav>
    </div>

    <div class="dashboard-card mb-4">
        <form action="{{ route('admin.reports.revenue') }}" method="GET">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">From Date</label>
                    <input type="date" class="form-control" name="from_date" value="{{ $filters['from_date'] }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">To Date</label>
                    <input type="date" class="form-control" name="to_date" value="{{ $filters['to_date'] }}">
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="themeBtn w-100">Search</button>
                    <a href="{{ route('admin.reports.revenue') }}" class="themeBtn themeBtn--white w-100 text-center">Reset</a>
                </div>
            </div>
        </form>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="account-card account-card--yellow">
                <div class="account-card__content">
                    <h3 class="account-card__title">Sales</h3>
                    <p class="metric-card__value">{{ $summary['sales'] }}</p>
                    <small class="text-muted">{{ $summary['qty'] }} units sold</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="account-card account-card--orange">
                <div class="account-card__content">
                    <h3 class="account-card__title">Total Revenue</h3>
                    <p class="metric-card__value">Rs. {{ number_format((float) $summary['revenue'], 2) }}</p>
                    <small class="text-muted">Subtotal Rs. {{ number_format((float) $summary['subtotal'], 2) }}</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="account-card account-card--green">
                <div class="account-card__content">
                    <h3 class="account-card__title">Profit</h3>
                    <p class="metric-card__value">Rs. {{ number_format((float) $summary['profit'], 2) }}</p>
                    <small class="text-muted">Positive sale profit in selected range</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="account-card account-card--red">
                <div class="account-card__content">
                    <h3 class="account-card__title">Loss</h3>
                    <p class="metric-card__value">Rs. {{ number_format((float) $summary['loss'], 2) }}</p>
                    <small class="text-muted">Loss-making sales in selected range</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-4 col-md-6">
            <div class="account-card account-card--red">
                <div class="account-card__content">
                    <h3 class="account-card__title">Expenses</h3>
                    <p class="metric-card__value">Rs. {{ number_format((float) $summary['expenses'], 2) }}</p>
                    <small class="text-muted">{{ $summary['expense_records'] }} expense records in selected range</small>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="account-card account-card--teal">
                <div class="account-card__content">
                    <h3 class="account-card__title">Net Profit</h3>
                    <p class="metric-card__value {{ (float) $summary['net_profit'] < 0 ? 'text-danger' : '' }}">Rs. {{ number_format((float) $summary['net_profit'], 2) }}</p>
                    <small class="text-muted">Profit - Loss - Expense</small>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="account-card account-card--yellow">
                <div class="account-card__content">
                    <h3 class="account-card__title">Formula</h3>
                    <p class="metric-card__value">Rs. {{ number_format((float) $summary['profit'], 2) }} - {{ number_format((float) $summary['loss'], 2) }}</p>
                    <small class="text-muted">Then minus expense Rs. {{ number_format((float) $summary['expenses'], 2) }}</small>
                </div>
            </div>
        </div>
    </div>

    <div class="dashboard-card">
        <div class="sales-table-section">
            <div class="sales-table__header">
                <span class="fw-semibold">Revenue Details</span>
                <a href="{{ route('admin.reports.revenue.csv', request()->only('from_date', 'to_date')) }}" class="themeBtn themeBtn--green">
                    <i class="fas fa-file-csv me-1"></i> Export CSV
                </a>
            </div>
            <div class="table-responsive">
                <table id="revenueTable" class="table table-hover tables">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Receipt</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Qty</th>
                            <th>Sale Price</th>
                            <th>Actual Cost</th>
                            <th>Net Profit</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sales as $sale)
                            @php
                                $row = $saleRowComputed[$sale->id] ?? ['cogs'=>0,'gross_profit'=>0,'admin_profit'=>0,'margin'=>0];
                            @endphp
                            <tr>
                                <td>{{ $sales->firstItem() + $loop->index }}</td>
                                <td>{{ $sale->receipt_no }}</td>
                                <td>{{ $sale->sale_date?->format('d-M-Y') }}</td>
                                <td>
                                    <div>{{ $sale->customer_name }}</div>
                                    @if(!empty($sale->customer_phone))
                                        <small class="text-muted">{{ $sale->customer_phone }}</small>
                                    @endif
                                </td>
                                <td>{{ $sale->items->count() }}</td>
                                <td>{{ $sale->items->sum('quantity') }}</td>
                                <td class="fw-semibold">{{ number_format((float) $sale->grand_total, 2) }}</td>
                                <td>{{ number_format((float) $row['cogs'], 2) }}</td>
                                <td class="{{ (float) $row['admin_profit'] < 0 ? 'text-danger' : 'text-primary' }}">
                                    {{ number_format((float) $row['admin_profit'], 2) }}
                                </td>
                                <td><a href="{{ route('admin.sales.show', $sale) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="text-center">No revenue record found.</td></tr>
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
