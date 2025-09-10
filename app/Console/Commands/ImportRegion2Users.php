<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Imports\Region2UsersImport;
use App\Models\User;
use Spatie\Permission\Models\Role;

class ImportRegion2Users extends Command
{
    protected $signature = 'users:import-region2 {file?} {--dry-run : Show what would be imported without actually importing}';
    protected $description = 'Import all 84 users from Region 2 employee CSV file';

    public function handle()
    {
        $file = $this->argument('file') ?? 'region2_employees.csv';
        $dryRun = $this->option('dry-run');
        
        if (!file_exists($file)) {
            $this->error("File not found: {$file}");
            $this->info("Available files:");
            $this->info("- region2_employees.csv (converted from Excel)");
            $this->info("- REGION2 EMPLOYEES DIRECTORY 1.xlsx (original file)");
            return 1;
        }

        try {
            $this->info("Starting import from: {$file}");
            if ($dryRun) {
                $this->warn("DRY RUN MODE - No actual changes will be made");
                return $this->performDryRun($file);
            }
            
            // Ensure required roles exist
            $this->ensureRolesExist();
            
            // Count users before import
            $userCountBefore = User::count();
            $this->info("Current users in database: {$userCountBefore}");
            
            // Perform the import
            $this->info("Importing users...");
            $importer = new Region2UsersImport();
            $stats = $importer->import($file);
            
            // Count users after import
            $userCountAfter = User::count();
            
            // Display results
            $this->newLine();
            $this->info('=== IMPORT COMPLETED ===');
            $this->info("✅ Users imported: {$stats['imported']}");
            $this->info("⚠️  Users skipped: {$stats['skipped']}");
            $this->info("❌ Errors: {$stats['errors']}");
            $this->info("📊 Total users before: {$userCountBefore}");
            $this->info("📊 Total users after: {$userCountAfter}");
            $this->info("🔑 All imported users have password: 12345678!@#");
            
            if ($stats['imported'] > 0) {
                $this->info("\n🎉 Successfully imported {$stats['imported']} new users!");
                $this->info("Users can now log in with their email and password: 12345678!@#");
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('Import failed: ' . $e->getMessage());
            return 1;
        }
    }
    
    private function performDryRun($file)
    {
        $csvData = array_map('str_getcsv', file($file));
        $headers = array_shift($csvData);
        
        $this->info("CSV Headers: " . implode(', ', $headers));
        $this->info("Total rows to process: " . count($csvData));
        
        // Show first 5 rows as sample
        $this->info("\nSample data (first 5 rows):");
        for ($i = 0; $i < min(5, count($csvData)); $i++) {
            $row = array_combine($headers, $csvData[$i]);
            $this->info("Row " . ($i + 2) . ":");
            $this->line("  Name: " . ($row['NAME'] ?? 'N/A'));
            $this->line("  Email: " . ($row['EMAIL ADDRESS'] ?? 'N/A'));
            $this->line("  Position: " . ($row['POSITION'] ?? 'N/A'));
            $this->line("  Office: " . ($row['PROVINCIAL OFFICE'] ?? 'N/A'));
            $this->newLine();
        }
        
        // Count potential duplicates
        $existingEmails = 0;
        $newEmails = 0;
        $generatedEmails = 0;
        
        foreach ($csvData as $rowData) {
            $row = array_combine($headers, $rowData);
            $email = trim($row['EMAIL ADDRESS'] ?? '');
            
            if (empty($email)) {
                $generatedEmails++;
            } else {
                if (User::where('email', $email)->exists()) {
                    $existingEmails++;
                } else {
                    $newEmails++;
                }
            }
        }
        
        $this->info("Email Analysis:");
        $this->info("  📧 Existing emails (will be skipped): {$existingEmails}");
        $this->info("  ✉️  New emails (will be imported): {$newEmails}");
        $this->info("  🔄 Generated emails (no email in file): {$generatedEmails}");
        
        return 0;
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
        
        $this->info('✅ Ensured roles exist: ' . implode(', ', $roles));
    }
}
