<?php

namespace App\Policies;

use App\Models\User;

class ProductPolicy
{
    /**
     * Determine if the user can create products.
     */
    public function create(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine if the user can update products.
     */
    public function update(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine if the user can delete products.
     */
    public function delete(User $user): bool
    {
        return $user->is_admin;
    }
}
