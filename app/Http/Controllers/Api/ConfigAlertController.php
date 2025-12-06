<?php

namespace App\Http\Controllers\Api;

use App\Exports\ConfigAlertExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Config_alerta\StoreAlertConfigRequest;
use App\Http\Requests\Config_alerta\UpdateAlertConfigRequest;
use App\Http\Resources\ConfigAlertResource;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\ConfigAlert;
use App\Pipelines\FilterByConfigAlert;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Gate;

class ConfigAlertController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', ConfigAlert::class);
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search');

        $query = app(Pipeline::class)
            ->send(ConfigAlert::query())
            ->through([
                new FilterByConfigAlert($search),
            ])
            ->thenReturn();

        return ConfigAlertResource::collection($query->paginate($perPage));
    }

    public function store(StoreAlertConfigRequest $request)
    {
        Gate::authorize('create', ConfigAlert::class);
        $validated = $request->validated();

        $configAlert = ConfigAlert::create($validated);

        return response()->json([
            'state' => true,
            'message' => 'Configuración de alerta registrada correctamente.',
            'configAlert' => new ConfigAlertResource($configAlert),
        ]);
    }

    public function show(ConfigAlert $configAlert)
    {
        Gate::authorize('view', $configAlert);

        return response()->json([
            'state' => true,
            'message' => 'Configuración de alerta encontrada.',
            'configAlert' => new ConfigAlertResource($configAlert),
        ]);
    }

    public function update(UpdateAlertConfigRequest $request, ConfigAlert $configAlert)
    {
        Gate::authorize('update', $configAlert);
        $validated = $request->validated();

        $configAlert->update($validated);

        return response()->json([
            'state' => true,
            'message' => 'Configuración de alerta actualizada correctamente.',
            'configAlert' => new ConfigAlertResource($configAlert->refresh()),
        ]);
    }

    public function destroy(ConfigAlert $configAlert)
    {
        Gate::authorize('delete', $configAlert);

        $configAlert->delete();

        return response()->json([
            'state' => true,
            'message' => 'Configuración de alerta eliminada correctamente.',
        ]);
    }

    # EXPORTACION
    public function exportExcel()
    {
        set_time_limit(0);
        return Excel::download(new ConfigAlertExport, 'ConfigAlertas.xlsx');
    }

    public function latest()
    {
        Gate::authorize('viewAny', ConfigAlert::class);

        $configAlert = ConfigAlert::latest()->first();

        return response()->json([
            'state' => true,
            'message' => $configAlert ? 'Última configuración encontrada.' : 'No hay configuraciones registradas.',
            'configAlert' => $configAlert ? new ConfigAlertResource($configAlert) : null,
        ]);
    }
}
