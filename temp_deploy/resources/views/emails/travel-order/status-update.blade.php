<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travel Order Status Update</title>
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
        .status-update {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            text-align: center;
        }
        .status-approved {
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        .status-rejected {
            background-color: #f8d7da;
            border-color: #f5c6cb;
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
    </style>
</head>
<body>
    <div class="header">
        <h1>Travel Order Status Update</h1>
    </div>
    
    <div class="content">
        <p>Hello <strong>{{ $travelOrder->employee_name }}</strong>,</p>
        
        <p>Your travel order status has been updated. Here are the details:</p>
        
        <div class="details">
            <h3>Travel Order Information</h3>
            <p><strong>Order Number:</strong> {{ $travelOrder->local_travel_order_no }}</p>
            <p><strong>Employee:</strong> {{ $travelOrder->employee_name }}</p>
            <p><strong>Destination:</strong> {{ $travelOrder->farthest_destination }}</p>
            <p><strong>Purpose:</strong> {{ $travelOrder->purpose }}</p>
            <p><strong>Travel Date:</strong> {{ $travelOrder->date_of_travel_from->format('F d, Y') }} to {{ $travelOrder->date_of_travel_to->format('F d, Y') }}</p>
        </div>
        
        <div class="status-update @if($travelOrder->status == 'approved') status-approved @elseif($travelOrder->status == 'rejected') status-rejected @endif">
            <h3>Status Update</h3>
            @if($previousStatus)
                <p><strong>Previous Status:</strong> {{ Str::headline($previousStatus) }}</p>
            @endif
            <p><strong>Current Status:</strong> <span style="font-size: 18px; font-weight: bold;">{{ $statusMessage }}</span></p>
            
            @if($travelOrder->status == 'approved')
                <p style="color: #155724;">🎉 <strong>Congratulations!</strong> Your travel order has been approved.</p>
            @elseif($travelOrder->status == 'rejected')
                <p style="color: #721c24;">❌ Your travel order has been rejected.</p>
            @elseif($travelOrder->status == 'pending_approval')
                <p style="color: #0c5460;">⏳ Your travel order is currently under review.</p>
            @endif
        </div>
        
        @if($customMessage)
        <div style="background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 15px 0; border-radius: 5px;">
            <h4>Additional Message:</h4>
            <p>{{ $customMessage }}</p>
        </div>
        @endif
        
        @if($travelOrder->status == 'approved')
        <div style="background-color: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin: 15px 0; border-radius: 5px;">
            <h4>Next Steps:</h4>
            <ul>
                <li>You can now proceed with your travel arrangements</li>
                <li>Download and print your approved travel order for reference</li>
                <li>Keep all receipts for expense reimbursement</li>
                <li>Submit your travel report upon return</li>
            </ul>
        </div>
        @elseif($travelOrder->status == 'rejected')
        <div style="background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; margin: 15px 0; border-radius: 5px;">
            <h4>What to do next:</h4>
            <ul>
                <li>Review the rejection reasons if provided</li>
                <li>Make necessary corrections to your travel order</li>
                <li>Resubmit your travel order when ready</li>
                <li>Contact your supervisor if you need clarification</li>
            </ul>
        </div>
        @endif
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ config('app.url') }}/travel-orders/{{ $travelOrder->id }}" class="btn">View Travel Order</a>
        </div>
        
        <p>You can always check the status of your travel orders by logging into the Travel Order Management System:</p>
        <p><a href="{{ config('app.url') }}/travel-orders">{{ config('app.url') }}/travel-orders</a></p>
        
        <p>If you have any questions, please don't hesitate to contact us.</p>
        
        <p>Best regards,<br>
        <strong>{{ config('app.name') }}</strong></p>
    </div>
    
    <div class="footer">
        This is an automated message from {{ config('app.name') }}. Please do not reply to this email.
    </div>
</body>
</html>
