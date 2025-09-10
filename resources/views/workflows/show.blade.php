@extends('layouts.app')

@section('title', 'Workflow Template: ' . $workflowTemplate->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- Template Header -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0">
                            <i class="fas fa-sitemap mr-2"></i>
                            {{ $workflowTemplate->name }}
                            @if($workflowTemplate->is_default)
                                <span class="badge badge-success ml-2">Default</span>
                            @endif
                        </h4>
                        <small class="text-muted">
                            Created by {{ $workflowTemplate->createdBy->name }} 
                            on {{ $workflowTemplate->created_at->format('M d, Y \a\t g:i A') }}
                        </small>
                    </div>
                    <div class="btn-group">
                        @can('update', $workflowTemplate)
                            <a href="{{ route('workflows.edit', $workflowTemplate->id) }}" class="btn btn-warning">
                                <i class="fas fa-edit mr-1"></i>
                                Edit Template
                            </a>
                            @if(!$workflowTemplate->is_default)
                                <form method="POST" action="{{ route('workflows.set-default', $workflowTemplate->id) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success" 
                                            onclick="return confirm('Set this template as your default?')">
                                        <i class="fas fa-star mr-1"></i>
                                        Set as Default
                                    </button>
                                </form>
                            @endif
                        @endcan
                        <a href="{{ route('workflows.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i>
                            Back to Templates
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <!-- Template Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Template Details</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td width="30%"><strong>Name:</strong></td>
                                    <td>{{ $workflowTemplate->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Description:</strong></td>
                                    <td>{{ $workflowTemplate->description ?? 'No description provided' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Total Steps:</strong></td>
                                    <td>{{ $workflowTemplate->steps->count() }} steps</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        @if($workflowTemplate->is_active)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-secondary">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Layout:</strong></td>
                                    <td>
                                        @if($workflowTemplate->layout === 'horizontal')
                                            <span class="badge badge-info">
                                                <i class="fas fa-arrows-alt-h mr-1"></i>Horizontal
                                            </span>
                                        @else
                                            <span class="badge badge-primary">
                                                <i class="fas fa-arrows-alt-v mr-1"></i>Vertical
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Default Template:</strong></td>
                                    <td>
                                        @if($workflowTemplate->is_default)
                                            <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Yes</span>
                                        @else
                                            <span class="badge badge-secondary"><i class="fas fa-times mr-1"></i>No</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Visibility & Access</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td width="30%"><strong>Visibility:</strong></td>
                                    <td>
                                        @switch($workflowTemplate->visibility)
                                            @case('private')
                                                <span class="badge badge-secondary">
                                                    <i class="fas fa-lock mr-1"></i>Private
                                                </span>
                                                <small class="text-muted d-block">Only visible to you</small>
                                                @break
                                            @case('public')
                                                <span class="badge badge-success">
                                                    <i class="fas fa-globe mr-1"></i>Public
                                                </span>
                                                <small class="text-muted d-block">Visible to all users</small>
                                                @break
                                            @case('department')
                                                <span class="badge badge-primary">
                                                    <i class="fas fa-users mr-1"></i>{{ $workflowTemplate->department }}
                                                </span>
                                                <small class="text-muted d-block">Visible to {{ $workflowTemplate->department }} department</small>
                                                @break
                                        @endswitch
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Created:</strong></td>
                                    <td>{{ $workflowTemplate->created_at->format('M d, Y \a\t g:i A') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Last Updated:</strong></td>
                                    <td>{{ $workflowTemplate->updated_at->format('M d, Y \a\t g:i A') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Created By:</strong></td>
                                    <td>
                                        {{ $workflowTemplate->createdBy->name }}
                                        <small class="text-muted d-block">{{ $workflowTemplate->createdBy->email }}</small>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Workflow Steps -->
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-list-ol mr-2"></i>
                        Workflow Steps ({{ $workflowTemplate->steps->count() }})
                    </h5>
                    <div class="layout-info">
                        @if($workflowTemplate->layout === 'horizontal')
                            <span class="badge badge-info">
                                <i class="fas fa-arrows-alt-h mr-1"></i>Horizontal Layout
                            </span>
                        @else
                            <span class="badge badge-primary">
                                <i class="fas fa-arrows-alt-v mr-1"></i>Vertical Layout
                            </span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @if($workflowTemplate->steps->count() > 0)
                        <!-- Workflow Preview -->
                        <div class="workflow-preview workflow-{{ $workflowTemplate->layout ?? 'vertical' }} mb-4">
                            @foreach($workflowTemplate->steps->sortBy('sequence') as $index => $step)
                                <div class="preview-step">
                                    <div class="step-badge">{{ $step->sequence }}</div>
                                    <div class="step-content">
                                        <div class="step-title">{{ $step->step_name }}</div>
                                        <div class="step-approver">{{ $step->approver_name }}</div>
                                        <div class="step-action">
                                            <span class="badge bg-{{ $step->action_type === 'approve' ? 'success' : ($step->action_type === 'review' ? 'warning' : 'primary') }}">
                                                {{ ucfirst($step->action_type) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                @if(!$loop->last)
                                    <div class="step-connector"></div>
                                @endif
                            @endforeach
                        </div>
                        
                        <!-- Detailed Step Information -->
                        <div class="workflow-details">
                            <h6 class="mb-3"><i class="fas fa-info-circle mr-1"></i>Detailed Step Information</h6>
                            @foreach($workflowTemplate->steps->sortBy('sequence') as $index => $step)
                                <div class="workflow-step-item {{ $loop->last ? '' : 'border-bottom' }} py-3">
                                    <div class="row align-items-start">
                                        <!-- Step Number -->
                                        <div class="col-md-1 text-center">
                                            <div class="step-number-circle">
                                                {{ $step->sequence }}
                                            </div>
                                        </div>
                                        
                                        <!-- Step Details -->
                                        <div class="col-md-7">
                                            <div class="step-details">
                                                <h6 class="mb-1">
                                                    {{ $step->step_name }}
                                                    <span class="badge badge-{{ $step->action_type === 'approve' ? 'success' : ($step->action_type === 'review' ? 'warning' : 'info') }} ml-2">
                                                        {{ ucfirst($step->action_type) }}
                                                    </span>
                                                </h6>
                                                
                                                @if($step->step_description)
                                                    <p class="text-muted small mb-2">{{ $step->step_description }}</p>
                                                @endif
                                                
                                                <!-- Approver Info -->
                                                <div class="approver-info">
                                                    <div class="row">
                                                        <div class="col-sm-6">
                                                            <strong>Approver:</strong> {{ $step->approver_name }}<br>
                                                            <strong>Title:</strong> {{ $step->approver_title }}
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <strong>Type:</strong> 
                                                            @switch($step->approver_type)
                                                                @case('user')
                                                                    <span class="badge badge-primary">Specific User</span>
                                                                    @if($step->approverUser)
                                                                        <small class="text-muted d-block">{{ $step->approverUser->email }}</small>
                                                                    @endif
                                                                    @break
                                                                @case('role')
                                                                    <span class="badge badge-info">Role: {{ ucfirst($step->approver_role) }}</span>
                                                                    @break
                                                                @case('position')
                                                                    <span class="badge badge-warning">Position: {{ $step->approver_position }}</span>
                                                                    @break
                                                                @case('department')
                                                                    <span class="badge badge-success">Department: {{ $step->approver_department }}</span>
                                                                    @break
                                                            @endswitch
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Step Properties -->
                                        <div class="col-md-4 text-right">
                                            <div class="step-properties">
                                                @if($step->is_required)
                                                    <span class="badge badge-danger mb-1">Required</span><br>
                                                @else
                                                    <span class="badge badge-secondary mb-1">Optional</span><br>
                                                @endif
                                                
                                                @if($step->can_delegate)
                                                    <span class="badge badge-info">Delegatable</span><br>
                                                @endif
                                                
                                                @if($step->step_type === 'parallel')
                                                    <span class="badge badge-warning">Parallel</span><br>
                                                @else
                                                    <span class="badge badge-secondary">Sequential</span><br>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-exclamation-triangle fa-2x text-muted mb-2"></i>
                            <p class="text-muted">No workflow steps defined for this template.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Usage Statistics -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar mr-2"></i>
                        Usage Statistics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="stat-card text-center">
                                <div class="stat-number">{{ $workflowTemplate->travelOrders()->count() }}</div>
                                <div class="stat-label">Travel Orders</div>
                                <small class="text-muted">Total using this template</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card text-center">
                                <div class="stat-number">{{ $workflowTemplate->travelOrders()->where('status', 'pending')->count() }}</div>
                                <div class="stat-label">Pending</div>
                                <small class="text-muted">Currently in progress</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card text-center">
                                <div class="stat-number">{{ $workflowTemplate->travelOrders()->where('status', 'approved')->count() }}</div>
                                <div class="stat-label">Approved</div>
                                <small class="text-muted">Successfully completed</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card text-center">
                                <div class="stat-number">{{ $workflowTemplate->travelOrders()->where('status', 'rejected')->count() }}</div>
                                <div class="stat-label">Rejected</div>
                                <small class="text-muted">Not approved</small>
                            </div>
                        </div>
                    </div>
                    
                    @if($workflowTemplate->travelOrders()->count() > 0)
                        <hr>
                        <h6>Recent Travel Orders Using This Template</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Employee</th>
                                        <th>Destination</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($workflowTemplate->travelOrders()->latest()->take(5)->get() as $order)
                                        <tr>
                                            <td><span class="badge badge-secondary">{{ $order->order_number }}</span></td>
                                            <td>{{ $order->user->name }}</td>
                                            <td>{{ $order->destination_city }}</td>
                                            <td>
                                                <span class="badge badge-{{ 
                                                    $order->status === 'approved' ? 'success' : 
                                                    ($order->status === 'rejected' ? 'danger' : 
                                                    ($order->status === 'pending' ? 'warning' : 'secondary')) }}">
                                                    {{ ucfirst($order->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $order->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <a href="{{ route('travel-orders.show', $order) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($workflowTemplate->travelOrders()->count() > 5)
                            <div class="text-center">
                                <small class="text-muted">
                                    Showing 5 of {{ $workflowTemplate->travelOrders()->count() }} total orders
                                </small>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Workflow Preview Styles */
.workflow-preview {
    padding: 30px 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 2px dashed #dee2e6;
}

.workflow-vertical {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.workflow-horizontal {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
}

.preview-step {
    background: white;
    border: 2px solid #007bff;
    border-radius: 12px;
    padding: 20px 15px;
    margin: 8px;
    text-align: center;
    min-width: 180px;
    max-width: 200px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.preview-step:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.15);
}

.step-badge {
    display: inline-block;
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
    border-radius: 50%;
    line-height: 40px;
    font-weight: bold;
    font-size: 16px;
    margin-bottom: 12px;
}

.step-title {
    font-weight: 600;
    margin-bottom: 8px;
    color: #2c3e50;
    font-size: 14px;
}

.step-approver {
    font-size: 12px;
    color: #6c757d;
    margin-bottom: 10px;
    font-weight: 500;
}

.step-connector {
    background: linear-gradient(135deg, #007bff, #0056b3);
    margin: 8px 0;
    border-radius: 2px;
}

.workflow-vertical .step-connector {
    width: 3px;
    height: 25px;
}

.workflow-horizontal .step-connector {
    width: 25px;
    height: 3px;
    margin: 0 8px;
}

/* Step number circle */
.step-number-circle {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 35px;
    height: 35px;
    background: #007bff;
    color: white;
    border-radius: 50%;
    font-weight: bold;
    font-size: 14px;
    margin-bottom: 10px;
}

/* Step properties styling */
.step-properties {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.step-properties .badge {
    font-size: 10px;
    padding: 4px 8px;
}

/* Statistics cards */
.stat-card {
    padding: 20px;
    background: white;
    border-radius: 8px;
    border: 1px solid #e3e6f0;
    margin-bottom: 20px;
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    color: #007bff;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 0.9rem;
    font-weight: 600;
    color: #5a5c69;
    margin-bottom: 3px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .workflow-horizontal {
        flex-direction: column;
    }
    
    .workflow-horizontal .step-connector {
        width: 3px;
        height: 25px;
        margin: 8px 0;
    }
    
    .preview-step {
        min-width: 150px;
        max-width: 100%;
    }
}
</style>
@endpush

@push('styles')
<style>
    .workflow-step-item {
        padding: 20px;
        position: relative;
    }
    
    .step-number-circle {
        width: 40px;
        height: 40px;
        background: #007bff;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        margin: 0 auto;
    }
    
    .step-details h6 {
        color: #333;
        font-weight: 600;
    }
    
    .approver-info {
        background: #f8f9fa;
        padding: 10px;
        border-radius: 5px;
        font-size: 0.9em;
    }
    
    .stat-card {
        padding: 15px;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        margin-bottom: 15px;
    }
    
    .stat-number {
        font-size: 2rem;
        font-weight: bold;
        color: #007bff;
    }
    
    .stat-label {
        font-weight: 600;
        color: #333;
        margin-bottom: 5px;
    }
    
    .badge {
        font-size: 0.75em;
    }
    
    .table-sm td {
        padding: 0.5rem;
    }
</style>
@endpush
