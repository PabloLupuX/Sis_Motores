<?php

namespace App\Policies;

use App\Models\ConfigAlert;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ConfigAlertPolicy
{
    /**
     * Determina si el usuario puede ver cualquier configuración de alerta.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('ver configuraciones de alerta');
    }

    /**
     * Determina si el usuario puede ver una configuración de alerta específica.
     */
    public function view(User $user, ConfigAlert $configAlert): bool
    {
        return $user->can('ver configuraciones de alerta');
    }

    /**
     * Determina si el usuario puede crear configuraciones de alerta.
     */
    public function create(User $user): bool
    {
        return $user->can('crear configuraciones de alerta');
    }

    /**
     * Determina si el usuario puede actualizar una configuración de alerta.
     */
    public function update(User $user, ConfigAlert $configAlert): bool
    {
        return $user->can('editar configuraciones de alerta');
    }

    /**
     * Determina si el usuario puede eliminar una configuración de alerta.
     */
    public function delete(User $user, ConfigAlert $configAlert): bool
    {
        return $user->can('eliminar configuraciones de alerta');
    }

    /**
     * Restaurar no aplica en este caso.
     */
    public function restore(User $user, ConfigAlert $configAlert): bool
    {
        return false;
    }

    /**
     * Eliminación forzada no aplica en este caso.
     */
    public function forceDelete(User $user, ConfigAlert $configAlert): bool
    {
        return false;
    }
}
