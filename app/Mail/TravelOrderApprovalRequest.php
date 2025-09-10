<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\TravelOrder;
use App\Models\TravelOrderApproval;

class TravelOrderApprovalRequest extends Mailable
{
    use Queueable, SerializesModels;

    public $travelOrder;
    public $approval;
    public $approvalUrl;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(TravelOrder $travelOrder, TravelOrderApproval $approval, $approvalUrl = null)
    {
        $this->travelOrder = $travelOrder;
        $this->approval = $approval;
        $this->approvalUrl = $approvalUrl;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Travel Order Approval Request - ' . $this->travelOrder->local_travel_order_no)
                    ->view('emails.travel-order.approval-request')
                    ->with([
                        'travelOrder' => $this->travelOrder,
                        'approval' => $this->approval,
                        'approvalUrl' => $this->approvalUrl,
                    ]);
    }
}
