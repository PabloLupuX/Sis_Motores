<?php

namespace App\Pipelines;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Movement;

class FilterByTipo
{
    public function __construct(
        private ?int $tipo = null,   // 1 = Cara, 2 = Huella
        private ?string $fecha = null
    ) {}

    public function __invoke(Builder $builder, Closure $next)
    {
        // Filtrar por tipo dentro de los movimientos (JSON)
        if (!is_null($this->tipo)) {
            $builder->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->whereNotNull('idMovimientos');
                })->where(function ($sub) {
                    // Recuperar IDs de movimientos con ese tipo
                    $ids = Movement::where('idTipo', $this->tipo)->pluck('id');
                    foreach ($ids as $id) {
                        $sub->orWhereJsonContains('idMovimientos', $id);
                    }
                });
            });
        }

        // Filtrar por fecha exacta si está definida
        if (!is_null($this->fecha)) {
            $builder->whereDate('fecha', $this->fecha);
        }

        return $next($builder);
    }
}
