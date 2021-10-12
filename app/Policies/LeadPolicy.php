<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LeadPolicy
{
    use HandlesAuthorization;

    public function before(User $user)
    {
        // return $user->is_admin?true:false;
        return $user->is_admin === true;
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        // return $user->is_admin?true:false;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Lead  $lead
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Lead $lead)
    {
        //
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Lead  $lead
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Lead $lead)
    {
        return $user->id == $lead->creator;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Lead  $lead
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Lead $lead)
    {
        return $user->id == $lead->creator;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Lead  $lead
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Lead $lead)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Lead  $lead
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Lead $lead)
    {
        //
    }
}
