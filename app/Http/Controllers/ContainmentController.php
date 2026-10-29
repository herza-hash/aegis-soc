<?php

namespace App\Http\Controllers;

use App\Models\BlockedIp;
use App\Models\NormalizedEvent;
use App\Models\SecurityAlert;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContainmentController extends Controller
{
    public function index(Request $request): View
    {
        $query = BlockedIp::query();

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('ip_address', 'like', "%{$search}%")
                    ->orWhere('reason', 'like', "%{$search}%");
            });
        }

        $blockedIps = $query->orderByDesc('created_at')->paginate(10)->withQueryString();

        $activeBlocks = BlockedIp::where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', CarbonImmutable::now());
            })
            ->pluck('ip_address');

        // Generate Automated Firewall Scripts
        $firewallRules = [
            'iptables' => $activeBlocks->map(fn ($ip) => "iptables -I INPUT 1 -s {$ip} -j DROP -m comment --comment \"Aegis-SOAR\"")->implode("\n"),
            'ufw' => $activeBlocks->map(fn ($ip) => "ufw insert 1 deny from {$ip} to any comment \"Aegis-SOAR\"")->implode("\n"),
            'nftables' => "table inet filter {\n  set aegis_blackhole {\n    type ipv4_addr\n    elements = { " . $activeBlocks->implode(', ') . " }\n  }\n  chain input {\n    ip saddr @aegis_blackhole drop\n  }\n}",
            'cisco' => $activeBlocks->map(fn ($ip) => "access-list AEGIS_INGRESS line 1 extended deny ip host {$ip} any log")->implode("\n"),
        ];

        $metrics = [
            'total' => BlockedIp::count(),
            'active' => $activeBlocks->count(),
            'permanent' => BlockedIp::where('status', 'active')->whereNull('expires_at')->count(),
            'quarantined' => BlockedIp::where('status', 'active')->whereNotNull('expires_at')->count(),
            'mitigated_attacks' => NormalizedEvent::where('event_outcome', 'blocked')->count(),
        ];

        return view('pages.containment', [
            'blockedIps' => $blockedIps,
            'metrics' => $metrics,
            'firewallRules' => $firewallRules,
            'filters' => [
                'status' => $request->input('status', 'all'),
                'search' => $request->input('search', ''),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ip_address' => 'required|string|max:45',
            'reason' => 'required|string|max:255',
            'duration' => 'required|in:1h,24h,7d,permanent',
            'blocked_by' => 'nullable|string|max:100',
            'alert_id' => 'nullable|exists:security_alerts,id',
        ]);

        $now = CarbonImmutable::now();
        $expiresAt = match ($validated['duration']) {
            '1h' => $now->addHour(),
            '24h' => $now->addDay(),
            '7d' => $now->addDays(7),
            'permanent' => null,
        };

        $blocked = BlockedIp::updateOrCreate(
            ['ip_address' => $validated['ip_address']],
            [
                'reason' => $validated['reason'],
                'blocked_by' => $validated['blocked_by'] ?? 'SOC Analyst (Active Containment)',
                'status' => 'active',
                'expires_at' => $expiresAt,
            ]
        );

        // If triggered from an alert, automatically acknowledge/resolve the alert
        if (! empty($validated['alert_id'])) {
            $alert = SecurityAlert::find($validated['alert_id']);
            if ($alert && $alert->status === 'open') {
                $alert->update(['status' => 'acknowledged']);
            }
        }

        $durationLabel = $validated['duration'] === 'permanent' ? 'Permanently' : "for {$validated['duration']}";

        return back()->with('success', "🛡️ [CONTAINMENT ACTIVE]: IP {$blocked->ip_address} quarantined {$durationLabel}. Ingress traffic will be dropped.");
    }

    public function unblock(BlockedIp $blockedIp): RedirectResponse
    {
        $ip = $blockedIp->ip_address;
        $blockedIp->update(['status' => 'revoked']);

        return back()->with('success', "IP {$ip} containment revoked. Traffic restored to default inspection policy.");
    }
}
