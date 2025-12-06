<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use App\Models\ConfigAlert;
use TCPDF;

class ConfigAlertPDFController extends Controller
{
    public function exportPDF()
    {
        // Obtener los datos de las configuraciones y convertirlos en array
        $configalerts = ConfigAlert::orderBy('id', 'asc')->get();

        $configalertsArray = $configalerts->map(function ($configalerts) {
            return [
                'id' => $configalerts->id,
                'amount' => $configalerts->amount,
                'time' => $configalerts->time,
                'created_at' => $configalerts->created_at,
                'updated_at' => $configalerts->updated_at,
            ];
        })->toArray();
        // Crear el objeto TCPDF
        $pdf = new TCPDF(); // 'L' para orientación horizontal

        $pdf->SetCreator('Laravel TCPDF');
        $pdf->SetAuthor('Laravel');
        $pdf->SetTitle('Lista de Configuraciones de alertas');
        $pdf->SetSubject('Reporte de Configuraciones de alertas');

        // Configuración de márgenes
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 10); // Asegura que haya espacio entre las filas y el pie de la página

        // Eliminar la línea de encabezado (borde superior)
        $pdf->SetHeaderData('', 0, '', '', [0, 0, 0], [255, 255, 255]);

        // Personalizar el pie de página (eliminar línea predeterminada)
        $pdf->setFooterData(array(0, 0, 0), array(255, 255, 255));

        // Agregar la primera página
        $pdf->AddPage();

        // Encabezado del PDF
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->Cell(0, 20, 'Lista de Configuraciones de alertas', 0, 1, 'C');

        // Encabezados de la tabla
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(242, 242, 242);  // Color de fondo para los encabezados

        $header = ['ID', 'Cantidad', 'Tiempo', 'Creación', 'Actualización'];
        $widths = [14, 50, 50, 38, 38]; // Tamaños adecuados para las celdas

        // Establecer los encabezados de la tabla en la primera página
        foreach ($header as $i => $col) {
            $pdf->MultiCell($widths[$i], 9, $col, 1, 'C', 1, 0);
        }
        $pdf->Ln();  // Salto de línea después del encabezado

        // Imprimir los datos de las configuraciones
        $pdf->SetFont('helvetica', '', 9);

        foreach ($configalertsArray as $configalert) {
            // Si la posición Y está cerca del final de la página, añadir una nueva página y repetir los encabezados
            if ($pdf->GetY() > 250) {
                $pdf->AddPage(); // Añadir una nueva página
                // Repetir los encabezados en la nueva página
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->SetFillColor(242, 242, 242);
                foreach ($header as $i => $col) {
                    $pdf->MultiCell($widths[$i], 9, $col, 1, 'C', 1, 0);
                }
                $pdf->Ln();
            }

            // Asegurarse de que las celdas no se sobrepasen
            $pdf->SetFont('helvetica', '', 9);
            $pdf->MultiCell($widths[0], 9, $configalert['id'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[1], 9, $configalert['amount'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[2], 9, $configalert['time'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[3], 9, $configalert['created_at'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[4], 9, $configalert['updated_at'], 1, 'C', 0, 0);
            $pdf->Ln();  // Salto de línea después de cada fila
        }
        // Detenemos cualquier salida previa si la hay
        if (ob_get_length()) {
            ob_end_clean();
        }

        $pdfOutput = $pdf->Output('ConfigAlertas.pdf', 'S'); // "S" = string, no lo imprime directamente
        return response($pdfOutput)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="ConfigAlertas.pdf"');
    }
}
