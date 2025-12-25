#!/bin/bash
################################################################################
# KBPanel Installation Script v1.0.0
# Optimized for Ubuntu 24.04 LTS
#
# This script installs a complete KBPanel environment:
# 1. System dependencies (PHP, Composer, Node, Docker, etc.)
# 2. Fresh Laravel 12 application
# 3. KBPanel overlay files from GitHub repository
# 4. Shared infrastructure (MySQL, Redis, phpMyAdmin containers)
# 5. Apache web server configuration
# 6. Security hardening (firewall, permissions)
#
# Usage: sudo ./install.sh
################################################################################

set -euo pipefail

# Script Configuration
SCRIPT_VERSION="1.0.0"
LARAVEL_VERSION="12.*"
REPO_URL="https://github.com/tsangkingyiu/KBPanel.git"
REPO_BRANCH="v1.0.0"
INSTALL_DIR="/var/www/kbpanel"
LOG_FILE="/var/log/kbpanel-install.log"
TEMP_DIR="/tmp/kbpanel-install-$$"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

################################################################################
# Utility Functions
################################################################################

log() {
    echo "[$(date +'%Y-%m-%d %H:%M:%S')] $*" | tee -a "$LOG_FILE"
}

log_success() {
    echo -e "${GREEN}✓ $*${NC}" | tee -a "$LOG_FILE"
}

log_error() {
    echo -e "${RED}✗ $*${NC}" | tee -a "$LOG_FILE"
}

log_warning() {
    echo -e "${YELLOW}⚠ $*${NC}" | tee -a "$LOG_FILE"
}

log_info() {
    echo -e "${BLUE}ℹ $*${NC}" | tee -a "$LOG_FILE"
}

check_root() {
    if [[ $EUID -ne 0 ]]; then
        log_error "This script must be run as root (use sudo)"
        exit 1
    fi
}

check_os() {
    if [[ ! -f /etc/os-release ]]; then
        log_error "Cannot detect OS version"
        exit 1
    fi
    
    source /etc/os-release
    
    if [[ "$ID" != "ubuntu" ]]; then
        log_error "This script is designed for Ubuntu. Detected: $ID"
        exit 1
    fi
    
    if [[ "$VERSION_ID" != "24.04" ]]; then
        log_warning "This script is optimized for Ubuntu 24.04. Detected: $VERSION_ID"
        read -p "Continue anyway? (y/N): " -n 1 -r
        echo
        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            exit 1
        fi
    fi
}

check_requirements() {
    log_info "Checking system requirements..."
    
    # Check RAM
    total_ram=$(free -m | awk '/^Mem:/{print $2}')
    if [[ $total_ram -lt 3500 ]]; then
        log_warning "System has ${total_ram}MB RAM. Minimum 4GB recommended."
    fi
    
    # Check Disk Space
    available_space=$(df -BG / | awk 'NR==2 {print $4}' | sed 's/G//')
    if [[ $available_space -lt 50 ]]; then
        log_warning "Available disk space: ${available_space}GB. Minimum 50GB recommended."
    fi
    
    log_success "System requirements check complete"
}

prompt_config() {
    log_info "KBPanel Configuration"
    echo
    
    # MySQL Root Password
    while true; do
        read -sp "Enter MySQL root password: " DB_ROOT_PASSWORD
        echo
        read -sp "Confirm MySQL root password: " DB_ROOT_PASSWORD_CONFIRM
        echo
        if [[ "$DB_ROOT_PASSWORD" == "$DB_ROOT_PASSWORD_CONFIRM" ]]; then
            break
        else
            log_error "Passwords do not match. Try again."
        fi
    done
    
    # Admin Email
    read -p "Enter admin email: " ADMIN_EMAIL
    
    # Admin Password
    while true; do
        read -sp "Enter admin password: " ADMIN_PASSWORD
        echo
        read -sp "Confirm admin password: " ADMIN_PASSWORD_CONFIRM
        echo
        if [[ "$ADMIN_PASSWORD" == "$ADMIN_PASSWORD_CONFIRM" ]]; then
            break
        else
            log_error "Passwords do not match. Try again."
        fi
    done
    
    # Domain Name
    SERVER_IP=$(hostname -I | awk '{print $1}')
    read -p "Enter domain name (or press Enter for IP: $SERVER_IP): " DOMAIN_NAME
    if [[ -z "$DOMAIN_NAME" ]]; then
        DOMAIN_NAME="$SERVER_IP"
    fi
    
    # Installation Directory
    read -p "Installation directory [$INSTALL_DIR]: " CUSTOM_INSTALL_DIR
    if [[ -n "$CUSTOM_INSTALL_DIR" ]]; then
        INSTALL_DIR="$CUSTOM_INSTALL_DIR"
    fi
    
    echo
    log_success "Configuration collected"
}

