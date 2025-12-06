<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Gate;

class ScheduleWebController extends Controller
{
    public function index(): Response
    {
        // Autorizar que el usuario pueda ver cualquier horario
        Gate::authorize('viewAny', Schedule::class);

        // Renderiza la vista de Inertia para horarios
        return Inertia::render('panel/Schedule/indexSchedule');
    }
}
