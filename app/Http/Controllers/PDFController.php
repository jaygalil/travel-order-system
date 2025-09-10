<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TravelOrder;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\Auth;
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
        
        $travelOrder->load(['user', 'preparedBy', 'approvals']);
        
        $pdf = PDF::loadView('pdf.travel-order', compact('travelOrder'));
        
        $filename = 'Travel_Order_' . $travelOrder->local_travel_order_no . '.pdf';
        
        return $pdf->download($filename);
    }
    
    /**
     * Preview PDF in browser
     */
    public function previewPDF(TravelOrder $travelOrder)
    {
        $this->authorize('view', $travelOrder);
        
        $travelOrder->load(['user', 'preparedBy', 'approvals']);
        
        $pdf = PDF::loadView('pdf.travel-order', compact('travelOrder'));
        
        return $pdf->stream('Travel_Order_' . $travelOrder->local_travel_order_no . '.pdf');
    }
}
