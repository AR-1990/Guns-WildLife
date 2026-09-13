@extends('admin.layouts.main')

@section('title', 'Restock Product')
@section('pageHeading', 'Restock Product')

@section('content')
<div class="products-dashboard">
    <div class="dashboard-content__head">
        <h2 class="boldHeading">Restock {{ $product->name }}</h2>

        <nav aria-label="breadcrumb">
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route(auth()->user()->dashboardRouteName()) }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Products</a></li>
                <li class="breadcrumb-item active" aria-current="page">Restock</li>
            </ul>
        </nav>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="dashboard-card add-product-form">
                <form action="{{ route('admin.products.restock.store', $product) }}" method="POST">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Product Code</label>
                            <input type="text" class="form-control" value="{{ $product->product_code }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Current Stock</label>
                            <input type="text" class="form-control" value="{{ $product->is_serialized ? $product->availableUnits->count() : $product->stock_quantity }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Restock Date <span class="text-danger">*</span></label>
                            <input type="date" name="restocked_at" class="form-control" value="{{ old('restocked_at', now()->toDateString()) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Purchase Amount <span class="text-danger">*</span></label>
                            <input type="number" name="purchase_price" class="form-control" value="{{ old('purchase_price', $product->purchase_price) }}" min="0" step="0.01" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" id="restockQuantity" class="form-control" value="{{ old('quantity', 1) }}" min="1" required>
                            <small class="text-muted">{{ $product->is_serialized ? 'Serialized item mein quantity weapon codes ke count ke برابر hogi.' : 'Yahan jitna stock aaya woh quantity likhein.' }}</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Selling Price</label>
                            <input type="text" class="form-control" value="{{ number_format((float) $product->selling_price, 2) }}" readonly>
                        </div>

                        @if ($product->is_serialized)
                            <div class="col-12">
                                <label class="form-label">Weapon Codes <span class="text-danger">*</span></label>
                                <textarea name="weapon_numbers" id="weaponNumbersTextarea" class="form-control" rows="7" placeholder="Har line par aik weapon code likhein">{{ old('weapon_numbers') }}</textarea>
                                <small class="text-muted">Example: GLK17-004, phir next line GLK17-005. Quantity auto match honi chahiye.</small>
                            </div>
                        @endif

                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="4" placeholder="Optional note">{{ old('notes') }}</textarea>
                        </div>

                        <div class="col-12">
                            <div class="add-product-form__actions">
                                <button type="submit" class="themeBtn themeBtn--primary">Save Restock</button>
                                <a href="{{ route('admin.products.index') }}" class="themeBtn themeBtn--gray">Back</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const quantityInput = document.getElementById('restockQuantity');
        const weaponNumbersTextarea = document.getElementById('weaponNumbersTextarea');

        if (!quantityInput || !weaponNumbersTextarea) {
            return;
        }

        const syncQuantity = () => {
            const lines = weaponNumbersTextarea.value
                .split(/\r\n|\r|\n/)
                .map((line) => line.trim())
                .filter(Boolean);

            quantityInput.value = lines.length || 1;
        };

        weaponNumbersTextarea.addEventListener('input', syncQuantity);
        syncQuantity();
    })();
</script>
@endpush
