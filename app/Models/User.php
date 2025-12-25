<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\HasResourceLimits;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasResourceLimits;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'disk_quota',
        'project_limit',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'disk_quota' => 'integer',
        'project_limit' => 'integer',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function backups()
    {
        return $this->hasMany(Backup::class);
    }

    public function resourceUsage()
    {
        return $this->hasMany(ResourceUsage::class);
    }

    public function isAdmin()
    {
        return $this->role?->name === 'admin';
    }

    public function isUser()
    {
        return $this->role?->name === 'user';
    }
}
