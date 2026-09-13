@extends('admin.layouts.main')

@section('title', 'Khata Detail')
@section('pageHeading', 'Khata Detail')

@section('content')
@php($editingEntry = $editingEntry ?? null)
@php($initialPartnerId = old('partner_id', $editingEntry?->partner_id))
@php($initialInvestmentTotal = old('investment_amount', $editingEntry?->total_amount !== null ? number_format((float) $editingEntry->total_amount, 2, '.', '') : ''))
@php($initialWalletUsedTotal = old('wallet_used_amount', $editingEntry ? number_format((float) $editingEntry->items->sum('wallet_used_amount'), 2, '.', '') : ''))
@php($initialOnHoldTotal = old('on_hold_amount', $editingEntry ? number_format((float) $editingEntry->items->sum('on_hold_amount'), 2, '.', '') : ''))
@php($initialInvestmentDate = old('investment_date', $editingEntry?->investment_date?->format('Y-m-d') ?: now()->format('Y-m-d')))
@php($initialNotes = old('notes', $editingEntry?->notes))
@php($initialProductIds = collect(old('product_ids', ($editingEntry?->items ?? collect())->pluck('product_id')->filter()->values()->all()))->map(fn ($id) => (int) $id)->all())
@php($hasOldEditProfitPercent = old('profit_percent') !== null)
@php($initialEditProfitPercent = old('profit_percent', $editingEntry && $editingEntry->items->isNotEmpty() ? number_format((float) $editingEntry->items->first()->profit_value / 2, 2, '.', '') : ''))
@php($initialMembers = old('members', [[
    'partner_id' => '',
    'investment_amount' => '',
    'wallet_used_amount' => '',
    'on_hold_amount' => '',
    'profit_percent' => '',
]]))
<div class="accounts-dashboard">
    <div class="dashboard-content__head">
        <h2 class="boldHeading">{{ $khata->name }}</h2>

        <nav aria-label="breadcrumb">
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item">Accounts</li>
                <li class="breadcrumb-item"><a href="{{ route('admin.accounts.khatas.index') }}">Khatas</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $khata->name }}</li>
            </ul>
        </nav>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="account-card account-card--orange">
                <div class="account-card__content">
                    <h3 class="account-card__title">Members</h3>
                    <p class="metric-card__value">{{ $overview['members_count'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="account-card account-card--teal">
                <div class="account-card__content">
                    <h3 class="account-card__title">Total Amount</h3>
                    <p class="metric-card__value">{{ number_format((float) $overview['total_amount'], 2) }}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="account-card account-card--red">
                <div class="account-card__content">
                    <h3 class="account-card__title">Available Balance</h3>
                    <p class="metric-card__value">{{ number_format((float) $overview['on_hold_amount'], 2) }}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="account-card account-card--yellow">
                <div class="account-card__content">
                    <h3 class="account-card__title">Active Amount</h3>
                    <p class="metric-card__value">{{ number_format((float) $overview['active_amount'], 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    @if ($editingEntry)
        <div class="dashboard-card mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <h4 class="mb-0">Edit Khata Investment</h4>
                    <p class="account-toolbar__text mb-0">Member amount selected products me equal split ho kar save hogi aur profit percentage auto formula se calculate hogi.</p>
                </div>
                <a href="{{ route('admin.accounts.khatas.show', $khata) }}" class="themeBtn themeBtn--white">Back</a>
            </div>

            <form action="{{ route('admin.accounts.khatas.entries.update', [$khata, $editingEntry]) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3 mb-3">
                    <div class="col-lg-3">
                        <label class="form-label">Partner</label>
                        <select class="form-select js-select2" name="partner_id" id="khataPartnerSelect" required>
                            <option value="">Select Partner</option>
                            @foreach ($partners as $partner)
                                <option value="{{ $partner->id }}" @selected((string) $initialPartnerId === (string) $partner->id)>{{ $partner->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3">
                        <label class="form-label">Reusable Hold Balance</label>
                        <input type="text" class="form-control" id="khataEditWalletAvailable" value="0.00" readonly>
                    </div>
                    <div class="col-lg-3">
                        <label class="form-label">New Amount</label>
                        <input type="number" class="form-control" id="khataInvestmentAmount" name="investment_amount" value="{{ $initialInvestmentTotal }}" min="0" step="0.01" required>
                    </div>
                    <div class="col-lg-3">
                        <label class="form-label">Use Hold Balance</label>
                        <input type="number" class="form-control" id="khataEditWalletUsedAmount" name="wallet_used_amount" value="{{ $initialWalletUsedTotal }}" min="0" step="0.01">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-lg-3">
                        <label class="form-label">Keep In Hold</label>
                        <input type="number" class="form-control" id="khataEditOnHoldAmount" name="on_hold_amount" value="{{ $initialOnHoldTotal }}" min="0" step="0.01">
                    </div>
                    <div class="col-lg-3">
                        <label class="form-label">Active Amount</label>
                        <input type="text" class="form-control" id="khataEditActiveAmount" value="0.00" readonly>
                    </div>
                    <div class="col-lg-3">
                        <label class="form-label">Investment Date</label>
                        <input type="date" class="form-control" name="investment_date" value="{{ $initialInvestmentDate }}" required>
                    </div>
                    <div class="col-lg-3">
                        <label class="form-label">Product Amount</label>
                        <input type="text" class="form-control" id="khataEditProductAmount" value="0.00" readonly>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-lg-6">
                        <label class="form-label">Products</label>
                        <select class="form-select js-select2" name="product_ids[]" id="khataEditProductIds" multiple required>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" @selected(in_array($product->id, $initialProductIds, true))>{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="2" placeholder="Optional note">{{ $initialNotes }}</textarea>
                    </div>
                    <div class="col-12">
                        <div class="account-data-table__subtext" id="khataInvestmentHelpText">
                            Selected products me amount, hold aur auto-pay equally split hongi.
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="account-data-table__subtext" id="khataInvestmentBreakdownText"></div>
                    </div>
                    <div class="col-lg-3">
                        <label class="form-label">Profit %</label>
                        <input type="number" class="form-control" id="khataEditProfitPercent" name="profit_percent" value="{{ $initialEditProfitPercent }}" min="0" max="100" step="0.01">
                        <div class="account-data-table__subtext mt-1" id="khataEditProfitHelpText"></div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="themeBtn w-100">Update Investment</button>
                    <a href="{{ route('admin.accounts.khatas.show', $khata) }}" class="themeBtn themeBtn--white w-100 text-center">Cancel Edit</a>
                </div>
                <div class="account-data-table__subtext mt-2">Aik hi profit % field dikh rahi hai. Admin isko adjust kare aur backend yahi % selected sab products par apply karega.</div>
            </form>
        </div>
    @else
        <div class="dashboard-card mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <h4 class="mb-0">Add Multi Product Structure</h4>
                    <div class="account-toolbar__text mt-2">
                        <div class="mb-1">Product dropdown me multiple products select karein. Un sab ka total capital mil kar Product Amount me show hoga.</div>
                        <div class="mb-1">Har member ki New Amount, Hold aur Use Hold Balance selected products me equal split ho kar save hogi.</div>
                        <div class="mb-1">Profit percentage ka formula same rahega. Har product par partner ka share us product ki active amount ke hisab se calculate hoga.</div>
                        <div><strong>Formula:</strong> Active = Amount - Hold | Product Amount = Selected Products Total | Member Split = Member Amount / Selected Products Count | Partner Profit % = Ownership % / 2</div>
                    </div>
                </div>
                <a href="{{ route('admin.accounts.khatas.index') }}" class="themeBtn themeBtn--white">Back</a>
            </div>

            <form action="{{ route('admin.accounts.khatas.entries.store', $khata) }}" method="POST">
                @csrf
                <div class="row g-3 mb-3">
                    <div class="col-lg-3">
                        <label class="form-label">Investment Date</label>
                        <input type="date" class="form-control" name="investment_date" value="{{ $initialInvestmentDate }}" required>
                    </div>
                    <div class="col-lg-5">
                        <label class="form-label">Products</label>
                        <select class="form-select js-select2" name="product_ids[]" id="khataCreateProductIds" multiple required>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" @selected(in_array($product->id, $initialProductIds, true))>{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label">Product Amount</label>
                        <input type="text" class="form-control" id="khataCreateProductAmount" value="0.00" readonly>
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label">Notes</label>
                        <input type="text" class="form-control" name="notes" value="{{ $initialNotes }}" placeholder="Optional note for all created partner entries">
                    </div>
                </div>

                <div class="account-data-table__subtext mb-3" id="khataCreateProductHelpText">
                    Products select karte hi total amount aur per-product split preview update ho jayegi.
                </div>

                <div id="khataMembersContainer"></div>

                <div class="d-flex gap-2 mt-3">
                    <button type="button" class="btn btn-outline-secondary filterBtn" id="addKhataMemberRow">
                        <i class="fas fa-user-plus"></i>
                    </button>
                    <button type="submit" class="themeBtn w-100">Save All Investments</button>
                </div>
                <div class="account-data-table__subtext mt-2">
                    Aik save me selected products sab members par apply hongi. Har member ki row database me inhi products ke equal split ke sath save hogi aur admin remainder by default rahega.
                </div>
            </form>
        </div>
    @endif

    <div class="account-ledger mb-4">
        <div class="account-ledger__header">
            <h3 class="account-ledger__title">Current Product Structure</h3>
            <div class="account-ledger__controls">
                <span class="badge blue">{{ $productStructures->count() }} Products</span>
            </div>
        </div>
        <div class="account-data-table__subtext mb-3">
            Restock ya purchase amount change hone par unlocked partner profit current stock value ke hisab se auto update hota rahega. Neeche admin chahe to kisi bhi partner ka profit manual save kar sakta hai ya dobara auto mode par la sakta hai.
        </div>
        <div class="table-responsive">
            <table class="table table-hover tables account-data-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Capital</th>
                        <th>Partners</th>
                        <th>Admin Default</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($productStructures as $structure)
                        <tr>
                            <td>{{ $structure['name'] }}</td>
                            <td>{{ number_format((float) $structure['capital'], 2) }}</td>
                            <td>
                                @if ($structure['partners']->isNotEmpty())
                                    @foreach ($structure['partners'] as $partner)
                                        <div class="border rounded p-2 mb-2">
                                            <div class="fw-semibold">{{ $partner['name'] }}</div>
                                            <div class="account-data-table__subtext mb-2">
                                                Active {{ number_format((float) $partner['active_amount'], 2) }}
                                                | Ownership {{ number_format((float) $partner['ownership_percent'], 2) }}%
                                                | Current Profit {{ number_format((float) $partner['profit_percent'], 2) }}%
                                                | Suggested {{ number_format((float) $partner['suggested_profit_percent'], 2) }}%
                                                | {{ $partner['is_profit_manual'] ? 'Manual Override' : 'Auto Stock Based' }}
                                            </div>
                                            <form action="{{ route('admin.accounts.khatas.profits.update', [$khata, $partner['id']]) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="row g-2 align-items-end">
                                                    <div class="col-lg-4">
                                                        <label class="form-label mb-1">Partner Profit %</label>
                                                        <input
                                                            type="number"
                                                            class="form-control"
                                                            name="product_profit_percent"
                                                            value="{{ number_format((float) $partner['profit_percent'], 2, '.', '') }}"
                                                            min="0"
                                                            max="100"
                                                            step="0.01"
                                                        >
                                                    </div>
                                                    <div class="col-lg-8">
                                                        <div class="d-flex gap-2 flex-wrap">
                                                            <button type="submit" name="mode" value="manual" class="themeBtn">
                                                                Save Manual Profit
                                                            </button>
                                                            <button type="submit" name="mode" value="auto" class="themeBtn themeBtn--white">
                                                                Use Auto Profit
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    @endforeach
                                @else
                                    No partner investment yet.
                                @endif
                            </td>
                            <td>
                                {{ $structure['admin_name'] }} (ID {{ $structure['admin_user_id'] }})
                                <div class="account-data-table__subtext">Capital {{ number_format((float) $structure['admin_active_amount'], 2) }} / Ownership {{ number_format((float) $structure['admin_ownership_percent'], 2) }}% / Profit {{ number_format((float) $structure['admin_profit_percent'], 2) }}%</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">No products found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="account-ledger mb-4">
        <div class="account-ledger__header">
            <h3 class="account-ledger__title">Member Summary</h3>
            <div class="account-ledger__controls">
                <span class="badge blue">{{ $memberSummaries->count() }} Members</span>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover tables account-data-table">
                <thead>
                    <tr>
                        <th>Partner</th>
                        <th>Entries</th>
                        <th>Total Amount</th>
                        <th>Available Balance</th>
                        <th>Active</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($memberSummaries as $summaryRow)
                        <tr>
                            <td>{{ $summaryRow['partner_name'] }}</td>
                            <td>{{ $summaryRow['entries_count'] }}</td>
                            <td>{{ number_format((float) $summaryRow['total_amount'], 2) }}</td>
                            <td>{{ number_format((float) $summaryRow['on_hold_amount'], 2) }}</td>
                            <td>{{ number_format((float) $summaryRow['active_amount'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No member summary found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="account-ledger">
        <div class="account-ledger__header">
            <h3 class="account-ledger__title">Khata Investment History</h3>
            <div class="account-ledger__controls">
                <span class="badge blue">{{ $entries->count() }} Records</span>
            </div>
        </div>
        <div class="account-data-table__subtext mb-3">
            Saved % khata ki original history hai. Current % latest stock/restock ke mutabiq live profit hai jo time ke sath change ho sakta hai.
        </div>
        <div class="table-responsive">
            <table class="table table-hover tables account-data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Partner</th>
                        <th>Total Amount</th>
                        <th>Products</th>
                        <th>Notes</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($entries as $entry)
                        <tr>
                            <td>{{ $entry->investment_date?->format('d-M-Y') }}</td>
                            <td>{{ $entry->partner?->name ?: 'Partner' }}</td>
                            <td>{{ number_format((float) $entry->total_amount, 2) }}</td>
                            <td>
                                {{ $entry->items->map(function ($item) use ($entry, $productSnapshots) {
                                    $currentProfit = data_get($productSnapshots, $item->product_id . '.partners.' . $entry->partner_id . '.profit_value');

                                    return ($item->product?->name ?: 'Product')
                                        . ' (Amount ' . number_format((float) $item->amount, 2)
                                        . ' / Hold ' . number_format((float) $item->on_hold_amount, 2)
                                        . ' / Saved % ' . number_format((float) $item->profit_value / 2, 2)
                                        . ' / Current % ' . number_format((float) ($currentProfit ?? ($item->profit_value / 2)), 2)
                                        . ')';
                                })->implode(', ') }}
                            </td>
                            <td>{{ $entry->notes ?: 'N/A' }}</td>
                            <td>
                                <div class="account-action-links">
                                    <a href="{{ route('admin.accounts.khatas.show', ['khata' => $khata, 'entry' => $entry->id]) }}" class="btn btn-sm btn-outline-primary account-icon-btn" title="Edit Investment" aria-label="Edit Investment">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <form action="{{ route('admin.accounts.khatas.entries.destroy', [$khata, $entry]) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this investment?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger account-icon-btn" title="Delete Investment" aria-label="Delete Investment">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No khata investment history found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const editingMode = @json((bool) $editingEntry);
        const products = @json($products->map(fn ($product) => ['id' => $product->id, 'name' => $product->name])->values());
        const partners = @json($partners->map(fn ($partner) => ['id' => $partner->id, 'name' => $partner->name])->values());
        const productSnapshots = @json($productSnapshots);
        const partnerWallets = @json($partnerWallets);
        const initialMembers = @json($initialMembers);
        const initialEditProfitPercent = @json($initialEditProfitPercent);
        const hasOldEditProfitPercent = @json($hasOldEditProfitPercent);

        const formatAmount = (value) => Number(value || 0).toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });

        const toFixedAmount = (value) => Number(value || 0).toFixed(2);
        const getSelectedValues = (select) => Array.from(select?.selectedOptions || [])
            .map((option) => option.value)
            .filter((value) => value !== '');
        const productNameById = (productId) => products.find((product) => String(product.id) === String(productId))?.name || 'Product';
        const productCapital = (productId) => Number(productSnapshots?.[productId]?.product_capital || 0);
        const currentPartnerActiveAmount = (productId, partnerId) => Number(productSnapshots?.[productId]?.partners?.[partnerId]?.active_amount || 0);
        const suggestedOverallProfitPercent = (productIds, partnerId, activeAmount) => {
            const totalCapital = totalSelectedProductAmount(productIds);

            if (totalCapital <= 0) {
                return 0;
            }

            const currentPartnerActive = productIds.reduce((sum, productId) => {
                return sum + currentPartnerActiveAmount(productId, partnerId);
            }, 0);

            return Number((((currentPartnerActive + Number(activeAmount || 0)) / totalCapital) * 100 / 2).toFixed(2));
        };

        const partnerOptions = (selectedValue = '') => {
            let html = '<option value="">Select Partner</option>';

            partners.forEach((partner) => {
                const selected = String(selectedValue) === String(partner.id) ? 'selected' : '';
                html += `<option value="${partner.id}" ${selected}>${partner.name}</option>`;
            });

            return html;
        };

        const distributedValues = (total, count) => {
            const normalizedCount = Math.max(0, Number(count || 0));
            const normalizedTotal = Number(Number(total || 0).toFixed(2));

            if (!normalizedCount) {
                return [];
            }

            if (normalizedCount === 1) {
                return [normalizedTotal];
            }

            const baseAmount = Number((normalizedTotal / normalizedCount).toFixed(2));
            let runningAmount = 0;
            const values = [];

            for (let index = 0; index < normalizedCount; index += 1) {
                const value = index === normalizedCount - 1
                    ? Number((normalizedTotal - runningAmount).toFixed(2))
                    : baseAmount;

                if (index !== normalizedCount - 1) {
                    runningAmount = Number((runningAmount + value).toFixed(2));
                }

                values.push(Math.max(0, value));
            }

            return values;
        };

        const totalSelectedProductAmount = (productIds) => productIds.reduce((sum, productId) => {
            return sum + productCapital(productId);
        }, 0);

        const bindSelectEvents = (element, callback) => {
            element?.addEventListener('change', callback);

            if (window.jQuery && element) {
                window.jQuery(element).on('select2:select select2:unselect change', callback);
            }
        };

        const perProductFromTotal = (totalPercent, count) => {
            const normalizedCount = Math.max(1, Number(count || 1));
            const totalRaw = Number(Number(totalPercent || 0).toFixed(2));
            const parts = distributedValues(totalRaw, normalizedCount);
            return parts.map((value) => Number(value.toFixed(2)));
        };

        const buildSplitPreview = (productIds, amount, onHoldAmount, walletUsedAmount, partnerId = '', manualTotalProfitPercent = null) => {
            if (!productIds.length) {
                return 'Products select karein. Split preview yahan aa jayega.';
            }

            const amountParts = distributedValues(amount, productIds.length);
            const onHoldParts = distributedValues(Math.min(amount, onHoldAmount), productIds.length);
            const walletParts = distributedValues(walletUsedAmount, productIds.length);
            const suggestedProfit = suggestedOverallProfitPercent(productIds, partnerId, Math.max(0, Number((amount - onHoldAmount).toFixed(2))));
            const hasManualTotal = manualTotalProfitPercent !== null && !isNaN(manualTotalProfitPercent);
            const perProductProfits = hasManualTotal
                ? perProductFromTotal(manualTotalProfitPercent, productIds.length)
                : productIds.map(() => suggestedProfit);

            return productIds.map((productId, index) => {
                const amountPart = Number(amountParts[index] || 0);
                const onHoldPart = Number(onHoldParts[index] || 0);
                const walletPart = Number(walletParts[index] || 0);
                const activePart = Math.max(0, Number((amountPart - onHoldPart).toFixed(2)));
                const profitPercent = Number(perProductProfits[index] || 0);

                return `${productNameById(productId)}: Amount ${formatAmount(amountPart)}, Use Hold ${formatAmount(walletPart)}, Hold ${formatAmount(onHoldPart)}, Active ${formatAmount(activePart)}, Profit ${profitPercent.toFixed(2)}%`;
            }).join(' | ');
        };
        const syncProfitInput = (input, helpText, suggestedValue, shouldRespectManual = true, productCount = 0) => {
            if (!input) {
                return;
            }

            const previousSuggested = Number(input.dataset.suggestedValue || 0);
            const currentValue = Number(input.value || 0);
            const hasManualValue = input.dataset.manual === 'true';
            const shouldUpdateValue = !input.value || !shouldRespectManual || !hasManualValue || Math.abs(currentValue - previousSuggested) < 0.01;

            if (shouldUpdateValue) {
                input.value = suggestedValue.toFixed(2);
                input.dataset.manual = 'false';
            }

            input.dataset.suggestedValue = suggestedValue.toFixed(2);

            if (helpText) {
                const count = Math.max(1, Number(productCount || 0));
                const isManual = input.dataset.manual === 'true';
                if (isManual && count > 1) {
                    const enteredValue = Number(input.value || 0);
                    const perProduct = Number((enteredValue / count).toFixed(2));
                    helpText.textContent = `Manual TOTAL ${enteredValue.toFixed(2)}% hai. Selected ${count} products me equal divide: har product par ~${perProduct.toFixed(2)}% lagega.`;
                } else if (isManual && count === 1) {
                    const enteredValue = Number(input.value || 0);
                    helpText.textContent = `Manual TOTAL ${enteredValue.toFixed(2)}% hai. Sirf 1 product select hai to isi product par ${enteredValue.toFixed(2)}% lagega.`;
                } else {
                    helpText.textContent = `Auto ${suggestedValue.toFixed(2)}% prefill hai (har product par). Admin chahe to isko change kar sakta hai aur manual value TOTAL samjha kar selected products me divide ho jayega.`;
                }
            }
        };

        if (editingMode) {
            const partnerSelect = document.getElementById('khataPartnerSelect');
            const amountInput = document.getElementById('khataInvestmentAmount');
            const walletAvailableInput = document.getElementById('khataEditWalletAvailable');
            const walletUsedInput = document.getElementById('khataEditWalletUsedAmount');
            const onHoldInput = document.getElementById('khataEditOnHoldAmount');
            const activeInput = document.getElementById('khataEditActiveAmount');
            const productSelect = document.getElementById('khataEditProductIds');
            const productAmountInput = document.getElementById('khataEditProductAmount');
            const helpText = document.getElementById('khataInvestmentHelpText');
            const breakdownText = document.getElementById('khataInvestmentBreakdownText');
            const profitInput = document.getElementById('khataEditProfitPercent');
            const profitHelpText = document.getElementById('khataEditProfitHelpText');

            if (!partnerSelect || !amountInput || !walletAvailableInput || !walletUsedInput || !onHoldInput || !activeInput || !productSelect || !productAmountInput || !helpText || !breakdownText || !profitInput || !profitHelpText) {
                return;
            }

            const syncEditForm = () => {
                const selectedProductIds = getSelectedValues(productSelect);
                const partnerId = partnerSelect.value || '';
                const amount = Number(amountInput.value || 0);
                const walletUsedAmount = Number(walletUsedInput.value || 0);
                const availableBalance = Number(partnerWallets?.[partnerId]?.available_balance || 0);
                let onHoldAmount = Number(onHoldInput.value || 0);

                if (onHoldAmount > amount) {
                    onHoldAmount = amount;
                    onHoldInput.value = toFixedAmount(onHoldAmount);
                }

                const suggestedProfit = suggestedOverallProfitPercent(selectedProductIds, partnerId, Math.max(0, Number((amount - onHoldAmount).toFixed(2))));
                const isManualProfit = profitInput.dataset.manual === 'true';
                const manualTotalProfit = isManualProfit ? Number(profitInput.value || 0) : null;

                walletAvailableInput.value = formatAmount(availableBalance);
                activeInput.value = formatAmount(Math.max(0, Number((amount - onHoldAmount).toFixed(2))));
                productAmountInput.value = formatAmount(totalSelectedProductAmount(selectedProductIds));

                if (!selectedProductIds.length) {
                    helpText.textContent = 'Products select karein. Selected products me amount, hold aur auto-pay equally split hongi.';
                    helpText.style.color = '';
                    breakdownText.textContent = 'Split preview yahan aa jayega.';
                    profitHelpText.textContent = '';
                    return;
                }

                if (walletUsedAmount > availableBalance) {
                    helpText.textContent = `Use hold balance ${formatAmount(walletUsedAmount)} available reusable hold balance ${formatAmount(availableBalance)} se zyada hai.`;
                    helpText.style.color = '#dc3545';
                } else {
                    helpText.textContent = `Selected ${selectedProductIds.length} products me yeh entry equal split ke sath save hogi. Product Amount ${formatAmount(totalSelectedProductAmount(selectedProductIds))} hai.`;
                    helpText.style.color = '';
                }

                breakdownText.textContent = buildSplitPreview(
                    selectedProductIds,
                    amount,
                    onHoldAmount,
                    walletUsedAmount,
                    partnerId,
                    manualTotalProfit
                );
                syncProfitInput(profitInput, profitHelpText, suggestedProfit, true, selectedProductIds.length);
            };

            [partnerSelect, amountInput, walletUsedInput, onHoldInput].forEach((element) => {
                element.addEventListener('input', syncEditForm);
                element.addEventListener('change', syncEditForm);
            });

            bindSelectEvents(partnerSelect, syncEditForm);
            bindSelectEvents(productSelect, syncEditForm);
            profitInput.addEventListener('input', () => {
                profitInput.dataset.manual = 'true';
                syncEditForm();
            });
            if (hasOldEditProfitPercent && initialEditProfitPercent !== '') {
                profitInput.dataset.manual = 'true';
            }
            syncEditForm();

            return;
        }

        const membersContainer = document.getElementById('khataMembersContainer');
        const addMemberButton = document.getElementById('addKhataMemberRow');
        const productSelect = document.getElementById('khataCreateProductIds');
        const productAmountInput = document.getElementById('khataCreateProductAmount');
        const productHelpText = document.getElementById('khataCreateProductHelpText');
        let memberIndex = 0;

        if (!membersContainer || !addMemberButton || !productSelect || !productAmountInput || !productHelpText) {
            return;
        }

        const getMemberRows = () => Array.from(membersContainer.querySelectorAll('.khata-member-row'));

        const buildPendingWalletUsage = () => {
            const pending = {};

            getMemberRows().forEach((row) => {
                const partnerId = row.querySelector('.khata-member-partner')?.value || '';

                if (!partnerId) {
                    return;
                }

                pending[partnerId] = Number((pending[partnerId] || 0) + Number(row.querySelector('.wallet-used-input')?.value || 0));
            });

            return pending;
        };

        const buildPartnerCounts = () => {
            const counts = {};

            getMemberRows().forEach((row) => {
                const partnerId = row.querySelector('.khata-member-partner')?.value || '';

                if (!partnerId) {
                    return;
                }

                counts[partnerId] = Number(counts[partnerId] || 0) + 1;
            });

            return counts;
        };

        const syncMemberRow = (row, selectedProductIds, pendingWalletUsage, partnerCounts) => {
            const partnerId = row.querySelector('.khata-member-partner')?.value || '';
            const amountInput = row.querySelector('.partner-amount-input');
            const walletUsedInput = row.querySelector('.wallet-used-input');
            const onHoldInput = row.querySelector('.on-hold-input');
            const walletAvailableDisplay = row.querySelector('.wallet-available-display');
            const activeDisplay = row.querySelector('.active-amount-display');
            const walletText = row.querySelector('.row-wallet-text');
            const helperText = row.querySelector('.row-helper-text');
            const profitInput = row.querySelector('.row-profit-input');
            const profitHelpText = row.querySelector('.row-profit-help');
            const amount = Number(amountInput?.value || 0);
            const walletUsedAmount = Number(walletUsedInput?.value || 0);
            const availableBalance = Number(partnerWallets?.[partnerId]?.available_balance || 0);
            const duplicatePartner = Number(partnerCounts?.[partnerId] || 0) > 1;
            let onHoldAmount = Number(onHoldInput?.value || 0);

            if (onHoldAmount > amount) {
                onHoldAmount = amount;
                onHoldInput.value = toFixedAmount(onHoldAmount);
            }

            const suggestedProfit = suggestedOverallProfitPercent(selectedProductIds, partnerId, Math.max(0, Number((amount - onHoldAmount).toFixed(2))));
            const isManualProfit = profitInput?.dataset.manual === 'true';
            const manualTotalProfit = isManualProfit ? Number(profitInput?.value || 0) : null;

            walletAvailableDisplay.value = formatAmount(availableBalance);
            activeDisplay.value = formatAmount(Math.max(0, Number((amount - onHoldAmount).toFixed(2))));

            if (walletText) {
                const pendingAmount = Number(pendingWalletUsage?.[partnerId] || 0);
                walletText.textContent = pendingAmount > availableBalance
                    ? `Use hold balance total ${formatAmount(pendingAmount)} available reusable hold balance ${formatAmount(availableBalance)} se zyada hai.`
                    : availableBalance > 0
                        ? `Reusable hold balance ${formatAmount(availableBalance)} available hai.`
                        : 'No previous reusable hold balance found. New amount se investment continue ho sakti hai.';
                walletText.style.color = pendingAmount > availableBalance ? '#dc3545' : '';
            }

            if (!helperText) {
                return;
            }

            if (!selectedProductIds.length) {
                helperText.textContent = 'Products select karein taake member split preview dikh sake.';
                helperText.style.color = '';
                if (profitHelpText) {
                    profitHelpText.textContent = '';
                }
                return;
            }

            if (duplicatePartner) {
                helperText.textContent = 'Same member ko aik save me sirf aik martaba add karein.';
                helperText.style.color = '#dc3545';
                return;
            }

            if (Number(pendingWalletUsage?.[partnerId] || 0) > availableBalance) {
                helperText.textContent = `Use hold balance ${formatAmount(pendingWalletUsage?.[partnerId] || 0)} available reusable hold balance ${formatAmount(availableBalance)} se zyada hai.`;
                helperText.style.color = '#dc3545';
                return;
            }

            helperText.textContent = buildSplitPreview(
                selectedProductIds,
                amount,
                onHoldAmount,
                walletUsedAmount,
                partnerId,
                manualTotalProfit
            );
            helperText.style.color = '';
            syncProfitInput(profitInput, profitHelpText, suggestedProfit, true, selectedProductIds.length);
        };

        const syncCreateForm = () => {
            const selectedProductIds = getSelectedValues(productSelect);
            const selectedNames = selectedProductIds.map((productId) => productNameById(productId));
            const productAmount = totalSelectedProductAmount(selectedProductIds);
            const pendingWalletUsage = buildPendingWalletUsage();
            const partnerCounts = buildPartnerCounts();

            productAmountInput.value = formatAmount(productAmount);
            productHelpText.textContent = selectedProductIds.length
                ? `Selected ${selectedProductIds.length} products: ${selectedNames.join(', ')}. In sab ka total Product Amount ${formatAmount(productAmount)} hai aur har member ki values in sab products me equal split hongi.`
                : 'Products select karte hi total amount aur per-product split preview update ho jayegi.';

            getMemberRows().forEach((row) => {
                syncMemberRow(row, selectedProductIds, pendingWalletUsage, partnerCounts);
            });
        };

        const createMemberRow = (member = {}) => {
            const row = document.createElement('div');
            row.className = 'dashboard-card mb-3 khata-member-row';
            row.dataset.memberIndex = String(memberIndex);
            row.innerHTML = `
                <div class="row g-3 align-items-end">
                    <div class="col-lg-3">
                        <label class="form-label">Member</label>
                        <select class="form-select js-select2 khata-member-partner" name="members[${memberIndex}][partner_id]">
                            ${partnerOptions(member.partner_id || '')}
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label">Reusable Hold Balance</label>
                        <input type="text" class="form-control wallet-available-display" value="0.00" readonly>
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label">New Amount</label>
                        <input type="number" class="form-control partner-amount-input" name="members[${memberIndex}][investment_amount]" value="${member.investment_amount || ''}" min="0" step="0.01">
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label">Use Hold Balance</label>
                        <input type="number" class="form-control wallet-used-input" name="members[${memberIndex}][wallet_used_amount]" value="${member.wallet_used_amount || ''}" min="0" step="0.01">
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label">Keep In Hold</label>
                        <input type="number" class="form-control on-hold-input" name="members[${memberIndex}][on_hold_amount]" value="${member.on_hold_amount || ''}" min="0" step="0.01">
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label">Profit %</label>
                        <input type="number" class="form-control row-profit-input" name="members[${memberIndex}][profit_percent]" value="${member.profit_percent || ''}" min="0" max="100" step="0.01">
                    </div>
                    <div class="col-lg-1">
                        <button type="button" class="btn btn-outline-danger remove-member-row" title="Remove Member">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="col-lg-3">
                        <label class="form-label">Active</label>
                        <input type="text" class="form-control active-amount-display" value="0.00" readonly>
                    </div>
                    <div class="col-12">
                        <div class="account-data-table__subtext row-wallet-text"></div>
                    </div>
                    <div class="col-12">
                        <div class="account-data-table__subtext row-helper-text"></div>
                    </div>
                    <div class="col-12">
                        <div class="account-data-table__subtext row-profit-help"></div>
                    </div>
                </div>
            `;

            membersContainer.appendChild(row);
            memberIndex += 1;

            if (typeof window.initSelect2Elements === 'function') {
                window.initSelect2Elements(row);
            }

            row.querySelector('.remove-member-row')?.addEventListener('click', () => {
                row.remove();
                syncCreateForm();
            });

            row.querySelectorAll('input').forEach((input) => {
                input.addEventListener('input', syncCreateForm);
                input.addEventListener('change', syncCreateForm);
            });
            row.querySelector('.row-profit-input')?.addEventListener('input', (event) => {
                event.currentTarget.dataset.manual = 'true';
                syncCreateForm();
            });
            if (member.profit_percent !== undefined && member.profit_percent !== '') {
                row.querySelector('.row-profit-input')?.setAttribute('data-manual', 'true');
            }

            bindSelectEvents(row.querySelector('.khata-member-partner'), syncCreateForm);
            syncCreateForm();
        };

        bindSelectEvents(productSelect, syncCreateForm);
        addMemberButton.addEventListener('click', () => createMemberRow());

        (initialMembers.length ? initialMembers : [{}]).forEach((member) => createMemberRow(member));
        syncCreateForm();
    })();
</script>
@endpush
