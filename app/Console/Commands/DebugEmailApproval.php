<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TravelOrderApproval;

class DebugEmailApproval extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:email-approval';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug email approval tokens and URLs';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Debugging Email Approval System...');
        
        // Get an approval with email token
        $approval = TravelOrderApproval::whereNotNull('email_token')
            ->where('status', 'pending')
            ->first();
        
        if ($approval) {
            $this->info('Found pending approval with token:');
            $this->info('ID: ' . $approval->id);
            $this->info('Token: ' . $approval->email_token);
            $this->info('Status: ' . $approval->status);
            $this->info('Approver: ' . $approval->approver_name);
            $this->info('Email sent at: ' . ($approval->email_sent_at ? $approval->email_sent_at : 'Not sent'));
            
            // Test the URL generation
            $url = route('approval.email.show', ['token' => $approval->email_token]);
            $this->info('Generated URL: ' . $url);
            
            // Test can approve method
            $canApprove = $approval->canApproveViaEmail($approval->email_token);
            $this->info('Can approve via email: ' . ($canApprove ? 'Yes' : 'No'));
            
            if (!$canApprove) {
                $this->warn('Reasons why cannot approve:');
                $this->warn('- Token match: ' . ($approval->email_token === $approval->email_token ? 'Yes' : 'No'));
                $this->warn('- Status is pending: ' . ($approval->status === 'pending' ? 'Yes' : 'No'));
                $this->warn('- Email sent: ' . ($approval->email_sent_at ? 'Yes' : 'No'));
            }
            
        } else {
            $this->warn('No pending approvals with email tokens found.');
            
            // Check all approvals
            $allApprovals = TravelOrderApproval::all();
            $this->info('Total approvals: ' . $allApprovals->count());
            
            foreach ($allApprovals as $appr) {
                $this->info("Approval ID: {$appr->id}, Status: {$appr->status}, Token: " . ($appr->email_token ? 'Has token' : 'No token'));
            }
        }
        
        // Test creating a new token for the first pending approval
        $pendingApproval = TravelOrderApproval::where('status', 'pending')->first();
        if ($pendingApproval) {
            $this->info("\nTesting token generation for approval ID: " . $pendingApproval->id);
            $token = $pendingApproval->generateEmailToken();
            $this->info('New token generated: ' . $token);
            $url = route('approval.email.show', ['token' => $token]);
            $this->info('New URL: ' . $url);
        }
        
        return Command::SUCCESS;
    }
}
