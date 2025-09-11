<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Email Approval Result</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #f4f6f9; }
    .result-card { max-width: 700px; margin: 3rem auto; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
    .result-header.success { background: linear-gradient(135deg, #28a745, #20c997); color: #fff; }
    .result-header.error { background: linear-gradient(135deg, #dc3545, #c82333); color: #fff; }
  </style>
</head>
<body>
  <div class="card result-card">
    <div class="card-header result-header {{ $type ?? 'success' }}">
      <h4 class="mb-0">{{ $title ?? 'Action Result' }}</h4>
    </div>
    <div class="card-body p-4">
      <p class="lead">{{ $message ?? 'Your request has been processed.' }}</p>
      @if(isset($approval) && $approval)
        <hr>
        <p><strong>Travel Order No:</strong> {{ $approval->travelOrder->local_travel_order_no }}</p>
        <p><strong>Employee:</strong> {{ $approval->travelOrder->employee_name }}</p>
        <p><strong>Current Status:</strong> {{ Str::headline($approval->travelOrder->status) }}</p>
      @endif
      <div class="mt-3">
        <a href="{{ config('app.url') }}" class="btn btn-outline-secondary">Go to System</a>
      </div>
    </div>
  </div>
</body>
</html>

