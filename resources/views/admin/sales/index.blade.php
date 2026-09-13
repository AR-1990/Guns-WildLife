@extends('admin.layouts.main')

@section('title', 'Sales')
@section('pageHeading', 'Sales')

@section('content')
@php($isEditingDraft = filled($selectedDraft))
@php($initialItems = old('items', $selectedDraftItems))
@php($initialItems = filled($initialItems) ? $initialItems : [['product_id' => '', 'product_unit_id' => '', 'serial_number' => '', 'quantity' => 1, 'price' => 0]])
<div class="sales-dashboard">
    <div class="dashboard-content__head">
        <h2 class="boldHeading">Sales Module</h2>

        <nav aria-label="breadcrumb">
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route(auth()->user()->dashboardRouteName()) }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Sales</li>
            </ul>
        </nav>
    </div>
    
    <div class="dashboard-card">
        <div class="row g-3 mb-4">
            <div class="col-lg-8">
                <div class="sales-form">
                    <form action="{{ $isEditingDraft ? route('admin.sales.update', $selectedDraft) : route('admin.sales.store') }}" method="POST">
                        @csrf
                        @if ($isEditingDraft)
                            @method('PUT')
                        @endif

                        @if ($isEditingDraft)
                            <div class="alert alert-warning">
                                Draft loaded: <strong>{{ $selectedDraft->receipt_no }}</strong>
                                <a href="{{ route('admin.sales.index') }}" class="ms-2">Create New Sale</a>
                            </div>
                        @endif

                        <div class="row g-3 align-items-end mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Document Type</label>
                                <select class="form-control js-select2" name="document_type">
                                    <option value="sales_invoice" {{ old('document_type', $selectedDraft?->document_type ?: 'sales_invoice') === 'sales_invoice' ? 'selected' : '' }}>Sales Invoice</option>
                                    <option value="sales_order" {{ old('document_type', $selectedDraft?->document_type) === 'sales_order' ? 'selected' : '' }}>Sales Order</option>
                                    <option value="quotation" {{ old('document_type', $selectedDraft?->document_type) === 'quotation' ? 'selected' : '' }}>Quotation</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Customer Name</label>
                                <input type="text" class="form-control" name="customer_name" value="{{ old('customer_name', $selectedDraft?->customer_name ?: 'Walk-in Customer') }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Phone Number</label>
                                <input type="text" class="form-control" name="customer_phone" value="{{ old('customer_phone', $selectedDraft?->customer_phone) }}" placeholder="03xx xxxxxxx">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Date</label>
                                <input type="date" class="form-control" name="sale_date" value="{{ old('sale_date', $selectedDraft?->sale_date?->format('Y-m-d') ?: now()->toDateString()) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Reference / PO No.</label>
                                <input type="text" class="form-control" name="reference" value="{{ old('reference', $selectedDraft?->reference) }}" placeholder="Optional">
                            </div>
                        </div>

                        <div class="border rounded p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">Sale Items</h5>
                                <button type="button" class="btn btn-outline-secondary filterBtn" id="addSaleItemRow">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            @if (empty($productsJson))
                                <div class="alert alert-warning mb-3">
                                    No in-stock product available right now. Restock first, phir sale create karein.
                                </div>
                            @endif
                            <div id="saleItemsContainer"></div>
                        </div>

                        <div class="row g-3 align-items-end">
                            <div class="col-lg-2 col-md-4">
                                <label class="form-label">Discount %</label>
                                <input type="number" class="form-control" id="discountPercent" name="discount_percent" value="{{ old('discount_percent', $selectedDraft?->discount_percent ?? 0) }}" min="0" max="100" step="0.01">
                            </div>

                            <div class="col-lg-2 col-md-4">
                                <label class="form-label">Items Subtotal</label>
                                <input type="text" class="form-control" id="itemsSubtotalPreview" value="0.00" readonly>
                            </div>

                            <div class="col-lg-2 col-md-4">
                                <label class="form-label">Grand Total</label>
                                <input type="text" class="form-control" id="grandTotalPreview" value="0.00" readonly>
                            </div>

                            @unless ($isSalesmanView)
                                <div class="col-lg-2 col-md-4">
                                    <label class="form-label">Actual Cost</label>
                                    <input type="text" class="form-control" id="actualCostPreview" value="0.00" readonly>
                                </div>

                                <div class="col-lg-2 col-md-4">
                                    <label class="form-label">Profit</label>
                                    <input type="text" class="form-control" id="profitPreview" value="0.00" readonly>
                                </div>
                            @endunless

                            <div class="col-lg-{{ $isSalesmanView ? '3' : '2' }} col-md-6">
                                <button type="submit" name="action_type" value="completed" class="themeBtn w-100">
                                    {{ $isEditingDraft ? 'Complete Draft' : 'Complete Sale' }}
                                </button>
                            </div>

                            <div class="col-lg-{{ $isSalesmanView ? '3' : '2' }} col-md-6">
                                <button type="submit" name="action_type" value="draft" class="themeBtn themeBtn--white w-100">
                                    {{ $isEditingDraft ? 'Update Draft' : 'Save Draft' }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="payment-summary">
                    <h3 class="payment-summary__title">Sales Summary</h3>
                    <div class="payment-summary__content">
                        <div class="payment-summary__row">
                            <span>Total Subtotal:</span>
                            <strong>{{ number_format($summary['subtotal'], 2) }}</strong>
                        </div>
                        <div class="payment-summary__row">
                            <span>Draft Sales:</span>
                            <span class="badge bg-gray">{{ $summary['drafts'] }}</span>
                        </div>
                        <div class="payment-summary__row">
                            <span>Completed Sales:</span>
                            <strong>{{ $summary['completed'] }}</strong>
                        </div>
                        <div class="payment-summary__row payment-summary__row--total">
                            <span>Grand Total:</span>
                            <strong>{{ number_format($summary['grand_total'], 2) }}</strong>
                        </div>
                    </div>
                    <div class="payment-summary__actions">
                        <a href="{{ route('admin.sales.export.csv', request()->only('status')) }}" class="themeBtn themeBtn--green w-100 text-center">
                            <i class="fas fa-file-csv me-1"></i> Export CSV
                        </a>
                    </div>
                    <hr>
                    <div class="payment-summary__content">
                        <div class="payment-summary__row">
                            <span>Open Drafts</span>
                            <strong>{{ $draftSales->count() }}</strong>
                        </div>
                        @forelse ($draftSales->take(5) as $draft)
                            <div class="payment-summary__row">
                                <a href="{{ route('admin.sales.index', ['draft' => $draft->id]) }}">
                                    {{ $draft->receipt_no }}
                                </a>
                                <span>{{ $draft->customer_name }}</span>
                            </div>
                        @empty
                            <div class="payment-summary__row">
                                <span>No draft saved yet.</span>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="sales-table-section">
            <div class="sales-table__header">
                <div class="sales-table__header-left">
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle filterBtn" type="button"
                            id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-filter me-2"></i> Filter
                        </button>
                        <ul class="dropdown-menu sales-filter-menu" aria-labelledby="filterDropdown">
                            <li><h6 class="dropdown-header">Filter by Status</h6></li>
                            <li><a class="dropdown-item {{ !request('status') ? 'active' : '' }}" href="{{ route('admin.sales.index') }}">All</a></li>
                            <li><a class="dropdown-item {{ request('status') === 'completed' ? 'active' : '' }}" href="{{ route('admin.sales.index', ['status' => 'completed']) }}">Completed</a></li>
                            <li><a class="dropdown-item {{ request('status') === 'draft' ? 'active' : '' }}" href="{{ route('admin.sales.index', ['status' => 'draft']) }}">Draft</a></li>
                        </ul>
                    </div>
                    <a href="{{ route('admin.sales.export.csv', request()->only('status')) }}" class="themeBtn themeBtn--green ms-2">
                        <i class="fas fa-file-csv me-1"></i> Export CSV
                    </a>
                </div>
                <span class="sales-table__header-right">Total Sales: {{ $sales->count() }}</span>
            </div>
            <div class="table-responsive">
                <table id="salesTable" class="table table-hover tables">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Receipt</th>
                            <th>Customer</th>
                            <th>Product Details</th>
                            <th>Qty</th>
                            <th>Line Total</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sales as $sale)
                            @foreach ($sale->items as $item)
                                <tr>
                                    <td>{{ $loop->parent->iteration }}</td>
                                    <td>{{ $sale->receipt_no }}</td>
                                    <td>{{ $sale->customer_name }}</td>
                                    <td>
                                        {{ $item->product_name }}
                                        @if ($item->serial_number)
                                            <br><small>Serial: {{ $item->serial_number }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $item->quantity }} {{ $item->unit }}</td>
                                    <td>{{ number_format((float) $item->total, 2) }}</td>
                                    <td>{{ $sale->sale_date?->format('d-M-Y') }}</td>
                                    <td>
                                        <span class="badge {{ $sale->status === 'completed' ? 'active' : 'inActive' }}">
                                            {{ ucfirst($sale->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="account-action-links">
                                            <a href="{{ route('admin.sales.show', $sale) }}" class="btn btn-sm btn-outline-primary account-icon-btn" title="View Sale" aria-label="View Sale">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if (auth()->user()?->isAdmin())
                                                <form action="{{ route('admin.sales.destroy', $sale) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to remove this sale?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger account-icon-btn" title="Remove Sale" aria-label="Remove Sale">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @if ($sale->status === 'draft')
                                                <a href="{{ route('admin.sales.index', ['draft' => $sale->id]) }}" class="btn btn-sm btn-outline-secondary">
                                                    Load Draft
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">No sales found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const saleItemsContainer = document.getElementById('saleItemsContainer');
        const addSaleItemRowButton = document.getElementById('addSaleItemRow');
        const discountPercentInput = document.getElementById('discountPercent');
        const itemsSubtotalPreview = document.getElementById('itemsSubtotalPreview');
        const grandTotalPreview = document.getElementById('grandTotalPreview');
        const actualCostPreview = document.getElementById('actualCostPreview');
        const profitPreview = document.getElementById('profitPreview');
        const products = @json($productsJson);
        const productUnits = @json($productUnitsJson);
        const initialItems = @json($initialItems);
        const showProfit = @json(! $isSalesmanView);
        let rowIndex = 0;

        if (!saleItemsContainer || !addSaleItemRowButton) {
            return;
        }

        const productOptionsHtml = (selectedValue = '') => {
            let html = '<option value="">Select Product</option>';

            Object.values(products).forEach((product) => {
                const selected = String(selectedValue) === String(product.id) ? 'selected' : '';
                html += `<option value="${product.id}" ${selected}>${product.name} | ${product.code} | Stock ${product.stock} | Rs. ${Number(product.price).toFixed(2)}</option>`;
            });

            return html;
        };

        const selectedWeaponIds = (excludeRow = null) => {
            const selected = new Set();

            saleItemsContainer.querySelectorAll('.sale-item-row').forEach((row) => {
                if (excludeRow && row === excludeRow) {
                    return;
                }

                const weaponSelect = row.querySelector('.sale-item-weapon');
                const weaponId = weaponSelect?.value;

                if (weaponId) {
                    selected.add(String(weaponId));
                }
            });

            return selected;
        };

        const refreshSerializedRows = () => {
            saleItemsContainer.querySelectorAll('.sale-item-row').forEach((row) => {
                const productSelect = row.querySelector('.sale-item-product');
                const product = products[String(productSelect?.value)] || products[Number(productSelect?.value)];

                if (product?.serialized) {
                    syncRow(row, true);
                }
            });
        };

        const updateTotals = () => {
            let subtotal = 0;
            let totalCost = 0;

            saleItemsContainer.querySelectorAll('.sale-item-row').forEach((row) => {
                const qty = parseFloat(row.querySelector('.sale-item-qty')?.value || 0);
                const price = parseFloat(row.querySelector('.sale-item-price')?.value || 0);
                const actualCost = parseFloat(row.querySelector('.sale-item-actual-cost')?.value || 0);
                subtotal += qty * price;
                totalCost += actualCost;
            });

            const discountPercent = parseFloat(discountPercentInput?.value || 0);
            const discountAmount = subtotal * (discountPercent / 100);
            const grandTotal = subtotal - discountAmount;
            const profit = grandTotal - totalCost;

            if (itemsSubtotalPreview) {
                itemsSubtotalPreview.value = subtotal.toFixed(2);
            }

            if (grandTotalPreview) {
                grandTotalPreview.value = grandTotal.toFixed(2);
            }

            if (actualCostPreview) {
                actualCostPreview.value = totalCost.toFixed(2);
            }

            if (profitPreview) {
                profitPreview.value = profit.toFixed(2);
            }
        };

        const calculateCostPreview = (product, qty, selectedUnitId = '') => {
            if (!product || qty <= 0) {
                return 0;
            }

            if (product.serialized) {
                const units = productUnits[String(product.id)] || productUnits[product.id] || [];
                const selectedUnit = units.find((unit) => String(unit.id) === String(selectedUnitId));

                return Number(selectedUnit?.unit_purchase_price || 0);
            }

            let remaining = qty;
            let totalCost = 0;
            const layers = Array.isArray(product.cost_layers) ? product.cost_layers : [];

            layers.forEach((layer) => {
                if (remaining <= 0) {
                    return;
                }

                const picked = Math.min(remaining, Number(layer.quantity || 0));
                totalCost += picked * Number(layer.unit_purchase_price || 0);
                remaining -= picked;
            });

            return totalCost;
        };

        const syncRow = (row, preserveValues = false) => {
            const productSelect = row.querySelector('.sale-item-product');
            const quantityInput = row.querySelector('.sale-item-qty');
            const priceInput = row.querySelector('.sale-item-price');
            const actualCostInput = row.querySelector('.sale-item-actual-cost');
            const actualProfitInput = row.querySelector('.sale-item-profit');
            const productUnitWrap = row.querySelector('.sale-item-weapon-wrap');
            const productUnitSelect = row.querySelector('.sale-item-weapon');
            const serialWrap = row.querySelector('.sale-item-serial-wrap');
            const serialInput = row.querySelector('.sale-item-serial');
            const product = products[String(productSelect.value)] || products[Number(productSelect.value)];

            if (!product) {
                productUnitWrap.classList.add('d-none');
                serialWrap.classList.add('d-none');
                productUnitSelect.innerHTML = '<option value="">Select Weapon Code</option>';
                priceInput.value = '';
                quantityInput.removeAttribute('readonly');
                if (actualCostInput) {
                    actualCostInput.value = '0.00';
                }
                if (actualProfitInput) {
                    actualProfitInput.value = '0.00';
                }
                updateTotals();
                return;
            }

            if (!preserveValues || !priceInput.value) {
                priceInput.value = Number(product.price).toFixed(2);
            }

            if (product.serialized) {
                const units = productUnits[String(product.id)] || productUnits[product.id] || [];
                const alreadySelected = selectedWeaponIds(row);
                const currentSelectedUnitId = String(productUnitSelect.value || row.dataset.selectedUnitId || '');
                const canKeepCurrentSelection = currentSelectedUnitId && !alreadySelected.has(currentSelectedUnitId);
                productUnitWrap.classList.remove('d-none');
                serialWrap.classList.remove('d-none');
                productUnitSelect.innerHTML = '<option value="">Select Weapon Code</option>';

                units.forEach((unit) => {
                    if (alreadySelected.has(String(unit.id))) {
                        return;
                    }

                    const selected = canKeepCurrentSelection && String(unit.id) === currentSelectedUnitId ? 'selected' : '';
                    productUnitSelect.insertAdjacentHTML(
                        'beforeend',
                        `<option value="${unit.id}" data-weapon-number="${unit.weapon_number}" ${selected}>${unit.weapon_number}</option>`
                    );
                });

                if (!productUnitSelect.value) {
                    const firstAvailableOption = Array.from(productUnitSelect.options).find((option) => option.value);
                    productUnitSelect.value = firstAvailableOption?.value || '';
                }

                quantityInput.value = 1;
                quantityInput.setAttribute('readonly', 'readonly');
                serialInput.setAttribute('readonly', 'readonly');
                const selectedOption = productUnitSelect.options[productUnitSelect.selectedIndex];
                serialInput.value = selectedOption?.dataset.weaponNumber || '';
                row.dataset.selectedUnitId = productUnitSelect.value || '';
            } else {
                productUnitWrap.classList.add('d-none');
                serialWrap.classList.add('d-none');
                productUnitSelect.innerHTML = '<option value="">Select Weapon Code</option>';
                productUnitSelect.value = '';
                serialInput.value = '';
                quantityInput.removeAttribute('readonly');
                serialInput.removeAttribute('readonly');
                row.dataset.selectedUnitId = '';
            }

            const qty = parseFloat(quantityInput.value || 0);
            const price = parseFloat(priceInput.value || 0);
            const actualCost = calculateCostPreview(product, qty, productUnitSelect.value);

            if (actualCostInput) {
                actualCostInput.value = actualCost.toFixed(2);
            }

            if (actualProfitInput) {
                actualProfitInput.value = ((qty * price) - actualCost).toFixed(2);
            }

            updateTotals();
        };

        const attachRowListeners = (row) => {
            const productSelect = row.querySelector('.sale-item-product');
            const productUnitSelect = row.querySelector('.sale-item-weapon');
            const quantityInput = row.querySelector('.sale-item-qty');
            const priceInput = row.querySelector('.sale-item-price');
            const serialInput = row.querySelector('.sale-item-serial');
            const removeButton = row.querySelector('.remove-sale-item');

            const handleProductChange = () => {
                row.dataset.selectedUnitId = '';
                syncRow(row);
                refreshSerializedRows();
            };

            const handleWeaponChange = () => {
                const selectedOption = productUnitSelect.options[productUnitSelect.selectedIndex];
                serialInput.value = selectedOption?.dataset.weaponNumber || '';
                row.dataset.selectedUnitId = productUnitSelect.value || '';
                syncRow(row, true);
                refreshSerializedRows();
            };

            if (window.jQuery) {
                window.jQuery(productSelect).on('change select2:select', handleProductChange);
            } else {
                productSelect.addEventListener('change', handleProductChange);
            }
            productUnitSelect.addEventListener('change', handleWeaponChange);
            quantityInput.addEventListener('input', () => syncRow(row, true));
            priceInput.addEventListener('input', () => syncRow(row, true));
            removeButton.addEventListener('click', () => {
                row.remove();
                refreshSerializedRows();
                updateTotals();
            });
        };

        const createRow = (initialItem = null) => {
            const row = document.createElement('div');
            row.className = 'row g-3 align-items-end mb-3 sale-item-row border-bottom pb-3';
            row.dataset.rowIndex = String(rowIndex);
            row.dataset.selectedUnitId = initialItem?.product_unit_id || '';
            row.innerHTML = `
                <div class="col-md-4">
                    <label class="form-label">Product</label>
                    <select class="form-control sale-item-product js-select2" data-placeholder="Search product" name="items[${rowIndex}][product_id]" required>
                        ${productOptionsHtml(initialItem?.product_id || '')}
                    </select>
                </div>
                <div class="col-md-3 sale-item-weapon-wrap d-none">
                    <label class="form-label">Weapon Code</label>
                    <select class="form-control sale-item-weapon" name="items[${rowIndex}][product_unit_id]">
                        <option value="">Select Weapon Code</option>
                    </select>
                </div>
                <div class="col-md-3 sale-item-serial-wrap d-none">
                    <label class="form-label">Weapon / Serial</label>
                    <input type="text" class="form-control sale-item-serial" name="items[${rowIndex}][serial_number]" value="${initialItem?.serial_number || ''}" placeholder="Auto from selected weapon code">
                </div>
                <div class="col-md-1">
                    <label class="form-label">Qty</label>
                    <input type="number" class="form-control sale-item-qty" name="items[${rowIndex}][quantity]" value="${initialItem?.quantity || 1}" min="1" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Price</label>
                    <input type="number" class="form-control sale-item-price" name="items[${rowIndex}][price]" value="${initialItem?.price || 0}" min="0" step="0.01" required>
                </div>
                ${showProfit ? `
                    <div class="col-md-2">
                        <label class="form-label">Actual Cost</label>
                        <input type="text" class="form-control sale-item-actual-cost" value="0.00" readonly>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Profit</label>
                        <input type="text" class="form-control sale-item-profit" value="0.00" readonly>
                    </div>
                ` : ''}
                <div class="col-md-1">
                    <button type="button" class="btn btn-outline-danger remove-sale-item" title="Remove item">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;

            saleItemsContainer.appendChild(row);
            if (typeof window.initSelect2Elements === 'function') {
                window.initSelect2Elements(row);
            }
            attachRowListeners(row);
            syncRow(row, Boolean(initialItem));
            rowIndex += 1;
        };

        addSaleItemRowButton.addEventListener('click', createRow);
        discountPercentInput?.addEventListener('input', updateTotals);

        initialItems.forEach((item) => createRow(item));
    })();
</script>
@endpush
