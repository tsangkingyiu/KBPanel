<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'smtp_host',
        'smtp_port',
        'smtp_user',
        'smtp_password',
        'from_address',
        'from_name',
        'encryption',
    ];

    protected $hidden = [
        'smtp_password',
    ];

    protected $casts = [
        'smtp_port' => 'integer',
        'smtp_password' => 'encrypted',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
