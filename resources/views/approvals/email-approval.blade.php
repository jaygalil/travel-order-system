<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travel Order Approval - {{ $approval->travelOrder->local_travel_order_no }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .approval-card {
            max-width: 800px;
            margin: 2rem auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            text-align: center;
            padding: 2rem;
        }
        .travel-details {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin: 1rem 0;
        }
        .btn-approve { background: linear-gradient(135deg, #28a745, #20c997); border: none; }
        .btn-reject { background: linear-gradient(135deg, #dc3545, #c82333); border: none; }
        .btn-forward { background: linear-gradient(135deg, #007bff, #0056b3); border: none; }
        .btn:hover { transform: translateY(-2px); transition: all 0.3s ease; }
        .alert {
            border-radius: 10px;
            border: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="approval-card">
            <div class="card-header">
                <h3 class="mb-0">
                    <i class="fas fa-clipboard-check"></i>
                    Travel Order Approval Request
                </h3>
                <p class="mb-0 mt-2">You have been requested to review and approve a travel order</p>
            </div>
            
            <div class="card-body p-4">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <strong>Validation Errors:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger">
                        <strong>Error:</strong> {{ session('error') }}
                    </div>
                @endif
                
                @if (session('success'))
                    <div class="alert alert-success">
                        <strong>Success:</strong> {{ session('success') }}
                    </div>
                @endif

                <!-- Travel Order Summary -->
                <div class="travel-details">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-info-circle"></i> Travel Order Details
                    </h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Travel Order No:</strong><br>{{ $approval->travelOrder->local_travel_order_no }}</p>
                            <p><strong>Employee Name:</strong><br>{{ $approval->travelOrder->employee_name }}</p>
                            <p><strong>Position:</strong><br>{{ $approval->travelOrder->position }}</p>
                            <p><strong>Division/Agency:</strong><br>{{ $approval->travelOrder->division_agency }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Travel Dates:</strong><br>
                                {{ $approval->travelOrder->date_of_travel_from->format('M d, Y') }} - 
                                {{ $approval->travelOrder->date_of_travel_to->format('M d, Y') }}
                            </p>
                            <p><strong>Destination:</strong><br>{{ $approval->travelOrder->destination }}</p>
                            <p><strong>Farthest Destination:</strong><br>{{ $approval->travelOrder->farthest_destination }}</p>
                            <p><strong>Approx. Distance:</strong><br>{{ number_format($approval->travelOrder->approx_distance, 2) }} km</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <p><strong>Purpose of Travel:</strong><br>{{ $approval->travelOrder->purpose }}</p>
                            <p><strong>Source of Fund:</strong><br>{{ $approval->travelOrder->source_of_fund }}</p>
                            @if($approval->travelOrder->official_vehicle)
                                <p><strong>Official Vehicle:</strong><br>{{ $approval->travelOrder->official_vehicle }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Approval Action Form -->
                <div class="mt-4">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-user-check"></i> Your Approval Action
                    </h5>
                    <div class="bg-light p-3 rounded mb-3">
                        <strong>Your Role:</strong> {{ $approval->approver_title }}<br>
                        <strong>Approver Name:</strong> {{ $approval->approver_name }}
                    </div>

                    <form action="{{ route('approval.email.process', $token) }}" method="POST" id="approvalForm">
                        @csrf
                        <input type="hidden" name="debug" value="1">
                        <!-- Debug info -->
                        <div class="d-none" id="debug-info">
                            <p>Form Action: {{ route('approval.email.process', $token) }}</p>
                            <p>Token: {{ $token }}</p>
                            <p>CSRF Token: {{ csrf_token() }}</p>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="action" class="form-label"><strong>Select Action *</strong></label>
                                <div class="btn-group-vertical w-100" role="group">
                                    <input type="radio" class="btn-check" name="action" id="approve" value="approved" required>
                                    <label class="btn btn-success btn-lg mb-2" for="approve">
                                        <i class="fas fa-check-circle"></i> Approve
                                    </label>

                                    <input type="radio" class="btn-check" name="action" id="forward" value="forwarded" required>
                                    <label class="btn btn-primary btn-lg mb-2" for="forward">
                                        <i class="fas fa-arrow-right"></i> Forward
                                    </label>

                                    <input type="radio" class="btn-check" name="action" id="endorse" value="endorsed" required>
                                    <label class="btn btn-info btn-lg mb-2" for="endorse">
                                        <i class="fas fa-stamp"></i> Endorse
                                    </label>

                                    <input type="radio" class="btn-check" name="action" id="verify" value="verified" required>
                                    <label class="btn btn-warning btn-lg mb-2" for="verify">
                                        <i class="fas fa-shield-check"></i> Verify
                                    </label>

                                    <input type="radio" class="btn-check" name="action" id="reject" value="rejected" required>
                                    <label class="btn btn-danger btn-lg" for="reject">
                                        <i class="fas fa-times-circle"></i> Reject
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="comments" class="form-label"><strong>Comments/Remarks</strong> <small class="text-muted">(optional)</small></label>
                            <textarea name="comments" id="comments" rows="4" class="form-control" 
                                placeholder="Add any comments, remarks, or reasons for your decision..."></textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane"></i> Submit Decision
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="showDebugInfo()">
                                <i class="fas fa-bug"></i> Show Debug Info
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Security Notice -->
                <div class="alert alert-info mt-4">
                    <i class="fas fa-shield-alt"></i>
                    <strong>Security Notice:</strong> This approval link is unique and secure. It can only be used once and will expire after use.
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add smooth transitions
        document.querySelectorAll('.btn').forEach(btn => {
            btn.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
            });
            btn.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
        
        // Add form submission debugging
        document.querySelector('form').addEventListener('submit', function(e) {
            const selectedAction = document.querySelector('input[name="action"]:checked');
            
            if (!selectedAction) {
                e.preventDefault();
                alert('Please select an action before submitting.');
                return false;
            }
            
            // Show loading state
            const submitBtn = document.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            submitBtn.disabled = true;
            
            // Log for debugging
            console.log('Form submitted with action:', selectedAction.value);
            console.log('Form action URL:', this.action);
            console.log('Comments:', document.getElementById('comments').value);
            
            // Re-enable button after 30 seconds as fallback
            setTimeout(function() {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 30000);
        });
        
        // Add click handlers to action buttons for better UX
        document.querySelectorAll('input[name="action"]').forEach(radio => {
            radio.addEventListener('change', function() {
                console.log('Action selected:', this.value);
            });
        });
        
        // Debug function
        function showDebugInfo() {
            const debugInfo = document.getElementById('debug-info');
            debugInfo.classList.toggle('d-none');
            
            const form = document.getElementById('approvalForm');
            const formData = new FormData(form);
            
            console.log('=== FORM DEBUG INFO ===');
            console.log('Form action:', form.action);
            console.log('Form method:', form.method);
            
            console.log('Form data:');
            for (let [key, value] of formData.entries()) {
                console.log(key + ':', value);
            }
            
            const selectedAction = document.querySelector('input[name="action"]:checked');
            console.log('Selected action:', selectedAction ? selectedAction.value : 'None');
            
            alert('Debug info has been logged to console. Press F12 to view.');
        }
        
        // Add error handling for AJAX if needed
        window.addEventListener('beforeunload', function(e) {
            const submitBtn = document.querySelector('button[type="submit"]');
            if (submitBtn.disabled) {
                e.preventDefault();
                e.returnValue = 'Form is being processed. Are you sure you want to leave?';
            }
        });
    </script>
</body>
</html>
