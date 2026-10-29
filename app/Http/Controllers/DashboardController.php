<?php

namespace App\Http\Controllers;

use App\Models\LogSource;
use App\Models\NormalizedEvent;
use App\Models\SecurityAlert;
use App\Services\ThreatIntelService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the SOC Dashboard shell or return JSON for AJAX/lazy loading.
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->wantsJson() || $request->ajax() || $request->query('format') === 'json') {
            return response()->json($this->getDashboardData());
        }

        // Return dashboard immediately with skeleton loaders for lazy-loading
        return view('pages.dashboard');
    }

    /**
     * API endpoint for dashboard telemetry data (lazy loading & real-time sync).
     */
    public function data(): JsonResponse
    {
        return response()->json($this->getDashboardData());
    }

    /**
     * Gather and compile all SOC metrics, chart series, and telemetry feeds.
     */
    protected function getDashboardData(): array
    {
        $now = CarbonImmutable::now();
        $twentyFourHoursAgo = $now->subHours(24);

        // 1. KPI Metrics
        $totalEvents = NormalizedEvent::count();
        $eventsLast24h = NormalizedEvent::where('timestamp', '>=', $twentyFourHoursAgo)->count();

        $openAlertsCount = SecurityAlert::where('status', 'open')->count();
        $criticalAlertsCount = SecurityAlert::where('status', 'open')
            ->where('severity', 'critical')
            ->count();

        $activeSourcesCount = LogSource::where('status', 'active')->count();
        $totalSourcesCount = LogSource::count();

        $uniqueAttackersCount = NormalizedEvent::where('timestamp', '>=', $twentyFourHoursAgo)
            ->whereIn('severity', ['high', 'critical'])
            ->whereNotNull('source_ip')
            ->distinct('source_ip')
            ->count('source_ip');

        // 2. Timeline Chart Data (Past 12 Hours)
        $timelineHours = [];
        $timelineAllCounts = [];
        $timelineThreatCounts = [];

        for ($i = 11; $i >= 0; $i--) {
            $hourStart = $now->subHours($i)->startOfHour();
            $hourEnd = $now->subHours($i)->endOfHour();
            $hourLabel = $hourStart->format('H:00');

            $totalInHour = NormalizedEvent::whereBetween('timestamp', [$hourStart, $hourEnd])->count();
            $threatsInHour = NormalizedEvent::whereBetween('timestamp', [$hourStart, $hourEnd])
                ->whereIn('severity', ['high', 'critical'])
                ->count();

            $timelineHours[] = $hourLabel;
            $timelineAllCounts[] = $totalInHour;
            $timelineThreatCounts[] = $threatsInHour;
        }

        // 3. Severity Distribution
        $severityCounts = NormalizedEvent::select('severity', DB::raw('count(*) as aggregate'))
            ->groupBy('severity')
            ->pluck('aggregate', 'severity')
            ->all();

        $severityDistribution = [
            'critical' => (int) ($severityCounts['critical'] ?? 0),
            'high' => (int) ($severityCounts['high'] ?? 0),
            'medium' => (int) ($severityCounts['medium'] ?? 0),
            'low' => (int) ($severityCounts['low'] ?? 0),
            'info' => (int) ($severityCounts['info'] ?? 0),
        ];

        // 4. Category Distribution
        $categoryDistribution = NormalizedEvent::select('event_category', DB::raw('count(*) as count'))
            ->groupBy('event_category')
            ->orderByDesc('count')
            ->take(5)
            ->get();

        // 5. Top Attacker / Suspicious IPs
        $topAttackersRaw = NormalizedEvent::select('source_ip', DB::raw('count(*) as total_events'), DB::raw('MAX(timestamp) as last_seen'))
            ->whereNotNull('source_ip')
            ->whereIn('severity', ['high', 'critical'])
            ->groupBy('source_ip')
            ->orderByDesc('total_events')
            ->take(5)
            ->get();

        $topAttackers = $topAttackersRaw->map(function ($attacker) {
            $intel = ThreatIntelService::lookup($attacker->source_ip);
            return [
                'source_ip' => $attacker->source_ip,
                'total_events' => (int) $attacker->total_events,
                'last_seen' => $attacker->last_seen,
                'last_seen_human' => $attacker->last_seen ? \Carbon\Carbon::parse($attacker->last_seen)->locale(app()->getLocale())->diffForHumans() : __('recently'),
                'intel' => $intel,
            ];
        });

        // 6. Recent Active Alerts
        $recentAlertsRaw = SecurityAlert::with('detectionRule')
            ->orderByDesc('triggered_at')
            ->take(5)
            ->get();

        $recentAlerts = $recentAlertsRaw->map(function ($alert) {
            return [
                'id' => $alert->id,
                'severity' => $alert->severity,
                'status' => $alert->status,
                'source_ip' => $alert->source_ip,
                'rule_name' => $alert->detectionRule->name ?? 'Correlated Security Alert',
                'triggered_at_human' => $alert->triggered_at?->locale(app()->getLocale())->diffForHumans() ?? __('recently'),
            ];
        });

        // 7. Recent Normalized Events Feed
        $recentEventsRaw = NormalizedEvent::with('rawLog.logSource')
            ->orderByDesc('timestamp')
            ->take(12)
            ->get();

        $recentEvents = $recentEventsRaw->map(function ($event) {
            $intel = ThreatIntelService::lookup($event->source_ip);
            return [
                'id' => $event->id,
                'time' => $event->timestamp ? $event->timestamp->format('H:i:s') : 'N/A',
                'date' => $event->timestamp ? $event->timestamp->format('Y-m-d') : '',
                'source_ip' => $event->source_ip,
                'username' => $event->username,
                'destination' => $event->destination_ip ?? ($event->rawLog?->logSource?->name ?? __('Edge Ingest')),
                'event_category' => $event->event_category,
                'event_action' => $event->event_action,
                'raw_message' => !empty($event->parsed_data['raw_message']) ? \Illuminate\Support\Str::limit($event->parsed_data['raw_message'], 60) : '',
                'event_outcome' => $event->event_outcome,
                'severity' => $event->severity,
                'intel' => $intel,
            ];
        });

        return [
            'metrics' => [
                'total_events' => $totalEvents,
                'events_24h' => $eventsLast24h,
                'open_alerts' => $openAlertsCount,
                'critical_alerts' => $criticalAlertsCount,
                'active_sources' => $activeSourcesCount,
                'total_sources' => $totalSourcesCount,
                'unique_attackers' => $uniqueAttackersCount,
                'sources_online_percent' => round(($activeSourcesCount / max(1, $totalSourcesCount)) * 100),
            ],
            'chartData' => [
                'hours' => $timelineHours,
                'totalEvents' => $timelineAllCounts,
                'threatEvents' => $timelineThreatCounts,
                'severities' => $severityDistribution,
            ],
            'categoryDistribution' => $categoryDistribution,
            'topAttackers' => $topAttackers,
            'recentAlerts' => $recentAlerts,
            'recentEvents' => $recentEvents,
            'serverTimeUtc' => $now->utc()->format('Y-m-d H:i:s'),
        ];
    }
}
