@extends('admin.layouts.main')

@section('title', 'Expense Categories')
@section('pageHeading', 'Expense Categories')

@section('content')
<div class="accounts-dashboard">
    <div class="dashboard-content__head">
        <h2 class="boldHeading">Expense Categories</h2>

        <nav aria-label="breadcrumb">
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item">Accounts</li>
                <li class="breadcrumb-item"><a href="{{ route('admin.accounts.expenses') }}">Expenses</a></li>
                <li class="breadcrumb-item active" aria-current="page">Categories</li>
            </ul>
        </nav>
    </div>

    <div class="account-toolbar">
        <div>
            <h3 class="account-toolbar__title">Manage Expense Categories</h3>
            <p class="account-toolbar__text">Yahan se admin expense categories add, update aur activate/deactivate kar sakta hai.</p>
        </div>
        <div class="account-toolbar__group">
            <a href="{{ route('admin.accounts.expenses') }}" class="themeBtn themeBtn--white">Back to Expenses</a>
            <a href="{{ route('admin.accounts.expenses.create') }}" class="themeBtn">Add Expense</a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-5">
            <div class="account-ledger h-100">
                <div class="account-ledger__header">
                    <h3 class="account-ledger__title">{{ $editingCategory ? 'Edit Expense Category' : 'Add Expense Category' }}</h3>
                </div>
                <div class="p-3 p-md-4">
                    <form action="{{ $editingCategory ? route('admin.accounts.expense-categories.update', $editingCategory) : route('admin.accounts.expense-categories.store') }}" method="POST">
                        @csrf
                        @if ($editingCategory)
                            @method('PUT')
                        @endif

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Category Name</label>
                            <input
                                type="text"
                                name="name"
                                class="form-control"
                                value="{{ old('name', $editingCategory?->name) }}"
                                placeholder="e.g. Internet / Tea / Packing"
                                required
                            >
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="themeBtn w-100">
                                <i class="fa fa-save me-1"></i>{{ $editingCategory ? 'Update Category' : 'Save Category' }}
                            </button>
                            @if ($editingCategory)
                                <a href="{{ route('admin.accounts.expense-categories.index') }}" class="themeBtn themeBtn--white w-100 text-center">
                                    <i class="fa fa-times me-1"></i>Cancel
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="account-ledger h-100">
                <div class="account-ledger__header">
                    <h3 class="account-ledger__title">Category List</h3>
                    <div class="account-ledger__controls">
                        <span class="badge blue">{{ count($expenseCategories ?? []) }} Categories</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover account-data-table mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Status</th>
                                <th>Used In</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($expenseCategories as $expenseCategory)
                                <tr>
                                    <td>{{ $expenseCategory->name }}</td>
                                    <td>
                                        <span class="badge {{ $expenseCategory->is_active ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $expenseCategory->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>{{ $expenseCategory->expenses_count }} expenses</td>
                                    <td>
                                        <div class="account-action-links">
                                            <a
                                                href="{{ route('admin.accounts.expense-categories.index', ['expense_category' => $expenseCategory->id]) }}"
                                                class="btn btn-sm btn-outline-primary account-icon-btn"
                                                title="Edit Category"
                                                aria-label="Edit Category"
                                            >
                                                <i class="fas fa-pen"></i>
                                            </a>
                                            <form action="{{ route('admin.accounts.expense-categories.toggle', $expenseCategory) }}" method="POST" class="d-inline-block">
                                                @csrf
                                                @method('PATCH')
                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-outline-secondary account-icon-btn"
                                                    title="{{ $expenseCategory->is_active ? 'Deactivate Category' : 'Activate Category' }}"
                                                    aria-label="{{ $expenseCategory->is_active ? 'Deactivate Category' : 'Activate Category' }}"
                                                >
                                                    <i class="fas {{ $expenseCategory->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="account-empty-state">No expense categories found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
