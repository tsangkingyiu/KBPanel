# KBPanel Installation Guide

## System Requirements

### Minimum Requirements
- **OS**: Ubuntu 24.04 LTS (fresh installation recommended)
- **CPU**: 4 cores
- **RAM**: 4GB (8GB+ recommended for production)
- **Disk**: 50GB free space (SSD recommended)
- **Network**: Public IP address or domain name
- **Access**: Root/sudo privileges

### Recommended for Production
- **CPU**: 8+ cores
- **RAM**: 16GB+
- **Disk**: 500GB+ SSD
- **Backup**: Separate backup storage

## Pre-Installation Checklist

- [ ] Fresh Ubuntu 24.04 LTS server
- [ ] Root/sudo access
- [ ] Server has internet connectivity
- [ ] Firewall ports 22, 80, 443 accessible
- [ ] (Optional) Domain name with DNS access
- [ ] (Optional) SMTP credentials for email notifications

## Installation Methods

### Method 1: Quick Install (Recommended)

For most users, the automated installer is the easiest method:

```bash
# Download the installer
wget https://raw.githubusercontent.com/tsangkingyiu/KBPanel/v1.0.0/install.sh

# Make it executable
chmod +x install.sh

# Run the installer
sudo ./install.sh
```

The installer will:
1. Prompt you for configuration (MySQL password, admin credentials, domain)
2. Install all dependencies (PHP, Composer, Node.js, Docker, etc.)
3. Create a fresh Laravel 12 installation
4. Apply KBPanel overlay files
5. Configure Apache web server
6. Start Docker infrastructure
7. Set up firewall and security

**Installation time**: Approximately 10-20 minutes depending on your server speed.

### Method 2: Manual Installation

If you prefer manual control or are troubleshooting:

#### Step 1: System Update

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y software-properties-common apt-transport-https ca-certificates curl wget git unzip
```

#### Step 2: Install PHP 8.2

```bash
sudo add-apt-repository -y ppa:ondrej/php
sudo apt update
sudo apt install -y php8.2-cli php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring \
    php8.2-curl php8.2-zip php8.2-bcmath php8.2-gd php8.2-intl php8.2-redis php8.2-soap
```

#### Step 3: Install Composer

```bash
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
```

#### Step 4: Install Node.js

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo bash -
sudo apt install -y nodejs
```

#### Step 5: Install Docker

```bash
# Add Docker GPG key
sudo install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
sudo chmod a+r /etc/apt/keyrings/docker.asc

# Add Docker repository
echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] \
  https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | \
  sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# Install Docker
sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

# Start Docker
sudo systemctl enable docker
sudo systemctl start docker

# Add www-data to docker group
sudo usermod -aG docker www-data
```

#### Step 6: Install Apache

```bash
sudo apt install -y apache2 libapache2-mod-php8.2
sudo a2enmod rewrite ssl headers proxy proxy_http
sudo a2dissite 000-default
sudo systemctl enable apache2
```

#### Step 7: Install Laravel 12

```bash
sudo mkdir -p /var/www
cd /var/www
sudo composer create-project --prefer-dist laravel/laravel kbpanel "12.*"
```

#### Step 8: Apply KBPanel Overlay

```bash
# Clone KBPanel repository
git clone --branch v1.0.0 https://github.com/tsangkingyiu/KBPanel.git /tmp/kbpanel

# Copy overlay files
sudo cp -r /tmp/kbpanel/app/* /var/www/kbpanel/app/
sudo cp -r /tmp/kbpanel/config/* /var/www/kbpanel/config/
sudo cp -r /tmp/kbpanel/database/* /var/www/kbpanel/database/
sudo cp -r /tmp/kbpanel/resources/* /var/www/kbpanel/resources/
sudo cp -r /tmp/kbpanel/docker /var/www/kbpanel/

# Install dependencies
cd /var/www/kbpanel
sudo composer install --no-interaction
sudo npm install
sudo npm run build

# Generate app key
sudo php artisan key:generate

# Set permissions
sudo chown -R www-data:www-data /var/www/kbpanel
sudo chmod -R 775 /var/www/kbpanel/storage /var/www/kbpanel/bootstrap/cache

# Cleanup
sudo rm -rf /tmp/kbpanel
```

#### Step 9: Configure Environment

Edit `/var/www/kbpanel/.env`:

```bash
sudo nano /var/www/kbpanel/.env
```

Set these values:

```env
APP_NAME=KBPanel
APP_ENV=production
APP_DEBUG=false
APP_URL=http://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kbpanel
DB_USERNAME=root
DB_PASSWORD=your_mysql_password

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=127.0.0.1
REDIS_PORT=6379

KBPANEL_VERSION=1.0.0
KBPANEL_ADMIN_EMAIL=your@email.com
DB_ROOT_PASSWORD=your_mysql_password
```

#### Step 10: Start Docker Services

```bash
cd /var/www/kbpanel
sudo docker compose -f docker/services.yml up -d

# Wait for MySQL to start
sleep 15

# Create database
sudo docker exec kbpanel_db mysql -uroot -pyour_mysql_password -e "CREATE DATABASE IF NOT EXISTS kbpanel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

#### Step 11: Run Migrations

```bash
cd /var/www/kbpanel
sudo php artisan migrate --force
```

#### Step 12: Configure Apache

Create `/etc/apache2/sites-available/kbpanel.conf`:

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    ServerAdmin admin@your-domain.com
    DocumentRoot /var/www/kbpanel/public

    <Directory /var/www/kbpanel/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/kbpanel-error.log
    CustomLog ${APACHE_LOG_DIR}/kbpanel-access.log combined

    <FilesMatch \.php$>
        SetHandler "proxy:unix:/run/php/php8.2-fpm.sock|fcgi://localhost"
    </FilesMatch>
</VirtualHost>
```

