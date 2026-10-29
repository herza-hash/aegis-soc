@extends('layouts.app', ['title' => 'Security Operations Center | Aegis SOC'])

@section('content')
<div class="space-y-6">
    <!-- Header / Operation Status Bar -->
    <div class="flex flex-col gap-4 border-b border-zinc-800/80 pb-6 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2 font-mono text-[11px] uppercase tracking-wider text-cyan-400">
                <span class="inline-block h-1.5 w-1.5 rounded-full bg-cyan-400 shadow-[0_0_8px_#22d3ee]"></span>
                <span>AEGIS / OPERATIONS INTELLIGENCE</span>
            </div>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-zinc-100 sm:text-3xl">Security Overview & Telemetry</h1>
        </div>
        
        <div class="flex flex-wrap items-center gap-3">
            <!-- Active Monitoring Pill -->
            <div class="flex items-center gap-2 rounded-md border border-emerald-500/20 bg-emerald-950/30 px-3 py-1.5 font-mono text-xs text-emerald-400 backdrop-blur-sm">
                <span class="relative flex h-2 w-2">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                </span>
                <span>MONITORING ACTIVE</span>
            </div>

            <!-- UTC Timestamp -->
            <div class="rounded-md border border-zinc-800 bg-zinc-900/80 px-3 py-1.5 font-mono text-xs text-zinc-400">
                <span class="text-zinc-600">UTC</span> {{ now()->utc()->format('Y-m-d H:i:s') }}
            </div>

            <!-- Quick Refresh Button -->
            <button onclick="window.location.reload()" type="button" class="inline-flex items-center gap-1.5 rounded-md border border-zinc-700 bg-zinc-800/80 px-3 py-1.5 font-mono text-xs text-zinc-200 transition-colors hover:border-zinc-500 hover:bg-zinc-700 hover:text-white">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span>SYNC</span>
            </button>
        </div>
    </div>

    <!-- 1. Top KPI Metrics Cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Card 1: Critical & Open Alerts -->
        <div class="relative overflow-hidden rounded-lg border border-rose-900/40 bg-gradient-to-b from-rose-950/20 to-zinc-950/80 p-5 shadow-lg shadow-rose-950/10">
            <div class="flex items-center justify-between">
                <p class="font-mono text-xs uppercase tracking-wider text-rose-400">Pending Alerts</p>
                <span class="rounded bg-rose-950/80 px-2 py-0.5 font-mono text-[10px] font-semibold uppercase text-rose-300 border border-rose-800/60">
                    {{ $metrics['critical_alerts'] }} Critical
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold tracking-tight text-rose-100 font-mono">{{ $metrics['open_alerts'] }}</span>
                <span class="text-xs text-zinc-400 font-mono">Open Incidents</span>
            </div>
            <div class="mt-3 flex items-center justify-between border-t border-rose-900/30 pt-3 text-[11px] font-mono">
                <span class="text-rose-400/90 flex items-center gap-1">
                    <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span> Requires Triage
                </span>
                <a href="{{ route('alerts.index') }}" class="text-rose-400 hover:text-rose-300 underline underline-offset-2">Review &rarr;</a>
            </div>
        </div>

        <!-- Card 2: 24h Ingested Events -->
        <div class="relative overflow-hidden rounded-lg border border-cyan-900/40 bg-gradient-to-b from-cyan-950/20 to-zinc-950/80 p-5 shadow-lg shadow-cyan-950/10">
            <div class="flex items-center justify-between">
                <p class="font-mono text-xs uppercase tracking-wider text-cyan-400">Events (24h)</p>
                <span class="rounded bg-cyan-950/80 px-2 py-0.5 font-mono text-[10px] font-semibold uppercase text-cyan-300 border border-cyan-800/60">
                    Active Flow
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold tracking-tight text-cyan-100 font-mono">{{ number_format($metrics['events_24h']) }}</span>
                <span class="text-xs text-zinc-400 font-mono">Ingested</span>
            </div>
            <div class="mt-3 flex items-center justify-between border-t border-cyan-900/30 pt-3 text-[11px] font-mono text-zinc-400">
                <span>Total Historical:</span>
                <span class="text-cyan-300">{{ number_format($metrics['total_events']) }}</span>
            </div>
        </div>

        <!-- Card 3: Active Log Sources -->
        <div class="relative overflow-hidden rounded-lg border border-emerald-900/40 bg-gradient-to-b from-emerald-950/20 to-zinc-950/80 p-5 shadow-lg shadow-emerald-950/10">
            <div class="flex items-center justify-between">
                <p class="font-mono text-xs uppercase tracking-wider text-emerald-400">Log Sources</p>
                <span class="rounded bg-emerald-950/80 px-2 py-0.5 font-mono text-[10px] font-semibold uppercase text-emerald-300 border border-emerald-800/60">
                    {{ round(($metrics['active_sources'] / max(1, $metrics['total_sources'])) * 100) }}% Online
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold tracking-tight text-emerald-100 font-mono">{{ $metrics['active_sources'] }}</span>
                <span class="text-xs text-zinc-400 font-mono">/ {{ $metrics['total_sources'] }} Connected</span>
            </div>
            <div class="mt-3 flex items-center justify-between border-t border-emerald-900/30 pt-3 text-[11px] font-mono">
                <span class="text-emerald-400">Healthy Pipelines</span>
                <a href="{{ route('sources.index') }}" class="text-emerald-400 hover:text-emerald-300 underline underline-offset-2">Manage &rarr;</a>
            </div>
        </div>

        <!-- Card 4: Unique Malicious IPs -->
        <div class="relative overflow-hidden rounded-lg border border-amber-900/40 bg-gradient-to-b from-amber-950/20 to-zinc-950/80 p-5 shadow-lg shadow-amber-950/10">
            <div class="flex items-center justify-between">
                <p class="font-mono text-xs uppercase tracking-wider text-amber-400">Threat Actors</p>
                <span class="rounded bg-amber-950/80 px-2 py-0.5 font-mono text-[10px] font-semibold uppercase text-amber-300 border border-amber-800/60">
                    High Risk
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold tracking-tight text-amber-100 font-mono">{{ $metrics['unique_attackers'] }}</span>
                <span class="text-xs text-zinc-400 font-mono">Unique IPs</span>
            </div>
            <div class="mt-3 flex items-center justify-between border-t border-amber-900/30 pt-3 text-[11px] font-mono text-zinc-400">
                <span>Flagged Severities:</span>
                <span class="text-amber-300 font-semibold">High & Critical</span>
            </div>
        </div>
    </div>

    <!-- 2. Charts Section -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Ingestion Velocity Timeline (2 Cols) -->
        <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-5 lg:col-span-2">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-2 border-b border-zinc-800/70 pb-3">
                <div>
                    <h2 class="text-sm font-semibold tracking-wide text-zinc-100">Telemetry Velocity & Threat Peaks</h2>
                    <p class="font-mono text-xs text-zinc-500">Hourly events flow compared to flagged threat anomalies (Past 12 Hours)</p>
                </div>
                <div class="flex items-center gap-4 font-mono text-xs">
                    <span class="flex items-center gap-1.5 text-zinc-300">
                        <span class="h-2.5 w-2.5 rounded-sm bg-cyan-400"></span> Total Logs
                    </span>
                    <span class="flex items-center gap-1.5 text-rose-400">
                        <span class="h-2.5 w-2.5 rounded-sm bg-rose-500"></span> Threats
                    </span>
                </div>
            </div>
            <div class="relative h-64 w-full">
                <canvas id="velocityChart"></canvas>
            </div>
        </div>

        <!-- Severity Breakdown Donut (1 Col) -->
        <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-5">
            <div class="mb-4 border-b border-zinc-800/70 pb-3">
                <h2 class="text-sm font-semibold tracking-wide text-zinc-100">Threat Severity Distribution</h2>
                <p class="font-mono text-xs text-zinc-500">Normalized log classification hierarchy</p>
            </div>
            <div class="relative flex h-52 items-center justify-center">
                <canvas id="severityDonutChart"></canvas>
            </div>
            <div class="mt-3 grid grid-cols-2 gap-2 font-mono text-[11px] border-t border-zinc-800/70 pt-3">
                <div class="flex items-center justify-between px-2 py-1 bg-zinc-900/60 rounded">
                    <span class="text-rose-400 font-semibold">● Critical</span>
                    <span class="text-zinc-200">{{ $chartData['severities']['critical'] }}</span>
                </div>
                <div class="flex items-center justify-between px-2 py-1 bg-zinc-900/60 rounded">
                    <span class="text-orange-400 font-semibold">● High</span>
                    <span class="text-zinc-200">{{ $chartData['severities']['high'] }}</span>
                </div>
                <div class="flex items-center justify-between px-2 py-1 bg-zinc-900/60 rounded">
                    <span class="text-amber-400 font-semibold">● Medium</span>
                    <span class="text-zinc-200">{{ $chartData['severities']['medium'] }}</span>
                </div>
                <div class="flex items-center justify-between px-2 py-1 bg-zinc-900/60 rounded">
                    <span class="text-cyan-400 font-semibold">● Low/Info</span>
                    <span class="text-zinc-200">{{ $chartData['severities']['low'] + $chartData['severities']['info'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Intelligence Row: Top Threat IPs & Active Incidents -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Top Suspicious Attackers -->
        <div class="rounded-lg border border-zinc-800 bg-zinc-950">
            <div class="flex items-center justify-between border-b border-zinc-800 px-5 py-3.5">
                <div>
                    <h2 class="text-sm font-semibold text-zinc-100">Aggressive External Source IPs</h2>
                    <p class="font-mono text-[11px] text-zinc-500">Top originators of high & critical security events</p>
                </div>
                <span class="rounded bg-zinc-900 px-2 py-0.5 font-mono text-[10px] text-zinc-400 border border-zinc-800">
                    Top 5 Ranked
                </span>
            </div>
            <div class="divide-y divide-zinc-800/70">
                @forelse ($topAttackers as $attacker)
                    <div class="flex items-center justify-between px-5 py-3 text-xs transition-colors hover:bg-zinc-900/40">
                        @php
                            $intel = \App\Services\ThreatIntelService::lookup($attacker->source_ip);
                        @endphp
                        <div class="flex items-center gap-3">
                            <span class="grid h-7 w-7 place-items-center rounded bg-zinc-900 font-mono text-zinc-400 border border-zinc-800 text-[11px]">
                                #{{ $loop->iteration }}
                            </span>
                            <span class="text-base select-none leading-none" title="{{ $intel['country'] }} ({{ $intel['asn'] }})">{{ $intel['flag'] }}</span>
                            <div>
                                <div class="flex items-center gap-1.5">
                                    <span class="font-mono font-semibold text-zinc-200">{{ $attacker->source_ip }}</span>
                                    <span class="rounded bg-zinc-900 border border-zinc-800 px-1 py-0.2 text-[9px] font-bold text-zinc-400 uppercase font-mono">{{ $intel['country_code'] }}</span>
                                </div>
                                <p class="font-mono text-[10px] text-zinc-500">{{ $intel['country'] }} &bull; Last seen {{ \Carbon\Carbon::parse($attacker->last_seen)->diffForHumans() }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="text-right">
                                <span class="font-mono font-bold text-rose-400">{{ $attacker->total_events }}</span>
                                <span class="block font-mono text-[10px] text-zinc-500">attacks</span>
                            </div>
                            <form method="POST" action="{{ route('containment.block') }}" class="inline" onsubmit="return confirm('Quarantine {{ $attacker->source_ip }} for 24h?');">
                                @csrf
                                <input type="hidden" name="ip_address" value="{{ $attacker->source_ip }}">
                                <input type="hidden" name="reason" value="Quarantine from Dashboard: Ranked as Top Aggressive Source IP">
                                <input type="hidden" name="duration" value="24h">
                                <button type="submit" class="rounded bg-rose-950/70 hover:bg-rose-900 border border-rose-800/60 px-2.5 py-1 font-mono text-[10px] font-bold text-rose-300 transition-colors shadow-sm" title="Quarantine Attacker IP">
                                    BLOCK
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center font-mono text-xs text-zinc-500">
                        No malicious IPs recorded yet.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Triggered Security Alerts -->
        <div class="rounded-lg border border-zinc-800 bg-zinc-950">
            <div class="flex items-center justify-between border-b border-zinc-800 px-5 py-3.5">
                <div>
                    <h2 class="text-sm font-semibold text-zinc-100">Live Incident Triage Queue</h2>
                    <p class="font-mono text-[11px] text-zinc-500">Triggered alerts from detection rule correlation</p>
                </div>
                <a href="{{ route('alerts.index') }}" class="font-mono text-xs text-cyan-400 hover:text-cyan-300">
                    VIEW ALL &rarr;
                </a>
            </div>
            <div class="divide-y divide-zinc-800/70">
                @forelse ($recentAlerts as $alert)
                    <div class="flex items-start justify-between gap-3 px-5 py-3 text-xs transition-colors hover:bg-zinc-900/40">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                @if($alert->severity === 'critical')
                                    <span class="inline-flex items-center rounded bg-rose-950 px-1.5 py-0.5 font-mono text-[10px] font-bold uppercase text-rose-300 border border-rose-800">CRITICAL</span>
                                @elseif($alert->severity === 'high')
                                    <span class="inline-flex items-center rounded bg-orange-950 px-1.5 py-0.5 font-mono text-[10px] font-bold uppercase text-orange-300 border border-orange-800">HIGH</span>
                                @elseif($alert->severity === 'medium')
                                    <span class="inline-flex items-center rounded bg-amber-950 px-1.5 py-0.5 font-mono text-[10px] font-bold uppercase text-amber-300 border border-amber-800">MEDIUM</span>
                                @else
                                    <span class="inline-flex items-center rounded bg-zinc-900 px-1.5 py-0.5 font-mono text-[10px] font-bold uppercase text-zinc-400 border border-zinc-800">INFO</span>
                                @endif
                                <span class="font-medium text-zinc-200">{{ $alert->detectionRule->name ?? 'Correlated Security Alert' }}</span>
                            </div>
                            <div class="flex items-center gap-2 font-mono text-[10px] text-zinc-400">
                                <span>Targeted Source: <strong class="text-zinc-200">{{ $alert->source_ip }}</strong></span>
                                <span>&bull;</span>
                                <span>{{ $alert->triggered_at?->diffForHumans() ?? 'recently' }}</span>
                            </div>
                        </div>
                        <div>
                            @if($alert->status === 'open')
                                <span class="inline-flex items-center gap-1 rounded-full bg-rose-950/60 px-2 py-0.5 font-mono text-[10px] font-semibold text-rose-400 border border-rose-800/60">
                                    <span class="h-1.5 w-1.5 rounded-full bg-rose-400 animate-pulse"></span> OPEN
                                </span>
                            @elseif($alert->status === 'acknowledged')
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-950/60 px-2 py-0.5 font-mono text-[10px] font-semibold text-amber-400 border border-amber-800/60">
                                    ACK
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-950/60 px-2 py-0.5 font-mono text-[10px] font-semibold text-emerald-400 border border-emerald-800/60">
                                    RESOLVED
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center font-mono text-xs text-zinc-500">
                        No security alerts triggered. System operating normally.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- 4. Live Normalized Event Telemetry Feed -->
    <section class="rounded-lg border border-zinc-800 bg-zinc-950" aria-labelledby="event-stream-title">
        <div class="flex min-h-12 flex-wrap items-center justify-between gap-3 border-b border-zinc-800 px-5 py-3">
            <div class="flex items-center gap-2">
                <span class="h-2 w-2 rounded-full bg-cyan-400 animate-pulse"></span>
                <h2 id="event-stream-title" class="text-sm font-semibold text-zinc-200">Recent Normalized Events Telemetry</h2>
            </div>
            <div class="flex items-center gap-3">
                <span class="font-mono text-xs text-zinc-500">Displaying {{ $recentEvents->count() }} real-time events</span>
                <a href="{{ route('logs.index') }}" class="inline-flex items-center gap-1 rounded bg-zinc-900 px-2.5 py-1 font-mono text-xs font-semibold text-cyan-400 hover:bg-zinc-800 hover:text-cyan-300 border border-zinc-800 transition-colors">
                    <span>EXPLORER</span> &rarr;
                </a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-left text-xs">
                <thead class="border-b border-zinc-800 bg-zinc-900/60 font-mono uppercase text-zinc-400">
                    <tr>
                        <th class="px-4 py-3 font-normal">Timestamp (UTC)</th>
                        <th class="px-4 py-3 font-normal">Source IP</th>
                        <th class="px-4 py-3 font-normal">Destination</th>
                        <th class="px-4 py-3 font-normal">Category</th>
                        <th class="px-4 py-3 font-normal">Action & Payload</th>
                        <th class="px-4 py-3 font-normal">Outcome</th>
                        <th class="px-4 py-3 font-normal">Severity</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/60 font-mono">
                    @forelse ($recentEvents as $event)
                        <tr class="transition-colors hover:bg-zinc-900/50">
                            <!-- Timestamp -->
                            <td class="whitespace-nowrap px-4 py-3 text-zinc-400">
                                {{ $event->timestamp ? $event->timestamp->format('H:i:s') : 'N/A' }}
                                <span class="block text-[10px] text-zinc-600">{{ $event->timestamp ? $event->timestamp->format('Y-m-d') : '' }}</span>
                            </td>

                            <!-- Source IP with Geolocation -->
                            <td class="whitespace-nowrap px-4 py-3">
                                @if($event->source_ip)
                                    @php
                                        $intel = \App\Services\ThreatIntelService::lookup($event->source_ip);
                                    @endphp
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-sm select-none" title="{{ $intel['country'] }}">{{ $intel['flag'] }}</span>
                                        <div>
                                            <span class="font-semibold text-zinc-200">{{ $event->source_ip }}</span>
                                            @if($event->username)
                                                <span class="block text-[10px] text-zinc-500">user: {{ $event->username }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <span class="text-zinc-600">Local / Gateway</span>
                                @endif
                            </td>

                            <!-- Destination -->
                            <td class="whitespace-nowrap px-4 py-3 text-zinc-400">
                                {{ $event->destination_ip ?? ($event->rawLog->logSource->name ?? 'Edge Ingest') }}
                            </td>

                            <!-- Category -->
                            <td class="whitespace-nowrap px-4 py-3">
                                <span class="rounded bg-zinc-900 px-2 py-0.5 text-[10px] uppercase font-bold text-zinc-300 border border-zinc-800">
                                    {{ $event->event_category }}
                                </span>
                            </td>

                            <!-- Action & Message -->
                            <td class="px-4 py-3 text-zinc-300 max-w-xs truncate" title="{{ $event->event_action }}">
                                <span class="font-semibold text-cyan-300">{{ $event->event_action }}</span>
                                @if(!empty($event->parsed_data['raw_message']))
                                    <span class="block truncate text-[10px] text-zinc-500">{{ Str::limit($event->parsed_data['raw_message'], 60) }}</span>
                                @endif
                            </td>

                            <!-- Outcome -->
                            <td class="whitespace-nowrap px-4 py-3">
                                @if($event->event_outcome === 'success')
                                    <span class="rounded bg-emerald-950/60 px-1.5 py-0.5 text-[10px] font-bold text-emerald-400 border border-emerald-800/40">SUCCESS</span>
                                @elseif($event->event_outcome === 'failure')
                                    <span class="rounded bg-rose-950/60 px-1.5 py-0.5 text-[10px] font-bold text-rose-400 border border-rose-800/40">FAILURE</span>
                                @elseif($event->event_outcome === 'blocked')
                                    <span class="rounded bg-purple-950/60 px-1.5 py-0.5 text-[10px] font-bold text-purple-300 border border-purple-800/40">BLOCKED</span>
                                @else
                                    <span class="rounded bg-zinc-900 px-1.5 py-0.5 text-[10px] text-zinc-400 border border-zinc-800">{{ strtoupper($event->event_outcome) }}</span>
                                @endif
                            </td>

                            <!-- Severity -->
                            <td class="whitespace-nowrap px-4 py-3">
                                @if($event->severity === 'critical')
                                    <span class="inline-flex items-center gap-1 font-bold text-rose-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-rose-500 animate-ping"></span> CRITICAL
                                    </span>
                                @elseif($event->severity === 'high')
                                    <span class="font-bold text-orange-400">HIGH</span>
                                @elseif($event->severity === 'medium')
                                    <span class="font-semibold text-amber-400">MEDIUM</span>
                                @elseif($event->severity === 'low')
                                    <span class="text-cyan-400">LOW</span>
                                @else
                                    <span class="text-zinc-500">INFO</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center font-mono text-zinc-500">No events have been ingested.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

<!-- Chart.js Initialization Script -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof window.Chart === 'undefined') {
        console.error('Chart.js not loaded');
        return;
    }

    const chartData = @json($chartData);

    // 1. Velocity Timeline Chart
    const velocityCtx = document.getElementById('velocityChart');
    if (velocityCtx) {
        new window.Chart(velocityCtx, {
            type: 'line',
            data: {
                labels: chartData.hours,
                datasets: [
                    {
                        label: 'Total Logs',
                        data: chartData.totalEvents,
                        borderColor: '#22d3ee', // Cyan
                        backgroundColor: 'rgba(34, 211, 238, 0.08)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.35,
                        pointRadius: 3,
                        pointBackgroundColor: '#22d3ee',
                    },
                    {
                        label: 'Flagged Threats',
                        data: chartData.threatEvents,
                        borderColor: '#f43f5e', // Rose
                        backgroundColor: 'rgba(244, 63, 94, 0.15)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.35,
                        pointRadius: 4,
                        pointBackgroundColor: '#f43f5e',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#09090b',
                        borderColor: '#27272a',
                        borderWidth: 1,
                        titleColor: '#e4e4e7',
                        bodyColor: '#a1a1aa',
                        titleFont: { family: 'monospace', size: 12 },
                        bodyFont: { family: 'monospace', size: 11 },
                        padding: 10
                    }
                },
                scales: {
                    x: {
                        grid: {
                            color: 'rgba(39, 39, 42, 0.5)'
                        },
                        ticks: {
                            color: '#71717a',
                            font: { family: 'monospace', size: 10 }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(39, 39, 42, 0.5)'
                        },
                        ticks: {
                            color: '#71717a',
                            font: { family: 'monospace', size: 10 }
                        }
                    }
                }
            }
        });
    }

    // 2. Severity Donut Chart
    const severityCtx = document.getElementById('severityDonutChart');
    if (severityCtx) {
        new window.Chart(severityCtx, {
            type: 'doughnut',
            data: {
                labels: ['Critical', 'High', 'Medium', 'Low', 'Info'],
                datasets: [{
                    data: [
                        chartData.severities.critical,
                        chartData.severities.high,
                        chartData.severities.medium,
                        chartData.severities.low,
                        chartData.severities.info
                    ],
                    backgroundColor: [
                        '#f43f5e', // Rose 500
                        '#f97316', // Orange 500
                        '#eab308', // Amber 500
                        '#06b6d4', // Cyan 500
                        '#64748b'  // Slate 500
                    ],
                    borderColor: '#09090b',
                    borderWidth: 3,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#09090b',
                        borderColor: '#27272a',
                        borderWidth: 1,
                        titleColor: '#e4e4e7',
                        bodyColor: '#a1a1aa',
                        titleFont: { family: 'monospace', size: 12 },
                        bodyFont: { family: 'monospace', size: 11 },
                        padding: 10
                    }
                }
            }
        });
    }
});
</script>
@endsection