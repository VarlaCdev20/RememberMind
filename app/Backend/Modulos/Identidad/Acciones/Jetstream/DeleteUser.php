<?php

namespace App\Backend\Modulos\Identidad\Acciones\Jetstream;

use App\Models\User;
use Laravel\Jetstream\Contracts\DeletesUsers;

class DeleteUser implements DeletesUsers
{
    /**
     * Delete the given user.
     */
    public function delete(User $user): void
    {
        $user->tokens->each->delete();
        $user->update(['estado' => 'INACTIVO']);
    }
}
