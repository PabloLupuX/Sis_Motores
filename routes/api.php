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
Route::post(uri: '/verificar-acceso', action: [VerificarAccesoController::class, 'verificar']);
Route::get('/verificar-acceso', function () {
    return response()->json(['mensaje' => 'Ruta funcionando, usa POST para enviar datos.']);
});
Route::middleware('auth')->group(function () {
    Route::apiResource('tipos_empleados', EmployeeType::class);
    Route::apiResource('empleado', EmployeeController::class);
    Route::apiResource('config_alertas', ConfigAlertController::class);
    Route::apiResource('movimiento', MovementController::class);
    Route::apiResource('alerta', AlertController::class);
    Route::apiResource('horario', ScheduleController::class);
    Route::apiResource('espacio', SpaceController::class);
    //Route::apiResource('caja', CajaController::class);

    #EXPORT API
    // Exportación a Excel
    Route::get('/espacios/export-excel', [SpaceController::class, 'exportExcel']);
    Route::get('/horarios/export-excel', [ScheduleController::class, 'exportExcel']);
    Route::get('/tipos_empleados/export-excel', [EmployeeTypeController::class, 'exportExcel']);
    Route::get('/empleados/export-excel', [EmployeeController::class, 'exportExcel']);
    //Route::get('/movimientos/export-excel', [MovementController::class, 'exportExcel']);
    Route::get('/alertas/export-excel', [AlertController::class, 'exportExcel']);
    
    // Exportación a PDF
    Route::get('/espacios/export-pdf', [SpacePDFController::class, 'exportPDF']);
    Route::get('/horarios/export-pdf', [SchedulePDFController::class, 'exportPDF']);
    Route::get('/tipos_empleados/export-pdf', [EmployeeTypePDFController::class, 'exportPDF']);
    Route::get('/empleados/export-pdf', [EmployeePDFController::class, 'exportPDF']);
    //Route::get('/movimientos/export-pdf', [MovementPDFController::class, 'exportPDF']);
    //Route::get('/alertas/export-pdf', [AlertPDFController::class, 'exportPDF']);

    #IMPORT API
    // Importación de Excel
    Route::post('/espacios/import-excel', [SpaceController::class, 'importExcel']);
    Route::post('/horarios/import-excel', [ScheduleController::class, 'importExcel']);
    Route::post('/tipos_empleados/import-excel', [EmployeeTypeController::class, 'importExcel']);
    Route::post('/empleados/import-excel', [EmployeeController::class, 'importExcel']);
    //Route::post('/movimientos/import-excel', [MovementController::class, 'importExcel']);
});

