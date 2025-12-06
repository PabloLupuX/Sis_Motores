<?php

namespace App\Exports;
use App\Models\ConfigAlert;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ConfigAlertExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return ConfigAlert::orderBy('id', 'asc')->get(); // Traemos
    }

    public function map($configalert): array
    {
        return [
            $configalert->id,
            $configalert->amount,
            $configalert->time,
            $configalert->created_at->format('d-m-Y H:i:s'),
            $configalert->updated_at->format('d-m-Y H:i:s'),
        ];
    }

    public function headings(): array
    {
        return [
            ['LISTA DE CONFIGURACIÓN DE ALERTAS', '', '', '', ''], // Fila 1 con el título
            [], // Fila 2 en blanco
            ['Código', 'Cantidad', 'Tiempo', 'Creación', 'Actualización'], // Fila 3 con encabezados
        ];
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function styles(Worksheet $sheet)
    {
        // Estilo para el título
        $sheet->mergeCells('A1:E1');
        $sheet->getStyle('A1:E1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'CFE2F3'],
            ],
        ]);

        // Estilo para los encabezados de la tabla
        $sheet->getStyle('A3:E3')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9EAD3'],
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
            ],
        ]);

        // Estilo para las filas de datos
        $sheet->getStyle('A4:E' . $sheet->getHighestRow())->applyFromArray([
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'borders' => [
                'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
            ],
        ]);

        // Ajuste de columnas
        foreach (range('A', 'E') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return [];
    }
}
