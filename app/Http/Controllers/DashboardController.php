<?php

namespace App\Http\Controllers;

use App\Models\LogSource;
use App\Models\NormalizedEvent;
use App\Models\SecurityAlert;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(): View
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

        // 2. Timeline Chart Data (Last 12 Hours)
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
            'critical' => $severityCounts['critical'] ?? 0,
            'high' => $severityCounts['high'] ?? 0,
            'medium' => $severityCounts['medium'] ?? 0,
            'low' => $severityCounts['low'] ?? 0,
            'info' => $severityCounts['info'] ?? 0,
        ];

        // 4. Category Distribution
        $categoryDistribution = NormalizedEvent::select('event_category', DB::raw('count(*) as count'))
            ->groupBy('event_category')
            ->orderByDesc('count')
            ->take(5)
            ->get();

        // 5. Top Attacker / Suspicious IPs
        $topAttackers = NormalizedEvent::select('source_ip', DB::raw('count(*) as total_events'), DB::raw('MAX(timestamp) as last_seen'))
            ->whereNotNull('source_ip')
            ->whereIn('severity', ['high', 'critical'])
            ->groupBy('source_ip')
            ->orderByDesc('total_events')
            ->take(5)
            ->get();

        // 6. Recent Active Alerts
        $recentAlerts = SecurityAlert::with('detectionRule')
            ->orderByDesc('triggered_at')
            ->take(5)
            ->get();

        // 7. Recent Normalized Events Feed
        $recentEvents = NormalizedEvent::with('rawLog.logSource')
            ->orderByDesc('timestamp')
            ->take(12)
            ->get();

        return view('pages.dashboard', [
            'metrics' => [
                'total_events' => $totalEvents,
                'events_24h' => $eventsLast24h,
                'open_alerts' => $openAlertsCount,
                'critical_alerts' => $criticalAlertsCount,
                'active_sources' => $activeSourcesCount,
                'total_sources' => $totalSourcesCount,
                'unique_attackers' => $uniqueAttackersCount,
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
        ]);
    }
}
