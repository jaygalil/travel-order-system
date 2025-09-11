@extends('layouts.app')

@section('title', 'Edit Workflow Template: ' . $workflowTemplate->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-edit mr-2"></i>
                        Edit Workflow Template: {{ $workflowTemplate->name }}
                    </h4>
                </div>

                <form method="POST" action="{{ route('workflows.update', $workflowTemplate->id) }}" id="workflow-form">
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

                        <!-- Basic Information -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Template Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $workflowTemplate->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="visibility">Visibility <span class="text-danger">*</span></label>
                                    <select class="form-control @error('visibility') is-invalid @enderror" 
                                            id="visibility" name="visibility" required>
                                        <option value="">Select Visibility</option>
                                        <option value="private" {{ old('visibility', $workflowTemplate->visibility) === 'private' ? 'selected' : '' }}>
                                            Private (Only me)
                                        </option>
                                        <option value="public" {{ old('visibility', $workflowTemplate->visibility) === 'public' ? 'selected' : '' }}>
                                            Public (All users)
                                        </option>
                                        <option value="department" {{ old('visibility', $workflowTemplate->visibility) === 'department' ? 'selected' : '' }}>
                                            Department Only
                                        </option>
                                    </select>
                                    @error('visibility')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6" id="department-field" style="{{ old('visibility', $workflowTemplate->visibility) === 'department' ? 'display: block' : 'display: none' }}">
                                <div class="form-group">
                                    <label for="department">Department <span class="text-danger">*</span></label>
                                    <select class="form-control @error('department') is-invalid @enderror" 
                                            id="department" name="department">
                                        <option value="">Select Department</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept }}" {{ old('department', $workflowTemplate->department) === $dept ? 'selected' : '' }}>
                                                {{ $dept }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('department')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="3" 
                                              placeholder="Describe the purpose of this workflow template...">{{ old('description', $workflowTemplate->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Workflow Steps -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">
                                <i class="fas fa-list-ol mr-2"></i>
                                Workflow Steps
                            </h5>
                            <button type="button" class="btn btn-success btn-sm" onclick="addStep()">
                                <i class="fas fa-plus mr-1"></i>
                                Add Step
                            </button>
                        </div>

                        <div id="steps-container">
                            <!-- Existing steps will be loaded here -->
                        </div>

                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Note:</strong> Add at least one approval step to create a complete workflow. 
                            Steps will be executed in the order they appear.
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="row">
                            <div class="col-md-6">
                                <a href="{{ route('workflows.show', $workflowTemplate->id) }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left mr-1"></i>
                                    Cancel
                                </a>
                            </div>
                            <div class="col-md-6 text-right">
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

<!-- Step Template (Hidden) -->
<div id="step-template" style="display: none;">
    <div class="card mb-3 workflow-step">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">
                <i class="fas fa-step-forward mr-2"></i>
                Step <span class="step-number">1</span>
            </h6>
            <button type="button" class="btn btn-sm btn-danger" onclick="removeStep(this)">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Step Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control step-name" name="steps[0][step_name]" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Action Type <span class="text-danger">*</span></label>
                        <select class="form-control step-action" name="steps[0][action_type]" required>
                            <option value="approve">Approve</option>
                            <option value="review">Review</option>
                            <option value="acknowledge">Acknowledge</option>
                            <option value="verify">Verify</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Approver Type <span class="text-danger">*</span></label>
                        <select class="form-control approver-type" name="steps[0][approver_type]" onchange="toggleApproverFields(this)" required>
                            <option value="">Select Type</option>
                            <option value="user">Specific User</option>
                            <option value="role">Role</option>
                            <option value="position">Position</option>
                            <option value="department">Department</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <!-- User Selection -->
                    <div class="form-group approver-field user-field" style="display: none;">
                        <label>Select User <span class="text-danger">*</span></label>
                        <select class="form-control approver-user" name="steps[0][approver_user_id]">
                            <option value="">Select User</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Role Selection -->
                    <div class="form-group approver-field role-field" style="display: none;">
                        <label>Select Role <span class="text-danger">*</span></label>
                        <select class="form-control approver-role" name="steps[0][approver_role]">
                            <option value="">Select Role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Position Selection -->
                    <div class="form-group approver-field position-field" style="display: none;">
                        <label>Select Position <span class="text-danger">*</span></label>
                        <select class="form-control approver-position" name="steps[0][approver_position]">
                            <option value="">Select Position</option>
                            @foreach($positions as $position)
                                <option value="{{ $position }}">{{ $position }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Department Selection -->
                    <div class="form-group approver-field department-field" style="display: none;">
                        <label>Select Department <span class="text-danger">*</span></label>
                        <select class="form-control approver-dept" name="steps[0][approver_department]">
                            <option value="">Select Department</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept }}">{{ $dept }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Approver Display Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control approver-name" name="steps[0][approver_name]" required>
                    </div>
                    <div class="form-group">
                        <label>Approver Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control approver-title" name="steps[0][approver_title]" required>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Step Description</label>
                        <textarea class="form-control step-description" name="steps[0][step_description]" rows="2" 
                                  placeholder="Optional description for this step..."></textarea>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="steps[0][is_required]" value="1" checked>
                        <label class="form-check-label">Required Step</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="steps[0][can_delegate]" value="1">
                        <label class="form-check-label">Can be Delegated</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let stepCounter = 0;
const existingSteps = @json($workflowTemplate->steps->sortBy('sequence')->values());

// Handle visibility change
document.getElementById('visibility').addEventListener('change', function() {
    const departmentField = document.getElementById('department-field');
    const departmentSelect = document.getElementById('department');
    
    if (this.value === 'department') {
        departmentField.style.display = 'block';
        departmentSelect.required = true;
    } else {
        departmentField.style.display = 'none';
        departmentSelect.required = false;
        departmentSelect.value = '';
    }
});

// Add new step
function addStep(stepData = null) {
    const container = document.getElementById('steps-container');
    const template = document.getElementById('step-template').innerHTML;
    const stepDiv = document.createElement('div');
    
    stepDiv.innerHTML = template.replace(/\[0\]/g, `[${stepCounter}]`);
    const newStepElement = stepDiv.firstElementChild;
    newStepElement.querySelector('.step-number').textContent = stepCounter + 1;
    
    if (stepData) {
        // Populate with existing data
        populateStepData(newStepElement, stepData);
    }
    
    container.appendChild(newStepElement);
    stepCounter++;
    
    // Update step numbers
    updateStepNumbers();
}

// Populate step with existing data
function populateStepData(stepElement, stepData) {
    // Basic fields
    stepElement.querySelector('.step-name').value = stepData.step_name || '';
    stepElement.querySelector('.step-action').value = stepData.action_type || 'approve';
    stepElement.querySelector('.approver-type').value = stepData.approver_type || '';
    stepElement.querySelector('.approver-name').value = stepData.approver_name || '';
    stepElement.querySelector('.approver-title').value = stepData.approver_title || '';
    stepElement.querySelector('.step-description').value = stepData.step_description || '';
    
    // Checkboxes
    stepElement.querySelector('input[name*="[is_required]"]').checked = stepData.is_required;
    stepElement.querySelector('input[name*="[can_delegate]"]').checked = stepData.can_delegate;
    
    // Handle approver type specific fields
    if (stepData.approver_type) {
        toggleApproverFields(stepElement.querySelector('.approver-type'));
        
        setTimeout(() => {
            switch(stepData.approver_type) {
                case 'user':
                    if (stepData.approver_user_id) {
                        stepElement.querySelector('.approver-user').value = stepData.approver_user_id;
                    }
                    break;
                case 'role':
                    if (stepData.approver_role) {
                        stepElement.querySelector('.approver-role').value = stepData.approver_role;
                    }
                    break;
                case 'position':
                    if (stepData.approver_position) {
                        stepElement.querySelector('.approver-position').value = stepData.approver_position;
                    }
                    break;
                case 'department':
                    if (stepData.approver_department) {
                        stepElement.querySelector('.approver-dept').value = stepData.approver_department;
                    }
                    break;
            }
        }, 100);
    }
}

// Remove step
function removeStep(button) {
    if (document.querySelectorAll('.workflow-step').length <= 1) {
        alert('You must have at least one workflow step.');
        return;
    }
    
    button.closest('.workflow-step').remove();
    updateStepNumbers();
}

// Update step numbers
function updateStepNumbers() {
    const steps = document.querySelectorAll('.workflow-step');
    steps.forEach((step, index) => {
        step.querySelector('.step-number').textContent = index + 1;
        
        // Update all name attributes
        const inputs = step.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            const name = input.name;
            if (name && name.includes('steps[')) {
                input.name = name.replace(/steps\[\d+\]/, `steps[${index}]`);
            }
        });
    });
}

// Toggle approver fields based on type selection
function toggleApproverFields(select) {
    const step = select.closest('.workflow-step');
    const fields = step.querySelectorAll('.approver-field');
    
    // Hide all approver fields
    fields.forEach(field => {
        field.style.display = 'none';
        const input = field.querySelector('select');
        if (input) {
            input.required = false;
        }
    });
    
    // Show relevant field
    const selectedType = select.value;
    if (selectedType) {
        const targetField = step.querySelector(`.${selectedType}-field`);
        if (targetField) {
            targetField.style.display = 'block';
            const input = targetField.querySelector('select');
            if (input) {
                input.required = true;
            }
        }
        
        // Auto-populate name and title based on selection (if not already populated)
        setTimeout(() => {
            const nameInput = step.querySelector('.approver-name');
            const titleInput = step.querySelector('.approver-title');
            if (!nameInput.value || !titleInput.value) {
                autoPopulateApproverInfo(step);
            }
        }, 100);
    }
}

// Auto-populate approver name and title
function autoPopulateApproverInfo(step) {
    const typeSelect = step.querySelector('.approver-type');
    const nameInput = step.querySelector('.approver-name');
    const titleInput = step.querySelector('.approver-title');
    
    const type = typeSelect.value;
    let displayName = '';
    let displayTitle = '';
    
    switch(type) {
        case 'user':
            const userSelect = step.querySelector('.approver-user');
            if (userSelect.value) {
                const selectedOption = userSelect.options[userSelect.selectedIndex];
                displayName = selectedOption.text.split(' (')[0];
                displayTitle = 'User';
            }
            break;
        case 'role':
            const roleSelect = step.querySelector('.approver-role');
            if (roleSelect.value) {
                displayName = roleSelect.options[roleSelect.selectedIndex].text;
                displayTitle = 'Role';
            }
            break;
        case 'position':
            const positionSelect = step.querySelector('.approver-position');
            if (positionSelect.value) {
                displayName = positionSelect.value;
                displayTitle = 'Position';
            }
            break;
        case 'department':
            const deptSelect = step.querySelector('.approver-dept');
            if (deptSelect.value) {
                displayName = `${deptSelect.value} Department`;
                displayTitle = 'Department Head';
            }
            break;
    }
    
    if (displayName && !nameInput.value) {
        nameInput.value = displayName;
    }
    if (displayTitle && !titleInput.value) {
        titleInput.value = displayTitle;
    }
}

// Initialize with existing steps
document.addEventListener('DOMContentLoaded', function() {
    // Load existing steps
    if (existingSteps.length > 0) {
        existingSteps.forEach(stepData => {
            addStep(stepData);
        });
    } else {
        addStep(); // Add at least one empty step
    }
    
    // Set visibility field based on current value
    if ('{{ old('visibility', $workflowTemplate->visibility) }}' === 'department') {
        document.getElementById('visibility').dispatchEvent(new Event('change'));
    }
    
    // Add change listeners to approver selects
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('approver-user') || 
            e.target.classList.contains('approver-role') || 
            e.target.classList.contains('approver-position') || 
            e.target.classList.contains('approver-dept')) {
            autoPopulateApproverInfo(e.target.closest('.workflow-step'));
        }
    });
    
    // Form validation
    document.getElementById('workflow-form').addEventListener('submit', function(e) {
        const steps = document.querySelectorAll('.workflow-step');
        if (steps.length === 0) {
            e.preventDefault();
            alert('Please add at least one workflow step.');
            return;
        }
        
        let isValid = true;
        steps.forEach((step, index) => {
            const requiredFields = step.querySelectorAll('input[required], select[required]');
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields.');
        }
    });
});
</script>
@endpush

@push('styles')
<style>
    .workflow-step {
        border-left: 4px solid #007bff;
    }
    .step-number {
        font-weight: bold;
        color: #007bff;
    }
    .approver-field {
        transition: all 0.3s ease;
    }
    .is-invalid {
        border-color: #dc3545;
    }
    .text-danger {
        color: #dc3545!important;
    }
</style>
@endpush
