<?php

use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ConsultasDni;
use App\Http\Controllers\Api\ConsultasId;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\EngineController;
use App\Http\Controllers\Api\RolesController;
use App\Http\Controllers\Api\UsuariosController;
use App\Http\Controllers\Api\AccessoryController;
use App\Http\Controllers\Api\ReceptionController;
use App\Http\Controllers\Api\ReceptionMediaController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Web\CustomersWebController;
use App\Http\Controllers\Web\UsuarioWebController;
use App\Http\Controllers\Web\EnginesWebController;
use App\Http\Controllers\Reportes\CustomerPDFController;
use App\Http\Controllers\Reportes\EnginePDFController;
use App\Http\Controllers\Reportes\AccessoriesPDFController;
use App\Http\Controllers\Web\AccessoriesWebController;
use App\Http\Controllers\Web\ReceptionsWebController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use PSpell\Config;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        $user = Auth::user();
        return Inertia::render('Dashboard', [
            'mustReset' => $user->restablecimiento == 0,
        ]);
    })->name('dashboard');

    #VISTAS DEL FRONTEND
    Route::get('/clientes', [CustomersWebController::class, 'index'])->name('index.view');
    Route::get('/motores', [EnginesWebController::class, 'index'])->name('index.view');
    Route::get('/accesorios', [AccessoriesWebController::class, 'index'])->name('index.view');
    Route::get('/recepciones', [ReceptionsWebController::class, 'index'])->name('index.view');
    Route::get('/usuario', [UsuarioWebController::class, 'index'])->name('index.view');
    Route::get('/roles', [UsuarioWebController::class, 'roles'])->name('roles.view');
    Route::get('/datos/dashboard', [DashboardController::class, 'getdatos']);

    #CONSULTA  => BACKEND
    Route::get('/consulta/{dni}', [ConsultasDni::class, 'consultar'])->name('consultar.dni');
    Route::get('/user-id', [ConsultasId::class, 'getUserId'])->middleware('auth:api');


    // CLIENTE -> BACKEND
    Route::prefix('cliente')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('clientes.index');
        Route::post('/', [CustomerController::class, 'store'])->name('clientes.store');
        Route::get('/{customer}', [CustomerController::class, 'show'])->name('clientes.show');
        Route::put('/{customer}', [CustomerController::class, 'update'])->name('clientes.update');
        Route::delete('/{customer}', [CustomerController::class, 'destroy'])->name('clientes.destroy');
    });

    // MOTORES -> BACKEND
    Route::prefix('motor')->group(function () {
        Route::get('/', [EngineController::class, 'index'])->name('motores.index');
        Route::post('/', [EngineController::class, 'store'])->name('motores.store');
        Route::get('/{engine}', [EngineController::class, 'show'])->name('motores.show');
        Route::put('/{engine}', [EngineController::class, 'update'])->name('motores.update');
        Route::delete('/{engine}', [EngineController::class, 'destroy'])->name('motores.destroy');
    });
    // ACCESORIOS -> BACKEND
    Route::prefix('accesorio')->group(function () {
        Route::get('/', [AccessoryController::class, 'index'])->name('accesorios.index');
        Route::post('/', [AccessoryController::class, 'store'])->name('accesorios.store');
        Route::get('/{accessory}', [AccessoryController::class, 'show'])->name('accesorios.show');
        Route::put('/{accessory}', [AccessoryController::class, 'update'])->name('accesorios.update');
        Route::delete('/{accessory}', [AccessoryController::class, 'destroy'])->name('accesorios.destroy');
    });
    // RECEPCION -> BACKEND
    Route::prefix('recepcion')->group(function () {
        Route::get('/', [ReceptionController::class, 'index'])->name('recepcion.index');
        Route::post('/', [ReceptionController::class, 'store'])->name('recepcion.store');
        Route::get('/{reception}', [ReceptionController::class, 'show'])->name('recepcion.show');
        Route::put('/{reception}', [ReceptionController::class, 'update'])->name('recepcion.update');
        Route::delete('/{reception}', [ReceptionController::class, 'destroy'])->name('recepcion.destroy');
    });
    // RECEPCIONMEDIA -> BACKEND
    Route::prefix('recepcion-media')->group(function () {
        Route::get('/', [ReceptionMediaController::class, 'index'])->name('recepcion-media.index');
        Route::post('/', [ReceptionMediaController::class, 'store'])->name('recepcion-media.store');
        Route::get('/{media}', [ReceptionMediaController::class, 'show'])->name('recepcion-media.show');
        Route::put('/{media}', [ReceptionMediaController::class, 'update'])->name('recepcion-media.update');
        Route::delete('/{media}', [ReceptionMediaController::class, 'destroy'])->name('recepcion-media.destroy');
    });

    #USUARIOS -> BACKEND
    Route::prefix('usuarios')->group(function () {
        Route::get('/', [UsuariosController::class, 'index'])->name('usuarios.index');
        Route::post('/', [UsuariosController::class, 'store'])->name('usuarios.store');
        Route::get('/{user}', [UsuariosController::class, 'show'])->name('usuarios.show');
        Route::put('/{user}', [UsuariosController::class, 'update'])->name('usuarios.update');
        Route::delete('/{user}', [UsuariosController::class, 'destroy'])->name('usuarios.destroy');
    });

    #ROLES => BACKEND
    Route::prefix('rol')->group(function () {
        Route::get('/', [RolesController::class, 'index'])->name('rol.index');
        Route::get('/Permisos', [RolesController::class, 'indexPermisos'])->name('rol.indexPermisos');
        Route::post('/', [RolesController::class, 'store'])->name('rol.store');
        Route::get('/{id}', [RolesController::class, 'show'])->name('rol.show');
        Route::put('/{id}', [RolesController::class, 'update'])->name('rol.update');
        Route::delete('/{id}', [RolesController::class, 'destroy'])->name('rol.destroy');
    });
    Route::prefix('panel/reports')->group(function () {

        #EXPORTACION Y IMPORTACION ESPACIOS
        Route::get('/export-excel-customers', [CustomerController::class, 'exportExcel'])->name('export-excel-customers');
        Route::get('/export-excel-engines', [EngineController::class, 'exportExcel'])->name('export-excel-engines');
        Route::get('/export-excel-accessories', [AccessoryController::class, 'exportExcel'])->name('export-excel-accessories');
        Route::get('/export-pdf-customers', [CustomerPDFController::class, 'exportPDF'])->name('export-pdf-customers');
        Route::get('/export-pdf-engines', [EnginePDFController::class, 'exportPDF'])->name('export-pdf-engines');
        Route::get('/export-pdf-accessories', [AccessoriesPDFController::class, 'exportPDF'])->name('export-pdf-accessories');
    });
});
            //RUTAS PARA QUE PASEN EL TEST
        Route::get('/register', [RegisteredUserController::class, 'create'])
            ->middleware('guest')
            ->name('register');

        Route::post('/register', [RegisteredUserController::class, 'store'])
            ->middleware('guest');
// Archivos de configuración adicionales
require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
