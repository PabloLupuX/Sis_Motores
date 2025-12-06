<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Exports\CustomersExport;
use Maatwebsite\Excel\Facades\Excel;
class CustomerController extends Controller
{
   public function index(Request $request)
{
    Gate::authorize('viewAny', Customer::class);

    $perPage = $request->input('per_page', 15);
    $search = $request->input('search');
    $state  = $request->input('state');

    $query = Customer::query();

    // Filtro de búsqueda general (case-insensitive)
    if ($search) {
        $search = strtolower($search);

        $query->where(function ($q) use ($search) {
            $q->whereRaw('LOWER(nombres) LIKE ?', ["%{$search}%"])
              ->orWhereRaw('LOWER(alias) LIKE ?', ["%{$search}%"])
              ->orWhereRaw('LOWER(telefono) LIKE ?', ["%{$search}%"])
              ->orWhereRaw('LOWER(codigo) LIKE ?', ["%{$search}%"]);
        });
    }

    // Filtro por estado
    if ($state !== null && $state !== '') {
        $query->where('state', $state);
    }
    // 👇 Ordenar SIEMPRE de menor a mayor por ID
    $query->orderBy('id', 'asc');
    return CustomerResource::collection($query->paginate($perPage));
}


    public function store(StoreCustomerRequest $request)
    {
        Gate::authorize('create', Customer::class);

        $customer = Customer::create($request->validated());

        return response()->json([
            'state' => true,
            'message' => 'Cliente registrado correctamente.',
            'customer' => new CustomerResource($customer),
        ]);
    }

    public function show(Customer $customer)
    {
        Gate::authorize('view', $customer);

        return response()->json([
            'state'   => true,
            'message' => 'Cliente encontrado',
            'customer' => new CustomerResource($customer),
        ]);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        Gate::authorize('update', $customer);

        $customer->update($request->validated());

        return response()->json([
            'state' => true,
            'message' => 'Cliente actualizado correctamente.',
            'customer' => new CustomerResource($customer->refresh()),
        ]);
    }

    public function destroy(Customer $customer)
    {
        Gate::authorize('delete', $customer);

        $customer->delete();

        return response()->json([
            'state' => true,
            'message' => 'Cliente eliminado correctamente.',
        ]);
    }
    # EXPORTACION
    public function exportExcel()
    {
        $fecha = now()->format('d-m-Y');
        $fileName = "Lista de Clientes {$fecha}.xlsx";

        return Excel::download(new CustomersExport, $fileName);
    }

}
