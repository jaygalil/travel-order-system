<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\TravelOrderRequest;
use App\Models\TravelOrder;
use App\Models\TravelOrderApproval;
use App\Models\TravelOrderParticipant;
use App\Models\WorkflowTemplate;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\TravelOrderCreated;
use App\Mail\TravelOrderStatusUpdate;
use App\Mail\TravelOrderApprovalRequest;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\ApprovalWorkflowBuilder;

class TravelOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $travelOrders = TravelOrder::where('user_id', Auth::id())
            ->orWhere('prepared_by_user_id', Auth::id())
            ->with(['user', 'preparedBy', 'approvals'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('travel-orders.index', compact('travelOrders'));
    }

    /**
     * Admin view of all travel orders
     */
    public function adminIndex()
    {
        $travelOrders = TravelOrder::with(['user', 'preparedBy', 'approvals'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('travel-orders.admin-index', compact('travelOrders'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = Auth::user();
        $availableTemplates = WorkflowTemplate::with('steps')
            ->availableToUser($user)
            ->active()
            ->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get();
        
        return view('travel-orders.create', compact('availableTemplates'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\TravelOrderRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(TravelOrderRequest $request)
    {
        // Log the incoming request data
        \Log::info('Travel Order Store Request', [
            'user_id' => Auth::id(),
            'workflow_template_id' => $request->workflow_template_id,
            'workflow_template_id_type' => gettype($request->workflow_template_id),
            'all_input' => $request->except(['participants', '_token']),
            'participants_count' => count($request->participants ?? [])
        ]);
        
        DB::beginTransaction();
        
        try {
            // Get primary participant data for backwards compatibility
            $primaryParticipant = collect($request->participants)->firstWhere('is_primary', 1);
            if (!$primaryParticipant) {
                $primaryParticipant = $request->participants[0]; // Fallback to first participant
            }
            
            $travelOrder = TravelOrder::create([
                'local_travel_order_no' => TravelOrder::generateTravelOrderNumber(),
                'user_id' => $request->user_id ?: Auth::id(),
                'date_of_travel_from' => $request->date_of_travel_from,
                'date_of_travel_to' => $request->date_of_travel_to,
                'prepared_by_user_id' => Auth::id(),
                'employee_name' => $primaryParticipant['employee_name'],
                'position' => $primaryParticipant['position'],
                'division_agency' => $primaryParticipant['division_agency'],
                'source_of_fund' => $request->source_of_fund,
                'official_vehicle' => $request->official_vehicle,
                'purpose' => $request->purpose,
                'destination' => $request->destination,
                'farthest_destination' => $request->farthest_destination,
                'approx_distance' => $request->approx_distance,
                'workflow_template_id' => $request->workflow_template_id,
                'status' => 'draft',
            ]);
            
            // Create participants
            foreach ($request->participants as $index => $participantData) {
                TravelOrderParticipant::create([
                    'travel_order_id' => $travelOrder->id,
                    'user_id' => $participantData['user_id'] ?: null,
                    'employee_name' => $participantData['employee_name'],
                    'employee_id' => $participantData['employee_id'] ?: null,
                    'position' => $participantData['position'],
                    'division_agency' => $participantData['division_agency'],
                    'phone' => $participantData['phone'] ?: null,
                    'email' => $participantData['email'] ?: null,
                    'is_primary' => $participantData['is_primary'] == 1,
                    'special_requirements' => $participantData['special_requirements'] ?: null,
                ]);
            }

            // Create approval workflow using the dedicated service
            $workflowBuilder = new ApprovalWorkflowBuilder();
            
            // Handle empty string template ID (convert to null)
            $templateId = $request->workflow_template_id;
            if ($templateId === '' || $templateId === '0') {
                $templateId = null;
            }
            
            \Log::info('Creating workflow with processed template ID', [
                'original' => $request->workflow_template_id,
                'processed' => $templateId,
                'type' => gettype($templateId)
            ]);
            
            $workflowBuilder->createWorkflowFromTemplate($travelOrder, $templateId);
            
            // Send creation notification email
            try {
                Mail::to($travelOrder->user->email)
                    ->send(new TravelOrderCreated($travelOrder));
            } catch (\Exception $e) {
                // Log email error but don't fail the operation
                \Log::error('Failed to send travel order creation email: ' . $e->getMessage());
            }

            DB::commit();

            return redirect()->route('travel-orders.show', $travelOrder)
                ->with('success', 'Travel order created successfully.');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to create travel order: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TravelOrder  $travelOrder
     * @return \Illuminate\Http\Response
     */
    public function show(TravelOrder $travelOrder)
    {
        $this->authorize('view', $travelOrder);
        
        $travelOrder->load(['user', 'preparedBy', 'approvals.approverUser', 'participants.user']);
        
        return view('travel-orders.show', compact('travelOrder'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TravelOrder  $travelOrder
     * @return \Illuminate\Http\Response
     */
    public function edit(TravelOrder $travelOrder)
    {
        $this->authorize('update', $travelOrder);
        
        // Only allow editing if status is draft
        if ($travelOrder->status !== 'draft') {
            return redirect()->route('travel-orders.show', $travelOrder)
                ->with('error', 'Cannot edit travel order after submission.');
        }
        
        $user = Auth::user();
        $availableTemplates = WorkflowTemplate::with('steps')
            ->availableToUser($user)
            ->active()
            ->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get();
            
        $selectedTemplate = $travelOrder->workflowTemplate()->with('steps')->first();
        
        // Load participants for editing
        $travelOrder->load('participants');
        
        return view('travel-orders.edit', compact('travelOrder', 'availableTemplates', 'selectedTemplate'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\TravelOrderRequest  $request
     * @param  \App\Models\TravelOrder  $travelOrder
     * @return \Illuminate\Http\Response
     */
    public function update(TravelOrderRequest $request, TravelOrder $travelOrder)
    {
        $this->authorize('update', $travelOrder);
        
        if ($travelOrder->status !== 'draft') {
            return redirect()->route('travel-orders.show', $travelOrder)
                ->with('error', 'Cannot edit travel order after submission.');
        }
        
        DB::beginTransaction();
        
        try {
            // Get primary participant data for backwards compatibility
            $primaryParticipant = collect($request->participants)->firstWhere('is_primary', 1);
            if (!$primaryParticipant) {
                $primaryParticipant = $request->participants[0]; // Fallback to first participant
            }
            
            // Update travel order with primary participant data
            $travelOrder->update([
                'date_of_travel_from' => $request->date_of_travel_from,
                'date_of_travel_to' => $request->date_of_travel_to,
                'employee_name' => $primaryParticipant['employee_name'],
                'position' => $primaryParticipant['position'],
                'division_agency' => $primaryParticipant['division_agency'],
                'source_of_fund' => $request->source_of_fund,
                'official_vehicle' => $request->official_vehicle,
                'purpose' => $request->purpose,
                'destination' => $request->destination,
                'farthest_destination' => $request->farthest_destination,
                'approx_distance' => $request->approx_distance,
                'workflow_template_id' => $request->workflow_template_id,
            ]);
            
            // Delete existing participants and recreate
            $travelOrder->participants()->delete();
            
            // Create new participants
            foreach ($request->participants as $participantData) {
                TravelOrderParticipant::create([
                    'travel_order_id' => $travelOrder->id,
                    'user_id' => $participantData['user_id'] ?: null,
                    'employee_name' => $participantData['employee_name'],
                    'employee_id' => $participantData['employee_id'] ?: null,
                    'position' => $participantData['position'],
                    'division_agency' => $participantData['division_agency'],
                    'phone' => $participantData['phone'] ?: null,
                    'email' => $participantData['email'] ?: null,
                    'is_primary' => $participantData['is_primary'] == 1,
                    'special_requirements' => $participantData['special_requirements'] ?: null,
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('travel-orders.show', $travelOrder)
                ->with('success', 'Travel order updated successfully.');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to update travel order: ' . $e->getMessage()])
                        ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TravelOrder  $travelOrder
     * @return \Illuminate\Http\Response
     */
    public function destroy(TravelOrder $travelOrder)
    {
        $this->authorize('delete', $travelOrder);
        
        if ($travelOrder->status !== 'draft') {
            return redirect()->route('travel-orders.index')
                ->with('error', 'Cannot delete travel order after submission.');
        }
        
        $travelOrder->delete();
        
        return redirect()->route('travel-orders.index')
            ->with('success', 'Travel order deleted successfully.');
    }

    /**
     * Submit travel order for approval
     */
    public function submit(TravelOrder $travelOrder)
    {
        $this->authorize('update', $travelOrder);
        
        if ($travelOrder->status !== 'draft') {
            return redirect()->route('travel-orders.show', $travelOrder)
                ->with('error', 'Travel order has already been submitted.');
        }
        
        DB::beginTransaction();
        
        try {
            $previousStatus = $travelOrder->status;
            
            $travelOrder->update([
                'status' => 'pending_approval',
                'submitted_at' => Carbon::now()
            ]);
            
            // Send status update email to requester
            try {
                Mail::to($travelOrder->user->email)
                    ->send(new TravelOrderStatusUpdate($travelOrder, $previousStatus, 'Your travel order has been submitted for approval.'));
            } catch (\Exception $e) {
                \Log::error('Failed to send status update email: ' . $e->getMessage());
            }
            
            // Send first approval email
            $firstApproval = $travelOrder->approvals()->orderBy('sequence')->first();
            if ($firstApproval) {
                $this->sendApprovalEmail($firstApproval);
            }
            
            DB::commit();
            
            return redirect()->route('travel-orders.show', $travelOrder)
                ->with('success', 'Travel order submitted for approval successfully.');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to submit travel order: ' . $e->getMessage());
        }
    }

    /**
     * Cancel travel order
     */
    public function cancel(TravelOrder $travelOrder)
    {
        $this->authorize('update', $travelOrder);
        
        if (!in_array($travelOrder->status, ['pending_approval', 'approved'])) {
            return redirect()->route('travel-orders.show', $travelOrder)
                ->with('error', 'Cannot cancel travel order in current status.');
        }
        
        $travelOrder->update([
            'status' => 'draft',
            'submitted_at' => null,
            'approved_at' => null
        ]);
        
        // Reset all approvals
        $travelOrder->approvals()->update([
            'status' => 'pending',
            'action_date' => null,
            'comments' => null
        ]);
        
        return redirect()->route('travel-orders.show', $travelOrder)
            ->with('success', 'Travel order cancelled and returned to draft.');
    }

    /*
     * Note: The workflow creation logic has been moved to App\Services\ApprovalWorkflowBuilder
     * for better maintainability and separation of concerns.
     */

    /**
     * Send approval email
     */
    private function sendApprovalEmail(TravelOrderApproval $approval)
    {
        // Generate email token for email-based approval
        $token = $approval->generateEmailToken();
        $approvalUrl = route('approval.email.show', ['token' => $token]);
        
        // Try to send email if approver has email
        if ($approval->approverUser && $approval->approverUser->email) {
            try {
                Mail::to($approval->approverUser->email)
                    ->send(new TravelOrderApprovalRequest($approval->travelOrder, $approval, $approvalUrl));
                    
                $approval->update(['email_sent_at' => Carbon::now()]);
            } catch (\Exception $e) {
                \Log::error('Failed to send approval email: ' . $e->getMessage());
                // Update without email sent timestamp to indicate failure
            }
        } else {
            // Log that no email was sent due to missing approver email
            \Log::warning('No email address for approver: ' . $approval->approver_name);
        }
    }
}
