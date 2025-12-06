<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use TCPDF;
use Carbon\Carbon;

class AlertPDFController extends Controller
{
    public function exportPDF()
    {
        // Obtener las alertas con sus movimientos
        $alerts = Alert::orderBy('id', 'asc')->get();

        // Convertir a array con formato
        $alertsArray = $alerts->map(function ($alert) {
            // obtenemos los movimientos
            $movimientos = $alert->movimientos();

            // sacamos los tipos
            $tipos = $movimientos->map(function ($mov) {
                return $mov->idTipo === 1 ? 'Cara' : 'Huella';
            })->unique()->implode(', ');

            return [
                'id'          => $alert->id,
                'movimientos' => $movimientos->pluck('id')->implode(', ') ?: 'Sin movimientos',
                'descripcion' => $alert->descripcion,
                'fecha'       => $alert->fecha ? Carbon::parse($alert->fecha)->format('d-m-Y') : null,
                'tipo'        => $tipos ?: 'Sin tipo',
                'created_at'  => Carbon::parse($alert->created_at)->format('d-m-Y H:i:s A'),
                'updated_at'  => Carbon::parse($alert->updated_at)->format('d-m-Y H:i:s A'),
            ];
        })->toArray();

        // Crear el objeto TCPDF
        $pdf = new TCPDF();

        $pdf->SetCreator('Laravel TCPDF');
        $pdf->SetAuthor('Laravel');
        $pdf->SetTitle('Lista de Alertas');
        $pdf->SetSubject('Reporte de Alertas');

        // Configuración de márgenes
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 10);

        // Eliminar la línea de encabezado
        $pdf->SetHeaderData('', 0, '', '', [0, 0, 0], [255, 255, 255]);

        // Personalizar pie de página
        $pdf->setFooterData(array(0, 0, 0), array(255, 255, 255));

        // Agregar página
        $pdf->AddPage();

        // Encabezado principal
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->Cell(0, 20, 'Lista de Alertas', 0, 1, 'C');

        // Encabezados de la tabla
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(242, 242, 242);

        $header = ['ID', 'Movimientos', 'Descripción', 'Fecha', 'Tipo', 'Creación', 'Actualización'];
        $widths = [7, 26, 55, 20, 16, 34, 34]; // ajustado para que quepa "movimientos"

        // Dibujar encabezados
        foreach ($header as $i => $col) {
            $pdf->MultiCell($widths[$i], 8, $col, 1, 'C', 1, 0);
        }
        $pdf->Ln();

        // Imprimir los datos
        $pdf->SetFont('helvetica', '', 8);

        foreach ($alertsArray as $alert) {
            // Si se llega al final de la página -> nueva página y repetir encabezados
            if ($pdf->GetY() > 250) {
                $pdf->AddPage();
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->SetFillColor(242, 242, 242);
                foreach ($header as $i => $col) {
                    $pdf->MultiCell($widths[$i], 8, $col, 1, 'C', 1, 0);
                }
                $pdf->Ln();
            }

            // Datos
            $pdf->SetFont('helvetica', '', 8);
            $pdf->MultiCell($widths[0], 8, $alert['id'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[1], 8, $alert['movimientos'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[2], 8, $alert['descripcion'], 1, 'L', 0, 0); // Descripción alineada a la izquierda
            $pdf->MultiCell($widths[3], 8, $alert['fecha'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[4], 8, $alert['tipo'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[5], 8, $alert['created_at'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[6], 8, $alert['updated_at'], 1, 'C', 0, 0);
            $pdf->Ln();
        }

        if (ob_get_length()) {
            ob_end_clean();
        }

        $pdfOutput = $pdf->Output('Alertas.pdf', 'S');

        return response($pdfOutput)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="Alertas.pdf"');
    }
}
