# Deploy Travel Order System to Hostinger VPS

## Your VPS Details
- **Domain**: travelorder.dictr2.online
- **SSH Username**: cruelty-ssh
- **SSH Password**: n5KVhuiwFttzACQmVoay
- **IP Address**: 82.25.110.218

## Step-by-Step Deployment

### Step 1: Connect to VPS
Open PowerShell or Command Prompt and run:
```
ssh cruelty-ssh@82.25.110.218
```
When prompted, enter password: `n5KVhuiwFttzACQmVoay`

### Step 2: Update System & Install Software
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y php8.1 php8.1-cli php8.1-fpm php8.1-mysql php8.1-xml php8.1-gd php8.1-curl php8.1-mbstring php8.1-zip php8.1-intl php8.1-bcmath apache2 mysql-server git unzip curl
```

### Step 3: Install Composer & Node.js
```bash
cd /tmp
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs
```

### Step 4: Create Project Directory
```bash
sudo mkdir -p /var/www/travelorder
sudo chown -R cruelty-ssh:cruelty-ssh /var/www/travelorder
cd /var/www/travelorder
```

Now we'll upload your files using one of these methods...
