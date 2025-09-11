<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\TravelOrder;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Carbon\Carbon;

class PDFController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Generate PDF for download
     */
    public function generatePDF(TravelOrder $travelOrder)
    {
        $this->authorize('view', $travelOrder);
        
        try {
            $travelOrder->load(['user', 'preparedBy', 'approvals.approverUser', 'participants.user']);
            
            // Generate QR Code for the travel order URL
            $travelOrderUrl = url('/travel-orders/' . $travelOrder->id);
            $qrCode = QrCode::format('svg')
                ->size(100)
                ->margin(1)
                ->generate($travelOrderUrl);
            
            // Convert QR code to base64 for embedding in PDF
            $qrCodeBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrCode);
            
            // Get logo as base64
            $logoBase64 = $this->getLogoBase64();
            
            $pdf = PDF::loadView('pdf.travel-order', compact('travelOrder', 'qrCodeBase64', 'logoBase64'));
            
            // Set PDF options for better rendering
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => true,
                'defaultFont' => 'Arial',
                'dpi' => 150
            ]);
            
            $filename = 'Travel_Order_' . $travelOrder->local_travel_order_no . '.pdf';
            
            return $pdf->download($filename);
        } catch (\Exception $e) {
            \Log::error('PDF Generation Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }
    
    /**
     * Preview PDF in browser
     */
    public function previewPDF(TravelOrder $travelOrder)
    {
        $this->authorize('view', $travelOrder);
        
        try {
            $travelOrder->load(['user', 'preparedBy', 'approvals.approverUser', 'participants.user']);
            
            // Generate QR Code for the travel order URL
            $travelOrderUrl = url('/travel-orders/' . $travelOrder->id);
            $qrCode = QrCode::format('svg')
                ->size(100)
                ->margin(1)
                ->generate($travelOrderUrl);
            
            // Convert QR code to base64 for embedding in PDF
            $qrCodeBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrCode);
            
            // Get logo as base64
            $logoBase64 = $this->getLogoBase64();
            
            $pdf = PDF::loadView('pdf.travel-order', compact('travelOrder', 'qrCodeBase64', 'logoBase64'));
            
            // Set PDF options for better rendering
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => true,
                'defaultFont' => 'Arial',
                'dpi' => 150
            ]);
            
            return $pdf->stream('Travel_Order_' . $travelOrder->local_travel_order_no . '.pdf');
        } catch (\Exception $e) {
            \Log::error('PDF Preview Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to generate PDF preview: ' . $e->getMessage());
        }
    }
    
    /**
     * Get logo as base64 encoded string
     */
    private function getLogoBase64()
    {
        $logoFiles = [
            'DICT-Logo.png',
            'dict-logo.png', 
            'logo.png',
            'dict-logo.jpg'
        ];
        
        foreach ($logoFiles as $file) {
            $logoPath = public_path('images/logo/' . $file);
            if (file_exists($logoPath)) {
                $logoData = file_get_contents($logoPath);
                $logoType = pathinfo($logoPath, PATHINFO_EXTENSION);
                $logoMimeType = $logoType === 'png' ? 'image/png' : 
                                ($logoType === 'jpg' || $logoType === 'jpeg' ? 'image/jpeg' : 'image/png');
                
                return 'data:' . $logoMimeType . ';base64,' . base64_encode($logoData);
            }
        }
        
        return null;
    }
}
