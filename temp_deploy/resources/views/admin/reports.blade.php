@extends('layouts.app')

@section('content')
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="fas fa-chart-bar"></i> Reports & Analytics</h3>
    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
      <i class="fas fa-arrow-left"></i> Back
    </a>
  </div>

  <!-- Status Distribution -->
  <div class="row mb-4">
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">
          <h5><i class="fas fa-pie-chart"></i> Status Distribution</h5>
        </div>
        <div class="card-body">
          @if(isset($statusStats) && $statusStats->count() > 0)
            @foreach($statusStats as $status => $count)
              <div class="d-flex justify-content-between mb-2">
                <span>{{ Str::headline($status) }}</span>
                <span class="badge bg-primary">{{ $count }}</span>
              </div>
            @endforeach
          @else
            <p class="text-muted">No data available</p>
          @endif
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card">
        <div class="card-header">
          <h5><i class="fas fa-map-marker-alt"></i> Top Destinations</h5>
        </div>
        <div class="card-body">
          @if(isset($topDestinations) && $topDestinations->count() > 0)
            @foreach($topDestinations as $destination)
              <div class="d-flex justify-content-between mb-2">
                <span>{{ Str::limit($destination->farthest_destination, 30) }}</span>
                <span class="badge bg-info">{{ $destination->count }}</span>
              </div>
            @endforeach
          @else
            <p class="text-muted">No data available</p>
          @endif
        </div>
      </div>
    </div>
  </div>

  <!-- Monthly Statistics -->
  <div class="card mb-4">
    <div class="card-header">
      <h5><i class="fas fa-calendar"></i> Monthly Statistics (Last 12 Months)</h5>
    </div>
    <div class="card-body">
      @if(isset($monthlyStats) && $monthlyStats->count() > 0)
        <div class="table-responsive">
          <table class="table table-sm">
            <thead>
              <tr>
                <th>Month</th>
                <th>Total</th>
                <th>Approved</th>
                <th>Pending</th>
                <th>Rejected</th>
              </tr>
            </thead>
            <tbody>
              @foreach($monthlyStats as $stat)
                <tr>
                  <td>{{ DateTime::createFromFormat('!m', $stat->month)->format('F') }} {{ $stat->year }}</td>
                  <td><span class="badge bg-primary">{{ $stat->total }}</span></td>
                  <td><span class="badge bg-success">{{ $stat->approved }}</span></td>
                  <td><span class="badge bg-warning">{{ $stat->pending }}</span></td>
                  <td><span class="badge bg-danger">{{ $stat->rejected }}</span></td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @else
        <p class="text-muted">No monthly data available</p>
      @endif
    </div>
  </div>

  <!-- Recent Activity -->
  <div class="card">
    <div class="card-header">
      <h5><i class="fas fa-history"></i> Recent Activity</h5>
    </div>
    <div class="card-body">
      @if(isset($recentActivity) && $recentActivity->count() > 0)
        <div class="table-responsive">
          <table class="table table-hover">
            <thead class="table-light">
              <tr>
                <th>Order No.</th>
                <th>Employee</th>
                <th>Prepared By</th>
                <th>Status</th>
                <th>Created</th>
              </tr>
            </thead>
            <tbody>
              @foreach($recentActivity as $order)
                <tr>
                  <td><strong>{{ $order->local_travel_order_no }}</strong></td>
                  <td>{{ $order->employee_name }}</td>
                  <td>{{ $order->preparedBy->name ?? 'N/A' }}</td>
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
                    <span class="badge bg-{{ $badge }}">{{ Str::headline($order->status) }}</span>
                  </td>
                  <td>{{ $order->created_at->format('M d, Y g:i A') }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @else
        <p class="text-muted">No recent activity</p>
      @endif
    </div>
  </div>
</div>
@endsection
