<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WorkflowTemplate;
use App\Models\WorkflowStep;
use App\Models\User;

class WorkflowTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get admin user for created_by
        $adminUser = User::whereHas('roles', function($q) {
            $q->where('name', 'admin');
        })->first();

        if (!$adminUser) {
            $this->command->error('❌ Admin user not found. Please run RolesAndUsersSeeder first.');
            return;
        }

        // Standard 5-Step Approval Workflow
        $standardWorkflow = WorkflowTemplate::create([
            'name' => 'Standard 5-Step Approval',
            'description' => 'Standard government approval workflow with 5 sequential steps',
            'created_by_user_id' => $adminUser->id,
            'visibility' => 'public',
            'department' => null,
            'is_active' => true,
            'is_default' => true,
            'layout' => 'sequential',
            'metadata' => [
                'estimated_completion_days' => 5,
                'priority_level' => 'standard',
                'notes' => 'Default workflow for most travel orders'
            ]
        ]);

        $this->createWorkflowSteps($standardWorkflow, [
            [
                'step_name' => 'Immediate Supervisor Review',
                'step_description' => 'Review by immediate supervisor or department head',
                'sequence' => 1,
                'approver_type' => 'role',
                'approver_role' => 'supervisor',
                'approver_title' => 'Immediate Supervisor',
                'action_type' => 'forwarded'
            ],
            [
                'step_name' => 'Department Head Endorsement', 
                'step_description' => 'Endorsement by department head',
                'sequence' => 2,
                'approver_type' => 'role',
                'approver_role' => 'approver',
                'approver_title' => 'Department Head',
                'action_type' => 'endorsed'
            ],
            [
                'step_name' => 'Budget Office Verification',
                'step_description' => 'Budget verification and fund availability check',
                'sequence' => 3,
                'approver_type' => 'department',
                'approver_department' => 'Finance',
                'approver_title' => 'Budget Officer',
                'action_type' => 'verified'
            ],
            [
                'step_name' => 'Division Chief Approval',
                'step_description' => 'Approval by division chief or equivalent',
                'sequence' => 4,
                'approver_type' => 'position',
                'approver_position' => 'Division Chief',
                'approver_title' => 'Division Chief',
                'action_type' => 'approved'
            ],
            [
                'step_name' => 'Final Authority Approval',
                'step_description' => 'Final approval by head of office or authorized signatory',
                'sequence' => 5,
                'approver_type' => 'role',
                'approver_role' => 'admin',
                'approver_title' => 'Head of Office',
                'action_type' => 'approved'
            ]
        ]);

        // Simplified 3-Step Approval Workflow
        $simplifiedWorkflow = WorkflowTemplate::create([
            'name' => 'Simplified 3-Step Approval',
            'description' => 'Simplified approval workflow for routine travel',
            'created_by_user_id' => $adminUser->id,
            'visibility' => 'public', 
            'department' => null,
            'is_active' => true,
            'is_default' => false,
            'layout' => 'sequential',
            'metadata' => [
                'estimated_completion_days' => 3,
                'priority_level' => 'fast',
                'notes' => 'For routine travel under specific amount threshold'
            ]
        ]);

        $this->createWorkflowSteps($simplifiedWorkflow, [
            [
                'step_name' => 'Supervisor Approval',
                'step_description' => 'Review and approval by immediate supervisor',
                'sequence' => 1,
                'approver_type' => 'role',
                'approver_role' => 'approver',
                'approver_title' => 'Immediate Supervisor',
                'action_type' => 'forwarded'
            ],
            [
                'step_name' => 'Budget Verification',
                'step_description' => 'Budget verification by finance department',
                'sequence' => 2,
                'approver_type' => 'department',
                'approver_department' => 'Finance',
                'approver_title' => 'Finance Officer',
                'action_type' => 'verified'
            ],
            [
                'step_name' => 'Final Approval',
                'step_description' => 'Final approval by department head',
                'sequence' => 3,
                'approver_type' => 'role',
                'approver_role' => 'admin',
                'approver_title' => 'Department Head',
                'action_type' => 'approved'
            ]
        ]);

        // Emergency Travel Workflow
        $emergencyWorkflow = WorkflowTemplate::create([
            'name' => 'Emergency Travel Approval',
            'description' => 'Fast-track approval for emergency or urgent travel',
            'created_by_user_id' => $adminUser->id,
            'visibility' => 'public',
            'department' => null,
            'is_active' => true,
            'is_default' => false,
            'layout' => 'parallel',
            'metadata' => [
                'estimated_completion_days' => 1,
                'priority_level' => 'urgent',
                'notes' => 'For emergency travel requiring immediate approval'
            ]
        ]);

        $this->createWorkflowSteps($emergencyWorkflow, [
            [
                'step_name' => 'Emergency Approval',
                'step_description' => 'Parallel approval by supervisor and finance',
                'sequence' => 1,
                'step_group' => 1,
                'group_order' => 1,
                'step_type' => 'parallel',
                'completion_rule' => 'all',
                'allows_multiple_approvers' => true,
                'approver_type' => 'role',
                'approver_role' => 'approver',
                'approver_title' => 'Supervisor',
                'action_type' => 'endorsed'
            ],
            [
                'step_name' => 'Budget Emergency Check',
                'step_description' => 'Emergency budget verification',
                'sequence' => 1,
                'step_group' => 1,
                'group_order' => 2,
                'step_type' => 'parallel', 
                'completion_rule' => 'all',
                'allows_multiple_approvers' => true,
                'approver_type' => 'department',
                'approver_department' => 'Finance',
                'approver_title' => 'Finance Manager',
                'action_type' => 'verified'
            ],
            [
                'step_name' => 'Emergency Final Approval',
                'step_description' => 'Final emergency approval by authorized official',
                'sequence' => 2,
                'approver_type' => 'role',
                'approver_role' => 'admin',
                'approver_title' => 'Authorized Official',
                'action_type' => 'approved'
            ]
        ]);

        // Department-Specific IT Workflow
        $itWorkflow = WorkflowTemplate::create([
            'name' => 'IT Department Workflow',
            'description' => 'Specialized workflow for IT department travel orders',
            'created_by_user_id' => $adminUser->id,
            'visibility' => 'department',
            'department' => 'IT Department',
            'is_active' => true,
            'is_default' => false,
            'layout' => 'sequential',
            'metadata' => [
                'estimated_completion_days' => 4,
                'priority_level' => 'standard',
                'notes' => 'Specific to IT department with technical considerations'
            ]
        ]);

        $this->createWorkflowSteps($itWorkflow, [
            [
                'step_name' => 'Technical Review',
                'step_description' => 'Technical justification review by IT supervisor',
                'sequence' => 1,
                'approver_type' => 'department',
                'approver_department' => 'IT Department',
                'approver_position' => 'IT Supervisor',
                'approver_title' => 'IT Supervisor',
                'action_type' => 'forwarded'
            ],
            [
                'step_name' => 'Budget Approval',
                'step_description' => 'Budget verification for IT-related travel',
                'sequence' => 2,
                'approver_type' => 'department',
                'approver_department' => 'Finance',
                'approver_title' => 'Finance Officer',
                'action_type' => 'verified'
            ],
            [
                'step_name' => 'IT Director Approval',
                'step_description' => 'Final approval by IT Director',
                'sequence' => 3,
                'approver_type' => 'position',
                'approver_position' => 'IT Director',
                'approver_title' => 'IT Director',
                'action_type' => 'approved'
            ]
        ]);

        $this->command->info('✅ Workflow templates seeded successfully!');
        $this->command->info('📋 Created 4 workflow templates:');
        $this->command->info('   - Standard 5-Step Approval (default)');
        $this->command->info('   - Simplified 3-Step Approval'); 
        $this->command->info('   - Emergency Travel Approval');
        $this->command->info('   - IT Department Workflow');
    }

    private function createWorkflowSteps($template, $steps)
    {
        foreach ($steps as $stepData) {
            WorkflowStep::create(array_merge([
                'workflow_template_id' => $template->id,
                'step_type' => 'sequential',
                'completion_rule' => 'all',
                'allows_multiple_approvers' => false,
                'is_required' => true,
                'can_delegate' => true,
                'step_group' => null,
                'group_order' => null,
            ], $stepData));
        }
    }
}
