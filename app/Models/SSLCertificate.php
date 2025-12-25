<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SSLCertificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'domain_id',
        'certificate_path',
        'private_key_path',
        'chain_path',
        'issuer',
        'expires_at',
        'auto_renew',
        'status',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'auto_renew' => 'boolean',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function domain()
    {
        return $this->belongsTo(Domain::class);
    }

    public function isExpiringSoon($days = 30)
    {
        return $this->expires_at?->diffInDays(now()) <= $days;
    }
}
