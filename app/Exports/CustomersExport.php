<?php

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomersExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithCustomStartCell
{
    public function collection()
    {
        return Customer::orderBy('id', 'asc')->get();
    }

    public function map($customer): array
    {
        return [
            $customer->id,
            $customer->codigo,
            $customer->nombres,
            $customer->alias,
            $customer->telefono,
            $customer->state ? 'Activo' : 'Inactivo',
            $customer->created_at ? $customer->created_at->format('d-m-Y H:i:s') : '',
            $customer->updated_at ? $customer->updated_at->format('d-m-Y H:i:s') : '',
        ];
    }

    public function headings(): array
    {
        return [
            ['LISTA DE CLIENTES', '', '', '', '', '', '', ''], // FILA 1
            [], // FILA 2 vacía
            ['ID', 'Código', 'Nombres', 'Alias', 'Teléfono', 'Estado', 'Creación', 'Actualización'] // FILA 3
        ];
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function styles(Worksheet $sheet)
    {
        // Combinar título y dar estilo
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => 'center'],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'CFE2F3'],
            ],
        ]);

        // Encabezados (Fila 3)
        $sheet->getStyle('A3:H3')->applyFromArray([
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

        // Estilos de datos (desde fila 4 hasta el final)
        $sheet->getStyle('A4:H' . $sheet->getHighestRow())->applyFromArray([
            'alignment' => ['horizontal' => 'center'],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                ],
            ],
        ]);

        // Ajuste automático de ancho
        foreach (range('A', 'H') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return [];
    }
}
