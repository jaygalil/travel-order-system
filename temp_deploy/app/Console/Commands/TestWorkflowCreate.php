<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use App\Http\Controllers\WorkflowTemplateController;
use App\Models\User;

class TestWorkflowCreate extends Command
{
    protected $signature = 'workflow:test-create';
    protected $description = 'Test workflow creation via controller';

    public function handle()
    {
        $this->info('Testing workflow creation...');
        
        $user = User::first();
        if (!$user) {
            $this->error('No users found!');
            return;
        }
        
        // Simulate login
        auth()->login($user);
        
        // Create a test request
        $requestData = [
            'name' => 'Test Workflow CLI',
            'description' => 'Test workflow created via CLI',
            'visibility' => 'private',
            'steps' => [
                [
                    'step_name' => 'First Step',
                    'approver_type' => 'user',
                    'approver_user_id' => $user->id,
                    'approver_name' => $user->name,
                    'approver_title' => 'Test User',
                    'action_type' => 'approve',
                    'is_required' => true,
                    'can_delegate' => false,
                ]
            ]
        ];
        
        $request = new Request($requestData);
        $request->setMethod('POST');
        
        try {
            $controller = new WorkflowTemplateController();
            $response = $controller->store($request);
            
            $this->info('✅ Workflow created successfully!');
            $this->info('Response type: ' . get_class($response));
            
        } catch (\Exception $e) {
            $this->error('❌ Failed to create workflow:');
            $this->error($e->getMessage());
            $this->error('Trace: ' . $e->getTraceAsString());
        }
    }
}
