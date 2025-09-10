<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\TravelOrder;

class TravelOrderCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $travelOrder;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(TravelOrder $travelOrder)
    {
        $this->travelOrder = $travelOrder;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('New Travel Order Created - ' . $this->travelOrder->local_travel_order_no)
                    ->view('emails.travel-order.created')
                    ->with([
                        'travelOrder' => $this->travelOrder,
                    ]);
    }
}
