<?php

namespace App\Policies;

use App\Models\Nfe;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class NfePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Controller handles filtering
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Nfe $nfe): bool
    {
        return $user->id === $nfe->company->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Nfe $nfe): bool
    {
        return $user->id === $nfe->company->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Nfe $nfe): bool
    {
        return $user->id === $nfe->company->user_id;
    }

    // Additional Actions
    public function transmit(User $user, Nfe $nfe): bool
    {
        return $user->id === $nfe->company->user_id;
    }

    public function cancel(User $user, Nfe $nfe): bool
    {
        return $user->id === $nfe->company->user_id;
    }

    public function correction(User $user, Nfe $nfe): bool
    {
        return $user->id === $nfe->company->user_id;
    }
}
