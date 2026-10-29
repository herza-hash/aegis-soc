<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LogSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'ip_address',
        'type',
        'status',
    ];

    public function rawLogs(): HasMany
    {
        return $this->hasMany(RawLog::class);
    }
}