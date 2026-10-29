<?php

namespace App\Services\Parsers;

use Carbon\CarbonImmutable;

class WebLogParser implements LogParser
{
    public function supports(string $message): bool
    {
        return preg_match('/^\S+\s+\S+\s+\S+\s+\[[^]]+\]\s+"[A-Z]+\s+\S+\s+HTTP\/\d(?:\.\d)?"\s+\d{3}\s+/i', $message) === 1;
    }

    public function parse(string $message): array
    {
        preg_match('/^(\S+)\s+\S+\s+\S+\s+\[([^]]+)\]\s+"([A-Z]+)\s+(\S+)\s+HTTP\/\d(?:\.\d)?"\s+(\d{3})\s+(\S+)/i', $message, $matches);

        $status = (int) ($matches[5] ?? 0);
        $outcome = $status >= 400 ? 'failure' : 'success';
        $severity = $status >= 500 ? 'high' : ($status >= 400 ? 'medium' : 'info');
        try {
            $parsedTimestamp = isset($matches[2])
                ? CarbonImmutable::createFromFormat('d/M/Y:H:i:s O', $matches[2])
                : null;
        } catch (\Throwable) {
            $parsedTimestamp = null;
        }
        $timestamp = $parsedTimestamp instanceof CarbonImmutable
            ? $parsedTimestamp->toISOString()
            : null;

        return [
            'timestamp' => $timestamp,
            'source_ip' => $matches[1] ?? null,
            'destination_ip' => null,
            'username' => null,
            'event_category' => 'web',
            'event_action' => isset($matches[3], $matches[4]) ? strtoupper($matches[3]).' '.$matches[4] : 'http_request',
            'event_outcome' => $outcome,
            'severity' => $severity,
            'parsed_data' => [
                'http_method' => $matches[3] ?? null,
                'path' => $matches[4] ?? null,
                'status_code' => $status,
                'response_bytes' => $matches[6] ?? null,
                'raw_message' => $message,
            ],
        ];
    }
}