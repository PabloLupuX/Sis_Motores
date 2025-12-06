<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Space;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Gate;

class SpaceWebController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('viewAny', Space::class);
        return Inertia::render('panel/Space/indexSpace');
    }
}
