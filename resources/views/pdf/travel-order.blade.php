<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Travel Order {{ $travelOrder->local_travel_order_no }}</title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            font-size: 11px;
            margin: 0;
            padding: 20px;
            line-height: 1.2;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        
        .logo {
            float: left;
            width: 60px;
            height: 60px;
            margin-right: 15px;
        }
        
        .qr-code {
            float: right;
            width: 60px;
            height: 60px;
            margin-left: 15px;
            border: 1px solid #000;
        }
        
        .header-text {
            margin: 0 75px;
        }
        
        .header h1 {
            font-size: 14px;
            margin: 5px 0;
            font-weight: bold;
        }
        
        .header h2 {
            font-size: 12px;
            margin: 3px 0;
            font-weight: normal;
        }
        
        .order-info {
            text-align: right;
            margin-bottom: 20px;
            clear: both;
        }
        
        .order-info strong {
            font-size: 12px;
        }
        
        .form-section {
            margin-bottom: 15px;
        }
        
        .form-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }
        
        .form-cell {
            display: table-cell;
            vertical-align: top;
            padding-right: 20px;
        }
        
        .form-label {
            font-weight: bold;
            margin-bottom: 2px;
        }
        
        .form-value {
            border-bottom: 1px solid #000;
            min-height: 18px;
            padding: 2px 5px;
        }
        
        .full-width {
            width: 100%;
        }
        
        .half-width {
            width: 48%;
        }
        
        .approval-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 10px;
        }
        
        .approval-table th,
        .approval-table td {
            border: 1px solid #000;
            padding: 8px 5px;
            text-align: center;
            vertical-align: middle;
        }
        
        .approval-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        
        .signature-section {
            text-align: center;
            padding: 30px 0 10px 0;
        }
        
        .status-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 5px 10px;
            border: 2px solid;
            font-weight: bold;
            font-size: 12px;
        }
        
        .status-approved {
            color: #28a745;
            border-color: #28a745;
        }
        
        .status-pending {
            color: #ffc107;
            border-color: #ffc107;
        }
        
        .status-rejected {
            color: #dc3545;
            border-color: #dc3545;
        }
        
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
    </style>
