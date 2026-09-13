@extends('admin.layouts.main')

@section('title', 'Expenses')
@section('pageHeading', 'Expenses')

@section('content')
<div class="accounts-dashboard">
    <div class="dashboard-content__head">
        <h2 class="boldHeading">Expense Management</h2>

        <nav aria-label="breadcrumb">
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item">Accounts</li>
                <li class="breadcrumb-item active" aria-current="page">Expenses</li>
            </ul>
        </nav>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-6 col-md-6">
            <div class="account-card account-card--yellow">
                <div class="account-card__content">
                    <h3 class="account-card__title">Total Expense</h3>
                    <p class="metric-card__value">{{ number_format((float) $summary['total'], 2) }}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-6">
            <div class="account-card account-card--orange">
                <div class="account-card__content">
                    <h3 class="account-card__title">Records</h3>
                    <p class="metric-card__value">{{ $summary['records'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="account-toolbar">
        <div>
            <h3 class="account-toolbar__title">Expense Records</h3>
            <p class="account-toolbar__text">
                {{ auth()->user()->isAdmin() ? 'Search and filter all expense records by date, category, reference and staff.' : 'Yahan salesman sirf apni expense entries dekh aur manage kar sakta hai.' }}
            </p>
        </div>
        <div class="account-toolbar__group">
            <a href="{{ route('admin.accounts.expenses.csv', request()->only('from_date', 'to_date', 'title', 'reference', 'category', 'payment_method', 'created_by')) }}" class="themeBtn themeBtn--white">
                Export CSV
            </a>
            @if ($canManageCategories)
                <a href="{{ route('admin.accounts.expense-categories.index') }}" class="themeBtn themeBtn--white">Manage Categories</a>
            @endif
            <a href="{{ route('admin.accounts.expenses.create') }}" class="themeBtn">Add Expense</a>
        </div>
    </div>

    <form id="expenseFilterBox" class="account-ledger mb-3">
        <div class="account-ledger__header">
            <h3 class="account-ledger__title">
                <i class="fa fa-filter me-2 opacity-75"></i>Filters
            </h3>
            <div class="account-ledger__controls">
                <span class="badge blue" title="{{ $summary['records'] }}">{{ $summary['records'] }} records match</span>
            </div>
        </div>
        <div class="p-3 p-md-4">
            <div class="row g-3 align-items-end">
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <label class="form-label fw-semibold mb-1">
                        <i class="fa fa-calendar-check-o text-primary me-1 opacity-75"></i>From Date
                    </label>
                    <input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="form-control form-control-sm" id="from_date" placeholder="From">
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <label class="form-label fw-semibold mb-1">
                        <i class="fa fa-calendar text-primary me-1 opacity-75"></i>To Date
                    </label>
                    <input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="form-control form-control-sm" id="to_date" placeholder="To">
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <label class="form-label fw-semibold mb-1">
                        <i class="fa fa-tag text-primary me-1 opacity-75"></i>Title
                    </label>
                    <input type="text" name="title" value="{{ $filters['title'] ?? '' }}" class="form-control form-control-sm" placeholder="Search title" maxlength="250">
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <label class="form-label fw-semibold mb-1">
                        <i class="fa fa-list-alt text-primary me-1 opacity-75"></i>Category
                    </label>
                    <select name="category" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        @foreach (($categories ?? []) as $cat)
                            <option value="{{ $cat }}" {{ (string) ($filters['category'] ?? '') === (string) $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <label class="form-label fw-semibold mb-1">
                        <i class="fa fa-credit-card text-primary me-1 opacity-75"></i>Payment Method
                    </label>
                    <select name="payment_method" class="form-select form-select-sm">
                        <option value="">All Methods</option>
                        @foreach (['cash' => 'Cash', 'bank' => 'Bank', 'card' => 'Card', 'online' => 'Online'] as $key => $label)
                            <option value="{{ $key }}" {{ (string) ($filters['payment_method'] ?? '') === (string) $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <label class="form-label fw-semibold mb-1">
                        <i class="fa fa-hashtag text-primary me-1 opacity-75"></i>Reference
                    </label>
                    <input type="text" name="reference" value="{{ $filters['reference'] ?? '' }}" class="form-control form-control-sm" placeholder="Bill / TXN / reference" maxlength="250">
                </div>
                @if ($canFilterByCreator)
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <label class="form-label fw-semibold mb-1">
                            <i class="fa fa-user-plus text-primary me-1 opacity-75"></i>Added By
                        </label>
                        <select name="created_by" class="form-select form-select-sm">
                            <option value="">All Users</option>
                            @foreach (($staffList ?? []) as $usr)
                                <option value="{{ $usr->id }}" {{ (string) ($filters['created_by'] ?? '') === (string) $usr->id ? 'selected' : '' }}>
                                    {{ $usr->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-lg-4 col-md-12 col-sm-12 d-flex gap-2">
                    <button type="submit" class="themeBtn themeBtn--sm w-50">
                        <i class="fa fa-search me-1"></i>Apply Filters
                    </button>
                    <a href="{{ route('admin.accounts.expenses') }}" class="themeBtn themeBtn--white themeBtn--sm w-50 text-center">
                        <i class="fa fa-times me-1"></i>Clear
                    </a>
                </div>
            </div>
        </div>
    </form>

    <div class="account-ledger">
        <div class="account-ledger__header">
            <h3 class="account-ledger__title">Expense Ledger</h3>
            <div class="account-ledger__controls">
                <span class="badge blue">{{ $expenses->total() }} Records</span>
            </div>
        </div>

        <div class="table-responsive">
            <table id="expenseTable" data-laravel-pagination="true" class="table table-hover tables account-data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Method</th>
                        <th>Amount</th>
                        <th>Reference</th>
                        <th>Added By</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($expenses as $expense)
                        <tr>
                            <td>{{ $expenses->firstItem() + $loop->index }}</td>
                            <td>{{ $expense->expense_date?->format('d-M-Y') }}</td>
                            <td>
                                {{ $expense->title }}
                                <span class="account-data-table__subtext">{!! App\Support\NotesFormatter::toHtmlBr($expense->notes, 'No extra notes') !!}</span>
                            </td>
                            <td>{{ $expense->category ?: 'N/A' }}</td>
                            <td>{{ \Illuminate\Support\Str::title($expense->payment_method) }}</td>
                            <td class="credit-amount">{{ number_format((float) $expense->amount, 2) }}</td>
                            <td>{{ $expense->reference ?: 'N/A' }}</td>
                            <td>{{ $expense->creator?->name ?: 'Admin' }}</td>
                            <td>
                                <div class="account-action-links">
                                    <a href="{{ route('admin.accounts.expenses.edit', $expense) }}" class="btn btn-sm btn-outline-primary account-icon-btn" title="Edit Expense" aria-label="Edit Expense">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <form action="{{ route('admin.accounts.expenses.destroy', $expense) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this expense?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger account-icon-btn" title="Delete Expense" aria-label="Delete Expense">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="account-empty-state">No expenses found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $expenses->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
