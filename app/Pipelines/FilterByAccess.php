<?php

namespace App\Pipelines;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class FilterByAccess
{
    public function __construct(private $access) {}

    public function __invoke(Builder $builder, Closure $next)
    {
        if (!is_null($this->access)) {
            $builder->where('access', (bool) $this->access);
        }

        return $next($builder);
    }
}
