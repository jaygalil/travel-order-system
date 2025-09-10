<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Models\TravelOrder;
use App\Models\TravelOrderApproval;
use App\Models\User;
use App\Mail\TravelOrderCreated;
use App\Mail\TravelOrderApprovalRequest;
use App\Mail\TravelOrderStatusUpdate;
use Carbon\Carbon;

class TestEmailSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test {type} {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email functionality for the travel order system';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $type = $this->argument('type');
        $email = $this->argument('email');

        $this->info("Testing {$type} email to {$email}...");

        try {
            switch ($type) {
                case 'created':
                    $this->testTravelOrderCreatedEmail($email);
                    break;
                    
                case 'approval':
                    $this->testApprovalRequestEmail($email);
                    break;
                    
                case 'status':
                    $this->testStatusUpdateEmail($email);
                    break;
                    
                default:
                    $this->error('Invalid email type. Use: created, approval, or status');
                    return 1;
            }
            
            $this->info("✅ Email sent successfully!");
            return 0;
            
        } catch (\Exception $e) {
            $this->error("❌ Email failed: " . $e->getMessage());
            return 1;
        }
    }

    private function testTravelOrderCreatedEmail($email)
    {
        $travelOrder = $this->createMockTravelOrder();
        Mail::to($email)->send(new TravelOrderCreated($travelOrder));
    }

    private function testApprovalRequestEmail($email)
    {
        $travelOrder = $this->createMockTravelOrder();
        $approval = $this->createMockApproval($travelOrder);
        $approvalUrl = route('approval.email.show', ['token' => 'test-token']);
        
        Mail::to($email)->send(new TravelOrderApprovalRequest($travelOrder, $approval, $approvalUrl));
    }

    private function testStatusUpdateEmail($email)
    {
        $travelOrder = $this->createMockTravelOrder();
        $travelOrder->status = 'approved';
        
        Mail::to($email)->send(new TravelOrderStatusUpdate($travelOrder, 'pending_approval', 'Your travel order has been approved!'));
    }

    private function createMockTravelOrder()
    {
        // Create a mock travel order for testing (doesn't save to DB)
        $travelOrder = new TravelOrder([
            'local_travel_order_no' => 'TO-' . date('Y') . '-TEST-001',
            'employee_name' => 'John Doe',
            'position' => 'Software Developer',
            'division_agency' => 'IT Department',
            'date_of_travel_from' => Carbon::now()->addDays(7),
            'date_of_travel_to' => Carbon::now()->addDays(10),
            'purpose' => 'Attend software development conference and training workshop',
            'destination' => 'Manila, Philippines',
            'farthest_destination' => 'Makati City, Metro Manila',
            'source_of_fund' => 'Government Appropriation',
            'official_vehicle' => 'Service Vehicle',
            'approx_distance' => 450.50,
            'status' => 'draft'
        ]);
        
        // Set mock attributes that aren't fillable
        $travelOrder->id = 999;
        $travelOrder->created_at = Carbon::now();
        $travelOrder->updated_at = Carbon::now();
        
        return $travelOrder;
    }

    private function createMockApproval($travelOrder)
    {
        // Create a mock approval for testing (doesn't save to DB)
        $approval = new TravelOrderApproval([
            'travel_order_id' => $travelOrder->id,
            'sequence' => 1,
            'approval_level' => 1,
            'approver_name' => 'Jane Smith',
            'approver_title' => 'Department Head',
            'approver_position' => 'Manager',
            'status' => 'pending',
            'is_required' => true,
            'step_description' => 'Department Head Approval'
        ]);
        
        // Set mock travel order relationship
        $approval->setRelation('travelOrder', $travelOrder);
        
        return $approval;
    }
}
