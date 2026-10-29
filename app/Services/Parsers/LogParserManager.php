<?php

namespace App\Services\Parsers;

class LogParserManager
{
    private array $parsers;

    public function __construct()
    {
        $this->parsers = [
            new JsonLogParser(),
            new SshLogParser(),
            new WebLogParser(),
        ];
    }

    public function parse(string $message): array
    {
        foreach ($this->parsers as $parser) {
            if ($parser->supports($message)) {
                return $parser->parse($message);
            }
        }

        return [
            'timestamp' => null,
            'source_ip' => null,
            'destination_ip' => null,
            'username' => null,
            'event_category' => 'system',
            'event_action' => 'unclassified_log',
            'event_outcome' => 'unknown',
            'severity' => 'info',
            'parsed_data' => ['raw_message' => $message],
        ];
    }
}