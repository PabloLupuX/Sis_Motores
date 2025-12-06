<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use App\Models\Accessory;
use TCPDF;

class AccessoriesPDFController extends Controller
{
    public function exportPDF()
    {
        // Fecha actual en formato 06-12-2025
        $fecha = now()->format('d-m-Y');
        $fileName = "Lista de Accesorios {$fecha}.pdf";

        // Obtener lista de accesorios ordenados por ID ASC
        $accessories = Accessory::orderBy('id', 'asc')->get();

        // Convertir a arreglo limpio
        $accessoriesArray = $accessories->map(function ($a) {
            return [
                'id'         => $a->id,
                'name'       => $a->name,
                'state'      => $a->state ? 'Activo' : 'Inactivo',
                'created_at' => $a->created_at,
                'updated_at' => $a->updated_at,
            ];
        })->toArray();

        // Crear PDF
        $pdf = new TCPDF();

        $pdf->SetCreator('Laravel TCPDF');
        $pdf->SetAuthor('Laravel');
        $pdf->SetTitle("Lista de Accesorios {$fecha}");
        $pdf->SetSubject('Reporte de Accesorios');

        // Márgenes
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 10);

        // Sin encabezado ni footer
        $pdf->SetHeaderData('', 0, '', '');
        $pdf->setFooterData([0,0,0], [255,255,255]);

        // Página
        $pdf->AddPage();

        // Título
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->Cell(0, 20, "Lista de Accesorios - {$fecha}", 0, 1, 'C');

        // Cabecera de tabla
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(242, 242, 242);

        $header = ['ID', 'Nombre', 'Estado', 'Creación', 'Actualización'];
        $widths = [10, 60, 20, 40, 40];

        foreach ($header as $i => $col) {
            $pdf->MultiCell($widths[$i], 8, $col, 1, 'C', 1, 0);
        }
        $pdf->Ln();

        // Datos de tabla
        $pdf->SetFont('helvetica', '', 8);

        foreach ($accessoriesArray as $item) {

            // Salto de página automático si la tabla llega al final
            if ($pdf->GetY() > 250) {
                $pdf->AddPage();

                $pdf->SetFont('helvetica', 'B', 9);
                $pdf->SetFillColor(242, 242, 242);

                foreach ($header as $i => $col) {
                    $pdf->MultiCell($widths[$i], 8, $col, 1, 'C', 1, 0);
                }
                $pdf->Ln();
                $pdf->SetFont('helvetica', '', 8);
            }

            $pdf->MultiCell($widths[0], 8, $item['id'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[1], 8, $item['name'], 1, 'L', 0, 0);
            $pdf->MultiCell($widths[2], 8, $item['state'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[3], 8, $item['created_at'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[4], 8, $item['updated_at'], 1, 'C', 0, 0);

            $pdf->Ln();
        }

        // Limpiar buffers
        if (ob_get_length()) {
            ob_end_clean();
        }

        // Generar archivo
        $pdfOutput = $pdf->Output($fileName, 'S');

        return response($pdfOutput)
            ->header('Content-Type', 'application/pdf')
            ->header("Content-Disposition", "attachment; filename=\"{$fileName}\"");
    }
}
