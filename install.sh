#!/bin/bash
# KBPanel Installer v1.0.0
# Optimized for Ubuntu 24.04 LTS
# Repository: https://github.com/tsangkingyiu/KBPanel

set -euo pipefail

# Configuration
REPO_URL="https://github.com/tsangkingyiu/KBPanel.git"
VERSION="v1.0.0"
INSTALL_DIR="/var/www/kbpanel"
LOG_FILE="/var/log/kbpanel-install.log"
LARAVEL_VERSION="12.*"

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Logging function
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1" | tee -a "$LOG_FILE"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1" | tee -a "$LOG_FILE"
    exit 1
}

warn() {
    echo -e "${YELLOW}[WARNING]${NC} $1" | tee -a "$LOG_FILE"
}

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    error "Please run as root (use sudo)"
fi

log "=== KBPanel v1.0.0 Installation Started ==="

# Phase 1: Pre-Installation Checks
log "Phase 1: Pre-Installation Checks"

# Check OS
if [ -f /etc/os-release ]; then
    . /etc/os-release
    if [ "$ID" != "ubuntu" ]; then
        warn "This installer is optimized for Ubuntu 24.04. You are running: $ID $VERSION_ID"
        read -p "Continue anyway? (y/N): " -n 1 -r
        echo
        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            exit 1
        fi
    fi
fi

# Check RAM
TOTAL_RAM=$(free -m | awk '/^Mem:/{print $2}')
if [ "$TOTAL_RAM" -lt 4096 ]; then
    warn "Minimum 8GB RAM recommended. Detected: ${TOTAL_RAM}MB"
fi

# Check Disk Space
FREE_SPACE=$(df -BG / | awk 'NR==2 {print $4}' | sed 's/G//')
if [ "$FREE_SPACE" -lt 50 ]; then
    warn "Minimum 100GB disk space recommended. Available: ${FREE_SPACE}GB"
fi

# Interactive Configuration
log "Collecting installation parameters..."

read -p "Enter MySQL root password: " -s DB_ROOT_PASSWORD
echo
read -p "Confirm MySQL root password: " -s DB_ROOT_PASSWORD_CONFIRM
echo

if [ "$DB_ROOT_PASSWORD" != "$DB_ROOT_PASSWORD_CONFIRM" ]; then
    error "Passwords do not match"
fi

read -p "Enter KBPanel admin email: " ADMIN_EMAIL
read -p "Enter KBPanel admin password: " -s ADMIN_PASSWORD
echo
read -p "Enter server domain (or press Enter for IP): " SERVER_DOMAIN

if [ -z "$SERVER_DOMAIN" ]; then
    SERVER_DOMAIN=$(curl -s ifconfig.me)
    log "Using server IP: $SERVER_DOMAIN"
fi

# Phase 2: System Update & Dependencies
log "Phase 2: Installing system dependencies"

export DEBIAN_FRONTEND=noninteractive

apt-get update -y >> "$LOG_FILE" 2>&1
log "System updated"

# Install basic tools
apt-get install -y software-properties-common curl wget git unzip zip ufw supervisor >> "$LOG_FILE" 2>&1

# Phase 3: Install PHP 8.2
log "Phase 3: Installing PHP 8.2"

add-apt-repository ppa:ondrej/php -y >> "$LOG_FILE" 2>&1
apt-get update -y >> "$LOG_FILE" 2>&1

apt-get install -y \
    php8.2-cli \
    php8.2-fpm \
    php8.2-mysql \
    php8.2-xml \
    php8.2-mbstring \
    php8.2-curl \
    php8.2-zip \
    php8.2-bcmath \
    php8.2-gd \
    php8.2-intl \
    php8.2-redis \
    >> "$LOG_FILE" 2>&1

# Configure PHP
sed -i 's/upload_max_filesize = .*/upload_max_filesize = 128M/' /etc/php/8.2/fpm/php.ini
sed -i 's/post_max_size = .*/post_max_size = 128M/' /etc/php/8.2/fpm/php.ini
sed -i 's/memory_limit = .*/memory_limit = 512M/' /etc/php/8.2/fpm/php.ini

systemctl enable php8.2-fpm >> "$LOG_FILE" 2>&1
systemctl start php8.2-fpm >> "$LOG_FILE" 2>&1

log "PHP 8.2 installed and configured"

# Phase 4: Install Composer
log "Phase 4: Installing Composer"

curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer >> "$LOG_FILE" 2>&1
log "Composer installed: $(composer --version)"

# Phase 5: Install Node.js
log "Phase 5: Installing Node.js"

curl -fsSL https://deb.nodesource.com/setup_20.x | bash - >> "$LOG_FILE" 2>&1
apt-get install -y nodejs >> "$LOG_FILE" 2>&1

log "Node.js installed: $(node --version)"
log "NPM installed: $(npm --version)"

# Phase 6: Install Apache
log "Phase 6: Installing Apache"

apt-get install -y apache2 >> "$LOG_FILE" 2>&1

a2enmod rewrite ssl headers proxy proxy_http >> "$LOG_FILE" 2>&1
a2dissite 000-default >> "$LOG_FILE" 2>&1

