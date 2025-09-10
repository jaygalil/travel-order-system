<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Imports\UsersImport;
use Illuminate\Support\Facades\File;

class ImportUsersFromExcel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:import {file?} {--sample}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import users from Excel/CSV file or create sample data';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->option('sample')) {
            return $this->importSampleData();
        }

        $file = $this->argument('file');
        
        if (!$file) {
            $this->error('Please provide a file path or use --sample flag for sample data.');
            $this->info('Usage: php artisan users:import /path/to/users.xlsx');
            $this->info('Or: php artisan users:import --sample');
            $this->info('Template available at: storage/templates/users_template.csv');
            return 1;
        }

        if (!File::exists($file)) {
            $this->error("File not found: {$file}");
            return 1;
        }

        try {
            $this->info('Starting import...');
            $importer = new UsersImport();
            $importer->import($file);
            $this->info('Users imported successfully!');
            return 0;
        } catch (\Exception $e) {
            $this->error('Import failed: ' . $e->getMessage());
            return 1;
        }
    }

    private function importSampleData()
    {
        $templateFile = storage_path('templates/users_template.csv');
        
        if (!File::exists($templateFile)) {
            $this->error('Template file not found at: ' . $templateFile);
            return 1;
        }

        try {
            $this->info('Importing sample users from template...');
            $importer = new UsersImport();
            $importer->import($templateFile);
            $this->info('Sample users imported successfully!');
            $this->info('Default password for all users: 12345678!@#');
            return 0;
        } catch (\Exception $e) {
            $this->error('Sample import failed: ' . $e->getMessage());
            return 1;
        }
    }
}
