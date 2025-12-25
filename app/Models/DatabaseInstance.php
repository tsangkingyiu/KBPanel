<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DatabaseInstance extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'db_type',
        'db_name',
        'db_user',
        'db_password',
        'port',
        'container_id',
        'adminer_url',
    ];

    protected $hidden = [
        'db_password',
    ];

    protected $casts = [
        'port' => 'integer',
        'db_password' => 'encrypted',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
