<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\WorkflowTemplate;
use App\Models\WorkflowStep;

class CreateTestWorkflows extends Command
{
    protected $signature = 'workflows:create-test';
    protected $description = 'Create test workflow templates for demonstration';

    public function handle()
    {
        $this->info('Creating test workflow templates...');
        
        $adminUser = User::where('email', 'admin@travelorder.local')->first();
        if (!$adminUser) {
            $adminUser = User::first();
        }
        
        if (!$adminUser) {
            $this->error('No users found! Please create users first.');
            return;
        }
        
        // Template 1: Simple Two-Step Approval
        $template1 = WorkflowTemplate::create([
            'name' => 'Simple Two-Step Approval',
            'description' => 'A simple workflow with manager and director approval',
            'visibility' => 'public',
            'created_by_user_id' => $adminUser->id,
            'is_active' => true,
            'is_default' => true
        ]);
        
        WorkflowStep::create([
            'workflow_template_id' => $template1->id,
            'sequence' => 1,
            'step_name' => 'Manager Review',
            'step_description' => 'Initial review by direct manager',
            'approver_type' => 'position',
            'approver_position' => 'Human Resources Manager',
            'approver_name' => 'Department Manager',
            'approver_title' => 'Manager',
            'action_type' => 'approve',
            'is_required' => true,
            'can_delegate' => false
        ]);
        
        WorkflowStep::create([
            'workflow_template_id' => $template1->id,
            'sequence' => 2,
            'step_name' => 'Director Approval',
            'step_description' => 'Final approval by director',
            'approver_type' => 'position',
            'approver_position' => 'Director',
            'approver_name' => 'Department Director',
            'approver_title' => 'Director',
            'action_type' => 'approve',
            'is_required' => true,
            'can_delegate' => true
        ]);
        
        // Template 2: Comprehensive Four-Step Process
        $template2 = WorkflowTemplate::create([
            'name' => 'Comprehensive Review Process',
            'description' => 'Full workflow with supervisor, HR, finance, and director approval',
            'visibility' => 'public',
            'created_by_user_id' => $adminUser->id,
            'is_active' => true
        ]);
        
        WorkflowStep::create([
            'workflow_template_id' => $template2->id,
            'sequence' => 1,
            'step_name' => 'Supervisor Review',
            'step_description' => 'Initial review by immediate supervisor',
            'approver_type' => 'user',
            'approver_user_id' => optional(User::where('email', 'ronald.bariuan@dict.gov.ph')->first())->id,
            'approver_name' => 'Ronald S. Bariuan',
            'approver_title' => 'Provincial Officer',
            'action_type' => 'review',
            'is_required' => true,
            'can_delegate' => false
        ]);
        
        WorkflowStep::create([
            'workflow_template_id' => $template2->id,
            'sequence' => 2,
            'step_name' => 'HR Verification',
            'step_description' => 'Human resources policy verification',
            'approver_type' => 'user',
            'approver_user_id' => optional(User::where('email', 'jayfer.ammasi@dict.gov.ph')->first())->id,
            'approver_name' => 'Jayfer T. Ammasi',
            'approver_title' => 'Human Resources',
            'action_type' => 'verify',
            'is_required' => true,
            'can_delegate' => false
        ]);
        
        WorkflowStep::create([
            'workflow_template_id' => $template2->id,
            'sequence' => 3,
            'step_name' => 'Finance Review',
            'step_description' => 'Budget and finance verification',
            'approver_type' => 'user',
            'approver_user_id' => optional(User::where('email', 'minaflor.villafuerte@dict.gov.ph')->first())->id,
            'approver_name' => 'Mina Flor T. Villafuerte',
            'approver_title' => 'Verification Officer',
            'action_type' => 'verify',
            'is_required' => true,
            'can_delegate' => false
        ]);
        
        WorkflowStep::create([
            'workflow_template_id' => $template2->id,
            'sequence' => 4,
            'step_name' => 'Final Approval',
            'step_description' => 'Final authorization by director',
            'approver_type' => 'user',
            'approver_user_id' => optional(User::where('email', 'pinky.jimenez@dict.gov.ph')->first())->id,
            'approver_name' => 'Pinky T. Jimenez',
            'approver_title' => 'Final Approver',
            'action_type' => 'approve',
            'is_required' => true,
            'can_delegate' => true
        ]);
        
        // Template 3: Department-specific workflow
        $template3 = WorkflowTemplate::create([
            'name' => 'IT Department Workflow',
            'description' => 'Specialized workflow for IT department travel requests',
            'visibility' => 'department',
            'department' => 'IT',
            'created_by_user_id' => $adminUser->id,
            'is_active' => true
        ]);
        
        WorkflowStep::create([
            'workflow_template_id' => $template3->id,
            'sequence' => 1,
            'step_name' => 'Technical Review',
            'step_description' => 'Technical necessity and impact assessment',
            'approver_type' => 'role',
            'approver_role' => 'admin',
            'approver_name' => 'IT Administrator',
            'approver_title' => 'System Administrator',
            'action_type' => 'review',
            'is_required' => true,
            'can_delegate' => false
        ]);
        
        WorkflowStep::create([
            'workflow_template_id' => $template3->id,
            'sequence' => 2,
            'step_name' => 'Budget Approval',
            'step_description' => 'IT budget allocation approval',
            'approver_type' => 'user',
            'approver_user_id' => optional(User::where('email', 'magdalena.gomez@dict.gov.ph')->first())->id,
            'approver_name' => 'Magdalena D. Gomez',
            'approver_title' => 'Recommending Approval',
            'action_type' => 'approve',
            'is_required' => true,
            'can_delegate' => false
        ]);
        
        $this->info('✅ Created 3 test workflow templates:');
        $this->info('1. Simple Two-Step Approval (Public, Default)');
        $this->info('2. Comprehensive Review Process (Public)');
        $this->info('3. IT Department Workflow (Department: IT)');
        $this->info('');
        $this->info('📋 Total workflow templates: ' . WorkflowTemplate::count());
        $this->info('⚙️ Total workflow steps: ' . WorkflowStep::count());
    }
}
