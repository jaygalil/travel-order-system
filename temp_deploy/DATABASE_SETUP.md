# Database Setup and Seeding Guide

## Quick Setup

Run all the setup commands to get your system ready:

```bash
# 1. Run database migrations
php artisan migrate

# 2. Seed database with users, roles, and workflow templates
php artisan db:seed

# 3. Test email functionality (optional)
php artisan email:test created admin@example.com
```

## What Gets Created

### 🔐 Users and Roles

**Demo Users:**
- **admin@example.com** / password (Admin)
- **jane.smith@example.com** / password (Approver - HR Department)
- **john.doe@example.com** / password (Employee - IT Department)
- **maria.santos@example.com** / password (Approver - Finance)
- **robert.garcia@example.com** / password (Approver - Operations)
- **lisa.wong@example.com** / password (Employee - IT Department)

**Roles & Permissions:**
- **Admin**: Full system access
- **Approver**: Can approve/reject travel orders
- **Employee**: Can create and manage own travel orders
- **Workflow Manager**: Can manage workflow templates

### 📋 Workflow Templates

**1. Standard 5-Step Approval (Default)**
- Immediate Supervisor Review
- Department Head Endorsement  
- Budget Office Verification
- Division Chief Approval
- Final Authority Approval

**2. Simplified 3-Step Approval**
- Supervisor Approval
- Budget Verification
- Final Approval

**3. Emergency Travel Approval**
- Parallel Emergency Approval
- Budget Emergency Check
- Emergency Final Approval

**4. IT Department Workflow**
- Technical Review
- Budget Approval
- IT Director Approval

## Manual Seeding

Run seeders individually if needed:

```bash
# Users and roles only
php artisan db:seed --class=RolesAndUsersSeeder

# Workflow templates only  
php artisan db:seed --class=WorkflowTemplatesSeeder
```

## Testing Email System

Test different email types:

```bash
# Test travel order creation email
php artisan email:test created your@email.com

# Test approval request email
php artisan email:test approval your@email.com

# Test status update email
php artisan email:test status your@email.com
```

## Environment Configuration

Make sure your `.env` file has proper email settings:

```env
# For Gmail
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="Travel Order System"

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

## Troubleshooting

**Migration Issues:**
```bash
# Reset and migrate fresh
php artisan migrate:fresh --seed
```

**Permission Issues:**
```bash
# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

**Email Issues:**
- Check SMTP settings in `.env`
- Verify firewall/antivirus isn't blocking
- For Gmail, use App Passwords not regular password

## Next Steps

1. ✅ Login with admin account: `admin@example.com` / `password`
2. ✅ Create your first travel order
3. ✅ Test the approval workflow
4. ✅ Customize workflow templates as needed
5. ✅ Import your organization's users

## Production Deployment

For production deployment:

1. Set `APP_ENV=production` in `.env`
2. Use secure passwords (not 'password')
3. Configure proper email service
4. Set up database backups
5. Configure HTTPS/SSL certificates
