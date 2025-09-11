# Travel Order Management System

A comprehensive Laravel-based travel order management system with email approval workflow and PDF generation capabilities, designed for the Department of Information and Communications Technology.

## 🚀 Features

- **Travel Order Management**: Create, edit, view, and submit travel orders
- **Email-based Approval Workflow**: Sequential approval process with email notifications
- **PDF Generation**: Generate official travel order documents matching government format
- **Role-based Access Control**: Different user roles and permissions
- **Dashboard & Reporting**: Administrative dashboard with travel order analytics
- **Responsive Design**: Bootstrap-based responsive interface

## 📋 System Requirements

- PHP 7.4+
- MySQL 5.7+
- Composer
- XAMPP (for local development)
- Node.js & NPM (for asset compilation)

## 🛠️ Installation & Setup

### 1. Database Setup

1. Start XAMPP and ensure MySQL is running
2. Open phpMyAdmin (http://localhost/phpmyadmin)
3. Create a new database named `travel_order_system`

### 2. Environment Configuration

The `.env` file is already configured for XAMPP. Key settings:

```env
APP_NAME="Travel Order Management System"
APP_URL=http://localhost/travel-order-system/public
DB_DATABASE=travel_order_system
```

### 3. Install Dependencies & Run Migrations

```bash
# Install Node.js dependencies and compile assets
npm install
npm run dev

# Run database migrations
php artisan migrate

# Seed initial data
php artisan db:seed
```

### 4. Generate Application Key

```bash
php artisan key:generate
```

## 🏗️ System Architecture

### Database Structure

#### Users Table
- Basic user information with employee details
- Role-based permissions via Spatie Laravel Permission

#### Travel Orders Table
- Complete travel order information
- Status tracking (draft, pending_approval, approved, rejected)
- Links to employee and preparer

#### Travel Order Approvals Table
- Sequential approval workflow
- Email token for email-based approvals
- Approval status and comments

### 📊 Approval Workflow

The system implements a 5-step approval process:

1. **Provincial Officer** - Bariuan, Ronald S. (forwarded)
2. **Human Resources** - Ammasi, Jayfer T. (forwarded)  
3. **Recommending Approval** - Gomez, Magdalena D. (endorsed)
4. **Verified By** - Villafuerte, Mina Flor T. (verified)
5. **Approved By** - Jimenez, Pinky T. (approved)

### 📧 Email Approval System

- Unique email tokens for secure approvals
- Direct approval links in emails
- Complete audit trail
- Email notifications for all stakeholders

## 🎯 Key Controllers

- **TravelOrderController**: CRUD operations and workflow management
- **ApprovalController**: Email-based approval processing
- **PDFController**: PDF generation matching official format
- **DashboardController**: Analytics and reporting

## 🔗 Routes Structure

```
/                           - Redirect to login
/dashboard                  - User dashboard
/travel-orders              - Travel order management
/approvals                  - Approval management
/approval/{token}           - Email approval (no auth required)
/admin/*                    - Admin routes (role-based)
```

## 📄 PDF Generation

Generates PDFs matching the exact government format including:
- Department header with logo and QR code
- All form fields populated from database
- Approval workflow table with signatures
- Professional government document formatting

## 🔐 Security Features

- Laravel authentication and CSRF protection
- Role and permission-based access control
- Email token verification for approvals
- SQL injection and XSS prevention

## 🚀 Deployment to Hostinger VPS

1. Upload files via FTP/SFTP
2. Update `.env` with production settings
3. Run production optimizations
4. Configure SSL and email settings

## ⚡ Quick Start

1. Ensure XAMPP is running with MySQL
2. Create database `travel_order_system`
3. Run `php artisan migrate --seed`
4. Run `npm install && npm run dev`
5. Access: `http://localhost/travel-order-system/public`

## 🎨 Built With

- **Laravel 8** - PHP Framework
- **Bootstrap 4** - CSS Framework
- **MySQL** - Database
- **DomPDF** - PDF Generation
- **Spatie Permissions** - Role Management

## 📞 Support

For technical support, refer to Laravel documentation or contact the development team.

## 📝 License

Proprietary software for the Department of Information and Communications Technology.
