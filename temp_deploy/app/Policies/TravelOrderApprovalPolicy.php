<?php

namespace App\Policies;

use App\Models\TravelOrderApproval;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TravelOrderApprovalPolicy
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
        return $user->hasRole(['admin', 'approver']);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TravelOrderApproval  $travelOrderApproval
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, TravelOrderApproval $travelOrderApproval)
    {
        return $user->id === $travelOrderApproval->approver_user_id 
            || $user->name === $travelOrderApproval->approver_name
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
        return $user->hasRole(['admin']);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TravelOrderApproval  $travelOrderApproval
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, TravelOrderApproval $travelOrderApproval)
    {
        // User can update (approve/reject) if they are the assigned approver and status is pending
        return ($user->id === $travelOrderApproval->approver_user_id 
                || $user->name === $travelOrderApproval->approver_name)
            && $travelOrderApproval->status === 'pending';
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TravelOrderApproval  $travelOrderApproval
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, TravelOrderApproval $travelOrderApproval)
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TravelOrderApproval  $travelOrderApproval
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, TravelOrderApproval $travelOrderApproval)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TravelOrderApproval  $travelOrderApproval
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, TravelOrderApproval $travelOrderApproval)
    {
        //
    }
}
