@extends('layouts.app')

@section('title', ' - Dashboard')

@section('content')
<div class="container-fluid">
  <!-- Header Section -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
          <h1 class="h2 mb-1 text-gradient">Dashboard</h1>
          <p class="text-muted mb-0 lead">Welcome back, <strong>{{ auth()->user()->name }}</strong>! Here's your travel order overview.</p>
        </div>
        <div class="mt-3 mt-md-0">
          <a href="{{ route('travel-orders.create') }}" class="btn btn-modern btn-primary btn-lg">
            <i class="fas fa-plus me-2"></i>New Travel Order
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Statistics Cards -->
  <div class="row g-4 mb-5">
    <div class="col-6 col-md-3">
      <div class="stat-card stat-primary animate-slide-up">
        <div class="stat-icon">
          <i class="fas fa-suitcase-rolling"></i>
        </div>
        <div class="stat-value">{{ $stats['total'] ?? 0 }}</div>
        <div class="stat-label">Total Orders</div>
        <div class="stat-change positive">
          <i class="fas fa-arrow-up"></i> +12% this month
        </div>
      </div>
    </div>

    <div class="col-6 col-md-3">
      <div class="stat-card stat-warning animate-slide-up" style="animation-delay: 0.1s;">
        <div class="stat-icon" style="background: var(--gradient-warning);">
          <i class="fas fa-clock"></i>
        </div>
        <div class="stat-value">{{ $stats['pending'] ?? 0 }}</div>
        <div class="stat-label">Pending Approval</div>
        @if(($stats['pending'] ?? 0) > 0)
          <div class="stat-change negative">
            <i class="fas fa-exclamation-triangle"></i> Action needed
          </div>
        @else
          <div class="stat-change positive">
            <i class="fas fa-check"></i> All caught up!
          </div>
        @endif
      </div>
    </div>

    <div class="col-6 col-md-3">
      <div class="stat-card stat-success animate-slide-up" style="animation-delay: 0.2s;">
        <div class="stat-icon" style="background: var(--gradient-success);">
          <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-value">{{ $stats['approved'] ?? 0 }}</div>
        <div class="stat-label">Approved</div>
        <div class="stat-change positive">
          <i class="fas fa-arrow-up"></i> +8% this month
        </div>
      </div>
    </div>

    <div class="col-6 col-md-3">
      <div class="stat-card stat-secondary animate-slide-up" style="animation-delay: 0.3s;">
        <div class="stat-icon" style="background: var(--gradient-secondary);">
          <i class="fas fa-file-edit"></i>
        </div>
        <div class="stat-value">{{ $stats['draft'] ?? 0 }}</div>
        <div class="stat-label">Draft Orders</div>
        @if(($stats['draft'] ?? 0) > 0)
          <div class="stat-change">
            <i class="fas fa-edit"></i> {{ $stats['draft'] }} to complete
          </div>
        @else
          <div class="stat-change positive">
            <i class="fas fa-check"></i> No drafts
          </div>
        @endif
      </div>
    </div>
  </div>

  <!-- Main Content Grid -->
  <div class="row g-4">
    <!-- Recent Travel Orders -->
    <div class="col-lg-8">
      <div class="card card-modern animate-slide-up" style="animation-delay: 0.4s;">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0 d-flex align-items-center">
            <i class="fas fa-history me-2 text-primary"></i>
            Recent Travel Orders
          </h5>
          <a href="{{ route('travel-orders.index') }}" class="btn btn-modern btn-outline btn-sm">
            View All <i class="fas fa-arrow-right ms-1"></i>
          </a>
        </div>
        <div class="card-body p-0">
          @if(($recentTravelOrders ?? []) && count($recentTravelOrders) > 0)
            <div class="table-responsive">
              <table class="table table-modern table-hover mb-0">
                <thead>
                  <tr>
                    <th>Order No.</th>
                    <th class="d-none d-md-table-cell">Destination</th>
                    <th class="d-none d-sm-table-cell">Travel Date</th>
                    <th>Status</th>
                    <th width="120">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($recentTravelOrders ?? [] as $order)
                    <tr>
                      <td>
                        <div class="d-flex flex-column">
                          <strong class="text-primary">{{ $order->local_travel_order_no }}</strong>
                          <small class="text-muted d-md-none">{{ Str::limit($order->farthest_destination ?? $order->destination, 25) }}</small>
                        </div>
                      </td>
                      <td class="d-none d-md-table-cell">
                        <span class="text-truncate" style="max-width: 200px; display: inline-block;">
                          {{ $order->farthest_destination ?? $order->destination }}
                        </span>
                      </td>
                      <td class="d-none d-sm-table-cell">
                        <div class="d-flex flex-column">
                          <span>{{ $order->date_of_travel_from->format('M d, Y') }}</span>
                          @if($order->date_of_travel_to && $order->date_of_travel_to != $order->date_of_travel_from)
                            <small class="text-muted">to {{ $order->date_of_travel_to->format('M d, Y') }}</small>
                          @endif
                        </div>
                      </td>
                      <td>
                        @php
                          $statusConfig = [
                            'draft' => ['class' => 'secondary', 'icon' => 'fas fa-edit'],
                            'pending_approval' => ['class' => 'warning', 'icon' => 'fas fa-clock'],
                            'approved' => ['class' => 'success', 'icon' => 'fas fa-check-circle'],
                            'rejected' => ['class' => 'danger', 'icon' => 'fas fa-times-circle'],
                          ];
                          $config = $statusConfig[$order->status] ?? ['class' => 'secondary', 'icon' => 'fas fa-question'];
                        @endphp
                        <span class="badge badge-modern badge-{{ $config['class'] }}">
                          <i class="{{ $config['icon'] }} me-1"></i>
                          {{ Str::headline($order->status) }}
                        </span>
                      </td>
                      <td>
                        <div class="btn-group" role="group">
                          <a href="{{ route('travel-orders.show', $order) }}" 
                             class="btn btn-sm btn-outline-primary" 
                             title="View Details">
                            <i class="fas fa-eye"></i>
                            <span class="d-none d-lg-inline ms-1">View</span>
                          </a>
                          @if($order->status === 'draft')
                            <a href="{{ route('travel-orders.edit', $order) }}" 
                               class="btn btn-sm btn-outline-warning" 
                               title="Edit">
                              <i class="fas fa-edit"></i>
                            </a>
                          @endif
                        </div>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <div class="text-center py-5">
              <div class="mb-3">
                <i class="fas fa-suitcase-rolling fa-3x text-muted opacity-50"></i>
              </div>
              <h6 class="text-muted">No travel orders yet</h6>
              <p class="text-muted mb-4">Start by creating your first travel order to see it here.</p>
              <a href="{{ route('travel-orders.create') }}" class="btn btn-modern btn-primary">
                <i class="fas fa-plus me-2"></i>Create First Order
              </a>
            </div>
          @endif
        </div>
      </div>
    </div>

    <!-- Quick Actions Sidebar -->
    <div class="col-lg-4">
      <div class="row g-4">
        <!-- Quick Actions -->
        <div class="col-12">
          <div class="card card-modern animate-slide-up" style="animation-delay: 0.5s;">
            <div class="card-header">
              <h5 class="mb-0 d-flex align-items-center">
                <i class="fas fa-bolt me-2 text-warning"></i>
                Quick Actions
              </h5>
            </div>
            <div class="card-body">
              <div class="d-grid gap-3">
                <a href="{{ route('travel-orders.create') }}" class="btn btn-modern btn-primary">
                  <i class="fas fa-plus me-2"></i>New Travel Order
                </a>
                <a href="{{ route('travel-orders.index') }}" class="btn btn-modern btn-outline">
                  <i class="fas fa-list me-2"></i>My Travel Orders
                </a>
                <a href="{{ route('approvals.index') }}" class="btn btn-modern btn-outline">
                  <i class="fas fa-check-circle me-2"></i>My Approvals
                  @if(isset($pendingApprovalCount) && $pendingApprovalCount > 0)
                    <span class="badge bg-warning rounded-pill ms-2">{{ $pendingApprovalCount }}</span>
                  @endif
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- Pending Approvals -->
        @if(isset($pendingApprovals) && $pendingApprovals->count() > 0)
          <div class="col-12">
            <div class="card card-modern animate-slide-up" style="animation-delay: 0.6s;">
              <div class="card-header">
                <h6 class="mb-0 d-flex align-items-center text-warning">
                  <i class="fas fa-exclamation-triangle me-2"></i>
                  Pending Approvals ({{ $pendingApprovals->count() }})
                </h6>
              </div>
              <div class="card-body">
                @foreach($pendingApprovals as $approval)
                  <div class="d-flex justify-content-between align-items-start p-3 rounded glass mb-3 last:mb-0">
                    <div class="flex-grow-1">
                      <div class="fw-semibold text-primary">{{ $approval->travelOrder->local_travel_order_no }}</div>
                      <div class="small text-muted">{{ $approval->travelOrder->employee_name }}</div>
                      <div class="small text-muted mt-1">
                        <i class="fas fa-calendar-alt me-1"></i>
                        {{ $approval->travelOrder->date_of_travel_from->format('M d, Y') }}
                      </div>
                    </div>
                    <a href="{{ route('approvals.show', $approval) }}" class="btn btn-sm btn-warning ms-3">
                      <i class="fas fa-eye me-1"></i>Review
                    </a>
                  </div>
                @endforeach
              </div>
            </div>
          </div>
        @endif

        <!-- Recent Activity -->
        <div class="col-12">
          <div class="card card-modern animate-slide-up" style="animation-delay: 0.7s;">
            <div class="card-header">
              <h6 class="mb-0 d-flex align-items-center">
                <i class="fas fa-clock me-2 text-info"></i>
                Recent Activity
              </h6>
            </div>
            <div class="card-body">
              <div class="timeline">
                <div class="timeline-item">
                  <div class="timeline-marker bg-success"></div>
                  <div class="timeline-content">
                    <p class="mb-1 small">Travel order <strong>TO-2024-001</strong> approved</p>
                    <small class="text-muted">2 hours ago</small>
                  </div>
                </div>
                <div class="timeline-item">
                  <div class="timeline-marker bg-primary"></div>
                  <div class="timeline-content">
                    <p class="mb-1 small">New travel order <strong>TO-2024-002</strong> created</p>
                    <small class="text-muted">5 hours ago</small>
                  </div>
                </div>
                <div class="timeline-item">
                  <div class="timeline-marker bg-warning"></div>
                  <div class="timeline-content">
                    <p class="mb-1 small">Document uploaded for <strong>TO-2024-001</strong></p>
                    <small class="text-muted">1 day ago</small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  @can('viewAny', App\Models\TravelOrder::class)
    <!-- Admin Section -->
    <div class="row mt-5">
      <div class="col-12">
        <div class="card card-modern animate-slide-up" style="animation-delay: 0.8s;">
          <div class="card-header bg-gradient-secondary text-white">
            <h5 class="mb-0 d-flex align-items-center">
              <i class="fas fa-cogs me-2"></i>
              Administration Panel
            </h5>
            <p class="mb-0 small opacity-75">System management and oversight tools</p>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-4">
                <div class="d-grid">
                  <a href="{{ route('admin.travel-orders.index') }}" class="btn btn-modern btn-outline d-flex flex-column align-items-center p-4">
                    <i class="fas fa-list fa-2x mb-2 text-primary"></i>
                    <strong>All Travel Orders</strong>
                    <small class="text-muted">View and manage all orders</small>
                  </a>
                </div>
              </div>
              <div class="col-md-4">
                <div class="d-grid">
                  <a href="{{ route('admin.users.index') }}" class="btn btn-modern btn-outline d-flex flex-column align-items-center p-4">
                    <i class="fas fa-users fa-2x mb-2 text-success"></i>
                    <strong>Manage Users</strong>
                    <small class="text-muted">User accounts and permissions</small>
                  </a>
                </div>
              </div>
              <div class="col-md-4">
                <div class="d-grid">
                  <a href="{{ route('admin.reports') }}" class="btn btn-modern btn-outline d-flex flex-column align-items-center p-4">
                    <i class="fas fa-chart-bar fa-2x mb-2 text-warning"></i>
                    <strong>Reports & Analytics</strong>
                    <small class="text-muted">System insights and metrics</small>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  @endcan
</div>
@endsection
