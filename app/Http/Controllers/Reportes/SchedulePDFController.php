<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use TCPDF;
use Carbon\Carbon;

class SchedulePDFController extends Controller
{
    public function exportPDF()
    {
        // Traemos todos los horarios con relaciones
        $schedules = Schedule::with(['espacio', 'empleado'])->orderBy('id', 'asc')->get();

        $schedulesArray = $schedules->map(function ($schedule) {
            return [
                'id' => $schedule->id,
                'fecha' => Carbon::parse($schedule->fecha)->format('d-m-Y'),
                'fechaInicio' => Carbon::parse($schedule->fechaInicio)->format('d-m-Y H:i:s A'),
                'fechaFin' => Carbon::parse($schedule->fechaFin)->format('d-m-Y H:i:s A'),
                'state' => $schedule->state ? 'Activo' : 'Inactivo',
                'espacio' => $schedule->espacio->name ?? 'N/A',
                'empleado' => $schedule->empleado ? $schedule->empleado->name . ' ' . $schedule->empleado->apellido : 'N/A',
                'created_at' => Carbon::parse($schedule->created_at)->format('d-m-Y H:i:s A'),
                'updated_at' => Carbon::parse($schedule->updated_at)->format('d-m-Y H:i:s A'),
            ];
        })->toArray();

        // Crear el objeto TCPDF
        $pdf = new TCPDF(); // Puedes usar 'L' para orientación horizontal si quieres

        $pdf->SetCreator('Laravel TCPDF');
        $pdf->SetAuthor('Laravel');
        $pdf->SetTitle('Lista de Horarios');
        $pdf->SetSubject('Reporte de Horarios');

        // Márgenes
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 10);

        // Encabezado y pie de página
        $pdf->SetHeaderData('', 0, '', '', [0,0,0], [255,255,255]);
        $pdf->setFooterData([0,0,0], [255,255,255]);

        // Agregar página
        $pdf->AddPage();

        // Título
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->Cell(0, 20, 'Lista de Horarios', 0, 1, 'C');

        // Encabezados de tabla
        $pdf->SetFont('helvetica', 'B', 6); // encabezados más pequeños
        $pdf->SetFillColor(242, 242, 242);
        $header = ['ID', 'Fecha', 'Fecha Inicio', 'Fecha Fin', 'Estado', 'Espacio', 'Empleado', 'Creación', 'Actualización'];
        $widths = [6, 16, 25, 25, 14, 22, 28, 24, 24]; // ajustar anchos para vertical

        foreach ($header as $i => $col) {
            $pdf->MultiCell($widths[$i], 5, $col, 1, 'C', 1, 0); // altura reducida
        }
        $pdf->Ln();

        // Filas de datos
        $pdf->SetFont('helvetica', '', 4); // contenido más pequeño
        foreach ($schedulesArray as $schedule) {
            if ($pdf->GetY() > 260) { // nueva página si estamos al final
                $pdf->AddPage();
                $pdf->SetFont('helvetica', 'B', 5);
                $pdf->SetFillColor(242,242,242);
                foreach ($header as $i => $col) {
                    $pdf->MultiCell($widths[$i], 4, $col, 1, 'C', 1, 0);
                }
                $pdf->Ln();
                $pdf->SetFont('helvetica', '', 4);
            }

            $pdf->MultiCell($widths[0], 5, $schedule['id'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[1], 5, $schedule['fecha'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[2], 5, $schedule['fechaInicio'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[3], 5, $schedule['fechaFin'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[4], 5, $schedule['state'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[5], 5, $schedule['espacio'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[6], 5, $schedule['empleado'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[7], 5, $schedule['created_at'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[8], 5, $schedule['updated_at'], 1, 'C', 0, 0);
            $pdf->Ln();
        }
        // Limpiar buffer
        if (ob_get_length()) {
            ob_end_clean();
        }

        $pdfOutput = $pdf->Output('Horarios.pdf', 'S'); // "S" = string
        return response($pdfOutput)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="Horarios.pdf"');
    }
}
