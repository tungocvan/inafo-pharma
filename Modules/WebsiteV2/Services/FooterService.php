<?php

namespace Modules\WebsiteV2\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Modules\WebsiteV2\Models\FooterColumn;
use Modules\WebsiteV2\Models\SocialLink;

class FooterService
{
    public function getColumnsForFrontend(): Collection
    {
        return FooterColumn::query()
            ->active()
            ->with(['links' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();
    }

    public function getSocialLinks(): Collection
    {
        return SocialLink::query()->active()->orderBy('sort_order')->get();
    }

    public function reorderColumns(array $orderedIds): void
    {
        DB::transaction(function () use ($orderedIds) {
            foreach ($orderedIds as $index => $id) {
                FooterColumn::query()->whereKey($id)->update(['sort_order' => $index + 1]);
            }
        });
    }
}
