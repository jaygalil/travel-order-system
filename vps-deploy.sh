#!/bin/bash

# Travel Order System - VPS Deployment Script
# Run this script on your VPS server after uploading the files

echo "=== Travel Order System - VPS Deployment ==="
echo "Starting deployment process..."

# Check if we're in the correct directory
if [ ! -f "artisan" ]; then
    echo "Error: artisan file not found. Make sure you're in the Laravel project directory."
    exit 1
fi

# Step 1: Set up environment file
echo "Step 1: Setting up environment configuration..."
if [ -f ".env.production" ]; then
    cp .env.production .env
    echo "✓ Production environment file copied to .env"
else
    echo "✗ .env.production file not found!"
    exit 1
fi

# Step 2: Install dependencies
echo "Step 2: Installing PHP dependencies..."
if command -v composer &> /dev/null; then
    composer install --no-dev --optimize-autoloader
    echo "✓ Composer dependencies installed"
else
    echo "✗ Composer not found. Please install Composer first."
    exit 1
fi

# Step 3: Generate application key (if needed)
echo "Step 3: Generating application key..."
php artisan key:generate --force
echo "✓ Application key generated"

# Step 4: Set file permissions
echo "Step 4: Setting file permissions..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chown -R www-data:www-data storage
chown -R www-data:www-data bootstrap/cache
chown -R www-data:www-data public
echo "✓ File permissions set"

# Step 5: Clear and cache configurations
echo "Step 5: Optimizing application for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "✓ Application optimized"

# Step 6: Run database migrations
echo "Step 6: Running database migrations..."
php artisan migrate --force
if [ $? -eq 0 ]; then
    echo "✓ Database migrations completed successfully"
else
    echo "✗ Database migrations failed. Please check your database configuration."
fi

# Step 7: Seed the database (if needed)
echo "Step 7: Seeding database..."
php artisan db:seed --force
if [ $? -eq 0 ]; then
    echo "✓ Database seeded successfully"
else
    echo "! Database seeding failed or no seeders available"
fi

# Step 8: Create storage symlink
echo "Step 8: Creating storage symlink..."
php artisan storage:link
echo "✓ Storage symlink created"

echo ""
echo "=== Deployment Summary ==="
echo "✓ Environment configured"
echo "✓ Dependencies installed" 
echo "✓ Application key generated"
echo "✓ File permissions set"
echo "✓ Application optimized"
echo "✓ Database migrations run"
echo "✓ Storage symlink created"
echo ""
echo "🎉 Deployment completed successfully!"
echo ""
echo "Next steps:"
echo "1. Configure your web server to point to the 'public' directory"
echo "2. Update your email configuration in .env if needed"
echo "3. Test your application at: https://travelorder.dictr2.online"
echo ""
echo "Troubleshooting:"
echo "- If you encounter permission issues, run: sudo chown -R www-data:www-data /path/to/your/app"
echo "- If database connection fails, verify your database credentials in .env"
echo "- Check Laravel logs at: storage/logs/laravel.log"
