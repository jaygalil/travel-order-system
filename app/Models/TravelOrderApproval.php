<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TravelOrderApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'travel_order_id',
        'sequence',
        'approval_level',
        'step_group',
        'group_order',
        'approver_role',
        'approver_name',
        'approver_title',
        'approver_position',
        'approver_user_id',
        'status',
        'action_date',
        'comments',
        'email_token',
        'email_sent_at',
        'action_type',
        'is_required',
        'can_delegate',
        'step_description',
        'step_type',
        'completion_rule',
        'required_approvals',
        'delegated_to_user_id',
        'delegated_at',
        'delegated_reason',
    ];

    protected $casts = [
        'action_date' => 'datetime',
        'email_sent_at' => 'datetime',
        'delegated_at' => 'datetime',
        'is_required' => 'boolean',
        'can_delegate' => 'boolean',
        'approval_level' => 'integer',
        'step_group' => 'integer',
        'group_order' => 'integer',
        'required_approvals' => 'integer',
    ];

    /**
     * Travel order this approval belongs to
     */
    public function travelOrder()
    {
        return $this->belongsTo(TravelOrder::class);
    }

    /**
     * User who can approve this step
     */
    public function approverUser()
    {
        return $this->belongsTo(User::class, 'approver_user_id');
    }

    /**
     * Generate unique email token for approval
     */
    public function generateEmailToken()
    {
        $this->email_token = Str::random(64);
        $this->save();
        return $this->email_token;
    }

    /**
     * Process approval action
     */
    public function processAction($status, $comments = null, $approverUserId = null)
    {
        $this->status = $status;
        $this->action_date = Carbon::now();
        $this->comments = $comments;
        
        if ($approverUserId) {
            $this->approver_user_id = $approverUserId;
        }
        
        $this->save();

        // Update travel order status if needed
        $this->updateTravelOrderStatus();
    }

    /**
     * Update the parent travel order status based on approvals
     */
    private function updateTravelOrderStatus()
    {
        $travelOrder = $this->travelOrder;
        $allApprovals = $travelOrder->approvals;
        
        // Check if all approvals are completed
        if ($allApprovals->where('status', 'pending')->isEmpty()) {
            // Check if any approval was rejected
            if ($allApprovals->where('status', 'rejected')->isNotEmpty()) {
                $travelOrder->status = 'rejected';
            } else {
                // All approvals completed successfully
                $travelOrder->status = 'approved';
                $travelOrder->approved_at = Carbon::now();
            }
            $travelOrder->save();
        }
    }

    /**
     * Get approval status with formatted display
     */
    public function getStatusDisplayAttribute()
    {
        switch ($this->status) {
            case 'pending':
                return 'Pending';
            case 'forwarded':
                return 'Forwarded';
            case 'endorsed':
                return 'Endorsed';
            case 'verified':
                return 'Verified';
            case 'approved':
                return 'Approved';
            case 'rejected':
                return 'Rejected';
            default:
                return ucfirst($this->status);
        }
    }

    /**
     * Check if this approval can be acted upon via email
     */
    public function canApproveViaEmail($token)
    {
        return $this->email_token === $token && 
               $this->status === 'pending' && 
               $this->email_sent_at;
    }
}
