# User Import and Approval Flow Setup Guide

## Overview

The Travel Order Management System now supports importing users from Excel/CSV files and automatically setting up approval flows based on real users in your system. This guide explains how to set up and manage users with their approval sequences.

## Features

### ✅ User Import System
- **Excel/CSV Import**: Import users from spreadsheet files
- **Bulk User Creation**: Create multiple users at once
- **Role Assignment**: Automatically assign roles (admin, approver, employee)
- **Approval Sequence**: Set up approval hierarchy with sequence numbers
- **Default Password**: All imported users get default password: `12345678!@#`

### ✅ Dynamic Approval Flow
- **Real User-Based**: Uses actual users from database instead of hardcoded names
- **Sequence-Based**: Approvers are ordered by their sequence number (1-10)
- **Email Integration**: Sends approval requests to real user emails
- **Fallback System**: Uses default approval flow if no approvers configured

### ✅ Admin User Management
- **User Dashboard**: View all users with roles and approval sequences
- **Search & Filter**: Find users by name, email, position, or role
- **Edit Users**: Modify user details, roles, and approval sequences
- **Visual Approval Flow**: See current approval hierarchy at a glance

## CSV File Format

### Required Columns
```csv
name,email,position,role,sequence,approver_title
```

### Sample Data
```csv
name,email,position,role,sequence,approver_title
John Doe,john.doe@example.com,Provincial Officer,approver,1,Provincial Officer
Jane Smith,jane.smith@example.com,Human Resources Manager,approver,2,Human Resources
Bob Johnson,bob.johnson@example.com,Department Head,approver,3,Recommending Approval
Alice Brown,alice.brown@example.com,Finance Manager,approver,4,Verified By
Charlie Wilson,charlie.wilson@example.com,Director,approver,5,Approved By
Mike Davis,mike.davis@employee.com,Staff,employee,,
Sarah Connor,sarah.connor@employee.com,Clerk,employee,,
Tom Anderson,tom.anderson@admin.com,IT Admin,admin,,
```

### Column Descriptions

| Column | Required | Description | Example Values |
|--------|----------|-------------|----------------|
| `name` | ✅ Yes | Full name of the user | "John Doe" |
| `email` | ✅ Yes | Valid email address | "john.doe@company.com" |
| `position` | ❌ Optional | Job position/title | "Provincial Officer" |
| `role` | ❌ Optional | User role in system | admin, approver, employee |
| `sequence` | ❌ Optional | Approval order (1-10) | 1, 2, 3, etc. |
| `approver_title` | ❌ Optional | Title shown in approvals | "Human Resources" |

### Default Values
- **Password**: `12345678!@#` (users should change on first login)
- **Role**: `employee` (if not specified)
- **Status**: `active`
- **Email Verified**: `yes`

## How to Import Users

### Method 1: Web Interface (Recommended)

1. **Access Admin Panel**
   - Login as admin user
   - Go to `Dashboard > Users Management`

2. **Import Users**
   - Click "Import Users" button
   - Download the template CSV file
   - Fill in your user data
   - Upload the CSV file
   - Review import results

### Method 2: Command Line

```bash
# Import sample users (for testing)
php artisan users:import --sample

# Import from specific file
php artisan users:import /path/to/your-users.csv

# Import from Excel file (if supported)
php artisan users:import /path/to/your-users.xlsx
```

## Approval Flow Setup

### 1. Define Approvers
Users with `approver` role and a `sequence` number will be automatically included in the approval flow.

### 2. Sequence Numbers
- **Range**: 1-10
- **Order**: Lower numbers = earlier in approval process
- **Example**: 1→Provincial Officer, 2→HR, 3→Department Head, 4→Finance, 5→Director

### 3. Email Integration
Each approver will receive:
- Email notification when travel order reaches their level
- Direct approve/reject buttons in email
- Secure token-based authentication (no login required)

## User Roles and Permissions

### 🔴 Admin / Super-Admin
- Full system access
- User management
- System configuration
- Reports and analytics

### 🟡 Approver
- Approve/reject travel orders
- View assigned approvals
- Email-based approval actions
- Dashboard access

### ⚫ Employee
- Create travel orders
- Submit for approval
- View own travel orders
- Download approved orders as PDF

## Managing Users

### View Users
- **Path**: `Admin > Users Management`
- **Features**: Search, filter by role, pagination
- **Info**: Shows name, email, position, role, approval sequence, status

### Edit Users
- Click edit button next to user
- Modify: name, email, position, role, approval sequence
- Activate/deactivate users

### Approval Flow Overview
The system displays the current approval flow showing:
- Approval levels (1-5)
- Approver names
- Their titles/positions
- Visual flow diagram

## Email System Integration

### Approval Request Emails
```
Subject: Travel Order Approval Request - TO-20250101-001
Content: 
- Travel order details
- Approve/Reject buttons
- Direct action links
- System login alternative
```

### Status Update Emails
```
Subject: Travel Order Status Update - TO-20250101-001
Content:
- Status change notification
- Next steps information
- PDF download links (if approved)
```

## Testing the System

### 1. Import Sample Users
```bash
php artisan users:import --sample
```

### 2. Create Test Travel Order
- Login as employee user (Mike Davis or Sarah Connor)
- Create a new travel order
- Submit for approval

### 3. Test Approval Flow
- Check approval emails sent to approvers
- Login as approver or use email links
- Process approvals in sequence

### 4. Verify Email Notifications
- Check all parties receive appropriate notifications
- Verify PDF generation for approved orders
- Test email-based approval functionality

## Troubleshooting

### Common Issues

**1. No Approvers Found**
- **Problem**: Travel orders use default approval flow
- **Solution**: Ensure users have `approver` role AND `sequence` number

**2. Emails Not Sending**
- **Problem**: Approval emails not delivered
- **Solution**: Configure SMTP settings in `.env` file

**3. Import Failures**
- **Problem**: CSV import fails
- **Solution**: Check file format, required columns, data validation

**4. Duplicate Users**
- **Problem**: Import tries to create existing users
- **Solution**: System automatically skips duplicate emails

### Verification Commands

```bash
# Check imported users
php artisan tinker
>>> App\Models\User::with('roles')->get(['name', 'email', 'approver_sequence'])

# Check approvers
>>> App\Models\User::role('approver')->whereNotNull('approver_sequence')->orderBy('approver_sequence')->get(['name', 'approver_sequence', 'approver_title'])

# Test email configuration
php artisan email:test created --to=your-email@test.com
```

## Production Deployment

### 1. Prepare User Data
- Export users from your current system
- Format according to CSV template
- Verify email addresses are valid

### 2. Configure Email
- Set up SMTP service (Gmail, SendGrid, etc.)
- Test email delivery
- Configure queue system for better performance

### 3. Import Users
- Start with small batch for testing
- Import all users
- Verify approval flow is correctly set up

### 4. Train Users
- Inform users of default password
- Explain approval process
- Provide system access instructions

## Security Considerations

- **Default Password**: Users should change password on first login
- **Email Security**: Approval tokens expire after 24 hours
- **Role-Based Access**: Users can only access features for their role
- **Audit Trail**: All approvals are logged with timestamps

## Next Steps

1. **Prepare your user data** in CSV format
2. **Import users** using the web interface or command line
3. **Configure email settings** for notifications
4. **Test the approval flow** with sample travel orders
5. **Train your users** on the new system

The system is now ready for production use with real user data and dynamic approval flows! 🎉
