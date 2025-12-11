<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use App\Models\Reception;
use TCPDF;

class ReceptionsPDFController extends Controller
{
    public function exportPDF()
    {
        $fecha = now()->format('d-m-Y');
        $fileName = "Lista de Recepciones {$fecha}.pdf";

        // RECIBIR FILTROS DEL FRONT
        $search      = request('search');
        $state       = request('state');
        $fechaInicio = request('fecha_inicio');
        $fechaFin    = request('fecha_fin');

        // -------------------------------------------------------
        //           MOSTRAR FILTROS EN EL PDF
        // -------------------------------------------------------
        $filtroTexto = $search ?: "Sin filtro";
        $filtroEstado = ($state === "" || $state === null) 
            ? "Todos" 
            : ($state == 1 ? "Abiertos" : "Cerrados");

        $filtroFecha = ($fechaInicio && $fechaFin)
            ? "{$fechaInicio} ➝ {$fechaFin}"
            : "Sin filtro";

        // QUERY BASE CON RELACIONES
        $query = Reception::with([
            'engine:id,marca,modelo,hp,tipo,combustible',
            'owner:id,nombres,alias',
            'contact:id,nombres,alias'
        ]);

        // FILTRO: ESTADO
        if ($state !== '' && $state !== null) {
            $query->where('state', $state);
        }

        // FILTRO: BUSCADOR GLOBAL
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('numero_serie', 'like', "%{$search}%")
                    ->orWhere('problema', 'like', "%{$search}%")
                    ->orWhereHas('engine', function ($e) use ($search) {
                        $e->where('marca', 'like', "%{$search}%")
                          ->orWhere('modelo', 'like', "%{$search}%");
                    })
                    ->orWhereHas('owner', fn($o) => $o->where('nombres', 'like', "%{$search}%"))
                    ->orWhereHas('contact', fn($c) => $c->where('nombres', 'like', "%{$search}%"));
            });
        }

        // 🗓 FILTRO: RANGO DE FECHAS (FIX)
        if ($fechaInicio && $fechaFin) {
            $query->whereBetween('fecha_ingreso', [
                $fechaInicio . ' 00:00:00',
                $fechaFin . ' 23:59:59'
            ]);
        }

        // 🔥 CONSULTA FINAL
        $receptions = $query->orderBy('id', 'asc')->get();

        // TRANSFORMAR DATOS
        $receptionsArray = $receptions->map(function ($r) {
            return [
                'id'             => $r->id,
                'engine'         => $r->engine ? $r->engine->marca . ' ' . $r->engine->modelo . ' (' . $r->engine->combustible . ')' : '-',
                'owner'          => $r->owner->nombres ?? '-',
                'contact'        => $r->contact->nombres ?? '-',
                'numero_serie'   => $r->numero_serie ?? '-',
                'problema'       => $r->problema ?? '-',
                'fecha_ingreso'  => $r->fecha_ingreso ?? '-',
                'fecha_resuelto' => $r->fecha_resuelto ?? '-',
                'fecha_entrega'  => $r->fecha_entrega ?? '-',
                'state'          => $r->state ? 'ACTIVO' : 'INACTIVO',
            ];
        })->toArray();

        // -------------------------------------------------------
        //                      PDF
        // -------------------------------------------------------
        $pdf = new TCPDF();
        $pdf->SetCreator('Laravel TCPDF');
        $pdf->SetTitle("Lista de Recepciones {$fecha}");

        $pdf->SetMargins(10, 10, 10);
        $pdf->AddPage('L');

        //  TÍTULO PRINCIPAL
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, "LISTA DE RECEPCIONES - {$fecha}", 0, 1, 'C');
        $pdf->Ln(2);

        // -------------------------------------------------------
        //             SECCIÓN "FILTROS APLICADOS"
        // -------------------------------------------------------
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 7, "FILTROS APLICADOS", 0, 1, 'L');

        $pdf->SetFont('helvetica', '', 10);
        $pdf->MultiCell(0, 6, "Búsqueda: {$filtroTexto}", 0, 'L');
        $pdf->MultiCell(0, 6, "Estado: {$filtroEstado}", 0, 'L');
        $pdf->MultiCell(0, 6, "Fecha de ingreso: {$filtroFecha}", 0, 'L');

        $pdf->Ln(3);

        // -------------------------------------------------------
        //                 ENCABEZADOS DE TABLAS
        // -------------------------------------------------------
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(230, 230, 230);

        $widths = [90, 90, 90];

        $pdf->Cell($widths[0], 8, "DATOS PRINCIPALES", 1, 0, 'C', 1);
        $pdf->Cell($widths[1], 8, "DATOS DEL PROCESO", 1, 0, 'C', 1);
        $pdf->Cell($widths[2], 8, "PROBLEMA REPORTADO", 1, 1, 'C', 1);

        $pdf->SetFont('helvetica', '', 9);

        // -------------------------------------------------------
        //                  CUERPO DEL REPORTE
        // -------------------------------------------------------
        foreach ($receptionsArray as $r) {

            if ($pdf->GetY() > 180) {
                $pdf->AddPage('L');

                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->SetFillColor(230, 230, 230);
                $pdf->Cell($widths[0], 8, "DATOS PRINCIPALES", 1, 0, 'C', 1);
                $pdf->Cell($widths[1], 8, "DATOS DEL PROCESO", 1, 0, 'C', 1);
                $pdf->Cell($widths[2], 8, "PROBLEMA REPORTADO", 1, 1, 'C', 1);
                $pdf->SetFont('helvetica', '', 9);
            }

            // ⭐ COLUMNA 1
            $col1 =
                "OT: {$r['id']}\n" .
                "Motor: {$r['engine']}\n" .
                "Dueño: {$r['owner']}\n" .
                "Contacto: {$r['contact']}\n" .
                "Serie: {$r['numero_serie']}\n" .
                "Estado: {$r['state']}";

            // ⭐ COLUMNA 2
            $col2 =
                "Ingreso: {$r['fecha_ingreso']}\n" .
                "Resuelto: {$r['fecha_resuelto']}\n" .
                "Entrega: {$r['fecha_entrega']}";

            // ⭐ COLUMNA 3
            $col3 = $r['problema'];

            $pdf->MultiCell($widths[0], 25, $col1, 1, 'L', 0, 0);
            $pdf->MultiCell($widths[1], 25, $col2, 1, 'L', 0, 0);
            $pdf->MultiCell($widths[2], 25, $col3, 1, 'L', 0, 1);
        }

        if (ob_get_length()) ob_end_clean();

        $pdfOutput = $pdf->Output($fileName, 'S');

        return response($pdfOutput)
            ->header('Content-Type', 'application/pdf')
            ->header("Content-Disposition", "attachment; filename=\"{$fileName}\"");
    }
}
