<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WorkflowTemplate;
use App\Models\WorkflowStep;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class WorkflowTemplateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        
        $templates = WorkflowTemplate::with(['createdBy', 'steps'])
            ->availableToUser($user)
            ->active()
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('workflows.index', compact('templates'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('workflows.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Log incoming request for debugging
            \Log::info('Workflow template creation request', [
                'user_id' => Auth::id(),
                'input' => $request->all()
            ]);
            
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'layout' => 'required|in:vertical,horizontal,combo',
                'steps' => 'required|array|min:1',
                'steps.*.step_name' => 'required|string|max:255',
                'steps.*.approvers' => 'required|array|min:1',
                'steps.*.approvers.*.user_id' => 'required|exists:users,id',
            ]);
            
            DB::beginTransaction();
        
            $template = WorkflowTemplate::create([
                'name' => $request->name,
                'description' => $request->description,
                'created_by_user_id' => Auth::id(),
                'visibility' => 'private', // Always private for simplicity
                'layout' => $request->layout,
            ]);
            
            foreach ($request->steps as $stepIndex => $stepData) {
                foreach ($stepData['approvers'] as $approverIndex => $approverData) {
                    $user = \App\Models\User::find($approverData['user_id']);
                    
                    WorkflowStep::create([
                        'workflow_template_id' => $template->id,
                        'sequence' => $stepIndex + 1,
                        'step_group' => $stepIndex + 1,
                        'group_order' => $approverIndex + 1,
                        'step_name' => $stepData['step_name'] . (count($stepData['approvers']) > 1 ? ' - ' . $user->name : ''),
                        'approver_type' => 'user',
                        'approver_user_id' => $approverData['user_id'],
                        'approver_name' => $user->name,
                        'approver_title' => $user->position ?? 'User',
                        'action_type' => 'approve',
                        'is_required' => true,
                    ]);
                }
            }
            
            DB::commit();
            
            \Log::info('Workflow template created successfully', [
                'user_id' => Auth::id(),
                'template_id' => $template->id,
                'template_name' => $template->name
            ]);
            
            return redirect()->route('workflows.index')
                           ->with('success', 'Workflow template created successfully!');
                           
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            \Log::warning('Workflow template validation failed', [
                'user_id' => Auth::id(),
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);
            throw $e;
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Workflow template creation failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Failed to create workflow: ' . $e->getMessage()])
                        ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Find the workflow template explicitly
        $workflowTemplate = WorkflowTemplate::findOrFail($id);
        
        $this->authorize('view', $workflowTemplate);
        
        $workflowTemplate->load(['createdBy', 'steps.approverUser']);
        
        return view('workflows.show', compact('workflowTemplate'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // Find the workflow template explicitly
        $workflowTemplate = WorkflowTemplate::findOrFail($id);
        
        $this->authorize('update', $workflowTemplate);
        
        $workflowTemplate->load('steps');
        
        $users = User::where('is_active', true)->orderBy('name')->get();
        $roles = Role::all();
        $positions = User::whereNotNull('position')
                        ->distinct()
                        ->pluck('position')
                        ->filter()
                        ->sort()
                        ->values();
        $departments = User::whereNotNull('department')
                          ->distinct()
                          ->pluck('department')
                          ->filter()
                          ->sort()
                          ->values();
        
        return view('workflows.simple-edit', compact('workflowTemplate', 'users', 'roles', 'positions', 'departments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Find the workflow template explicitly
        $workflowTemplate = WorkflowTemplate::findOrFail($id);
        
        $this->authorize('update', $workflowTemplate);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'layout' => 'required|in:vertical,horizontal,combo',
            'steps' => 'required|array|min:1',
            'steps.*.step_name' => 'required|string|max:255',
            'steps.*.approvers' => 'required|array|min:1',
            'steps.*.approvers.*.user_id' => 'required|exists:users,id',
        ]);
        
        DB::beginTransaction();
        
        try {
            $workflowTemplate->update([
                'name' => $request->name,
                'description' => $request->description,
                'layout' => $request->layout,
            ]);
            
            // Delete existing steps and recreate
            $workflowTemplate->steps()->delete();
            
            foreach ($request->steps as $stepIndex => $stepData) {
                foreach ($stepData['approvers'] as $approverIndex => $approverData) {
                    $user = \App\Models\User::find($approverData['user_id']);
                    
                    WorkflowStep::create([
                        'workflow_template_id' => $workflowTemplate->id,
                        'sequence' => $stepIndex + 1,
                        'step_group' => $stepIndex + 1,
                        'group_order' => $approverIndex + 1,
                        'step_name' => $stepData['step_name'] . (count($stepData['approvers']) > 1 ? ' - ' . $user->name : ''),
                        'approver_type' => 'user',
                        'approver_user_id' => $approverData['user_id'],
                        'approver_name' => $user->name,
                        'approver_title' => $user->position ?? 'User',
                        'action_type' => 'approve',
                        'is_required' => true,
                    ]);
                }
            }
            
            DB::commit();
            
            return redirect()->route('workflows.index')
                           ->with('success', 'Workflow template updated successfully!');
                           
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to update workflow: ' . $e->getMessage()])
                        ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Find the workflow template explicitly
        $workflowTemplate = WorkflowTemplate::findOrFail($id);
        
        $this->authorize('delete', $workflowTemplate);
        
        // Check if template is being used by any travel orders
        if ($workflowTemplate->travelOrders()->exists()) {
            return back()->with('error', 'Cannot delete workflow template that is being used by travel orders.');
        }
        
        $workflowTemplate->delete();
        
        return redirect()->route('workflows.index')
                       ->with('success', 'Workflow template deleted successfully!');
    }
    
    /**
     * Set workflow template as default
     */
    public function setDefault($id)
    {
        // Find the workflow template explicitly
        $workflowTemplate = WorkflowTemplate::findOrFail($id);
        
        $this->authorize('update', $workflowTemplate);
        
        DB::transaction(function () use ($workflowTemplate) {
            // Remove default from all user's templates
            WorkflowTemplate::where('created_by_user_id', Auth::id())
                           ->update(['is_default' => false]);
                           
            // Set this template as default
            $workflowTemplate->update(['is_default' => true]);
        });
        
        return back()->with('success', 'Workflow template set as default!');
    }
}
