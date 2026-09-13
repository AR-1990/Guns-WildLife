@extends('admin.layouts.main')

@section('title', 'Loan & Advance')
@section('pageHeading', 'Loan & Advance')

@section('content')
<div class="accounts-dashboard loan-advance-page">
    <div class="dashboard-content__head">
        <h2 class="boldHeading">Loan & Advance</h2>

        <nav aria-label="breadcrumb">
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item">Accounts</li>
                <li class="breadcrumb-item active" aria-current="page">Loan & Advance</li>
            </ul>
        </nav>
    </div>

    <div class="account-toolbar">
        <div>
            <h3 class="account-toolbar__title">Loan & Advance Control</h3>
            <p class="account-toolbar__text">Track issued advances, issued loans, and salary-based loan adjustments from one clean ledger.</p>
        </div>
        <div class="account-toolbar__group">
            <a href="{{ route('admin.reports.revenue') }}" class="themeBtn">Open Reports</a>
        </div>
    </div>

    <div class="dashboard-card mb-4 loan-advance-filter-card">
        <form action="{{ route('admin.accounts.loan-advance') }}" method="GET">
            <div class="row g-3 align-items-end">
                <div class="col-lg-4 col-md-6">
                    <label class="form-label">Employee</label>
                    <select class="form-select js-select2" name="employee_id">
                        <option value="">All Employees</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}" {{ (string) $filters['employee_id'] === (string) $employee->id ? 'selected' : '' }}>
                                {{ $employee->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 col-md-6">
                    <label class="form-label">From Month</label>
                    <input type="month" class="form-control" name="from_month" value="{{ $filters['from_month'] }}">
                </div>
                <div class="col-lg-3 col-md-6">
                    <label class="form-label">To Month</label>
                    <input type="month" class="form-control" name="to_month" value="{{ $filters['to_month'] }}">
                </div>
                <div class="col-lg-2 col-md-6">
                    <div class="loan-advance-filter-actions">
                        <button type="submit" class="themeBtn">Search</button>
                        <a href="{{ route('admin.accounts.loan-advance') }}" class="themeBtn themeBtn--white text-center">Reset</a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="account-card account-card--yellow">
                <div class="account-card__content">
                    <h3 class="account-card__title">Records</h3>
                    <p class="metric-card__value">{{ $summary['records'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="account-card account-card--teal">
                <div class="account-card__content">
                    <h3 class="account-card__title">Advance Total</h3>
                    <p class="metric-card__value">{{ number_format((float) $summary['advance'], 2) }}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="account-card account-card--primary">
                <div class="account-card__content">
                    <h3 class="account-card__title">Advance Adjusted</h3>
                    <p class="metric-card__value">{{ number_format((float) $summary['advance_adjusted'], 2) }}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="account-card account-card--orange">
                <div class="account-card__content">
                    <h3 class="account-card__title">Loan Issued</h3>
                    <p class="metric-card__value">{{ number_format((float) $summary['loan'], 2) }}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="account-card account-card--yellow">
                <div class="account-card__content">
                    <h3 class="account-card__title">Loan Adjusted</h3>
                    <p class="metric-card__value">{{ number_format((float) $summary['loan_adjusted'], 2) }}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="account-card account-card--yellow">
                <div class="account-card__content">
                    <h3 class="account-card__title">Loan Waived</h3>
                    <p class="metric-card__value">{{ number_format((float) $summary['loan_waived'], 2) }}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="account-card account-card--red">
                <div class="account-card__content">
                    <h3 class="account-card__title">Outstanding Loan</h3>
                    <p class="metric-card__value">{{ number_format((float) $summary['outstanding'], 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="account-ledger">
        <div class="account-ledger__header">
            <div>
                <h3 class="account-ledger__title">Loan & Advance Ledger</h3>
                <p class="account-toolbar__text">Salary deductions against loan balances are also reflected below.</p>
            </div>
            <div class="account-ledger__controls">
                <span class="badge blue">{{ $records->total() }} Records</span>
            </div>
        </div>

        <div class="table-responsive">
            <table id="loanAdvanceTable" class="table table-hover tables account-data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Month</th>
                        <th>Employee</th>
                        <th>Advance</th>
                        <th>Advance Adjusted</th>
                        <th>Loan Issued</th>
                        <th>Loan Adjusted</th>
                        <th>Loan Waived</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($records as $record)
                        @php
                            $statusClass = match ($record->status) {
                                'loan' => 'blue',
                                'advance' => 'active',
                                default => 'inActive',
                            };
                        @endphp
                        <tr>
                            <td>{{ $records->firstItem() + $loop->index }}</td>
                            <td>{{ $record->salary_month }}</td>
                            <td>
                                {{ $record->employee?->name }}
                                <span class="account-data-table__subtext">
                                    {{ $record->employee?->roleLabel() ?: 'Employee' }}
                                </span>
                            </td>
                            <td>{{ number_format((float) $record->advance_salary, 2) }}</td>
                            <td class="debit-amount">{{ number_format((float) $record->advance_adjustment_amount, 2) }}</td>
                            <td>{{ number_format((float) $record->loan_amount, 2) }}</td>
                            <td class="debit-amount">{{ number_format((float) $record->loan_adjustment_amount, 2) }}</td>
                            <td class="debit-amount">{{ number_format((float) $record->loan_waived_amount, 2) }}</td>
                            <td class="debit-amount">{{ number_format((float) $record->paid_amount, 2) }}</td>
                            <td class="credit-amount">{{ number_format((float) $record->balance_amount, 2) }}</td>
                            <td>
                                <span class="badge {{ $statusClass }}">{{ \Illuminate\Support\Str::title(str_replace('_', ' ', $record->status)) }}</span>
                                <span class="account-data-table__subtext">
                                    {{ (float) ($record->loan_adjustment_amount + $record->advance_adjustment_amount) > 0
                                        ? 'Adjusted in salary'
                                        : ((float) $record->loan_waived_amount > 0 || (float) $record->advance_waived_amount > 0 ? 'Waived by admin' : 'No salary adjustment') }}
                                </span>
                            </td>
                            <td>
                                <div class="account-action-links">
                                    <span class="text-muted small">No direct action</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="account-empty-state">No loan or advance record found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $records->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
