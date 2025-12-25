#!/bin/bash
#
# KBPanel Installation Script v1.0.0
# Ubuntu 24.04 LTS Only
# Repository: https://github.com/tsangkingyiu/KBPanel
#
# This script follows the "Laravel-first" approach:
# 1. Install a fresh Laravel 12 application
# 2. Overlay KBPanel files from GitHub repository
# 3. Configure shared Docker services
# 4. Set up web server and security
#

set -euo pipefail

# ============================================================================
# CONFIGURATION
# ============================================================================

SCRIPT_VERSION="1.0.0"
LARAVEL_VERSION="12.*"
REPO_URL="https://github.com/tsangkingyiu/KBPanel.git"
REPO_BRANCH="main"
INSTALL_DIR="/var/www/kbpanel"
LOG_FILE="/var/log/kbpanel-install.log"
PHP_VERSION="8.2"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# ============================================================================
# UTILITY FUNCTIONS
# ============================================================================

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

# ============================================================================
# PHASE 1: PRE-INSTALLATION CHECKS
# ============================================================================

check_root() {
    if [[ $EUID -ne 0 ]]; then
        log_error "This script must be run as root or with sudo"
        exit 1
    fi
    log_success "Root privileges verified"
}

check_os() {
    if [[ ! -f /etc/os-release ]]; then
        log_error "Cannot detect operating system"
        exit 1
    fi
    
    source /etc/os-release
    
    if [[ "$ID" != "ubuntu" ]]; then
        log_error "This script only supports Ubuntu"
        exit 1
    fi
    
    if [[ "$VERSION_ID" != "24.04" ]]; then
        log_warning "This script is designed for Ubuntu 24.04. You are running $VERSION_ID"
        read -p "Continue anyway? (y/N): " -n 1 -r
        echo
        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            exit 1
        fi
    fi
    
    log_success "Operating system check passed: Ubuntu $VERSION_ID"
}

check_system_resources() {
    local ram_mb=$(free -m | awk '/^Mem:/{print $2}')
    local disk_gb=$(df -BG / | awk 'NR==2 {print $4}' | sed 's/G//')
    
    if [[ $ram_mb -lt 4096 ]]; then
        log_warning "RAM is below recommended 4GB (found ${ram_mb}MB)"
    else
        log_success "RAM check passed: ${ram_mb}MB available"
    fi
    
    if [[ $disk_gb -lt 50 ]]; then
        log_warning "Disk space is below recommended 50GB (found ${disk_gb}GB free)"
    else
        log_success "Disk space check passed: ${disk_gb}GB available"
    fi
}

collect_configuration() {
    log_info "Configuration required"
    echo
    
    read -sp "Enter MySQL root password: " MYSQL_ROOT_PASSWORD
    echo
    read -sp "Confirm MySQL root password: " MYSQL_ROOT_PASSWORD_CONFIRM
    echo
    
    if [[ "$MYSQL_ROOT_PASSWORD" != "$MYSQL_ROOT_PASSWORD_CONFIRM" ]]; then
        log_error "Passwords do not match"
        exit 1
    fi
    
    read -p "Enter KBPanel admin email: " ADMIN_EMAIL
    read -sp "Enter KBPanel admin password: " ADMIN_PASSWORD
    echo
    
    read -p "Enter domain name (or press Enter for server IP): " DOMAIN_NAME
    if [[ -z "$DOMAIN_NAME" ]]; then
        DOMAIN_NAME=$(curl -s ifconfig.me || echo "localhost")
    fi
    
    read -p "Installation directory [$INSTALL_DIR]: " CUSTOM_DIR
    if [[ -n "$CUSTOM_DIR" ]]; then
        INSTALL_DIR="$CUSTOM_DIR"
    fi
    
    log_success "Configuration collected"
}

# ============================================================================
# PHASE 2: CORE SYSTEM COMPONENTS
# ============================================================================

update_system() {
    log "Updating system packages..."
    apt-get update -y >> "$LOG_FILE" 2>&1
    apt-get upgrade -y >> "$LOG_FILE" 2>&1
    log_success "System updated"
}

install_php() {
    log "Installing PHP $PHP_VERSION and extensions..."
    
    add-apt-repository ppa:ondrej/php -y >> "$LOG_FILE" 2>&1
    apt-get update -y >> "$LOG_FILE" 2>&1
    
    apt-get install -y \
        php${PHP_VERSION}-cli \
        php${PHP_VERSION}-fpm \
        php${PHP_VERSION}-mysql \
        php${PHP_VERSION}-xml \
        php${PHP_VERSION}-mbstring \
        php${PHP_VERSION}-curl \
        php${PHP_VERSION}-zip \
        php${PHP_VERSION}-bcmath \
        php${PHP_VERSION}-gd \
        php${PHP_VERSION}-intl \
        php${PHP_VERSION}-redis >> "$LOG_FILE" 2>&1
    
    systemctl enable php${PHP_VERSION}-fpm >> "$LOG_FILE" 2>&1
    systemctl start php${PHP_VERSION}-fpm >> "$LOG_FILE" 2>&1
    
    log_success "PHP $PHP_VERSION installed"
}