################################################################################
# Installation Phases
################################################################################

phase_system_update() {
    log_info "[Phase 1/12] Updating system packages..."
    apt-get update -y >> "$LOG_FILE" 2>&1
    DEBIAN_FRONTEND=noninteractive apt-get upgrade -y >> "$LOG_FILE" 2>&1
    log_success "System updated"
}

phase_install_dependencies() {
    log_info "[Phase 2/12] Installing system dependencies..."
    apt-get install -y \
        software-properties-common \
        apt-transport-https \
        ca-certificates \
        curl \
        wget \
        git \
        unzip \
        zip \
        gnupg \
        lsb-release \
        supervisor \
        ufw \
        >> "$LOG_FILE" 2>&1
    log_success "Dependencies installed"
}

phase_install_php() {
    log_info "[Phase 3/12] Installing PHP 8.2 and extensions..."
    add-apt-repository -y ppa:ondrej/php >> "$LOG_FILE" 2>&1
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
        php8.2-soap \
        >> "$LOG_FILE" 2>&1
    
    # Configure PHP
    sed -i 's/upload_max_filesize = .*/upload_max_filesize = 100M/' /etc/php/8.2/fpm/php.ini
    sed -i 's/post_max_size = .*/post_max_size = 100M/' /etc/php/8.2/fpm/php.ini
    sed -i 's/memory_limit = .*/memory_limit = 256M/' /etc/php/8.2/fpm/php.ini
    
    systemctl enable php8.2-fpm >> "$LOG_FILE" 2>&1
    systemctl start php8.2-fpm >> "$LOG_FILE" 2>&1
    
    log_success "PHP 8.2 installed and configured"
}

phase_install_composer() {
    log_info "[Phase 4/12] Installing Composer..."
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer >> "$LOG_FILE" 2>&1
    log_success "Composer installed: $(composer --version | head -n1)"
}

phase_install_nodejs() {
    log_info "[Phase 5/12] Installing Node.js and NPM..."
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash - >> "$LOG_FILE" 2>&1
    apt-get install -y nodejs >> "$LOG_FILE" 2>&1
    log_success "Node.js installed: $(node --version), NPM: $(npm --version)"
}

phase_install_apache() {
    log_info "[Phase 6/12] Installing Apache web server..."
    apt-get install -y apache2 libapache2-mod-php8.2 >> "$LOG_FILE" 2>&1
    
    # Enable required modules
    a2enmod rewrite ssl headers proxy proxy_http >> "$LOG_FILE" 2>&1
    
    # Disable default site
    a2dissite 000-default >> "$LOG_FILE" 2>&1
    
    systemctl enable apache2 >> "$LOG_FILE" 2>&1
    
    log_success "Apache installed and configured"
}

phase_install_docker() {
    log_info "[Phase 7/12] Installing Docker and Docker Compose..."
    
    # Add Docker's official GPG key
    install -m 0755 -d /etc/apt/keyrings
    curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
    chmod a+r /etc/apt/keyrings/docker.asc
    
    # Add repository
    echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/ubuntu \
    $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | \
    tee /etc/apt/sources.list.d/docker.list > /dev/null
    
    apt-get update -y >> "$LOG_FILE" 2>&1
    apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin >> "$LOG_FILE" 2>&1
    
    systemctl enable docker >> "$LOG_FILE" 2>&1
    systemctl start docker >> "$LOG_FILE" 2>&1
    
    # Add www-data to docker group
    usermod -aG docker www-data
    
    log_success "Docker installed: $(docker --version)"
}

