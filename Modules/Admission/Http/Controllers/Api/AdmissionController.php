<?php

namespace Modules\Admission\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdmissionController extends Controller
{
    public function index()
    {
        return response()->json([
            'message' => 'Admission API is not available yet.',
        ], 501);
    }
}
