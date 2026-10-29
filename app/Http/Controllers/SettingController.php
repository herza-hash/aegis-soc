<?php

namespace App\Http\Controllers;

use App\Models\DetectionRule;
use App\Models\LogSource;
use App\Models\NormalizedEvent;
use App\Models\RawLog;
use App\Models\SecurityAlert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $rules = DetectionRule::withCount('securityAlerts')
            ->orderBy('id', 'asc')
            ->get();

        // System Diagnostic Metrics
        $systemInfo = [
            'app_name' => config('app.name'),
            'app_env' => config('app.env'),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database_driver' => config('database.default'),
            'queue_driver' => config('queue.default'),
            'server_os' => PHP_OS_FAMILY,
            'counts' => [
                'raw_logs' => RawLog::count(),
                'normalized_events' => NormalizedEvent::count(),
                'security_alerts' => SecurityAlert::count(),
                'log_sources' => LogSource::count(),
                'detection_rules' => $rules->count(),
            ],
            'parsers' => [
                ['name' => 'WebLogParser', 'format' => 'Nginx / Apache Common Log Format', 'status' => 'ONLINE'],
                ['name' => 'SshLogParser', 'format' => 'Linux OpenSSH sshd auth failures & successes', 'status' => 'ONLINE'],
                ['name' => 'JsonLogParser', 'format' => 'Structured JSON Syslog / Cloud Audit', 'status' => 'ONLINE'],
                ['name' => 'FallbackParser', 'format' => 'Unclassified raw text stream', 'status' => 'ONLINE'],
            ],
        ];

        return view('pages.settings', [
            'rules' => $rules,
            'systemInfo' => $systemInfo,
        ]);
    }

    public function storeRule(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'condition_type' => 'required|in:threshold_exceeded,pattern_match,single_event,volume_spike',
            'threshold' => 'required|integer|min:1|max:10000',
            'time_window_minutes' => 'required|integer|min:1|max:1440',
            'severity' => 'required|in:critical,high,medium,low,info',
        ]);

        $rule = DetectionRule::create($validated);

        return back()->with('success', "Detection Rule '{$rule->name}' successfully configured and deployed.");
    }

    public function destroyRule(DetectionRule $rule): RedirectResponse
    {
        $name = $rule->name;
        $rule->delete();

        return back()->with('success', "Detection Rule '{$name}' retired.");
    }
}
