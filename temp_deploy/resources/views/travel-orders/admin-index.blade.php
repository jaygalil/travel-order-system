@extends('layouts.app')

@section('content')
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="fas fa-cogs"></i> All Travel Orders (Admin)</h3>
    <div>
      <a href="{{ route('travel-orders.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> New Travel Order
      </a>
      <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Dashboard
      </a>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Order No.</th>
              <th>Employee</th>
              <th>Prepared By</th>
              <th>Travel Dates</th>
              <th>Destination</th>
              <th>Status</th>
              <th>Progress</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          @forelse ($travelOrders as $order)
            <tr>
              <td>{{ $loop->iteration + ($travelOrders->currentPage() - 1) * $travelOrders->perPage() }}</td>
              <td><strong>{{ $order->local_travel_order_no }}</strong></td>
              <td>
                {{ $order->employee_name }}
                <br><small class="text-muted">{{ $order->position }}</small>
              </td>
              <td>
                {{ $order->preparedBy->name ?? 'N/A' }}
                <br><small class="text-muted">{{ $order->created_at->format('M d, Y') }}</small>
              </td>
              <td>
                {{ $order->date_of_travel_from->format('M d') }} - 
                {{ $order->date_of_travel_to->format('M d, Y') }}
              </td>
              <td>{{ Str::limit($order->farthest_destination, 30) }}</td>
              <td>
                @php
                  switch($order->status) {
                    case 'draft':
                      $badge = 'secondary';
                      break;
                    case 'pending_approval':
                      $badge = 'warning';
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
                <span class="badge bg-{{ $badge }} status-badge">{{ Str::headline($order->status) }}</span>
              </td>
              <td>
                @if($order->status !== 'draft')
                  <div class="progress" style="height: 6px;">
                    <div class="progress-bar" role="progressbar" style="width: {{ $order->getApprovalProgress() }}%"></div>
                  </div>
                  <small class="text-muted">{{ $order->getApprovalProgress() }}%</small>
                @else
                  <small class="text-muted">Not submitted</small>
                @endif
              </td>
              <td>
                <div class="btn-group btn-group-sm" role="group">
                  <a href="{{ route('travel-orders.show', $order) }}" class="btn btn-outline-primary" title="View Details">
                    <i class="fas fa-eye"></i>
                  </a>
                  <a href="{{ route('travel-orders.pdf.preview', $order) }}" class="btn btn-outline-secondary" title="Preview PDF">
                    <i class="fas fa-file-pdf"></i>
                  </a>
                  @if($order->status === 'draft')
                    <a href="{{ route('travel-orders.edit', $order) }}" class="btn btn-outline-warning" title="Edit">
                      <i class="fas fa-edit"></i>
                    </a>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="9" class="text-center text-muted p-4">
                <i class="fas fa-inbox fa-2x mb-3 d-block"></i>
                No travel orders found in the system yet.
              </td>
            </tr>
          @endforelse
          </tbody>
        </table>
      </div>
    </div>
    @if($travelOrders->hasPages())
      <div class="card-footer">
        {{ $travelOrders->links() }}
      </div>
    @endif
  </div>

  <!-- Summary Cards -->
  <div class="row mt-4">
    <div class="col-md-3">
      <div class="card bg-info text-white">
        <div class="card-body text-center">
          <h4>{{ $travelOrders->where('status', 'draft')->count() }}</h4>
          <small>Drafts</small>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card bg-warning text-white">
        <div class="card-body text-center">
          <h4>{{ $travelOrders->where('status', 'pending_approval')->count() }}</h4>
          <small>Pending</small>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card bg-success text-white">
        <div class="card-body text-center">
          <h4>{{ $travelOrders->where('status', 'approved')->count() }}</h4>
          <small>Approved</small>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card bg-danger text-white">
        <div class="card-body text-center">
          <h4>{{ $travelOrders->where('status', 'rejected')->count() }}</h4>
          <small>Rejected</small>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
