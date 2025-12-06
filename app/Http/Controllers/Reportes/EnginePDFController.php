<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use App\Models\Engine;
use TCPDF;

class EnginePDFController extends Controller
{
    public function exportPDF()
    {
        $fecha = now()->format('d-m-Y');
        $fileName = "Lista de Motores {$fecha}.pdf";

        // Lista de motores
        $engines = Engine::orderBy('id', 'asc')->get();

        $enginesArray = $engines->map(function ($e) {
            return [
                'id'         => $e->id,
                'hp'         => $e->hp,
                'tipo'       => $e->tipo,
                'marca'      => $e->marca,
                'modelo'     => $e->modelo,
                'year'        => $e->year,
                'created_at' => $e->created_at,
                'updated_at' => $e->updated_at,
            ];
        })->toArray();

        // Crear PDF
        $pdf = new TCPDF();

        $pdf->SetCreator('Laravel TCPDF');
        $pdf->SetAuthor('Laravel');
        $pdf->SetTitle("Lista de Motores {$fecha}");
        $pdf->SetSubject('Reporte de Motores');

        // Configuración general
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->SetHeaderData('', 0, '', '');
        $pdf->setFooterData([0,0,0], [255,255,255]);

        // Página inicial
        $pdf->AddPage();

        // Título
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->Cell(0, 15, "Lista de Motores - {$fecha}", 0, 1, 'C');

        // Encabezados
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(242, 242, 242);

        $header = ['ID', 'HP', 'Tipo', 'Marca', 'Modelo', 'Año', 'Creación', 'Actualización'];

        // Anchos ajustados
        $widths = [8, 16, 22, 28, 30, 12, 32, 32];

        // Dibujar encabezados
        foreach ($header as $i => $col) {
            $pdf->MultiCell($widths[$i], 8, $col, 1, 'C', 1, 0);
        }
        $pdf->Ln();

        // Datos
        $pdf->SetFont('helvetica', '', 8);

        foreach ($enginesArray as $engine) {

            if ($pdf->GetY() > 250) {
                $pdf->AddPage();

                // Volver a dibujar encabezados
                $pdf->SetFont('helvetica', 'B', 9);
                $pdf->SetFillColor(242, 242, 242);

                foreach ($header as $i => $col) {
                    $pdf->MultiCell($widths[$i], 8, $col, 1, 'C', 1, 0);
                }

                $pdf->Ln();
                $pdf->SetFont('helvetica', '', 8);
            }

            // Fila
            $pdf->MultiCell($widths[0], 8, $engine['id'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[1], 8, $engine['hp'], 1, 'L', 0, 0);
            $pdf->MultiCell($widths[2], 8, $engine['tipo'], 1, 'L', 0, 0);
            $pdf->MultiCell($widths[3], 8, $engine['marca'], 1, 'L', 0, 0);
            $pdf->MultiCell($widths[4], 8, $engine['modelo'], 1, 'L', 0, 0);
            $pdf->MultiCell($widths[5], 8, $engine['year'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[6], 8, $engine['created_at'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[7], 8, $engine['updated_at'], 1, 'C', 0, 0);

            $pdf->Ln();
        }

        // Limpiar buffer
        if (ob_get_length()) {
            ob_end_clean();
        }

        // Descargar archivo
        $pdfOutput = $pdf->Output($fileName, 'S');

        return response($pdfOutput)
            ->header('Content-Type', 'application/pdf')
            ->header("Content-Disposition", "attachment; filename=\"{$fileName}\"");
    }
}
