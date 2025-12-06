<?php

use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ConsultasDni;
use App\Http\Controllers\Api\ConsultasId;
use App\Http\Controllers\Api\RolesController;
use App\Http\Controllers\Api\UsuariosController;
use App\Http\Controllers\Api\SpaceController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Reportes\SpacePDFController;
use App\Http\Controllers\Web\SpaceWebController;
use App\Http\Controllers\Web\UsuarioWebController;

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
    Route::get('/espacios', [SpaceWebController::class, 'index'])->name('index.view');
    Route::get('/usuario', [UsuarioWebController::class, 'index'])->name('index.view');
    Route::get('/roles', [UsuarioWebController::class, 'roles'])->name('roles.view');
    Route::get('/datos/dashboard', [DashboardController::class, 'getdatos']);

    #CONSULTA  => BACKEND
    Route::get('/consulta/{dni}', [ConsultasDni::class, 'consultar'])->name('consultar.dni');
    Route::get('/user-id', [ConsultasId::class, 'getUserId'])->middleware('auth:api');

    // ESPACIO -> BACKEND
    Route::prefix('espacio')->group(function () {
        Route::get('/', [SpaceController::class, 'index'])->name('espacio.index');
        Route::post('/', [SpaceController::class, 'store'])->name('espacios.store');
        Route::get('/{space}', [SpaceController::class, 'show'])->name('espacios.show');
        Route::put('/{space}', [SpaceController::class, 'update'])->name('espacios.update');
        Route::delete('/{space}', [SpaceController::class, 'destroy'])->name('espacios.destroy');
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
        Route::get('/export-excel-spaces', [SpaceController::class, 'exportExcel'])->name('export-excel-spaces');
        Route::get('/export-pdf-spaces', [SpacePDFController::class, 'exportPDF'])->name('export-pdf-spaces');
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