systemctl enable apache2 >> "$LOG_FILE" 2>&1

log "Apache installed and configured"

# Phase 7: Install MySQL
log "Phase 7: Installing MySQL"

apt-get install -y mysql-server >> "$LOG_FILE" 2>&1

# Secure MySQL
mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '${DB_ROOT_PASSWORD}';" >> "$LOG_FILE" 2>&1
mysql -e "DELETE FROM mysql.user WHERE User='';" >> "$LOG_FILE" 2>&1
mysql -e "DROP DATABASE IF EXISTS test;" >> "$LOG_FILE" 2>&1
mysql -e "FLUSH PRIVILEGES;" >> "$LOG_FILE" 2>&1

systemctl enable mysql >> "$LOG_FILE" 2>&1

log "MySQL installed and secured"

# Phase 8: Install Docker
log "Phase 8: Installing Docker"

# Remove old versions
apt-get remove -y docker docker-engine docker.io containerd runc >> "$LOG_FILE" 2>&1 || true

# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sh get-docker.sh >> "$LOG_FILE" 2>&1
rm get-docker.sh

# Add www-data to docker group
usermod -aG docker www-data

systemctl enable docker >> "$LOG_FILE" 2>&1
systemctl start docker >> "$LOG_FILE" 2>&1

log "Docker installed: $(docker --version)"

# Phase 9: Install Redis
log "Phase 9: Installing Redis"

apt-get install -y redis-server >> "$LOG_FILE" 2>&1

sed -i 's/^bind 127.0.0.1/bind 127.0.0.1/' /etc/redis/redis.conf

systemctl enable redis-server >> "$LOG_FILE" 2>&1
systemctl start redis-server >> "$LOG_FILE" 2>&1

log "Redis installed"

# Phase 10: Install Certbot
log "Phase 10: Installing Certbot"

apt-get install -y certbot python3-certbot-apache >> "$LOG_FILE" 2>&1

log "Certbot installed"

# Phase 11: Install Laravel Base
log "Phase 11: Installing Laravel $LARAVEL_VERSION"

mkdir -p "$(dirname $INSTALL_DIR)"
cd "$(dirname $INSTALL_DIR)"

log "Creating Laravel project (this may take a few minutes)..."
su -s /bin/bash -c "composer create-project laravel/laravel $(basename $INSTALL_DIR) '$LARAVEL_VERSION' --prefer-dist --no-interaction" www-data >> "$LOG_FILE" 2>&1

log "Laravel installed"

# Phase 12: Clone KBPanel Overlay
log "Phase 12: Applying KBPanel overlay"

TMP_DIR="/tmp/kbpanel-overlay-$$"
git clone --branch "$VERSION" --depth 1 "$REPO_URL" "$TMP_DIR" >> "$LOG_FILE" 2>&1

