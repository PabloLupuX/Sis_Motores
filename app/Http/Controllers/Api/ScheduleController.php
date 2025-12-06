<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Exports\ScheduleExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\Horario\StoreScheduleRequest;
use App\Http\Requests\Horario\UpdateScheduleRequest;
use App\Http\Resources\ScheduleResource;
use App\Models\Schedule;
use Illuminate\Pipeline\Pipeline;
use App\Pipelines\FilterById;
use App\Pipelines\FilterBySearch;
use App\Pipelines\FilterByState;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Schedule::class);
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search');

        $query = app(Pipeline::class)
            ->send(Schedule::query())
            ->through([
                new FilterBySearch($search),
                new FilterByState($request->input('state')),
            ])
            ->thenReturn()
            ->orderBy('created_at', 'desc');

        return ScheduleResource::collection($query->paginate($perPage));
    }

    public function store(StoreScheduleRequest $request)
    {
        Gate::authorize('create', Schedule::class);
        $schedule = Schedule::create($request->validated());

        return response()->json([
            'state' => true,
            'message'  => 'Horario registrado correctamente.',
            'schedule' => new ScheduleResource($schedule),
        ]);
    }

    public function show(Schedule $schedule)
    {
        Gate::authorize('view', $schedule);
        return response()->json([
            'state' => true,
            'message'  => 'Horario encontrado.',
            'schedule' => new ScheduleResource($schedule),
        ]);
    }

    public function update(UpdateScheduleRequest $request, Schedule $schedule)
    {
        Gate::authorize('update', $schedule);
        $schedule->update($request->validated());

        return response()->json([
            'state' => true,
            'message'  => 'Horario actualizado correctamente.',
            'schedule' => new ScheduleResource($schedule->refresh()),
        ]);
    }

    public function destroy(Schedule $schedule)
    {
        Gate::authorize('delete', $schedule);
        $schedule->delete();

        return response()->json([
            'state' => true,
            'message' => 'Horario eliminado correctamente.',
        ]);
    }
    # EXPORTACION
    public function exportExcel()
    {
        set_time_limit(0);
        return Excel::download(new ScheduleExport, 'Horarios.xlsx');
    }
}
