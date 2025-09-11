<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TravelOrder;
use App\Models\TravelOrderParticipant;
use App\Models\User;

class TestParticipants extends Command
{
    protected $signature = 'travel:test-participants';
    protected $description = 'Test creating a travel order with multiple participants';

    public function handle()
    {
        $this->info('Testing travel order with multiple participants...');
        
        $user = User::first();
        if (!$user) {
            $this->error('No users found!');
            return;
        }
        
        // Create a test travel order
        $travelOrder = TravelOrder::create([
            'local_travel_order_no' => TravelOrder::generateTravelOrderNumber(),
            'user_id' => $user->id,
            'date_of_travel_from' => now()->addDays(7),
            'date_of_travel_to' => now()->addDays(9),
            'prepared_by_user_id' => $user->id,
            'employee_name' => 'John Doe (Primary)',
            'position' => 'Team Lead',
            'division_agency' => 'DICT - ICT Division',
            'source_of_fund' => 'Budget',
            'official_vehicle' => 'Service Vehicle',
            'purpose' => 'Team training and conference attendance',
            'destination' => 'Manila, Philippines',
            'farthest_destination' => 'Manila',
            'approx_distance' => 350.5,
            'status' => 'draft',
        ]);
        
        $this->info("Created travel order: {$travelOrder->local_travel_order_no}");
        
        // Create multiple participants
        $participants = [
            [
                'employee_name' => 'John Doe',
                'employee_id' => 'EMP001',
                'position' => 'Team Lead',
                'division_agency' => 'DICT - ICT Division',
                'phone' => '09123456789',
                'email' => 'john.doe@dict.gov.ph',
                'is_primary' => true,
                'special_requirements' => 'Vegetarian meals',
                'user_id' => $user->id,
            ],
            [
                'employee_name' => 'Jane Smith',
                'employee_id' => 'EMP002',
                'position' => 'Developer',
                'division_agency' => 'DICT - ICT Division',
                'phone' => '09987654321',
                'email' => 'jane.smith@dict.gov.ph',
                'is_primary' => false,
                'special_requirements' => 'Window seat preference',
                'user_id' => null,
            ],
            [
                'employee_name' => 'Bob Wilson',
                'employee_id' => 'EMP003',
                'position' => 'System Admin',
                'division_agency' => 'DICT - ICT Division',
                'phone' => '09555666777',
                'email' => 'bob.wilson@dict.gov.ph',
                'is_primary' => false,
                'special_requirements' => null,
                'user_id' => null,
            ],
        ];
        
        foreach ($participants as $participantData) {
            TravelOrderParticipant::create([
                'travel_order_id' => $travelOrder->id,
                'user_id' => $participantData['user_id'],
                'employee_name' => $participantData['employee_name'],
                'employee_id' => $participantData['employee_id'],
                'position' => $participantData['position'],
                'division_agency' => $participantData['division_agency'],
                'phone' => $participantData['phone'],
                'email' => $participantData['email'],
                'is_primary' => $participantData['is_primary'],
                'special_requirements' => $participantData['special_requirements'],
            ]);
        }
        
        $this->info('✅ Created travel order with ' . count($participants) . ' participants');
        
        // Display the results
        $travelOrder->load('participants');
        
        $this->info('Travel Order: ' . $travelOrder->local_travel_order_no);
        $this->info('Purpose: ' . $travelOrder->purpose);
        $this->info('Destination: ' . $travelOrder->destination);
        $this->info('');
        
        $this->info('Participants:');
        foreach ($travelOrder->participants as $participant) {
            $this->info('  • ' . $participant->employee_name . 
                       ($participant->is_primary ? ' (Primary)' : '') .
                       ' - ' . $participant->position .
                       ' - ' . $participant->email);
        }
    }
}
