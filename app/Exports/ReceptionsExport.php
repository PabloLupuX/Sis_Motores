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
            'owner:id,nombres,alias',
            'contact:id,nombres,alias',
            'accessories:id,name'
        ]);

        if ($this->state !== '' && $this->state !== null) {
            $query->where('state', $this->state);
        }

        // 🔍 BUSCADOR CASE-INSENSITIVE
        if (!empty($this->search)) {

            $search = mb_strtolower($this->search);

            $query->where(function ($q) use ($search) {

                $q->whereRaw('LOWER(numero_serie) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(problema) LIKE ?', ["%{$search}%"])

                  ->orWhereHas('engine', function ($e) use ($search) {
                      $e->whereRaw('LOWER(marca) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(modelo) LIKE ?', ["%{$search}%"]);
                  })

                  ->orWhereHas('owner', function ($o) use ($search) {
                      $o->where(function ($oo) use ($search) {
                          $oo->whereRaw('LOWER(nombres) LIKE ?', ["%{$search}%"])
                             ->orWhereRaw('LOWER(alias) LIKE ?', ["%{$search}%"]);
                      });
                  })

                  ->orWhereHas('contact', function ($c) use ($search) {
                      $c->where(function ($cc) use ($search) {
                          $cc->whereRaw('LOWER(nombres) LIKE ?', ["%{$search}%"])
                             ->orWhereRaw('LOWER(alias) LIKE ?', ["%{$search}%"]);
                      });
                  });
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
        $accesorios = $r->accessories->pluck('name')->implode(', ');

        $tipoMantenimiento = match ($r->tipo_mantenimiento) {
            'preventivo' => 'Preventivo',
            'correctivo' => 'Correctivo',
            'predictivo' => 'Predictivo',
            'proactivo' => 'Proactivo',
            'detectivo_inspeccion' => 'Detectivo / Inspección',
            default => '',
        };

        return [
            $r->id,
            $r->engine->marca ?? '',
            $r->engine->modelo ?? '',
            $r->engine->combustible ?? '',
            $r->owner->nombres ?? '',
            $r->contact->nombres ?? '',
            $r->numero_serie ?? '',
            $tipoMantenimiento, // ✅ FIX
            $accesorios ?: '-',
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
        return [
            ["LISTA DE RECEPCIONES"],
            [],
            [
                'OT',
                'Marca Motor',
                'Modelo Motor',
                'Combustible',
                'Dueño',
                'Contacto',
                'N° Serie',
                'Tipo Mantenimiento',
                'Accesorios',
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
        $sheet->mergeCells('A1:P1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        $sheet->getStyle('A3:P3')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => 'center'],
            'fill' => [
                'fillType' => 'solid',
                'startColor' => ['rgb' => 'D9EAD3'],
            ],
            'borders' => ['allBorders' => ['borderStyle' => 'thin']]
        ]);

        $last = $sheet->getHighestRow();
        $sheet->getStyle("A4:P{$last}")->applyFromArray([
            'alignment' => ['wrapText' => true, 'vertical' => 'center', 'horizontal' => 'center'],
            'borders' => ['allBorders' => ['borderStyle' => 'thin']]
        ]);

        foreach (range('A', 'P') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [];
    }
}
