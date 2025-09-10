<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UsersImport
{
    public function import($filePath)
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File not found: {$filePath}");
        }

        $csvData = array_map('str_getcsv', file($filePath));
        $headers = array_shift($csvData); // Get headers
        
        foreach ($csvData as $rowData) {
            if (empty($rowData) || count($rowData) < count($headers)) {
                continue; // Skip empty or incomplete rows
            }
            
            // Combine headers with row data
            $row = array_combine($headers, $rowData);
            
            // Skip empty rows
            if (empty($row['name']) || empty($row['email'])) {
                continue;
            }

            // Check if user already exists
            $existingUser = User::where('email', $row['email'])->first();
            if ($existingUser) {
                echo "User already exists: {$row['email']}\n";
                continue; // Skip existing users
            }

            // Create user
            $user = User::create([
                'name' => $row['name'],
                'email' => $row['email'],
                'email_verified_at' => now(),
                'password' => Hash::make('12345678!@#'), // Default password
                'position' => $row['position'] ?? '',
            ]);

            // Assign role
            $roleName = $row['role'] ?? 'employee';
            
            // Ensure role exists
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $user->assignRole($role);

            // Store additional data for approvers
            if ($roleName === 'approver' && !empty($row['sequence'])) {
                $user->update([
                    'approver_sequence' => (int)$row['sequence'],
                    'approver_title' => $row['approver_title'] ?? $row['position'],
                ]);
            }

            echo "Created user: {$user->name} ({$user->email}) with role: {$roleName}\n";
        }
    }
}
