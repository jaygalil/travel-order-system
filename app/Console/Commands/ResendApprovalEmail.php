<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Models\TravelOrderApproval;
use App\Mail\TravelOrderApprovalRequest;
use Carbon\Carbon;

class ResendApprovalEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:resend-approval {approvalId} {--to=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resend approval email with updated URL format';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $approvalId = $this->argument('approvalId');
        $emailTo = $this->option('to');
        
        $approval = TravelOrderApproval::find($approvalId);
        
        if (!$approval) {
            $this->error('Approval not found with ID: ' . $approvalId);
            return Command::FAILURE;
        }
        
        // Generate new token
        $token = $approval->generateEmailToken();
        $approvalUrl = route('approval.email.show', ['token' => $token]);
        
        $this->info('Regenerated token: ' . $token);
        $this->info('Approval URL: ' . $approvalUrl);
        
        // Determine email address
        if ($emailTo) {
            $targetEmail = $emailTo;
        } elseif ($approval->approverUser && $approval->approverUser->email) {
            $targetEmail = $approval->approverUser->email;
        } else {
            $this->error('No email address specified and no approver email found.');
            return Command::FAILURE;
        }
        
        try {
            Mail::to($targetEmail)->send(new TravelOrderApprovalRequest(
                $approval->travelOrder,
                $approval,
                $approvalUrl
            ));
            
            $approval->update(['email_sent_at' => Carbon::now()]);
            
            $this->info('✅ Approval email sent successfully to: ' . $targetEmail);
            $this->info('Approver: ' . $approval->approver_name);
            $this->info('Travel Order: ' . $approval->travelOrder->local_travel_order_no);
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('❌ Failed to send email: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
