<?php

namespace Modules\WebsiteV2\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ProductService
{
    public function featured(array $ids = [], int $limit = 8)
    {
        $model = $this->productModel();

        if (! $model) {
            return collect();
        }

        $query = $model::query();

        if ($ids !== []) {
            $query->whereIn('id', $ids);
        }

        return $query->latest()->limit($limit)->get();
    }

    public function newArrivals(int $limit = 8)
    {
        $model = $this->productModel();

        return $model ? $model::query()->latest()->limit($limit)->get() : collect();
    }

    public function bestSellers(int $limit = 8)
    {
        $model = $this->productModel();

        return $model ? $model::query()->latest()->limit($limit)->get() : collect();
    }

    public function paginate(array $filters = [])
    {
        $model = $this->productModel();

        if (! $model) {
            return collect();
        }

        return $model::query()->latest()->paginate($filters['per_page'] ?? 12);
    }

    public function listing(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $model = $this->productModel();

        abort_if(! $model, 404);

        $query = $model::query()
            ->with('categories')
            ->when(method_exists($model, 'scopeActive'), fn (Builder $query) => $query->active(), fn (Builder $query) => $query->where('is_active', true));

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['selected_categories'])) {
            $categoryIds = array_filter((array) $filters['selected_categories']);
            $query->whereHas('categories', fn (Builder $query) => $query->whereIn('categories.id', $categoryIds));
        } elseif (! empty($filters['categorySlug'])) {
            $query->whereHas('categories', fn (Builder $query) => $query->where('categories.slug', $filters['categorySlug']));
        }

        if (! empty($filters['price_range'])) {
            $parts = explode('-', $filters['price_range']);
            if (count($parts) === 2) {
                $query->whereRaw('COALESCE(sale_price, regular_price) BETWEEN ? AND ?', [(int) $parts[0], (int) $parts[1]]);
            }
        }

        match ($filters['sort'] ?? 'latest') {
            'price_asc' => $query->orderByRaw('COALESCE(sale_price, regular_price) ASC'),
            'price_desc' => $query->orderByRaw('COALESCE(sale_price, regular_price) DESC'),
            'name_asc' => $query->orderBy('title'),
            default => $query->latest(),
        };

        return $query->paginate($perPage);
    }

    public function findBySlug(string $slug)
    {
        $model = $this->productModel();

        abort_if(! $model, 404);

        return $model::query()->where('slug', $slug)->firstOrFail();
    }

    private function productModel(): ?string
    {
        return class_exists(\Modules\Product\Models\Product::class)
            ? \Modules\Product\Models\Product::class
            : null;
    }
}
