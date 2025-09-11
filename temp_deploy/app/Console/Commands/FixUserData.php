<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class FixUserData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:fix-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix missing user data for position and division_agency';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Fixing user data...');
        
        $usersWithoutPosition = User::whereNull('position')->orWhere('position', '')->get();
        $usersWithoutDivision = User::whereNull('division_agency')->orWhere('division_agency', '')->get();
        
        $this->info("Found {$usersWithoutPosition->count()} users without position");
        $this->info("Found {$usersWithoutDivision->count()} users without division_agency");
        
        // Update users without position
        foreach ($usersWithoutPosition as $user) {
            $user->update(['position' => 'Staff']);
        }
        
        // Update users without division_agency
        foreach ($usersWithoutDivision as $user) {
            $user->update(['division_agency' => 'REGIONAL OFFICE']);
        }
        
        // Update specific users with proper data if they exist
        $specialUsers = [
            'admin@example.com' => ['position' => 'System Administrator', 'division_agency' => 'Information Technology Division'],
            'jane.smith@example.com' => ['position' => 'Department Head', 'division_agency' => 'Human Resources Division'],
            'john.doe@example.com' => ['position' => 'Software Developer', 'division_agency' => 'Information Technology Division'],
            'maria.santos@example.com' => ['position' => 'Finance Manager', 'division_agency' => 'Finance Division'],
            'robert.garcia@example.com' => ['position' => 'Division Chief', 'division_agency' => 'Operations Division'],
            'lisa.wong@example.com' => ['position' => 'Project Manager', 'division_agency' => 'Information Technology Division'],
        ];
        
        foreach ($specialUsers as $email => $data) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->update($data);
                $this->info("Updated {$email}: {$data['position']} - {$data['division_agency']}");
            }
        }
        
        $this->info('✅ User data fixed successfully!');
        return 0;
    }
}
