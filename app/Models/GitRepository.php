<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GitRepository extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'repository_url',
        'branch',
        'access_token',
        'last_commit_hash',
        'last_pulled_at',
        'auto_deploy',
    ];

    protected $hidden = [
        'access_token',
    ];

    protected $casts = [
        'last_pulled_at' => 'datetime',
        'auto_deploy' => 'boolean',
        'access_token' => 'encrypted',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
