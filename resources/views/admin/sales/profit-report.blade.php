@extends('admin.layouts.main')

@section('title', 'Profit Report')
@section('pageHeading', 'Profit Report')

@section('content')
<div class="sales-dashboard">
    <div class="dashboard-content__head">
        <h2 class="boldHeading">Profit Report</h2>

        <nav aria-label="breadcrumb">
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route(auth()->user()->dashboardRouteName()) }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Profit Report</li>
            </ul>
        </nav>
    </div>

    <div class="dashboard-card mb-4">
        <form action="{{ route('admin.reports.profit') }}" method="GET">
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
                    <label class="form-label">Partner</label>
                    <select class="form-control js-select2" name="partner_id">
                        <option value="all" {{ ($filters['partner_id'] ?? 'all') === 'all' ? 'selected' : '' }}>All Partners</option>
                        @foreach ($partners as $partner)
                            <option value="{{ $partner->id }}" {{ (string) $filters['partner_id'] === (string) $partner->id ? 'selected' : '' }}>
                                {{ $partner->name }}
                            </option>
                        @endforeach
                    </select>
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
                    <a href="{{ route('admin.reports.profit') }}" class="themeBtn themeBtn--white w-100 text-center">Reset</a>
                </div>
            </div>
        </form>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-2 col-md-6">
            <div class="account-card account-card--yellow">
                <div class="account-card__content">
                    <h3 class="account-card__title">Total Sales</h3>
                    <p class="metric-card__value">{{ $summary['total_sales'] }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-6">
            <div class="account-card account-card--teal">
                <div class="account-card__content">
                    <h3 class="account-card__title">Partner Profit</h3>
                    <p class="metric-card__value">{{ number_format((float) $summary['partner_profit'], 2) }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-6">
            <div class="account-card account-card--orange">
                <div class="account-card__content">
                    <h3 class="account-card__title">Expense Deduction</h3>
                    <p class="metric-card__value">{{ number_format((float) $summary['expense_deductions'], 2) }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-6">
            <div class="account-card account-card--red">
                <div class="account-card__content">
                    <h3 class="account-card__title">Total Deductions</h3>
                    <p class="metric-card__value">{{ number_format((float) $summary['total_deductions'], 2) }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-6">
            <div class="account-card account-card--teal">
                <div class="account-card__content">
                    <h3 class="account-card__title">Net Partner Profit</h3>
                    <p class="metric-card__value">{{ number_format((float) $summary['net_partner_profit'], 2) }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-6">
            <div class="account-card account-card--red">
                <div class="account-card__content">
                    <h3 class="account-card__title">Admin Profit</h3>
                    <p class="metric-card__value">{{ number_format((float) $summary['admin_profit'], 2) }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-6">
            <div class="account-card account-card--yellow">
                <div class="account-card__content">
                    <h3 class="account-card__title">Grand Total</h3>
                    <p class="metric-card__value">{{ number_format((float) $summary['grand_total'], 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="dashboard-card mb-4">
        <div class="sales-table__header">
            <div class="sales-table__header-left">
                <span class="fw-semibold">Partner Wise Summary</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover tables">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Partner</th>
                        <th>Sales Count</th>
                        <th>Actual Investment</th>
                        <th>Total Profit</th>
                        <th>Expense</th>
                        <th>Salary</th>
                        <th>Deductions</th>
                        <th>Net Profit</th>
                        <th>Withdrawn</th>
                        <th>Available</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($partnerSummaries as $partnerSummary)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $partnerSummary['partner_name'] }}</td>
                            <td>{{ $partnerSummary['sales_count'] }}</td>
                            <td>{{ number_format((float) $partnerSummary['actual_amount'], 2) }}</td>
                            <td>{{ number_format((float) $partnerSummary['profit_amount'], 2) }}</td>
                            <td>{{ number_format((float) $partnerSummary['expense_deduction_amount'], 2) }}</td>
                            <td>{{ number_format((float) $partnerSummary['salary_deduction_amount'], 2) }}</td>
                            <td>{{ number_format((float) $partnerSummary['deduction_amount'], 2) }}</td>
                            <td>{{ number_format((float) $partnerSummary['net_profit'], 2) }}</td>
                            <td>{{ number_format((float) $partnerSummary['withdrawn_amount'], 2) }}</td>
                            <td>{{ number_format((float) $partnerSummary['available_balance'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center">Selected filters ke liye koi partner profit nahi mila.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="dashboard-card mb-4">
        <div class="sales-table__header">
            <div class="sales-table__header-left">
                <span class="fw-semibold">Deduction Entries</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover tables">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Partner</th>
                        <th>Type</th>
                        <th>Label</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($deductionEntries as $entry)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $entry['partner_name'] }}</td>
                            <td>{{ ucfirst($entry['type']) }}</td>
                            <td>{{ $entry['label'] }}</td>
                            <td>{{ number_format((float) $entry['amount'], 2) }}</td>
                            <td>{{ $entry['date'] ? \Illuminate\Support\Carbon::parse($entry['date'])->format('d-M-Y') : 'N/A' }}</td>
                            <td>{{ $entry['notes'] ?: 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Selected filters ke liye koi deduction entry nahi mili.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="dashboard-card">
        <div class="sales-table-section">
            <div class="sales-table__header">
                <div class="sales-table__header-left">
                    <span class="fw-semibold">Profit Details</span>
                </div>
                <a href="{{ route('admin.reports.profit.csv', request()->only('from_date', 'to_date', 'status', 'partner_id')) }}" class="themeBtn themeBtn--green">
                    <i class="fas fa-file-csv me-1"></i> Export CSV
                </a>
            </div>

            <div class="table-responsive">
                <table id="salesTable" data-laravel-pagination="true" class="table table-hover tables">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Receipt</th>
                            <th>Customer</th>
                            <th>Partner Detail</th>
                            <th>Partner Profit</th>
                            <th>Admin Profit</th>
                            <th>Grand Total</th>
                            <th>Date</th>
                            <th>Status</th>
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
                                    @php($partnerDetails = collect($salePartnerDetails[$sale->id] ?? []))
                                    @forelse ($partnerDetails as $partnerDetail)
                                        <div class="mb-2">
                                            <strong>{{ $partnerDetail['partner_name'] }}</strong>
                                            ({{ number_format((float) $partnerDetail['ownership_percentage'], 2) }}%)
                                            <br>
                                            <small>{{ implode(', ', $partnerDetail['product_names']) }}</small>
                                            <br>
                                            {{ number_format((float) $partnerDetail['profit_amount'], 2) }}
                                        </div>
                                    @empty
                                        <div>N/A</div>
                                    @endforelse
                                </td>
                                <td>{{ number_format((float) $partnerDetails->sum('profit_amount'), 2) }}</td>
                                <td>{{ number_format((float) $sale->items->sum(fn ($item) => ((((float) $item->price) - (float) ($item->product?->unit_purchase_price ?? 0)) * (int) $item->quantity) - (float) $item->partner_profit), 2) }}</td>
                                <td>{{ number_format((float) $sale->grand_total, 2) }}</td>
                                <td>{{ $sale->sale_date?->format('d-M-Y') }}</td>
                                <td>
                                    <span class="badge {{ $sale->status === 'completed' ? 'active' : 'inActive' }}">
                                        {{ ucfirst($sale->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.sales.show', $sale) }}" class="btn btn-sm btn-outline-primary">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">No profit report found for selected filters.</td>
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
