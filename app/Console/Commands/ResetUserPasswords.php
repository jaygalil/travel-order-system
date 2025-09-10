<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ResetUserPasswords extends Command
{
    protected $signature = 'users:reset-passwords {password=12345678!@#}';
    protected $description = 'Reset all user passwords to a default password for testing';

    public function handle()
    {
        $password = $this->argument('password');
        $hashedPassword = Hash::make($password);
        
        $userCount = User::count();
        
        if ($userCount === 0) {
            $this->info('No users found in the database.');
            return;
        }
        
        $this->info("Found {$userCount} users. Updating passwords...");
        
        User::query()->update(['password' => $hashedPassword]);
        
        $this->info("Successfully updated passwords for {$userCount} users.");
        $this->info("Default password: {$password}");
        
        // Display current users
        $this->info("\nCurrent users in the system:");
        $users = User::select('id', 'name', 'email', 'position')->get();
        
        $this->table(
            ['ID', 'Name', 'Email', 'Position'],
            $users->map(function ($user) {
                return [$user->id, $user->name, $user->email, $user->position ?? 'N/A'];
            })->toArray()
        );
    }
}
