<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Exports\AlertsExport;
use App\Http\Requests\Alerta\StoreAlertRequest;
use App\Http\Requests\Alerta\UpdateAlertRequest;
use App\Http\Resources\AlertResource;
use App\Models\Alert;
use Maatwebsite\Excel\Facades\Excel;
use App\Pipelines\FilterByAlert;
use App\Pipelines\FilterByTipo;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Gate;

class AlertController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Alert::class);

        $perPage = $request->input('per_page', 15);
        $search  = $request->input('search');
        $tipo    = $request->input('tipo');
        $fecha   = $request->input('fecha');

        $query = app(Pipeline::class)
            ->send(Alert::query())
            ->through([
                new FilterByAlert($search),
                new FilterByTipo($tipo, $fecha),
            ])
            ->thenReturn();
        return AlertResource::collection($query->paginate($perPage));
    }

    public function store(StoreAlertRequest $request)
    {
        Gate::authorize('create', Alert::class);

        $validated = $request->validated();
        $alert = Alert::create($validated);

        return response()->json([
            'message' => 'Alerta registrada correctamente.',
            'alert' => new AlertResource($alert)
        ]);
    }

    public function show(Alert $alert)
    {
        Gate::authorize('view', $alert);
        return response()->json([
            'message' => 'Alerta encontrada.',
            'alert' => new AlertResource($alert)
        ]);
    }

    public function update(UpdateAlertRequest $request, Alert $alert)
    {
        Gate::authorize('update', $alert);

        $validated = $request->validated();
        $alert->update($validated);

        return response()->json([
            'message' => 'Alerta actualizada correctamente.',
            'alert' => new AlertResource($alert->refresh())
        ]);
    }

    public function destroy(Alert $alert)
    {
        Gate::authorize('delete', $alert);

        $alert->delete();

        return response()->json([
            'message' => 'Alerta eliminada correctamente.'
        ]);
    }
    # EXPORTACION
    public function exportExcel()
    {
        set_time_limit(0);
        return Excel::download(new AlertsExport, 'Alertas.xlsx');
    }
}
