<?php

/**
 * KBPanel Email Configuration
 * 
 * This configuration file manages email server settings for KBPanel.
 * Currently serves as a placeholder for future email functionality.
 * 
 * Planned Features:
 * - SMTP configuration and testing
 * - Domain-based email account management  
 * - Email server integration (Postfix/Dovecot)
 * - Webmail interface integration
 * - Email forwarding and aliases
 * 
 * @version 1.0.0
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Enable Email Features
    |--------------------------------------------------------------------------
    |
    | Master switch for email functionality. Set to false in v1.0.0 as email
    | features are planned for future releases.
    |
    */

    'enabled' => env('KBPANEL_EMAIL_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | SMTP Configuration
    |--------------------------------------------------------------------------
    |
    | Default SMTP settings for system notifications and user email services.
    | Users will be able to configure their own SMTP settings per project.
    |
    */

    'smtp' => [
        'default_host' => env('MAIL_HOST', 'smtp.mailtrap.io'),
        'default_port' => env('MAIL_PORT', 2525),
        'default_encryption' => env('MAIL_ENCRYPTION', 'tls'),
        'default_username' => env('MAIL_USERNAME'),
        'default_password' => env('MAIL_PASSWORD'),
        'default_from_address' => env('MAIL_FROM_ADDRESS', 'noreply@kbpanel.local'),
        'default_from_name' => env('MAIL_FROM_NAME', 'KBPanel'),
        
        // Connection timeout in seconds
        'timeout' => 10,
        
        // Verify SSL certificates
        'verify_peer' => env('MAIL_VERIFY_PEER', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Per-User Email Accounts (Planned Feature)
    |--------------------------------------------------------------------------
    |
    | Configuration for user-specific email accounts tied to their domains.
    | Users can create email accounts like admin@theirdomain.com
    |
    */

    'user_accounts' => [
        // Enable users to create email accounts
        'allow_creation' => false,
        
        // Maximum email accounts per user
        'max_per_user' => 10,
        
        // Maximum email accounts per project
        'max_per_project' => 5,
        
        // Storage quota per email account (MB)
        'storage_quota_mb' => 1024,
        
        // Email account naming pattern
        'allowed_pattern' => '/^[a-z0-9._-]+@[a-z0-9.-]+\.[a-z]{2,}$/',
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Server Integration (Future)
    |--------------------------------------------------------------------------
    |
    | Settings for integrated email server management (Postfix, Dovecot, etc.)
    |
    */

    'server' => [
        // Email server type: 'postfix', 'exim', 'sendmail'
        'type' => env('EMAIL_SERVER_TYPE', 'postfix'),
        
        // IMAP server settings
        'imap' => [
            'enabled' => false,
            'port' => 993,
            'encryption' => 'ssl',
        ],
        
        // POP3 server settings
        'pop3' => [
            'enabled' => false,
            'port' => 995,
            'encryption' => 'ssl',
        ],
        
        // SMTP server for sending
        'smtp' => [
            'enabled' => false,
            'port' => 587,
            'encryption' => 'tls',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Webmail Integration (Future)
    |--------------------------------------------------------------------------
    |
    | Configuration for webmail clients like Roundcube, Rainloop, or SnappyMail
    |
    */

    'webmail' => [
        'enabled' => false,
        
        // Webmail client: 'roundcube', 'rainloop', 'snappymail'
        'client' => 'roundcube',
        
        // Webmail URL path
        'url' => env('WEBMAIL_URL', '/webmail'),
        
        // Auto-login users to webmail
        'auto_login' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Forwarding & Aliases
    |--------------------------------------------------------------------------
    |
    | Settings for email forwarding rules and alias management
    |
    */

    'forwarding' => [
        'enabled' => false,
        
        // Maximum forwards per email account
        'max_per_account' => 5,
        
        // Allow catch-all addresses
        'allow_catchall' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Anti-Spam & Security
    |--------------------------------------------------------------------------
    |
    | Email security and spam protection settings
    |
    */

    'security' => [
        // Enable SpamAssassin integration
        'spamassassin' => false,
        
        // Enable ClamAV antivirus scanning
        'clamav' => false,
        
        // SPF record auto-generation
        'auto_spf' => true,
        
        // DKIM signing
        'dkim' => [
            'enabled' => false,
            'key_size' => 2048,
        ],
        
        // DMARC policy
        'dmarc' => [
            'enabled' => false,
            'policy' => 'quarantine', // 'none', 'quarantine', 'reject'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | System notification email preferences
    |
    */

    'notifications' => [
        // Send deployment notifications
        'deployments' => true,
        
        // Send backup completion notifications
        'backups' => true,
        
        // Send SSL renewal notifications
        'ssl_renewals' => true,
        
        // Send resource quota warnings
        'quota_warnings' => true,
        
        // Send security alerts
        'security_alerts' => true,
        
        // Email administrators on system errors
        'system_errors' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Queue Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for email queue processing
    |
    */

    'queue' => [
        // Queue driver for emails: 'sync', 'database', 'redis'
        'driver' => env('MAIL_QUEUE_DRIVER', 'redis'),
        
        // Queue name for email jobs
        'queue_name' => 'emails',
        
        // Retry failed emails
        'retry_on_failure' => true,
        
        // Maximum retry attempts
        'max_retries' => 3,
        
        // Retry delay in seconds
        'retry_delay' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Testing & Debugging
    |--------------------------------------------------------------------------
    |
    | Development and testing configurations
    |
    */

    'testing' => [
        // Log all outgoing emails
        'log_emails' => env('MAIL_LOG', false),
        
        // Prevent actual sending (catch-all for testing)
        'disable_delivery' => env('MAIL_DISABLE_DELIVERY', false),
        
        // Test email address
        'test_recipient' => env('MAIL_TEST_RECIPIENT'),
        
        // Show detailed SMTP debug output
        'debug' => env('MAIL_DEBUG', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Prevent email abuse with rate limiting
    |
    */

    'rate_limiting' => [
        'enabled' => true,
        
        // Maximum emails per user per hour
        'per_user_hourly' => 100,
        
        // Maximum emails per user per day
        'per_user_daily' => 500,
        
        // Maximum emails per project per hour
        'per_project_hourly' => 50,
    ],

];
