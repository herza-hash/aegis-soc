<?php

namespace App\Services\Parsers;

interface LogParser
{
    public function supports(string $message): bool;

    public function parse(string $message): array;
}