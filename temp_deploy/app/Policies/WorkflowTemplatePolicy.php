<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkflowTemplate;
use Illuminate\Auth\Access\HandlesAuthorization;

class WorkflowTemplatePolicy
{
    use HandlesAuthorization;

    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user, $ability)
    {
        // Super admin can do anything
        if ($user->hasRole('super-admin')) {
            return true;
        }
        
        // Admin can manage all workflow templates
        if ($user->hasRole('admin')) {
            return true;
        }
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        return true; // All authenticated users can view workflow templates
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, WorkflowTemplate $workflowTemplate)
    {
        // User can view their own templates or public ones or department ones if they match
        return $this->canAccess($user, $workflowTemplate);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        return true; // All authenticated users can create workflow templates
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, WorkflowTemplate $workflowTemplate)
    {
        // Admin can edit any template
        if ($user->hasRole('admin')) {
            return true;
        }
        
        // Template creator can edit their own template
        if ($workflowTemplate->created_by_user_id === $user->id) {
            return true;
        }
        
        // Public templates can be edited by users with workflow_manager role
        if ($workflowTemplate->visibility === 'public' && $user->hasRole('workflow_manager')) {
            return true;
        }
        
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, WorkflowTemplate $workflowTemplate)
    {
        // Admin can delete any template
        if ($user->hasRole('admin')) {
            return true;
        }
        
        // Only the creator can delete their template
        return $workflowTemplate->created_by_user_id === $user->id;
    }

    /**
     * Check if user can access the workflow template based on visibility rules
     */
    private function canAccess(User $user, WorkflowTemplate $workflowTemplate)
    {
        // Owner can always access
        if ($workflowTemplate->created_by_user_id === $user->id) {
            return true;
        }

        // Public templates are accessible to all
        if ($workflowTemplate->visibility === 'public') {
            return true;
        }

        // Department templates are accessible to users in the same department
        if ($workflowTemplate->visibility === 'department') {
            return $user->department === $workflowTemplate->department;
        }

        // Private templates are only accessible to the owner
        return false;
    }
}
