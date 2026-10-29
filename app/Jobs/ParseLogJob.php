<?php

namespace App\Jobs;

use App\Models\NormalizedEvent;
use App\Models\RawLog;
use App\Services\Parsers\LogParserManager;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ParseLogJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(public readonly int $rawLogId)
    {
    }

    public function handle(LogParserManager $parsers): void
    {
        $rawLog = RawLog::find($this->rawLogId);

        if ($rawLog === null) {
            return;
        }

        $attributes = $parsers->parse($rawLog->raw_message);
        $timestamp = $attributes['timestamp'] ?? null;

        if (is_string($timestamp) && trim($timestamp) !== '') {
            try {
                $attributes['timestamp'] = CarbonImmutable::parse($timestamp);
            } catch (Throwable) {
                $attributes['timestamp'] = $rawLog->received_at;
            }
        } else {
            $attributes['timestamp'] = $rawLog->received_at;
        }

        if (! empty($attributes['source_ip'])) {
            $isBlocked = \App\Models\BlockedIp::where('ip_address', $attributes['source_ip'])
                ->where('status', 'active')
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->exists();

            if ($isBlocked) {
                $attributes['event_outcome'] = 'blocked';
                $parsedData = $attributes['parsed_data'] ?? [];
                $parsedData['active_defense'] = 'FIREWALL_POLICY_DROP';
                $attributes['parsed_data'] = $parsedData;
            }
        }

        $event = NormalizedEvent::updateOrCreate(
            ['raw_log_id' => $rawLog->id],
            $attributes,
        );

        app(\App\Services\DetectionEngine::class)->evaluate($event);
    }
}