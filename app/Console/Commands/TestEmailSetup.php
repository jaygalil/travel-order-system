<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\TravelOrderCreated;
use App\Models\TravelOrder;
use App\Models\User;

class TestEmailSetup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:email {--to=jaymar.recolizado@dict.gov.ph}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email configuration with threading';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Testing email configuration...');
        
        // Create a test travel order (not saved to database)
        $testTravelOrder = new TravelOrder([
            'id' => 999,
            'local_travel_order_no' => 'TEST-' . date('Y-m-d-H-i-s'),
            'user_id' => 1,
            'employee_name' => 'Test Employee',
            'position' => 'Test Position',
            'division_agency' => 'Test Department',
            'destination' => 'Test Destination',
            'farthest_destination' => 'Test Farthest Destination',
            'purpose' => 'Testing email functionality',
            'date_of_travel_from' => now()->addDays(7),
            'date_of_travel_to' => now()->addDays(9),
            'status' => 'draft',
            'created_at' => now()
        ]);
        
        try {
            $toEmail = $this->option('to');
            $this->info("Sending test email to: {$toEmail}");
            
            Mail::to($toEmail)->send(new TravelOrderCreated($testTravelOrder));
            
            $this->info('✅ Test email sent successfully!');
            $this->info('Check your inbox for the email with threading headers.');
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('❌ Failed to send test email:');
            $this->error($e->getMessage());
            
            return Command::FAILURE;
        }
    }
}
