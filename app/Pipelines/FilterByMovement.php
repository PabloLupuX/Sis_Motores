<?php

namespace App\Pipelines;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class FilterByMovement {
    private $search;
    private $idTipo; // 🔹 añadimos propiedad

    public function __construct($search, $idTipo = null) {
        $this->search = $search;
        $this->idTipo = $idTipo; // 🔹 inicializamos
    }

    public function __invoke(Builder $builder, Closure $next) {
        if (!empty($this->search)) {
            $searchLower = strtolower($this->search);

            $builder->where(function ($q) use ($searchLower) {
                // Buscar por ID (parcial)
                $q
                  // Buscar por nombre del espacio (parcial, case-insensitive)
                  ->orWhereHas('espacio', function ($q2) use ($searchLower) {
                      $q2->whereRaw('LOWER(name) LIKE ?', ['%' . $searchLower . '%']);
                  });
            });
        }

        // 🔹 Filtrar por idTipo
        if (!is_null($this->idTipo) && $this->idTipo !== '') {
            $builder->where('idTipo', $this->idTipo);
        }

        return $next($builder);
    }
}
