<?php

namespace App\Exports;

use App\Models\Reception;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReceptionsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithCustomStartCell
{
    protected $search;
    protected $state;
    protected $fechaInicio;
    protected $fechaFin;

    public function __construct($search, $state, $fechaInicio, $fechaFin)
    {
        $this->search      = $search;
        $this->state       = $state;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin    = $fechaFin;
    }

    public function collection()
    {
        $query = Reception::with([
            'engine:id,marca,modelo,combustible',
            'owner:id,nombres',
            'contact:id,nombres',
            'accessories:id,name' // 🔥 ACCESORIOS AÑADIDO
        ]);

        if ($this->state !== '' && $this->state !== null) {
            $query->where('state', $this->state);
        }

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('numero_serie', 'like', "%{$this->search}%")
                  ->orWhere('problema', 'like', "%{$this->search}%")
                  ->orWhereHas('engine', function ($e) {
                      $e->where('marca', 'like', "%{$this->search}%")
                        ->orWhere('modelo', 'like', "%{$this->search}%");
                  })
                  ->orWhereHas('owner', fn($o) => $o->where('nombres', 'like', "%{$this->search}%"))
                  ->orWhereHas('contact', fn($c) => $c->where('nombres', 'like', "%{$this->search}%"));
            });
        }

        if ($this->fechaInicio && $this->fechaFin) {
            $query->whereBetween('fecha_ingreso', [
                $this->fechaInicio . ' 00:00:00',
                $this->fechaFin . ' 23:59:59'
            ]);
        }

        return $query->orderBy('id', 'asc')->get();
    }

    public function map($r): array
    {
        // 🔥 Convertimos los accesorios a una lista
        $accesorios = $r->accessories
            ? $r->accessories->pluck('name')->implode(', ')
            : '';

        return [
            $r->id,
            $r->engine->marca ?? '',
            $r->engine->modelo ?? '',
            $r->engine->combustible ?? '',
            $r->owner->nombres ?? '',
            $r->contact->nombres ?? '',
            $r->numero_serie ?? '',
            $accesorios, // 🔥 NUEVA COLUMNA
            $r->problema ?? '',
            $r->fecha_ingreso ? date('d-m-Y H:i', strtotime($r->fecha_ingreso)) : '',
            $r->fecha_resuelto ? date('d-m-Y H:i', strtotime($r->fecha_resuelto)) : '',
            $r->fecha_entrega ? date('d-m-Y H:i', strtotime($r->fecha_entrega)) : '',
            $r->state ? 'ACTIVO' : 'INACTIVO',
            $r->created_at?->format('d-m-Y H:i:s') ?? '',
            $r->updated_at?->format('d-m-Y H:i:s') ?? '',
        ];
    }

    public function headings(): array
    {
        $filtroTexto = $this->search ?: "Sin filtro";
        $filtroEstado = ($this->state === "" || $this->state === null)
            ? "Todos"
            : ($this->state == 1 ? "Abiertos" : "Cerrados");

        $filtroFecha = ($this->fechaInicio && $this->fechaFin)
            ? "{$this->fechaInicio} → {$this->fechaFin}"
            : "Sin filtro";

        return [
            ["LISTA DE RECEPCIONES"],
            ["FILTROS APLICADOS:"],
            ["📝 Búsqueda:", $filtroTexto],
            ["📌 Estado:",   $filtroEstado],
            ["📅 Fecha:",    $filtroFecha],
            [],
            [
                'OT',
                'Marca Motor',
                'Modelo Motor',
                'Combustible',
                'Dueño',
                'Contacto',
                'N° Serie',
                'Accesorios', // 🔥 NUEVA COLUMNA
                'Problema',
                'Ingreso',
                'Resuelto',
                'Entrega',
                'Estado',
                'Creación',
                'Actualización'
            ],
        ];
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:O1'); // 🔥 Ahora llega hasta la columna O
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        $sheet->getStyle('A2')->getFont()->setBold(true);
        $sheet->getStyle('A3:A5')->getFont()->setBold(true);

        $sheet->getStyle('A7:O7')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => 'center'],
            'fill' => [
                'fillType' => 'solid',
                'startColor' => ['rgb' => 'D9EAD3'],
            ],
            'borders' => ['allBorders' => ['borderStyle' => 'thin']]
        ]);

        $last = $sheet->getHighestRow();
        $sheet->getStyle("A8:O{$last}")->applyFromArray([
            'alignment' => ['wrapText' => true, 'vertical' => 'center', 'horizontal' => 'center'],
            'borders' => ['allBorders' => ['borderStyle' => 'thin']]
        ]);

        foreach (range('A', 'O') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [];
    }
}
