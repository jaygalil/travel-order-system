@extends('layouts.app')

@section('content')
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Travel Order Details</h3>
    <div>
      <a href="{{ route('travel-orders.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Back
      </a>
      @if($travelOrder->status === 'draft')
        <a href="{{ route('travel-orders.edit', $travelOrder) }}" class="btn btn-warning">
          <i class="fas fa-edit"></i> Edit
        </a>
        <form action="{{ route('travel-orders.submit', $travelOrder) }}" method="POST" class="d-inline">
          @csrf
          <button class="btn btn-success" onclick="return confirm('Submit this travel order for approval?')">
            <i class="fas fa-paper-plane"></i> Submit for Approval
          </button>
        </form>
      @endif
      <a href="{{ route('travel-orders.pdf', $travelOrder) }}" class="btn btn-primary">
        <i class="fas fa-file-pdf"></i> Download PDF
      </a>
    </div>
  </div>

  <div class="row">
    <div class="col-md-8">
      <!-- Travel Order Information -->
      <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between">
          <h5 class="mb-0"><i class="fas fa-route"></i> Travel Order Information</h5>
          @php
            switch($travelOrder->status) {
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
          <span class="badge bg-{{ $badge }} fs-6">{{ Str::headline($travelOrder->status) }}</span>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <p><strong>Travel Order No:</strong><br>{{ $travelOrder->local_travel_order_no }}</p>
              <p><strong>Employee Name:</strong><br>{{ $travelOrder->employee_name }}</p>
              <p><strong>Position:</strong><br>{{ $travelOrder->position }}</p>
              <p><strong>Division/Agency:</strong><br>{{ $travelOrder->division_agency }}</p>
            </div>
            <div class="col-md-6">
              <p><strong>Travel From:</strong><br>{{ $travelOrder->date_of_travel_from->format('F d, Y') }}</p>
              <p><strong>Travel To:</strong><br>{{ $travelOrder->date_of_travel_to->format('F d, Y') }}</p>
              <p><strong>Source of Fund:</strong><br>{{ $travelOrder->source_of_fund }}</p>
              <p><strong>Official Vehicle:</strong><br>{{ $travelOrder->official_vehicle ?: 'Not specified' }}</p>
            </div>
          </div>

          <div class="row mt-3">
            <div class="col-12">
              <p><strong>Purpose:</strong></p>
              <div class="bg-light p-3 rounded">{{ $travelOrder->purpose }}</div>
            </div>
          </div>

          <div class="row mt-3">
            <div class="col-12">
              <p><strong>Destination:</strong></p>
              <div class="bg-light p-3 rounded">{{ $travelOrder->destination }}</div>
            </div>
          </div>

          <div class="row mt-3">
            <div class="col-md-8">
              <p><strong>Farthest Destination:</strong><br>{{ $travelOrder->farthest_destination }}</p>
            </div>
            <div class="col-md-4">
              <p><strong>Approx Distance:</strong><br>{{ number_format($travelOrder->approx_distance, 2) }} km</p>
            </div>
          </div>

          <div class="row mt-3">
            <div class="col-md-6">
              <p><strong>Prepared By:</strong><br>{{ $travelOrder->preparedBy->name }}</p>
            </div>
            <div class="col-md-6">
              <p><strong>Created:</strong><br>{{ $travelOrder->created_at->format('F d, Y g:i A') }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <!-- Approval Progress -->
      <div class="card shadow-sm">
        <div class="card-header">
          <h5 class="mb-0"><i class="fas fa-check-circle"></i> Approval Progress</h5>
        </div>
        <div class="card-body">
          @if($travelOrder->status === 'draft')
            <div class="text-center text-muted">
              <i class="fas fa-file-edit fa-3x mb-3"></i>
              <p>Travel order is in draft status. Submit for approval to start the approval process.</p>
            </div>
          @else
            <div class="progress mb-3">
              <div class="progress-bar" 
                   role="progressbar" 
                   style="width: {{ $travelOrder->getApprovalProgress() }}%">
                {{ $travelOrder->getApprovalProgress() }}%
              </div>
            </div>

            @foreach($travelOrder->approvals as $approval)
              <div class="approval-step {{ $approval->status === 'pending' ? 'current' : ($approval->status !== 'pending' ? 'completed' : '') }} p-3 mb-3 rounded">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <h6 class="mb-1">{{ $approval->approver_name }}</h6>
                    <small class="text-muted">{{ $approval->approver_title }}</small>
                  </div>
                  <div class="text-end">
                    @php
                      switch($approval->status) {
                        case 'pending':
                          $statusBadge = 'warning';
                          break;
                        case 'forwarded':
                          $statusBadge = 'info';
                          break;
                        case 'endorsed':
                          $statusBadge = 'primary';
                          break;
                        case 'verified':
                          $statusBadge = 'success';
                          break;
                        case 'approved':
                          $statusBadge = 'success';
                          break;
                        case 'rejected':
                          $statusBadge = 'danger';
                          break;
                        default:
                          $statusBadge = 'secondary';
                          break;
                      }
                    @endphp
                    <span class="badge bg-{{ $statusBadge }}">{{ Str::headline($approval->status) }}</span>
                    @if($approval->action_date)
                      <br><small class="text-muted">{{ $approval->action_date->format('M d, Y g:i A') }}</small>
                    @endif
                  </div>
                </div>
                @if($approval->comments)
                  <div class="mt-2">
                    <small class="text-muted"><strong>Comments:</strong> {{ $approval->comments }}</small>
                  </div>
                @endif
              </div>
            @endforeach
          @endif
        </div>
      </div>

      @if($travelOrder->status === 'pending_approval' || $travelOrder->status === 'approved')
        <div class="card shadow-sm mt-3">
          <div class="card-header">
            <h6 class="mb-0"><i class="fas fa-info-circle"></i> Actions</h6>
          </div>
          <div class="card-body">
            @if($travelOrder->status === 'pending_approval')
              <form action="{{ route('travel-orders.cancel', $travelOrder) }}" method="POST" class="d-grid">
                @csrf
                <button class="btn btn-outline-warning" onclick="return confirm('Cancel this travel order and return to draft?')">
                  <i class="fas fa-times"></i> Cancel & Return to Draft
                </button>
              </form>
            @endif
            
            @if($travelOrder->status === 'approved')
              <div class="alert alert-success mb-0">
                <i class="fas fa-check-circle"></i> This travel order has been fully approved.
                @if($travelOrder->approved_at)
                  <br><small>Approved on {{ $travelOrder->approved_at->format('F d, Y g:i A') }}</small>
                @endif
              </div>
            @endif
          </div>
        </div>
      @endif
    </div>
  </div>
</div>
@endsection
