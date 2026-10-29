<?php

namespace App\Services\Parsers;

class SshLogParser implements LogParser
{
    public function supports(string $message): bool
    {
        return preg_match('/\bsshd(?:\[\d+\])?/i', $message) === 1
            || preg_match('/\b(?:Accepted|Failed)\s+(?:password|publickey)/i', $message) === 1;
    }

    public function parse(string $message): array
    {
        $sourceIp = null;
        $username = null;
        $outcome = 'unknown';
        $action = 'ssh_event';

        if (preg_match('/\b(Accepted|Failed)\s+(?:password|publickey|keyboard-interactive(?:\/pam)?)\s+for\s+(?:invalid user\s+)?(\S+)\s+from\s+([0-9a-f:.]+)/i', $message, $matches) === 1) {
            $outcome = strtolower($matches[1]) === 'accepted' ? 'success' : 'failure';
            $username = $matches[2];
            $sourceIp = $matches[3];
            $action = 'ssh_login';
        } elseif (preg_match('/\bInvalid user\s+(\S+)\s+from\s+([0-9a-f:.]+)/i', $message, $matches) === 1) {
            $outcome = 'failure';
            $username = $matches[1];
            $sourceIp = $matches[2];
            $action = 'ssh_login';
        }

        $severity = 'info';
        if ($outcome === 'failure') {
            $severity = strtolower((string) $username) === 'root' ? 'high' : 'medium';
        }

        return [
            'timestamp' => null,
            'source_ip' => $sourceIp,
            'destination_ip' => null,
            'username' => $username,
            'event_category' => 'authentication',
            'event_action' => $action,
            'event_outcome' => $outcome,
            'severity' => $severity,
            'parsed_data' => ['raw_message' => $message],
        ];
    }
}