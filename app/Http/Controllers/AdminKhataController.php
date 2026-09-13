<?php

namespace App\Http\Controllers;

use App\Models\Khata;
use App\Models\PartnerInvestmentEntry;
use App\Models\PartnerInvestmentEntryItem;
use App\Models\Product;
use App\Models\ProductPartnerInvestment;
use App\Models\User;
use App\Support\PartnerInvestmentSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminKhataController extends Controller
{
    private const DEFAULT_ADMIN_ID = 1;

    public function index(): View
    {
        $khatas = Khata::query()
            ->with(['investmentEntries.partner', 'investmentEntries.items'])
            ->orderBy('name')
            ->get();

        $khataRows = $khatas->map(function (Khata $khata) {
            $items = $khata->investmentEntries->flatMap->items;
            $totalAmount = (float) $khata->investmentEntries->sum('total_amount');
            $onHoldAmount = (float) $items->sum('on_hold_amount');

            return [
                'khata' => $khata,
                'members_count' => $khata->investmentEntries->pluck('partner_id')->filter()->unique()->count(),
                'entries_count' => $khata->investmentEntries->count(),
                'total_amount' => round($totalAmount, 2),
                'on_hold_amount' => round($onHoldAmount, 2),
                'active_amount' => round(max(0, $totalAmount - $onHoldAmount), 2),
            ];
        });

        return view('admin.accounts.khatas.index', [
            'khataRows' => $khataRows,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('khatas', 'name')],
            'notes' => ['nullable', 'string'],
        ]);

        $khata = Khata::query()->create($data);

        return redirect()
            ->route('admin.accounts.khatas.show', $khata)
            ->with('status', 'Khata created successfully.');
    }

    public function destroy(Khata $khata): RedirectResponse
    {
        DB::transaction(function () use ($khata) {
            $partnerIds = PartnerInvestmentEntry::query()
                ->where('khata_id', $khata->id)
                ->pluck('partner_id')
                ->filter()
                ->unique()
                ->values()
                ->all();

            $khata->investmentEntries()->delete();
            $khata->delete();

            foreach ($partnerIds as $partnerId) {
                PartnerInvestmentSyncService::rebuildPartnerAssignments((int) $partnerId);
            }
        });

        return redirect()
            ->route('admin.accounts.khatas.index')
            ->with('status', 'Khata ' . $khata->name . ' delete ho gaya. Us ke sab entries aur partner assignments bhi sync ho gaye hain.');
    }

    public function show(Request $request, Khata $khata): View
    {
        $entries = PartnerInvestmentEntry::query()
            ->with(['partner', 'items.product'])
            ->where('khata_id', $khata->id)
            ->orderByDesc('id')
            ->get();

        $editingEntry = $request->filled('entry')
            ? PartnerInvestmentEntry::query()
                ->with(['partner', 'items.product'])
                ->where('khata_id', $khata->id)
                ->findOrFail($request->integer('entry'))
            : null;

        return view('admin.accounts.khatas.show', [
            'khata' => $khata,
            'entries' => $entries,
            'editingEntry' => $editingEntry,
            'adminUser' => User::query()->find(self::DEFAULT_ADMIN_ID, ['id', 'name']),
            'partnerWallets' => $this->partnerWallets(),
            'partners' => User::query()
                ->where('role', User::ROLE_PARTNER)
                ->orderBy('name')
                ->get(['id', 'name']),
            'products' => Product::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name']),
            'memberSummaries' => $this->memberSummaries($entries),
            'overview' => $this->overview($entries),
            'productSnapshots' => $this->productSnapshots(),
            'productStructures' => $this->productStructures(),
        ]);
    }

    public function storeEntry(Request $request, Khata $khata): RedirectResponse
    {
        [$entriesPayload, $partnerIds] = $this->validatedProductEntriesData($request, $khata);

        DB::transaction(function () use ($request, $entriesPayload, $partnerIds) {
            foreach ($entriesPayload as [$entryData, $assignments]) {
                $entry = PartnerInvestmentEntry::query()->create($entryData + [
                    'created_by' => $request->user()->id,
                ]);

                $entry->items()->createMany($this->persistableAssignments($assignments)->all());
            }

            foreach ($partnerIds as $partnerId) {
                PartnerInvestmentSyncService::rebuildPartnerAssignments((int) $partnerId);
            }

            $this->applyAssignmentProfitModes(
                $entriesPayload->flatMap(function (array $payload) {
                    [$entryData, $assignments] = $payload;

                    return $assignments->map(fn (array $assignment) => $assignment + [
                        'partner_id' => (int) $entryData['partner_id'],
                    ]);
                })
            );
        });

        return redirect()
            ->route('admin.accounts.khatas.show', $khata)
            ->with('status', 'Khata investments saved successfully.');
    }

    public function updateEntry(Request $request, Khata $khata, PartnerInvestmentEntry $investmentEntry): RedirectResponse
    {
        abort_unless($investmentEntry->khata_id === $khata->id, 404);

        [$entryData, $assignments] = $this->validatedEntryData($request, $khata, $investmentEntry);

        DB::transaction(function () use ($investmentEntry, $entryData, $assignments) {
            $investmentEntry->update($entryData);
            $investmentEntry->items()->delete();
            $investmentEntry->items()->createMany($this->persistableAssignments($assignments)->all());
            PartnerInvestmentSyncService::rebuildPartnerAssignments((int) $entryData['partner_id']);
            $this->applyAssignmentProfitModes(
                $assignments->map(fn (array $assignment) => $assignment + [
                    'partner_id' => (int) $entryData['partner_id'],
                ])
            );
        });

        return redirect()
            ->route('admin.accounts.khatas.show', $khata)
            ->with('status', 'Khata investment updated successfully.');
    }

    public function destroyEntry(Khata $khata, PartnerInvestmentEntry $investmentEntry): RedirectResponse
    {
        abort_unless($investmentEntry->khata_id === $khata->id, 404);

        DB::transaction(function () use ($investmentEntry) {
            $partnerId = (int) $investmentEntry->partner_id;
            $investmentEntry->delete();
            PartnerInvestmentSyncService::rebuildPartnerAssignments($partnerId);
        });

        return redirect()
            ->route('admin.accounts.khatas.show', $khata)
            ->with('status', 'Khata investment deleted successfully.');
    }

    public function updateProductProfit(Request $request, Khata $khata, ProductPartnerInvestment $investment): RedirectResponse
    {
        $belongsToKhata = PartnerInvestmentEntryItem::query()
            ->where('product_id', $investment->product_id)
            ->whereHas('entry', function ($query) use ($khata, $investment) {
                $query->where('khata_id', $khata->id)
                    ->where('partner_id', $investment->partner_id);
            })
            ->exists();

        abort_unless($belongsToKhata, 404);

        $data = $request->validate([
            'mode' => ['required', Rule::in(['manual', 'auto'])],
            'product_profit_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $investment->loadMissing(['product', 'partner']);

        DB::transaction(function () use ($data, $investment) {
            if (($data['mode'] ?? 'manual') === 'auto') {
                $investment->update([
                    'is_profit_manual' => false,
                ]);
            } else {
                $profitPercent = round((float) ($data['product_profit_percent'] ?? 0), 2);
                $this->ensureManualProductProfitLimit($investment, $profitPercent);

                $investment->update([
                    'profit_type' => 'percent',
                    'profit_value' => round($profitPercent * 2, 2),
                    'is_profit_manual' => true,
                ]);
            }

            PartnerInvestmentSyncService::syncProductAssignments((int) $investment->product_id);
        });

        $modeText = ($data['mode'] ?? 'manual') === 'auto'
            ? 'auto stock-based'
            : 'manual';

        return redirect()
            ->route('admin.accounts.khatas.show', $khata)
            ->with('status', ($investment->product?->name ?: 'Product') . ' par ' . ($investment->partner?->name ?: 'Partner') . " ka profit {$modeText} mode me update ho gaya.");
    }

    private function validatedEntryData(Request $request, Khata $khata, ?PartnerInvestmentEntry $entry = null): array
    {
        $data = $request->validate([
            'partner_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', User::ROLE_PARTNER)),
            ],
            'investment_amount' => ['required', 'numeric', 'min:0'],
            'wallet_used_amount' => ['nullable', 'numeric', 'min:0'],
            'on_hold_amount' => ['nullable', 'numeric', 'min:0'],
            'investment_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'product_ids' => ['required', 'array', 'min:1'],
            'product_ids.*' => ['nullable', 'integer', Rule::exists('products', 'id')],
            'profit_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        [$entryData, $assignments] = $this->validatedEntryPayload($data, $khata, $entry);
        $this->ensureWalletAvailability(
            (int) $entryData['partner_id'],
            round((float) ($data['wallet_used_amount'] ?? 0), 2),
            $entry?->id
        );
        $this->ensurePercentLimits((int) $entryData['partner_id'], $assignments);

        return [$entryData, $assignments];
    }

    private function validatedProductEntriesData(Request $request, Khata $khata): array
    {
        $data = $request->validate([
            'investment_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'product_ids' => ['required', 'array', 'min:1'],
            'product_ids.*' => ['nullable', 'integer', Rule::exists('products', 'id')],
            'profit_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'members' => ['required', 'array', 'min:1'],
            'members.*.partner_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', User::ROLE_PARTNER)),
            ],
            'members.*.investment_amount' => ['nullable', 'numeric', 'min:0'],
            'members.*.wallet_used_amount' => ['nullable', 'numeric', 'min:0'],
            'members.*.on_hold_amount' => ['nullable', 'numeric', 'min:0'],
            'members.*.profit_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $productIds = $this->validatedProductIds($data['product_ids'] ?? [], 'product_ids');

        $members = collect($data['members'] ?? [])
            ->filter(fn (array $row) => filled($row['partner_id'] ?? null))
            ->values();

        if ($members->isEmpty()) {
            throw ValidationException::withMessages([
                'members' => 'Kam az kam aik member add karein.',
            ]);
        }

        if ($members->pluck('partner_id')->duplicates()->isNotEmpty()) {
            throw ValidationException::withMessages([
                'members' => 'Aik save me same member ko sirf aik martaba add karein.',
            ]);
        }

        $entriesPayload = $members->map(function (array $memberRow) use ($data, $khata, $productIds) {
            $amount = round((float) ($memberRow['investment_amount'] ?? 0), 2);
            $walletUsedAmount = round((float) ($memberRow['wallet_used_amount'] ?? 0), 2);
            $onHoldAmount = round((float) ($memberRow['on_hold_amount'] ?? 0), 2);

            if ($onHoldAmount > $amount) {
                throw ValidationException::withMessages([
                    'members' => 'On hold amount member amount se zyada nahi ho sakti.',
                ]);
            }

            if ($amount <= 0 && $walletUsedAmount <= 0) {
                throw ValidationException::withMessages([
                    'members' => 'Har member row me new amount ya use balance me se kam az kam aik value deni hogi.',
                ]);
            }

            $assignments = $this->buildDistributedAssignments(
                $productIds,
                $amount,
                $onHoldAmount,
                $walletUsedAmount
            );

            $assignments = $this->withResolvedProfitRatios(
                $assignments,
                (int) $memberRow['partner_id'],
                null,
                $this->normalizedProfitPercent($memberRow['profit_percent'] ?? null)
            )->map(function (array $assignment) {
                unset($assignment['profit_value_provided'], $assignment['suggested_profit_value']);

                return $assignment;
            })->values();

            return [[
                'khata_id' => $khata->id,
                'partner_id' => (int) $memberRow['partner_id'],
                'investment_date' => $data['investment_date'],
                'total_amount' => $amount,
                'notes' => $data['notes'] ?? null,
            ], $assignments];
        })->values();

        $flattenedRows = $entriesPayload
            ->flatMap(function (array $payload) {
                [$entryData, $assignments] = $payload;

                return $assignments->map(fn (array $assignment) => $assignment + [
                    'partner_id' => (int) $entryData['partner_id'],
                ]);
            })
            ->values();

        $this->ensureBatchWalletAvailability($flattenedRows);
        $this->ensureWalletUsedWithinActive($flattenedRows, 'members');

        $this->ensureBatchPercentAndCapitalLimits($entriesPayload);

        return [
            $entriesPayload,
            $entriesPayload->map(fn (array $payload) => (int) $payload[0]['partner_id'])->unique()->values()->all(),
        ];
    }

    private function validatedEntryPayload(array $data, Khata $khata, ?PartnerInvestmentEntry $entry = null): array
    {
        $amount = round((float) ($data['investment_amount'] ?? 0), 2);
        $walletUsedAmount = round((float) ($data['wallet_used_amount'] ?? 0), 2);
        $onHoldAmount = round((float) ($data['on_hold_amount'] ?? 0), 2);

        if ($onHoldAmount > $amount) {
            throw ValidationException::withMessages([
                'on_hold_amount' => 'On hold amount investment amount se zyada nahi ho sakti.',
            ]);
        }

        if ($amount <= 0 && $walletUsedAmount <= 0) {
            throw ValidationException::withMessages([
                'investment_amount' => 'New amount ya use balance me se kam az kam aik value deni hogi.',
            ]);
        }

        $assignments = $this->buildDistributedAssignments(
            $this->validatedProductIds($data['product_ids'] ?? [], 'product_ids'),
            $amount,
            $onHoldAmount,
            $walletUsedAmount
        );

        $this->ensureWalletUsedWithinActive($assignments, 'product_ids');

        $assignments = $this->withResolvedProfitRatios(
            $assignments,
            (int) $data['partner_id'],
            $entry?->id,
            $this->normalizedProfitPercent($data['profit_percent'] ?? null)
        );

        return [[
            'khata_id' => $khata->id,
            'partner_id' => (int) $data['partner_id'],
            'investment_date' => $data['investment_date'],
            'total_amount' => round((float) $data['investment_amount'], 2),
            'notes' => $data['notes'] ?? null,
        ], $assignments->map(function (array $assignment) {
            unset($assignment['profit_value_provided'], $assignment['suggested_profit_value']);

            return $assignment;
        })];
    }

    private function validatedProductIds(array $productIds, string $fieldKey): Collection
    {
        $selectedProductIds = collect($productIds)
            ->filter(fn ($productId) => filled($productId))
            ->map(fn ($productId) => (int) $productId)
            ->values();

        if ($selectedProductIds->isEmpty()) {
            throw ValidationException::withMessages([
                $fieldKey => 'Kam az kam aik product select karein.',
            ]);
        }

        if ($selectedProductIds->duplicates()->isNotEmpty()) {
            throw ValidationException::withMessages([
                $fieldKey => 'Aik hi product ko sirf aik martaba select karein.',
            ]);
        }

        return $selectedProductIds->values();
    }

    private function buildDistributedAssignments(
        Collection $productIds,
        float $amount,
        float $onHoldAmount = 0,
        float $walletUsedAmount = 0
    ): Collection {
        $distributedAmounts = $this->distributedValues($amount, $productIds->count());
        $distributedOnHold = $this->distributedValues($onHoldAmount, $productIds->count());
        $distributedWallet = $this->distributedValues($walletUsedAmount, $productIds->count());

        return $productIds->values()->map(function (int $productId, int $index) use ($distributedAmounts, $distributedOnHold, $distributedWallet) {
            return [
                'product_id' => $productId,
                'amount' => $distributedAmounts[$index] ?? 0,
                'on_hold_amount' => $distributedOnHold[$index] ?? 0,
                'wallet_used_amount' => $distributedWallet[$index] ?? 0,
                'profit_type' => 'percent',
                'profit_value' => 0,
                'profit_value_provided' => false,
            ];
        })->values();
    }

    private function distributedValues(float $total, int $count): array
    {
        $count = max(0, $count);
        $total = round(max(0, $total), 2);

        if ($count === 0) {
            return [];
        }

        if ($count === 1) {
            return [$total];
        }

        $baseAmount = round($total / $count, 2);
        $runningAmount = 0.0;
        $values = [];

        for ($index = 0; $index < $count; $index++) {
            $value = $index === ($count - 1)
                ? round($total - $runningAmount, 2)
                : $baseAmount;

            if ($index !== ($count - 1)) {
                $runningAmount += $value;
            }

            $values[] = max(0, $value);
        }

        return $values;
    }

    private function ensureBatchPercentAndCapitalLimits(Collection $entriesPayload): void
    {
        $plannedRows = $entriesPayload
            ->flatMap(function (array $payload) {
                [$entryData, $assignments] = $payload;

                return $assignments->map(function (array $assignment) use ($entryData) {
                    return [
                        'product_id' => (int) $assignment['product_id'],
                        'partner_id' => (int) $entryData['partner_id'],
                        'active_amount' => $this->activeAmountFromRow($assignment),
                        'profit_value' => (float) $assignment['profit_value'],
                    ];
                });
            })
            ->groupBy('product_id');

        foreach ($plannedRows as $productId => $rows) {
            $productCapital = $this->productCapitalAmount((int) $productId);
            $existingInvestments = ProductPartnerInvestment::query()
                ->where('product_id', $productId)
                ->get();
            $replacedPartnerIds = $rows->pluck('partner_id')->unique();
            $otherActive = (float) $existingInvestments
                ->reject(fn (ProductPartnerInvestment $investment) => $replacedPartnerIds->contains((int) $investment->partner_id))
                ->sum(fn (ProductPartnerInvestment $investment) => max(0, (float) $investment->amount - (float) $investment->on_hold_amount));
            $otherPercent = (float) $existingInvestments
                ->reject(fn (ProductPartnerInvestment $investment) => $replacedPartnerIds->contains((int) $investment->partner_id))
                ->where('profit_type', 'percent')
                ->sum(fn (ProductPartnerInvestment $investment) => (float) $investment->profit_value / 2);
            $currentActive = (float) $rows->sum('active_amount');
            $currentPercent = (float) $rows->sum(fn (array $row) => (float) $row['profit_value'] / 2);

            if (round($otherPercent + $currentPercent, 2) > 100) {
                throw ValidationException::withMessages([
                    'members' => 'Kisi bhi product ka total user percent 100 se zyada nahi ho sakta.',
                ]);
            }

            if ($productCapital > 0 && round($otherActive + $currentActive, 2) > round($productCapital, 2)) {
                throw ValidationException::withMessages([
                    'members' => 'Product investment total product capital se zyada nahi ho sakti. Baqi share admin ka rehta hai.',
                ]);
            }
        }
    }

    private function ensureWalletUsedWithinActive(Collection $rows, string $fieldKey): void
    {
        foreach ($rows as $row) {
            $activeAmount = $this->activeAmountFromRow($row);

            if ((float) ($row['wallet_used_amount'] ?? 0) > $activeAmount) {
                throw ValidationException::withMessages([
                    $fieldKey => 'Use balance active amount se zyada nahi ho sakti.',
                ]);
            }
        }
    }

    private function withResolvedProfitRatios(
        Collection $assignments,
        int $partnerId,
        ?int $entryId = null,
        ?float $requestedProfitPercent = null
    ): Collection
    {
        $suggestedProfitValue = $this->suggestedGroupedProfitRatio($assignments, $partnerId, $entryId);
        $count = $assignments->count();

        $perProductRawProfitValues = null;
        $isManualRequested = $requestedProfitPercent !== null;

        if ($isManualRequested && $count > 0) {
            $perProductRawProfitValues = $this->distributedValues(
                round((float) $requestedProfitPercent * 2, 2),
                $count
            );
        }

        return $assignments->values()->map(function (array $assignment, int $index) use (
            $suggestedProfitValue,
            $requestedProfitPercent,
            $perProductRawProfitValues,
            $isManualRequested
        ) {
            $assignment['suggested_profit_value'] = $suggestedProfitValue;

            if (! $isManualRequested) {
                $assignment['profit_value'] = $suggestedProfitValue;
                $assignment['is_profit_manual'] = false;
                $assignment['profit_value_provided'] = false;

                return $assignment;
            }

            $perProductRaw = (float) ($perProductRawProfitValues[$index] ?? 0);
            $assignment['profit_value'] = $perProductRaw;
            $assignment['is_profit_manual'] = true;
            $assignment['profit_value_provided'] = true;

            return $assignment;
        });
    }

    private function ensurePercentLimits(int $partnerId, Collection $assignments): void
    {
        $percentProducts = $assignments->groupBy('product_id');

        foreach ($percentProducts as $productId => $rows) {
            $productCapital = $this->productCapitalAmount((int) $productId);
            $otherActive = (float) ProductPartnerInvestment::query()
                ->where('product_id', $productId)
                ->where('partner_id', '!=', $partnerId)
                ->get()
                ->sum(fn (ProductPartnerInvestment $investment) => max(0, (float) $investment->amount - (float) $investment->on_hold_amount));
            $currentActive = (float) $rows->sum(fn (array $row) => $this->activeAmountFromRow($row));
            $otherPercent = (float) ProductPartnerInvestment::query()
                ->where('product_id', $productId)
                ->where('partner_id', '!=', $partnerId)
                ->where('profit_type', 'percent')
                ->get()
                ->sum(fn (ProductPartnerInvestment $investment) => (float) $investment->profit_value / 2);

            $currentPercent = (float) $rows->sum(fn (array $row) => (float) $row['profit_value'] / 2);

            if (round($otherPercent + $currentPercent, 2) > 100) {
                throw ValidationException::withMessages([
                    'assignments' => 'Kisi bhi product ka total user percent 100 se zyada nahi ho sakta.',
                ]);
            }

            if ($productCapital > 0 && round($otherActive + $currentActive, 2) > round($productCapital, 2)) {
                throw ValidationException::withMessages([
                    'assignments' => 'Product investment total product capital se zyada nahi ho sakti. Baqi share admin ka rehta hai.',
                ]);
            }
        }
    }

    private function suggestedGroupedProfitRatio(Collection $assignments, int $partnerId, ?int $entryId = null): float
    {
        $selectedProductIds = $assignments->pluck('product_id')->map(fn ($id) => (int) $id)->unique()->values();
        $selectedProductCapital = round((float) $selectedProductIds->sum(fn (int $productId) => $this->productCapitalAmount($productId)), 2);

        if ($selectedProductCapital <= 0) {
            return 0;
        }

        $existingPartnerActive = (float) $selectedProductIds->sum(function (int $productId) use ($partnerId, $entryId) {
            return $this->existingPartnerActiveForProduct($productId, $partnerId, $entryId);
        });
        $newActiveAmount = (float) $assignments->sum(fn (array $assignment) => $this->activeAmountFromRow($assignment));
        $partnerAfter = max(0, $existingPartnerActive + $newActiveAmount);

        return round(min(100, ($partnerAfter / $selectedProductCapital) * 100), 2);
    }

    private function existingPartnerActiveForProduct(int $productId, int $partnerId, ?int $entryId = null): float
    {
        $currentPartner = ProductPartnerInvestment::query()
            ->where('product_id', $productId)
            ->where('partner_id', $partnerId)
            ->first();

        $existingPartnerActive = max(
            0,
            (float) ($currentPartner?->amount ?? 0) - (float) ($currentPartner?->on_hold_amount ?? 0)
        );

        if (! $entryId) {
            return $existingPartnerActive;
        }

        $currentEntryItem = PartnerInvestmentEntry::query()
            ->with('items')
            ->find($entryId)?->items
            ->firstWhere('product_id', $productId);

        if (! $currentEntryItem) {
            return $existingPartnerActive;
        }

        return max(0, $existingPartnerActive - $this->activeAmountFromItem($currentEntryItem));
    }

    private function activeAmountFromRow(array $row): float
    {
        return max(
            0,
            round(
                (float) ($row['amount'] ?? 0)
                - (float) ($row['on_hold_amount'] ?? 0),
                2
            )
        );
    }

    private function activeAmountFromItem(PartnerInvestmentEntryItem $item): float
    {
        return $this->activeAmountFromRow([
            'amount' => (float) $item->amount,
            'on_hold_amount' => (float) $item->on_hold_amount,
            'wallet_used_amount' => (float) ($item->wallet_used_amount ?? 0),
        ]);
    }

    private function availableWalletBalance(int $partnerId, ?int $excludeEntryId = null): float
    {
        $items = PartnerInvestmentEntryItem::query()
            ->whereHas('entry', function ($query) use ($partnerId, $excludeEntryId) {
                $query->where('partner_id', $partnerId);

                if ($excludeEntryId) {
                    $query->where('id', '!=', $excludeEntryId);
                }
            })
            ->get();

        return round(max(
            0,
            (float) $items->sum('on_hold_amount') - (float) $items->sum('wallet_used_amount')
        ), 2);
    }

    private function ensureWalletAvailability(int $partnerId, float $walletUsedAmount, ?int $excludeEntryId = null): void
    {
        if ($walletUsedAmount <= 0) {
            return;
        }

        $availableBalance = $this->availableWalletBalance($partnerId, $excludeEntryId);

        if (round($walletUsedAmount, 2) > round($availableBalance, 2)) {
            throw ValidationException::withMessages([
                'assignments' => 'Use balance partner ki available hold balance se zyada nahi ho sakti.',
            ]);
        }
    }

    private function ensureBatchWalletAvailability(Collection $rows): void
    {
        $rows->groupBy('partner_id')->each(function (Collection $partnerRows, int $partnerId) {
            $walletUsedAmount = round((float) $partnerRows->sum('wallet_used_amount'), 2);

            if ($walletUsedAmount <= 0) {
                return;
            }

            $availableBalance = $this->availableWalletBalance($partnerId);

            if ($walletUsedAmount > round($availableBalance, 2)) {
                throw ValidationException::withMessages([
                    'products' => 'Use balance partner ki available hold balance se zyada nahi ho sakti.',
                ]);
            }
        });
    }

    private function memberSummaries(Collection $entries): Collection
    {
        return $entries
            ->groupBy('partner_id')
            ->map(function (Collection $partnerEntries) {
                $partner = $partnerEntries->first()?->partner;
                $totalAmount = (float) $partnerEntries->sum('total_amount');
                $items = $partnerEntries->flatMap->items;
                $availableBalance = max(0, (float) $items->sum('on_hold_amount') - (float) $items->sum('wallet_used_amount'));
                $activeAmount = (float) $items->sum(fn ($item) => $this->activeAmountFromItem($item));

                return [
                    'partner_name' => $partner?->name ?: 'Partner',
                    'entries_count' => $partnerEntries->count(),
                    'total_amount' => round($totalAmount, 2),
                    'on_hold_amount' => round($availableBalance, 2),
                    'active_amount' => round($activeAmount, 2),
                ];
            })
            ->sortByDesc('active_amount')
            ->values();
    }

    private function overview(Collection $entries): array
    {
        $totalAmount = (float) $entries->sum('total_amount');
        $items = $entries->flatMap->items;
        $availableBalance = max(0, (float) $items->sum('on_hold_amount') - (float) $items->sum('wallet_used_amount'));
        $activeAmount = (float) $items->sum(fn ($item) => $this->activeAmountFromItem($item));

        return [
            'members_count' => $entries->pluck('partner_id')->filter()->unique()->count(),
            'entries_count' => $entries->count(),
            'total_amount' => round($totalAmount, 2),
            'on_hold_amount' => round($availableBalance, 2),
            'active_amount' => round($activeAmount, 2),
        ];
    }

    private function productSnapshots(): array
    {
        $investmentsByProduct = ProductPartnerInvestment::query()
            ->get()
            ->groupBy('product_id');

        return Product::query()
            ->withCount('availableUnits')
            ->where('status', 'active')
            ->get(['id', 'purchase_price', 'stock_quantity', 'is_serialized'])
            ->mapWithKeys(function (Product $product) use ($investmentsByProduct) {
                $investments = $investmentsByProduct->get($product->id, collect());
                $activeAmount = round((float) $investments->sum(function (ProductPartnerInvestment $investment) {
                    return max(0, (float) $investment->amount - (float) $investment->on_hold_amount);
                }), 2);
                $productCapital = $this->productCapitalFromProduct($product);

                return [$product->id => [
                    'product_capital' => $productCapital,
                    'active_amount' => $activeAmount,
                    'admin_active_amount' => round(max(0, $productCapital - $activeAmount), 2),
                    'used_ownership_percent' => round((float) $investments->sum('ownership_percentage'), 2),
                    'used_percent' => round((float) $investments
                        ->where('profit_type', 'percent')
                        ->sum(fn (ProductPartnerInvestment $investment) => (float) $investment->profit_value / 2), 2),
                    'admin_user_id' => self::DEFAULT_ADMIN_ID,
                    'admin_name' => $this->adminDisplayName(),
                    'partners' => $investments->mapWithKeys(function (ProductPartnerInvestment $investment) {
                        $suggestedRawProfitValue = round((float) $investment->ownership_percentage, 2);

                        return [(string) $investment->partner_id => [
                            'active_amount' => round(max(0, (float) $investment->amount - (float) $investment->on_hold_amount), 2),
                            'ownership_percentage' => (float) $investment->ownership_percentage,
                            'profit_value' => round((float) $investment->profit_value / 2, 2),
                            'raw_profit_value' => (float) $investment->profit_value,
                            'suggested_profit_value' => round($suggestedRawProfitValue / 2, 2),
                            'suggested_raw_profit_value' => $suggestedRawProfitValue,
                            'is_profit_manual' => (bool) $investment->is_profit_manual,
                        ]];
                    })->all(),
                ]];
            })
            ->all();
    }

    private function productStructures(): Collection
    {
        return Product::query()
            ->withCount('availableUnits')
            ->with(['partnerInvestments.partner'])
            ->where('status', 'active')
            ->orderBy('name')
            ->get()
            ->map(function (Product $product) {
                $partners = $product->partnerInvestments
                    ->sortBy('id')
                    ->map(function (ProductPartnerInvestment $investment) {
                        $activeAmount = max(0, (float) $investment->amount - (float) $investment->on_hold_amount);
                        $suggestedRawProfitValue = round((float) $investment->ownership_percentage, 2);

                        return [
                            'id' => (int) $investment->id,
                            'name' => $investment->partner?->name ?: 'Partner',
                            'amount' => round((float) $investment->amount, 2),
                            'on_hold_amount' => round((float) $investment->on_hold_amount, 2),
                            'active_amount' => round($activeAmount, 2),
                            'ownership_percent' => round((float) $investment->ownership_percentage, 2),
                            'profit_percent' => round((float) $investment->profit_value / 2, 2),
                            'entered_profit_percent' => round((float) $investment->profit_value, 2),
                            'suggested_profit_percent' => round($suggestedRawProfitValue / 2, 2),
                            'suggested_raw_profit_percent' => $suggestedRawProfitValue,
                            'is_profit_manual' => (bool) $investment->is_profit_manual,
                        ];
                    })
                    ->values();

                $productCapital = $this->productCapitalFromProduct($product);
                $partnerActive = round((float) $partners->sum('active_amount'), 2);
                $partnerOwnership = round((float) $partners->sum('ownership_percent'), 2);
                $partnerPercent = round((float) $partners->sum('profit_percent'), 2);

                return [
                    'name' => $product->name,
                    'capital' => $productCapital,
                    'partners' => $partners,
                    'admin_user_id' => self::DEFAULT_ADMIN_ID,
                    'admin_name' => $this->adminDisplayName(),
                    'admin_active_amount' => round(max(0, $productCapital - $partnerActive), 2),
                    'admin_ownership_percent' => round(max(0, 100 - $partnerOwnership), 2),
                    'admin_profit_percent' => round(max(0, 100 - $partnerPercent), 2),
                ];
            });
    }

    private function partnerWallets(): array
    {
        return User::query()
            ->where('role', User::ROLE_PARTNER)
            ->get(['id'])
            ->mapWithKeys(fn (User $partner) => [$partner->id => [
                'available_balance' => $this->availableWalletBalance((int) $partner->id),
            ]])
            ->all();
    }

    private function productCapitalAmount(int $productId): float
    {
        $product = Product::query()
            ->withCount('availableUnits')
            ->find($productId, ['id', 'purchase_price', 'stock_quantity', 'is_serialized']);

        return $this->productCapitalFromProduct($product);
    }

    private function productCapitalFromProduct(?Product $product): float
    {
        if (! $product) {
            return 0;
        }

        $availableQuantity = $product->is_serialized
            ? (int) ($product->available_units_count ?? 0)
            : (int) $product->stock_quantity;

        return round(max(0, $availableQuantity * (float) $product->unit_purchase_price), 2);
    }

    private function normalizedProfitPercent(mixed $profitPercent): ?float
    {
        if (! is_numeric($profitPercent)) {
            return null;
        }

        return round(max(0, min(100, (float) $profitPercent)), 2);
    }

    private function persistableAssignments(Collection $assignments): Collection
    {
        return $assignments->map(function (array $assignment) {
            unset($assignment['profit_value_provided'], $assignment['suggested_profit_value'], $assignment['is_profit_manual']);

            return $assignment;
        })->values();
    }

    private function applyAssignmentProfitModes(Collection $rows): void
    {
        $autoProductIds = collect();

        $rows->each(function (array $row) use ($autoProductIds) {
            $investment = ProductPartnerInvestment::query()
                ->where('partner_id', (int) $row['partner_id'])
                ->where('product_id', (int) $row['product_id'])
                ->first();

            if (! $investment) {
                return;
            }

            if ((bool) ($row['is_profit_manual'] ?? false)) {
                $investment->update([
                    'profit_type' => 'percent',
                    'profit_value' => round((float) $row['profit_value'], 2),
                    'is_profit_manual' => true,
                ]);

                return;
            }

            $investment->update([
                'profit_type' => 'percent',
                'is_profit_manual' => false,
            ]);

            $autoProductIds->push((int) $investment->product_id);
        });

        $autoProductIds
            ->unique()
            ->values()
            ->each(fn (int $productId) => PartnerInvestmentSyncService::syncProductAssignments($productId));
    }

    private function ensureManualProductProfitLimit(ProductPartnerInvestment $investment, float $profitPercent): void
    {
        $otherPercent = (float) ProductPartnerInvestment::query()
            ->where('product_id', $investment->product_id)
            ->whereKeyNot($investment->id)
            ->where('profit_type', 'percent')
            ->get()
            ->sum(fn (ProductPartnerInvestment $row) => (float) $row->profit_value / 2);

        if (round($otherPercent + $profitPercent, 2) > 100) {
            throw ValidationException::withMessages([
                'product_profit_percent' => 'Is product ka total partner profit 100% se zyada nahi ho sakta.',
            ]);
        }
    }

    private function adminDisplayName(): string
    {
        return User::query()->whereKey(self::DEFAULT_ADMIN_ID)->value('name') ?: 'Admin';
    }
}
