@extends('layouts.app')

@section('title', 'Edit Workflow Template')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-edit mr-2"></i>
                        Edit Workflow Template: {{ $workflowTemplate->name }}
                    </h4>
                </div>

                <form method="POST" action="{{ route('workflows.update', $workflowTemplate->id) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Template Name -->
                        <div class="form-group">
                            <label for="name">Template Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $workflowTemplate->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description', $workflowTemplate->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Layout -->
                        <div class="form-group">
                            <label for="layout">Layout <span class="text-danger">*</span></label>
                            <select class="form-control @error('layout') is-invalid @enderror" id="layout" name="layout" required>
                                <option value="vertical" {{ old('layout', $workflowTemplate->layout) == 'vertical' ? 'selected' : '' }}>
                                    Vertical (Sequential)
                                </option>
                                <option value="horizontal" {{ old('layout', $workflowTemplate->layout) == 'horizontal' ? 'selected' : '' }}>
                                    Horizontal (Parallel)
                                </option>
                                <option value="combo" {{ old('layout', $workflowTemplate->layout) == 'combo' ? 'selected' : '' }}>
                                    Combined (Mixed)
                                </option>
                            </select>
                            @error('layout')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>

                        <!-- Workflow Steps -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">
                                <i class="fas fa-list-ol mr-2"></i>
                                Approval Steps
                            </h5>
                            <button type="button" class="btn btn-success btn-sm" onclick="addStep()">
                                <i class="fas fa-plus mr-1"></i>
                                Add Step
                            </button>
                        </div>

                        <div id="steps-container">
                            <!-- Steps will be populated here -->
                        </div>

                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Note:</strong> Each step can have multiple approvers. 
                            In vertical layout, steps are processed sequentially. 
                            In horizontal layout, all approvers in a step must approve before moving to the next step.
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="row">
                            <div class="col-6">
                                <a href="{{ route('workflows.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left mr-1"></i>
                                    Cancel
                                </a>
                            </div>
                            <div class="col-6 text-right">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-1"></i>
                                    Update Template
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Step Template -->
<div id="step-template" style="display: none;">
    <div class="card mb-3 workflow-step">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Step <span class="step-number">1</span></h6>
            <button type="button" class="btn btn-sm btn-danger" onclick="removeStep(this)">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>Step Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control step-name" name="steps[0][step_name]" required>
            </div>
            
            <div class="approvers-section">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="mb-0">Approvers <span class="text-danger">*</span></label>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addApprover(this)">
                        <i class="fas fa-plus"></i> Add Approver
                    </button>
                </div>
                <div class="approvers-list">
                    <!-- Approvers will be added here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approver Template -->
<div id="approver-template" style="display: none;">
    <div class="approver-item mb-2 p-2 border rounded">
        <div class="d-flex justify-content-between align-items-center">
            <select class="form-control approver-select" name="steps[0][approvers][0][user_id]" required>
                <option value="">Select User</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                @endforeach
            </select>
            <button type="button" class="btn btn-sm btn-outline-danger ml-2" onclick="removeApprover(this)">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let stepCounter = 0;
const existingSteps = @json($workflowTemplate->steps->groupBy('step_group')->map(function($group) {
    return [
        'step_name' => $group->first()->step_name,
        'approvers' => $group->map(function($step) {
            return ['user_id' => $step->approver_user_id];
        })
    ];
})->values());

function addStep(stepData = null) {
    const container = document.getElementById('steps-container');
    const template = document.getElementById('step-template').innerHTML;
    const stepDiv = document.createElement('div');
    
    stepDiv.innerHTML = template.replace(/\[0\]/g, `[${stepCounter}]`);
    const newStep = stepDiv.firstElementChild;
    newStep.querySelector('.step-number').textContent = stepCounter + 1;
    
    container.appendChild(newStep);
    
    // Add at least one approver
    if (stepData) {
        newStep.querySelector('.step-name').value = stepData.step_name || '';
        stepData.approvers.forEach(() => {
            addApprover(newStep.querySelector('.btn-outline-primary'));
        });
        // Set approver values
        const approverSelects = newStep.querySelectorAll('.approver-select');
        stepData.approvers.forEach((approver, index) => {
            if (approverSelects[index]) {
                approverSelects[index].value = approver.user_id;
            }
        });
    } else {
        addApprover(newStep.querySelector('.btn-outline-primary'));
    }
    
    stepCounter++;
    updateStepNumbers();
}

function removeStep(button) {
    if (document.querySelectorAll('.workflow-step').length <= 1) {
        alert('You must have at least one step.');
        return;
    }
    button.closest('.workflow-step').remove();
    updateStepNumbers();
}

function addApprover(button) {
    const step = button.closest('.workflow-step');
    const stepIndex = Array.from(document.querySelectorAll('.workflow-step')).indexOf(step);
    const approversList = step.querySelector('.approvers-list');
    const approverCount = approversList.querySelectorAll('.approver-item').length;
    
    const template = document.getElementById('approver-template').innerHTML;
    const approverDiv = document.createElement('div');
    approverDiv.innerHTML = template
        .replace(/\[0\]\[approvers\]\[0\]/g, `[${stepIndex}][approvers][${approverCount}]`);
    
    approversList.appendChild(approverDiv.firstElementChild);
}

function removeApprover(button) {
    const approverItem = button.closest('.approver-item');
    const approversList = approverItem.parentElement;
    
    if (approversList.querySelectorAll('.approver-item').length <= 1) {
        alert('Each step must have at least one approver.');
        return;
    }
    
    approverItem.remove();
    updateApproverIndexes();
}

function updateStepNumbers() {
    const steps = document.querySelectorAll('.workflow-step');
    steps.forEach((step, stepIndex) => {
        step.querySelector('.step-number').textContent = stepIndex + 1;
        
        // Update step inputs
        const inputs = step.querySelectorAll('input, select');
        inputs.forEach(input => {
            const name = input.name;
            if (name && name.includes('steps[')) {
                input.name = name.replace(/steps\[\d+\]/, `steps[${stepIndex}]`);
            }
        });
    });
    updateApproverIndexes();
}

function updateApproverIndexes() {
    const steps = document.querySelectorAll('.workflow-step');
    steps.forEach((step, stepIndex) => {
        const approvers = step.querySelectorAll('.approver-item');
        approvers.forEach((approver, approverIndex) => {
            const select = approver.querySelector('select');
            if (select) {
                select.name = `steps[${stepIndex}][approvers][${approverIndex}][user_id]`;
            }
        });
    });
}

// Initialize with existing steps
document.addEventListener('DOMContentLoaded', function() {
    if (existingSteps.length > 0) {
        existingSteps.forEach(step => {
            addStep(step);
        });
    } else {
        addStep(); // Add at least one empty step
    }
});
</script>
@endpush