# Copy overlay files
cp -r "$TMP_DIR/app"/* "$INSTALL_DIR/app/" 2>/dev/null || true
cp -r "$TMP_DIR/config"/* "$INSTALL_DIR/config/" 2>/dev/null || true
cp -r "$TMP_DIR/database"/* "$INSTALL_DIR/database/" 2>/dev/null || true
cp -r "$TMP_DIR/docker" "$INSTALL_DIR/" 2>/dev/null || true
cp -r "$TMP_DIR/resources"/* "$INSTALL_DIR/resources/" 2>/dev/null || true
cp "$TMP_DIR/composer.json" "$INSTALL_DIR/composer.json"
cp "$TMP_DIR/.env.example" "$INSTALL_DIR/.env.example"

rm -rf "$TMP_DIR"

log "KBPanel overlay applied"

# Phase 13: Install Dependencies
log "Phase 13: Installing KBPanel dependencies"

cd "$INSTALL_DIR"

su -s /bin/bash -c "composer install --no-interaction --optimize-autoloader" www-data >> "$LOG_FILE" 2>&1

if [ -f package.json ]; then
    su -s /bin/bash -c "npm install" www-data >> "$LOG_FILE" 2>&1
    su -s /bin/bash -c "npm run build" www-data >> "$LOG_FILE" 2>&1
fi

log "Dependencies installed"

# Phase 14: Configure Environment
log "Phase 14: Configuring environment"

cp .env.example .env

# Update .env
sed -i "s|APP_NAME=.*|APP_NAME=KBPanel|" .env
sed -i "s|APP_ENV=.*|APP_ENV=production|" .env
sed -i "s|APP_DEBUG=.*|APP_DEBUG=false|" .env
sed -i "s|APP_URL=.*|APP_URL=http://${SERVER_DOMAIN}|" .env
sed -i "s|DB_DATABASE=.*|DB_DATABASE=kbpanel|" .env
sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=${DB_ROOT_PASSWORD}|" .env

# Generate app key
su -s /bin/bash -c "php artisan key:generate --force" www-data >> "$LOG_FILE" 2>&1

log "Environment configured"

# Phase 15: Setup Database
log "Phase 15: Setting up database"

mysql -u root -p"${DB_ROOT_PASSWORD}" -e "CREATE DATABASE IF NOT EXISTS kbpanel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" >> "$LOG_FILE" 2>&1

su -s /bin/bash -c "php artisan migrate --force" www-data >> "$LOG_FILE" 2>&1

log "Database migrated"

# Phase 16: Create Storage Links
log "Phase 16: Creating storage links"

su -s /bin/bash -c "php artisan storage:link" www-data >> "$LOG_FILE" 2>&1

# Create required directories
mkdir -p "$INSTALL_DIR/storage/projects" "$INSTALL_DIR/storage/backups" "$INSTALL_DIR/storage/git-repos" "$INSTALL_DIR/storage/ssl"
chown -R www-data:www-data "$INSTALL_DIR/storage"
chmod -R 775 "$INSTALL_DIR/storage" "$INSTALL_DIR/bootstrap/cache"

log "Storage configured"

# Phase 17: Start Docker Services
log "Phase 17: Starting Docker services"

cd "$INSTALL_DIR/docker"

# Create .env for Docker Compose
cat > .env << EOF
DB_ROOT_PASSWORD=${DB_ROOT_PASSWORD}
EOF

docker compose -f services.yml up -d >> "$LOG_FILE" 2>&1

log "Docker services started"

# Phase 18: Configure Apache
log "Phase 18: Configuring Apache virtual host"

cat > /etc/apache2/sites-available/kbpanel.conf << EOF
<VirtualHost *:80>
    ServerName ${SERVER_DOMAIN}
    ServerAdmin webmaster@${SERVER_DOMAIN}
    DocumentRoot ${INSTALL_DIR}/public

    <Directory ${INSTALL_DIR}/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/kbpanel-error.log
    CustomLog \${APACHE_LOG_DIR}/kbpanel-access.log combined
</VirtualHost>
EOF

a2ensite kbpanel.conf >> "$LOG_FILE" 2>&1
apache2ctl configtest >> "$LOG_FILE" 2>&1
systemctl reload apache2 >> "$LOG_FILE" 2>&1

log "Apache configured"

# Phase 19: Configure Supervisor
log "Phase 19: Configuring Supervisor for queues"

cat > /etc/supervisor/conf.d/kbpanel-worker.conf << EOF
[program:kbpanel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php ${INSTALL_DIR}/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=${INSTALL_DIR}/storage/logs/worker.log
stopwaitsecs=3600
EOF

supervisorctl reread >> "$LOG_FILE" 2>&1
supervisorctl update >> "$LOG_FILE" 2>&1
supervisorctl start kbpanel-worker:* >> "$LOG_FILE" 2>&1

log "Supervisor configured"

# Phase 20: Configure Cron
log "Phase 20: Setting up cron jobs"

(crontab -u www-data -l 2>/dev/null; echo "* * * * * cd ${INSTALL_DIR} && php artisan schedule:run >> /dev/null 2>&1") | crontab -u www-data -

log "Cron jobs configured"

# Phase 21: Configure Firewall
log "Phase 21: Configuring firewall"

ufw --force enable >> "$LOG_FILE" 2>&1
ufw allow 22/tcp >> "$LOG_FILE" 2>&1
ufw allow 80/tcp >> "$LOG_FILE" 2>&1
ufw allow 443/tcp >> "$LOG_FILE" 2>&1

log "Firewall configured"

# Phase 22: Final Permissions
log "Phase 22: Setting final permissions"

chown -R www-data:www-data "$INSTALL_DIR"

log "Permissions set"

# Phase 23: Create Admin User
log "Phase 23: Creating admin user"

cd "$INSTALL_DIR"

# This would normally use an artisan command, but for v1.0.0 we'll do it directly
mysql -u root -p"${DB_ROOT_PASSWORD}" kbpanel << EOF >> "$LOG_FILE" 2>&1
INSERT INTO users (name, email, password, created_at, updated_at) 
VALUES ('Admin', '${ADMIN_EMAIL}', '$(php -r "echo password_hash('${ADMIN_PASSWORD}', PASSWORD_BCRYPT);")', NOW(), NOW());
EOF

log "Admin user created"

# Installation Complete
log "=== Installation Complete ==="

echo ""
echo -e "${GREEN}╔══════════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║          KBPanel v1.0.0 Installation Complete!               ║${NC}"
echo -e "${GREEN}╚══════════════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "Access KBPanel at: ${GREEN}http://${SERVER_DOMAIN}${NC}"
echo ""
echo "Admin Credentials:"
echo -e "  Email:    ${GREEN}${ADMIN_EMAIL}${NC}"
echo -e "  Password: ${GREEN}[as provided]${NC}"
echo ""
echo "phpMyAdmin (localhost only): http://127.0.0.1:8080"
echo ""
echo "Next Steps:"
echo "  1. Configure DNS to point to this server"
echo "  2. Run 'sudo certbot --apache' for SSL certificate"
echo "  3. Review .env file at ${INSTALL_DIR}/.env"
echo ""
echo "Installation log: ${LOG_FILE}"
echo ""
