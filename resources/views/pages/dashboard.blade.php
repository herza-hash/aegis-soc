@extends('layouts.app', ['title' => __('Security Operations Center | Aegis SOC')])

@section('content')
<div x-data="dashboardLoader()" x-init="initDashboard()" class="space-y-6">
    <!-- Header / Operation Status Bar -->
    <div class="flex flex-col gap-4 border-b border-zinc-800/80 pb-6 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2 font-mono text-[11px] uppercase tracking-wider text-cyan-400">
                <span class="inline-block h-1.5 w-1.5 rounded-full bg-cyan-400"></span>
                <span><i class="fa-solid fa-network-wired mr-1 text-cyan-400"></i>{{ __('AEGIS / OPERATIONS INTELLIGENCE') }}</span>
            </div>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-zinc-100 sm:text-3xl">{{ __('Security Overview & Telemetry') }}</h1>
        </div>
        
        <div class="flex flex-wrap items-center gap-3">
            <!-- Active Monitoring Pill -->
            <div class="flex items-center gap-2 rounded-md border border-emerald-500/20 bg-emerald-950/30 px-3 py-1.5 font-mono text-xs text-emerald-400 backdrop-blur-sm">
                <template x-if="loading">
                    <span class="flex items-center gap-2 text-cyan-400">
                        <i class="fa-solid fa-spinner fa-spin"></i>
                        <span>{{ __('SYNCING TELEMETRY') }}</span>
                    </span>
                </template>
                <template x-if="!loading">
                    <span class="flex items-center gap-2 text-emerald-400">
                        <span class="relative flex h-2 w-2">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                        </span>
                        <span>{{ __('MONITORING ACTIVE') }}</span>
                    </span>
                </template>
            </div>

            <!-- UTC Timestamp -->
            <div class="rounded-md border border-zinc-800 bg-zinc-900/80 px-3 py-1.5 font-mono text-xs text-zinc-400">
                <span class="text-zinc-600"><i class="fa-regular fa-clock mr-1"></i>UTC</span> 
                <span x-text="serverTime || '{{ now()->utc()->format('Y-m-d H:i:s') }}'"></span>
            </div>

            <!-- Quick Refresh Button -->
            <button @click="fetchDashboard(true)" type="button" :disabled="loading" class="inline-flex items-center gap-1.5 rounded-md border border-zinc-700 bg-zinc-800/80 px-3 py-1.5 font-mono text-xs text-zinc-200 transition-colors hover:border-zinc-500 hover:bg-zinc-700 hover:text-white disabled:opacity-50">
                <i class="fa-solid fa-arrows-rotate text-cyan-400" :class="{ 'fa-spin': loading }"></i>
                <span>{{ __('SYNC') }}</span>
            </button>
        </div>
    </div>

    <!-- 1. Top KPI Metrics Cards (Lazy Loaded) -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Card 1: Critical & Open Alerts -->
        <div class="relative overflow-hidden rounded-lg border border-rose-900/40 bg-gradient-to-b from-rose-950/20 to-zinc-950/80 p-5">
            <div class="flex items-center justify-between">
                <p class="font-mono text-xs uppercase tracking-wider text-rose-400">
                    <i class="fa-solid fa-triangle-exclamation mr-1.5 text-rose-400"></i>{{ __('Pending Alerts') }}
                </p>
                <template x-if="loading">
                    <div class="h-4 w-16 animate-pulse rounded bg-rose-950/80"></div>
                </template>
                <template x-if="!loading">
                    <span class="rounded bg-rose-950/80 px-2 py-0.5 font-mono text-[10px] font-semibold uppercase text-rose-300 border border-rose-800/60" x-text="(metrics.critical_alerts ?? 0) + ' {{ __('Critical') }}'"></span>
                </template>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <template x-if="loading">
                    <div class="h-9 w-20 animate-pulse rounded bg-rose-900/30"></div>
                </template>
                <template x-if="!loading">
                    <span class="text-3xl font-extrabold tracking-tight text-rose-100 font-mono" x-text="metrics.open_alerts ?? 0"></span>
                </template>
                <span class="text-xs text-zinc-400 font-mono">{{ __('Open Incidents') }}</span>
            </div>
            <div class="mt-3 flex items-center justify-between border-t border-rose-900/30 pt-3 text-[11px] font-mono">
                <span class="text-rose-400/90 flex items-center gap-1">
                    <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span> {{ __('Requires Triage') }}
                </span>
                <a href="{{ route('alerts.index') }}" class="text-rose-400 hover:text-rose-300 underline underline-offset-2">
                    {{ __('Review') }} <i class="fa-solid fa-arrow-right text-[10px] ml-0.5"></i>
                </a>
            </div>
        </div>

        <!-- Card 2: 24h Ingested Events -->
        <div class="relative overflow-hidden rounded-lg border border-cyan-900/40 bg-gradient-to-b from-cyan-950/20 to-zinc-950/80 p-5">
            <div class="flex items-center justify-between">
                <p class="font-mono text-xs uppercase tracking-wider text-cyan-400">
                    <i class="fa-solid fa-bolt mr-1.5 text-cyan-400"></i>{{ __('Events (24h)') }}
                </p>
                <span class="rounded bg-cyan-950/80 px-2 py-0.5 font-mono text-[10px] font-semibold uppercase text-cyan-300 border border-cyan-800/60">
                    {{ __('Active Flow') }}
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <template x-if="loading">
                    <div class="h-9 w-28 animate-pulse rounded bg-cyan-900/30"></div>
                </template>
                <template x-if="!loading">
                    <span class="text-3xl font-extrabold tracking-tight text-cyan-100 font-mono" x-text="formatNumber(metrics.events_24h ?? 0)"></span>
                </template>
                <span class="text-xs text-zinc-400 font-mono">{{ __('Ingested') }}</span>
            </div>
            <div class="mt-3 flex items-center justify-between border-t border-cyan-900/30 pt-3 text-[11px] font-mono text-zinc-400">
                <span>{{ __('Total Historical:') }}</span>
                <template x-if="loading">
                    <div class="h-3 w-16 animate-pulse rounded bg-cyan-900/30"></div>
                </template>
                <template x-if="!loading">
                    <span class="text-cyan-300" x-text="formatNumber(metrics.total_events ?? 0)"></span>
                </template>
            </div>
        </div>

        <!-- Card 3: Active Log Sources -->
        <div class="relative overflow-hidden rounded-lg border border-emerald-900/40 bg-gradient-to-b from-emerald-950/20 to-zinc-950/80 p-5">
            <div class="flex items-center justify-between">
                <p class="font-mono text-xs uppercase tracking-wider text-emerald-400">
                    <i class="fa-solid fa-server mr-1.5 text-emerald-400"></i>{{ __('Log Sources') }}
                </p>
                <template x-if="loading">
                    <div class="h-4 w-16 animate-pulse rounded bg-emerald-950/80"></div>
                </template>
                <template x-if="!loading">
                    <span class="rounded bg-emerald-950/80 px-2 py-0.5 font-mono text-[10px] font-semibold uppercase text-emerald-300 border border-emerald-800/60" x-text="(metrics.sources_online_percent ?? 0) + '% ' + '{{ __('Online') }}'"></span>
                </template>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <template x-if="loading">
                    <div class="h-9 w-20 animate-pulse rounded bg-emerald-900/30"></div>
                </template>
                <template x-if="!loading">
                    <span class="text-3xl font-extrabold tracking-tight text-emerald-100 font-mono" x-text="metrics.active_sources ?? 0"></span>
                </template>
                <span class="text-xs text-zinc-400 font-mono" x-text="'/ ' + (metrics.total_sources ?? 0) + ' {{ __('Connected') }}'"></span>
            </div>
            <div class="mt-3 flex items-center justify-between border-t border-emerald-900/30 pt-3 text-[11px] font-mono">
                <span class="text-emerald-400">{{ __('Healthy Pipelines') }}</span>
                <a href="{{ route('sources.index') }}" class="text-emerald-400 hover:text-emerald-300 underline underline-offset-2">
                    {{ __('Manage') }} <i class="fa-solid fa-arrow-right text-[10px] ml-0.5"></i>
                </a>
            </div>
        </div>

        <!-- Card 4: Unique Malicious IPs -->
        <div class="relative overflow-hidden rounded-lg border border-amber-900/40 bg-gradient-to-b from-amber-950/20 to-zinc-950/80 p-5">
            <div class="flex items-center justify-between">
                <p class="font-mono text-xs uppercase tracking-wider text-amber-400">
                    <i class="fa-solid fa-user-secret mr-1.5 text-amber-400"></i>{{ __('Threat Actors') }}
                </p>
                <span class="rounded bg-amber-950/80 px-2 py-0.5 font-mono text-[10px] font-semibold uppercase text-amber-300 border border-amber-800/60">
                    {{ __('High Risk') }}
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <template x-if="loading">
                    <div class="h-9 w-16 animate-pulse rounded bg-amber-900/30"></div>
                </template>
                <template x-if="!loading">
                    <span class="text-3xl font-extrabold tracking-tight text-amber-100 font-mono" x-text="metrics.unique_attackers ?? 0"></span>
                </template>
                <span class="text-xs text-zinc-400 font-mono">{{ __('Unique IPs') }}</span>
            </div>
            <div class="mt-3 flex items-center justify-between border-t border-amber-900/30 pt-3 text-[11px] font-mono text-zinc-400">
                <span>{{ __('Flagged Severities:') }}</span>
                <span class="text-amber-300 font-semibold">{{ __('High & Critical') }}</span>
            </div>
        </div>
    </div>

    <!-- 2. Charts Section (Lazy Loaded) -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Ingestion Velocity Timeline (2 Cols) -->
        <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-5 lg:col-span-2">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-2 border-b border-zinc-800/70 pb-3">
                <div>
                    <h2 class="text-sm font-semibold tracking-wide text-zinc-100 flex items-center gap-1.5">
                        <i class="fa-solid fa-chart-line text-cyan-400"></i>
                        <span>{{ __('Telemetry Velocity & Threat Peaks') }}</span>
                    </h2>
                    <p class="font-mono text-xs text-zinc-500">{{ __('Hourly events flow compared to flagged threat anomalies (Past 12 Hours)') }}</p>
                </div>
                <div class="flex items-center gap-4 font-mono text-xs">
                    <span class="flex items-center gap-1.5 text-zinc-300">
                        <span class="h-2.5 w-2.5 rounded-sm bg-cyan-400"></span> {{ __('Total Logs') }}
                    </span>
                    <span class="flex items-center gap-1.5 text-rose-400">
                        <span class="h-2.5 w-2.5 rounded-sm bg-rose-500"></span> {{ __('Threats') }}
                    </span>
                </div>
            </div>
            <div class="relative h-64 w-full">
                <div x-show="loading" class="absolute inset-0 flex flex-col items-center justify-center gap-2 rounded bg-zinc-950/70 backdrop-blur-xs z-10">
                    <i class="fa-solid fa-circle-notch fa-spin text-2xl text-cyan-400"></i>
                    <span class="font-mono text-xs text-zinc-400">{{ __('Compiling 12-Hour Ingestion Timeline...') }}</span>
                </div>
                <canvas id="velocityChart"></canvas>
            </div>
        </div>

        <!-- Severity Breakdown Donut (1 Col) -->
        <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-5">
            <div class="mb-4 border-b border-zinc-800/70 pb-3">
                <h2 class="text-sm font-semibold tracking-wide text-zinc-100 flex items-center gap-1.5">
                    <i class="fa-solid fa-chart-pie text-cyan-400"></i>
                    <span>{{ __('Threat Severity Distribution') }}</span>
                </h2>
                <p class="font-mono text-xs text-zinc-500">{{ __('Normalized log classification hierarchy') }}</p>
            </div>
            <div class="relative flex h-52 items-center justify-center">
                <div x-show="loading" class="absolute inset-0 flex flex-col items-center justify-center gap-2 rounded bg-zinc-950/70 backdrop-blur-xs z-10">
                    <i class="fa-solid fa-circle-notch fa-spin text-2xl text-cyan-400"></i>
                    <span class="font-mono text-xs text-zinc-400">{{ __('Categorizing Severities...') }}</span>
                </div>
                <canvas id="severityDonutChart"></canvas>
            </div>
            <div class="mt-3 grid grid-cols-2 gap-2 font-mono text-[11px] border-t border-zinc-800/70 pt-3">
                <div class="flex items-center justify-between px-2 py-1 bg-zinc-900/60 rounded">
                    <span class="text-rose-400 font-semibold flex items-center gap-1.5">
                        <i class="fa-solid fa-circle text-[8px] text-rose-500"></i> {{ __('Critical') }}
                    </span>
                    <span class="text-zinc-200" x-text="chartData.severities?.critical ?? 0"></span>
                </div>
                <div class="flex items-center justify-between px-2 py-1 bg-zinc-900/60 rounded">
                    <span class="text-orange-400 font-semibold flex items-center gap-1.5">
                        <i class="fa-solid fa-circle text-[8px] text-orange-500"></i> {{ __('High') }}
                    </span>
                    <span class="text-zinc-200" x-text="chartData.severities?.high ?? 0"></span>
                </div>
                <div class="flex items-center justify-between px-2 py-1 bg-zinc-900/60 rounded">
                    <span class="text-amber-400 font-semibold flex items-center gap-1.5">
                        <i class="fa-solid fa-circle text-[8px] text-amber-500"></i> {{ __('Medium') }}
                    </span>
                    <span class="text-zinc-200" x-text="chartData.severities?.medium ?? 0"></span>
                </div>
                <div class="flex items-center justify-between px-2 py-1 bg-zinc-900/60 rounded">
                    <span class="text-cyan-400 font-semibold flex items-center gap-1.5">
                        <i class="fa-solid fa-circle text-[8px] text-cyan-500"></i> {{ __('Low/Info') }}
                    </span>
                    <span class="text-zinc-200" x-text="(chartData.severities?.low ?? 0) + (chartData.severities?.info ?? 0)"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Intelligence Row: Top Threat IPs & Active Incidents -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Top Suspicious Attackers (Lazy Loaded) -->
        <div class="rounded-lg border border-zinc-800 bg-zinc-950">
            <div class="flex items-center justify-between border-b border-zinc-800 px-5 py-3.5">
                <div>
                    <h2 class="text-sm font-semibold text-zinc-100 flex items-center gap-2">
                        <i class="fa-solid fa-shield-virus text-amber-400"></i>
                        <span>{{ __('Aggressive External Source IPs') }}</span>
                    </h2>
                    <p class="font-mono text-[11px] text-zinc-500">{{ __('Top originators of high & critical security events') }}</p>
                </div>
                <span class="rounded bg-zinc-900 px-2 py-0.5 font-mono text-[10px] text-zinc-400 border border-zinc-800">
                    {{ __('Top 5 Ranked') }}
                </span>
            </div>

            <div class="divide-y divide-zinc-800/70">
                <!-- Skeleton Loader -->
                <template x-if="loading">
                    <div class="space-y-3 p-4">
                        <div class="flex items-center justify-between animate-pulse py-2">
                            <div class="flex items-center gap-3">
                                <div class="h-7 w-7 rounded bg-zinc-900 border border-zinc-800"></div>
                                <div class="h-4 w-32 rounded bg-zinc-900"></div>
                            </div>
                            <div class="h-6 w-16 rounded bg-zinc-900"></div>
                        </div>
                        <div class="flex items-center justify-between animate-pulse py-2">
                            <div class="flex items-center gap-3">
                                <div class="h-7 w-7 rounded bg-zinc-900 border border-zinc-800"></div>
                                <div class="h-4 w-28 rounded bg-zinc-900"></div>
                            </div>
                            <div class="h-6 w-16 rounded bg-zinc-900"></div>
                        </div>
                        <div class="flex items-center justify-between animate-pulse py-2">
                            <div class="flex items-center gap-3">
                                <div class="h-7 w-7 rounded bg-zinc-900 border border-zinc-800"></div>
                                <div class="h-4 w-36 rounded bg-zinc-900"></div>
                            </div>
                            <div class="h-6 w-16 rounded bg-zinc-900"></div>
                        </div>
                    </div>
                </template>

                <!-- Real Attackers Content -->
                <template x-if="!loading && topAttackers.length > 0">
                    <div>
                        <template x-for="(attacker, idx) in topAttackers" :key="attacker.source_ip">
                            <div class="flex items-center justify-between px-5 py-3 text-xs transition-colors hover:bg-zinc-900/40 border-b border-zinc-800/50 last:border-b-0">
                                <div class="flex items-center gap-3">
                                    <span class="grid h-7 w-7 place-items-center rounded bg-zinc-900 font-mono text-zinc-400 border border-zinc-800 text-[11px]" x-text="'#' + (idx + 1)"></span>
                                    <span class="text-sm select-none leading-none flex items-center justify-center w-5">
                                        <i :class="attacker.intel?.icon || 'fa-solid fa-globe text-cyan-400'" :title="(attacker.intel?.country ?? '{{ __('Unknown') }}') + ' (' + (attacker.intel?.asn ?? '') + ')'"></i>
                                    </span>
                                    <div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-mono font-semibold text-zinc-200" x-text="attacker.source_ip"></span>
                                            <span class="rounded bg-zinc-900 border border-zinc-800 px-1 py-0.2 text-[9px] font-bold text-zinc-400 uppercase font-mono" x-text="attacker.intel?.country_code ?? 'XX'"></span>
                                        </div>
                                        <p class="font-mono text-[10px] text-zinc-500">
                                            <span x-text="attacker.intel?.country ?? '{{ __('Unknown') }}'"></span> &bull; <span x-text="attacker.last_seen_human"></span>
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="text-right">
                                        <span class="font-mono font-bold text-rose-400" x-text="attacker.total_events"></span>
                                        <span class="block font-mono text-[10px] text-zinc-500">{{ __('attacks') }}</span>
                                    </div>
                                    <form method="POST" action="{{ route('containment.block') }}" class="inline" @submit.prevent="if (confirm('{{ __('Quarantine') }} ' + attacker.source_ip + ' {{ __('for 24 hours?') }}')) $el.submit()">
                                        @csrf
                                        <input type="hidden" name="ip_address" :value="attacker.source_ip">
                                        <input type="hidden" name="reason" value="{{ __('Quarantine from Dashboard: Ranked as Top Aggressive Source IP') }}">
                                        <input type="hidden" name="duration" value="24h">
                                        <button type="submit" class="rounded bg-rose-950/70 hover:bg-rose-900 border border-rose-800/60 px-2.5 py-1 font-mono text-[10px] font-bold text-rose-300 transition-colors" title="{{ __('Quarantine Attacker IP') }}">
                                            <i class="fa-solid fa-ban mr-1"></i>{{ __('BLOCK') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <template x-if="!loading && topAttackers.length === 0">
                    <div class="px-5 py-8 text-center font-mono text-xs text-zinc-500">
                        <i class="fa-solid fa-shield-check text-emerald-500 mr-1.5"></i>{{ __('No malicious IPs recorded yet.') }}
                    </div>
                </template>
            </div>
        </div>

        <!-- Recent Triggered Security Alerts (Lazy Loaded) -->
        <div class="rounded-lg border border-zinc-800 bg-zinc-950">
            <div class="flex items-center justify-between border-b border-zinc-800 px-5 py-3.5">
                <div>
                    <h2 class="text-sm font-semibold text-zinc-100 flex items-center gap-2">
                        <i class="fa-solid fa-tower-broadcast text-rose-400"></i>
                        <span>{{ __('Live Incident Triage Queue') }}</span>
                    </h2>
                    <p class="font-mono text-[11px] text-zinc-500">{{ __('Triggered alerts from detection rule correlation') }}</p>
                </div>
                <a href="{{ route('alerts.index') }}" class="font-mono text-xs text-cyan-400 hover:text-cyan-300">
                    {{ __('VIEW ALL') }} <i class="fa-solid fa-arrow-right text-[10px] ml-0.5"></i>
                </a>
            </div>

            <div class="divide-y divide-zinc-800/70">
                <!-- Skeleton Loader -->
                <template x-if="loading">
                    <div class="space-y-3 p-4">
                        <div class="flex items-center justify-between animate-pulse py-2">
                            <div class="space-y-1.5">
                                <div class="h-4 w-40 rounded bg-zinc-900"></div>
                                <div class="h-3 w-28 rounded bg-zinc-900/60"></div>
                            </div>
                            <div class="h-5 w-14 rounded bg-zinc-900"></div>
                        </div>
                        <div class="flex items-center justify-between animate-pulse py-2">
                            <div class="space-y-1.5">
                                <div class="h-4 w-36 rounded bg-zinc-900"></div>
                                <div class="h-3 w-24 rounded bg-zinc-900/60"></div>
                            </div>
                            <div class="h-5 w-14 rounded bg-zinc-900"></div>
                        </div>
                    </div>
                </template>

                <!-- Real Alerts List -->
                <template x-if="!loading && recentAlerts.length > 0">
                    <div>
                        <template x-for="alert in recentAlerts" :key="alert.id">
                            <div class="flex items-start justify-between gap-3 px-5 py-3 text-xs transition-colors hover:bg-zinc-900/40 border-b border-zinc-800/50 last:border-b-0">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span x-show="alert.severity === 'critical'" class="inline-flex items-center gap-1 rounded bg-rose-950 px-1.5 py-0.5 font-mono text-[10px] font-bold uppercase text-rose-300 border border-rose-800">
                                            <i class="fa-solid fa-triangle-exclamation text-[9px]"></i>{{ __('CRITICAL') }}
                                        </span>
                                        <span x-show="alert.severity === 'high'" class="inline-flex items-center gap-1 rounded bg-orange-950 px-1.5 py-0.5 font-mono text-[10px] font-bold uppercase text-orange-300 border border-orange-800">
                                            <i class="fa-solid fa-circle-exclamation text-[9px]"></i>{{ __('HIGH') }}
                                        </span>
                                        <span x-show="alert.severity === 'medium'" class="inline-flex items-center gap-1 rounded bg-amber-950 px-1.5 py-0.5 font-mono text-[10px] font-bold uppercase text-amber-300 border border-amber-800">
                                            <i class="fa-solid fa-circle-dot text-[9px]"></i>{{ __('MEDIUM') }}
                                        </span>
                                        <span x-show="alert.severity !== 'critical' && alert.severity !== 'high' && alert.severity !== 'medium'" class="inline-flex items-center gap-1 rounded bg-zinc-900 px-1.5 py-0.5 font-mono text-[10px] font-bold uppercase text-zinc-400 border border-zinc-800">
                                            <i class="fa-solid fa-circle-info text-[9px]"></i>{{ __('INFO') }}
                                        </span>
                                        <span class="font-medium text-zinc-200" x-text="alert.rule_name"></span>
                                    </div>
                                    <div class="flex items-center gap-2 font-mono text-[10px] text-zinc-400">
                                        <span>{{ __('Targeted Source:') }} <strong class="text-zinc-200" x-text="alert.source_ip"></strong></span>
                                        <span>&bull;</span>
                                        <span x-text="alert.triggered_at_human"></span>
                                    </div>
                                </div>
                                <div>
                                    <span x-show="alert.status === 'open'" class="inline-flex items-center gap-1 rounded-full bg-rose-950/60 px-2 py-0.5 font-mono text-[10px] font-semibold text-rose-400 border border-rose-800/60">
                                        <span class="h-1.5 w-1.5 rounded-full bg-rose-400 animate-pulse"></span> {{ __('OPEN') }}
                                    </span>
                                    <span x-show="alert.status === 'acknowledged'" class="inline-flex items-center gap-1 rounded-full bg-amber-950/60 px-2 py-0.5 font-mono text-[10px] font-semibold text-amber-400 border border-amber-800/60">
                                        {{ __('ACK') }}
                                    </span>
                                    <span x-show="alert.status === 'resolved'" class="inline-flex items-center gap-1 rounded-full bg-emerald-950/60 px-2 py-0.5 font-mono text-[10px] font-semibold text-emerald-400 border border-emerald-800/60">
                                        {{ __('RESOLVED') }}
                                    </span>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <template x-if="!loading && recentAlerts.length === 0">
                    <div class="px-5 py-8 text-center font-mono text-xs text-zinc-500">
                        <i class="fa-solid fa-circle-check text-emerald-400 mr-1.5"></i>{{ __('No security alerts triggered. System operating normally.') }}
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- 4. Live Normalized Event Telemetry Feed (Lazy Loaded) -->
    <section class="rounded-lg border border-zinc-800 bg-zinc-950" aria-labelledby="event-stream-title">
        <div class="flex min-h-12 flex-wrap items-center justify-between gap-3 border-b border-zinc-800 px-5 py-3">
            <div class="flex items-center gap-2">
                <span class="h-2 w-2 rounded-full bg-cyan-400 animate-pulse"></span>
                <h2 id="event-stream-title" class="text-sm font-semibold text-zinc-200 flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-cyan-400"></i>
                    <span>{{ __('Recent Normalized Events Telemetry') }}</span>
                </h2>
            </div>
            <div class="flex items-center gap-3">
                <span class="font-mono text-xs text-zinc-500">
                    {{ __('Displaying') }} <span x-text="recentEvents.length"></span> {{ __('real-time events') }}
                </span>
                <a href="{{ route('logs.index') }}" class="inline-flex items-center gap-1.5 rounded bg-zinc-900 px-2.5 py-1 font-mono text-xs font-semibold text-cyan-400 hover:bg-zinc-800 hover:text-cyan-300 border border-zinc-800 transition-colors">
                    <span>{{ __('EXPLORER') }}</span> <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-left text-xs">
                <thead class="border-b border-zinc-800 bg-zinc-900/60 font-mono uppercase text-zinc-400">
                    <tr>
                        <th class="px-4 py-3 font-normal">{{ __('Timestamp (UTC)') }}</th>
                        <th class="px-4 py-3 font-normal">{{ __('Source IP') }}</th>
                        <th class="px-4 py-3 font-normal">{{ __('Destination') }}</th>
                        <th class="px-4 py-3 font-normal">{{ __('Category') }}</th>
                        <th class="px-4 py-3 font-normal">{{ __('Action & Payload') }}</th>
                        <th class="px-4 py-3 font-normal">{{ __('Outcome') }}</th>
                        <th class="px-4 py-3 font-normal">{{ __('Severity') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/60 font-mono">
                    <!-- Skeleton rows during loading -->
                    <template x-if="loading">
                        <template x-for="i in [1,2,3,4,5,6]" :key="i">
                            <tr class="animate-pulse">
                                <td class="px-4 py-3"><div class="h-4 w-16 bg-zinc-900 rounded"></div></td>
                                <td class="px-4 py-3"><div class="h-4 w-28 bg-zinc-900 rounded"></div></td>
                                <td class="px-4 py-3"><div class="h-4 w-20 bg-zinc-900 rounded"></div></td>
                                <td class="px-4 py-3"><div class="h-4 w-16 bg-zinc-900 rounded"></div></td>
                                <td class="px-4 py-3"><div class="h-4 w-40 bg-zinc-900 rounded"></div></td>
                                <td class="px-4 py-3"><div class="h-4 w-14 bg-zinc-900 rounded"></div></td>
                                <td class="px-4 py-3"><div class="h-4 w-14 bg-zinc-900 rounded"></div></td>
                            </tr>
                        </template>
                    </template>

                    <!-- Real telemetry rows -->
                    <template x-if="!loading && recentEvents.length > 0">
                        <template x-for="event in recentEvents" :key="event.id">
                            <tr class="transition-colors hover:bg-zinc-900/50">
                                <!-- Timestamp -->
                                <td class="whitespace-nowrap px-4 py-3 text-zinc-400">
                                    <span x-text="event.time"></span>
                                    <span class="block text-[10px] text-zinc-600" x-text="event.date"></span>
                                </td>

                                <!-- Source IP with Geolocation -->
                                <td class="whitespace-nowrap px-4 py-3">
                                    <template x-if="event.source_ip">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-sm select-none flex items-center justify-center w-4">
                                                <i :class="event.intel?.icon || 'fa-solid fa-globe text-cyan-400'" :title="event.intel?.country ?? ''"></i>
                                            </span>
                                            <div>
                                                <span class="font-semibold text-zinc-200" x-text="event.source_ip"></span>
                                                <span x-show="event.username" class="block text-[10px] text-zinc-500" x-text="'{{ __('user:') }} ' + event.username"></span>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="!event.source_ip">
                                        <span class="text-zinc-600"><i class="fa-solid fa-network-wired mr-1 text-zinc-600"></i>{{ __('Local / Gateway') }}</span>
                                    </template>
                                </td>

                                <!-- Destination -->
                                <td class="whitespace-nowrap px-4 py-3 text-zinc-400" x-text="event.destination"></td>

                                <!-- Category -->
                                <td class="whitespace-nowrap px-4 py-3">
                                    <span class="rounded bg-zinc-900 px-2 py-0.5 text-[10px] uppercase font-bold text-zinc-300 border border-zinc-800" x-text="event.event_category"></span>
                                </td>

                                <!-- Action & Message -->
                                <td class="px-4 py-3 text-zinc-300 max-w-xs truncate" :title="event.event_action">
                                    <span class="font-semibold text-cyan-300" x-text="event.event_action"></span>
                                    <span x-show="event.raw_message" class="block truncate text-[10px] text-zinc-500" x-text="event.raw_message"></span>
                                </td>

                                <!-- Outcome -->
                                <td class="whitespace-nowrap px-4 py-3">
                                    <span x-show="event.event_outcome === 'success'" class="rounded bg-emerald-950/60 px-1.5 py-0.5 text-[10px] font-bold text-emerald-400 border border-emerald-800/40">
                                        <i class="fa-solid fa-check mr-1 text-[9px]"></i>{{ __('SUCCESS') }}
                                    </span>
                                    <span x-show="event.event_outcome === 'failure'" class="rounded bg-rose-950/60 px-1.5 py-0.5 text-[10px] font-bold text-rose-400 border border-rose-800/40">
                                        <i class="fa-solid fa-xmark mr-1 text-[9px]"></i>{{ __('FAILURE') }}
                                    </span>
                                    <span x-show="event.event_outcome === 'blocked'" class="rounded bg-purple-950/60 px-1.5 py-0.5 text-[10px] font-bold text-purple-300 border border-purple-800/40">
                                        <i class="fa-solid fa-shield-halved mr-1 text-[9px]"></i>{{ __('BLOCKED') }}
                                    </span>
                                    <span x-show="event.event_outcome !== 'success' && event.event_outcome !== 'failure' && event.event_outcome !== 'blocked'" class="rounded bg-zinc-900 px-1.5 py-0.5 text-[10px] text-zinc-400 border border-zinc-800" x-text="translateOutcome(event.event_outcome)"></span>
                                </td>

                                <!-- Severity -->
                                <td class="whitespace-nowrap px-4 py-3">
                                    <span x-show="event.severity === 'critical'" class="inline-flex items-center gap-1 font-bold text-rose-400">
                                        <i class="fa-solid fa-triangle-exclamation text-[10px] text-rose-500 animate-pulse"></i> {{ __('CRITICAL') }}
                                    </span>
                                    <span x-show="event.severity === 'high'" class="font-bold text-orange-400">
                                        <i class="fa-solid fa-circle-exclamation text-[10px] mr-0.5"></i> {{ __('HIGH') }}
                                    </span>
                                    <span x-show="event.severity === 'medium'" class="font-semibold text-amber-400">
                                        <i class="fa-solid fa-circle-dot text-[10px] mr-0.5"></i> {{ __('MEDIUM') }}
                                    </span>
                                    <span x-show="event.severity === 'low'" class="text-cyan-400">
                                        <i class="fa-solid fa-circle-info text-[10px] mr-0.5"></i> {{ __('LOW') }}
                                    </span>
                                    <span x-show="event.severity === 'info' || !event.severity" class="text-zinc-500">
                                        <i class="fa-solid fa-info text-[10px] mr-0.5"></i> {{ __('INFO') }}
                                    </span>
                                </td>
                            </tr>
                        </template>
                    </template>

                    <!-- Empty state -->
                    <template x-if="!loading && recentEvents.length === 0">
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center font-mono text-zinc-500">
                                <i class="fa-solid fa-database text-zinc-600 mr-2"></i>{{ __('No events have been ingested into this pipeline yet.') }}
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </section>
</div>

<!-- Alpine & Chart.js Lazy Loading Script -->
<script>
function dashboardLoader() {
    return {
        loading: true,
        metrics: {},
        chartData: {
            hours: [],
            totalEvents: [],
            threatEvents: [],
            severities: { critical: 0, high: 0, medium: 0, low: 0, info: 0 }
        },
        topAttackers: [],
        recentAlerts: [],
        recentEvents: [],
        serverTime: '',
        velocityChartInstance: null,
        severityChartInstance: null,
        i18n: {
            totalLogs: "{{ __('Total Logs') }}",
            flaggedThreats: "{{ __('Threats') }}",
            critical: "{{ __('Critical') }}",
            high: "{{ __('High') }}",
            medium: "{{ __('Medium') }}",
            low: "{{ __('Low') }}",
            info: "{{ __('Info') }}"
        },

        formatNumber(num) {
            return new Intl.NumberFormat().format(num || 0);
        },

        translateOutcome(outcome) {
            const map = {
                'success': "{{ __('SUCCESS') }}",
                'failure': "{{ __('FAILURE') }}",
                'blocked': "{{ __('BLOCKED') }}",
                'unknown': "{{ __('UNKNOWN') }}"
            };
            return map[outcome] || (outcome || '').toUpperCase();
        },

        initDashboard() {
            window.aegisHasLazyLoader = true;
            this.fetchDashboard();

            // Listen for wallboard auto-sync events
            window.addEventListener('aegis-refresh', () => {
                this.fetchDashboard(false);
            });
        },

        async fetchDashboard(isManual = false) {
            if (isManual) this.loading = true;

            try {
                const response = await fetch("{{ route('dashboard.data') }}", {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }

                const payload = await response.json();
                this.metrics = payload.metrics || {};
                this.chartData = payload.chartData || this.chartData;
                this.topAttackers = payload.topAttackers || [];
                this.recentAlerts = payload.recentAlerts || [];
                this.recentEvents = payload.recentEvents || [];
                this.serverTime = payload.serverTimeUtc || '';

                this.$nextTick(() => {
                    this.renderCharts();
                });
            } catch (err) {
                console.error('[Aegis SOC] Failed to lazy load dashboard telemetry:', err);
            } finally {
                this.loading = false;
            }
        },

        renderCharts() {
            if (typeof window.Chart === 'undefined') {
                console.error('Chart.js not loaded');
                return;
            }

            // 1. Velocity Timeline Chart
            const velocityCtx = document.getElementById('velocityChart');
            if (velocityCtx) {
                if (this.velocityChartInstance) {
                    this.velocityChartInstance.data.labels = this.chartData.hours || [];
                    this.velocityChartInstance.data.datasets[0].data = this.chartData.totalEvents || [];
                    this.velocityChartInstance.data.datasets[1].data = this.chartData.threatEvents || [];
                    this.velocityChartInstance.update();
                } else {
                    this.velocityChartInstance = new window.Chart(velocityCtx, {
                        type: 'line',
                        data: {
                            labels: this.chartData.hours || [],
                            datasets: [
                                {
                                    label: this.i18n.totalLogs,
                                    data: this.chartData.totalEvents || [],
                                    borderColor: '#22d3ee', // Cyan
                                    backgroundColor: 'rgba(34, 211, 238, 0.08)',
                                    borderWidth: 2,
                                    fill: true,
                                    tension: 0.35,
                                    pointRadius: 3,
                                    pointBackgroundColor: '#22d3ee',
                                },
                                {
                                    label: this.i18n.flaggedThreats,
                                    data: this.chartData.threatEvents || [],
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
                            animation: { duration: 600 },
                            plugins: {
                                legend: { display: false },
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
                                    grid: { color: 'rgba(39, 39, 42, 0.5)' },
                                    ticks: { color: '#71717a', font: { family: 'monospace', size: 10 } }
                                },
                                y: {
                                    beginAtZero: true,
                                    grid: { color: 'rgba(39, 39, 42, 0.5)' },
                                    ticks: { color: '#71717a', font: { family: 'monospace', size: 10 } }
                                }
                            }
                        }
                    });
                }
            }

            // 2. Severity Donut Chart
            const severityCtx = document.getElementById('severityDonutChart');
            if (severityCtx) {
                const severities = this.chartData.severities || {};
                const sevData = [
                    severities.critical || 0,
                    severities.high || 0,
                    severities.medium || 0,
                    severities.low || 0,
                    severities.info || 0
                ];

                if (this.severityChartInstance) {
                    this.severityChartInstance.data.datasets[0].data = sevData;
                    this.severityChartInstance.update();
                } else {
                    this.severityChartInstance = new window.Chart(severityCtx, {
                        type: 'doughnut',
                        data: {
                            labels: [
                                this.i18n.critical,
                                this.i18n.high,
                                this.i18n.medium,
                                this.i18n.low,
                                this.i18n.info
                            ],
                            datasets: [{
                                data: sevData,
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
                            animation: { duration: 600 },
                            plugins: {
                                legend: { display: false },
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
            }
        }
    };
}
</script>
@endsection
