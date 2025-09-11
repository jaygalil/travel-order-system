<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;

class InspectExcel extends Command
{
    protected $signature = 'excel:inspect {file}';
    protected $description = 'Inspect Excel file structure';

    public function handle()
    {
        $file = $this->argument('file');
        
        if (!file_exists($file)) {
            $this->error("File not found: $file");
            return 1;
        }

        try {
            $this->info("Inspecting $file...\n");
            
            // Get worksheet names
            $worksheets = Excel::worksheetNames($file);
            $this->info("Worksheets found:");
            foreach ($worksheets as $index => $name) {
                $this->line("  [$index] $name");
            }
            $this->newLine();
            
            // Get headers from first worksheet
            $this->info("Headers from first worksheet:");
            $headings = (new HeadingRowImport)->toArray($file);
            if (!empty($headings[0])) {
                $headers = $headings[0][0]; // First worksheet, first row
                foreach ($headers as $index => $header) {
                    $this->line("  [$index] '$header'");
                }
            }
            $this->newLine();
            
            // Get first 3 rows of data
            $this->info("First 3 rows of data:");
            $data = Excel::toArray([], $file)[0]; // First worksheet
            $headerRow = array_shift($data); // Remove header row
            
            for ($i = 0; $i < min(3, count($data)); $i++) {
                $this->info("Row " . ($i + 2) . ":");
                foreach ($headerRow as $colIndex => $header) {
                    $value = $data[$i][$colIndex] ?? '';
                    $this->line("  '$header': '$value'");
                }
                $this->newLine();
            }
            
            $this->info("Total data rows (excluding header): " . count($data));
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            return 1;
        }
    }
}
