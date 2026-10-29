<?php

namespace App\Http\Controllers;

use App\Jobs\ParseLogJob;
use App\Models\LogSource;
use App\Models\NormalizedEvent;
use App\Models\RawLog;
use App\Models\SecurityAlert;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LogUploadController extends Controller
{
    public function index(): View
    {
        $sources = LogSource::where('status', 'active')->orderBy('name')->get();

        // Recent upload history (last 20 batch uploads)
        $recentUploads = RawLog::select('log_source_id', 'received_at')
            ->whereNotNull('received_at')
            ->with('logSource')
            ->orderByDesc('received_at')
            ->take(1)
            ->first();

        $metrics = [
            'total_raw_logs' => RawLog::count(),
            'total_events' => NormalizedEvent::count(),
            'total_alerts' => SecurityAlert::count(),
            'active_sources' => LogSource::where('status', 'active')->count(),
        ];

        // Get last 10 upload batches (grouped by batch tag in parsed_data)
        $uploadBatches = NormalizedEvent::whereNotNull('parsed_data')
            ->where('parsed_data', 'like', '%upload_batch%')
            ->orderByDesc('timestamp')
            ->take(50)
            ->get()
            ->groupBy(function ($event) {
                return $event->parsed_data['upload_batch'] ?? 'unknown';
            })
            ->take(10)
            ->map(function ($group, $batchId) {
                return [
                    'batch_id' => $batchId,
                    'total_lines' => $group->count(),
                    'first_event' => $group->last(),
                    'last_event' => $group->first(),
                    'severities' => $group->groupBy('severity')->map->count(),
                    'alerts_triggered' => SecurityAlert::where('triggered_at', '>=', $group->last()->timestamp)
                        ->where('triggered_at', '<=', $group->first()->timestamp->addMinutes(1))
                        ->count(),
                ];
            });

        return view('pages.upload', [
            'sources' => $sources,
            'metrics' => $metrics,
            'uploadBatches' => $uploadBatches,
        ]);
    }

    public function upload(Request $request): RedirectResponse
    {
        $request->validate([
            'log_file' => 'required|file|max:10240',
            'log_source_id' => 'required|exists:log_sources,id',
            'file_format' => 'required|in:auto,line_by_line,json_array,csv',
        ]);

        // Additional extension validation
        $allowedExtensions = ['txt', 'log', 'csv', 'json', 'text'];
        $ext = strtolower($request->file('log_file')->getClientOriginalExtension());
        if (!in_array($ext, $allowedExtensions)) {
            return back()->with('error', "Unsupported file extension '.{$ext}'. Allowed: " . implode(', ', $allowedExtensions));
        }

        $file = $request->file('log_file');
        $sourceId = $request->input('log_source_id');
        $format = $request->input('file_format');
        $source = LogSource::findOrFail($sourceId);
        $now = CarbonImmutable::now();
        $batchId = 'BATCH-' . strtoupper(substr(md5($file->getClientOriginalName() . $now->timestamp), 0, 8));

        $content = file_get_contents($file->getRealPath());
        $originalFilename = $file->getClientOriginalName();
        $fileSize = $file->getSize();

        // Determine format & extract lines
        $lines = $this->extractLines($content, $format, $originalFilename);

        if (empty($lines)) {
            return back()->with('error', 'No valid log lines found in the uploaded file. Ensure the file contains readable log entries.');
        }

        $totalLines = count($lines);
        $processedCount = 0;
        $alertsTriggered = 0;
        $alertsBefore = SecurityAlert::count();

        foreach ($lines as $index => $line) {
            $line = trim($line);
            if (empty($line) || strlen($line) < 5) {
                continue;
            }

            // Skip CSV headers
            if ($index === 0 && $format === 'csv' && $this->looksLikeHeader($line)) {
                continue;
            }

            $rawLog = RawLog::create([
                'log_source_id' => $source->id,
                'raw_message' => $line,
                'checksum' => hash('sha256', $line . $now->timestamp . $index),
                'received_at' => $now,
            ]);

            // Process through SIEM pipeline synchronously
            ParseLogJob::dispatchSync($rawLog->id);

            // Tag the normalized event with upload batch metadata
            $normalizedEvent = NormalizedEvent::where('raw_log_id', $rawLog->id)->first();
            if ($normalizedEvent) {
                $parsedData = $normalizedEvent->parsed_data ?? [];
                $parsedData['upload_batch'] = $batchId;
                $parsedData['source_file'] = $originalFilename;
                $parsedData['line_number'] = $index + 1;
                $normalizedEvent->update(['parsed_data' => $parsedData]);
            }

            $processedCount++;
        }

        $alertsAfter = SecurityAlert::count();
        $alertsTriggered = $alertsAfter - $alertsBefore;

        $feedback = "📁 [FILE UPLOAD]: '{$originalFilename}' (" . $this->formatBytes($fileSize) . ") successfully ingested into pipeline '{$source->name}'. ";
        $feedback .= "Processed {$processedCount}/{$totalLines} log entries (Batch: {$batchId}).";

        if ($alertsTriggered > 0) {
            $feedback .= " ⚠️ {$alertsTriggered} SECURITY ALERT(S) TRIGGERED from uploaded logs!";
        }

        return back()->with('success', $feedback);
    }

    /**
     * Extract log lines from file content based on format.
     */
    private function extractLines(string $content, string $format, string $filename): array
    {
        // Auto-detect format
        if ($format === 'auto') {
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            if ($ext === 'json') {
                $format = 'json_array';
            } elseif ($ext === 'csv') {
                $format = 'csv';
            } else {
                $format = 'line_by_line';
            }
        }

        switch ($format) {
            case 'json_array':
                return $this->extractJsonLines($content);

            case 'csv':
                return $this->extractCsvLines($content);

            case 'line_by_line':
            default:
                return array_filter(
                    explode("\n", str_replace("\r\n", "\n", $content)),
                    fn ($line) => trim($line) !== ''
                );
        }
    }

    /**
     * Extract lines from JSON content.
     * Supports both JSON arrays and NDJSON (newline-delimited JSON).
     */
    private function extractJsonLines(string $content): array
    {
        $content = trim($content);

        // Try as JSON array first
        $decoded = json_decode($content, true);
        if (is_array($decoded) && !empty($decoded)) {
            // Array of objects
            if (isset($decoded[0]) && is_array($decoded[0])) {
                return array_map(fn ($item) => json_encode($item), $decoded);
            }
            // Single object
            if (!isset($decoded[0])) {
                return [json_encode($decoded)];
            }
        }

        // Try as NDJSON (one JSON object per line)
        $lines = explode("\n", str_replace("\r\n", "\n", $content));
        $validLines = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line)) {
                $test = json_decode($line, true);
                if ($test !== null) {
                    $validLines[] = $line;
                } else {
                    $validLines[] = $line; // Still include non-JSON lines
                }
            }
        }

        return $validLines;
    }

    /**
     * Extract lines from CSV content. Each row becomes a single concatenated log string.
     */
    private function extractCsvLines(string $content): array
    {
        $rows = str_getcsv_rows($content);
        if (empty($rows)) {
            // Fallback to line-by-line
            return array_filter(
                explode("\n", str_replace("\r\n", "\n", $content)),
                fn ($line) => trim($line) !== ''
            );
        }

        $lines = [];
        foreach ($rows as $row) {
            $lines[] = implode(' | ', $row);
        }

        return $lines;
    }

    /**
     * Check if a line looks like a CSV header.
     */
    private function looksLikeHeader(string $line): bool
    {
        $headerKeywords = ['timestamp', 'date', 'time', 'source', 'ip', 'message', 'log', 'severity', 'level', 'action', 'event'];
        $lineLower = strtolower($line);
        $matches = 0;

        foreach ($headerKeywords as $keyword) {
            if (str_contains($lineLower, $keyword)) {
                $matches++;
            }
        }

        return $matches >= 2;
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }
}

/**
 * Helper: Parse CSV string into rows.
 */
function str_getcsv_rows(string $csv): array
{
    $rows = [];
    $handle = fopen('php://temp', 'r+');
    fwrite($handle, $csv);
    rewind($handle);

    while (($row = fgetcsv($handle)) !== false) {
        $rows[] = $row;
    }

    fclose($handle);

    return $rows;
}
