# 🚀 Quick Deployment Guide

Your Laravel Travel Order System is ready to deploy! Follow these simple steps:

## ✅ Pre-deployment Complete
- [x] Production assets built
- [x] Composer optimized
- [x] Production environment file ready
- [x] Database credentials configured

## 🎯 Quick Deployment Steps

### Step 1: Upload Files (5 minutes)
From your Windows PowerShell (as Administrator):
```powershell
cd C:\xampp\htdocs\projects\laravel\travel-order-system
.\deploy.ps1
```
This script will:
- Package your files
- Upload to your VPS automatically
- Show you next steps

### Step 2: Setup Server (10 minutes)
SSH into your VPS:
```bash
ssh cruelty-ssh@82.25.110.218
# Password: n5KVhuiwFttzACQmVoay
```

First, install required software:
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y php8.1 php8.1-cli php8.1-fpm php8.1-mysql php8.1-xml php8.1-gd php8.1-curl php8.1-mbstring php8.1-zip php8.1-intl php8.1-bcmath apache2 mysql-server git unzip curl wget

# Install Composer
cd /tmp
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
```

Then run the setup script:
```bash
sudo mkdir -p /var/www/travelorder
sudo chown -R $USER:$USER /var/www/travelorder
cd /var/www/travelorder
chmod +x server-setup.sh
./server-setup.sh
```

### Step 3: Test Your Website (2 minutes)
Visit: **https://travelorder.dictr2.online**

## 📋 Your Configuration
- **Domain**: travelorder.dictr2.online
- **Database**: travelorderdb (already exists)
- **DB User**: nginx123
- **SSL**: Will be configured automatically

## 🔧 If Something Goes Wrong
1. Check Apache logs:
   ```bash
   sudo tail -f /var/log/apache2/travelorder_error.log
   ```

2. Fix permissions:
   ```bash
   sudo chown -R www-data:www-data /var/www/travelorder
   sudo chmod -R 775 /var/www/travelorder/storage
   ```

3. Clear Laravel cache:
   ```bash
   cd /var/www/travelorder
   php artisan cache:clear
   php artisan config:clear
   ```

## 📞 Need Help?
- Detailed guide: `deploy.md`
- All commands are in the files I created
- Your VPS is already configured with the database

**Total deployment time: ~15-20 minutes**

Good luck with your deployment! 🎉
