<?php

namespace App\Http\Controllers;

use App\Models\SecurityAlert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SecurityAlertController extends Controller
{
    public function index(Request $request): View
    {
        $query = SecurityAlert::with('detectionRule');

        // Filter by Status
        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        // Filter by Severity
        if ($request->filled('severity') && $request->input('severity') !== 'all') {
            $query->where('severity', $request->input('severity'));
        }

        // Filter by Search Query (Source IP or Rule Name)
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('source_ip', 'like', "%{$search}%")
                    ->orWhereHas('detectionRule', function ($ruleQ) use ($search) {
                        $ruleQ->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $alerts = $query->orderByDesc('triggered_at')->paginate(10)->withQueryString();

        // Metrics for Header & Quick Filter Badges
        $metrics = [
            'total' => SecurityAlert::count(),
            'open' => SecurityAlert::where('status', 'open')->count(),
            'critical' => SecurityAlert::where('severity', 'critical')->where('status', 'open')->count(),
            'acknowledged' => SecurityAlert::where('status', 'acknowledged')->count(),
            'resolved' => SecurityAlert::where('status', 'resolved')->count(),
        ];

        return view('pages.alerts', [
            'alerts' => $alerts,
            'metrics' => $metrics,
            'filters' => [
                'status' => $request->input('status', 'all'),
                'severity' => $request->input('severity', 'all'),
                'search' => $request->input('search', ''),
            ],
        ]);
    }

    public function updateStatus(Request $request, SecurityAlert $alert): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:open,acknowledged,resolved',
        ]);

        $alert->update([
            'status' => $validated['status'],
        ]);

        $statusLabels = [
            'open' => 'REOPENED',
            'acknowledged' => 'ACKNOWLEDGED',
            'resolved' => 'RESOLVED',
        ];

        $label = $statusLabels[$validated['status']] ?? strtoupper($validated['status']);

        return back()->with('success', "Alert #{$alert->id} successfully marked as {$label}.");
    }

    public function report(SecurityAlert $alert): View
    {
        $alert->load('detectionRule');

        $correlatedEvents = \App\Models\NormalizedEvent::with('rawLog.logSource')
            ->where('source_ip', $alert->source_ip)
            ->orderByDesc('timestamp')
            ->take(15)
            ->get();

        $containment = \App\Models\BlockedIp::where('ip_address', $alert->source_ip)
            ->where('status', 'active')
            ->first();

        $threatIntel = \App\Services\ThreatIntelService::lookup($alert->source_ip);

        return view('pages.alert-report', [
            'alert' => $alert,
            'correlatedEvents' => $correlatedEvents,
            'containment' => $containment,
            'threatIntel' => $threatIntel,
        ]);
    }
}
