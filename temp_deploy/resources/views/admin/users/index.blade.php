@extends('layouts.app')

@section('content')
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="fas fa-users"></i> Users Management</h3>
    <div>
      <a href="{{ route('admin.users.import') }}" class="btn btn-info me-2">
        <i class="fas fa-upload"></i> Import Users
      </a>
      <a href="{{ route('admin.users.create') }}" class="btn btn-success me-2">
        <i class="fas fa-plus"></i> Add User
      </a>
      <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Back
      </a>
    </div>
  </div>

  <!-- Filters -->
  <div class="card mb-3">
    <div class="card-body">
      <form method="GET" action="{{ route('admin.users.index') }}">
        <div class="row g-3">
          <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Search by name, email, or position" value="{{ request('search') }}">
          </div>
          <div class="col-md-3">
            <select name="role" class="form-control">
              <option value="">All Roles</option>
              @foreach($roles as $role)
                <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>{{ ucfirst($role->name) }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-search"></i> Search
            </button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
              <i class="fas fa-times"></i> Clear
            </a>
          </div>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          {{ session('success') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      @endif

      @if($users->count() > 0)
        <div class="table-responsive">
          <table class="table table-hover">
            <thead class="table-light">
              <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Position</th>
                <th>Role</th>
                <th>Approver Sequence</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($users as $user)
                <tr>
                  <td><strong>{{ $user->name }}</strong></td>
                  <td>{{ $user->email }}</td>
                  <td>{{ $user->position ?? 'N/A' }}</td>
                  <td>
                    @foreach($user->roles as $role)
                      @php
                        switch($role->name) {
                          case 'admin':
                          case 'super-admin':
                            $badge = 'danger';
                            break;
                          case 'approver':
                            $badge = 'warning';
                            break;
                          default:
                            $badge = 'secondary';
                            break;
                        }
                      @endphp
                      <span class="badge bg-{{ $badge }} me-1">{{ ucfirst($role->name) }}</span>
                    @endforeach
                  </td>
                  <td>
                    @if($user->approver_sequence)
                      <span class="badge bg-info">{{ $user->approver_sequence }}</span>
                      <small class="d-block text-muted">{{ $user->approver_title }}</small>
                    @else
                      <span class="text-muted">N/A</span>
                    @endif
                  </td>
                  <td>
                    @if($user->is_active)
                      <span class="badge bg-success">Active</span>
                    @else
                      <span class="badge bg-secondary">Inactive</span>
                    @endif
                  </td>
                  <td>{{ $user->created_at->format('M d, Y') }}</td>
                  <td>
                    <div class="btn-group" role="group">
                      <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                        <i class="fas fa-edit"></i>
                      </a>
                    </div>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        
        {{ $users->appends(request()->query())->links() }}
      @else
        <div class="text-center py-4">
          <i class="fas fa-users fa-3x text-muted mb-3"></i>
          <p class="text-muted">No users found.</p>
          <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add First User
          </a>
        </div>
      @endif
    </div>
  </div>

  <!-- Approval Flow Info -->
  <div class="card mt-4">
    <div class="card-header">
      <h5><i class="fas fa-sitemap"></i> Current Approval Flow</h5>
    </div>
    <div class="card-body">
      @php
        $approvers = App\Models\User::role('approver')
            ->whereNotNull('approver_sequence')
            ->where('is_active', true)
            ->orderBy('approver_sequence')
            ->get();
      @endphp
      
      @if($approvers->count() > 0)
        <div class="row">
          @foreach($approvers as $approver)
            <div class="col-md-2 text-center mb-3">
              <div class="card border-primary">
                <div class="card-body p-2">
                  <div class="badge bg-primary mb-1">Level {{ $approver->approver_sequence }}</div>
                  <h6 class="card-title mb-1" style="font-size: 0.85rem;">{{ $approver->name }}</h6>
                  <p class="card-text mb-0" style="font-size: 0.75rem;">{{ $approver->approver_title }}</p>
                </div>
              </div>
            </div>
            @if(!$loop->last)
              <div class="col-auto d-flex align-items-center">
                <i class="fas fa-arrow-right text-primary"></i>
              </div>
            @endif
          @endforeach
        </div>
      @else
        <div class="text-muted">
          <i class="fas fa-exclamation-triangle"></i> 
          No approvers configured. Travel orders will use default approval flow.
        </div>
      @endif
    </div>
  </div>
</div>
@endsection
