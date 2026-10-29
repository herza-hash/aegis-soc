<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DetectionRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'condition_type',
        'threshold',
        'time_window_minutes',
        'severity',
    ];

    protected function casts(): array
    {
        return [
            'threshold' => 'integer',
            'time_window_minutes' => 'integer',
        ];
    }

    public function securityAlerts(): HasMany
    {
        return $this->hasMany(SecurityAlert::class, 'rule_id');
    }
}