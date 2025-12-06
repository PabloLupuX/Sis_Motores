<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class VerificarAccesoController extends Controller
{
    public function verificar(Request $request)
    {
        date_default_timezone_set('America/Lima');
$empleadoId = null;
        $codigosErrores = [
            0   => 'Sin error',
            101 => 'Error al tomar la foto en ESP32',
            102 => 'Error al decodificar la foto',
            107 => 'No se envió idHuella',
            205 => 'Acceso denegado según servidor',
            404 => 'Error de servidor al enviar foto',
            405 => 'Falló POST al enviar datos',
            406 => 'Conexión HTTPS fallida',
        ];

        $fechaRecepcion = Carbon::now()->format('Y-m-d H:i:s');

        $idEspacio = (int) $request->input('idEspacio', 0);
        $idTipo = (int) $request->input('idTipo', 0);
        $codigoError = (int) $request->input('codigo_error', 0);
        $idHuella = $request->input('idHuella');
        $fechaEnvioESP32 = $request->input('fechaEnvioESP32');
        $fotoAcceso = $request->input('fotoBase64');

        $respuesta = [
            'idEspacio' => $idEspacio,
            'idTipo' => $idTipo,
            'codigoError' => $codigoError,
            'fechaRecepcion' => $fechaRecepcion,
        ];

        try {
            DB::select("SELECT 1 as test");
        } catch (Exception $e) {
            return response()->json([
                'error' => '❌ Error de conexión a la base de datos',
                'detalle' => $e->getMessage(),
                'fechaRecepcion' => $fechaRecepcion,
            ], 500);
        }

        try {
            if ($idTipo === 1) {
                // 📸 PROCESAR RECONOCIMIENTO FACIAL
                if ($codigoError === 0 && !empty($fotoAcceso)) {
                    $carpetaAccesos = public_path('uploads/fotos/accesos/');
                    if (!file_exists($carpetaAccesos)) {
                        mkdir($carpetaAccesos, 0755, true);
                    }

                    $nombreArchivo = uniqid("acceso_") . ".png";
                    $rutaFotoAcceso = $carpetaAccesos . $nombreArchivo;

                    // Decodificar y guardar imagen
                    $fotoDecodificada = base64_decode(
                        preg_replace('#^data:image/\w+;base64,#i', '', $fotoAcceso)
                    );

                    if (!$fotoDecodificada) {
                        $respuesta['codigoError'] = 102;
                        $respuesta['mensaje'] = '❌ Error decodificando la imagen';
                        return response()->json($respuesta, 400);
                    }

                    file_put_contents($rutaFotoAcceso, $fotoDecodificada);

                    // Buscar empleados con foto
                    $empleados = DB::table('employees')
                        ->whereNotNull('foto')
                        ->where('foto', '!=', '')
                        ->get();

                    $empleadoReconocido = null;
                    $carpetaEmpleados = public_path('uploads/fotos/empleados/');

                    foreach ($empleados as $empleado) {
                        $rutaFotoEmpleado = $carpetaEmpleados . $empleado->foto;
                        if (!file_exists($rutaFotoEmpleado)) continue;

                        $data = json_encode([
                            "foto1" => 'data:image/png;base64,' . base64_encode(file_get_contents($rutaFotoAcceso)),
                            "foto2" => 'data:image/png;base64,' . base64_encode(file_get_contents($rutaFotoEmpleado))
                        ]);

                        $ch = curl_init("http://185.140.33.65:5000/comparar");
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                        $respuestaComparacion = curl_exec($ch);
                        curl_close($ch);

                        $resultado = json_decode($respuestaComparacion, true);
                        if (!empty($resultado['resultado']) && $resultado['resultado'] === true) {
                            $empleadoReconocido = $empleado;
                            break;
                        }
                    }

                    $respuesta['fechaReconocimiento'] = Carbon::now()->format('Y-m-d H:i:s');

                    if ($empleadoReconocido) {
                        $respuesta['reconocido'] = 1;
                        $empleadoId= $empleado->id ?? '';
                        $respuesta['nombre'] = $empleadoReconocido->name;
                        $respuesta['apellido'] = $empleadoReconocido->apellido;

                        // Verificar horario
                        $fechaActual = Carbon::now();
                        $horario = DB::table('schedule_table')
                            ->where('idEmpleado', $empleadoReconocido->id)
                            ->where('idEspacio', $idEspacio)
                            ->whereDate('fechaInicio', '<=', $fechaActual->toDateString())
                            ->whereDate('fechaFin', '>=', $fechaActual->toDateString())
                            ->first();

                        if ($horario) {
                            $respuesta['acceso'] = 1;
                            $respuesta['mensajeHorario'] = '✅ Acceso dentro del horario permitido';
                        } else {
                            $respuesta['acceso'] = 0;
                            $respuesta['mensajeHorario'] = '⛔ Usuario reconocido pero fuera del horario permitido';
                        }
                    } else {
                        $respuesta['reconocido'] = 0;
                        $respuesta['acceso'] = 0;
                        $respuesta['mensaje'] = '❌ Usuario no reconocido';
                    }
                } else {
                    $respuesta['mensaje'] = "⚠️ ESP32 reportó codigo_error=$codigoError o no envió foto";
                }
            } elseif ($idTipo === 2) {
                // Procesar huella
                $respuesta['mensaje'] = '👆 Recibida huella para verificación';
                $respuesta['idHuella'] = $idHuella;

                if (empty($idHuella)) {
                    $respuesta['codigoError'] = 107;
                    $respuesta['reconocido'] = 0;
                    $respuesta['acceso'] = 0;
                } else {
                    // Buscar empleado por huella
                    $empleado = DB::table('employees')
                        ->where('idHuella', (int) $idHuella)
                        ->first();

                    if ($empleado) {
                        $respuesta['reconocido'] = 1;
                        $empleadoId= $empleado->id ?? '';
                        $respuesta['nombre'] = $empleado->name ?? '';
                        $respuesta['apellido'] = $empleado->apellido ?? '';

                        // Buscar horario válido para este empleado en este espacio
                        $fechaActual = Carbon::now();

                        $horario = DB::table('schedule_table')
                            ->where('idEmpleado', $empleado->id)
                            ->where('idEspacio', $idEspacio)
                            ->whereDate('fechaInicio', '<=', $fechaActual->toDateString())
                            ->whereDate('fechaFin', '>=', $fechaActual->toDateString())
                            ->first();

                        if ($horario) {
                            $respuesta['acceso'] = 1;
                            $respuesta['mensajeHorario'] = '✅ Acceso dentro del horario permitido';
                        } else {
                            $respuesta['acceso'] = 0;
                            $respuesta['mensajeHorario'] = '⛔ Acceso denegado: fuera del horario permitido';
                        }
                    } else {
                        $respuesta['reconocido'] = 0;
                        $respuesta['acceso'] = 0;
                        $respuesta['mensaje'] = '❌ Huella no reconocida';
                    }
                }

                $respuesta['fechaReconocimiento'] = Carbon::now()->format('Y-m-d H:i:s');
            } else {
                return response()->json([
                    'error' => '❌ idTipo inválido, debe ser 1 (foto) o 2 (huella)',
                    'fechaRecepcion' => $fechaRecepcion,
                ], 400);
            }


            // ✅ REGISTRAR MOVIMIENTO DESPUÉS DE PROCESAR
            DB::table('movimientos')->insert([
                'idEspacio'          => $idEspacio,
                'idTipo'             => $idTipo,
                'reconocido'         => $respuesta['reconocido'] ?? 0,
                'access'             => $respuesta['acceso'] ?? 0,
                'error'              => $codigoError ?: null,
                'fechaEnvioESP32'    => $fechaEnvioESP32 ?: null,
                'fechaRecepcion'     => $fechaRecepcion,
                'fechaReconocimiento' => $respuesta['fechaReconocimiento'] ?? null,
                'created_at'         => Carbon::now(),
                'updated_at'         => Carbon::now(),
            ]);

            $idMovimiento = DB::getPdo()->lastInsertId(); 

           if (($respuesta['reconocido'] ?? 0) === 1) {
                $empleadoId = $empleadoReconocido->id ?? ($empleado->id ?? null);

                if ($empleadoId) {
                    DB::table('EmployeeMovement')->insert([
                        'idMovimiento' => $idMovimiento,
                        'idEmpleado'   => $empleadoId,
                        'created_at'    => Carbon::now(),
                        'updated_at'    => Carbon::now(),
                    ]);
                }
            }    
$respuesta['idMovimiento'] = $idMovimiento;
$respuesta['idEmpleado']   = $empleadoId ?? null;
$respuesta['relacionGuardada'] = isset($empleadoId);

if (($respuesta['acceso'] ?? 1) === 0) {

    // 🔎 Obtener la configuración más reciente
    $config = DB::table('alert_configuration')
        ->orderBy('id', 'desc')
        ->first();

    // Valores por defecto si no hay configuración
    $timeHours = $config->time ?? 0.5;   // ejemplo: 0.5 horas = 30 minutos
    $amount    = $config->amount ?? 3;   // cantidad mínima de intentos

    // Convertir horas decimales a minutos
    $timeMinutes = (int) round($timeHours * 60);

    // movimientos fallidos dentro de ese rango de tiempo
    $fallidos = DB::table('movimientos')
        ->where('access', 0)
        ->where('created_at', '>=', Carbon::now()->subMinutes($timeMinutes))
        ->pluck('id')
        ->toArray();

    // ids ya utilizados en alertas previas
    $usadosEnAlertas = DB::table('alerts')
        ->pluck('idMovimientos')
        ->map(function ($json) {
            return json_decode($json, true) ?: [];
        })
        ->flatten()
        ->toArray();

    // excluir movimientos ya alertados
    $fallidos = array_values(array_diff($fallidos, $usadosEnAlertas));

    // Condicional con los valores de configuración
    if (count($fallidos) >= $amount) {
        DB::table('alerts')->insert([
            'idMovimientos' => json_encode($fallidos),
            'descripcion'   => '⚠️ Se detectaron ' . count($fallidos) .
                               " intentos fallidos en los últimos {$timeMinutes} minutos",
            'fecha'         => Carbon::now()->toDateString(),
            'created_at'    => Carbon::now(),
            'updated_at'    => Carbon::now(),
        ]);

        $respuesta['alerta_generada']    = true;
        $respuesta['movimientos_alerta'] = $fallidos;
    } else {
        $respuesta['alerta_generada'] = false;
    }
}


        } catch (Exception $e) {
            return response()->json([
                'error' => '❌ Error interno al procesar la solicitud',
                'detalle' => $e->getMessage(),
                'fechaRecepcion' => $fechaRecepcion,
            ], 500);
        }

        return response()->json($respuesta);
    }
}
