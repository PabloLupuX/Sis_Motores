<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Gate;

class AlertWebController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('viewAny', Alert::class);
        return Inertia::render('panel/Alert/indexAlert');
    }
}
