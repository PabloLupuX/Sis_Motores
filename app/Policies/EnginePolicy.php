<?php

namespace App\Policies;

use App\Models\Engine;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EnginePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('ver motores');     
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Engine $engine): bool
    {
        return $user->can('ver motores');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('crear motores');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Engine $engine): bool
    {
        return $user->can('editar motores');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Engine $engine): bool
    {
        return $user->can('eliminar motores');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Engine $engine): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Engine $engine): bool
    {
        return false;
    }
}
