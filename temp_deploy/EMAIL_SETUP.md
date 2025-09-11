# Email Configuration Guide

## Email System Setup

The Travel Order Management System includes comprehensive email notifications for:
- New travel order creation
- Status updates
- Approval requests
- Workflow notifications

## Configuration Options

### Option 1: Mailtrap (Recommended for Development)

1. Sign up for free at [https://mailtrap.io](https://mailtrap.io)
2. Create an inbox in your Mailtrap dashboard
3. Copy the SMTP credentials
4. Update your `.env` file:

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@travelorder.local"
MAIL_FROM_NAME="Travel Order Management System"
```

### Option 2: Gmail SMTP (Production)

1. Enable 2-Factor Authentication on your Gmail account
2. Generate an App Password for your application
3. Update your `.env` file:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your-email@gmail.com"
MAIL_FROM_NAME="Travel Order Management System"
```

### Option 3: Other SMTP Services

The system supports any SMTP service. Update the `.env` file with your provider's settings:
- **SendGrid**: `MAIL_HOST=smtp.sendgrid.net`
- **Mailgun**: `MAIL_HOST=smtp.mailgun.org`
- **Amazon SES**: `MAIL_HOST=email-smtp.us-east-1.amazonaws.com`

## Testing Email Functionality

### Clear Config Cache
```bash
php artisan config:clear
php artisan config:cache
```

### Test Commands
```bash
# Test travel order creation email
php artisan email:test created --to=test@example.com

# Test status update email
php artisan email:test status --to=test@example.com

# Test approval request email
php artisan email:test approval --to=test@example.com
```

### Manual Testing
1. Create a new travel order through the web interface
2. Submit it for approval
3. Check your inbox/Mailtrap for notifications

## Email Templates

The system includes responsive HTML email templates:

- **Travel Order Created** (`resources/views/emails/travel-order/created.blade.php`)
- **Status Updates** (`resources/views/emails/travel-order/status-update.blade.php`)
- **Approval Requests** (`resources/views/emails/travel-order/approval-request.blade.php`)

## Email Features

### Automatic Notifications
- **Creation**: Sent when a travel order is created
- **Submission**: Sent when submitted for approval
- **Status Changes**: Sent for approvals, rejections, forwards
- **Approval Requests**: Sent to each approver in sequence

### Email-Based Approvals
- Approvers can approve/reject directly from email
- Secure token-based authentication
- No login required for email approvals
- Links expire after 24 hours for security

### Responsive Design
- Mobile-friendly email templates
- Professional styling
- Clear call-to-action buttons
- Comprehensive travel order information

## Troubleshooting

### Common Issues

1. **Emails not sending**
   - Check SMTP credentials in `.env`
   - Verify internet connection
   - Check Laravel logs: `storage/logs/laravel.log`

2. **Gmail "Less secure apps" error**
   - Use App Password instead of account password
   - Enable 2FA first, then generate App Password

3. **Connection timeout**
   - Try different MAIL_PORT (587, 465, 25)
   - Check firewall settings
   - Verify MAIL_ENCRYPTION setting

4. **Authentication failed**
   - Double-check username/password
   - Some providers require specific username format

### Debug Mode
Enable mail debugging in `.env`:
```env
LOG_CHANNEL=single
LOG_LEVEL=debug
```

Check logs in `storage/logs/laravel.log` for detailed error messages.

## Production Deployment

1. Use a reliable SMTP service (Gmail, SendGrid, etc.)
2. Set up proper domain authentication (SPF, DKIM)
3. Configure queue system for better performance:
   ```env
   QUEUE_CONNECTION=database
   ```
4. Run queue worker:
   ```bash
   php artisan queue:work
   ```

## Security Considerations

- Email tokens expire after 24 hours
- Tokens are single-use for approvals
- All email communications are logged
- Sensitive data is not included in email URLs
- HTTPS required for production email links

## Email Content Customization

To customize email templates:
1. Edit files in `resources/views/emails/travel-order/`
2. Maintain responsive design
3. Test across different email clients
4. Keep content concise and actionable

The system is now ready for email testing and production use!
