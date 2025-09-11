@extends('layouts.app')

@section('title', 'Create Workflow Template')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-plus mr-2"></i>
                        Create Simple Workflow
                    </h4>
                </div>

                <form method="POST" action="{{ route('workflows.store') }}" id="workflow-form">
                    @csrf
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
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="name">Workflow Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required 
                                           placeholder="Enter workflow name">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="layout">Layout <span class="text-danger">*</span></label>
                                    <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                                        <input type="radio" class="btn-check" name="layout" id="layout-vertical" value="vertical" checked required>
                                        <label class="btn btn-outline-primary" for="layout-vertical">
                                            <i class="fas fa-arrows-alt-v me-1"></i>Vertical
                                        </label>
                                        <input type="radio" class="btn-check" name="layout" id="layout-horizontal" value="horizontal">
                                        <label class="btn btn-outline-primary" for="layout-horizontal">
                                            <i class="fas fa-arrows-alt-h me-1"></i>Horizontal
                                        </label>
                                        <input type="radio" class="btn-check" name="layout" id="layout-combo" value="combo">
                                        <label class="btn btn-outline-primary" for="layout-combo">
                                            <i class="fas fa-th me-1"></i>Combo
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea class="form-control" id="description" name="description" 
                                              rows="2" placeholder="Optional description">{{ old('description') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Workflow Steps -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">
                                <i class="fas fa-sitemap mr-2"></i>
                                Workflow Steps
                            </h5>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-info btn-sm" id="preview-btn">
                                    <i class="fas fa-eye mr-1"></i>Preview
                                </button>
                                <button type="button" class="btn btn-success btn-sm" id="add-step-btn">
                                    <i class="fas fa-plus mr-1"></i>Add Step
                                </button>
                            </div>
                        </div>

                        <!-- Preview Area -->
                        <div id="preview-area" class="mb-3" style="display: none;">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-eye mr-1"></i>Workflow Preview</h6>
                                </div>
                                <div class="card-body">
                                    <div id="preview-container" class="workflow-preview"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Visual Workflow Steps -->
                        <div id="workflow-steps" class="workflow-builder">
                            <div class="empty-state" id="empty-state">
                                <i class="fas fa-sitemap fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Click "Add Step" to start building your workflow</p>
                            </div>
                        </div>

                        <!-- Hidden Form Data -->
                        <div id="form-data"></div>

                        <div class="alert alert-info mt-4">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>How it works:</strong>
                            <ol class="mb-0 mt-2">
                                <li>Click "Add Step" to create workflow steps</li>
                                <li>Click on each step to assign a user</li>
                                <li>Choose your layout (vertical, horizontal, or combo)</li>
                                <li>Preview your workflow and save</li>
                            </ol>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="row">
                            <div class="col-md-6">
                                <a href="{{ route('workflows.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left mr-1"></i>Cancel
                                </a>
                            </div>
                            <div class="col-md-6 text-right">
                                <button type="submit" class="btn btn-primary" id="save-btn">
                                    <i class="fas fa-save mr-1"></i>Create Workflow
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- User Selection Modal -->
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select User for Step <span id="step-number"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <input type="text" class="form-control" id="user-search" placeholder="Search users by name or email...">
                </div>
                <div id="user-list" class="mt-3">
                    <div class="text-center">
                        <div class="spinner-border" role="status"></div>
                        <p class="mt-2">Loading users...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
class SimpleWorkflowBuilder {
    constructor() {
        this.steps = [];
        this.users = [];
        this.currentStep = -1;
        this.currentApprover = -1;
        
        this.init();
    }
    
    init() {
        // Event listeners
        document.getElementById('add-step-btn').addEventListener('click', () => this.addStep());
        document.getElementById('preview-btn').addEventListener('click', () => this.togglePreview());
        document.getElementById('user-search').addEventListener('input', (e) => this.searchUsers(e.target.value));
        document.querySelectorAll('input[name="layout"]').forEach(radio => {
            radio.addEventListener('change', () => this.updatePreview());
        });
        
        // Form validation
        document.getElementById('workflow-form').addEventListener('submit', (e) => this.validateForm(e));
        
        // Load users
        this.loadUsers();
    }
    
    async loadUsers() {
        try {
            const response = await fetch('/api/users');
            this.users = await response.json();
        } catch (error) {
            console.error('Error loading users:', error);
        }
    }
    
    addStep() {
        const step = {
            id: this.steps.length,
            name: `Step ${this.steps.length + 1}`,
            approvers: [{ user: null }] // Each step starts with one approver
        };
        
        this.steps.push(step);
        this.renderSteps();
        this.updateFormData();
        this.updatePreview();
        
        // Hide empty state
        document.getElementById('empty-state').style.display = 'none';
    }
    
    addHorizontalApprover(stepId) {
        const step = this.steps.find(s => s.id === stepId);
        if (step) {
            step.approvers.push({ user: null });
            this.renderSteps();
            this.updateFormData();
            this.updatePreview();
        }
    }
    
    removeApprover(stepId, approverIndex) {
        const step = this.steps.find(s => s.id === stepId);
        if (step && step.approvers.length > 1) {
            step.approvers.splice(approverIndex, 1);
            this.renderSteps();
            this.updateFormData();
            this.updatePreview();
        } else {
            alert('Each step must have at least one approver!');
        }
    }
    
    removeStep(stepId) {
        if (this.steps.length <= 1) {
            alert('You need at least one step!');
            return;
        }
        
        this.steps = this.steps.filter(step => step.id !== stepId);
        
        // Reindex steps
        this.steps.forEach((step, index) => {
            step.id = index;
            step.name = `Step ${index + 1}`;
        });
        
        this.renderSteps();
        this.updateFormData();
        this.updatePreview();
        
        // Show empty state if no steps
        if (this.steps.length === 0) {
            document.getElementById('empty-state').style.display = 'block';
        }
    }
    
    renderSteps() {
        const container = document.getElementById('workflow-steps');
        const emptyState = document.getElementById('empty-state');
        
        // Clear current steps (but keep empty state)
        const existingSteps = container.querySelectorAll('.workflow-step-group');
        existingSteps.forEach(step => step.remove());
        
        this.steps.forEach((step, stepIndex) => {
            const stepGroupElement = document.createElement('div');
            stepGroupElement.className = 'workflow-step-group';
            
            // Create step header
            const stepHeader = document.createElement('div');
            stepHeader.className = 'step-header';
            stepHeader.innerHTML = `
                <div class="step-title">
                    <span class="step-number-badge">${stepIndex + 1}</span>
                    <span class="step-name">${step.name}</span>
                </div>
                <div class="step-actions">
                    <button type="button" class="btn btn-sm btn-success add-approver" title="Add Horizontal Approver">
                        <i class="fas fa-plus"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-danger remove-step" title="Remove Step">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
            
            // Create approvers container
            const approversContainer = document.createElement('div');
            approversContainer.className = 'approvers-container';
            
            step.approvers.forEach((approver, approverIndex) => {
                const approverElement = document.createElement('div');
                const isAssigned = approver.user !== null;
                approverElement.className = `approver-block ${isAssigned ? 'assigned' : 'unassigned'}`;
                
                approverElement.innerHTML = `
                    <div class="approver-content">
                        <div class="approver-info">
                            <div class="approver-name">${isAssigned ? approver.user.name : 'Click to assign'}</div>
                            ${isAssigned ? `<small class="text-muted">${approver.user.email}</small>` : ''}
                        </div>
                        ${step.approvers.length > 1 ? `
                            <button type="button" class="btn btn-xs btn-outline-danger remove-approver" title="Remove Approver">
                                <i class="fas fa-times"></i>
                            </button>
                        ` : ''}
                    </div>
                `;
                
                // Add click handler for user selection
                approverElement.addEventListener('click', (e) => {
                    if (!e.target.classList.contains('remove-approver') && !e.target.closest('.remove-approver')) {
                        this.selectUser(stepIndex, approverIndex);
                    }
                });
                
                // Add remove approver handler
                const removeBtn = approverElement.querySelector('.remove-approver');
                if (removeBtn) {
                    removeBtn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        this.removeApprover(stepIndex, approverIndex);
                    });
                }
                
                approversContainer.appendChild(approverElement);
            });
            
            // Add event handlers for step actions
            stepHeader.querySelector('.add-approver').addEventListener('click', () => {
                this.addHorizontalApprover(stepIndex);
            });
            
            stepHeader.querySelector('.remove-step').addEventListener('click', () => {
                this.removeStep(stepIndex);
            });
            
            stepGroupElement.appendChild(stepHeader);
            stepGroupElement.appendChild(approversContainer);
            container.appendChild(stepGroupElement);
        });
    }
    
    selectUser(stepId, approverId) {
        this.currentStep = stepId;
        this.currentApprover = approverId;
        document.getElementById('step-number').textContent = `${stepId + 1} (Approver ${approverId + 1})`;
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('userModal'));
        modal.show();
        
        this.displayUsers(this.users);
    }
    
    searchUsers(query) {
        if (!query.trim()) {
            this.displayUsers(this.users);
            return;
        }
        
        const filtered = this.users.filter(user => 
            user.name.toLowerCase().includes(query.toLowerCase()) ||
            user.email.toLowerCase().includes(query.toLowerCase())
        );
        
        this.displayUsers(filtered);
    }
    
    displayUsers(users) {
        const userList = document.getElementById('user-list');
        
        if (users.length === 0) {
            userList.innerHTML = '<p class="text-muted text-center">No users found</p>';
            return;
        }
        
        userList.innerHTML = users.map(user => `
            <div class="user-item" data-user-id="${user.id}">
                <div class="d-flex align-items-center p-2 border rounded mb-2 cursor-pointer">
                    <div class="user-avatar mr-3">
                        <i class="fas fa-user-circle fa-2x text-primary"></i>
                    </div>
                    <div>
                        <strong>${user.name}</strong><br>
                        <small class="text-muted">${user.email}</small>
                    </div>
                </div>
            </div>
        `).join('');
        
        // Add click handlers
        userList.querySelectorAll('.user-item').forEach(item => {
            item.addEventListener('click', () => {
                const userId = parseInt(item.dataset.userId);
                const user = users.find(u => u.id === userId);
                this.assignUser(user);
            });
        });
    }
    
    assignUser(user) {
        const step = this.steps[this.currentStep];
        if (step && step.approvers[this.currentApprover]) {
            step.approvers[this.currentApprover].user = user;
            this.renderSteps();
            this.updateFormData();
            this.updatePreview();
        }
        
        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('userModal'));
        modal.hide();
    }
    
    updateFormData() {
        const formData = document.getElementById('form-data');
        formData.innerHTML = '';
        
        this.steps.forEach((step, stepIndex) => {
            formData.innerHTML += `<input type="hidden" name="steps[${stepIndex}][step_name]" value="${step.name}">`;
            
            step.approvers.forEach((approver, approverIndex) => {
                if (approver.user) {
                    formData.innerHTML += `
                        <input type="hidden" name="steps[${stepIndex}][approvers][${approverIndex}][user_id]" value="${approver.user.id}">
                    `;
                }
            });
        });
    }
    
    togglePreview() {
        const preview = document.getElementById('preview-area');
        const btn = document.getElementById('preview-btn');
        
        if (preview.style.display === 'none') {
            preview.style.display = 'block';
            btn.innerHTML = '<i class="fas fa-eye-slash mr-1"></i>Hide Preview';
            this.updatePreview();
        } else {
            preview.style.display = 'none';
            btn.innerHTML = '<i class="fas fa-eye mr-1"></i>Preview';
        }
    }
    
    updatePreview() {
        const container = document.getElementById('preview-container');
        const layout = document.querySelector('input[name="layout"]:checked').value;
        
        container.className = `workflow-preview layout-${layout}`;
        
        if (this.steps.length === 0) {
            container.innerHTML = '<p class="text-muted text-center">No steps to preview</p>';
            return;
        }
        
        let html = '';
        this.steps.forEach((step, stepIndex) => {
            html += '<div class="preview-step-group">';
            html += `<div class="step-label">Step ${stepIndex + 1}</div>`;
            html += '<div class="approvers-row">';
            
            step.approvers.forEach((approver, approverIndex) => {
                const isAssigned = approver.user !== null;
                html += `
                    <div class="preview-approver ${isAssigned ? 'assigned' : 'unassigned'}">
                        <div class="approver-badge">${stepIndex + 1}.${approverIndex + 1}</div>
                        <div class="approver-info">
                            <div class="approver-name">${isAssigned ? approver.user.name : 'Unassigned'}</div>
                        </div>
                    </div>
                `;
                
                // Add horizontal connector between approvers in same step
                if (approverIndex < step.approvers.length - 1) {
                    html += '<div class="approver-connector"></div>';
                }
            });
            
            html += '</div></div>'; // Close approvers-row and preview-step-group
            
            // Add vertical connector between steps
            if (stepIndex < this.steps.length - 1) {
                html += '<div class="step-connector"></div>';
            }
        });
        
        container.innerHTML = html;
    }
    
    validateForm(e) {
        if (this.steps.length === 0) {
            e.preventDefault();
            alert('Please add at least one workflow step.');
            return false;
        }
        
        // Check if all approvers are assigned
        let hasUnassigned = false;
        this.steps.forEach(step => {
            step.approvers.forEach(approver => {
                if (!approver.user) {
                    hasUnassigned = true;
                }
            });
        });
        
        if (hasUnassigned) {
            e.preventDefault();
            alert('Please assign users to all approvers in all workflow steps.');
            return false;
        }
        
        return true;
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    new SimpleWorkflowBuilder();
});
</script>
@endpush

@push('styles')
<style>
/* Workflow Builder */
.workflow-builder {
    min-height: 200px;
    padding: 20px;
    background: #f8f9fa;
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* Step Groups */
.workflow-step-group {
    background: white;
    border: 2px solid #007bff;
    border-radius: 12px;
    padding: 15px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.step-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #e9ecef;
}

.step-title {
    display: flex;
    align-items: center;
    gap: 10px;
}

.step-number-badge {
    display: inline-block;
    width: 30px;
    height: 30px;
    background: #007bff;
    color: white;
    border-radius: 50%;
    line-height: 30px;
    text-align: center;
    font-weight: bold;
}

.step-name {
    font-weight: 600;
    font-size: 16px;
    color: #333;
}

.step-actions {
    display: flex;
    gap: 5px;
}

.approvers-container {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
}

/* Approver Blocks */
.approver-block {
    background: #fff;
    border: 2px solid #dc3545;
    border-radius: 8px;
    padding: 12px;
    min-width: 180px;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}

.approver-block:hover {
    transform: translateY(-2px);
    box-shadow: 0 3px 6px rgba(0,0,0,0.15);
}

.approver-block.assigned {
    border-color: #28a745;
    background: linear-gradient(135deg, #ffffff, #f8fff9);
}

.approver-block.unassigned {
    border-color: #dc3545;
    background: linear-gradient(135deg, #ffffff, #fff8f8);
}

.approver-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.approver-name {
    font-weight: 500;
    color: #333;
    margin-bottom: 2px;
}

.approver-block.assigned .approver-name {
    color: #28a745;
}

.approver-block.unassigned .approver-name {
    color: #dc3545;
}

.remove-approver {
    padding: 2px 6px;
    font-size: 10px;
}

.empty-state {
    width: 100%;
    text-align: center;
    padding: 40px;
}

/* Step Blocks */
.workflow-step {
    position: relative;
    background: white;
    border: 2px solid #dc3545;
    border-radius: 12px;
    padding: 15px;
    min-width: 200px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.workflow-step:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.workflow-step.assigned {
    border-color: #28a745;
    background: linear-gradient(135deg, #ffffff, #f8fff9);
}

.workflow-step.unassigned {
    border-color: #dc3545;
    background: linear-gradient(135deg, #ffffff, #fff8f8);
}

.step-number {
    display: inline-block;
    width: 30px;
    height: 30px;
    background: #dc3545;
    color: white;
    border-radius: 50%;
    line-height: 30px;
    text-align: center;
    font-weight: bold;
    margin-bottom: 10px;
}

.workflow-step.assigned .step-number {
    background: #28a745;
}

.step-title {
    font-weight: bold;
    margin-bottom: 5px;
    color: #333;
}

.step-user {
    font-size: 14px;
    color: #666;
}

.workflow-step.assigned .step-user {
    color: #28a745;
    font-weight: 500;
}

.step-remove {
    position: absolute;
    top: -5px;
    right: -5px;
}

/* User Modal */
.user-item {
    cursor: pointer;
}

.user-item:hover .border {
    border-color: #007bff !important;
}

/* Layout Preview */
.workflow-preview {
    padding: 20px;
    background: #f1f3f4;
    border-radius: 8px;
    min-height: 100px;
}

.layout-vertical {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.layout-horizontal {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
}

.layout-combo {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
    align-items: center;
}

.preview-step {
    background: white;
    border: 2px solid #007bff;
    border-radius: 8px;
    padding: 10px;
    margin: 5px;
    text-align: center;
    min-width: 120px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.preview-step.unassigned {
    border-color: #dc3545;
    opacity: 0.7;
}

.step-badge {
    display: inline-block;
    width: 20px;
    height: 20px;
    background: #007bff;
    color: white;
    border-radius: 50%;
    line-height: 20px;
    font-size: 10px;
    font-weight: bold;
    margin-bottom: 5px;
}

.preview-step.unassigned .step-badge {
    background: #dc3545;
}

.step-name {
    font-size: 11px;
    font-weight: bold;
    margin-bottom: 2px;
}

.step-user {
    font-size: 10px;
    color: #666;
}

.step-connector {
    background: #007bff;
    border-radius: 2px;
}

.layout-vertical .step-connector {
    width: 2px;
    height: 20px;
}

.layout-horizontal .step-connector {
    width: 20px;
    height: 2px;
}

.layout-combo .step-connector {
    display: none;
}

/* Preview Step Groups */
.preview-step-group {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin: 10px;
}

.step-label {
    font-weight: bold;
    margin-bottom: 8px;
    color: #333;
    font-size: 12px;
}

.approvers-row {
    display: flex;
    align-items: center;
    gap: 10px;
}

.preview-approver {
    background: white;
    border: 2px solid #007bff;
    border-radius: 6px;
    padding: 8px;
    text-align: center;
    min-width: 80px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.preview-approver.unassigned {
    border-color: #dc3545;
    opacity: 0.7;
}

.approver-badge {
    display: inline-block;
    width: 18px;
    height: 18px;
    background: #007bff;
    color: white;
    border-radius: 50%;
    line-height: 18px;
    font-size: 9px;
    font-weight: bold;
    margin-bottom: 4px;
}

.preview-approver.unassigned .approver-badge {
    background: #dc3545;
}

.approver-name {
    font-size: 9px;
    font-weight: bold;
    margin-bottom: 2px;
}

.approver-connector {
    width: 15px;
    height: 2px;
    background: #6c757d;
    border-radius: 1px;
}

/* Button Groups */
.btn-group-toggle .btn {
    flex: 1;
}

/* Responsive */
@media (max-width: 768px) {
    .workflow-builder {
        flex-direction: column;
    }
    
    .workflow-step {
        min-width: 100%;
    }
    
    .layout-horizontal {
        flex-direction: column;
    }
    
    .layout-horizontal .step-connector {
        width: 2px;
        height: 20px;
    }
}
</style>
@endpush
