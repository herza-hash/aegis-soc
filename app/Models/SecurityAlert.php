<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'rule_id',
        'source_ip',
        'severity',
        'status',
        'triggered_at',
    ];

    protected function casts(): array
    {
        return [
            'triggered_at' => 'immutable_datetime',
        ];
    }

    public function detectionRule(): BelongsTo
    {
        return $this->belongsTo(DetectionRule::class, 'rule_id');
    }
}