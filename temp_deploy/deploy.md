# Laravel Travel Order System - VPS Deployment Guide

## Your VPS Details
- **Domain**: travelorder.dictr2.online
- **SSH User**: cruelty-ssh
- **SSH Password**: n5KVhuiwFttzACQmVoay
- **IP Address**: 82.25.110.218
- **Database**: travel_orders (using existing database travelorderdb)
- **DB User**: nginx123
- **DB Password**: 6qbXKVI62Lbcfu9UFPsj

## Pre-Deployment Checklist ✅
- [x] Built production assets (npm run production)
- [x] Optimized composer autoloader
- [x] Created production environment file (.env.production)

## Step-by-Step Deployment Instructions

### Step 1: Connect to Your VPS

From PowerShell on your Windows machine:
```powershell
ssh cruelty-ssh@82.25.110.218
```
Enter password when prompted: `n5KVhuiwFttzACQmVoay`

### Step 2: Prepare Server Environment

Update system and install required packages:
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y php8.1 php8.1-cli php8.1-fpm php8.1-mysql php8.1-xml php8.1-gd php8.1-curl php8.1-mbstring php8.1-zip php8.1-intl php8.1-bcmath apache2 mysql-server git unzip curl wget
```

Install Composer:
```bash
cd /tmp
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
```

Install Node.js (for future updates):
```bash
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs
```

### Step 3: Create Project Directory and Set Permissions

```bash
# Create directory
sudo mkdir -p /var/www/travelorder
sudo chown -R $USER:$USER /var/www/travelorder
cd /var/www/travelorder
```

### Step 4: Upload Your Files

From your Windows machine (open another PowerShell window), create a deployment package:

```powershell
# Navigate to your project directory
cd C:\xampp\htdocs\projects\laravel\travel-order-system

# Create a compressed archive (excluding unnecessary files)
# You can use 7zip or WinRAR to create a .tar.gz file
# Or use WSL if available:
tar --exclude='node_modules' --exclude='.git' --exclude='vendor' --exclude='storage/logs/*' -czf travelorder.tar.gz .
```

Upload using SCP:
```powershell
scp travelorder.tar.gz cruelty-ssh@82.25.110.218:/var/www/travelorder/
```

### Step 5: Extract and Setup Application on VPS

Back in your VPS SSH session:
```bash
cd /var/www/travelorder
tar -xzf travelorder.tar.gz
rm travelorder.tar.gz

# Copy production environment file
cp .env.production .env

# Set proper permissions
find /var/www/travelorder -type f -exec chmod 644 {} \;
find /var/www/travelorder -type d -exec chmod 755 {} \;
chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache
```

### Step 6: Install Dependencies and Optimize

```bash
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 7: Database Setup

The database `travelorderdb` and user `nginx123` already exist based on your configuration.

Run Laravel migrations:
```bash
php artisan migrate --force
```

If you have seeders:
```bash
php artisan db:seed --force
```

### Step 8: Configure Apache Virtual Host

Create Apache configuration:
```bash
sudo nano /etc/apache2/sites-available/travelorder.conf
```

Add this configuration:
```apache
<VirtualHost *:80>
    ServerName travelorder.dictr2.online
    ServerAlias www.travelorder.dictr2.online
    DocumentRoot /var/www/travelorder/public
    
    <Directory /var/www/travelorder>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/travelorder_error.log
    CustomLog ${APACHE_LOG_DIR}/travelorder_access.log combined
</VirtualHost>
```

Enable the site and Apache modules:
```bash
sudo a2ensite travelorder.conf
sudo a2enmod rewrite
sudo a2dissite 000-default
sudo systemctl reload apache2
```

### Step 9: SSL Certificate (Let's Encrypt)

Install Certbot:
```bash
sudo apt install certbot python3-certbot-apache -y
```

Obtain SSL certificate:
```bash
sudo certbot --apache -d travelorder.dictr2.online -d www.travelorder.dictr2.online
```

### Step 10: Final Configuration

Create storage link:
```bash
php artisan storage:link
```

Set final permissions:
```bash
sudo chown -R www-data:www-data /var/www/travelorder
sudo chmod -R 755 /var/www/travelorder
sudo chmod -R 775 /var/www/travelorder/storage
sudo chmod -R 775 /var/www/travelorder/bootstrap/cache
```

Test the configuration:
```bash
sudo apache2ctl configtest
sudo systemctl restart apache2
```

## Testing Your Deployment

1. Visit: `https://travelorder.dictr2.online`
2. Check that all assets load correctly
3. Test database connectivity by attempting to log in
4. Verify email functionality works

## Troubleshooting

### Common Issues:

1. **500 Internal Server Error**
   - Check Apache error logs: `sudo tail -f /var/log/apache2/travelorder_error.log`
   - Ensure proper permissions: `sudo chown -R www-data:www-data /var/www/travelorder`

2. **Database Connection Error**
   - Verify database credentials in `.env` file
   - Test MySQL connection: `mysql -u nginx123 -p travelorderdb`

3. **Assets Not Loading**
   - Run: `php artisan config:clear && php artisan cache:clear`
   - Check if storage link exists: `ls -la public/storage`

4. **Email Issues**
   - Update email configuration in `.env`
   - Test with: `php artisan tinker` then `Mail::raw('Test', function($msg) { $msg->to('test@example.com')->subject('Test'); });`

## Post-Deployment Tasks

1. Set up automated backups
2. Configure log rotation
3. Set up monitoring
4. Update DNS records if needed
5. Test all application features

## For Future Updates

When updating your application:
1. Build assets locally: `npm run production`
2. Upload files via SCP
3. Run on server:
   ```bash
   composer install --no-dev --optimize-autoloader
   php artisan migrate --force
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   sudo systemctl reload apache2
   ```

---

**Security Notes:**
- Keep your server updated: `sudo apt update && sudo apt upgrade`
- Regularly backup your database
- Monitor server logs for suspicious activity
- Consider setting up fail2ban for SSH protection
