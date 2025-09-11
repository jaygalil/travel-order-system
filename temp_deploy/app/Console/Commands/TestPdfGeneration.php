<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TravelOrder;
use App\Http\Controllers\PDFController;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TestPdfGeneration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:pdf';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test PDF generation for travel orders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // Get first travel order
            $order = TravelOrder::with(['participants.user'])->first();
            
            if (!$order) {
                $this->error("No travel orders found in database");
                return 1;
            }
            
            $this->info("Found Travel Order: " . $order->reference_number);
            $this->info("Participants: " . $order->participants->count());
            $this->info("Status: " . $order->status);
            
            // Test logo file existence
            $logoFiles = [
                'dict-logo.png',
                'DICT-Logo.png', 
                'logo.png',
                'dict-logo.jpg'
            ];
            
            $this->info("\nChecking logo files:");
            foreach ($logoFiles as $logo) {
                $path = public_path('images/logo/' . $logo);
                $status = file_exists($path) ? "EXISTS" : "NOT FOUND";
                $this->line("  $logo: $status ($path)");
            }
            
            // Test QR Code generation
            $this->info("\nTesting QR Code generation...");
            $testUrl = url('/travel-orders/' . $order->id);
            $qrCode = QrCode::format('svg')->size(100)->generate($testUrl);
            $this->info("QR Code generated successfully for: $testUrl");
            $this->info("QR Code size: " . strlen($qrCode) . " bytes");
            
            // Create PDF controller instance and test PDF generation
            $pdfController = app(PDFController::class);
            $this->info("\nGenerating PDF...");
            
            // Create a mock request
            $request = new Request();
            $response = $pdfController->generatePDF($order);
            
            $this->info("PDF generation successful!");
            $this->info("Response type: " . get_class($response));
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            $this->error("File: " . $e->getFile() . ":" . $e->getLine());
            $this->error("Stack trace: " . $e->getTraceAsString());
            return 1;
        }
    }
}
