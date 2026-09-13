<?php

namespace App\Http\Controllers;

use App\Models\PartnerInvestmentEntry;
use App\Models\PartnerInvestmentEntryItem;
use App\Models\Product;
use App\Models\User;
use App\Support\PartnerAccountSummary;
use App\Support\PartnerInvestmentSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminPartnerAssignmentController extends Controller
{
    public function edit(Request $request, User $user): View
    {
        abort_unless($user->role === User::ROLE_PARTNER, 404);

        $entries = PartnerInvestmentEntry::query()
            ->with('items.product')
            ->where('partner_id', $user->id)
            ->orderByDesc('id')
            ->get();
        $editingEntry = $request->filled('entry')
            ? PartnerInvestmentEntry::query()
                ->with('items.product')
                ->where('partner_id', $user->id)
                ->findOrFail($request->integer('entry'))
            : null;

        return view('admin.accounts.partner-assignments', [
            'partner' => $user,
            'products' => Product::query()->where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'entries' => $entries,
            'editingEntry' => $editingEntry,
            'summary' => [
                'actual' => PartnerAccountSummary::totalActual($user->id),
                'earned' => PartnerAccountSummary::totalEarned($user->id),
                'deductions' => PartnerAccountSummary::totalDeductions($user->id),
                'available' => PartnerAccountSummary::availableBalance($user->id),
            ],
            'deductionLogs' => PartnerAccountSummary::deductionEntries($user->id),
        ]);
    }

    public function store(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->role === User::ROLE_PARTNER, 404);

        [$entryData, $assignments] = $this->validatedEntryData($request);

        DB::transaction(function () use ($request, $user, $entryData, $assignments) {
            $entry = PartnerInvestmentEntry::query()->create($entryData + [
                'partner_id' => $user->id,
                'created_by' => $request->user()->id,
            ]);

            $this->syncEntryItems($entry, $assignments);
            $this->rebuildPartnerAssignments($user->id);
        });

        return redirect()
            ->route('admin.accounts.users.assignments.edit', $user)
            ->with('status', 'Investment saved successfully.');
    }

    public function update(Request $request, User $user, PartnerInvestmentEntry $investmentEntry): RedirectResponse
    {
        abort_unless($user->role === User::ROLE_PARTNER && $investmentEntry->partner_id === $user->id, 404);

        [$entryData, $assignments] = $this->validatedEntryData($request, $user->id, $investmentEntry->id);

        DB::transaction(function () use ($investmentEntry, $entryData, $assignments, $user) {
            $investmentEntry->update($entryData);
            $this->syncEntryItems($investmentEntry, $assignments);
            $this->rebuildPartnerAssignments($user->id);
        });

        return redirect()
            ->route('admin.accounts.users.assignments.edit', $user)
            ->with('status', 'Investment updated successfully.');
    }

    public function destroy(User $user, PartnerInvestmentEntry $investmentEntry): RedirectResponse
    {
        abort_unless($user->role === User::ROLE_PARTNER && $investmentEntry->partner_id === $user->id, 404);

        DB::transaction(function () use ($user, $investmentEntry) {
            $investmentEntry->delete();
            $this->rebuildPartnerAssignments($user->id);
        });

        return redirect()
            ->route('admin.accounts.users.assignments.edit', $user)
            ->with('status', 'Investment deleted successfully.');
    }

    private function validatedEntryData(Request $request, ?int $partnerId = null, ?int $entryId = null): array
    {
        $data = $request->validate([
            'investment_amount' => ['required', 'numeric', 'min:0.01'],
            'investment_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'assignments' => ['nullable', 'array'],
            'assignments.*.product_id' => ['nullable', 'integer', Rule::exists('products', 'id')],
            'assignments.*.on_hold_amount' => ['nullable', 'numeric', 'min:0'],
            'assignments.*.profit_type' => ['nullable', Rule::in(['percent', 'fixed'])],
            'assignments.*.profit_value' => ['nullable', 'numeric', 'min:0'],
        ]);

        $assignments = collect($data['assignments'] ?? [])
            ->filter(fn (array $row) => filled($row['product_id'] ?? null))
            ->map(fn (array $row) => [
                'product_id' => (int) $row['product_id'],
                'on_hold_amount' => round((float) ($row['on_hold_amount'] ?? 0), 2),
                'profit_type' => $row['profit_type'] ?? 'percent',
                'profit_value' => round((float) ($row['profit_value'] ?? 0), 2),
            ])
            ->values();

        if ($assignments->isEmpty()) {
            throw ValidationException::withMessages([
                'assignments' => 'Kam az kam aik product select karein.',
            ]);
        }

        if ($assignments->pluck('product_id')->duplicates()->isNotEmpty()) {
            throw ValidationException::withMessages([
                'assignments' => 'Ek product ko ek partner ke liye sirf aik martaba assign karein.',
            ]);
        }

        $assignments = $this->withDistributedAmounts(
            $assignments,
            round((float) $data['investment_amount'], 2)
        );
        $this->ensureOnHoldAmounts($assignments);

        $this->ensurePercentLimits($partnerId, $assignments, $entryId);

        return [[
            'investment_date' => $data['investment_date'],
            'total_amount' => round((float) $data['investment_amount'], 2),
            'notes' => $data['notes'] ?? null,
        ], $assignments];
    }

    private function withDistributedAmounts(Collection $assignments, float $investmentAmount): Collection
    {
        $count = $assignments->count();
        $baseAmount = round($investmentAmount / $count, 2);
        $runningAmount = 0.0;
        $lastIndex = $assignments->keys()->last();

        return $assignments->map(function (array $assignment, int $index) use ($baseAmount, $investmentAmount, $lastIndex, &$runningAmount) {
            $amount = $index === $lastIndex
                ? round($investmentAmount - $runningAmount, 2)
                : $baseAmount;

            if ($index !== $lastIndex) {
                $runningAmount += $amount;
            }

            $assignment['amount'] = max(0, $amount);

            return $assignment;
        });
    }

    private function ensurePercentLimits(?int $partnerId, Collection $assignments, ?int $entryId = null): void
    {
        $percentProducts = $assignments
            ->where('profit_type', 'percent')
            ->groupBy('product_id');

        foreach ($percentProducts as $productId => $rows) {
            $otherPercent = (float) PartnerInvestmentEntryItem::query()
                ->where('product_id', $productId)
                ->where('profit_type', 'percent')
                ->whereHas('entry', function ($query) use ($partnerId, $entryId) {
                    $query->when($partnerId, fn ($nested) => $nested->where('partner_id', '!=', $partnerId));

                    if ($entryId) {
                        $query->where('id', '!=', $entryId);
                    }
                })
                ->sum('profit_value');

            $currentPercent = (float) $rows->sum('profit_value');

            if (round($otherPercent + $currentPercent, 2) > 100) {
                throw ValidationException::withMessages([
                    'assignments' => 'Kisi bhi product ka total percent profit 100 se zyada nahi ho sakta.',
                ]);
            }
        }
    }

    private function ensureOnHoldAmounts(Collection $assignments): void
    {
        foreach ($assignments as $assignment) {
            if ((float) $assignment['on_hold_amount'] > (float) $assignment['amount']) {
                throw ValidationException::withMessages([
                    'assignments' => 'On hold amount assigned amount se zyada nahi ho sakti.',
                ]);
            }
        }
    }

    private function syncEntryItems(PartnerInvestmentEntry $entry, Collection $assignments): void
    {
        $entry->items()->delete();

        foreach ($assignments as $assignment) {
            $entry->items()->create($assignment);
        }
    }

    private function rebuildPartnerAssignments(int $partnerId): void
    {
        PartnerInvestmentSyncService::rebuildPartnerAssignments($partnerId);
    }
}
