@extends('layouts.app')

@section('content')
<div class="container">
  <div class="row">
    <div class="col-md-8">
      <div class="card shadow-sm">
        <div class="card-header">
          <h5 class="mb-0">Approval Details</h5>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <strong>Travel Order:</strong> {{ $approval->travelOrder->local_travel_order_no }}<br>
            <strong>Employee:</strong> {{ $approval->travelOrder->employee_name }}<br>
            <strong>Destination:</strong> {{ $approval->travelOrder->farthest_destination }}<br>
            <strong>Travel Dates:</strong> {{ $approval->travelOrder->date_of_travel_from->format('M d, Y') }} - {{ $approval->travelOrder->date_of_travel_to->format('M d, Y') }}
          </div>

          <div class="mb-3">
            <strong>Your Role:</strong> {{ $approval->approver_title }}<br>
            <strong>Status:</strong> <span class="badge bg-warning">{{ Str::headline($approval->status) }}</span>
          </div>

          <form action="{{ route('approvals.process', $approval) }}" method="POST">
            @csrf
            <div class="mb-3">
              <label for="action" class="form-label">Action</label>
              <select name="action" id="action" class="form-control" required>
                <option value="">Select an action</option>
                <option value="forwarded">Forward</option>
                <option value="endorsed">Endorse</option>
                <option value="verified">Verify</option>
                <option value="approved">Approve</option>
                <option value="rejected">Reject</option>
              </select>
            </div>
            <div class="mb-3">
              <label for="comments" class="form-label">Comments (optional)</label>
              <textarea name="comments" id="comments" rows="3" class="form-control" placeholder="Add remarks or reasons if necessary..."></textarea>
            </div>
            <div class="d-flex justify-content-end">
              <a href="{{ route('approvals.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
              <button class="btn btn-primary">
                <i class="fas fa-paper-plane"></i> Submit
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card shadow-sm">
        <div class="card-header">
          <h6 class="mb-0">Travel Order Summary</h6>
        </div>
        <div class="card-body">
          <p><strong>Purpose:</strong><br>{{ $approval->travelOrder->purpose }}</p>
          <p><strong>Destination:</strong><br>{{ $approval->travelOrder->destination }}</p>
          <p><strong>Source of Fund:</strong><br>{{ $approval->travelOrder->source_of_fund }}</p>
          <p><strong>Official Vehicle:</strong><br>{{ $approval->travelOrder->official_vehicle ?: 'Not specified' }}</p>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
