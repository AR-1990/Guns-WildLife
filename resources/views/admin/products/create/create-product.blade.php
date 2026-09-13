@extends('admin.layouts.main')

@section('title', $title)
@section('pageHeading', $pageHeading)

@section('content')
    @php($isEdit = $product->exists)
    @php($serializedChecked = old('is_serialized', $product->is_serialized))

    <div class="products-dashboard">
        <div class="dashboard-content__head">
            <h2 class="boldHeading">{{ $pageHeading }}</h2>
        </div>

        <div class="dashboard-card add-product-form">
            <form action="{{ $isEdit ? route('admin.products.update', $product) : route('admin.products.store') }}"
                method="POST" enctype="multipart/form-data">
                @csrf
                @if ($isEdit)
                    @method('PUT')
                @endif

                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Product Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}"
                            placeholder="Product Name" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Category <span class="text-danger">*</span></label>
                        <select class="form-select js-select2" name="category_id" required>
                            <option value="">Select Category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ (string) old('category_id', $product->category_id) === (string) $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select js-select2" name="status" required>
                            <option value="active" {{ old('status', $product->status ?: 'active') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $product->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">{{ $isEdit ? 'Entry Date' : 'Product Entry Date' }} <span class="text-danger">*</span></label>
                        <input type="date" name="entry_date" class="form-control"
                            value="{{ old('entry_date', optional($product->created_at)->toDateString() ?: now()->toDateString()) }}"
                            required>
                        <small class="text-muted">
                            {{ $isEdit ? 'Original entry date change kar sakte hain, bas yeh restock ya sale history ke baad ki nahi honi chahiye.' : 'Back-date product add karna ho to yahan se date select karein.' }}
                        </small>
                    </div>

                    <input type="hidden" name="ownership_type" value="{{ old('ownership_type', $product->ownership_type ?: 'company') }}">

                    <div class="col-md-4">
                        <label class="form-label">Product Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        @if ($product->image_path)
                            <div class="mt-2">
                                <img src="{{ asset('storage/' . $product->image_path) }}" alt="Product Image" style="max-height: 70px;">
                            </div>
                        @endif
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Unit <span class="text-danger">*</span></label>
                        <select class="form-select js-select2" name="unit" required>
                            @foreach (['Piece', 'Box', 'Packet', 'Round', 'Pair', 'Set', 'Kg', 'Gram'] as $unit)
                                <option value="{{ $unit }}" {{ old('unit', $product->unit) === $unit ? 'selected' : '' }}>{{ $unit }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Barcode</label>
                        <input type="text" name="barcode" class="form-control"
                            value="{{ old('barcode', $product->barcode) }}" placeholder="Optional">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Purchase Amount <span class="text-danger">*</span></label>
                        <input type="number" name="purchase_price" id="purchaseAmountInput" class="form-control"
                            value="{{ old('purchase_price', $product->purchase_price ?? '0.00') }}" step="0.01" min="0" required {{ $isEdit ? 'readonly' : '' }}>
                        @if ($isEdit)
                            <small class="text-muted">Purchase amount ab restock screen se update hoga.</small>
                        @endif
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Selling Price <span class="text-danger">*</span></label>
                        <input type="number" name="selling_price" class="form-control"
                            value="{{ old('selling_price', $product->selling_price ?? '0.00') }}" step="0.01" min="0" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Stock Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="stock_quantity" id="stockQuantity" class="form-control"
                            value="{{ old('stock_quantity', $product->stock_quantity ?? count($productUnits)) }}" min="0" required {{ $isEdit ? 'readonly' : '' }}>
                        <small class="text-muted">
                            {{ $isEdit ? 'Stock ab Restock button se manage hoga.' : 'Opening stock yahan set karein. Baad mein Restock button use hoga.' }}
                        </small>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Min Stock Level</label>
                        <input type="number" name="min_stock_level" class="form-control"
                            value="{{ old('min_stock_level', $product->min_stock_level) }}" min="0">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Max Stock Level</label>
                        <input type="number" name="max_stock_level" class="form-control"
                            value="{{ old('max_stock_level', $product->max_stock_level) }}" min="0">
                    </div>

                    <div class="col-md-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_serialized" value="1"
                                id="isSerialized" {{ $serializedChecked ? 'checked' : '' }} {{ $isEdit ? 'disabled' : '' }}>
                            <label class="form-check-label" for="isSerialized">Serialized Gun</label>
                        </div>
                        @if ($isEdit)
                            <small class="text-muted d-block mt-1">Serialized type change nahi hoga. Restock alag screen se karein.</small>
                        @endif
                    </div>

                    <div class="col-12 {{ $serializedChecked ? '' : 'd-none' }}" id="weaponUnitsWrap">
                        <div class="border rounded p-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">Weapon Codes</h5>
                                <small class="text-muted">Enter one weapon code for each unit in quantity</small>
                            </div>
                            <div id="weaponUnitsContainer">
                                @foreach ($productUnits as $index => $unit)
                                    <div class="row g-3 mb-2 weapon-unit-row">
                                        <input type="hidden" name="weapon_units[{{ $index }}][id]" value="{{ $unit['id'] ?? '' }}">
                                        <div class="col-md-10">
                                            <label class="form-label">Weapon Number {{ $index + 1 }}</label>
                                            <input type="text" name="weapon_units[{{ $index }}][weapon_number]" class="form-control"
                                                value="{{ $unit['weapon_number'] ?? '' }}" placeholder="Weapon code" {{ $isEdit || ($unit['status'] ?? 'available') !== 'available' ? 'readonly' : '' }}>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Status</label>
                                            <input type="text" class="form-control" value="{{ ucfirst($unit['status'] ?? 'available') }}" readonly>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="4"
                            placeholder="Add stock or sale notes">{{ old('notes', $product->notes) }}</textarea>
                    </div>

                    <div class="col-12">
                        <div class="add-product-form__actions">
                            <button type="submit" class="themeBtn themeBtn--primary">
                                {{ $isEdit ? 'Update Product' : 'Save Product' }}
                            </button>
                            <a href="{{ route('admin.products.index') }}" class="themeBtn themeBtn--gray">
                                Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        const purchaseAmountInput = document.getElementById('purchaseAmountInput');
        const serializedCheckbox = document.getElementById('isSerialized');
        const stockQuantityInput = document.getElementById('stockQuantity');
        const weaponUnitsWrap = document.getElementById('weaponUnitsWrap');
        const weaponUnitsContainer = document.getElementById('weaponUnitsContainer');
        const existingUnits = @json($productUnits);

        if (!purchaseAmountInput || !serializedCheckbox || !stockQuantityInput || !weaponUnitsWrap || !weaponUnitsContainer) {
            return;
        }

        const renderWeaponRows = () => {
            const total = Math.max(parseInt(stockQuantityInput.value || '0', 10), 0);
            const isSerialized = serializedCheckbox.checked;

            weaponUnitsWrap.classList.toggle('d-none', !isSerialized);

            if (!isSerialized) {
                weaponUnitsContainer.innerHTML = '';
                return;
            }

            let html = '';

            for (let index = 0; index < total; index++) {
                const unit = existingUnits[index] || {};
                const readonly = unit.status && unit.status !== 'available' ? 'readonly' : '';

                html += `
                    <div class="row g-3 mb-2 weapon-unit-row">
                        <input type="hidden" name="weapon_units[${index}][id]" value="${unit.id ?? ''}">
                        <div class="col-md-10">
                            <label class="form-label">Weapon Number ${index + 1}</label>
                            <input type="text" name="weapon_units[${index}][weapon_number]" class="form-control" value="${unit.weapon_number ?? ''}" placeholder="Weapon code" ${readonly}>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <input type="text" class="form-control" value="${unit.status ? unit.status.charAt(0).toUpperCase() + unit.status.slice(1) : 'Available'}" readonly>
                        </div>
                    </div>
                `;
            }

            weaponUnitsContainer.innerHTML = html;
        };

        serializedCheckbox.addEventListener('change', renderWeaponRows);
        stockQuantityInput.addEventListener('input', renderWeaponRows);
        renderWeaponRows();
    })();
</script>
@endpush
