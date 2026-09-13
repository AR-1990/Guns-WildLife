@extends('admin.layouts.main')

@section('title', filled($editingUser) ? 'Edit User' : 'Add User')
@section('pageHeading', filled($editingUser) ? 'Edit User' : 'Add User')

@section('content')
@php($isEditing = filled($editingUser))
<div class="accounts-dashboard">
    <div class="dashboard-content__head">
        <h2 class="boldHeading">{{ $isEditing ? 'Edit User' : 'Add User' }}</h2>

        <nav aria-label="breadcrumb">
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item">Accounts</li>
                <li class="breadcrumb-item"><a href="{{ route('admin.accounts.users') }}">Users</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $isEditing ? 'Edit' : 'Add' }}</li>
            </ul>
        </nav>
    </div>

    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10">
            <div class="dashboard-card">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <h4 class="mb-0">{{ $isEditing ? 'Update User Details' : 'Create New User' }}</h4>
                    <a href="{{ route('admin.accounts.users') }}" class="themeBtn themeBtn--white">Back to List</a>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger mb-3">{{ $errors->first() }}</div>
                @endif

                <form action="{{ $isEditing ? route('admin.accounts.users.update', $editingUser) : route('admin.accounts.users.store') }}" method="POST">
                    @csrf
                    @if ($isEditing)
                        @method('PUT')
                    @endif

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Role</label>
                            <select class="form-select js-select2" name="role" required>
                                <option value="salesman" {{ old('role', $editingUser?->role ?: 'salesman') === 'salesman' ? 'selected' : '' }}>Salesman</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" value="{{ old('name', $editingUser?->name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="{{ old('email', $editingUser?->email) }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Password{{ $isEditing ? ' (Leave blank to keep current)' : '' }}</label>
                            <input type="password" class="form-control" name="password" {{ $isEditing ? '' : 'required' }}>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="themeBtn w-100">{{ $isEditing ? 'Update User' : 'Save User' }}</button>
                            <a href="{{ route('admin.accounts.users') }}" class="themeBtn themeBtn--white w-100 text-center">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
