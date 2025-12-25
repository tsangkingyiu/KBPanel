# KBPanel v1.0.0

**A Self-Hosted Multi-Tenant Web Hosting Control Panel**

KBPanel is a modern, Docker-based hosting control panel built on Laravel 12, designed for Ubuntu 24.04 LTS servers. It provides a comprehensive GUI for managing multiple Laravel and WordPress projects with staging environments, database management, Git integration, and more.

## Features

### Core Capabilities
- **Multi-Tenant Architecture**: Role-based access control (Admin/User) with project isolation
- **Easy Deployment**: One-click deployment for Laravel (8.x - 12.x) and WordPress projects
- **Staging Environments**: Create isolated staging environments synced from production
- **Database Management**: phpMyAdmin integration with per-project database isolation
- **Git Integration**: Connect GitHub/GitLab/Bitbucket repositories with auto-deploy capabilities
- **File Manager**: Web-based file editor and directory browser
- **SSH Terminal**: Browser-based SSH access to project containers
- **SSL Management**: Automated Let's Encrypt certificate generation and renewal
- **Resource Monitoring**: Real-time CPU, RAM, disk usage tracking per project and user
- **Backup System**: Full and incremental backups with one-click restore

### Technical Stack

**Backend:**
- Laravel 12 (PHP 8.2+)
- MySQL 8.0 (shared Dockerized instance)
- Redis 7.x (cache/queue)
- Apache 2.4 (with Nginx per-project support)
- Docker + Docker Compose

**Frontend:**
- Blade templating
- Vue.js 3 components
- Tailwind CSS (planned)

**Infrastructure:**
- Ubuntu 24.04 LTS (required)
- Supervisor (queue workers)
- Certbot (SSL automation)
- phpMyAdmin (database management)

## Installation

### Prerequisites

- Fresh Ubuntu 24.04 LTS server
- Minimum 4GB RAM (8GB+ recommended)
- 50GB+ free disk space
- Root/sudo access
- Public IP address or domain name

### Quick Install

```bash
# Download the installation script
wget https://raw.githubusercontent.com/tsangkingyiu/KBPanel/v1.0.0/install.sh

# Make it executable
chmod +x install.sh

# Run the installer (interactive mode)
sudo ./install.sh
```

### What the Installer Does

1. **System Preparation**: Updates Ubuntu, installs dependencies
2. **Environment Setup**: Installs PHP 8.2, Composer, Node.js, Docker
3. **Laravel Base**: Creates fresh Laravel 12 project at `/var/www/kbpanel`
4. **KBPanel Overlay**: Clones and applies KBPanel-specific files from this repository
5. **Infrastructure**: Starts shared MySQL, Redis, and phpMyAdmin containers
6. **Web Server**: Configures Apache with virtual host
7. **Security**: Sets up firewall and proper file permissions

### Post-Installation

After installation completes:

1. Access KBPanel at `http://your-server-ip` or `https://your-domain.com`
2. Log in with the admin credentials provided during installation
3. Configure DNS records for your domain (if applicable)
4. Run `certbot` to obtain production SSL certificate
5. Review `.env` settings at `/var/www/kbpanel/.env`

## Architecture Overview

### Repository Structure

This repository contains **overlay files only** (not a complete Laravel app). The installer applies these files on top of a fresh Laravel 12 installation.

```
KBPanel/
├── app/
│   ├── Console/Commands/       # Artisan commands for deploy, SSL, Docker
│   ├── Http/Controllers/       # Admin, User, shared controllers
│   ├── Models/                 # Project, Deployment, Staging, etc.
│   └── Services/               # Business logic (Docker, Git, SSL, etc.)
├── config/
│   ├── kbpanel.php            # Main KBPanel configuration
│   ├── docker.php             # Docker templates and settings
│   └── monitoring.php         # Resource tracking config
├── database/migrations/        # Database schema
├── docker/
│   ├── services.yml           # Shared infrastructure (MySQL, Redis, phpMyAdmin)
│   ├── templates/             # Per-project Docker Compose templates
│   ├── nginx/                 # Nginx configuration templates
│   └── apache/                # Apache configuration templates
├── resources/
│   ├── views/                 # Blade templates (admin/user dashboards)
│   └── js/components/         # Vue.js components (file manager, terminal, etc.)
└── install.sh                 # One-command installation script
```

### Database Architecture

**Shared MySQL Container**: A single Dockerized MySQL 8.0 instance serves all projects, reducing memory overhead. Each project receives:
- Dedicated database
- Dedicated MySQL user with permissions limited to that database
- Configurable storage quota

