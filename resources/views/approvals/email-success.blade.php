<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approval Submitted Successfully</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .success-card {
            max-width: 600px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
            overflow: hidden;
            animation: slideUp 0.6s ease-out;
        }
        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .success-header {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            text-align: center;
            padding: 3rem 2rem;
        }
        .success-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            animation: bounce 1s ease-in-out 0.5s;
        }
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }
        .travel-summary {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            margin: 1.5rem 0;
        }
        .status-badge {
            font-size: 1.1rem;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            text-transform: capitalize;
        }
        .action-approved { background: linear-gradient(135deg, #28a745, #20c997); }
        .action-forwarded { background: linear-gradient(135deg, #007bff, #0056b3); }
        .action-endorsed { background: linear-gradient(135deg, #17a2b8, #138496); }
        .action-verified { background: linear-gradient(135deg, #ffc107, #e0a800); color: #000; }
        .action-rejected { background: linear-gradient(135deg, #dc3545, #c82333); }
        .security-notice {
            border-radius: 12px;
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            border: none;
            color: #1565c0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-card">
            <div class="success-header">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h2 class="mb-0">Approval Submitted Successfully!</h2>
                <p class="mt-2 mb-0">Your decision has been recorded and processed.</p>
            </div>
            
            <div class="card-body p-4">
                <!-- Message Display -->
                <div class="text-center mb-4">
                    <div class="alert alert-success" role="alert" style="border-radius: 12px; border: none;">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>{{ $message }}</strong>
                    </div>
                </div>

                <!-- Travel Order Summary -->
                <div class="travel-summary">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-clipboard-list"></i> Travel Order Summary
                    </h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Travel Order No:</strong><br>{{ $approval->travelOrder->local_travel_order_no }}</p>
                            <p class="mb-2"><strong>Employee:</strong><br>{{ $approval->travelOrder->employee_name }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Destination:</strong><br>{{ $approval->travelOrder->farthest_destination }}</p>
                            <p class="mb-2"><strong>Travel Date:</strong><br>{{ $approval->travelOrder->date_of_travel_from->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Approval Details -->
                <div class="mb-4">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-user-check"></i> Your Approval Decision
                    </h5>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <strong>Approver:</strong> {{ $approval->approver_name }}<br>
                            <strong>Role:</strong> {{ $approval->approver_title }}
                        </div>
                        <div class="text-end">
                            @php
                                $actionClass = 'action-' . $approval->status;
                            @endphp
                            <span class="badge status-badge text-white {{ $actionClass }}">
                                @switch($approval->status)
                                    @case('approved')
                                        <i class="fas fa-check-circle"></i> Approved
                                        @break
                                    @case('rejected')
                                        <i class="fas fa-times-circle"></i> Rejected
                                        @break
                                    @case('forwarded')
                                        <i class="fas fa-arrow-right"></i> Forwarded
                                        @break
                                    @case('endorsed')
                                        <i class="fas fa-stamp"></i> Endorsed
                                        @break
                                    @case('verified')
                                        <i class="fas fa-shield-check"></i> Verified
                                        @break
                                    @default
                                        <i class="fas fa-check"></i> {{ ucfirst($approval->status) }}
                                @endswitch
                            </span>
                        </div>
                    </div>
                    
                    @if($approval->comments)
                        <div class="bg-light p-3 rounded">
                            <strong>Comments:</strong><br>
                            {{ $approval->comments }}
                        </div>
                    @endif
                    
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="fas fa-clock"></i> 
                            Processed on {{ $approval->action_date->format('F d, Y \a\t g:i A') }}
                        </small>
                    </div>
                </div>

                <!-- Next Steps Information -->
                <div class="alert security-notice">
                    <h6 class="mb-2">
                        <i class="fas fa-route"></i> What Happens Next?
                    </h6>
                    <ul class="mb-0 ps-3">
                        @if(in_array($approval->status, ['forwarded', 'endorsed', 'verified']))
                            <li>The travel order has been forwarded to the next approver in the workflow</li>
                            <li>An email notification will be sent to the next approver</li>
                            <li>The travel order requester will be notified of this progress</li>
                        @elseif($approval->status === 'approved')
                            <li>The travel order has been fully approved and is now ready for processing</li>
                            <li>The employee and requester will be notified of the approval</li>
                            <li>Travel arrangements can now proceed as authorized</li>
                        @elseif($approval->status === 'rejected')
                            <li>The travel order has been rejected and returned to the requester</li>
                            <li>The employee and requester will be notified with your comments</li>
                            <li>The requester may revise and resubmit if appropriate</li>
                        @endif
                    </ul>
                </div>

                <!-- Security Notice -->
                <div class="alert alert-warning" style="border-radius: 12px; border: none;">
                    <div class="d-flex">
                        <div class="me-3">
                            <i class="fas fa-shield-alt fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="mb-1">Security Notice</h6>
                            <small>This approval link has been used and is no longer valid. Thank you for your prompt response to this approval request.</small>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="text-center mt-4">
                    <p class="text-muted mb-0">
                        <small>If you have any questions about this travel order, please contact the system administrator or the requesting department.</small>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
