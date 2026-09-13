@extends('admin.layouts.main')

@section('title', 'View Sale')
@section('pageHeading', 'View Sale')

@section('content')
<div class="sales-dashboard">
    <div class="dashboard-content__head">
        <h2 class="boldHeading">Sale Detail</h2>

        <nav aria-label="breadcrumb">
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route(auth()->user()->dashboardRouteName()) }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.sales.index') }}">Sales</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $sale->receipt_no }}</li>
            </ul>
        </nav>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="account-card account-card--yellow">
                <div class="account-card__content">
                    <h3 class="account-card__title">Receipt No</h3>
                    <p class="metric-card__value">{{ $sale->receipt_no }}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="account-card account-card--teal">
                <div class="account-card__content">
                    <h3 class="account-card__title">Grand Total</h3>
                    <p class="metric-card__value">{{ number_format((float) $sale->grand_total, 2) }}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="account-card account-card--orange">
                <div class="account-card__content">
                    <h3 class="account-card__title">Items Count</h3>
                    <p class="metric-card__value">{{ $sale->items->count() }}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="account-card account-card--red">
                <div class="account-card__content">
                    <h3 class="account-card__title">Status</h3>
                    <p class="metric-card__value">{{ ucfirst($sale->status) }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="account-toolbar">
        <div>
            <h3 class="account-toolbar__title">Sale Information</h3>
            <p class="account-toolbar__text">Professional detail view for receipt, customer, totals, and item profits.</p>
        </div>
        <div class="account-toolbar__group">
            <a href="{{ route('admin.sales.pdf', $sale) }}" class="themeBtn themeBtn--green">
                <i class="fas fa-file-pdf me-1"></i> Download PDF
            </a>
            <a href="{{ route('admin.sales.print', $sale) }}" target="_blank" class="themeBtn">
                <i class="fas fa-print me-1"></i> Print Receipt
            </a>
            <span class="badge {{ $sale->status === 'completed' ? 'active' : 'inActive' }}">
                {{ ucfirst($sale->status) }}
            </span>
            <a href="{{ route('admin.sales.index') }}" class="themeBtn themeBtn--white">Back To Sales</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="account-ledger mt-0">
                <div class="account-ledger__header">
                    <h3 class="account-ledger__title">Order Information</h3>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Receipt No</label>
                        <input type="text" class="form-control" value="{{ $sale->receipt_no }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Document Type</label>
                        <input type="text" class="form-control" value="{{ \Illuminate\Support\Str::title(str_replace('_', ' ', $sale->document_type)) }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Sale Date</label>
                        <input type="text" class="form-control" value="{{ $sale->sale_date?->format('d-M-Y') }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Customer Name</label>
                        <input type="text" class="form-control" value="{{ $sale->customer_name }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone Number</label>
                        <input type="text" class="form-control" value="{{ $sale->customer_phone ?: 'N/A' }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Reference / PO No.</label>
                        <input type="text" class="form-control" value="{{ $sale->reference ?: 'N/A' }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Created By</label>
                        <input type="text" class="form-control" value="{{ $sale->creator?->name ?: 'System' }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Total Quantity</label>
                        <input type="text" class="form-control" value="{{ $sale->items->sum('quantity') }}" readonly>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="payment-summary">
                <h3 class="payment-summary__title">Order Summary</h3>
                <div class="payment-summary__content">
                    <div class="payment-summary__row">
                        <span>Subtotal</span>
                        <strong>{{ number_format((float) $sale->subtotal, 2) }}</strong>
                    </div>
                    <div class="payment-summary__row">
                        <span>Discount %</span>
                        <strong>{{ number_format((float) $sale->discount_percent, 2) }}</strong>
                    </div>
                    <div class="payment-summary__row">
                        <span>Discount Amount</span>
                        <strong>{{ number_format((float) $discountAmount, 2) }}</strong>
                    </div>
                    <div class="payment-summary__row">
                        <span>Net Amount</span>
                        <strong>{{ number_format((float) $sale->grand_total, 2) }}</strong>
                    </div>
                    @if (!$isSalesmanView)
                        <div class="payment-summary__row">
                            <span>Net Profit</span>
                            <strong>{{ number_format((float) $adminProfitTotal, 2) }}</strong>
                        </div>
                    @endif
                    <div class="payment-summary__row payment-summary__row--total">
                        <span>Grand Total</span>
                        <strong>{{ number_format((float) $sale->grand_total, 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="account-ledger">
        <div class="account-ledger__header">
            <div>
                <h3 class="account-ledger__title">Sale Items</h3>
                <p class="account-toolbar__text">Line-by-line product, serial, and sale details.</p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover account-data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Weapon / Serial</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                        @if (!$isSalesmanView)
                            <th>Net Profit</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sale->items as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                {{ $item->product_name }}
                                <span class="account-data-table__subtext">{{ $item->product?->category?->name ?: 'No category' }}</span>
                            </td>
                            <td>{{ $item->serial_number ?: ($item->productUnit?->weapon_number ?: 'N/A') }}</td>
                            <td>{{ $item->quantity }} {{ $item->unit }}</td>
                            <td>{{ number_format((float) $item->price, 2) }}</td>
                            <td>{{ number_format((float) $item->total, 2) }}</td>
                            @if (!$isSalesmanView)
                                <td>{{ number_format(((float) $item->price - (float) ($item->historical_cost ?? 0)) * (int) $item->quantity, 2) }}</td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="period-totals">
                        <td colspan="5">Totals</td>
                        <td>{{ number_format((float) $sale->items->sum('total'), 2) }}</td>
                        @if (!$isSalesmanView)
                            <td>{{ number_format((float) $adminProfitTotal, 2) }}</td>
                        @endif
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
