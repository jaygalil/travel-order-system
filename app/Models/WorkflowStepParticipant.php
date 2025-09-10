<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowStepParticipant extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'workflow_step_id',
        'user_id',
        'participant_name',
        'participant_title',
        'participant_role',
        'is_primary',
        'can_delegate',
        'weight',
        'conditions',
    ];
    
    protected $casts = [
        'conditions' => 'array',
        'is_primary' => 'boolean',
        'can_delegate' => 'boolean',
        'weight' => 'integer',
    ];
    
    /**
     * The workflow step this participant belongs to
     */
    public function workflowStep()
    {
        return $this->belongsTo(WorkflowStep::class);
    }
    
    /**
     * The user who will participate in this approval step
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Check if this participant's conditions are met for a travel order
     */
    public function conditionsMetFor(TravelOrder $travelOrder)
    {
        if (!$this->conditions || empty($this->conditions)) {
            return true;
        }
        
        // Implement condition logic here - can be extended for complex conditions
        // Example conditions: budget amount, destination, duration, etc.
        foreach ($this->conditions as $condition => $value) {
            switch ($condition) {
                case 'max_budget':
                    if ($travelOrder->estimated_budget > $value) {
                        return false;
                    }
                    break;
                case 'destinations':
                    if (!in_array($travelOrder->farthest_destination, $value)) {
                        return false;
                    }
                    break;
                case 'duration_days':
                    $duration = $travelOrder->date_of_travel_to->diffInDays($travelOrder->date_of_travel_from) + 1;
                    if ($duration > $value) {
                        return false;
                    }
                    break;
                // Add more conditions as needed
            }
        }
        
        return true;
    }
    
    /**
     * Scope for primary participants
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }
    
    /**
     * Scope for participants who can delegate
     */
    public function scopeCanDelegate($query)
    {
        return $query->where('can_delegate', true);
    }
}
