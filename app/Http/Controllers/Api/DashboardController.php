<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use App\Models\User;
use App\Models\Engine;
use App\Models\Accessory;
use App\Models\Reception;

class DashboardController extends Controller
{
    public function getDatos()
    {
        return response()->json([
            'clientes' => User::count(),
            'motores' => Engine::count(),
            'accesorios' => Accessory::count(),
            'recepciones' => Reception::count(),
        ]);
    }
}