</head>
<body>
    <!-- Status Badge -->
    @php
        switch($travelOrder->status) {
            case 'approved':
                $statusClass = 'status-approved';
                break;
            case 'pending_approval':
                $statusClass = 'status-pending';
                break;
            case 'rejected':
                $statusClass = 'status-rejected';
                break;
            default:
                $statusClass = '';
                break;
        }
    @endphp
    @if($travelOrder->status !== 'draft')
        <div class="status-badge {{ $statusClass }}">
            {{ strtoupper(str_replace('_', ' ', $travelOrder->status)) }}
        </div>
    @endif

    <!-- Header -->
    <div class="header clearfix">
        <!-- Placeholder for logo -->
        <div class="logo">
            <div style="width: 60px; height: 60px; border: 1px solid #000; text-align: center; line-height: 60px; font-size: 8px;">
                LOGO
            </div>
        </div>
        
        <!-- QR Code placeholder -->
        <div class="qr-code">
            <div style="width: 58px; height: 58px; text-align: center; line-height: 58px; font-size: 8px;">
                QR CODE
            </div>
        </div>
        
        <div class="header-text">
            <h1>REPUBLIC OF THE PHILIPPINES</h1>
            <h2>DEPARTMENT OF INFORMATION AND</h2>
            <h2>COMMUNICATIONS TECHNOLOGY</h2>
        </div>
    </div>
    
    <!-- Travel Order Number -->
    <div class="order-info">
        <strong>LOCAL TRAVEL ORDER No.: {{ $travelOrder->local_travel_order_no }}</strong><br>
        Series of {{ $travelOrder->created_at->year }}<br>
        Date of Travel: {{ $travelOrder->date_of_travel_from->format('F d, Y') }} to {{ $travelOrder->date_of_travel_to->format('F d, Y') }}<br>
        Prepared By: {{ $travelOrder->preparedBy->name }}
    </div>
    
    <!-- Authority to Travel -->
    <div class="form-section">
        <p><strong>Authority to Travel is hereby granted to:</strong></p>
    </div>
    
    <!-- Employee Information -->
    <div class="form-section">
        <div class="form-row">
            <div class="form-cell half-width">
                <div class="form-label">NAME</div>
                <div class="form-value">{{ $travelOrder->employee_name }}</div>
            </div>
            <div class="form-cell half-width">
                <div class="form-label">POSITION</div>
                <div class="form-value">{{ $travelOrder->position }}</div>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-cell full-width">
                <div class="form-label">DIVISION/AGENCY</div>
                <div class="form-value">{{ $travelOrder->division_agency }}</div>
            </div>
        </div>
    </div>
    
    <!-- Travel Details -->
    <div class="form-section">
        <div class="form-row">
            <div class="form-cell half-width">
                <div class="form-label">Source of Fund:</div>
                <div class="form-value">{{ $travelOrder->source_of_fund }}</div>
            </div>
            <div class="form-cell half-width">
                <div class="form-label">Official Vehicle:</div>
                <div class="form-value">{{ $travelOrder->official_vehicle ?: '' }}</div>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-cell full-width">
                <div class="form-label">Purpose:</div>
                <div class="form-value" style="min-height: 40px; white-space: pre-wrap;">{{ $travelOrder->purpose }}</div>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-cell full-width">
                <div class="form-label">Destination:</div>
                <div class="form-value" style="min-height: 40px; white-space: pre-wrap;">{{ $travelOrder->destination }}</div>
            </div>
        </div>
    </div>
    
    <!-- TEV Claims -->
    <div class="form-section">
        <p><strong>TEV claims:</strong></p>
        <div class="form-row">
            <div class="form-cell half-width">
                <div class="form-label">Name</div>
                <div class="form-value">{{ $travelOrder->employee_name }}</div>
            </div>
            <div class="form-cell half-width">
                <div class="form-label">Farthest Destination</div>
                <div class="form-value">{{ $travelOrder->farthest_destination }}</div>
            </div>
        </div>
        <div class="form-row">
            <div class="form-cell half-width">
                <div class="form-label">Approx Distance</div>
                <div class="form-value">{{ number_format($travelOrder->approx_distance, 2) }} Km</div>
            </div>
        </div>
    </div>
    
    <!-- Report Submission -->
    <div class="form-section">
        <p>A report of your travel must be submitted to the Agency Head/Supervising Official within 7 days from completion of travel.</p>
        <p>Liquidation of each cash advance should be made after the issuance of travel authority by the Regional Director.</p>
    </div>
    
    <!-- Travel Approval Log -->
    <div class="form-section">
        <p><strong>Travel Approval Log:</strong></p>
        <table class="approval-table">
            <thead>
                <tr>
                    <th style="width: 15%">Sequence</th>
                    <th style="width: 25%">Name</th>
                    <th style="width: 15%">Status</th>
                    <th style="width: 20%">Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($travelOrder->approvals as $approval)
                <tr>
                    <td>{{ $approval->approver_title }}</td>
                    <td>{{ $approval->approver_name }}</td>
                    <td style="text-transform: capitalize;">{{ $approval->status }}</td>
                    <td>
                        @if($approval->action_date)
                            {{ $approval->action_date->format('M d, Y') }}<br>
                            {{ $approval->action_date->format('g:i A') }}
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <!-- Final Status -->
    @if($travelOrder->status === 'approved' && $travelOrder->approved_at)
        <div class="signature-section">
            <p><strong>This Travel Order is FULLY APPROVED</strong></p>
            <p><strong>NOTE:</strong> This Travel Order is valid only if FULLY APPROVED, check QR code for validation.</p>
        </div>
    @elseif($travelOrder->status === 'rejected')
        <div class="signature-section">
            <p><strong>This Travel Order has been REJECTED</strong></p>
        </div>
    @else
        <div class="signature-section">
            <p><strong>NOTE:</strong> This Travel Order is valid only if FULLY APPROVED, check QR code for validation.</p>
        </div>
    @endif
    
    <!-- Footer -->
    <div style="position: fixed; bottom: 10px; width: 100%; text-align: center; font-size: 9px; color: #666;">
        Generated on {{ now()->format('F d, Y g:i A') }} | Travel Order Management System
    </div>
</body>
</html>
