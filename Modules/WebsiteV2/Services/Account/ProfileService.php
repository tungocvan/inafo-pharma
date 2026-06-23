<?php

namespace Modules\WebsiteV2\Services\Account;

class ProfileService
{
    public function current()
    {
        return auth()->user();
    }
}
