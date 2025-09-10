@echo off
echo =====================================
echo Travel Order Management System Setup
echo =====================================
echo.

echo Step 1: Installing Node.js dependencies...
call npm install
if errorlevel 1 (
    echo ERROR: Failed to install Node.js dependencies
    echo Please ensure Node.js is installed
    pause
    exit /b 1
)

echo.
echo Step 2: Compiling assets...
call npm run dev
if errorlevel 1 (
    echo ERROR: Failed to compile assets
    pause
    exit /b 1
)

echo.
echo Step 3: Generating application key...
call php artisan key:generate
if errorlevel 1 (
    echo ERROR: Failed to generate application key
    pause
    exit /b 1
)

echo.
echo Step 4: Publishing vendor assets...
call php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"
call php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --tag="migrations"
call php artisan config:cache
call php artisan route:clear

echo.
echo Step 5: Running database migrations...
echo WARNING: This will create the database tables.
echo Make sure you have created the 'travel_order_system' database in MySQL first.
set /p continue="Continue with migrations? (y/n): "
if /i "%continue%"=="y" (
    call php artisan migrate
    if errorlevel 1 (
        echo ERROR: Migration failed
        echo Please ensure:
        echo 1. MySQL is running in XAMPP
        echo 2. Database 'travel_order_system' exists
        echo 3. Database credentials in .env are correct
        pause
        exit /b 1
    )
    
    echo.
    echo Step 6: Seeding database with initial data...
    call php artisan db:seed
    if errorlevel 1 (
        echo WARNING: Seeding failed, but you can continue manually
    )
) else (
    echo Skipping database setup. You can run migrations manually later with:
    echo php artisan migrate
    echo php artisan db:seed
)

echo.
echo =====================================
echo Installation Complete!
echo =====================================
echo.
echo Next steps:
echo 1. Start XAMPP (Apache and MySQL)
echo 2. Create database 'travel_order_system' in phpMyAdmin if not done
echo 3. Access your application at:
echo    http://localhost/travel-order-system/public
echo.
echo Default admin credentials (after seeding):
echo Email: admin@travelorder.local
echo Password: password
echo.
echo For more information, check README.md
echo.
pause
