<?php

namespace App\Support;

use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Collection;

class SaleVisibilitySummary
{
    public static function applyToSales(Collection $sales): Collection
    {
        return $sales
            ->map(fn (Sale $sale) => self::applyToSale($sale))
            ->filter()
            ->values();
    }

    public static function applyToSale(Sale $sale): ?Sale
    {
        $visibleItems = self::visibleItems($sale->items);

        if ($visibleItems->isEmpty()) {
            return null;
        }

        $subtotal = round((float) $visibleItems->sum(fn (SaleItem $item) => (float) $item->total), 2);
        $discountAmount = $subtotal * ((float) $sale->discount_percent / 100);
        $grandTotal = round($subtotal - $discountAmount, 2);

        $sale->setRelation('items', $visibleItems);
        $sale->subtotal = $subtotal;
        $sale->tax_percent = 0;
        $sale->grand_total = $grandTotal;

        return $sale;
    }

    public static function visibleItems(Collection $items): Collection
    {
        return $items
            ->filter(fn ($item) => $item->product !== null)
            ->values();
    }
}
