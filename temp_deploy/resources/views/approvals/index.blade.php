@extends('layouts.app')

@section('content')
<div class="container">
  <h3 class="mb-3"><i class="fas fa-check-circle"></i> My Approvals</h3>

  <div class="card shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>Travel Order</th>
              <th>Employee</th>
              <th>Travel Date</th>
              <th>Destination</th>
              <th>My Role</th>
              <th>Status</th>
              <th>Action Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($approvals as $approval)
              <tr>
                <td><strong>{{ $approval->travelOrder->local_travel_order_no }}</strong></td>
                <td>{{ $approval->travelOrder->employee_name }}</td>
                <td>{{ $approval->travelOrder->date_of_travel_from->format('M d, Y') }}</td>
                <td>{{ Str::limit($approval->travelOrder->farthest_destination, 30) }}</td>
                <td>{{ $approval->approver_title }}</td>
                <td>
                  @php
                    switch($approval->status) {
                      case 'pending':
                        $badge = 'warning';
                        break;
                      case 'forwarded':
                        $badge = 'info';
                        break;
                      case 'endorsed':
                        $badge = 'primary';
                        break;
                      case 'verified':
                        $badge = 'success';
                        break;
                      case 'approved':
                        $badge = 'success';
                        break;
                      case 'rejected':
                        $badge = 'danger';
                        break;
                      default:
                        $badge = 'secondary';
                        break;
                    }
                  @endphp
                  <span class="badge bg-{{ $badge }}">{{ Str::headline($approval->status) }}</span>
                </td>
                <td>
                  @if($approval->action_date)
                    {{ $approval->action_date->format('M d, Y g:i A') }}
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </td>
                <td>
                  <a href="{{ route('approvals.show', $approval) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-eye"></i> View
                  </a>
                  @if($approval->status === 'pending')
                    <a href="{{ route('approvals.show', $approval) }}" class="btn btn-sm btn-warning">
                      <i class="fas fa-pen"></i> Review
                    </a>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8" class="text-center text-muted p-4">
                  <i class="fas fa-clipboard-check fa-2x mb-3 d-block"></i>
                  No approvals assigned to you yet.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    @if($approvals->hasPages())
      <div class="card-footer">
        {{ $approvals->links() }}
      </div>
    @endif
  </div>
</div>
@endsection
