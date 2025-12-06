<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Movement;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Gate;

class MovementWebController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('viewAny', Movement::class);
        return Inertia::render('panel/Movement/indexMovement');
    }
}
