<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Exports\EmployeesExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\Empleado\StoreEmployeeRequest;
use App\Http\Requests\Empleado\UpdateEmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use App\Pipelines\FilterByName;
use App\Pipelines\FilterByState;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Gate;

class EmployeeController extends Controller
{
    private $uploadPath = 'uploads/fotos/empleados';
    public function index(Request $request){
        Gate::authorize('viewAny', Employee::class);
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search');
        $query = app(Pipeline::class)
            ->send(Employee::query())
            ->through([
                new FilterByName($search),
                new FilterByState($request->input('state')),
            ])
            ->thenReturn();

        return EmployeeResource::collection($query->paginate($perPage));
    }

    public function store(StoreEmployeeRequest $request){
        Gate::authorize('create', Employee::class);
        $validated = $request->validated();
        if ($request->hasFile('foto')) {
            $folder = public_path($this->uploadPath);

            // Crear carpeta si no existe
            if (!file_exists($folder)) {
                mkdir($folder, 0777, true);
            }

            // Generar nombre único como empleado_########.ext
            $random = random_int(10000000, 99999999); 
            $extension = $request->file('foto')->getClientOriginalExtension();
            $fileName = 'empleado_' . $random . '.' . $extension;

            // Mover archivo
            $request->file('foto')->move(public_path($this->uploadPath), $fileName);

            // Guardar ruta relativa (para acceder desde frontend)
            $validated['foto'] = $fileName;
        }
        $employee = Employee::create($validated);
        return response()->json([
            'state' => true,
            'message' => 'Empleado registrado correctamente.',
            'employee' => new EmployeeResource($employee)
        ]);
    }
    public function show(Employee $employee){
        Gate::authorize('view', $employee);
        return response()->json([
            'state' => true,
            'message' => 'Empleado encontrado',
            'employee' => new EmployeeResource($employee)
        ]);
    }
        public function update(UpdateEmployeeRequest $request, Employee $employee){
        Gate::authorize('update', $employee);
        $validated = $request->validated();

        if ($request->hasFile('foto')) {
            $folder = public_path($this->uploadPath);

            // Crear carpeta si no existe
            if (!file_exists($folder)) {
                mkdir($folder, 0777, true);
            }

            // Eliminar foto anterior si existe
            $oldFoto = $employee->foto ? public_path($this->uploadPath . '/' . $employee->foto) : null;
            if ($oldFoto && file_exists($oldFoto)) {
                unlink($oldFoto);
            }

            // Generar nombre único como empleado_########.ext
            $random = random_int(10000000, 99999999); 
            $extension = $request->file('foto')->getClientOriginalExtension();
            $fileName = 'empleado_' . $random . '.' . $extension;

            // Mover archivo nuevo
            $request->file('foto')->move($folder, $fileName);

            // Guardar solo el nombre en BD
            $validated['foto'] = $fileName;
        }

        $employee->update($validated);

        return response()->json([
            'state' => true,
            'message' => 'Empleado actualizado correctamente.',
            'employee' => new EmployeeResource($employee->refresh())
        ]);
    }
    public function destroy(Employee $employee)
    {
        Gate::authorize('delete', $employee);

        // Primero validamos si tiene relaciones
        if ($employee->tieneRelaciones()) {
            return response()->json([
                'state'  => false,
                'message'=> 'No se puede eliminar este empleado porque tiene relaciones con otros registros.'
            ], 400);
        }

        // Construir la ruta completa de la foto
        $fotoPath = $employee->foto ? public_path($this->uploadPath . '/' . $employee->foto) : null;

        // Eliminar foto si existe
        if ($fotoPath && file_exists($fotoPath)) {
            unlink($fotoPath);
        }
        // Eliminar empleado
        $employee->delete();

        return response()->json([
            'state'   => true,
            'message' => 'Empleado eliminado correctamente'
        ]);
    }
    #EXPORTACION
    public function exportExcel()
    {
        set_time_limit(0);
        return Excel::download(new EmployeesExport, 'Empleados.xlsx');
    }
}
