# KBPanel v1.0.0

**KBPanel** is a self-hosted, multi-tenant web hosting control panel built for Ubuntu 24.04, powered by Laravel 12 and Docker.

## 🚀 Features

- **Multi-Tenancy**: Isolated Docker containers for each user project
- **Database Management**: Shared Dockerized MySQL with per-user isolation
- **One-Click Deployment**: Support for Laravel 12 and WordPress projects
- **Staging Environments**: Create staging subdomains with production sync
- **phpMyAdmin Integration**: Web-based database management
- **Git Integration**: Deploy from GitHub/GitLab/Bitbucket
- **SSL Certificates**: Let's Encrypt integration with auto-renewal
- **Resource Monitoring**: Real-time CPU, RAM, and disk usage tracking
- **File Manager**: Web-based file editor and SSH terminal
- **Role-Based Access**: Admin and user roles with permission management

## 📋 Architecture

- **Core Framework**: Laravel 12 (PHP 8.2+)
- **Containerization**: Docker + Docker Compose
- **Database**: Shared MySQL 8.0 (per-project DB isolation)
- **Cache/Queue**: Redis 7
- **Web Server**: Apache 2.4 (switchable to Nginx per project)
- **Frontend**: Blade Templates + Vue.js components
- **Database UI**: phpMyAdmin (localhost-restricted)

## 🛠️ Installation

This repository contains the **overlay files** for KBPanel. The installation script will install a fresh Laravel 12 base and apply these customizations.

### System Requirements

- **OS**: Ubuntu 24.04 LTS
- **CPU**: 4+ cores (8+ recommended)
- **RAM**: 8GB minimum (16GB+ recommended)
- **Storage**: 100GB SSD minimum (500GB+ recommended)
- **Network**: Public IP address

### Quick Install

Run the following command on your Ubuntu 24.04 server:

```bash
wget https://raw.githubusercontent.com/tsangkingyiu/KBPanel/v1.0.0/install.sh
chmod +x install.sh
sudo ./install.sh
```

The installer will:
1. Install system dependencies (PHP 8.2, Composer, Node.js, Docker)
2. Install a fresh Laravel 12 application
3. Clone and overlay KBPanel files from this repository
4. Configure Docker services (MySQL, Redis, phpMyAdmin)
5. Set up Apache virtual host
6. Run database migrations
7. Create the admin user

### Post-Installation

After installation completes:

1. **Access KBPanel**: Navigate to `http://your-server-ip` or `https://your-domain.com`
2. **Login**: Use the admin credentials displayed after installation
3. **Configure DNS**: Point your domain to the server IP
4. **Get SSL Certificate**: Run `sudo certbot --apache` for production SSL
5. **Review Settings**: Check `.env` file in `/var/www/kbpanel/`

## 📁 Project Structure

```
KBPanel/
├── app/
│   ├── Console/Commands/       # Artisan commands for deployment, SSL, Docker management
│   ├── Http/Controllers/       # Admin & User controllers
│   ├── Models/                 # Eloquent models (Project, Deployment, User, etc.)
│   └── Services/               # Business logic (DockerService, DeploymentService, etc.)
├── config/
│   ├── kbpanel.php            # KBPanel configuration
│   ├── docker.php             # Docker settings
│   └── monitoring.php         # Monitoring configuration
├── database/migrations/        # Database schema
├── docker/
│   ├── templates/             # Docker Compose templates for projects
│   ├── apache/                # Apache vhost templates
│   ├── nginx/                 # Nginx configuration templates
│   └── services.yml           # Core services (MySQL, Redis, phpMyAdmin)
├── resources/
│   ├── views/                 # Blade templates (admin, user dashboards)
│   └── js/components/         # Vue.js components
└── install.sh                 # Installation script
```

## 🔧 Usage

### Creating a Project

1. Log in to KBPanel
2. Navigate to **Projects** → **Create New**
3. Choose project type (Laravel or WordPress)
4. Configure domain, PHP version, and web server
5. Click **Deploy**

### Managing Databases

- Access phpMyAdmin at `http://your-server:8080` (localhost only)
- Or use KBPanel's built-in database manager
- Each project gets its own isolated database

### Creating Staging Environment

1. Open your project
2. Click **Staging** → **Create Staging**
3. System will clone production to `staging.yourdomain.com`
4. Test changes in staging before pushing to production

### Git Integration

1. Go to **Project** → **Git Settings**
2. Add repository URL and access token
3. Configure auto-deploy or manual pull
4. View commit history and deploy specific commits

## 🔐 Security

- **User Isolation**: Each project runs in isolated Docker containers
- **Database Security**: Per-project DB users with limited permissions
- **File Permissions**: Strict ownership and permission management
- **SSL/TLS**: Let's Encrypt SSL certificates for all domains
- **Access Control**: Role-based permissions (admin/user)
- **Audit Logging**: Track all administrative actions

## 📊 Monitoring

- **System Metrics**: CPU, RAM, disk usage (server-wide)
- **Project Metrics**: Per-container resource consumption
- **User Quotas**: Track disk usage against quotas
- **Real-time Updates**: WebSocket-powered live dashboards
- **Alerts**: Threshold notifications for resource limits

## 🤝 Contributing

This is a private repository. For bug reports or feature requests, please contact the repository owner.

## 📄 License

Proprietary - All rights reserved.

## 🔗 Links

- **Repository**: https://github.com/tsangkingyiu/KBPanel
- **Documentation**: Coming soon
- **Issues**: Contact repository owner

## ⚙️ Technology Stack

- **Backend**: Laravel 12, PHP 8.2
- **Frontend**: Blade, Vue.js 3, Tailwind CSS
- **Database**: MySQL 8.0
- **Cache**: Redis 7
- **Containers**: Docker, Docker Compose
- **Web Server**: Apache 2.4 / Nginx
- **Process Manager**: Supervisor
- **SSL**: Certbot (Let's Encrypt)
- **Monitoring**: Prometheus, Node Exporter

---

**Version**: 1.0.0  
**Last Updated**: December 26, 2025  
**Maintained by**: Kirby (tsangkingyiu)