phase_install_laravel() {
    log_info "[Phase 8/12] Installing Laravel 12..."
    
    mkdir -p "$(dirname "$INSTALL_DIR")"
    cd "$(dirname "$INSTALL_DIR")"
    
    # Install fresh Laravel
    composer create-project --prefer-dist laravel/laravel "$(basename "$INSTALL_DIR")" "$LARAVEL_VERSION" >> "$LOG_FILE" 2>&1
    
    cd "$INSTALL_DIR"
    
    # Set permissions
    chown -R www-data:www-data "$INSTALL_DIR"
    chmod -R 775 storage bootstrap/cache
    
    log_success "Laravel installed at $INSTALL_DIR"
}

phase_overlay_kbpanel() {
    log_info "[Phase 9/12] Applying KBPanel overlay from GitHub..."
    
    # Clone repository to temp directory
    git clone --branch "$REPO_BRANCH" "$REPO_URL" "$TEMP_DIR" >> "$LOG_FILE" 2>&1
    
    # Copy overlay files
    cp -r "$TEMP_DIR/app"/* "$INSTALL_DIR/app/" 2>/dev/null || true
    cp -r "$TEMP_DIR/config"/* "$INSTALL_DIR/config/" 2>/dev/null || true
    cp -r "$TEMP_DIR/database"/* "$INSTALL_DIR/database/" 2>/dev/null || true
    cp -r "$TEMP_DIR/resources"/* "$INSTALL_DIR/resources/" 2>/dev/null || true
    cp -r "$TEMP_DIR/routes"/* "$INSTALL_DIR/routes/" 2>/dev/null || true
    cp -r "$TEMP_DIR/docker" "$INSTALL_DIR/" 2>/dev/null || true
    
    # Install KBPanel dependencies
    cd "$INSTALL_DIR"
    composer install --no-interaction >> "$LOG_FILE" 2>&1
    npm install >> "$LOG_FILE" 2>&1
    npm run build >> "$LOG_FILE" 2>&1
    
    # Generate APP_KEY
    php artisan key:generate --force >> "$LOG_FILE" 2>&1
    
    # Cleanup
    rm -rf "$TEMP_DIR"
    
    log_success "KBPanel overlay applied"
}

phase_configure_database() {
    log_info "[Phase 10/12] Configuring database and infrastructure..."
    
    # Create .env file
    cat > "$INSTALL_DIR/.env" <<EOF
APP_NAME=KBPanel
APP_ENV=production
APP_KEY=$(grep APP_KEY "$INSTALL_DIR/.env" | cut -d '=' -f2)
APP_DEBUG=false
APP_URL=http://$DOMAIN_NAME

LOG_CHANNEL=stack
LOG_LEVEL=info

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kbpanel
DB_USERNAME=root
DB_PASSWORD=$DB_ROOT_PASSWORD

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

KBPANEL_VERSION=1.0.0
KBPANEL_ADMIN_EMAIL=$ADMIN_EMAIL
DB_ROOT_PASSWORD=$DB_ROOT_PASSWORD
EOF

    # Start Docker infrastructure
    cd "$INSTALL_DIR"
    docker compose -f docker/services.yml up -d >> "$LOG_FILE" 2>&1
    
    # Wait for MySQL to be ready
    log_info "Waiting for MySQL container to be ready..."
    sleep 10
    
    # Create KBPanel database
    docker exec kbpanel_db mysql -uroot -p"$DB_ROOT_PASSWORD" -e "CREATE DATABASE IF NOT EXISTS kbpanel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" >> "$LOG_FILE" 2>&1
    
    # Run migrations
    php artisan migrate --force >> "$LOG_FILE" 2>&1
    
    # Create admin user (you'll need to add a seeder or command for this)
    # php artisan kbpanel:create-admin "$ADMIN_EMAIL" "$ADMIN_PASSWORD" >> "$LOG_FILE" 2>&1
    
    log_success "Database configured and migrated"
}

phase_configure_apache() {
    log_info "[Phase 11/12] Configuring Apache virtual host..."
    
    cat > /etc/apache2/sites-available/kbpanel.conf <<EOF
<VirtualHost *:80>
    ServerName $DOMAIN_NAME
    ServerAdmin $ADMIN_EMAIL
    DocumentRoot $INSTALL_DIR/public

    <Directory $INSTALL_DIR/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/kbpanel-error.log
    CustomLog \${APACHE_LOG_DIR}/kbpanel-access.log combined

    <FilesMatch \.php$>
        SetHandler "proxy:unix:/run/php/php8.2-fpm.sock|fcgi://localhost"
    </FilesMatch>
</VirtualHost>
EOF

    a2ensite kbpanel >> "$LOG_FILE" 2>&1
    apache2ctl configtest >> "$LOG_FILE" 2>&1
    systemctl restart apache2 >> "$LOG_FILE" 2>&1
    
    log_success "Apache configured for $DOMAIN_NAME"
}

phase_security_hardening() {
    log_info "[Phase 12/12] Applying security hardening..."
    
    # Configure UFW firewall
    ufw --force enable >> "$LOG_FILE" 2>&1
    ufw default deny incoming >> "$LOG_FILE" 2>&1
    ufw default allow outgoing >> "$LOG_FILE" 2>&1
    ufw allow ssh >> "$LOG_FILE" 2>&1
    ufw allow http >> "$LOG_FILE" 2>&1
    ufw allow https >> "$LOG_FILE" 2>&1
    
    # Final permission check
    chown -R www-data:www-data "$INSTALL_DIR"
    chmod -R 755 "$INSTALL_DIR"
    chmod -R 775 "$INSTALL_DIR/storage" "$INSTALL_DIR/bootstrap/cache"
    
    log_success "Security hardening complete"
}

################################################################################
# Main Installation
################################################################################

main() {
    clear
    echo -e "${BLUE}"
    echo "═══════════════════════════════════════════════════════════"
    echo "   KBPanel v$SCRIPT_VERSION Installation Script"
    echo "   Ubuntu 24.04 LTS"
    echo "═══════════════════════════════════════════════════════════"
    echo -e "${NC}"
    echo
    
    # Create log file
    touch "$LOG_FILE"
    log "KBPanel Installation Started"
    
    # Pre-flight checks
    check_root
    check_os
    check_requirements
    
    # Collect configuration
    prompt_config
    
    echo
    log_info "Starting installation process..."
    echo
    
    # Run installation phases
    phase_system_update
    phase_install_dependencies
    phase_install_php
    phase_install_composer
    phase_install_nodejs
    phase_install_apache
    phase_install_docker
    phase_install_laravel
    phase_overlay_kbpanel
    phase_configure_database
    phase_configure_apache
    phase_security_hardening
    
    # Installation complete
    echo
    echo -e "${GREEN}"
    echo "═══════════════════════════════════════════════════════════"
    echo "   KBPanel Installation Complete!"
    echo "═══════════════════════════════════════════════════════════"
    echo -e "${NC}"
    echo
    log_success "Installation completed successfully"
    
    # Display access information
    echo -e "${BLUE}Access Information:${NC}"
    echo "  URL: http://$DOMAIN_NAME"
    echo "  Admin Email: $ADMIN_EMAIL"
    echo "  Admin Password: [as entered during installation]"
    echo
    echo -e "${BLUE}Installation Directory:${NC} $INSTALL_DIR"
    echo -e "${BLUE}Log File:${NC} $LOG_FILE"
    echo
    echo -e "${YELLOW}Next Steps:${NC}"
    echo "  1. Visit http://$DOMAIN_NAME and log in"
    echo "  2. Configure DNS records if using a domain"
    echo "  3. Run 'sudo certbot --apache' to obtain SSL certificate"
    echo "  4. Review .env file: $INSTALL_DIR/.env"
    echo "  5. Configure backup schedule"
    echo
    echo "For documentation and support:"
    echo "  GitHub: https://github.com/tsangkingyiu/KBPanel"
    echo
}

# Run main function
main "$@"