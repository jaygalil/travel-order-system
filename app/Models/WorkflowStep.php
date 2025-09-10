<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowStep extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'workflow_template_id',
        'sequence',
        'step_group',
        'group_order',
        'step_name',
        'step_description',
        'step_type', // sequential or parallel
        'completion_rule', // all, majority, any, custom
        'required_approvals', // for custom completion rules
        'allows_multiple_approvers',
        'approver_type',
        'approver_user_id',
        'approver_role',
        'approver_position',
        'approver_department',
        'approver_name',
        'approver_title',
        'action_type',
        'is_required',
        'can_delegate',
        'conditions',
    ];
    
    protected $casts = [
        'conditions' => 'array',
        'is_required' => 'boolean',
        'can_delegate' => 'boolean',
        'allows_multiple_approvers' => 'boolean',
        'required_approvals' => 'integer',
    ];
    
    /**
     * The workflow template this step belongs to
     */
    public function workflowTemplate()
    {
        return $this->belongsTo(WorkflowTemplate::class);
    }
    
    /**
     * The user assigned as approver (if approver_type is 'user')
     */
    public function approverUser()
    {
        return $this->belongsTo(User::class, 'approver_user_id');
    }
    
    /**
     * Multiple participants for this workflow step
     */
    public function participants()
    {
        return $this->hasMany(WorkflowStepParticipant::class);
    }
    
    /**
     * Get the actual approver user(s) based on approver_type and participants
     */
    public function getApproverUsers()
    {
        // If this step allows multiple approvers and has participants, use them
        if ($this->allows_multiple_approvers && $this->participants()->exists()) {
            return $this->participants()->with('user')->get()->map(function($participant) {
                return $participant->user;
            });
        }
        
        // Otherwise, use the legacy single approver logic
        switch ($this->approver_type) {
            case 'user':
                return $this->approverUser ? collect([$this->approverUser]) : collect();
                
            case 'role':
                return User::role($this->approver_role)->where('is_active', true)->get();
                
            case 'position':
                return User::where('position', $this->approver_position)
                          ->where('is_active', true)
                          ->get();
                          
            case 'department':
                return User::where('department', $this->approver_department)
                          ->where('is_active', true)
                          ->get();
                          
            default:
                return collect();
        }
    }
    
    /**
     * Check if this step's conditions are met for a travel order
     */
    public function conditionsMetFor(TravelOrder $travelOrder)
    {
        if (!$this->conditions || empty($this->conditions)) {
            return true;
        }
        
        // Implement condition logic here
        // For now, return true - can be extended for complex conditions
        return true;
    }
    
    /**
     * Check if step completion rule is met based on approvals
     */
    public function isCompletionRuleMet($approvals)
    {
        $totalParticipants = $this->allows_multiple_approvers ? 
            $this->participants()->count() : 1;
        $approvedCount = $approvals->where('status', 'approved')->count();
        $rejectedCount = $approvals->where('status', 'rejected')->count();
        
        switch ($this->completion_rule) {
            case 'all':
                return $approvedCount === $totalParticipants;
                
            case 'majority':
                $required = ceil($totalParticipants / 2);
                return $approvedCount >= $required;
                
            case 'any':
                return $approvedCount > 0;
                
            case 'custom':
                return $approvedCount >= $this->required_approvals;
                
            default:
                return $approvedCount === $totalParticipants;
        }
    }
    
    /**
     * Check if step should be rejected based on completion rule
     */
    public function shouldRejectOnRejection($approvals)
    {
        $totalParticipants = $this->allows_multiple_approvers ? 
            $this->participants()->count() : 1;
        $rejectedCount = $approvals->where('status', 'rejected')->count();
        $approvedCount = $approvals->where('status', 'approved')->count();
        
        switch ($this->completion_rule) {
            case 'all':
                // Any rejection kills the step
                return $rejectedCount > 0;
                
            case 'majority':
                $required = ceil($totalParticipants / 2);
                // If too many rejections to reach majority approval
                return ($totalParticipants - $rejectedCount) < $required;
                
            case 'any':
                // All must reject to fail the step
                return $rejectedCount === $totalParticipants;
                
            case 'custom':
                // If remaining approvers can't meet required threshold
                $remaining = $totalParticipants - $rejectedCount - $approvedCount;
                return ($approvedCount + $remaining) < $this->required_approvals;
                
            default:
                return $rejectedCount > 0;
        }
    }
    
    /**
     * Get eligible participants for a travel order (considering conditions)
     */
    public function getEligibleParticipants($travelOrder = null)
    {
        if (!$this->allows_multiple_approvers) {
            return collect();
        }
        
        $participants = $this->participants()->with('user')->get();
        
        if ($travelOrder) {
            $participants = $participants->filter(function($participant) use ($travelOrder) {
                return $participant->conditionsMetFor($travelOrder);
            });
        }
        
        return $participants;
    }
    
    /**
     * Check if this is a parallel approval step
     */
    public function isParallel()
    {
        return $this->step_type === 'parallel';
    }
    
    /**
     * Check if this is a sequential approval step
     */
    public function isSequential()
    {
        return $this->step_type === 'sequential';
    }
    
    /**
     * Scope for required steps
     */
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }
    
    /**
     * Scope for parallel steps
     */
    public function scopeParallel($query)
    {
        return $query->where('step_type', 'parallel');
    }
    
    /**
     * Scope for sequential steps
     */
    public function scopeSequential($query)
    {
        return $query->where('step_type', 'sequential');
    }
}
