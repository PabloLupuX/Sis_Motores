<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Accessory;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Gate;

class AccessoriesWebController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('viewAny',arguments: Accessory::class);
        return Inertia::render('panel/Accessories/indexAccessories');
    }
}
