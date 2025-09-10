<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Travel Order Created</title>
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
            background-color: #28a745;
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
            border-left: 4px solid #28a745;
        }
        .next-steps {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        .footer {
            background-color: #6c757d;
            color: white;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            border-radius: 0 0 8px 8px;
        }
        .btn {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 10px;
            margin: 15px 0;
            border-radius: 5px;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>✅ Travel Order Created Successfully</h1>
    </div>
    
    <div class="content">
        <p>Hello <strong>{{ $travelOrder->employee_name }}</strong>,</p>
        
        <p>Your travel order has been successfully created in the Travel Order Management System. Here are the details:</p>
        
        <div class="details">
            <h3>Travel Order Details</h3>
            <p><strong>Order Number:</strong> {{ $travelOrder->local_travel_order_no }}</p>
            <p><strong>Employee:</strong> {{ $travelOrder->employee_name }}</p>
            <p><strong>Position:</strong> {{ $travelOrder->position }}</p>
            <p><strong>Department:</strong> {{ $travelOrder->division_agency }}</p>
            <p><strong>Destination:</strong> {{ $travelOrder->farthest_destination }}</p>
            <p><strong>Purpose:</strong> {{ $travelOrder->purpose }}</p>
            <p><strong>Travel Date:</strong> {{ $travelOrder->date_of_travel_from->format('F d, Y') }} to {{ $travelOrder->date_of_travel_to->format('F d, Y') }}</p>
            <p><strong>Current Status:</strong> {{ Str::headline($travelOrder->status) }}</p>
            <p><strong>Created:</strong> {{ $travelOrder->created_at->format('F d, Y g:i A') }}</p>
        </div>
        
        @if($travelOrder->status == 'draft')
        <div class="warning">
            <strong>⚠️ Important:</strong> Your travel order is currently in <strong>DRAFT</strong> status. 
            You need to submit it for approval to proceed with the approval process.
        </div>
        
        <div class="next-steps">
            <h4>Next Steps:</h4>
            <ol>
                <li><strong>Review</strong> your travel order details carefully</li>
                <li><strong>Make any necessary edits</strong> if needed</li>
                <li><strong>Submit for approval</strong> when you're ready</li>
                <li><strong>Track the approval progress</strong> through the system</li>
            </ol>
        </div>
        @elseif($travelOrder->status == 'pending_approval')
        <div class="next-steps">
            <h4>What happens next:</h4>
            <ul>
                <li>Your travel order will go through the 5-step approval process</li>
                <li>Each approver will receive an email notification</li>
                <li>You'll be notified of any status changes</li>
                <li>The approval process typically takes 2-5 business days</li>
            </ul>
        </div>
        @endif
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ config('app.url') }}/travel-orders/{{ $travelOrder->id }}" class="btn">View Travel Order</a>
            @if($travelOrder->status == 'draft')
                <a href="{{ config('app.url') }}/travel-orders/{{ $travelOrder->id }}/edit" class="btn" style="background-color: #ffc107; color: #212529;">Edit Travel Order</a>
            @endif
        </div>
        
        <div style="background-color: #e9ecef; padding: 15px; margin: 15px 0; border-radius: 5px;">
            <h4>Travel Order Management System</h4>
            <p>Access your travel orders anytime at: <a href="{{ config('app.url') }}/travel-orders">{{ config('app.url') }}/travel-orders</a></p>
            <p>Features available to you:</p>
            <ul>
                <li>View all your travel orders and their status</li>
                <li>Edit draft travel orders</li>
                <li>Download approved travel orders as PDF</li>
                <li>Track approval progress in real-time</li>
                <li>Submit expense reports upon return</li>
            </ul>
        </div>
        
        <p>If you have any questions about your travel order or need assistance, please contact your supervisor or the system administrator.</p>
        
        <p>Safe travels!<br>
        <strong>{{ config('app.name') }}</strong></p>
    </div>
    
    <div class="footer">
        This is an automated message from {{ config('app.name') }}. Please do not reply to this email.
    </div>
</body>
</html>
