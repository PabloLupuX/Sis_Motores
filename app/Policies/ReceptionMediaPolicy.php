<?php

namespace App\Policies;

use App\Models\ReceptionMedia;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ReceptionMediaPolicy
{
    /**
     * Ver todos los archivos multimedia asociados.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('ver media recepcion');
    }

    /**
     * Ver un archivo específico.
     */
    public function view(User $user, ReceptionMedia $media): bool
    {
        return $user->can('ver media recepcion');
    }

    /**
     * Subir/crear archivos multimedia.
     */
    public function create(User $user): bool
    {
        return $user->can('crear media recepcion');
    }

    /**
     * Actualizar un archivo multimedia (si lo permites).
     */
    public function update(User $user, ReceptionMedia $media): bool
    {
        return $user->can('editar media recepcion');
    }

    /**
     * Eliminar archivo multimedia.
     */
    public function delete(User $user, ReceptionMedia $media): bool
    {
        return $user->can('eliminar media recepcion');
    }

    public function restore(User $user, ReceptionMedia $media): bool
    {
        return false;
    }

    public function forceDelete(User $user, ReceptionMedia $media): bool
    {
        return false;
    }
}
