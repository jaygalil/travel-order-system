<?php

namespace App\Services;

use App\Models\TravelOrder;
use App\Models\TravelOrderApproval;
use App\Models\WorkflowTemplate;
use App\Models\WorkflowStep;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ApprovalWorkflowBuilder
{
    /**
     * Create approval workflow from template or fallback to default
     */
    public function createWorkflowFromTemplate(TravelOrder $travelOrder, ?int $templateId = null): void
    {
        try {
            Log::info('Creating workflow from template', [
                'travel_order_id' => $travelOrder->id,
                'template_id' => $templateId,
                'template_id_type' => gettype($templateId),
                'is_null' => is_null($templateId),
                'is_empty' => empty($templateId)
            ]);

            // If a workflow template is specified, use it
            if ($templateId) {
                $workflowTemplate = WorkflowTemplate::with(['steps' => function($query) {
                    $query->orderBy('sequence')->orderBy('step_group')->orderBy('group_order');
                }])->find($templateId);
                
                if ($workflowTemplate) {
                    // Validate template before use
                    $validationErrors = $workflowTemplate->getValidationErrors();
                    
                    if (empty($validationErrors)) {
                        $this->createApprovalsFromTemplate($travelOrder, $workflowTemplate);
                        return;
                    } else {
                        Log::warning('Template validation failed, falling back to default', [
                            'template_id' => $templateId,
                            'travel_order_id' => $travelOrder->id,
                            'validation_errors' => $validationErrors
                        ]);
                    }
                } else {
                    Log::warning('Template not found, falling back to default', [
                        'template_id' => $templateId,
                        'travel_order_id' => $travelOrder->id
                    ]);
                }
            }

            // Try to find user's default template
            $defaultTemplate = WorkflowTemplate::with(['steps' => function($query) {
                $query->orderBy('sequence')->orderBy('step_group')->orderBy('group_order');
            }])
                ->availableToUser($travelOrder->user)
                ->where('is_default', true)
                ->active()
                ->first();
                
            if ($defaultTemplate && $defaultTemplate->steps->count() > 0) {
                Log::info('Using user default template', [
                    'template_id' => $defaultTemplate->id,
                    'travel_order_id' => $travelOrder->id
                ]);
                $this->createApprovalsFromTemplate($travelOrder, $defaultTemplate);
                return;
            }

            // Fall back to system default workflow
            Log::info('Using system default workflow', [
                'travel_order_id' => $travelOrder->id
            ]);
            $this->createDefaultApprovalWorkflow($travelOrder);
            
        } catch (\Exception $e) {
            Log::error('Failed to create approval workflow', [
                'travel_order_id' => $travelOrder->id,
                'template_id' => $templateId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Fall back to system default on any error
            $this->createDefaultApprovalWorkflow($travelOrder);
        }
    }

    /**
     * Create approval workflow from template steps
     */
    private function createApprovalsFromTemplate(TravelOrder $travelOrder, WorkflowTemplate $template): void
    {
        Log::info('Creating approvals from template steps', [
            'travel_order_id' => $travelOrder->id,
            'template_id' => $template->id,
            'template_name' => $template->name,
            'steps_count' => $template->steps->count()
        ]);

        $approvalSequence = 1;
        
        // Group steps by sequence (for parallel processing within the same sequence)
        $stepsBySequence = $template->steps->groupBy('sequence');
        
        foreach ($stepsBySequence as $sequence => $stepsInSequence) {
            Log::debug('Processing sequence', [
                'sequence' => $sequence,
                'steps_count' => $stepsInSequence->count()
            ]);
            
            // Sort by group_order within the sequence
            $sortedSteps = $stepsInSequence->sortBy('group_order');
            
            foreach ($sortedSteps as $step) {
                $this->createApprovalFromStep($travelOrder, $step, $approvalSequence);
                $approvalSequence++;
            }
        }
    }

    /**
     * Create individual approval record from workflow step
     */
    private function createApprovalFromStep(TravelOrder $travelOrder, WorkflowStep $step, int $sequence): void
    {
        try {
            // Find the actual approver based on the step configuration
            $approverUser = $this->resolveApproverUser($step);
            
            Log::debug('Creating approval from step', [
                'travel_order_id' => $travelOrder->id,
                'step_id' => $step->id,
                'step_name' => $step->step_name,
                'approver_type' => $step->approver_type,
                'approver_user_id' => $approverUser ? $approverUser->id : null,
                'sequence' => $sequence
            ]);

            $approvalData = [
                'sequence' => $sequence,
                'approval_level' => $step->sequence, // Use template sequence as level
                'step_group' => $step->step_group,
                'group_order' => $step->group_order,
                'approver_role' => $this->buildApproverRole($step),
                'approver_name' => $step->approver_name ?: ($approverUser ? $approverUser->name : 'Unknown'),
                'approver_title' => $step->approver_title ?: ($approverUser ? ($approverUser->position ?? 'Staff') : ''),
                'approver_position' => $step->approver_position ?: ($approverUser ? $approverUser->position : ''),
                'approver_user_id' => $approverUser ? $approverUser->id : null,
                'action_type' => $step->action_type ?: 'approve',
                'is_required' => $step->is_required ?? true,
                'can_delegate' => $step->can_delegate ?? false,
                'step_description' => $step->step_description,
                'step_type' => $step->step_type ?? 'sequential',
                'completion_rule' => $step->completion_rule ?? 'all',
                'required_approvals' => $step->required_approvals,
                'status' => 'pending'
            ];

            $approval = $travelOrder->approvals()->create($approvalData);
            
            Log::debug('Approval created successfully', [
                'approval_id' => $approval->id,
                'approver_name' => $approvalData['approver_name']
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to create approval from step', [
                'travel_order_id' => $travelOrder->id,
                'step_id' => $step->id,
                'error' => $e->getMessage()
            ]);
            throw $e; // Re-throw to trigger fallback
        }
    }

    /**
     * Resolve the actual user who should approve based on step configuration
     */
    private function resolveApproverUser(WorkflowStep $step): ?User
    {
        switch ($step->approver_type) {
            case 'user':
                if ($step->approver_user_id) {
                    return User::where('id', $step->approver_user_id)
                              ->where('is_active', true)
                              ->first();
                }
                break;
                
            case 'role':
                if ($step->approver_role) {
                    return User::role($step->approver_role)
                              ->where('is_active', true)
                              ->first();
                }
                break;
                
            case 'position':
                if ($step->approver_position) {
                    return User::where('position', $step->approver_position)
                              ->where('is_active', true)
                              ->first();
                }
                break;
                
            case 'department':
                if ($step->approver_department) {
                    // Find department head or first user in department
                    return User::where('department', $step->approver_department)
                              ->where('is_active', true)
                              ->orderBy('position') // Assuming hierarchy in position names
                              ->first();
                }
                break;
        }
        
        return null;
    }

    /**
     * Build approver role identifier
     */
    private function buildApproverRole(WorkflowStep $step): string
    {
        $roleComponents = [$step->approver_type];
        
        switch ($step->approver_type) {
            case 'user':
                $roleComponents[] = $step->approver_user_id;
                break;
            case 'role':
                $roleComponents[] = $step->approver_role;
                break;
            case 'position':
                $roleComponents[] = $step->approver_position;
                break;
            case 'department':
                $roleComponents[] = $step->approver_department;
                break;
        }
        
        return implode('_', array_filter($roleComponents));
    }

    /**
     * Create default approval workflow (fallback)
     */
    private function createDefaultApprovalWorkflow(TravelOrder $travelOrder): void
    {
        Log::info('Creating default approval workflow', [
            'travel_order_id' => $travelOrder->id
        ]);

        // Check if there are users with approver roles
        $approvers = User::role('approver')
            ->whereNotNull('approver_sequence')
            ->where('is_active', true)
            ->orderBy('approver_sequence')
            ->get();

        if ($approvers->isNotEmpty()) {
            foreach ($approvers as $approver) {
                $travelOrder->approvals()->create([
                    'sequence' => $approver->approver_sequence,
                    'approval_level' => $approver->approver_sequence,
                    'approver_role' => 'approver_' . $approver->approver_sequence,
                    'approver_name' => $approver->name,
                    'approver_title' => $approver->approver_title ?? $approver->position,
                    'approver_position' => $approver->position,
                    'approver_user_id' => $approver->id,
                    'status' => 'pending'
                ]);
            }
        } else {
            // Hard-coded fallback if no approvers are configured
            $this->createHardcodedApprovalSteps($travelOrder);
        }
    }

    /**
     * Create hard-coded approval steps as ultimate fallback
     */
    private function createHardcodedApprovalSteps(TravelOrder $travelOrder): void
    {
        $approvalSteps = [
            [
                'sequence' => 1,
                'approval_level' => 1,
                'approver_role' => 'provincial_officer',
                'approver_name' => 'Provincial Officer',
                'approver_title' => 'Provincial Officer',
                'approver_position' => 'Provincial Officer',
                'status' => 'pending'
            ],
            [
                'sequence' => 2,
                'approval_level' => 2,
                'approver_role' => 'human_resources',
                'approver_name' => 'Human Resources',
                'approver_title' => 'Human Resources',
                'approver_position' => 'Human Resources Manager',
                'status' => 'pending'
            ],
            [
                'sequence' => 3,
                'approval_level' => 3,
                'approver_role' => 'recommending_approval',
                'approver_name' => 'Department Head',
                'approver_title' => 'Recommending Approval',
                'approver_position' => 'Department Head',
                'status' => 'pending'
            ],
            [
                'sequence' => 4,
                'approval_level' => 4,
                'approver_role' => 'verified_by',
                'approver_name' => 'Finance Manager',
                'approver_title' => 'Verified By',
                'approver_position' => 'Finance Manager',
                'status' => 'pending'
            ],
            [
                'sequence' => 5,
                'approval_level' => 5,
                'approver_role' => 'approved_by',
                'approver_name' => 'Director',
                'approver_title' => 'Approved By',
                'approver_position' => 'Director',
                'status' => 'pending'
            ]
        ];

        foreach ($approvalSteps as $step) {
            $travelOrder->approvals()->create($step);
        }
    }
}
