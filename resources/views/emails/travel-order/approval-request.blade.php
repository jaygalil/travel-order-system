<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travel Order Approval Request</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #007bff;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f8f9fa;
            padding: 20px;
            border: 1px solid #dee2e6;
        }
        .details {
            background-color: white;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            border-left: 4px solid #007bff;
        }
        .btn {
            display: inline-block;
            background-color: #28a745;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 5px;
            text-align: center;
        }
        .btn-danger {
            background-color: #dc3545;
        }
        .footer {
            background-color: #6c757d;
            color: white;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            border-radius: 0 0 8px 8px;
        }
        .approval-info {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Travel Order Approval Request</h1>
    </div>
    
    <div class="content">
        <p>Dear <strong>{{ $approval->approver_name }}</strong>,</p>
        
        <p>You have been requested to approve a travel order. Please review the details below:</p>
        
        <div class="details">
            <h3>Travel Order Details</h3>
            <p><strong>Order Number:</strong> {{ $travelOrder->local_travel_order_no }}</p>
            <p><strong>Employee:</strong> {{ $travelOrder->employee_name }}</p>
            <p><strong>Position:</strong> {{ $travelOrder->employee_position }}</p>
            <p><strong>Department:</strong> {{ $travelOrder->office_department }}</p>
            <p><strong>Destination:</strong> {{ $travelOrder->farthest_destination }}</p>
            <p><strong>Purpose:</strong> {{ $travelOrder->purpose_of_travel }}</p>
            <p><strong>Travel Date:</strong> {{ date('F d, Y', strtotime($travelOrder->inclusive_dates_from)) }} to {{ date('F d, Y', strtotime($travelOrder->inclusive_dates_to)) }}</p>
            <p><strong>Current Status:</strong> {{ Str::headline($travelOrder->status) }}</p>
        </div>
        
        <div class="approval-info">
            <h4>Approval Information</h4>
            <p><strong>Approval Level:</strong> {{ $approval->approval_level }}</p>
            <p><strong>Your Role:</strong> {{ $approval->approver_position }}</p>
        </div>
        
        @if($approvalUrl)
        <div style="text-align: center; margin: 30px 0;">
            <p><strong>Click below to approve or reject this travel order:</strong></p>
            <a href="{{ $approvalUrl }}&action=approve" class="btn">✓ Approve</a>
            <a href="{{ $approvalUrl }}&action=reject" class="btn btn-danger">✗ Reject</a>
        </div>
        @endif
        
        <p>You can also log in to the Travel Order Management System to review and process this approval:</p>
        <p><a href="{{ config('app.url') }}/approvals">{{ config('app.url') }}/approvals</a></p>
        
        <p>If you have any questions about this travel order, please contact the requester or the system administrator.</p>
        
        <p>Best regards,<br>
        <strong>{{ config('app.name') }}</strong></p>
    </div>
    
    <div class="footer">
        This is an automated message from {{ config('app.name') }}. Please do not reply to this email.
    </div>
</body>
</html>
