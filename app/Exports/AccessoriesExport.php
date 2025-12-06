<?php

namespace App\Exports;

use App\Models\Accessory;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AccessoriesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithCustomStartCell
{
    public function collection()
    {
        return Accessory::orderBy('id', 'asc')->get();
    }

    public function map($accessory): array
    {
        return [
            $accessory->id,
            $accessory->name,
            $accessory->state ? 'Activo' : 'Inactivo',
            $accessory->created_at ? $accessory->created_at->format('d-m-Y H:i:s') : '',
            $accessory->updated_at ? $accessory->updated_at->format('d-m-Y H:i:s') : '',
        ];
    }

    public function headings(): array
    {
        return [
            ['LISTA DE ACCESORIOS', '', '', '', ''], // FILA 1
            [], // FILA 2 vacía
            ['ID', 'Nombre', 'Estado', 'Creación', 'Actualización'], // FILA 3
        ];
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function styles(Worksheet $sheet)
    {
        // Combinar título
        $sheet->mergeCells('A1:E1');
        $sheet->getStyle('A1:E1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => 'center'],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'CFE2F3'],
            ],
        ]);

        // Encabezados (Fila 3)
        $sheet->getStyle('A3:E3')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => 'center'],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9EAD3'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                ],
            ],
        ]);

        // Estilos de datos (Fila 4 en adelante)
        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle("A4:E{$highestRow}")->applyFromArray([
            'alignment' => ['horizontal' => 'center'],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                ],
            ],
        ]);

        // Auto-size de columnas
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [];
    }
}
