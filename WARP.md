# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Development Commands

### Environment Setup
```powershell
# First-time setup
npm install && npm run dev
php artisan key:generate
php artisan migrate --seed
```

### Build & Asset Management
```powershell
# Development build (watch files)
npm run dev
npm run watch

# Production build
npm run production

# Hot reload for development
npm run hot
```

### Database Operations
```powershell
# Run migrations
php artisan migrate

# Seed database with test data
php artisan db:seed

# Fresh migration (drops all tables)
php artisan migrate:fresh --seed

# Check migration status
php artisan migrate:status

# Rollback migrations
php artisan migrate:rollback
```

### Testing & Development
```powershell
# Run PHPUnit tests
php artisan test

# Start local development server
php artisan serve

# Clear all caches (common debugging step)
php artisan cache:clear && php artisan config:clear && php artisan view:clear && php artisan route:clear
```

### Application-Specific Commands
```powershell
# Test email functionality
php artisan email:test

# Import users from Excel/CSV
php artisan users:import

# Create test workflow templates
php artisan workflows:create-test

# Test travel order with participants
php artisan travel:test-participants

# Reset user passwords (testing)
php artisan users:reset-passwords

# Check system status
php artisan system:status
```

### Permission Management
```powershell
# Reset permission cache (after role changes)
php artisan permission:cache-reset

# Create new permissions/roles
php artisan permission:create-permission
php artisan permission:create-role

# Show current permissions table
php artisan permission:show
```

## Architecture Overview

### Core Domain Models

**TravelOrder**: Central entity representing a travel request with status workflow (draft → pending_approval → approved/rejected → completed). Contains travel details, participants, and links to approval workflow.

**TravelOrderApproval**: Individual approval steps in sequential workflow. Supports email-based approvals with secure tokens. Each approval has a specific sequence and can be forwarded, endorsed, verified, or approved/rejected.

**WorkflowTemplate**: Configurable approval workflows with different visibility levels (public, private, department). Templates define the approval steps and can have sequential or parallel layouts.

**TravelOrderParticipant**: Supports multiple travelers per order with primary/secondary distinction. Participants can be linked to system users or exist as external contacts.

**User**: Extended Laravel User model with department affiliations, position data, and role-based permissions via Spatie Laravel Permission package.

### Key Architectural Patterns

**Sequential Approval Workflow**: Travel orders follow a 5-step approval process:
1. Provincial Officer (forwarded)
2. Human Resources (forwarded)  
3. Recommending Approval (endorsed)
4. Verified By (verified)
5. Approved By (approved)

**Email-Based Approvals**: Secure email tokens allow approvals without system login. Tokens are single-use and time-sensitive.

**Multi-Participant Support**: Each travel order can have multiple participants with role distinctions and individual requirements.

**PDF Generation**: Uses DomPDF to generate official government-format documents with proper headers, QR codes, and approval signatures.

### Controller Architecture

**TravelOrderController**: Full CRUD operations with workflow integration. Handles participant management and approval workflow creation via `ApprovalWorkflowBuilder` service.

**ApprovalController**: Manages both authenticated and email-based approval processing. Handles workflow progression and notification emails.

**WorkflowTemplateController**: Administrative interface for creating and managing approval workflow templates.

**AttachmentController**: File upload management with security scanning and public/private visibility controls.

### Database Design

**Workflow System**: 
- `workflow_templates` define reusable approval patterns
- `workflow_steps` define individual approval stages with sequence ordering
- `travel_order_approvals` track actual approval instances for each travel order

**Participant System**: 
- `travel_order_participants` support multiple travelers per order
- Primary participant data is duplicated in `travel_orders` for backwards compatibility
- Flexible user linking (can reference system users or external contacts)

**Attachment System**:
- `travel_order_attachments` with security scanning capabilities
- Public/private visibility controls for approver access
- File type validation and safe storage

## Development Context

### Technology Stack
- **Backend**: Laravel 8.x with PHP 7.4+
- **Frontend**: Bootstrap 5 + vanilla JavaScript
- **Database**: MySQL 5.7+
- **PDF**: DomPDF for document generation
- **Permissions**: Spatie Laravel Permission
- **Email**: Laravel Mail with queue support

### Local Environment
This application is designed to run on XAMPP with specific configuration:
- Database: `travel_order_system` 
- URL: `http://localhost/travel-order-system/public`
- PHP version: 7.4+ recommended
- Node.js required for asset compilation

### File Upload Security
The attachment system includes security scanning with `is_safe` flags. Always validate uploaded files and use the safe attachment relationships when displaying files to users.

### Email Configuration
Email approvals are core functionality. Ensure proper SMTP configuration in production and test email functionality regularly with `php artisan email:test`.

### Role-Based Access
The system uses Spatie Laravel Permission with roles like 'admin'. Admin users have access to all travel orders and user management features. Regular users see only their own travel orders.

### Workflow Flexibility
Workflow templates support different layouts (sequential, parallel) and visibility settings. When creating new approval workflows, consider the completion rules and required approval counts for parallel steps.
