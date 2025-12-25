<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StagingEnvironment extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'subdomain',
        'docker_container_id',
        'port',
        'status',
        'sync_with_production',
    ];

    protected $casts = [
        'port' => 'integer',
        'sync_with_production' => 'boolean',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
