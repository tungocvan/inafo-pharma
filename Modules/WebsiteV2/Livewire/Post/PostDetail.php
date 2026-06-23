<?php

namespace Modules\WebsiteV2\Livewire\Post;

use Livewire\Component;
use Modules\Post\Models\Post;

class PostDetail extends Component
{
    public $post;

    public $relatedPosts;

    public int $readingTime = 1;

    public function mount(string $slug): void
    {
        $this->post = Post::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->with(['categories', 'user', 'tags'])
            ->firstOrFail();

        $this->post->increment('views');

        $wordCount = str_word_count(strip_tags((string) $this->post->content));
        $this->readingTime = max(1, (int) ceil($wordCount / 200));

        $this->relatedPosts = collect();

        if ($this->post->categories->isNotEmpty()) {
            $categoryIds = $this->post->categories->pluck('id');

            $this->relatedPosts = Post::query()
                ->where('status', 'published')
                ->where('id', '!=', $this->post->id)
                ->whereHas('categories', fn ($query) => $query->whereIn('id', $categoryIds))
                ->latest('published_at')
                ->take(3)
                ->get();
        }
    }

    public function render()
    {
        return view('website-v2::livewire.post.post-detail');
    }
}
