<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Imports\UsersImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class ImportExcelUsers extends Command
{
    protected $signature = 'users:import-excel {file} {--dry-run : Show what would be imported without actually importing}';
    protected $description = 'Import users from Excel file by converting to CSV first';

    public function handle()
    {
        $file = $this->argument('file');
        $dryRun = $this->option('dry-run');
        
        if (!File::exists($file)) {
            $this->error("File not found: {$file}");
            return 1;
        }

        try {
            $this->info("Starting import from: {$file}");
            if ($dryRun) {
                $this->warn("DRY RUN MODE - No actual changes will be made");
            }
            
            // Create required roles if they don't exist
            $this->ensureRolesExist();
            
            // Convert Excel to array
            $this->info("Reading Excel file...");
            $data = Excel::toArray([], $file)[0]; // First worksheet
            $headers = array_shift($data); // Remove header row
            
            $this->info("Headers found: " . implode(', ', $headers));
            $this->info("Total rows to process: " . count($data));
            
            if ($dryRun) {
                // Show first 3 rows for dry run
                for ($i = 0; $i < min(3, count($data)); $i++) {
                    $row = array_combine($headers, $data[$i]);
                    $this->info("Sample row " . ($i + 1) . ":");
                    foreach ($row as $key => $value) {
                        $this->line("  {$key}: {$value}");
                    }
                    $this->newLine();
                }
                return 0;
            }
            
            // Count users before import
            $userCountBefore = User::count();
            $importedCount = 0;
            $skippedCount = 0;
            $errorCount = 0;
            
            // Process each row
            foreach ($data as $rowIndex => $rowData) {
                try {
                    $row = array_combine($headers, $rowData);
                    
                    // Skip empty rows
                    if (empty($row) || $this->isEmptyRow($row)) {
                        $skippedCount++;
                        continue;
                    }
                    
                    $result = $this->createUserFromRow($row, $rowIndex + 2);
                    if ($result === 'created') {
                        $importedCount++;
                    } elseif ($result === 'skipped') {
                        $skippedCount++;
                    } else {
                        $errorCount++;
                    }
                    
                } catch (\Exception $e) {
                    $this->error("Error processing row " . ($rowIndex + 2) . ": " . $e->getMessage());
                    $errorCount++;
                }
            }
            
            // Assign roles to newly created users
            $this->assignRolesToNewUsers();
            
            // Show import statistics
            $userCountAfter = User::count();
            
            $this->newLine();
            $this->info('Import completed!');
            $this->info("Users imported: {$importedCount}");
            $this->info("Users skipped: {$skippedCount}");
            $this->info("Errors: {$errorCount}");
            $this->info("Total users before: {$userCountBefore}");
            $this->info("Total users after: {$userCountAfter}");
            $this->info("All imported users have password: 12345678!@#");
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('Import failed: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }
    }
    
    private function ensureRolesExist()
    {
        $roles = ['employee', 'approver', 'admin'];
        
        foreach ($roles as $roleName) {
            Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web'
            ]);
        }
        
        $this->info('Ensured roles exist: ' . implode(', ', $roles));
    }
    
    private function assignRolesToNewUsers()
    {
        // Get all users without roles
        $usersWithoutRoles = User::doesntHave('roles')->get();
        
        foreach ($usersWithoutRoles as $user) {
            // Determine role based on position
            $role = $this->determineUserRole($user);
            $user->assignRole($role);
            $this->info("Assigned role '{$role}' to user: {$user->name}");
        }
        
        $this->info("Assigned roles to {$usersWithoutRoles->count()} users");
    }
    
    private function determineUserRole($user)
    {
        $position = strtolower($user->position ?? '');
        
        // Management/Director roles
        if (str_contains($position, 'director') || 
            str_contains($position, 'manager') || 
            str_contains($position, 'head') ||
            str_contains($position, 'chief') ||
            str_contains($position, 'supervisor')) {
            return 'approver';
        }
        
        // Admin roles
        if (str_contains($position, 'admin') || 
            str_contains($position, 'it ') ||
            str_contains($position, 'system')) {
            return 'admin';
        }
        
        // Default to employee
        return 'employee';
    }
    
    private function isEmptyRow($row)
    {
        // Check if all important fields are empty
        $nameFields = ['name', 'fullname', 'full_name', 'employee_name', 'Name', 'Full Name', 'Employee Name'];
        
        foreach ($nameFields as $field) {
            if (!empty($row[$field] ?? '')) {
                return false;
            }
        }
        
        return true;
    }
    
    private function createUserFromRow($row, $rowNumber)
    {
        // Map potential column names to standardized values
        $name = $this->getValueFromRow($row, ['name', 'fullname', 'full_name', 'employee_name', 'Name', 'Full Name', 'Employee Name']);
        $email = $this->getValueFromRow($row, ['email', 'email_address', 'Email', 'Email Address']);
        $position = $this->getValueFromRow($row, ['position', 'title', 'job_title', 'designation', 'Position', 'Title', 'Job Title', 'Designation']);
        
        if (empty($name)) {
            $this->warn("Row {$rowNumber}: Skipping - no name found");
            return 'skipped';
        }
        
        // Generate email if missing
        if (empty($email)) {
            $email = $this->generateEmailFromName($name);
            $this->info("Row {$rowNumber}: Generated email '{$email}' from name '{$name}'");
        }
        
        // Check if user already exists
        $existingUser = User::where('email', $email)->first();
        if ($existingUser) {
            $this->warn("Row {$rowNumber}: User already exists: {$email}");
            return 'skipped';
        }
        
        try {
            // Create user
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'email_verified_at' => now(),
                'password' => Hash::make('12345678!@#'),
                'position' => $position,
                'employee_id' => $this->getValueFromRow($row, ['employee_id', 'id', 'emp_id', 'Employee ID', 'ID']),
                'division_agency' => $this->getValueFromRow($row, ['division', 'agency', 'department', 'Division', 'Agency', 'Department']),
                'phone' => $this->getValueFromRow($row, ['phone', 'contact', 'mobile', 'Phone', 'Contact', 'Mobile']),
                'is_active' => true,
            ]);
            
            $this->info("Row {$rowNumber}: Created user: {$name} ({$email})");
            return 'created';
            
        } catch (\Exception $e) {
            $this->error("Row {$rowNumber}: Failed to create user: " . $e->getMessage());
            return 'error';
        }
    }
    
    private function getValueFromRow($row, $possibleKeys)
    {
        foreach ($possibleKeys as $key) {
            if (isset($row[$key]) && !empty(trim($row[$key]))) {
                return trim($row[$key]);
            }
        }
        return '';
    }
    
    private function generateEmailFromName($name)
    {
        // Handle formats like "Last, First M." or "First Last"
        $name = trim($name);
        
        if (strpos($name, ',') !== false) {
            // Format: "Last, First M."
            $parts = explode(',', $name);
            $lastName = trim($parts[0]);
            $firstPart = trim($parts[1] ?? '');
            $firstName = explode(' ', $firstPart)[0] ?? '';
        } else {
            // Format: "First Last" or "First Middle Last"
            $parts = explode(' ', $name);
            $firstName = $parts[0] ?? '';
            $lastName = end($parts);
        }

        $email = strtolower($firstName . '.' . $lastName);
        $email = str_replace(' ', '', $email);
        $email = preg_replace('/[^a-z0-9.]/', '', $email);
        
        return $email . '@dict.gov.ph';
    }
}
