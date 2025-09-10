@extends('layouts.app')

@section('title', 'Create Simple Workflow Template')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-plus mr-2"></i>
                        Create Simple Workflow Template
                    </h4>
                </div>

                <form method="POST" action="{{ route('workflows.store') }}">
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
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Template Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required>
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
                                        <option value="private" {{ old('visibility') === 'private' ? 'selected' : '' }}>
                                            Private (Only me)
                                        </option>
                                        <option value="public" {{ old('visibility') === 'public' ? 'selected' : '' }}>
                                            Public (All users)
                                        </option>
                                        <option value="department" {{ old('visibility') === 'department' ? 'selected' : '' }}>
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
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="3" 
                                              placeholder="Describe the purpose of this workflow template...">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Single Step -->
                        <h5 class="mb-3">
                            <i class="fas fa-list-ol mr-2"></i>
                            Approval Step
                        </h5>

                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Step Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="steps[0][step_name]" value="Approval Step" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Action Type <span class="text-danger">*</span></label>
                                            <select class="form-control" name="steps[0][action_type]" required>
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
                                            <select class="form-control" name="steps[0][approver_type]" required>
                                                <option value="user">Specific User</option>
                                                <option value="role">Role</option>
                                                <option value="position">Position</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Select User <span class="text-danger">*</span></label>
                                            <select class="form-control" name="steps[0][approver_user_id]">
                                                <option value="">Select User</option>
                                                @if(isset($users))
                                                    @foreach($users as $user)
                                                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Approver Display Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="steps[0][approver_name]" required>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Approver Title <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="steps[0][approver_title]" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Step Description</label>
                                            <textarea class="form-control" name="steps[0][step_description]" rows="2" 
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

                    <div class="card-footer">
                        <div class="row">
                            <div class="col-md-6">
                                <a href="{{ route('workflows.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left mr-1"></i>
                                    Cancel
                                </a>
                            </div>
                            <div class="col-md-6 text-right">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-1"></i>
                                    Create Template
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
