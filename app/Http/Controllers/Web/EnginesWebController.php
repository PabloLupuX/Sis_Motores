<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Engine;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Gate;

class EnginesWebController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('viewAny',Engine::class);
        return Inertia::render('panel/Engines/indexEngines');
    }
}
