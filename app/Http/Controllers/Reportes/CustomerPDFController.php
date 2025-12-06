<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use TCPDF;

class CustomerPDFController extends Controller
{
    public function exportPDF()
    {
        // Fecha actual en formato 06-12-2025
        $fecha = now()->format('d-m-Y');
        $fileName = "Lista de Clientes {$fecha}.pdf";

        // Obtener lista de clientes
        $customers = Customer::orderBy('id', 'asc')->get();

        $customersArray = $customers->map(function ($c) {
            return [
                'id'         => $c->id,
                'codigo'     => $c->codigo,
                'nombres'    => $c->nombres,
                'alias'      => $c->alias,
                'telefono'   => $c->telefono,
                'state'      => $c->state ? 'Activo' : 'Inactivo',
                'created_at' => $c->created_at,
                'updated_at' => $c->updated_at,
            ];
        })->toArray();

        // Crear PDF
        $pdf = new TCPDF();

        $pdf->SetCreator('Laravel TCPDF');
        $pdf->SetAuthor('Laravel');
        $pdf->SetTitle("Lista de Clientes {$fecha}");
        $pdf->SetSubject('Reporte de Clientes');

        // Margenes
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 10);

        // Sin encabezado y sin footer
        $pdf->SetHeaderData('', 0, '', '');
        $pdf->setFooterData([0,0,0], [255,255,255]);

        // Página
        $pdf->AddPage();

        // Título
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->Cell(0, 20, "Lista de Clientes - {$fecha}", 0, 1, 'C');

        // Encabezados
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(242, 242, 242);

        $header = ['ID', 'Código', 'Nombres', 'Alias', 'Teléfono', 'Estado', 'Creación', 'Actualización'];
        $widths = [8, 22, 36, 26, 22, 18, 30, 30];

        foreach ($header as $i => $col) {
            $pdf->MultiCell($widths[$i], 8, $col, 1, 'C', 1, 0);
        }
        $pdf->Ln();

        // Datos
        $pdf->SetFont('helvetica', '', 8);

        foreach ($customersArray as $customer) {
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

            $pdf->MultiCell($widths[0], 8, $customer['id'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[1], 8, $customer['codigo'], 1, 'L', 0, 0);
            $pdf->MultiCell($widths[2], 8, $customer['nombres'], 1, 'L', 0, 0);
            $pdf->MultiCell($widths[3], 8, $customer['alias'], 1, 'L', 0, 0);
            $pdf->MultiCell($widths[4], 8, $customer['telefono'], 1, 'L', 0, 0);
            $pdf->MultiCell($widths[5], 8, $customer['state'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[6], 8, $customer['created_at'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[7], 8, $customer['updated_at'], 1, 'C', 0, 0);
            $pdf->Ln();
        }

        // Evitar errores de buffer
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
