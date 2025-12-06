<?php

namespace App\Pipelines;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class FilterByAlert
{
    private $search;

    public function __construct($search)
    {
        $this->search = $search;
    }

    public function __invoke(Builder $builder, Closure $next)
    {
        if (!empty($this->search)) {
            $searchLower = strtolower($this->search);

            $builder->where(function ($q) use ($searchLower) {
                $q
                    // Buscar por idMovimiento (parcial)
                    ->orWhere('idMovimientos', 'like', '%' . $searchLower . '%')
                    // Buscar por descripcion (parcial, case-insensitive)
                    ->orWhereRaw('LOWER(descripcion) LIKE ?', ['%' . $searchLower . '%']);
            });
        }

        return $next($builder);
    }
}
