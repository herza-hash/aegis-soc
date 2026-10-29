<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NormalizedEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'raw_log_id',
        'timestamp',
        'source_ip',
        'destination_ip',
        'username',
        'event_category',
        'event_action',
        'event_outcome',
        'severity',
        'parsed_data',
    ];

    protected function casts(): array
    {
        return [
            'timestamp' => 'immutable_datetime',
            'parsed_data' => 'array',
        ];
    }

    public function rawLog(): BelongsTo
    {
        return $this->belongsTo(RawLog::class);
    }
}