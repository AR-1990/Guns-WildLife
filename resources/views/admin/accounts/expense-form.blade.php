@extends('admin.layouts.main')

@section('title', filled($editingExpense) ? 'Edit Expense' : 'Add Expense')
@section('pageHeading', filled($editingExpense) ? 'Edit Expense' : 'Add Expense')

@section('content')
<div class="accounts-dashboard">
    <div class="dashboard-content__head">
        <h2 class="boldHeading">{{ filled($editingExpense) ? 'Edit Expense' : 'Add Expense' }}</h2>

        <nav aria-label="breadcrumb">
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item">Accounts</li>
                <li class="breadcrumb-item"><a href="{{ route('admin.accounts.expenses') }}">Expenses</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ filled($editingExpense) ? 'Edit' : 'Add' }}</li>
            </ul>
        </nav>
    </div>

    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10">
            <div class="dashboard-card">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <h4 class="mb-0"><i class="fa fa-file-text-o text-primary me-1"></i>{{ filled($editingExpense) ? 'Update Expense Details' : 'Create Expense Record' }}</h4>
                    <div class="d-flex gap-2 flex-wrap">
                        @if ($isAdmin)
                            <a href="{{ route('admin.accounts.expense-categories.index') }}" class="themeBtn themeBtn--white">
                                <i class="fa fa-tags me-1"></i>Manage Categories
                            </a>
                        @endif
                        <a href="{{ route('admin.accounts.expenses') }}" class="themeBtn themeBtn--white">
                            <i class="fa fa-arrow-left me-1"></i>Back to List
                        </a>
                    </div>
                </div>

                @php
                    $notesData = json_decode($editingExpense?->notes ?? '', true);
                @endphp

                <div class="alert alert-info border mb-4 py-2" role="alert">
                    <i class="fa fa-info-circle me-2"></i>
                    <strong>Note:</strong> Ye record <strong>company expense</strong> me save hoga aur `Added By` me current user show hoga.
                </div>

                <form action="{{ filled($editingExpense) ? route('admin.accounts.expenses.update', $editingExpense) : route('admin.accounts.expenses.store') }}" method="POST" id="expenseForm">
                    @csrf
                    @if (filled($editingExpense))
                        @method('PUT')
                    @endif

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="fa fa-calendar text-primary me-1"></i>Expense Date
                            </label>
                            <input type="date" class="form-control" name="expense_date" value="{{ old('expense_date', $editingExpense?->expense_date?->format('Y-m-d') ?: now()->toDateString()) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="fa fa-bookmark text-primary me-1"></i>Title
                            </label>
                            <input type="text" class="form-control" name="title" value="{{ old('title', $editingExpense?->title) }}" placeholder="e.g. Office Rent / Fuel / Maintenance" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="fa fa-tags text-primary me-1"></i>Category
                            </label>
                            <select class="form-select js-select2" name="category">
                                <option value="">Select Category</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category }}" {{ old('category', $editingExpense?->category) === $category ? 'selected' : '' }}>
                                        {{ $category }}
                                    </option>
                                @endforeach
                            </select>
                            @if (empty($categories))
                                <small class="text-muted d-block mt-1">Admin pehle expense category add karega phir yahan show hogi.</small>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="fa fa-credit-card text-primary me-1"></i>Payment Method
                            </label>
                            <select class="form-select js-select2" name="payment_method" required>
                                @foreach (['cash' => 'Cash', 'bank' => 'Bank', 'card' => 'Card', 'online' => 'Online'] as $value => $label)
                                    <option value="{{ $value }}" {{ old('payment_method', $editingExpense?->payment_method ?: 'cash') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="fa fa-money text-dark me-1"></i>Amount
                            </label>
                            <input
                                type="number"
                                class="form-control form-control-lg border-primary fw-semibold"
                                name="amount"
                                step="0.01"
                                min="0"
                                value="{{ old('amount', $editingExpense?->amount ?? 0) }}"
                                placeholder="Enter amount"
                                required
                            >
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="fa fa-list-alt text-primary me-1"></i>Reference
                            </label>
                            <input type="text" class="form-control" name="reference" value="{{ old('reference', $editingExpense?->reference) }}" placeholder="Bill no / transaction ref">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                <i class="fa fa-file-text-o text-primary me-1"></i>Notes
                            </label>
                            <textarea class="form-control" name="notes_user" rows="2" placeholder="Optional notes">{{ old('notes_user', is_array($notesData) ? ($notesData['user_notes'] ?? '') : '') }}</textarea>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="themeBtn w-100">
                                <i class="fa fa-save me-1"></i>{{ filled($editingExpense) ? 'Update Expense' : 'Save Expense' }}
                            </button>
                            <a href="{{ route('admin.accounts.expenses') }}" class="themeBtn themeBtn--white w-100 text-center">
                                <i class="fa fa-times me-1"></i>Cancel
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
