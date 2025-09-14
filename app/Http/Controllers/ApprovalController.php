<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TravelOrderApproval;
use App\Models\TravelOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Mail\TravelOrderStatusUpdate;
use App\Mail\TravelOrderApprovalRequest;

class ApprovalController extends Controller
{
    /**
     * Display a listing of approvals for the authenticated user
     */
    public function index()
    {
        $user = Auth::user();
        
        $approvals = TravelOrderApproval::where('approver_user_id', $user->id)
            ->orWhere('approver_name', 'like', '%' . $user->name . '%')
            ->with(['travelOrder.user', 'travelOrder.preparedBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('approvals.index', compact('approvals'));
    }
    
    /**
     * Show specific approval
     */
    public function show(TravelOrderApproval $approval)
    {
        $this->authorize('view', $approval);
        
        $approval->load(['travelOrder.user', 'travelOrder.preparedBy', 'travelOrder.approvals']);
        
        return view('approvals.show', compact('approval'));
    }
    
    /**
     * Process approval action (approve, reject, etc.)
     */
    public function process(Request $request, TravelOrderApproval $approval)
    {
        $this->authorize('update', $approval);
        
        $request->validate([
            'action' => 'required|in:forwarded,endorsed,verified,approved,rejected',
            'comments' => 'nullable|string|max:500',
        ]);
        
        if ($approval->status !== 'pending') {
            return back()->with('error', 'This approval has already been processed.');
        }
        
        DB::beginTransaction();
        
        try {
            // Process the approval
            $approval->processAction(
                $request->action,
                $request->comments,
                Auth::id()
            );
            
            // If approved/forwarded, send email to next approver
            if (in_array($request->action, ['forwarded', 'endorsed', 'verified'])) {
                $this->sendNextApprovalEmail($approval->travelOrder);
            }
            
            // Send notification to travel order owner
            $this->sendStatusUpdateEmail($approval);
            
            DB::commit();
            
            switch ($request->action) {
                case 'approved':
                    $message = 'Travel order approved successfully.';
                    break;
                case 'rejected':
                    $message = 'Travel order rejected.';
                    break;
                default:
                    $message = 'Approval processed and forwarded to next step.';
                    break;
            }
            
            return redirect()->route('approvals.index')
                ->with('success', $message);
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to process approval: ' . $e->getMessage());
        }
    }
    
    /**
     * Show email approval form (no auth required)
     */
    public function showEmailApproval($token)
    {
        $approval = TravelOrderApproval::where('email_token', $token)
            ->where('status', 'pending')
            ->firstOrFail();
            
        if (!$approval->canApproveViaEmail($token)) {
            abort(404, 'Invalid or expired approval link.');
        }
        
        $approval->load(['travelOrder.user', 'travelOrder.preparedBy']);
        
        return view('approvals.email-approval', compact('approval', 'token'));
    }
    
    /**
     * Process email approval (no auth required)
     */
    public function processEmailApproval(Request $request, $token)
    {
        $approval = TravelOrderApproval::where('email_token', $token)
            ->where('status', 'pending')
            ->firstOrFail();
            
        if (!$approval->canApproveViaEmail($token)) {
            abort(404, 'Invalid or expired approval link.');
        }
        
        $request->validate([
            'action' => 'required|in:forwarded,endorsed,verified,approved,rejected',
            'comments' => 'nullable|string|max:500',
        ]);
        
        DB::beginTransaction();
        
        try {
            // Process the approval
            $approval->processAction(
                $request->action,
                $request->comments
            );
            
            // Clear the email token to prevent reuse
            $approval->update(['email_token' => null]);
            
            // If approved/forwarded, send email to next approver
            if (in_array($request->action, ['forwarded', 'endorsed', 'verified'])) {
                $this->sendNextApprovalEmail($approval->travelOrder);
            }
            
            // Send notification to travel order owner
            $this->sendStatusUpdateEmail($approval);
            
            DB::commit();
            
            switch ($request->action) {
                case 'approved':
                    $message = 'Travel order approved successfully.';
                    break;
                case 'rejected':
                    $message = 'Travel order rejected.';
                    break;
                default:
                    $message = 'Approval processed and forwarded to next step.';
                    break;
            }
            
            return view('approvals.email-success', [
                'message' => $message,
                'approval' => $approval
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to process approval: ' . $e->getMessage());
        }
    }
    
    /**
     * Send email to next approver in sequence
     */
    private function sendNextApprovalEmail(TravelOrder $travelOrder)
    {
        // Get the next pending approval in sequence order
        $nextApproval = $travelOrder->approvals()
            ->where('status', 'pending')
            ->orderBy('sequence')
            ->first();
            
        if ($nextApproval) {
            $token = $nextApproval->generateEmailToken();
            $approvalUrl = route('approval.email.show', ['token' => $token]);
            
            // Send email if approver has email - check both approverUser and email field
            $emailAddress = null;
            if ($nextApproval->approverUser && $nextApproval->approverUser->email) {
                $emailAddress = $nextApproval->approverUser->email;
            }
            
            if ($emailAddress) {
                try {
                    Mail::to($emailAddress)
                        ->send(new TravelOrderApprovalRequest($travelOrder, $nextApproval, $approvalUrl));
                        
                    $nextApproval->update(['email_sent_at' => Carbon::now()]);
                    
                    \Log::info('Next approval email sent successfully', [
                        'travel_order_id' => $travelOrder->id,
                        'approval_id' => $nextApproval->id,
                        'approver_name' => $nextApproval->approver_name,
                        'email_address' => $emailAddress
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Failed to send next approval email', [
                        'travel_order_id' => $travelOrder->id,
                        'approval_id' => $nextApproval->id,
                        'error' => $e->getMessage()
                    ]);
                }
            } else {
                \Log::warning('No email address for next approver', [
                    'travel_order_id' => $travelOrder->id,
                    'approval_id' => $nextApproval->id,
                    'approver_name' => $nextApproval->approver_name,
                    'approver_user_id' => $nextApproval->approver_user_id
                ]);
            }
        } else {
            \Log::info('No more pending approvals found', [
                'travel_order_id' => $travelOrder->id
            ]);
        }
    }
    
    /**
     * Send status update email to travel order owner
     */
    private function sendStatusUpdateEmail(TravelOrderApproval $approval)
    {
        $travelOrder = $approval->travelOrder;
        
        // Determine message based on approval status
        switch ($approval->status) {
            case 'approved':
                $message = 'Your travel order has been approved by ' . $approval->approver_name . '.';
                break;
            case 'rejected':
                $message = 'Your travel order has been rejected by ' . $approval->approver_name . '.';
                if ($approval->comments) {
                    $message .= ' Reason: ' . $approval->comments;
                }
                break;
            case 'forwarded':
            case 'endorsed':
            case 'verified':
                $message = 'Your travel order has been processed by ' . $approval->approver_name . ' and forwarded to the next approver.';
                break;
            default:
                $message = 'Your travel order status has been updated.';
                break;
        }
        
        // Send email to travel order owner
        if ($travelOrder->user && $travelOrder->user->email) {
            try {
                Mail::to($travelOrder->user->email)
                    ->send(new TravelOrderStatusUpdate($travelOrder, 'pending_approval', $message));
            } catch (\Exception $e) {
                \Log::error('Failed to send status update email: ' . $e->getMessage());
            }
        }
    }
}
