<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\TravelOrderCreated;
use App\Mail\TravelOrderStatusUpdate;
use App\Mail\TravelOrderApprovalRequest;
use App\Models\TravelOrder;
use App\Models\TravelOrderApproval;
use App\Models\User;

class TestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test {type=created} {--to=admin@test.com}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email functionality with sample data';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $type = $this->argument('type');
        $toEmail = $this->option('to');
        
        $this->info('Testing email functionality...');
        $this->info('Email type: ' . $type);
        $this->info('Sending to: ' . $toEmail);
        
        try {
            switch ($type) {
                case 'created':
                    $this->testCreatedEmail($toEmail);
                    break;
                case 'status':
                    $this->testStatusEmail($toEmail);
                    break;
                case 'approval':
                    $this->testApprovalEmail($toEmail);
                    break;
                default:
                    $this->error('Invalid email type. Use: created, status, or approval');
                    return 1;
            }
            
            $this->info('Email sent successfully!');
            return 0;
            
        } catch (\Exception $e) {
            $this->error('Failed to send email: ' . $e->getMessage());
            return 1;
        }
    }
    
    private function testCreatedEmail($to)
    {
        // Get or create a sample travel order
        $travelOrder = TravelOrder::first();
        if (!$travelOrder) {
            $this->error('No travel orders found. Please create a travel order first.');
            return;
        }
        
        Mail::to($to)->send(new TravelOrderCreated($travelOrder));
        $this->info('Travel Order Created email sent!');
    }
    
    private function testStatusEmail($to)
    {
        $travelOrder = TravelOrder::first();
        if (!$travelOrder) {
            $this->error('No travel orders found. Please create a travel order first.');
            return;
        }
        
        Mail::to($to)->send(new TravelOrderStatusUpdate(
            $travelOrder, 
            'draft', 
            'This is a test status update message.'
        ));
        $this->info('Travel Order Status Update email sent!');
    }
    
    private function testApprovalEmail($to)
    {
        $approval = TravelOrderApproval::with('travelOrder')->first();
        if (!$approval) {
            $this->error('No approvals found. Please create a travel order first.');
            return;
        }
        
        $approvalUrl = 'http://localhost:8000/approval/test-token';
        
        Mail::to($to)->send(new TravelOrderApprovalRequest(
            $approval->travelOrder, 
            $approval, 
            $approvalUrl
        ));
        $this->info('Travel Order Approval Request email sent!');
    }
}
