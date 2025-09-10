<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\WorkflowTemplate;
use App\Models\TravelOrder;
use Spatie\Permission\Models\Role;

class SystemStatus extends Command
{
    protected $signature = 'system:status';
    protected $description = 'Display system status for testing';

    public function handle()
    {
        $this->info('=== TRAVEL ORDER SYSTEM STATUS ===');
        $this->info('');
        
        // Users
        $userCount = User::count();
        $this->info("👥 Users: {$userCount}");
        
        // Roles
        $roles = Role::pluck('name');
        $this->info("🎭 Roles: " . $roles->implode(', '));
        if ($roles->isEmpty()) {
            $this->warn("No roles found! Creating default roles...");
            $this->createDefaultRoles();
        }
        
        // Workflow Templates
        $templateCount = WorkflowTemplate::count();
        $this->info("📋 Workflow Templates: {$templateCount}");
        
        // Travel Orders
        $orderCount = TravelOrder::count();
        $this->info("✈️ Travel Orders: {$orderCount}");
        
        $this->info('');
        $this->info('=== TEST CREDENTIALS ===');
        $this->info('🔑 Default password for all users: 12345678!@#');
        $this->info('');
        $this->info('Sample login accounts:');
        $this->info('• admin@travelorder.local (System Administrator)');
        $this->info('• john.doe@travelorder.local (IT Specialist)');
        $this->info('• ronald.bariuan@dict.gov.ph (Provincial Officer)');
        
        $this->info('');
        $this->info('🌐 Application URL: http://127.0.0.1:8000');
        $this->info('📁 Workflow Management: http://127.0.0.1:8000/workflows');
    }
    
    private function createDefaultRoles()
    {
        $defaultRoles = ['admin', 'employee', 'approver', 'manager'];
        
        foreach ($defaultRoles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
            $this->info("Created role: {$roleName}");
        }
        
        // Assign admin role to first user
        $adminUser = User::first();
        if ($adminUser) {
            $adminUser->assignRole('admin');
            $this->info("Assigned admin role to: {$adminUser->name}");
        }
    }
}
