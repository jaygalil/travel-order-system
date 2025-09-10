<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\TravelOrder;

class TravelOrderStatusUpdate extends Mailable
{
    use Queueable, SerializesModels;

    public $travelOrder;
    public $previousStatus;
    public $message;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(TravelOrder $travelOrder, $previousStatus = null, $message = null)
    {
        $this->travelOrder = $travelOrder;
        $this->previousStatus = $previousStatus;
        $this->message = $message;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $statusMessage = ucfirst(str_replace('_', ' ', $this->travelOrder->status));
        
        return $this->subject('Travel Order Status Update - ' . $this->travelOrder->local_travel_order_no)
                    ->view('emails.travel-order.status-update')
                    ->with([
                        'travelOrder' => $this->travelOrder,
                        'previousStatus' => $this->previousStatus,
                        'statusMessage' => $statusMessage,
                        'customMessage' => $this->message,
                    ]);
    }
}
