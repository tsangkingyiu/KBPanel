<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResourceUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id',
        'cpu_percent',
        'memory_mb',
        'disk_mb',
        'bandwidth_mb',
        'recorded_at',
    ];

    protected $casts = [
        'cpu_percent' => 'float',
        'memory_mb' => 'float',
        'disk_mb' => 'float',
        'bandwidth_mb' => 'float',
        'recorded_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
