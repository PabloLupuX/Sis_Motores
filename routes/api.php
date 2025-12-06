<?php

use App\Http\Controllers\Api\AlertController;
use App\Http\Controllers\Api\ConfigAlertController;
use App\Http\Controllers\Api\EmployeeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EmployeeTypeController;
use App\Http\Controllers\Api\MovementController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Reportes\EmployeeTypePDFController;
use App\Http\Controllers\Reportes\EmployeePDFController;
use App\Http\Controllers\Api\SpaceController;
use App\Http\Controllers\Reportes\SchedulePDFController;
use App\Http\Controllers\Reportes\SpacePDFController;
use App\Http\Controllers\Api\VerificarAccesoController;

use App\Models\EmployeeType;
Route::get('/verificar-acceso', function () {
    return response()->json(['mensaje' => 'Ruta funcionando, usa POST para enviar datos.']);
});
Route::middleware('auth')->group(function () {
    Route::apiResource('espacio', SpaceController::class);
    //Route::apiResource('caja', CajaController::class);

    #EXPORT API
    // Exportación a Excel
    Route::get('/espacios/export-excel', [SpaceController::class, 'exportExcel']);
    
    // Exportación a PDF
    Route::get('/espacios/export-pdf', [SpacePDFController::class, 'exportPDF']);

    #IMPORT API
    // Importación de Excel
    Route::post('/espacios/import-excel', [SpaceController::class, 'importExcel']);
});