install_composer() {
    log "Installing Composer..."
    
    EXPECTED_CHECKSUM="$(php -r 'copy("https://composer.github.io/installer.sig", "php://stdout");')"
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    ACTUAL_CHECKSUM="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"
    
    if [[ "$EXPECTED_CHECKSUM" != "$ACTUAL_CHECKSUM" ]]; then
        log_error "Composer installer corrupt"
        rm composer-setup.php
        exit 1
    fi
    
    php composer-setup.php --quiet --install-dir=/usr/local/bin --filename=composer
    rm composer-setup.php
    
    log_success "Composer installed: $(composer --version)"
}

install_nodejs() {
    log "Installing Node.js 20 LTS..."
    
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash - >> "$LOG_FILE" 2>&1
    apt-get install -y nodejs >> "$LOG_FILE" 2>&1
    
    log_success "Node.js installed: $(node --version)"
}

install_apache() {
    log "Installing Apache web server..."
    
    apt-get install -y apache2 >> "$LOG_FILE" 2>&1
    
    a2enmod rewrite ssl headers proxy proxy_http >> "$LOG_FILE" 2>&1
    a2dissite 000-default.conf >> "$LOG_FILE" 2>&1
    
    systemctl enable apache2 >> "$LOG_FILE" 2>&1
    systemctl start apache2 >> "$LOG_FILE" 2>&1
    
    log_success "Apache installed"
}

install_docker() {
    log "Installing Docker and Docker Compose..."
    
    apt-get install -y \
        ca-certificates \
        curl \
        gnupg \
        lsb-release >> "$LOG_FILE" 2>&1
    
    mkdir -p /etc/apt/keyrings
    curl -fsSL https://download.docker.com/linux/ubuntu/gpg | \
        gpg --dearmor -o /etc/apt/keyrings/docker.gpg
    
    echo \
        "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] \
        https://download.docker.com/linux/ubuntu \
        $(lsb_release -cs) stable" | \
        tee /etc/apt/sources.list.d/docker.list > /dev/null
    
    apt-get update -y >> "$LOG_FILE" 2>&1
    apt-get install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin >> "$LOG_FILE" 2>&1
    
    systemctl enable docker >> "$LOG_FILE" 2>&1
    systemctl start docker >> "$LOG_FILE" 2>&1
    
    log_success "Docker installed: $(docker --version)"
}

install_additional_tools() {
    log "Installing additional tools..."
    
    apt-get install -y \
        supervisor \
        redis-server \
        certbot \
        python3-certbot-apache \
        git \
        unzip \
        zip \
        ufw >> "$LOG_FILE" 2>&1
    
    systemctl enable redis-server >> "$LOG_FILE" 2>&1
    systemctl start redis-server >> "$LOG_FILE" 2>&1
    
    log_success "Additional tools installed"
}

# ============================================================================
# PHASE 3: LARAVEL 12 BASE INSTALLATION
# ============================================================================

install_laravel() {
    log "Installing fresh Laravel $LARAVEL_VERSION..."
    
    mkdir -p "$(dirname $INSTALL_DIR)"
    cd "$(dirname $INSTALL_DIR)"
    
    composer create-project --prefer-dist laravel/laravel "$(basename $INSTALL_DIR)" "$LARAVEL_VERSION" >> "$LOG_FILE" 2>&1
    
    chown -R www-data:www-data "$INSTALL_DIR"
    chmod -R 775 "$INSTALL_DIR/storage" "$INSTALL_DIR/bootstrap/cache"
    
    log_success "Laravel $LARAVEL_VERSION installed"
}

# ============================================================================
# PHASE 4: KBPANEL REPOSITORY INTEGRATION
# ============================================================================

overlay_kbpanel() {
    log "Overlaying KBPanel files from repository..."
    
    local temp_dir="/tmp/kbpanel-repo"
    rm -rf "$temp_dir"
    
    git clone --branch "$REPO_BRANCH" "$REPO_URL" "$temp_dir" >> "$LOG_FILE" 2>&1
    
    # Copy overlay directories
    [[ -d "$temp_dir/app" ]] && cp -r "$temp_dir/app/"* "$INSTALL_DIR/app/"
    [[ -d "$temp_dir/resources" ]] && cp -r "$temp_dir/resources/"* "$INSTALL_DIR/resources/"
    [[ -d "$temp_dir/database/migrations" ]] && cp -r "$temp_dir/database/migrations/"* "$INSTALL_DIR/database/migrations/"
    [[ -d "$temp_dir/routes" ]] && cp -r "$temp_dir/routes/"* "$INSTALL_DIR/routes/"
    [[ -d "$temp_dir/config" ]] && cp -r "$temp_dir/config/"* "$INSTALL_DIR/config/"
    [[ -d "$temp_dir/docker" ]] && cp -r "$temp_dir/docker" "$INSTALL_DIR/"
    
    rm -rf "$temp_dir"
    
    chown -R www-data:www-data "$INSTALL_DIR"
    
    log_success "KBPanel files overlaid"
}

