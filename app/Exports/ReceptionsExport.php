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
            'contact:id,nombres'
        ]);

        // 🔍 FILTRO: ESTADO
        if ($this->state !== '' && $this->state !== null) {
            $query->where('state', $this->state);
        }

        // 🔍 FILTRO: BUSCADOR GLOBAL
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

        // 🗓 FILTRO FECHAS (IGUAL QUE PDF)
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
        return [
            $r->id,
            $r->engine->marca ?? '',
            $r->engine->modelo ?? '',
            $r->engine->combustible ?? '',
            $r->owner->nombres ?? '',
            $r->contact->nombres ?? '',
            $r->numero_serie ?? '',
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
        // ⭐ Mostrar filtros en Excel igual que PDF
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
            [], // Espacio
            [
                'OT',
                'Marca Motor',
                'Modelo Motor',
                'Combustible',
                'Dueño',
                'Contacto',
                'N° Serie',
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
        // Título
        $sheet->mergeCells('A1:N1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        // Filtros
        $sheet->getStyle('A2')->getFont()->setBold(true);
        $sheet->getStyle('A3:A5')->getFont()->setBold(true);

        // Encabezados
        $sheet->getStyle('A7:N7')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => 'center'],
            'fill' => [
                'fillType' => 'solid',
                'startColor' => ['rgb' => 'D9EAD3'],
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => 'thin']
            ]
        ]);

        // Datos
        $last = $sheet->getHighestRow();
        $sheet->getStyle("A8:N{$last}")->applyFromArray([
            'alignment' => ['wrapText' => true, 'vertical' => 'center', 'horizontal' => 'center'],
            'borders' => ['allBorders' => ['borderStyle' => 'thin']]
        ]);

        foreach (range('A', 'N') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [];
    }
}
