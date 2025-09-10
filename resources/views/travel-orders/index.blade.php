@extends('layouts.app')

@section('content')
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">My Travel Orders</h3>
    <a href="{{ route('travel-orders.create') }}" class="btn btn-primary">
      <i class="fas fa-plus"></i> New Travel Order
    </a>
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
              <th>Travel Dates</th>
              <th>Destination</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          @forelse ($travelOrders as $order)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td><strong>{{ $order->local_travel_order_no }}</strong></td>
              <td>{{ $order->employee_name }}</td>
              <td>{{ $order->date_of_travel_from->format('M d, Y') }} - {{ $order->date_of_travel_to->format('M d, Y') }}</td>
              <td>{{ Str::limit($order->destination, 40) }}</td>
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
                <a href="{{ route('travel-orders.show', $order) }}" class="btn btn-sm btn-outline-primary">
                  <i class="fas fa-eye"></i>
                </a>
                <a href="{{ route('travel-orders.pdf.preview', $order) }}" class="btn btn-sm btn-outline-secondary">
                  <i class="fas fa-file-pdf"></i>
                </a>
                @if($order->status === 'draft')
                  <a href="{{ route('travel-orders.edit', $order) }}" class="btn btn-sm btn-outline-warning">
                    <i class="fas fa-edit"></i>
                  </a>
                  <form action="{{ route('travel-orders.submit', $order) }}" method="POST" class="d-inline">
                    @csrf
                    <button class="btn btn-sm btn-success" onclick="return confirm('Submit this travel order for approval?')">
                      <i class="fas fa-paper-plane"></i>
                    </button>
                  </form>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center text-muted p-4">
                No travel orders found. Click "New Travel Order" to create one.
              </td>
            </tr>
          @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer">
      {{ $travelOrders->links() }}
    </div>
  </div>
</div>
@endsection