install_dependencies() {
    log "Installing KBPanel dependencies..."
    
    cd "$INSTALL_DIR"
    
    if [[ -f "composer.json" ]]; then
        composer install --no-interaction --prefer-dist >> "$LOG_FILE" 2>&1
    fi
    
    if [[ -f "package.json" ]]; then
        npm install >> "$LOG_FILE" 2>&1
        npm run build >> "$LOG_FILE" 2>&1
    fi
    
    log_success "Dependencies installed"
}

# ============================================================================
# PHASE 5: APPLICATION CONFIGURATION
# ============================================================================

configure_environment() {
    log "Configuring application environment..."
    
    cd "$INSTALL_DIR"
    
    cp .env.example .env
    
    sed -i "s/APP_NAME=.*/APP_NAME=KBPanel/" .env
    sed -i "s/APP_URL=.*/APP_URL=https:\/\/${DOMAIN_NAME}/" .env
    sed -i "s/DB_DATABASE=.*/DB_DATABASE=kbpanel/" .env
    sed -i "s/DB_USERNAME=.*/DB_USERNAME=root/" .env
    sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=${MYSQL_ROOT_PASSWORD}/" .env
    
    php artisan key:generate --force >> "$LOG_FILE" 2>&1
    
    log_success "Environment configured"
}

setup_docker_services() {
    log "Starting shared Docker services..."
    
    cd "$INSTALL_DIR"
    
    if [[ -f "docker/services.yml" ]]; then
        export DB_ROOT_PASSWORD="$MYSQL_ROOT_PASSWORD"
        docker compose -f docker/services.yml up -d >> "$LOG_FILE" 2>&1
        
        # Wait for MySQL to be ready
        sleep 10
    fi
    
    log_success "Docker services started"
}

setup_database() {
    log "Setting up KBPanel database..."
    
    cd "$INSTALL_DIR"
    
    php artisan migrate --force >> "$LOG_FILE" 2>&1
    
    log_success "Database migrated"
}

# ============================================================================
# PHASE 6: WEB SERVER CONFIGURATION
# ============================================================================

configure_apache() {
    log "Configuring Apache virtual host..."
    
    cat > /etc/apache2/sites-available/kbpanel.conf << EOF
<VirtualHost *:80>
    ServerName ${DOMAIN_NAME}
    DocumentRoot ${INSTALL_DIR}/public

    <Directory ${INSTALL_DIR}/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/kbpanel_error.log
    CustomLog \${APACHE_LOG_DIR}/kbpanel_access.log combined
</VirtualHost>
EOF

    a2ensite kbpanel.conf >> "$LOG_FILE" 2>&1
    systemctl reload apache2 >> "$LOG_FILE" 2>&1
    
    log_success "Apache configured"
}

# ============================================================================
# PHASE 7: SECURITY & FIREWALL
# ============================================================================

configure_firewall() {
    log "Configuring firewall..."
    
    ufw --force enable >> "$LOG_FILE" 2>&1
    ufw allow 22/tcp >> "$LOG_FILE" 2>&1
    ufw allow 80/tcp >> "$LOG_FILE" 2>&1
    ufw allow 443/tcp >> "$LOG_FILE" 2>&1
    ufw reload >> "$LOG_FILE" 2>&1
    
    log_success "Firewall configured"
}

# ============================================================================
# MAIN INSTALLATION FLOW
# ============================================================================

main() {
    clear
    echo "═══════════════════════════════════════════════════════════════"
    echo "  KBPanel Installation Script v${SCRIPT_VERSION}"
    echo "  Ubuntu 24.04 LTS"
    echo "═══════════════════════════════════════════════════════════════"
    echo
    
    log "Installation started"
    
    # Phase 1: Pre-installation
    check_root
    check_os
    check_system_resources
    collect_configuration
    
    # Phase 2: System components
    update_system
    install_php
    install_composer
    install_nodejs
    install_apache
    install_docker
    install_additional_tools
    
    # Phase 3: Laravel base
    install_laravel
    
    # Phase 4: KBPanel overlay
    overlay_kbpanel
    install_dependencies
    
    # Phase 5: Configuration
    configure_environment
    setup_docker_services
    setup_database
    
    # Phase 6: Web server
    configure_apache
    
    # Phase 7: Security
    configure_firewall
    
    echo
    echo "═══════════════════════════════════════════════════════════════"
    log_success "KBPanel installation completed successfully!"
    echo "═══════════════════════════════════════════════════════════════"
    echo
    log_info "Access KBPanel at: http://${DOMAIN_NAME}"
    log_info "Admin email: ${ADMIN_EMAIL}"
    log_info "Installation directory: ${INSTALL_DIR}"
    log_info "Log file: ${LOG_FILE}"
    echo
    log_warning "Next steps:"
    echo "  1. Configure DNS records for ${DOMAIN_NAME}"
    echo "  2. Run: certbot --apache -d ${DOMAIN_NAME} (for SSL)"
    echo "  3. Create your first admin user in the database"
    echo "  4. Log in and start deploying projects!"
    echo
}

main "$@"
