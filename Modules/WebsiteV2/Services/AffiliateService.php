<?php

namespace Modules\WebsiteV2\Services;

class AffiliateService
{
    public function summaryForUser(int $userId): array
    {
        return ['user_id' => $userId, 'commission_total' => 0, 'orders' => 0];
    }
}
