<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowTemplate extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'description',
        'created_by_user_id',
        'visibility',
        'department',
        'is_active',
        'is_default',
        'layout',
        'metadata',
    ];
    
    protected $casts = [
        'metadata' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];
    
    /**
     * The user who created this workflow template
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
    
    /**
     * Workflow steps in sequence
     */
    public function steps()
    {
        return $this->hasMany(WorkflowStep::class)->orderBy('sequence');
    }
    
    /**
     * Travel orders using this workflow
     */
    public function travelOrders()
    {
        return $this->hasMany(TravelOrder::class);
    }
    
    /**
     * Check if user can use this workflow template
     */
    public function canBeUsedBy(User $user)
    {
        if (!$this->is_active) {
            return false;
        }
        
        switch ($this->visibility) {
            case 'public':
                return true;
            case 'private':
                return $this->created_by_user_id === $user->id;
            case 'department':
                return $user->department === $this->department;
            default:
                return false;
        }
    }
    
    /**
     * Check if the template is valid for use
     */
    public function isValidForUse(): bool
    {
        return $this->is_active && $this->steps()->count() > 0;
    }
    
    /**
     * Get validation errors for the template
     */
    public function getValidationErrors(): array
    {
        $errors = [];
        
        if (!$this->is_active) {
            $errors[] = 'Template is not active';
        }
        
        if ($this->steps()->count() === 0) {
            $errors[] = 'Template must have at least one approval step';
        }
        
        // Check if all steps have valid approvers
        $invalidSteps = $this->steps()->whereNull('approver_user_id')
            ->whereNull('approver_role')
            ->whereNull('approver_position')
            ->whereNull('approver_department')
            ->count();
            
        if ($invalidSteps > 0) {
            $errors[] = "Template has {$invalidSteps} step(s) without valid approvers";
        }
        
        return $errors;
    }
    
    /**
     * Scope for active templates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    /**
     * Scope for templates available to a user
     */
    public function scopeAvailableToUser($query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->where('visibility', 'public')
              ->orWhere(function ($sq) use ($user) {
                  $sq->where('visibility', 'private')
                     ->where('created_by_user_id', $user->id);
              })
              ->orWhere(function ($sq) use ($user) {
                  $sq->where('visibility', 'department')
                     ->where('department', $user->department);
              });
        });
    }
}
