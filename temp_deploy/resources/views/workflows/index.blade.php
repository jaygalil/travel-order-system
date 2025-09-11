@extends('layouts.app')

@section('title', 'Workflow Templates')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-sitemap mr-2"></i>
                        Workflow Templates
                    </h4>
                    <div>
                        <a href="{{ route('workflows.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus mr-1"></i>
                            Create New Template
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

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <!-- Search and Filter Form -->
                    <form method="GET" action="{{ route('workflows.index') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="search">Search Templates</label>
                                    <input type="text" class="form-control" id="search" name="search" 
                                           value="{{ request('search') }}" placeholder="Search by name or description...">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="visibility">Visibility</label>
                                    <select class="form-control" id="visibility" name="visibility">
                                        <option value="">All Types</option>
                                        <option value="private" {{ request('visibility') === 'private' ? 'selected' : '' }}>Private</option>
                                        <option value="public" {{ request('visibility') === 'public' ? 'selected' : '' }}>Public</option>
                                        <option value="department" {{ request('visibility') === 'department' ? 'selected' : '' }}>Department</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="created_by">Created By</label>
                                    <select class="form-control" id="created_by" name="created_by">
                                        <option value="">All Users</option>
                                        <option value="me" {{ request('created_by') === 'me' ? 'selected' : '' }}>My Templates</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-secondary">
                                            <i class="fas fa-search mr-1"></i>
                                            Filter
                                        </button>
                                        <a href="{{ route('workflows.index') }}" class="btn btn-outline-secondary ml-1">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Templates Table -->
                    @if($templates->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead style="background-color: #e7f3ff;">
                                    <tr>
                                        <th style="color: #000000 !important; font-weight: 700; background-color: #d1e7fd;">NAME</th>
                                        <th style="color: #000000 !important; font-weight: 700; background-color: #d1e7fd;">DESCRIPTION</th>
                                        <th class="text-center" style="color: #000000 !important; font-weight: 700; background-color: #d1e7fd;">STEPS</th>
                                        <th class="text-center" style="color: #000000 !important; font-weight: 700; background-color: #d1e7fd;">VISIBILITY</th>
                                        <th style="color: #000000 !important; font-weight: 700; background-color: #d1e7fd;">CREATED BY</th>
                                        <th style="color: #000000 !important; font-weight: 700; background-color: #d1e7fd;">CREATED</th>
                                        <th class="text-center" style="color: #000000 !important; font-weight: 700; background-color: #d1e7fd;">STATUS</th>
                                        <th class="text-center" style="color: #000000 !important; font-weight: 700; background-color: #d1e7fd;">ACTIONS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($templates as $template)
                                        <tr>
                                            <td style="color: #000000 !important; font-weight: 600;">
                                                <strong style="color: #000000 !important;">{{ $template->name }}</strong>
                                                @if($template->is_default)
                                                    <span class="badge badge-success ml-1">Default</span>
                                                @endif
                                            </td>
                                            <td style="color: #000000 !important; font-weight: 500;">
                                                {{ Str::limit($template->description ?? 'No description', 50) }}
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-pill badge-info font-weight-bold" style="font-size: 0.9em; padding: 0.4em 0.8em;">
                                                    {{ $template->steps->count() }} {{ $template->steps->count() == 1 ? 'step' : 'steps' }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                @switch($template->visibility)
                                                    @case('private')
                                                        <span class="badge badge-pill badge-dark font-weight-bold" style="font-size: 0.9em; padding: 0.4em 0.8em;">
                                                            <i class="fas fa-lock mr-1"></i>Private
                                                        </span>
                                                        @break
                                                    @case('public')
                                                        <span class="badge badge-pill badge-success font-weight-bold" style="font-size: 0.9em; padding: 0.4em 0.8em;">
                                                            <i class="fas fa-globe mr-1"></i>Public
                                                        </span>
                                                        @break
                                                    @case('department')
                                                        <span class="badge badge-pill badge-primary font-weight-bold" style="font-size: 0.9em; padding: 0.4em 0.8em;">
                                                            <i class="fas fa-users mr-1"></i>{{ $template->department }}
                                                        </span>
                                                        @break
                                                @endswitch
                                            </td>
                                            <td style="color: #000000 !important; font-weight: 600;">{{ $template->createdBy->name }}</td>
                                            <td style="color: #000000 !important; font-weight: 600;">{{ $template->created_at->format('M d, Y') }}</td>
                                            <td class="text-center">
                                                @if($template->is_active)
                                                    <span class="badge badge-pill badge-success font-weight-bold" style="font-size: 0.9em; padding: 0.4em 0.8em;">
                                                        <i class="fas fa-check-circle mr-1"></i>Active
                                                    </span>
                                                @else
                                                    <span class="badge badge-pill badge-danger font-weight-bold" style="font-size: 0.9em; padding: 0.4em 0.8em;">
                                                        <i class="fas fa-times-circle mr-1"></i>Inactive
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('workflows.show', $template->id) }}" class="btn btn-sm btn-info" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @can('update', $template)
                                                        <a href="{{ route('workflows.edit', $template->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        @if(!$template->is_default)
                                                            <form method="POST" action="{{ route('workflows.set-default', $template->id) }}" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-success" title="Set as Default" 
                                                                        onclick="return confirm('Set this template as your default?')">
                                                                    <i class="fas fa-star"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                        <form method="POST" action="{{ route('workflows.destroy', $template->id) }}" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete" 
                                                                    onclick="return confirm('Are you sure you want to delete this template?')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-dark font-weight-bold">
                                Showing {{ $templates->firstItem() }} to {{ $templates->lastItem() }} of {{ $templates->total() }} templates
                            </div>
                            <div>
                                {{ $templates->withQueryString()->links() }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-sitemap fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No workflow templates found</h5>
                            <p class="text-muted">Create your first workflow template to get started.</p>
                            <a href="{{ route('workflows.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus mr-1"></i>
                                Create New Template
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Make ALL table text BLACK and readable */
    .table {
        color: #000000 !important;
    }
    
    .table td {
        color: #000000 !important;
        font-weight: 500 !important;
        vertical-align: middle;
    }
    
    .table tbody tr {
        background-color: #ffffff;
    }
    
    .table tbody tr:nth-of-type(odd) {
        background-color: #f8f9fa;
    }
    
    .table tbody tr:hover {
        background-color: #e9ecef;
    }
    
    /* Make badges more visible */
    .badge {
        font-size: 0.9rem !important;
        font-weight: 600 !important;
        padding: 0.4em 0.8em !important;
    }
    
    .badge-info {
        background-color: #17a2b8 !important;
        color: white !important;
    }
    
    .badge-dark {
        background-color: #343a40 !important;
        color: white !important;
    }
    
    .badge-success {
        background-color: #28a745 !important;
        color: white !important;
    }
    
    .badge-primary {
        background-color: #007bff !important;
        color: white !important;
    }
    
    /* Table borders */
    .table-bordered {
        border: 1px solid #dee2e6;
    }
    
    .table-bordered td, .table-bordered th {
        border: 1px solid #dee2e6;
    }
    
    /* Ensure pagination is visible */
    .pagination .page-link {
        color: #000000 !important;
    }
    
    .btn-group .btn {
        margin-right: 2px;
    }
    .btn-group .btn:last-child {
        margin-right: 0;
    }
</style>
@endpush
