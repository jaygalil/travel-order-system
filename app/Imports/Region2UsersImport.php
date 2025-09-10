<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class Region2UsersImport
{
    private $importedCount = 0;
    private $skippedCount = 0;
    private $errorCount = 0;

    public function import($filePath)
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File not found: {$filePath}");
        }

        $csvData = array_map('str_getcsv', file($filePath));
        $headers = array_shift($csvData); // Get headers
        
        // Expected headers: POSITION, DESIGNATION/PROJECT, NAME, EMAIL ADDRESS, PROVINCIAL OFFICE
        echo "Headers: " . implode(', ', $headers) . "\n";
        
        foreach ($csvData as $rowIndex => $rowData) {
            if (empty($rowData) || count($rowData) < count($headers)) {
                continue; // Skip empty or incomplete rows
            }
            
            // Combine headers with row data
            $row = array_combine($headers, $rowData);
            
            // Skip rows without essential data
            if (empty($row['NAME']) || empty(trim($row['NAME']))) {
                echo "Row " . ($rowIndex + 2) . ": Skipping - no name\n";
                $this->skippedCount++;
                continue;
            }
            
            try {
                $result = $this->createUser($row, $rowIndex + 2);
                if ($result === 'created') {
                    $this->importedCount++;
                } elseif ($result === 'skipped') {
                    $this->skippedCount++;
                } else {
                    $this->errorCount++;
                }
            } catch (\Exception $e) {
                echo "Row " . ($rowIndex + 2) . ": Error - " . $e->getMessage() . "\n";
                $this->errorCount++;
            }
        }
        
        return [
            'imported' => $this->importedCount,
            'skipped' => $this->skippedCount,
            'errors' => $this->errorCount,
        ];
    }
    
    private function createUser($row, $rowNumber)
    {
        $name = trim($row['NAME']);
        $email = trim($row['EMAIL ADDRESS'] ?? '');
        $position = trim($row['POSITION'] ?? '');
        $designation = trim($row['DESIGNATION/PROJECT'] ?? '');
        $location = trim($row['PROVINCIAL OFFICE'] ?? '');
        
        // Generate email if missing
        if (empty($email)) {
            $email = $this->generateEmailFromName($name);
            echo "Row {$rowNumber}: Generated email '{$email}' for '{$name}'\n";
        }
        
        // Check if user already exists
        $existingUser = User::where('email', $email)->first();
        if ($existingUser) {
            echo "Row {$rowNumber}: User already exists: {$email}\n";
            return 'skipped';
        }
        
        // Create user
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'email_verified_at' => now(),
            'password' => Hash::make('12345678!@#'), // Default password
            'position' => $position,
            'division_agency' => $location,
            'is_active' => true,
        ]);
        
        // Assign role based on position
        $roleName = $this->determineRole($position, $designation);
        
        // Ensure role exists
        $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        $user->assignRole($role);
        
        echo "Row {$rowNumber}: Created user: {$name} ({$email}) with role: {$roleName}\n";
        
        return 'created';
    }
    
    private function generateEmailFromName($name)
    {
        // Handle formats like "LAST FIRST MIDDLE" or "FIRST LAST"
        $name = strtolower(trim($name));
        $parts = explode(' ', $name);
        
        if (count($parts) >= 2) {
            // Use first and last parts
            $firstName = $parts[0];
            $lastName = end($parts);
        } else {
            // Single name, use as both
            $firstName = $parts[0];
            $lastName = $parts[0];
        }
        
        // Clean up names
        $firstName = preg_replace('/[^a-z0-9]/', '', $firstName);
        $lastName = preg_replace('/[^a-z0-9]/', '', $lastName);
        
        return $firstName . '.' . $lastName . '@dict.gov.ph';
    }
    
    private function determineRole($position, $designation)
    {
        $pos = strtolower($position);
        $des = strtolower($designation);
        
        // Check for management positions
        if (str_contains($pos, 'director') || 
            str_contains($pos, 'manager') || 
            str_contains($pos, 'head') ||
            str_contains($pos, 'chief') ||
            str_contains($pos, 'supervisor') ||
            str_contains($des, 'manager') ||
            str_contains($des, 'head') ||
            str_contains($des, 'supervisor')) {
            return 'approver';
        }
        
        // Check for admin/IT positions
        if (str_contains($pos, 'admin') || 
            str_contains($pos, 'it ') ||
            str_contains($pos, 'system') ||
            str_contains($des, 'admin') ||
            str_contains($des, 'system')) {
            return 'admin';
        }
        
        // Default to employee
        return 'employee';
    }
    
    public function getImportStats()
    {
        return [
            'imported' => $this->importedCount,
            'skipped' => $this->skippedCount,
            'errors' => $this->errorCount,
        ];
    }
}
