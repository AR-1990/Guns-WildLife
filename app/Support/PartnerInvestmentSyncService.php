<?php

namespace App\Support;

use App\Models\PartnerInvestmentEntryItem;
use App\Models\Product;
use App\Models\ProductPartnerInvestment;

class PartnerInvestmentSyncService
{
    public static function rebuildPartnerAssignments(int $partnerId): void
    {
        $existingAssignments = ProductPartnerInvestment::withTrashed()
            ->where('partner_id', $partnerId)
            ->get()
            ->keyBy('product_id');

        $existingProductIds = $existingAssignments->pluck('product_id');

        $aggregated = PartnerInvestmentEntryItem::query()
            ->select('product_id')
            ->selectRaw('SUM(amount - on_hold_amount) as total_active_amount')
            ->whereHas('entry', fn ($query) => $query->where('partner_id', $partnerId))
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        ProductPartnerInvestment::withTrashed()
            ->where('partner_id', $partnerId)
            ->forceDelete();

        foreach ($aggregated as $productId => $row) {
            $existingAssignment = $existingAssignments->get($productId);
            $latestItem = PartnerInvestmentEntryItem::query()
                ->with('entry')
                ->where('product_id', $productId)
                ->whereHas('entry', fn ($query) => $query->where('partner_id', $partnerId))
                ->latest('id')
                ->first();

            ProductPartnerInvestment::query()->create([
                'product_id' => $productId,
                'partner_id' => $partnerId,
                'amount' => max(0, round((float) $row->total_active_amount, 2)),
                'on_hold_amount' => 0,
                'investment_date' => $latestItem?->entry?->investment_date,
                'ownership_percentage' => 0,
                'profit_type' => $existingAssignment?->profit_type ?: ($latestItem?->profit_type ?: 'percent'),
                'profit_value' => round((float) ($existingAssignment?->profit_value ?? $latestItem?->profit_value ?? 0), 2),
                'is_profit_manual' => (bool) ($existingAssignment?->is_profit_manual ?? false),
            ]);
        }

        $productIds = $existingProductIds
            ->concat($aggregated->keys())
            ->unique()
            ->values();

        foreach ($productIds as $productId) {
            self::syncProductAssignments((int) $productId);
        }
    }

    public static function syncProductAssignments(int $productId): void
    {
        $product = Product::query()
            ->withCount('availableUnits')
            ->find($productId);

        if (! $product) {
            return;
        }

        $assignments = $product->partnerInvestments()->orderBy('id')->get();

        if ($assignments->isEmpty()) {
            $product->updateQuietly([
                'ownership_type' => 'company',
                'partner_id' => null,
            ]);

            return;
        }

        $availableQuantity = $product->is_serialized
            ? (int) ($product->available_units_count ?? 0)
            : (int) $product->stock_quantity;
        $productCapital = round(max(0, $availableQuantity * (float) $product->unit_purchase_price), 2);
        $totalActiveAmount = round((float) $assignments->sum(function (ProductPartnerInvestment $assignment) {
            return max(0, (float) $assignment->amount - (float) $assignment->on_hold_amount);
        }), 2);

        foreach ($assignments as $assignment) {
            $activeAmount = max(0, (float) $assignment->amount - (float) $assignment->on_hold_amount);
            $percentage = $productCapital > 0
                ? round(($activeAmount / $productCapital) * 100, 4)
                : 0;

            $payload = [
                'ownership_percentage' => max(0, min(100, $percentage)),
            ];

            if (! $assignment->is_profit_manual) {
                $payload['profit_type'] = 'percent';
                $payload['profit_value'] = round(max(0, min(100, $percentage)), 2);
            }

            $assignment->updateQuietly($payload);
        }

        $product->updateQuietly([
            'ownership_type' => 'partner',
            'partner_id' => $assignments->first()->partner_id,
        ]);
    }
}
