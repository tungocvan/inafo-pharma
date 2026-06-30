<?php

namespace Modules\Inafo\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('inafo::pages.home.index', [
            'siteName' => config('inafo.inafo.brand_name', 'INAFO Pharma'),
        ]);
    }
}
