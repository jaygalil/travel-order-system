#!/bin/bash

# Laravel File Permissions Fix Script for VPS
# Run this script if you encounter permission issues

echo "=== Laravel File Permissions Fix ==="

# Get the current directory
APP_DIR=$(pwd)

# Check if we're in a Laravel project
if [ ! -f "artisan" ]; then
    echo "Error: This doesn't appear to be a Laravel project directory."
    echo "Make sure you're running this script from your Laravel project root."
    exit 1
fi

echo "Setting Laravel file permissions for: $APP_DIR"

# Set directory permissions
echo "Setting directory permissions..."
find $APP_DIR -type d -exec chmod 755 {} \;

# Set file permissions  
echo "Setting file permissions..."
find $APP_DIR -type f -exec chmod 644 {} \;

# Set specific permissions for storage and cache
echo "Setting storage and cache permissions..."
chmod -R 755 $APP_DIR/storage
chmod -R 755 $APP_DIR/bootstrap/cache

# Set ownership to web server user
echo "Setting ownership to web server user..."
chown -R www-data:www-data $APP_DIR/storage
chown -R www-data:www-data $APP_DIR/bootstrap/cache
chown -R www-data:www-data $APP_DIR/public

# Make artisan executable
chmod +x $APP_DIR/artisan

# Set specific permissions for key directories
chmod -R 775 $APP_DIR/storage/app
chmod -R 775 $APP_DIR/storage/framework
chmod -R 775 $APP_DIR/storage/logs

echo "✓ File permissions fixed successfully!"

# Check if running as root and provide alternative commands
if [ "$EUID" -ne 0 ]; then
    echo ""
    echo "Note: If you encounter permission denied errors, try running:"
    echo "sudo $0"
    echo ""
    echo "Or manually run these commands:"
    echo "sudo chown -R www-data:www-data $APP_DIR/storage"
    echo "sudo chown -R www-data:www-data $APP_DIR/bootstrap/cache"
    echo "sudo chmod -R 755 $APP_DIR/storage"
    echo "sudo chmod -R 755 $APP_DIR/bootstrap/cache"
fi

echo ""
echo "=== Permission Summary ==="
echo "✓ Directory permissions: 755"
echo "✓ File permissions: 644"
echo "✓ Storage permissions: 755 (with www-data ownership)"
echo "✓ Cache permissions: 755 (with www-data ownership)"
echo "✓ Public permissions: www-data ownership"
echo "✓ Artisan executable: 755"
echo ""
echo "Your Laravel application should now have correct permissions!"
