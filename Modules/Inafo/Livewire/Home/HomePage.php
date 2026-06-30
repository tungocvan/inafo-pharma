<?php

namespace Modules\Inafo\Livewire\Home;

use Livewire\Component;
use Modules\Inafo\Services\HomePageService;

class HomePage extends Component
{
    public string $search = '';

    protected HomePageService $service;

    public function boot(HomePageService $service): void
    {
        $this->service = $service;
    }

    public function submitSearch(): void
    {
        $query = trim($this->search);

        if ($query === '') {
            return;
        }

        $prefix = trim((string) config('inafo.inafo.route_prefix', 'inafo'), '/');
        $path = ($prefix === '' ? '' : '/' . $prefix) . '/search?q=' . urlencode($query);

        $this->redirect($path, navigate: true);
    }

    public function render()
    {
        return view('inafo::livewire.home.home-page', [
            'home' => $this->service->getHomePayload(),
        ]);
    }
}
