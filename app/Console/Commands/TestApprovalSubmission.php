<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TravelOrderApproval;

class TestApprovalSubmission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:approval-submission {token} {action} {--comments=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test approval submission with given token and action';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $token = $this->argument('token');
        $action = $this->argument('action');
        $comments = $this->option('comments');
        
        $this->info('Testing approval submission...');
        $this->info('Token: ' . $token);
        $this->info('Action: ' . $action);
        $this->info('Comments: ' . ($comments ?: 'None'));
        
        // Find the approval
        $approval = TravelOrderApproval::where('email_token', $token)
            ->where('status', 'pending')
            ->first();
            
        if (!$approval) {
            $this->error('No pending approval found with this token');
            return Command::FAILURE;
        }
        
        $this->info('Found approval:');
        $this->info('ID: ' . $approval->id);
        $this->info('Approver: ' . $approval->approver_name);
        $this->info('Travel Order: ' . $approval->travelOrder->local_travel_order_no);
        $this->info('Can approve via email: ' . ($approval->canApproveViaEmail($token) ? 'Yes' : 'No'));
        
        // Test the processing
        try {
            $approval->processAction($action, $comments);
            $this->info('✅ Approval processed successfully!');
            $this->info('New status: ' . $approval->status);
            
        } catch (\Exception $e) {
            $this->error('❌ Failed to process approval: ' . $e->getMessage());
            return Command::FAILURE;
        }
        
        return Command::SUCCESS;
    }
}
