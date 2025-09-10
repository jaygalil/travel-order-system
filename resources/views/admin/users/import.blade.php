@extends('layouts.app')

@section('content')
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="fas fa-upload"></i> Import Users</h3>
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
      <i class="fas fa-arrow-left"></i> Back to Users
    </a>
  </div>

  <div class="row">
    <div class="col-md-8">
      <div class="card">
        <div class="card-header">
          <h5><i class="fas fa-file-csv"></i> Upload CSV File</h5>
        </div>
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

          <form action="{{ route('admin.users.import.process') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
              <label for="file" class="form-label">CSV File</label>
              <input type="file" class="form-control" id="file" name="file" accept=".csv,.txt" required>
              <div class="form-text">
                Upload a CSV file with user data. Maximum file size: 2MB
              </div>
            </div>
            
            <div class="d-flex justify-content-between">
              <a href="{{ route('admin.users.template') }}" class="btn btn-outline-info">
                <i class="fas fa-download"></i> Download Template
              </a>
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-upload"></i> Import Users
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card">
        <div class="card-header">
          <h5><i class="fas fa-info-circle"></i> Import Instructions</h5>
        </div>
        <div class="card-body">
          <h6>CSV Format Requirements:</h6>
          <ul class="small">
            <li>First row must contain headers</li>
            <li>Use comma (,) as separator</li>
            <li>Required columns: name, email</li>
            <li>Optional columns: position, role, sequence, approver_title</li>
          </ul>

          <h6 class="mt-3">Column Descriptions:</h6>
          <ul class="small">
            <li><strong>name:</strong> Full name of user</li>
            <li><strong>email:</strong> Valid email address</li>
            <li><strong>position:</strong> Job position/title</li>
            <li><strong>role:</strong> admin, approver, or employee</li>
            <li><strong>sequence:</strong> Approval order (1-10) for approvers</li>
            <li><strong>approver_title:</strong> Title shown in approvals</li>
          </ul>

          <h6 class="mt-3">Default Settings:</h6>
          <ul class="small">
            <li><strong>Password:</strong> 12345678!@#</li>
            <li><strong>Status:</strong> Active</li>
            <li><strong>Role:</strong> Employee (if not specified)</li>
          </ul>

          <div class="alert alert-info mt-3">
            <small>
              <i class="fas fa-lightbulb"></i>
              <strong>Tip:</strong> Download the template to see the exact format and example data.
            </small>
          </div>
        </div>
      </div>

      <div class="card mt-3">
        <div class="card-header">
          <h5><i class="fas fa-users-cog"></i> Roles & Permissions</h5>
        </div>
        <div class="card-body">
          <div class="mb-2">
            <span class="badge bg-danger me-2">Admin</span>
            <small>Full system access</small>
          </div>
          <div class="mb-2">
            <span class="badge bg-warning me-2">Approver</span>
            <small>Can approve travel orders</small>
          </div>
          <div class="mb-2">
            <span class="badge bg-secondary me-2">Employee</span>
            <small>Can create travel orders</small>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
