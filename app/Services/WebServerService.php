<?php

namespace App\Services;

use App\Models\Project;

class WebServerService
{
    public function generateNginxConfig(Project $project)
    {
        // TODO: Generate Nginx config from template
        $template = file_get_contents(base_path('docker/nginx/site.conf.template'));
        
        $config = str_replace([
            '{{domain}}',
            '{{port}}',
            '{{root}}'
        ], [
            $project->domain,
            $project->port,
            '/var/www/html/public'
        ], $template);

        return $config;
    }

    public function generateApacheConfig(Project $project)
    {
        // TODO: Generate Apache config from template
        $template = file_get_contents(base_path('docker/apache/site.conf.template'));
        
        $config = str_replace([
            '{{domain}}',
            '{{port}}',
            '{{root}}'
        ], [
            $project->domain,
            $project->port,
            '/var/www/html/public'
        ], $template);

        return $config;
    }

    public function reloadWebServer($containerId)
    {
        // TODO: Reload web server in container
        return true;
    }
}
