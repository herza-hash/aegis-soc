<?php

namespace App\Services;

use App\Models\DetectionRule;
use App\Models\NormalizedEvent;
use App\Models\SecurityAlert;
use Carbon\CarbonImmutable;

class DetectionEngine
{
    /**
     * Correlate a normalized event against active detection rules.
     */
    public function evaluate(NormalizedEvent $event): ?SecurityAlert
    {
        if (empty($event->source_ip)) {
            return null;
        }

        $rules = DetectionRule::all();
        $now = CarbonImmutable::now();

        foreach ($rules as $rule) {
            $isTriggered = false;

            if ($rule->condition_type === 'single_event') {
                if ($event->severity === 'critical' || str_contains(strtolower($event->event_action), 'root')) {
                    $isTriggered = true;
                }
            } elseif ($rule->condition_type === 'pattern_match') {
                $rawMsg = strtolower($event->parsed_data['raw_message'] ?? $event->event_action);
                if (
                    str_contains($rawMsg, '.env') ||
                    str_contains($rawMsg, 'union select') ||
                    str_contains($rawMsg, 'sqlmap') ||
                    str_contains($rawMsg, 'nikto') ||
                    str_contains($rawMsg, 'cgi-bin')
                ) {
                    $isTriggered = true;
                }
            } elseif ($rule->condition_type === 'threshold_exceeded' || $rule->condition_type === 'volume_spike') {
                $windowStart = $now->subMinutes($rule->time_window_minutes);
                $eventCount = NormalizedEvent::where('source_ip', $event->source_ip)
                    ->where('timestamp', '>=', $windowStart)
                    ->count();

                if ($eventCount >= $rule->threshold) {
                    $isTriggered = true;
                }
            }

            if ($isTriggered) {
                // Prevent duplicate open alert in the last 15 minutes for the same IP and Rule
                $existingAlert = SecurityAlert::where('rule_id', $rule->id)
                    ->where('source_ip', $event->source_ip)
                    ->where('status', 'open')
                    ->where('triggered_at', '>=', $now->subMinutes(15))
                    ->first();

                if (! $existingAlert) {
                    return SecurityAlert::create([
                        'rule_id' => $rule->id,
                        'source_ip' => $event->source_ip,
                        'severity' => $rule->severity,
                        'status' => 'open',
                        'triggered_at' => $now,
                    ]);
                }
            }
        }

        return null;
    }
}
