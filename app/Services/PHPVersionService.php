<?php

namespace App\Services;

class PHPVersionService
{
    protected $supportedVersions = ['7.4', '8.0', '8.1', '8.2', '8.3'];

    public function getSupportedVersions()
    {
        return $this->supportedVersions;
    }

    public function getDefaultVersion()
    {
        return '8.2';
    }

    public function switchPHPVersion($projectId, $version)
    {
        if (!in_array($version, $this->supportedVersions)) {
            throw new \InvalidArgumentException("PHP version {$version} is not supported");
        }

        // TODO: Update project Docker container to use specified PHP version
        // This would involve rebuilding the container with different PHP-FPM image
        return true;
    }

    public function getCurrentVersion($projectId)
    {
        // TODO: Get current PHP version from container
        return '8.2';
    }
}
