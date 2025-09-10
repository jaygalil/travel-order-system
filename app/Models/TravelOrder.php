<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TravelOrder extends Model
{
    use HasFactory;
    
    // Status constants to match database ENUM
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING_APPROVAL = 'pending_approval';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_COMPLETED = 'completed';
    
    public static function getStatusOptions()
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PENDING_APPROVAL => 'Pending Approval',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_COMPLETED => 'Completed',
        ];
    }

    protected $fillable = [
        'local_travel_order_no',
        'user_id',
        'date_of_travel_from',
        'date_of_travel_to',
        'prepared_by_user_id',
        'employee_name',
        'position',
        'division_agency',
        'source_of_fund',
        'official_vehicle',
        'purpose',
        'destination',
        'farthest_destination',
        'approx_distance',
        'status',
        'submitted_at',
        'approved_at',
        'workflow_template_id',
    ];

    protected $casts = [
        'date_of_travel_from' => 'date',
        'date_of_travel_to' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'approx_distance' => 'decimal:2',
    ];

    /**
     * Employee who will travel
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * User who prepared this travel order
     */
    public function preparedBy()
    {
        return $this->belongsTo(User::class, 'prepared_by_user_id');
    }

    /**
     * All approval steps for this travel order
     */
    public function approvals()
    {
        return $this->hasMany(TravelOrderApproval::class)->orderBy('sequence');
    }
    
    /**
     * Workflow template used for this travel order
     */
    public function workflowTemplate()
    {
        return $this->belongsTo(WorkflowTemplate::class);
    }
    
    /**
     * All participants in this travel order
     */
    public function participants()
    {
        return $this->hasMany(TravelOrderParticipant::class)->orderBy('is_primary', 'desc')->orderBy('created_at');
    }
    
    /**
     * Primary participant (main traveler)
     */
    public function primaryParticipant()
    {
        return $this->hasOne(TravelOrderParticipant::class)->where('is_primary', true);
    }
    
    /**
     * Secondary participants (additional travelers)
     */
    public function secondaryParticipants()
    {
        return $this->hasMany(TravelOrderParticipant::class)->where('is_primary', false)->orderBy('created_at');
    }

    /**
     * Current pending approval
     */
    public function currentApproval()
    {
        return $this->hasOne(TravelOrderApproval::class)->where('status', self::STATUS_PENDING_APPROVAL)->orderBy('sequence');
    }
    
    /**
     * All attachments for this travel order
     */
    public function attachments()
    {
        return $this->hasMany(TravelOrderAttachment::class)->orderBy('created_at');
    }
    
    /**
     * Public attachments (visible to approvers)
     */
    public function publicAttachments()
    {
        return $this->hasMany(TravelOrderAttachment::class)->where('is_public', true)->orderBy('created_at');
    }
    
    /**
     * Safe attachments only (passed security scan)
     */
    public function safeAttachments()
    {
        return $this->hasMany(TravelOrderAttachment::class)->where('is_safe', true)->orderBy('created_at');
    }

    /**
     * Generate unique travel order number using sequence table
     */
    public static function generateTravelOrderNumber()
    {
        $year = Carbon::now()->year;
        $sequence = \App\Models\TravelOrderSequence::getNextSequence($year);
        
        return $year . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Check if travel order is fully approved
     */
    public function isFullyApproved()
    {
        return $this->status === self::STATUS_APPROVED && 
               $this->approvals()->where('status', self::STATUS_PENDING_APPROVAL)->doesntExist();
    }

    /**
     * Get the approval progress percentage
     */
    public function getApprovalProgress()
    {
        $totalApprovals = $this->approvals()->count();
        $completedApprovals = $this->approvals()->whereIn('status', ['forwarded', 'endorsed', 'verified', 'approved'])->count();
        
        return $totalApprovals > 0 ? round(($completedApprovals / $totalApprovals) * 100, 2) : 0;
    }
}
