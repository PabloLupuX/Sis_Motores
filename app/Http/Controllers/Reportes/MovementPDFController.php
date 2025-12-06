<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use App\Models\Movement;
use TCPDF;

class MovementPDFController extends Controller
{
    public function exportPDF()
    {
        $movements = Movement::orderBy('id', 'asc')->get();

        $movementsArray = $movements->map(function ($movement) {
            return [
                'id' => $movement->id,
                'espacio_name' => $movement->espacio->name,
                'idTipo' => $movement->idTipo === 1 ? 'Cara' : 'Huella',
                'reconocido' => $movement->reconocido ? 'Sí' : 'No',
                'access' => $movement->access ? 'Si' : 'No',
                'error' => $movement->error ?? '-',
                'fechaEnvioESP32' => $movement->fechaEnvioESP32,
                'fechaRecepcion' => $movement->fechaRecepcion,
                'fechaReconocimiento' => $movement->fechaReconocimiento,
                'created_at' => $movement->created_at,
                'updated_at' => $movement->updated_at,
            ];
        })->toArray();

        // Crear el objeto TCPDF (orientación vertical)
        $pdf = new TCPDF('P'); // <-- P = Portrait (vertical)

        $pdf->SetCreator('Laravel TCPDF');
        $pdf->SetAuthor('Laravel');
        $pdf->SetTitle('Lista de Movimientos');
        $pdf->SetSubject('Reporte de Movimientos');

        $pdf->SetMargins(5, 5, 5);
        $pdf->SetAutoPageBreak(true, 8);

        $pdf->SetHeaderData('', 0, '', '', [0, 0, 0], [255, 255, 255]);
        $pdf->setFooterData([0, 0, 0], [255, 255, 255]);

        $pdf->AddPage();

        // Título del reporte
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'Lista de Movimientos', 0, 1, 'C');

        // Encabezados de la tabla
        $pdf->SetFont('helvetica', 'B', 6); // MÁS PEQUEÑO
        $pdf->SetFillColor(242, 242, 242);

        $header = ['ID', 'Espacio', 'Tipo', 'Reconocido', 'Acceso', 'Error', 'Fecha Envío ESP32', 'Fecha Recepción', 'Fecha Reconocimiento', 'Creación', 'Actualización'];
        $widths = [8, 28, 8, 15, 12, 10, 26, 23, 26, 22, 22];

        foreach ($header as $i => $col) {
            $pdf->MultiCell($widths[$i], 6, $col, 1, 'C', 1, 0);
        }
        $pdf->Ln();

        // Filas de datos
        $pdf->SetFont('helvetica', '', 5.5); // MÁS PEQUEÑO TAMBIÉN

        foreach ($movementsArray as $movement) {
            if ($pdf->GetY() > 250) {
                $pdf->AddPage();
                $pdf->SetFont('helvetica', 'B', 6);
                $pdf->SetFillColor(242, 242, 242);
                foreach ($header as $i => $col) {
                    $pdf->MultiCell($widths[$i], 6, $col, 1, 'C', 1, 0);
                }
                $pdf->Ln();
                $pdf->SetFont('helvetica', '', 5.5);
            }

            $pdf->MultiCell($widths[0], 6, $movement['id'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[1], 6, $movement['espacio_name'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[2], 6, $movement['idTipo'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[3], 6, $movement['reconocido'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[4], 6, $movement['access'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[5], 6, $movement['error'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[6], 6, $movement['fechaEnvioESP32'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[7], 6, $movement['fechaRecepcion'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[8], 6, $movement['fechaReconocimiento'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[9], 6, $movement['created_at'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[10], 6, $movement['updated_at'], 1, 'C', 0, 0);
            $pdf->Ln();
        }

        if (ob_get_length()) {
            ob_end_clean();
        }

        $pdfOutput = $pdf->Output('Movimientos.pdf', 'S');
        return response($pdfOutput)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="Movimientos.pdf"');
    }
}
