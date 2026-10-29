<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RawLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'log_source_id',
        'raw_message',
        'checksum',
        'received_at',
    ];

    protected function casts(): array
    {
        return [
            'received_at' => 'immutable_datetime',
        ];
    }

    public function logSource(): BelongsTo
    {
        return $this->belongsTo(LogSource::class);
    }

    public function normalizedEvent(): HasOne
    {
        return $this->hasOne(NormalizedEvent::class);
    }
}