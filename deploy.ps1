# Laravel Travel Order System - Deployment Script
# Run this from PowerShell in your project root directory

Write-Host "===============================================" -ForegroundColor Green
Write-Host "Travel Order System - VPS Deployment Script" -ForegroundColor Green
Write-Host "===============================================" -ForegroundColor Green

# Configuration
$VPS_USER = "cruelty-ssh"
$VPS_HOST = "82.25.110.218"
$REMOTE_PATH = "/var/www/travelorder"
$ARCHIVE_NAME = "travelorder-deployment.tar.gz"

Write-Host "`n1. Creating deployment package..." -ForegroundColor Yellow

# Check if running from project root
if (!(Test-Path "composer.json")) {
    Write-Host "Error: Please run this script from your Laravel project root directory" -ForegroundColor Red
    exit 1
}

# Create temporary directory for deployment files
$TEMP_DIR = "temp_deploy"
if (Test-Path $TEMP_DIR) {
    Remove-Item $TEMP_DIR -Recurse -Force
}
New-Item -ItemType Directory -Path $TEMP_DIR | Out-Null

Write-Host "Copying files to temporary directory..." -ForegroundColor Cyan

# Copy all files except excluded directories
$EXCLUDE_DIRS = @("node_modules", ".git", "vendor", "storage\logs", "temp_deploy")
Get-ChildItem -Path . | Where-Object { 
    $_.Name -notin $EXCLUDE_DIRS 
} | Copy-Item -Destination $TEMP_DIR -Recurse -Force

# Clean up storage/logs but keep .gitignore
if (Test-Path "$TEMP_DIR\storage\logs") {
    Get-ChildItem "$TEMP_DIR\storage\logs" | Where-Object { $_.Name -ne ".gitignore" } | Remove-Item -Force
}

Write-Host "Files copied successfully!" -ForegroundColor Green

# Check if WSL is available for tar
$WSL_AVAILABLE = $false
try {
    $wslResult = wsl --status 2>$null
    $WSL_AVAILABLE = $true
    Write-Host "WSL detected, using tar for compression..." -ForegroundColor Cyan
} catch {
    Write-Host "WSL not available, using 7-Zip..." -ForegroundColor Cyan
}

if ($WSL_AVAILABLE) {
    # Use WSL tar
    wsl tar -czf $ARCHIVE_NAME -C $TEMP_DIR .
} else {
    # Check for 7-Zip
    $7ZIP_PATH = @(
        "${env:ProgramFiles}\7-Zip\7z.exe",
        "${env:ProgramFiles(x86)}\7-Zip\7z.exe",
        "$env:PROGRAMW6432\7-Zip\7z.exe"
    ) | Where-Object { Test-Path $_ } | Select-Object -First 1

    if ($7ZIP_PATH) {
        Write-Host "Using 7-Zip for compression..." -ForegroundColor Cyan
        & "$7ZIP_PATH" a -tgzip "$ARCHIVE_NAME" "$TEMP_DIR\*"
    } else {
        Write-Host "Neither WSL nor 7-Zip found. Please install one of them or create the archive manually." -ForegroundColor Red
        Write-Host "Manual steps:" -ForegroundColor Yellow
        Write-Host "1. Compress the contents of '$TEMP_DIR' folder to '$ARCHIVE_NAME'" -ForegroundColor Yellow
        Write-Host "2. Upload using: scp $ARCHIVE_NAME ${VPS_USER}@${VPS_HOST}:$REMOTE_PATH/" -ForegroundColor Yellow
        exit 1
    }
}

Write-Host "`n2. Uploading to VPS..." -ForegroundColor Yellow
Write-Host "This will prompt for your VPS password: n5KVhuiwFttzACQmVoay" -ForegroundColor Cyan

# Upload using SCP
try {
    $scpCommand = "scp `"$ARCHIVE_NAME`" ${VPS_USER}@${VPS_HOST}:$REMOTE_PATH/"
    Write-Host "Running: $scpCommand" -ForegroundColor Gray
    Invoke-Expression $scpCommand
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "Upload successful!" -ForegroundColor Green
    } else {
        Write-Host "Upload failed. Please check your connection and credentials." -ForegroundColor Red
        exit 1
    }
} catch {
    Write-Host "Error during upload: $_" -ForegroundColor Red
    Write-Host "Please upload manually using: scp $ARCHIVE_NAME ${VPS_USER}@${VPS_HOST}:$REMOTE_PATH/" -ForegroundColor Yellow
    exit 1
}

Write-Host "`n3. Cleaning up..." -ForegroundColor Yellow
Remove-Item $TEMP_DIR -Recurse -Force
Remove-Item $ARCHIVE_NAME -Force

Write-Host "`n===============================================" -ForegroundColor Green
Write-Host "DEPLOYMENT PACKAGE UPLOADED SUCCESSFULLY!" -ForegroundColor Green
Write-Host "===============================================" -ForegroundColor Green

Write-Host "`nNext steps:" -ForegroundColor Yellow
Write-Host "1. SSH into your VPS: ssh ${VPS_USER}@${VPS_HOST}" -ForegroundColor Cyan
Write-Host "2. Navigate to project directory: cd $REMOTE_PATH" -ForegroundColor Cyan
Write-Host "3. Follow the deployment guide in deploy.md" -ForegroundColor Cyan

Write-Host "`nQuick server setup commands:" -ForegroundColor Yellow
Write-Host "cd $REMOTE_PATH" -ForegroundColor Cyan
Write-Host "tar -xzf travelorder-deployment.tar.gz" -ForegroundColor Cyan
Write-Host "cp .env.production .env" -ForegroundColor Cyan
Write-Host "composer install --no-dev --optimize-autoloader" -ForegroundColor Cyan
Write-Host "php artisan migrate --force" -ForegroundColor Cyan
Write-Host "php artisan config:cache" -ForegroundColor Cyan

Write-Host "`nPress any key to continue..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
