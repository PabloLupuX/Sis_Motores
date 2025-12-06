<?php

namespace App\Exports;

use App\Models\Engine;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EnginesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithCustomStartCell
{
    public function collection()
    {
        return Engine::orderBy('id', 'asc')->get();
    }

    public function map($engine): array
    {
        return [
            $engine->id,
            $engine->hp,
            $engine->tipo,
            $engine->marca,
            $engine->modelo,
            $engine->year,
            $engine->created_at ? $engine->created_at->format('d-m-Y H:i:s') : '',
            $engine->updated_at ? $engine->updated_at->format('d-m-Y H:i:s') : '',
        ];
    }

    public function headings(): array
    {
        return [
            ['LISTA DE MOTORES', '', '', '', '', '', '', '', ''], // Fila 1
            [],                                                   // Fila 2 vacía
            ['ID', 'HP', 'Tipo', 'Marca', 'Modelo', 'Año', 'Creación', 'Actualización'], // Fila 3
        ];
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function styles(Worksheet $sheet)
    {
        // TÍTULO
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => 'center'],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'CFE2F3'],
            ],
        ]);

        // ENCABEZADOS DE TABLA
        $sheet->getStyle('A3:H3')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => 'center'],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9EAD3'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ]);

        // DATOS
        $sheet->getStyle('A4:H' . $sheet->getHighestRow())->applyFromArray([
            'alignment' => ['horizontal' => 'center'],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ]);

        // AUTO-SIZE
        foreach (range('A', 'H') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return [];
    }
}
