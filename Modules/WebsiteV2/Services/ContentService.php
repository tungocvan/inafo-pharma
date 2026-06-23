<?php

namespace Modules\WebsiteV2\Services;

class ContentService
{
    public function latestPosts(int $limit = 3)
    {
        $model = $this->postModel();

        return $model ? $model::query()->latest()->limit($limit)->get() : collect();
    }

    public function paginatePosts(array $filters = [])
    {
        $model = $this->postModel();

        if (! $model) {
            return collect();
        }

        return $model::query()->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function findPostBySlug(string $slug)
    {
        $model = $this->postModel();

        abort_if(! $model, 404);

        return $model::query()->where('slug', $slug)->firstOrFail();
    }

    private function postModel(): ?string
    {
        return class_exists(\Modules\Post\Models\Post::class)
            ? \Modules\Post\Models\Post::class
            : null;
    }
}