This approach mirrors SiteGround's shared hosting model while maintaining data isolation.

### Multi-Tenancy Model

**Admin Users:**
- View/manage all projects across all users
- System monitoring dashboard
- User account management
- Global server configuration

**Regular Users:**
- Create and manage own projects (within quota)
- Deploy to production and staging
- File manager access (own projects only)
- SSH terminal (own containers only)
- Database management (own databases only)
- Git repository linking
- Backup/restore operations

## Usage Examples

### Deploy a Laravel Project

1. Log in to KBPanel
2. Navigate to **Projects** → **Create New Project**
3. Select "Laravel" as project type
4. Choose Laravel version (8.x - 12.x) and PHP version
5. Enter domain name
6. Click **Deploy**
7. KBPanel automatically:
   - Creates project directory
   - Spins up Docker container
   - Creates database and user
   - Configures web server (Apache/Nginx)
   - Generates SSL certificate

### Create Staging Environment

1. Open project dashboard
2. Click **Create Staging**
3. System creates `staging.yourdomain.com` with:
   - Cloned production files
   - Separate database (with production data)
   - Isolated Docker container
   - Separate SSL certificate

### Link Git Repository

1. Go to **Project** → **Git Integration**
2. Enter repository URL (GitHub/GitLab/Bitbucket)
3. Add access token or SSH key
4. Select branch
5. Enable auto-deploy on push (optional)
6. Click **Connect**

## Development Roadmap

### v1.0.0 (Current)
- ✅ Core multi-tenant architecture
- ✅ Laravel deployment support (8.x - 12.x)
- ✅ WordPress deployment support
- ✅ Staging environments
- ✅ Database management (phpMyAdmin)
- ✅ Git integration basics
- ✅ File manager
- ✅ SSH terminal
- ✅ SSL automation
- ✅ Resource monitoring
- ✅ Manual backups

### v1.1.0 (Planned)
- Automated backup scheduling
- Email server management (SMTP configuration)
- Advanced Git features (webhooks, commit history viewer)
- Nginx as primary web server option
- Custom PHP version per project
- Database import/export UI
- Team collaboration features

### v2.0.0 (Future)
- Multi-server support
- Load balancing
- Kubernetes integration
- Advanced monitoring (Prometheus + Grafana)
- Full email server (IMAP/POP3/SMTP)
- Marketplace for one-click app installations
- API for external integrations

## Security Considerations

- **Container Isolation**: Each project runs in a separate Docker container with user-namespaced UIDs
- **Network Isolation**: Projects cannot communicate with each other by default
- **Database Access**: Users can only access their own databases via permission-restricted MySQL users
- **File Permissions**: Projects stored in user-specific directories with proper ownership
- **phpMyAdmin**: Bound to localhost (127.0.0.1) by default, accessible via KBPanel proxy only
- **SSL Enforcement**: Automatic HTTPS redirection for all projects
- **Firewall**: UFW configured to allow only necessary ports (22, 80, 443)

## Troubleshooting

### Installation Failed

Check the installation log:
```bash
sudo cat /var/log/kbpanel-install.log
```

### Docker Container Won't Start

```bash
# Check Docker status
sudo systemctl status docker

# View container logs
sudo docker logs <container-name>

# Restart Docker service
sudo systemctl restart docker
```

### Database Connection Error

```bash
# Check if MySQL container is running
sudo docker ps | grep kbpanel_db

# Test database connection
sudo docker exec -it kbpanel_db mysql -uroot -p
```

### Apache Not Serving Site

```bash
# Check Apache configuration
sudo apache2ctl configtest

# View error logs
sudo tail -f /var/log/apache2/error.log

# Restart Apache
sudo systemctl restart apache2
```

## Contributing

Contributions are welcome! This is an early-stage project. Please:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

[To be determined]

## Credits

Developed by [Kirby Tsang](https://github.com/tsangkingyiu)

Inspired by:
- SiteGround cPanel
- Laravel Forge
- Plesk
- DirectAdmin

## Support

- **Documentation**: [Coming soon]
- **Issues**: [GitHub Issues](https://github.com/tsangkingyiu/KBPanel/issues)
- **Discussions**: [GitHub Discussions](https://github.com/tsangkingyiu/KBPanel/discussions)

---

**Note**: KBPanel v1.0.0 is an initial release. While functional, it's recommended for development/testing environments. Production use should include additional hardening and monitoring.