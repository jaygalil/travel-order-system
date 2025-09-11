#!/bin/bash
# Laravel Travel Order System - Server Setup Script
# Run this on your VPS after uploading files

echo "=============================================="
echo "Travel Order System - Server Setup Script"
echo "=============================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Configuration
PROJECT_PATH="/var/www/travelorder"
ARCHIVE_NAME="travelorder-deployment.tar.gz"

# Check if running as correct user
if [[ $EUID -eq 0 ]]; then
   echo -e "${RED}Don't run this script as root. Use your regular user account.${NC}"
   exit 1
fi

echo -e "\n${YELLOW}1. Extracting application files...${NC}"
cd $PROJECT_PATH

if [ ! -f "$ARCHIVE_NAME" ]; then
    echo -e "${RED}Error: $ARCHIVE_NAME not found in $PROJECT_PATH${NC}"
    echo -e "${YELLOW}Please upload the deployment package first.${NC}"
    exit 1
fi

# Extract files
tar -xzf $ARCHIVE_NAME
rm $ARCHIVE_NAME

echo -e "${GREEN}Files extracted successfully!${NC}"

echo -e "\n${YELLOW}2. Setting up environment...${NC}"
# Copy production environment file
cp .env.production .env

echo -e "\n${YELLOW}3. Setting file permissions...${NC}"
find $PROJECT_PATH -type f -exec chmod 644 {} \;
find $PROJECT_PATH -type d -exec chmod 755 {} \;
chmod -R 775 storage bootstrap/cache

echo -e "\n${YELLOW}4. Installing Composer dependencies...${NC}"
composer install --no-dev --optimize-autoloader

echo -e "\n${YELLOW}5. Running Laravel optimizations...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo -e "\n${YELLOW}6. Running database migrations...${NC}"
echo -e "${CYAN}This will use the database: travelorderdb${NC}"
read -p "Press Enter to continue with migrations or Ctrl+C to cancel..."

php artisan migrate --force

echo -e "\n${YELLOW}7. Creating storage link...${NC}"
php artisan storage:link

echo -e "\n${YELLOW}8. Setting final permissions...${NC}"
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

echo -e "\n${YELLOW}9. Configuring Apache Virtual Host...${NC}"
VHOST_FILE="/etc/apache2/sites-available/travelorder.conf"

if [ ! -f "$VHOST_FILE" ]; then
    echo -e "${CYAN}Creating Apache virtual host configuration...${NC}"
    sudo tee $VHOST_FILE > /dev/null <<EOF
<VirtualHost *:80>
    ServerName travelorder.dictr2.online
    ServerAlias www.travelorder.dictr2.online
    DocumentRoot /var/www/travelorder/public
    
    <Directory /var/www/travelorder>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog \${APACHE_LOG_DIR}/travelorder_error.log
    CustomLog \${APACHE_LOG_DIR}/travelorder_access.log combined
</VirtualHost>
EOF
    
    # Enable site and required modules
    sudo a2ensite travelorder.conf
    sudo a2enmod rewrite
    sudo a2dissite 000-default
    
    echo -e "${GREEN}Apache virtual host configured!${NC}"
else
    echo -e "${CYAN}Apache virtual host already exists.${NC}"
fi

echo -e "\n${YELLOW}10. Testing Apache configuration...${NC}"
sudo apache2ctl configtest

if [ $? -eq 0 ]; then
    echo -e "${GREEN}Apache configuration is valid!${NC}"
    sudo systemctl reload apache2
    echo -e "${GREEN}Apache reloaded successfully!${NC}"
else
    echo -e "${RED}Apache configuration has errors. Please check manually.${NC}"
fi

echo -e "\n${YELLOW}11. Setting up SSL Certificate...${NC}"
echo -e "${CYAN}Setting up Let's Encrypt SSL certificate...${NC}"
read -p "Do you want to set up SSL certificate now? (y/n): " setup_ssl

if [[ $setup_ssl =~ ^[Yy]$ ]]; then
    if ! command -v certbot &> /dev/null; then
        echo -e "${CYAN}Installing Certbot...${NC}"
        sudo apt update
        sudo apt install certbot python3-certbot-apache -y
    fi
    
    echo -e "${CYAN}Obtaining SSL certificate...${NC}"
    sudo certbot --apache -d travelorder.dictr2.online -d www.travelorder.dictr2.online
else
    echo -e "${YELLOW}SSL setup skipped. You can run this later:${NC}"
    echo -e "${CYAN}sudo certbot --apache -d travelorder.dictr2.online -d www.travelorder.dictr2.online${NC}"
fi

echo -e "\n${GREEN}=============================================="
echo -e "DEPLOYMENT COMPLETED SUCCESSFULLY!"
echo -e "==============================================${NC}"

echo -e "\n${YELLOW}Your application should now be accessible at:${NC}"
echo -e "${CYAN}http://travelorder.dictr2.online${NC}"
if [[ $setup_ssl =~ ^[Yy]$ ]]; then
    echo -e "${CYAN}https://travelorder.dictr2.online${NC}"
fi

echo -e "\n${YELLOW}Next Steps:${NC}"
echo -e "${CYAN}1. Test your application in a web browser${NC}"
echo -e "${CYAN}2. Update your email configuration in .env if needed${NC}"
echo -e "${CYAN}3. Test all functionality including login and email${NC}"

echo -e "\n${YELLOW}Troubleshooting:${NC}"
echo -e "${CYAN}- Check logs: sudo tail -f /var/log/apache2/travelorder_error.log${NC}"
echo -e "${CYAN}- Clear cache: php artisan cache:clear${NC}"
echo -e "${CYAN}- Check permissions: sudo chown -R www-data:www-data /var/www/travelorder${NC}"

echo -e "\n${GREEN}Deployment script completed!${NC}"
