<?php

namespace App\Pipelines;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class FilterByReconocido
{
    public function __construct(private $reconocido) {}

    public function __invoke(Builder $builder, Closure $next)
    {
        if (!is_null($this->reconocido)) {
            $builder->where('reconocido', (bool) $this->reconocido);
        }

        return $next($builder);
    }
}
