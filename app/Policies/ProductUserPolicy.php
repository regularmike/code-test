<?php

namespace App\Policies;

use App\ProductUser;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductUserPolicy
{
    use HandlesAuthorization;

    public function before($user, $ability)
    {                
        if ($user->is_admin) {
            return true;
        }
        if (!$user->hasActiveSubscription) {
            return false;
        }
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\User  $user
     * @param  \App\ProductUser  $productUser
     * @return mixed
     */
    public function view(User $user)
    {
        /**
         * right now viewing can only be done via user->products,
         * so we don't need to pass the model to check its user
         */
        return $user->hasActiveSubscription;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->hasActiveSubscription;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\User  $user
     * @param  \App\ProductUser  $productUser
     * @return mixed
     */
    public function update(User $user, ProductUser $productUser)
    {
        /**
         * there is no updating of assigned products,
         * you either have it or you don't
         */
        return $user->is_admin;        
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\User  $user
     * @param  \App\ProductUser  $productUser
     * @return mixed
     */
    public function delete(User $user)
    {
        /**
         * deleting can only be done via user->products,
         * so we don't need to pass the model to check its user
         */
        return $user->hasActiveSubscription;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\User  $user
     * @param  \App\ProductUser  $productUser
     * @return mixed
     */
    public function restore(User $user, ProductUser $productUser)
    {
        /**
         * there is no soft deleting of ProductUser assignments
         * since they are trivial to restore
         */
        return $user->is_admin;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\User  $user
     * @param  \App\ProductUser  $productUser
     * @return mixed
     */
    public function forceDelete(User $user, ProductUser $productUser)
    {
        /**
         * there is no soft deleting of ProductUser assignments
         * since they are trivial to restore
         */
        return $user->is_admin;
    }
}
