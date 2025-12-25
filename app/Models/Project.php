<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasResourceLimits;
use App\Traits\HasMonitoring;

class Project extends Model
{
    use HasFactory, HasResourceLimits, HasMonitoring;

    protected $fillable = [
        'user_id',
        'name',
        'domain',
        'type',
        'path',
        'status',
        'laravel_version',
        'php_version',
        'web_server',
        'docker_container_id',
        'port',
        'has_staging',
        'git_repository_id',
        'disk_usage',
    ];

    protected $casts = [
        'has_staging' => 'boolean',
        'disk_usage' => 'integer',
        'port' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deployments()
    {
        return $this->hasMany(Deployment::class);
    }

    public function stagingEnvironment()
    {
        return $this->hasOne(StagingEnvironment::class);
    }

    public function backups()
    {
        return $this->hasMany(Backup::class);
    }

    public function gitRepository()
    {
        return $this->belongsTo(GitRepository::class);
    }

    public function databaseInstance()
    {
        return $this->hasOne(DatabaseInstance::class);
    }

    public function sslCertificates()
    {
        return $this->hasMany(SSLCertificate::class);
    }

    public function domains()
    {
        return $this->hasMany(Domain::class);
    }

    public function resourceUsage()
    {
        return $this->hasMany(ResourceUsage::class);
    }
}
