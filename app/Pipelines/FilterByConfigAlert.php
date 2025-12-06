<?php

namespace App\Pipelines;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class FilterByConfigAlert
{
    private $search;

    public function __construct($search)
    {
        $this->search = $search;
    }

    public function __invoke(Builder $builder, Closure $next)
    {
        if (!empty($this->search)) {
            $builder->where('amount', 'like', '%' . $this->search . '%');
        }

        return $next($builder);
    }
}
