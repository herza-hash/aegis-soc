<?php

namespace App\Services\Parsers;

use JsonException;

class JsonLogParser implements LogParser
{
    public function supports(string $message): bool
    {
        try {
            return is_array(json_decode($message, true, 512, JSON_THROW_ON_ERROR));
        } catch (JsonException) {
            return false;
        }
    }

    public function parse(string $message): array
    {
        $data = json_decode($message, true, 512, JSON_THROW_ON_ERROR);
        $severity = strtolower($this->firstString($data, ['severity', 'level']) ?? 'info');

        if (! in_array($severity, ['critical', 'high', 'medium', 'low', 'info'], true)) {
            $severity = 'info';
        }

        return [
            'timestamp' => $this->firstString($data, ['timestamp', '@timestamp', 'time']),
            'source_ip' => $this->firstString($data, ['source_ip', 'sourceIp', 'src_ip']),
            'destination_ip' => $this->firstString($data, ['destination_ip', 'destinationIp', 'dst_ip']),
            'username' => $this->firstString($data, ['username', 'user']),
            'event_category' => $this->firstString($data, ['event_category', 'category']) ?? 'system',
            'event_action' => $this->firstString($data, ['event_action', 'action', 'event']) ?? 'log',
            'event_outcome' => $this->firstString($data, ['event_outcome', 'outcome']) ?? 'unknown',
            'severity' => $severity,
            'parsed_data' => $data,
        ];
    }

    private function firstString(array $data, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = $data[$key] ?? null;

            if (is_string($value) || is_numeric($value)) {
                return (string) $value;
            }
        }

        return null;
    }
}