<?php

namespace App\Policies;

use App\Models\Accessory;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AccessoryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('ver accesorios');     
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Accessory $customer): bool
    {
        return $user->can('ver accesorios');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('crear accesorios');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Accessory $customer): bool
    {
        return $user->can('editar accesorios');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Accessory $customer): bool
    {
        return $user->can('eliminar accesorios');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Accessory $customer): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Accessory $customer): bool
    {
        return false;
    }
}
