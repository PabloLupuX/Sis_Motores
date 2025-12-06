<?php

namespace App\Exports;

use App\Models\Schedule;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class ScheduleExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        // Traemos todos los horarios con relaciones para espacio y empleado
        return Schedule::with(['espacio', 'empleado'])->orderBy('id', 'asc')->get();
    }

    public function map($schedule): array
    {
        return [
            $schedule->id,
            Carbon::parse($schedule->fecha)->format('d-m-Y'),
            Carbon::parse($schedule->fechaInicio)->format('d-m-Y H:i:s A'),
            Carbon::parse($schedule->fechaFin)->format('d-m-Y H:i:s A'),
            $schedule->state ? 'Activo' : 'Inactivo',
            $schedule->espacio->name ?? 'N/A',
            $schedule->empleado ? $schedule->empleado->name . ' ' . $schedule->empleado->apellido : 'N/A',
            Carbon::parse($schedule->created_at)->format('d-m-Y H:i:s A'),
            Carbon::parse($schedule->updated_at)->format('d-m-Y H:i:s A'),
        ];
    }

    public function headings(): array
    {
        return [
            ['LISTA DE HORARIOS', '', '', '', '', '', '', '', ''], // Fila 1 con título
            [], // Fila 2 en blanco
            ['ID', 'Fecha', 'Fecha Inicio', 'Fecha Fin', 'Estado', 'Espacio', 'Empleado', 'Creación', 'Actualización'], // Fila 3 con encabezados
        ];
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function styles(Worksheet $sheet)
    {
        // Estilo para el título
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'CFE2F3'],
            ],
        ]);

        // Estilo para los encabezados de la tabla
        $sheet->getStyle('A3:I3')->applyFromArray([
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
        $sheet->getStyle('A4:I' . $sheet->getHighestRow())->applyFromArray([
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'borders' => [
                'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
            ],
        ]);

        // Ajuste de columnas
        foreach (range('A', 'I') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return [];
    }
}
