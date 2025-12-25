# KBPanel v1.0.0

**A Multi-Tenant Web Hosting Control Panel for Ubuntu 24.04**

KBPanel is a self-hosted, SiteGround/cPanel-like control panel built on Laravel 12 and Docker. It provides an intuitive GUI for deploying and managing multiple Laravel and WordPress projects with staging environments, Git integration, and comprehensive monitoring.

## Features

### Core Functionality
- **Multi-Tenant Architecture**: Role-based access control (Admin/User)
- **Easy Project Deployment**: One-click Laravel and WordPress installations
- **Staging Environments**: Create isolated staging instances per project
- **Git Integration**: Connect GitHub/GitLab repositories with auto-deploy
- **Web-Based Management**: File manager, SSH terminal, database manager (phpMyAdmin)
- **SSL/TLS**: Automatic Let's Encrypt certificate generation
- **Server Selection**: Choose between Nginx or Apache per project
- **PHP Version Control**: Support for PHP 7.4, 8.0, 8.1, 8.2, 8.3
- **Resource Monitoring**: Real-time CPU, RAM, disk, and bandwidth tracking

### Technology Stack
- **Backend**: Laravel 12, PHP 8.2
- **Frontend**: Blade templates + Vue.js components
- **Containerization**: Docker + Docker Compose
- **Database**: Shared MySQL 8.0 container (per-project isolation via users/permissions)
- **Cache/Queue**: Redis 7.x
- **Web Servers**: Apache 2.4 (host) + Nginx/Apache (per project)
- **Process Manager**: Supervisor
- **Monitoring**: Prometheus + Node Exporter

## Architecture

KBPanel uses an **overlay architecture**:
1. A fresh Laravel 12 base is installed on the server
2. KBPanel-specific files are overlaid onto the Laravel structure
3. Shared Docker services (MySQL, Redis, phpMyAdmin) run as core infrastructure
4. Each user project runs in an isolated Docker container

### Shared Database Architecture
To optimize RAM usage (critical for 4-8GB servers), KBPanel uses a **single shared MySQL Docker container** for all projects, with strict per-project database users and permissions for security isolation.

## Installation

### Requirements
- **OS**: Ubuntu 24.04 LTS
- **RAM**: Minimum 4GB (8GB recommended)
- **Disk**: 100GB+ SSD
- **CPU**: 4+ cores recommended
- **Root Access**: Required for installation

### Quick Install

```bash
# Download installer
wget https://raw.githubusercontent.com/tsangkingyiu/KBPanel/main/install.sh

# Make executable
chmod +x install.sh

# Run installation
sudo ./install.sh
```

The installer will:
1. Install system dependencies (PHP 8.2, Composer, Node.js, Docker)
2. Create a fresh Laravel 12 installation
3. Overlay KBPanel files from this repository
4. Configure Apache web server
5. Start shared Docker services (MySQL, Redis, phpMyAdmin)
6. Create initial admin user
7. Configure firewall and security settings

### Post-Installation

After installation completes:

1. Access KBPanel at `https://your-server-ip`
2. Log in with the admin credentials provided during installation
3. Configure DNS records for your domains
4. Set up Let's Encrypt SSL for your main domain
5. Create your first project!

## Project Structure

```
KBPanel/ (Overlay Repository)
├── app/
│   ├── Console/Commands/          # Artisan commands for deployment, SSL, staging
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/             # Admin dashboard controllers
│   │   │   └── User/              # User dashboard controllers
│   │   └── Middleware/            # Access control middleware
│   ├── Models/                    # Project, Deployment, User models
│   ├── Services/                  # Docker, Git, SSL, Monitoring services
│   └── Repositories/              # Data access layer
├── config/
│   ├── kbpanel.php               # Main KBPanel configuration
│   ├── docker.php                # Docker service settings
│   └── monitoring.php            # Resource monitoring config
├── database/migrations/           # Database schema
├── docker/
│   ├── services.yml              # Shared infrastructure (MySQL, Redis, phpMyAdmin)
│   ├── templates/                # Per-project Docker Compose templates
│   ├── nginx/                    # Nginx configuration templates
│   └── apache/                   # Apache configuration templates
├── resources/
│   ├── views/                    # Blade templates
│   └── js/components/            # Vue.js components
├── install.sh                     # Main installation script
└── README.md
```

## Usage

### For Users
- **Create Projects**: Deploy Laravel or WordPress with one click
- **Manage Staging**: Sync production to staging, test changes safely
- **Git Deployment**: Connect repositories, enable auto-deploy on push
- **File Management**: Web-based file editor and SSH terminal
- **Database Access**: Manage databases via integrated phpMyAdmin
- **Monitor Resources**: Track CPU, RAM, disk usage per project
- **Backups**: Create and restore full backups or database-only

### For Administrators
- **User Management**: Create users, set quotas, manage permissions
- **System Monitoring**: View server-wide resource usage
- **Global Settings**: Configure PHP versions, web servers, SSL
- **Access All Projects**: View and manage all user projects
- **Audit Logs**: Track all system actions

## Security

- **Multi-Tenant Isolation**: Docker containers prevent cross-project access
- **Database Isolation**: Per-project MySQL users with restricted permissions
- **Access Control**: Role-based permissions (Admin/User)
- **Encrypted Credentials**: Git tokens and passwords encrypted at rest
- **Firewall Configuration**: UFW configured to allow only necessary ports
- **SSL Enforcement**: Let's Encrypt integration for all domains
- **Audit Logging**: All administrative actions logged

## Development Roadmap

### v1.0.0 (Current)
- Core multi-tenant architecture
- Laravel and WordPress deployment
- Staging environments
- Basic Git integration
- File manager and SSH terminal
- phpMyAdmin database management
- Resource monitoring
- User and project CRUD

### Future Versions
- Email server management (SMTP configuration, domain email accounts)
- Automated backups with retention policies
- One-click application marketplace (additional frameworks)
- Advanced monitoring with alerts and webhooks
- Team collaboration features
- API for external integrations
- Mobile app for monitoring

## Contributing

This project is currently in initial release. Contributions, feature requests, and bug reports are welcome via GitHub Issues.

## License

TBD - Please check repository for license file.

## Support

For technical support and documentation, please visit the [GitHub repository](https://github.com/tsangkingyiu/KBPanel).

## Credits

Built with Laravel 12, Docker, and modern web technologies. Inspired by SiteGround and cPanel.
