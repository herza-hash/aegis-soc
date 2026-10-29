<?php

namespace App\Http\Controllers;

use App\Jobs\ParseLogJob;
use App\Models\LogSource;
use App\Models\NormalizedEvent;
use App\Models\RawLog;
use App\Models\SecurityAlert;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LogSourceController extends Controller
{
    public function index(): View
    {
        $sources = LogSource::withCount('rawLogs')
            ->orderBy('status', 'asc') // active first if we sort properly
            ->orderBy('id', 'asc')
            ->get();

        $metrics = [
            'total' => $sources->count(),
            'active' => $sources->where('status', 'active')->count(),
            'inactive' => $sources->where('status', 'inactive')->count(),
            'total_logs' => RawLog::count(),
            'syslog' => $sources->where('type', 'syslog')->count(),
            'file' => $sources->where('type', 'file')->count(),
            'api' => $sources->where('type', 'api')->count(),
        ];

        // Sample Log Presets for the Simulator
        $presets = [
            [
                'title' => 'Web .env Traversal Attack (Nginx HTTP 404)',
                'message' => '185.220.101.45 - - [' . date('d/M/Y:H:i:s O') . '] "GET /.env HTTP/1.1" 404 196 "-" "curl/7.81.0"',
                'type' => 'file',
            ],
            [
                'title' => 'SSH Root Brute Force Attempt (sshd failure)',
                'message' => 'Failed password for invalid user root from 45.154.255.89 port ' . rand(30000, 60000) . ' ssh2',
                'type' => 'syslog',
            ],
            [
                'title' => 'SQL Injection Vulnerability Probe (WAF Trigger)',
                'message' => '194.26.29.112 - - [' . date('d/M/Y:H:i:s O') . '] "GET /api/v1/users?id=1%20UNION%20SELECT%20password HTTP/1.1" 500 520 "-" "sqlmap/1.6"',
                'type' => 'file',
            ],
            [
                'title' => 'Clean Authenticated Web API Request (HTTP 200)',
                'message' => '10.0.1.105 - - [' . date('d/M/Y:H:i:s O') . '] "GET /api/v1/dashboard/metrics HTTP/1.1" 200 4820 "-" "Mozilla/5.0"',
                'type' => 'api',
            ],
            [
                'title' => 'Valid SSH Key Login (lead_analyst success)',
                'message' => 'Accepted publickey for lead_analyst from 10.0.1.105 port ' . rand(40000, 60000) . ' ssh2: RSA SHA256:abcd1234efgh',
                'type' => 'syslog',
            ],
        ];

        return view('pages.sources', [
            'sources' => $sources,
            'metrics' => $metrics,
            'presets' => $presets,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'ip_address' => 'required|string|max:45',
            'type' => 'required|in:syslog,file,api',
            'status' => 'required|in:active,inactive',
        ]);

        $source = LogSource::create($validated);

        return back()->with('success', "Log Source '{$source->name}' ({$source->ip_address}) successfully registered.");
    }

    public function toggleStatus(LogSource $source): RedirectResponse
    {
        $newStatus = $source->status === 'active' ? 'inactive' : 'active';
        $source->update(['status' => $newStatus]);

        return back()->with('success', "Pipeline '{$source->name}' status updated to " . strtoupper($newStatus) . '.');
    }

    public function destroy(LogSource $source): RedirectResponse
    {
        $name = $source->name;
        $source->delete();

        return back()->with('success', "Log Source '{$name}' removed from telemetry network.");
    }

    /**
     * Simulation Bench: Dispatches an ingestion event directly from the dashboard.
     */
    public function simulate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'log_source_id' => 'required|exists:log_sources,id',
            'raw_message' => 'required|string',
        ]);

        $now = CarbonImmutable::now();
        $source = LogSource::findOrFail($validated['log_source_id']);

        $rawLog = RawLog::create([
            'log_source_id' => $source->id,
            'raw_message' => $validated['raw_message'],
            'checksum' => hash('sha256', $validated['raw_message'] . $now->timestamp),
            'received_at' => $now,
        ]);

        // Process immediately synchronously
        ParseLogJob::dispatchSync($rawLog->id);

        $normalized = NormalizedEvent::where('raw_log_id', $rawLog->id)->first();
        $alert = SecurityAlert::where('source_ip', $normalized?->source_ip)
            ->where('triggered_at', '>=', $now->subSeconds(5))
            ->first();

        $feedback = "Log ingested into pipeline '{$source->name}' and normalized as [{$normalized?->event_category} / {$normalized?->event_action}].";
        if ($alert) {
            $feedback .= " [ALERT TRIGGERED]: #SEC-{$alert->id} ({$alert->severity} severity)!";
        }

        return back()->with('success', $feedback);
    }

    /**
     * Public API Ingest Endpoint: For external agents / curl.
     */
    public function ingestApi(Request $request): JsonResponse
    {
        $message = $request->input('raw_message') ?? $request->input('message');
        if (empty($message)) {
            return response()->json(['error' => 'Field "raw_message" is required'], 422);
        }

        $sourceId = $request->input('log_source_id');
        $ip = $request->ip();

        if ($sourceId) {
            $source = LogSource::find($sourceId);
        } else {
            $source = LogSource::where('ip_address', $ip)->first() ?? LogSource::first();
        }

        if (! $source) {
            return response()->json(['error' => 'No active log source available'], 400);
        }

        $now = CarbonImmutable::now();
        $rawLog = RawLog::create([
            'log_source_id' => $source->id,
            'raw_message' => $message,
            'checksum' => hash('sha256', $message . $now->timestamp),
            'received_at' => $now,
        ]);

        ParseLogJob::dispatchSync($rawLog->id);
        $normalized = NormalizedEvent::where('raw_log_id', $rawLog->id)->first();

        return response()->json([
            'status' => 'ingested',
            'raw_log_id' => $rawLog->id,
            'checksum' => $rawLog->checksum,
            'event' => $normalized,
        ], 201);
    }
}
