<?php

namespace Modules\WebsiteV2\Livewire\Post;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Post\Models\Category;
use Modules\Post\Models\Post;

class PostList extends Component
{
    use WithPagination;

    public $categorySlug = null;

    public $currentCategory = null;

    private const STATIC_PAGE_SLUG = 'pages';

    public function mount(?string $categorySlug = null): void
    {
        $this->categorySlug = $categorySlug;

        if ($categorySlug) {
            $this->currentCategory = Category::query()
                ->where('slug', $categorySlug)
                ->where('type', 'post')
                ->first();
        }
    }

    public function render()
    {
        $categories = Category::query()
            ->where('type', 'post')
            ->where('slug', '!=', self::STATIC_PAGE_SLUG)
            ->where('is_active', true)
            ->withCount(['posts' => function ($query) {
                $query->where('status', 'published');
            }])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $postsQuery = Post::query()
            ->where('status', 'published')
            ->with(['categories', 'user'])
            ->latest('published_at');

        if ($this->categorySlug) {
            $postsQuery->whereHas('categories', function ($query) {
                $query->where('slug', $this->categorySlug);
            });
        } else {
            $postsQuery->whereDoesntHave('categories', function ($query) {
                $query->where('slug', self::STATIC_PAGE_SLUG);
            });
        }

        $heroPost = null;
        $currentPage = $this->getPage();

        if ((int) $currentPage === 1 && ! $this->categorySlug) {
            $heroPost = (clone $postsQuery)->first();
            $posts = (clone $postsQuery)
                ->when($heroPost, fn ($query) => $query->where('id', '!=', $heroPost->id))
                ->paginate(9);
        } else {
            $posts = $postsQuery->paginate(9);
        }

        return view('website-v2::livewire.post.post-list', [
            'categories' => $categories,
            'posts' => $posts,
            'heroPost' => $heroPost,
        ]);
    }
}
