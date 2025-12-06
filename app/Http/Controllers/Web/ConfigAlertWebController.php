<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ConfigAlert;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Gate;

class ConfigAlertWebController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('viewAny', ConfigAlert::class);

        return Inertia::render('panel/ConfigAlert/indexConfigAlert');
    }
}
