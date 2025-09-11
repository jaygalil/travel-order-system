<?php

namespace App\Policies;

use App\Models\TravelOrder;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TravelOrderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return $user->hasRole(['admin', 'employee', 'approver']);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TravelOrder  $travelOrder
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, TravelOrder $travelOrder)
    {
        // User can view their own travel orders, ones they prepared, or if they have admin role
        return $user->id === $travelOrder->user_id 
            || $user->id === $travelOrder->prepared_by_user_id 
            || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->hasRole(['admin', 'employee']);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TravelOrder  $travelOrder
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, TravelOrder $travelOrder)
    {
        // Only the owner or preparer can update, and only if status is draft
        return ($user->id === $travelOrder->user_id || $user->id === $travelOrder->prepared_by_user_id)
            && $travelOrder->status === 'draft';
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TravelOrder  $travelOrder
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, TravelOrder $travelOrder)
    {
        // Only the owner or preparer can delete, and only if status is draft
        return ($user->id === $travelOrder->user_id || $user->id === $travelOrder->prepared_by_user_id)
            && $travelOrder->status === 'draft';
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TravelOrder  $travelOrder
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, TravelOrder $travelOrder)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TravelOrder  $travelOrder
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, TravelOrder $travelOrder)
    {
        //
    }
}
