<?php

namespace App\Exports;

use App\Models\Movement;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MovementExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        // Traemos los movimientos con su relación espacio, ordenados por ID
        return Movement::orderBy('id', 'asc')->get();
    }

    public function map($movement): array
    {
        return [
            $movement->id,
            $movement->espacio->name,
            $movement->idTipo === 1 ? 'Cara' : 'Huella',
            $movement->reconocido ? 'Sí' : 'No',
            $movement->access ? 'Si' : 'No',
            $movement->error ?? '-',
            $movement->fechaEnvioESP32 ? \Carbon\Carbon::parse($movement->fechaEnvioESP32)->format('d-m-Y H:i:s A') : '-',
            $movement->fechaRecepcion ? \Carbon\Carbon::parse($movement->fechaRecepcion)->format('d-m-Y H:i:s A') : '-',
            $movement->fechaReconocimiento ? \Carbon\Carbon::parse($movement->fechaReconocimiento)->format('d-m-Y H:i:s A') : '-',
            $movement->created_at->format('d-m-Y H:i:s A'),
            $movement->updated_at->format('d-m-Y H:i:s A'),
        ];
    }

    public function headings(): array
    {
        return [
            ['LISTA DE MOVIMIENTOS', '', '', '', '', '', '', '', '', '', ''], // Fila 1 con el título
            [], // Fila 2 en blanco
            ['ID', 'Espacio', 'Tipo', 'Reconocido', 'Acceso', 'Error', 'Fecha Envío ESP32', 'Fecha Recepción', 'Fecha Reconocimiento', 'Creación', 'Actualización'], // Fila 3 con encabezados
        ];
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function styles(Worksheet $sheet)
    {
        // Estilo para el título
        $sheet->mergeCells('A1:K1');
        $sheet->getStyle('A1:K1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'CFE2F3'],
            ],
        ]);

        // Estilo para los encabezados de la tabla
        $sheet->getStyle('A3:K3')->applyFromArray([
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
        $sheet->getStyle('A4:K' . $sheet->getHighestRow())->applyFromArray([
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'borders' => [
                'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
            ],
        ]);

        // Ajuste de columnas
        foreach (range('A', 'K') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return [];
    }
}
