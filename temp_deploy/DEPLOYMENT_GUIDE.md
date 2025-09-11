# VPS Deployment Guide - Hostinger

## Prerequisites
- Hostinger VPS access (SSH)
- Domain name (optional but recommended)
- Database credentials from Hostinger panel

## Step 1: Connect to VPS
```bash
ssh root@your-vps-ip
# or
ssh username@your-vps-ip
```

## Step 2: Install Required Software
```bash
# Update system
apt update && apt upgrade -y

# Install PHP 7.4/8.0, Apache, MySQL, Git
apt install -y php php-cli php-fpm php-mysql php-xml php-gd php-curl php-mbstring php-zip php-intl php-bcmath apache2 mysql-server git composer nodejs npm unzip

# Enable required PHP extensions
phpenmod gd xml curl mbstring zip intl bcmath mysql

# Install ImageMagick for QR codes (optional)
apt install -y imagemagick php-imagick

# Restart Apache
systemctl restart apache2
systemctl enable apache2
```

## Step 3: Clone Repository
```bash
cd /var/www/
git clone https://github.com/jaygalil/travel-order-system.git
cd travel-order-system
```

## Step 4: Install Dependencies
```bash
composer install --optimize-autoloader --no-dev
npm install && npm run production
```

## Step 5: Configure Environment
```bash
cp .env.example .env
nano .env
```

Update .env with your VPS settings:
```env
APP_NAME="Travel Order System"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=travel_orders
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=your-email@yourdomain.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
```

## Step 6: Generate App Key & Setup Laravel
```bash
php artisan key:generate
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Step 7: Database Setup
```bash
# Create database
mysql -u root -p
CREATE DATABASE travel_orders;
CREATE USER 'travel_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON travel_orders.* TO 'travel_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Run migrations
php artisan migrate --force
php artisan db:seed --force
```

## Step 8: Set Permissions
```bash
chown -R www-data:www-data /var/www/travel-order-system
chmod -R 755 /var/www/travel-order-system
chmod -R 775 /var/www/travel-order-system/storage
chmod -R 775 /var/www/travel-order-system/bootstrap/cache
```

## Step 9: Configure Apache Virtual Host
```bash
nano /etc/apache2/sites-available/travel-orders.conf
```

Add this configuration:
```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/travel-order-system/public
    
    <Directory /var/www/travel-order-system/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/travel-orders-error.log
    CustomLog ${APACHE_LOG_DIR}/travel-orders-access.log combined
</VirtualHost>
```

Enable site:
```bash
a2ensite travel-orders.conf
a2enmod rewrite
systemctl restart apache2
```

## Step 10: Setup SSL (Optional but Recommended)
```bash
apt install -y certbot python3-certbot-apache
certbot --apache -d your-domain.com
```

## Step 11: Setup Cron Jobs
```bash
crontab -e
# Add this line:
* * * * * cd /var/www/travel-order-system && php artisan schedule:run >> /dev/null 2>&1
```

## Step 12: Test Installation
- Visit your domain/IP address
- Test login functionality
- Test PDF generation
- Test email sending

## Troubleshooting Commands
```bash
# Check Apache error logs
tail -f /var/log/apache2/error.log

# Check Laravel logs
tail -f /var/www/travel-order-system/storage/logs/laravel.log

# Check PHP configuration
php -m | grep -E "(gd|imagick|dom|xml)"

# Test email configuration
php artisan tinker
# In tinker: Mail::raw('Test email', function($msg) { $msg->to('test@example.com')->subject('Test'); });
```

## Security Recommendations
1. Change default SSH port
2. Setup firewall (UFW)
3. Create non-root user for SSH
4. Regular backups
5. Keep system updated
6. Monitor logs regularly
