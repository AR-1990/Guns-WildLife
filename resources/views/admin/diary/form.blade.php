@extends('admin.layouts.main')

@section('title', filled($editingContact) ? 'Edit Contact' : 'Add Contact')
@section('pageHeading', filled($editingContact) ? 'Edit Contact' : 'Add Contact')

@section('content')
@php($isEditing = filled($editingContact))
<div class="accounts-dashboard">
    <div class="dashboard-content__head">
        <h2 class="boldHeading">{{ $isEditing ? 'Edit Diary Contact' : 'Add Diary Contact' }}</h2>

        <nav aria-label="breadcrumb">
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route(auth()->user()->dashboardRouteName()) }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.diary.index') }}">My Diary</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $isEditing ? 'Edit' : 'Add' }}</li>
            </ul>
        </nav>
    </div>

    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10">
            <div class="dashboard-card">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <h4 class="mb-0">{{ $isEditing ? 'Update Contact Details' : 'Create New Contact' }}</h4>
                    <a href="{{ route('admin.diary.index') }}" class="themeBtn themeBtn--white">Back to List</a>
                </div>

                <form action="{{ $isEditing ? route('admin.diary.update', $editingContact) : route('admin.diary.store') }}" method="POST">
                    @csrf
                    @if ($isEditing)
                        @method('PUT')
                    @endif

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="full_name" value="{{ old('full_name', $editingContact?->full_name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone Number</label>
                            <input type="text" class="form-control" name="phone" value="{{ old('phone', $editingContact?->phone) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="{{ old('email', $editingContact?->email) }}" placeholder="Optional">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Address</label>
                            <input type="text" class="form-control" name="address" value="{{ old('address', $editingContact?->address) }}" placeholder="Optional">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Note</label>
                            <textarea class="form-control" name="notes" rows="4" placeholder="Optional">{{ old('notes', $editingContact?->notes) }}</textarea>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="themeBtn w-100">{{ $isEditing ? 'Update Contact' : 'Save Contact' }}</button>
                            <a href="{{ route('admin.diary.index') }}" class="themeBtn themeBtn--white w-100 text-center">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
