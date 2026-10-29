@extends('layouts.app', ['title' => 'Security Alerts | Aegis SOC'])

@section('content')
<div class="space-y-6" x-data="{
    modalOpen: false,
    selectedAlert: null,
    investigate(alert) {
        this.selectedAlert = alert;
        this.modalOpen = true;
    }
}">
    <!-- Header -->
    <div class="flex flex-col gap-4 border-b border-zinc-800/80 pb-6 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2 font-mono text-[11px] uppercase tracking-wider text-rose-400">
                <span class="inline-block h-1.5 w-1.5 rounded-full bg-rose-500 shadow-[0_0_8px_#f43f5e]"></span>
                <span>AEGIS / INCIDENT RESPONSE QUEUE</span>
            </div>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-zinc-100 sm:text-3xl">Security Alerts & Incident Triage</h1>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 rounded-md border border-zinc-800 bg-zinc-900 px-3 py-1.5 font-mono text-xs text-zinc-300 hover:bg-zinc-800 hover:text-white transition-colors">
                &larr; BACK TO OVERVIEW
            </a>
        </div>
    </div>

    <!-- Quick Status Cards -->
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-5">
        <!-- Open Alerts -->
        <a href="{{ route('alerts.index', ['status' => 'open']) }}" class="rounded-lg border {{ $filters['status'] === 'open' ? 'border-rose-500 bg-rose-950/40 ring-1 ring-rose-500' : 'border-zinc-800 bg-zinc-950 hover:border-zinc-700' }} p-4 transition-all">
            <span class="font-mono text-xs uppercase text-rose-400 flex items-center gap-1.5">
                <span class="h-1.5 w-1.5 rounded-full bg-rose-400 animate-pulse"></span> Open
            </span>
            <span class="mt-2 block font-mono text-2xl font-extrabold text-zinc-100">{{ $metrics['open'] }}</span>
            <span class="text-[11px] font-mono text-zinc-500">{{ $metrics['critical'] }} Critical</span>
        </a>

        <!-- Acknowledged -->
        <a href="{{ route('alerts.index', ['status' => 'acknowledged']) }}" class="rounded-lg border {{ $filters['status'] === 'acknowledged' ? 'border-amber-500 bg-amber-950/40 ring-1 ring-amber-500' : 'border-zinc-800 bg-zinc-950 hover:border-zinc-700' }} p-4 transition-all">
            <span class="font-mono text-xs uppercase text-amber-400">Under Review</span>
            <span class="mt-2 block font-mono text-2xl font-extrabold text-zinc-100">{{ $metrics['acknowledged'] }}</span>
            <span class="text-[11px] font-mono text-zinc-500">Acknowledged</span>
        </a>

        <!-- Resolved -->
        <a href="{{ route('alerts.index', ['status' => 'resolved']) }}" class="rounded-lg border {{ $filters['status'] === 'resolved' ? 'border-emerald-500 bg-emerald-950/40 ring-1 ring-emerald-500' : 'border-zinc-800 bg-zinc-950 hover:border-zinc-700' }} p-4 transition-all">
            <span class="font-mono text-xs uppercase text-emerald-400">Mitigated</span>
            <span class="mt-2 block font-mono text-2xl font-extrabold text-zinc-100">{{ $metrics['resolved'] }}</span>
            <span class="text-[11px] font-mono text-zinc-500">Resolved</span>
        </a>

        <!-- Total History -->
        <a href="{{ route('alerts.index') }}" class="rounded-lg border {{ $filters['status'] === 'all' && $filters['severity'] === 'all' ? 'border-cyan-500 bg-cyan-950/40 ring-1 ring-cyan-500' : 'border-zinc-800 bg-zinc-950 hover:border-zinc-700' }} p-4 transition-all">
            <span class="font-mono text-xs uppercase text-cyan-400">Total Alerts</span>
            <span class="mt-2 block font-mono text-2xl font-extrabold text-zinc-100">{{ $metrics['total'] }}</span>
            <span class="text-[11px] font-mono text-zinc-500">All Statuses</span>
        </a>

        <!-- Critical Only -->
        <a href="{{ route('alerts.index', ['severity' => 'critical']) }}" class="rounded-lg border {{ $filters['severity'] === 'critical' ? 'border-rose-500 bg-rose-950/40 ring-1 ring-rose-500' : 'border-zinc-800 bg-zinc-950 hover:border-zinc-700' }} p-4 transition-all col-span-2 sm:col-span-1">
            <span class="font-mono text-xs uppercase text-rose-500 font-bold">Severity: Critical</span>
            <span class="mt-2 block font-mono text-2xl font-extrabold text-rose-400">{{ $metrics['critical'] }}</span>
            <span class="text-[11px] font-mono text-zinc-500">Immediate Action</span>
        </a>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-4">
        <form method="GET" action="{{ route('alerts.index') }}" class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-1 flex-wrap items-center gap-3">
                <!-- Search input -->
                <div class="relative min-w-[240px] flex-1">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-zinc-500">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Search by Source IP or Rule Name..." class="w-full rounded-md border border-zinc-800 bg-zinc-900/90 py-2 pl-9 pr-3 font-mono text-xs text-zinc-200 placeholder-zinc-500 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500">
                </div>

                <!-- Status Filter -->
                <div>
                    <select name="status" class="rounded-md border border-zinc-800 bg-zinc-900/90 px-3 py-2 font-mono text-xs text-zinc-200 focus:border-cyan-500 focus:outline-none">
                        <option value="all" {{ $filters['status'] === 'all' ? 'selected' : '' }}>Status: All</option>
                        <option value="open" {{ $filters['status'] === 'open' ? 'selected' : '' }}>Status: Open</option>
                        <option value="acknowledged" {{ $filters['status'] === 'acknowledged' ? 'selected' : '' }}>Status: Acknowledged</option>
                        <option value="resolved" {{ $filters['status'] === 'resolved' ? 'selected' : '' }}>Status: Resolved</option>
                    </select>
                </div>

                <!-- Severity Filter -->
                <div>
                    <select name="severity" class="rounded-md border border-zinc-800 bg-zinc-900/90 px-3 py-2 font-mono text-xs text-zinc-200 focus:border-cyan-500 focus:outline-none">
                        <option value="all" {{ $filters['severity'] === 'all' ? 'selected' : '' }}>Severity: All</option>
                        <option value="critical" {{ $filters['severity'] === 'critical' ? 'selected' : '' }}>Critical</option>
                        <option value="high" {{ $filters['severity'] === 'high' ? 'selected' : '' }}>High</option>
                        <option value="medium" {{ $filters['severity'] === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="low" {{ $filters['severity'] === 'low' ? 'selected' : '' }}>Low</option>
                        <option value="info" {{ $filters['severity'] === 'info' ? 'selected' : '' }}>Info</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="inline-flex items-center gap-1.5 rounded-md bg-cyan-600 px-4 py-2 font-mono text-xs font-semibold text-white hover:bg-cyan-500 transition-colors">
                    FILTER
                </button>
                @if($filters['search'] || $filters['status'] !== 'all' || $filters['severity'] !== 'all')
                    <a href="{{ route('alerts.index') }}" class="rounded-md border border-zinc-700 bg-zinc-800 px-3 py-2 font-mono text-xs text-zinc-300 hover:text-white transition-colors">
                        RESET
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Alert Records Table -->
    <div class="overflow-hidden rounded-lg border border-zinc-800 bg-zinc-950">
        <div class="flex items-center justify-between border-b border-zinc-800 px-5 py-3">
            <span class="font-mono text-xs text-zinc-400">
                Showing {{ $alerts->firstItem() ?? 0 }}-{{ $alerts->lastItem() ?? 0 }} of {{ $alerts->total() }} alerts
            </span>
            <span class="font-mono text-xs text-zinc-500">Live correlated incidents</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[880px] text-left text-xs">
                <thead class="border-b border-zinc-800 bg-zinc-900/70 font-mono uppercase text-zinc-400">
                    <tr>
                        <th class="px-4 py-3 font-normal">Alert ID</th>
                        <th class="px-4 py-3 font-normal">Severity</th>
                        <th class="px-4 py-3 font-normal">Triggered Rule</th>
                        <th class="px-4 py-3 font-normal">Target Source IP</th>
                        <th class="px-4 py-3 font-normal">Triggered Time</th>
                        <th class="px-4 py-3 font-normal">Status</th>
                        <th class="px-4 py-3 font-normal text-right">Triage Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/60 font-mono">
                    @forelse ($alerts as $alert)
                        <tr class="transition-colors hover:bg-zinc-900/40">
                            <!-- Alert ID -->
                            <td class="whitespace-nowrap px-4 py-3.5 text-zinc-400">
                                #SEC-{{ str_pad($alert->id, 4, '0', STR_PAD_LEFT) }}
                            </td>

                            <!-- Severity -->
                            <td class="whitespace-nowrap px-4 py-3.5">
                                @if($alert->severity === 'critical')
                                    <span class="inline-flex items-center gap-1 rounded bg-rose-950 px-2 py-0.5 font-bold uppercase text-rose-300 border border-rose-800">
                                        <span class="h-1.5 w-1.5 rounded-full bg-rose-400 animate-pulse"></span> CRITICAL
                                    </span>
                                @elseif($alert->severity === 'high')
                                    <span class="inline-flex items-center rounded bg-orange-950 px-2 py-0.5 font-bold uppercase text-orange-300 border border-orange-800">
                                        HIGH
                                    </span>
                                @elseif($alert->severity === 'medium')
                                    <span class="inline-flex items-center rounded bg-amber-950 px-2 py-0.5 font-bold uppercase text-amber-300 border border-amber-800">
                                        MEDIUM
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded bg-zinc-900 px-2 py-0.5 font-bold uppercase text-zinc-400 border border-zinc-800">
                                        INFO
                                    </span>
                                @endif
                            </td>

                            <!-- Rule Name -->
                            <td class="px-4 py-3.5">
                                <span class="font-semibold text-zinc-100 block">{{ $alert->detectionRule->name ?? 'Detection Rule' }}</span>
                                <span class="text-[10px] text-cyan-400 uppercase font-mono">
                                    Condition: {{ $alert->detectionRule->condition_type ?? 'anomaly' }} 
                                    (Threshold: {{ $alert->detectionRule->threshold ?? 1 }} / {{ $alert->detectionRule->time_window_minutes ?? 1 }}m)
                                </span>
                            </td>

                            <!-- Source IP with Geolocation & Threat Intel -->
                            <td class="whitespace-nowrap px-4 py-3.5">
                                @php
                                    $intel = \App\Services\ThreatIntelService::lookup($alert->source_ip);
                                @endphp
                                <div class="flex items-center gap-2">
                                    <span class="text-base leading-none select-none" title="{{ $intel['country'] }} ({{ $intel['asn'] }})">{{ $intel['flag'] }}</span>
                                    <div>
                                        <a href="{{ route('logs.index', ['source_ip' => $alert->source_ip]) }}" class="font-bold text-cyan-400 hover:text-cyan-300 hover:underline flex items-center gap-1" title="Filter related logs in Log Explorer">
                                            <span>{{ $alert->source_ip }}</span>
                                            <svg class="h-3 w-3 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                            </svg>
                                        </a>
                                        <span class="text-[9px] text-zinc-500 uppercase block font-mono">{{ $intel['country'] }} &bull; {{ $intel['threat_tag'] }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Triggered Time -->
                            <td class="whitespace-nowrap px-4 py-3.5 text-zinc-300">
                                <span>{{ $alert->triggered_at?->diffForHumans() ?? 'recently' }}</span>
                                <span class="block text-[10px] text-zinc-500">{{ $alert->triggered_at?->format('Y-m-d H:i:s') }} UTC</span>
                            </td>

                            <!-- Status -->
                            <td class="whitespace-nowrap px-4 py-3.5">
                                @if($alert->status === 'open')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-rose-950/70 px-2.5 py-0.5 text-[11px] font-bold text-rose-300 border border-rose-800">
                                        <span class="h-1.5 w-1.5 rounded-full bg-rose-400 animate-pulse"></span> OPEN
                                    </span>
                                @elseif($alert->status === 'acknowledged')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-950/70 px-2.5 py-0.5 text-[11px] font-bold text-amber-300 border border-amber-800">
                                        ACKNOWLEDGED
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-950/70 px-2.5 py-0.5 text-[11px] font-bold text-emerald-300 border border-emerald-800">
                                        RESOLVED
                                    </span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="whitespace-nowrap px-4 py-3.5 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <!-- Detail Investigate Modal Trigger -->
                                    <button type="button" @click="investigate({{ json_encode([
                                        'id' => $alert->id,
                                        'rule_name' => $alert->detectionRule->name ?? 'Detection Rule',
                                        'condition' => $alert->detectionRule->condition_type ?? 'N/A',
                                        'threshold' => $alert->detectionRule->threshold ?? 1,
                                        'window' => $alert->detectionRule->time_window_minutes ?? 1,
                                        'source_ip' => $alert->source_ip,
                                        'severity' => $alert->severity,
                                        'status' => $alert->status,
                                        'triggered_at' => $alert->triggered_at?->format('Y-m-d H:i:s') . ' UTC (' . $alert->triggered_at?->diffForHumans() . ')',
                                    ]) }})" class="rounded border border-zinc-700 bg-zinc-800 px-2 py-1 text-[11px] font-semibold text-zinc-200 hover:border-zinc-500 hover:bg-zinc-700 hover:text-white transition-colors">
                                        DETAILS
                                    </button>

                                    <!-- Formal Incident Dossier Report Link -->
                                    <a href="{{ route('alerts.report', $alert) }}" target="_blank" class="rounded border border-cyan-800/80 bg-cyan-950/60 px-2 py-1 text-[11px] font-semibold text-cyan-300 hover:bg-cyan-900 transition-colors" title="Export Printable Incident Dossier">
                                        REPORT
                                    </a>

                                    <!-- Quick Status Toggle Forms -->
                                    @if($alert->status === 'open')
                                        <form method="POST" action="{{ route('alerts.update-status', $alert) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="acknowledged">
                                            <button type="submit" class="rounded border border-amber-800/80 bg-amber-950/60 px-2.5 py-1 text-[11px] font-semibold text-amber-300 hover:bg-amber-900 transition-colors" title="Acknowledge Incident">
                                                ACK
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('alerts.update-status', $alert) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="resolved">
                                            <button type="submit" class="rounded border border-emerald-800/80 bg-emerald-950/60 px-2.5 py-1 text-[11px] font-semibold text-emerald-300 hover:bg-emerald-900 transition-colors" title="Resolve Incident">
                                                RESOLVE
                                            </button>
                                        </form>
                                    @elseif($alert->status === 'acknowledged')
                                        <form method="POST" action="{{ route('alerts.update-status', $alert) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="resolved">
                                            <button type="submit" class="rounded border border-emerald-800/80 bg-emerald-950/60 px-2.5 py-1 text-[11px] font-semibold text-emerald-300 hover:bg-emerald-900 transition-colors" title="Resolve Incident">
                                                RESOLVE
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('alerts.update-status', $alert) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="open">
                                            <button type="submit" class="rounded border border-zinc-700 bg-zinc-800 px-2.5 py-1 text-[11px] font-semibold text-zinc-400 hover:text-white transition-colors" title="Reopen Incident">
                                                REOPEN
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Quick Containment Block IP Button -->
                                    <form method="POST" action="{{ route('containment.block') }}" class="inline" onsubmit="return confirm('Enforce SOAR containment and quarantine {{ $alert->source_ip }} for 24h?');">
                                        @csrf
                                        <input type="hidden" name="ip_address" value="{{ $alert->source_ip }}">
                                        <input type="hidden" name="reason" value="Containment response to #SEC-{{ str_pad($alert->id, 4, '0', STR_PAD_LEFT) }} ({{ $alert->detectionRule->name ?? 'Threat alert' }})">
                                        <input type="hidden" name="duration" value="24h">
                                        <input type="hidden" name="alert_id" value="{{ $alert->id }}">
                                        <button type="submit" class="rounded border border-rose-800/80 bg-rose-950/80 px-2.5 py-1 text-[11px] font-bold text-rose-300 hover:bg-rose-900 transition-colors" title="Instant Quarantine / Block IP">
                                            BLOCK IP
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-16 text-center font-mono text-zinc-500">
                                No security alerts found matching the active filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($alerts->hasPages())
            <div class="border-t border-zinc-800 px-5 py-4 bg-zinc-950">
                {{ $alerts->links() }}
            </div>
        @endif
    </div>

    <!-- Alert Investigation Modal -->
    <div x-cloak x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" @click.self="modalOpen = false" @keydown.escape.window="modalOpen = false">
        <div class="w-full max-w-xl rounded-lg border border-zinc-800 bg-zinc-950 shadow-2xl overflow-hidden font-mono text-xs" x-show="modalOpen" x-transition>
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-zinc-800 px-5 py-4 bg-zinc-900/60">
                <div class="flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                    <h3 class="font-bold text-zinc-100 uppercase tracking-wide">
                        Incident Dossier: <span x-text="'#SEC-' + String(selectedAlert?.id).padStart(4, '0')"></span>
                    </h3>
                </div>
                <button type="button" @click="modalOpen = false" class="text-zinc-400 hover:text-white text-lg">&times;</button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 space-y-5">
                <div>
                    <span class="text-zinc-500 uppercase text-[10px] block mb-1">Triggered Detection Rule</span>
                    <p class="text-sm font-bold text-zinc-100" x-text="selectedAlert?.rule_name"></p>
                </div>

                <div class="grid grid-cols-2 gap-4 border-y border-zinc-800/80 py-4">
                    <div>
                        <span class="text-zinc-500 uppercase text-[10px] block">Source Malicious IP</span>
                        <span class="font-bold text-cyan-400 text-sm" x-text="selectedAlert?.source_ip"></span>
                    </div>
                    <div>
                        <span class="text-zinc-500 uppercase text-[10px] block">Severity Rating</span>
                        <span class="font-bold uppercase text-sm" :class="{
                            'text-rose-400': selectedAlert?.severity === 'critical',
                            'text-orange-400': selectedAlert?.severity === 'high',
                            'text-amber-400': selectedAlert?.severity === 'medium',
                            'text-zinc-400': selectedAlert?.severity === 'info'
                        }" x-text="selectedAlert?.severity"></span>
                    </div>
                    <div>
                        <span class="text-zinc-500 uppercase text-[10px] block">Condition Model</span>
                        <span class="text-zinc-300" x-text="selectedAlert?.condition"></span>
                    </div>
                    <div>
                        <span class="text-zinc-500 uppercase text-[10px] block">Correlation Window</span>
                        <span class="text-zinc-300" x-text="selectedAlert?.threshold + ' events in ' + selectedAlert?.window + ' mins'"></span>
                    </div>
                    <div class="col-span-2">
                        <span class="text-zinc-500 uppercase text-[10px] block">Timestamp Triggered</span>
                        <span class="text-zinc-300" x-text="selectedAlert?.triggered_at"></span>
                    </div>
                </div>

                <!-- Deep Dive & Containment Actions -->
                <div class="rounded-md bg-zinc-900 p-3.5 border border-zinc-800 text-zinc-400 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <span class="block font-semibold text-zinc-200">Active Defense & Telemetry</span>
                        <span class="text-[10px]">Analyze events or quarantine this attacker</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <a :href="'/alerts/' + selectedAlert?.id + '/report'" target="_blank" class="rounded bg-cyan-700 hover:bg-cyan-600 px-3 py-1.5 text-xs font-semibold text-white transition-colors">
                            🖨️ DOSSIER
                        </a>
                        <a :href="'{{ route('logs.index') }}?source_ip=' + selectedAlert?.source_ip" class="rounded bg-zinc-800 border border-zinc-700 px-3 py-1.5 text-xs font-semibold text-zinc-300 hover:bg-zinc-700 transition-colors">
                            VIEW LOGS &rarr;
                        </a>
                        <form method="POST" action="{{ route('containment.block') }}" class="inline">
                            @csrf
                            <input type="hidden" name="ip_address" :value="selectedAlert?.source_ip">
                            <input type="hidden" name="reason" :value="'SOAR Quarantine against rule: ' + selectedAlert?.rule_name">
                            <input type="hidden" name="duration" value="24h">
                            <input type="hidden" name="alert_id" :value="selectedAlert?.id">
                            <button type="submit" class="rounded bg-rose-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-rose-500 transition-colors shadow-sm shadow-rose-600/30">
                                🛡️ BLOCK IP
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-end gap-2 border-t border-zinc-800 px-5 py-3 bg-zinc-900/40">
                <button type="button" @click="modalOpen = false" class="rounded border border-zinc-700 bg-zinc-800 px-4 py-1.5 text-xs text-zinc-300 hover:text-white transition-colors">
                    CLOSE
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
