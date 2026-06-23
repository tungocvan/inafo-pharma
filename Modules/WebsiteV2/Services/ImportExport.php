<?php

namespace Modules\WebsiteV2\Services;

use Modules\WebsiteV2\Models\Coupon;

class ImportExport
{
    public function exportCoupons(): array
    {
        return Coupon::query()
            ->orderBy('code')
            ->get(['code', 'description', 'type', 'value', 'min_order_value', 'usage_limit', 'starts_at', 'expires_at', 'is_active'])
            ->toArray();
    }

    public function upsertCoupons(array $rows): int
    {
        $count = 0;

        foreach ($rows as $row) {
            Coupon::query()->updateOrCreate(
                ['code' => strtoupper(trim($row['code']))],
                [
                    'description' => $row['description'] ?? null,
                    'type' => $row['type'] ?? 'fixed',
                    'value' => $row['value'] ?? 0,
                    'min_order_value' => $row['min_order_value'] ?? 0,
                    'usage_limit' => $row['usage_limit'] ?? null,
                    'is_active' => $row['is_active'] ?? true,
                ]
            );
            $count++;
        }

        return $count;
    }
}