Enable site:

```bash
sudo a2ensite kbpanel
sudo apache2ctl configtest
sudo systemctl restart apache2
```

#### Step 13: Configure Firewall

```bash
sudo ufw allow ssh
sudo ufw allow http
sudo ufw allow https
sudo ufw --force enable
```

## Post-Installation

### 1. Access KBPanel

Open your browser and navigate to:
- `http://your-server-ip` or
- `http://your-domain.com`

You should see the KBPanel login page.

### 2. Create Admin User

Since v1.0.0 doesn't include an admin seeder yet, create the admin user manually:

```bash
cd /var/www/kbpanel
sudo docker exec kbpanel_db mysql -uroot -p
```

In MySQL:

```sql
USE kbpanel;

-- Create admin user (adjust as needed)
INSERT INTO users (name, email, email_verified_at, password, created_at, updated_at)
VALUES ('Admin', 'admin@example.com', NOW(), '$2y$12$your_bcrypt_hash_here', NOW(), NOW());

-- Note: Generate bcrypt hash in PHP:
-- php -r "echo password_hash('your_password', PASSWORD_BCRYPT);"
```

Or use Laravel Tinker:

```bash
sudo php artisan tinker

>>> $user = new App\Models\User();
>>> $user->name = 'Admin';
>>> $user->email = 'admin@example.com';
>>> $user->password = Hash::make('your_secure_password');
>>> $user->save();
>>> exit
```

### 3. Configure SSL (Production)

For production environments, obtain a free SSL certificate:

```bash
sudo apt install -y certbot python3-certbot-apache
sudo certbot --apache -d your-domain.com
```

Certbot will automatically:
- Generate SSL certificate
- Configure Apache for HTTPS
- Set up auto-renewal

### 4. Configure DNS (If using domain)

Add these DNS records:

```
A     @              your-server-ip
A     www            your-server-ip
A     *.yourdomain   your-server-ip  # For subdomains (staging, etc.)
```

### 5. Test phpMyAdmin Access

phpMyAdmin is accessible only from localhost for security. To access:

**Option A: SSH Tunnel (Recommended)**

```bash
ssh -L 8080:localhost:8080 user@your-server-ip
```

Then open `http://localhost:8080` in your browser.

**Option B: KBPanel Proxy**

KBPanel will proxy phpMyAdmin through its authenticated interface (coming in future versions).

## Verification Checklist

After installation, verify:

- [ ] KBPanel login page loads
- [ ] Can log in with admin credentials
- [ ] Docker containers are running: `sudo docker ps`
- [ ] MySQL accessible: `sudo docker exec -it kbpanel_db mysql -uroot -p`
- [ ] Redis accessible: `sudo docker exec -it kbpanel_redis redis-cli ping`
- [ ] phpMyAdmin loads (via SSH tunnel)
- [ ] Apache serving site correctly
- [ ] SSL certificate installed (if production)
- [ ] Firewall configured: `sudo ufw status`

## Troubleshooting

### Issue: Installation Script Fails

**Check logs:**
```bash
sudo tail -f /var/log/kbpanel-install.log
```

### Issue: Docker Containers Won't Start

**Check Docker status:**
```bash
sudo systemctl status docker
sudo docker ps -a
sudo docker logs kbpanel_db
sudo docker logs kbpanel_redis
```

**Restart Docker:**
```bash
sudo systemctl restart docker
cd /var/www/kbpanel
sudo docker compose -f docker/services.yml restart
```

### Issue: Permission Errors

**Fix permissions:**
```bash
sudo chown -R www-data:www-data /var/www/kbpanel
sudo chmod -R 775 /var/www/kbpanel/storage /var/www/kbpanel/bootstrap/cache
```

### Issue: 500 Internal Server Error

**Check Laravel logs:**
```bash
sudo tail -f /var/www/kbpanel/storage/logs/laravel.log
```

**Check Apache logs:**
```bash
sudo tail -f /var/log/apache2/kbpanel-error.log
```

**Clear caches:**
```bash
cd /var/www/kbpanel
sudo php artisan cache:clear
sudo php artisan config:clear
sudo php artisan view:clear
```

### Issue: Database Connection Error

**Test database connection:**
```bash
sudo docker exec -it kbpanel_db mysql -uroot -p
```

**Check .env database settings:**
```bash
sudo cat /var/www/kbpanel/.env | grep DB_
```

### Issue: Composer or NPM Fails

**Clear caches and retry:**
```bash
cd /var/www/kbpanel
sudo composer clear-cache
sudo composer install --no-interaction
sudo npm cache clean --force
sudo npm install
```

## Upgrading

Upgrade instructions will be provided with future releases.

## Uninstalling

To completely remove KBPanel:

```bash
# Stop and remove Docker containers
cd /var/www/kbpanel
sudo docker compose -f docker/services.yml down -v

# Remove application directory
sudo rm -rf /var/www/kbpanel

# Remove Apache config
sudo a2dissite kbpanel
sudo rm /etc/apache2/sites-available/kbpanel.conf
sudo systemctl reload apache2

# Optional: Remove installed packages
sudo apt remove --purge php8.2-* apache2 docker-ce docker-ce-cli
sudo apt autoremove -y
```

## Support

For installation issues:
- Check [GitHub Issues](https://github.com/tsangkingyiu/KBPanel/issues)
- Review [Troubleshooting Section](#troubleshooting)
- Check installation log: `/var/log/kbpanel-install.log`

## Next Steps

After successful installation:
1. Read [Usage Guide](USAGE.md) (coming soon)
2. Configure your first project
3. Set up automated backups
4. Review security best practices
