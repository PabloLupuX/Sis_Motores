<?php


use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\SpaceController;
use App\Http\Controllers\Reportes\SpacePDFController;


use App\Models\EmployeeType;
Route::get('/verificar-acceso', function () {
    return response()->json(['mensaje' => 'Ruta funcionando, usa POST para enviar datos.']);
});


