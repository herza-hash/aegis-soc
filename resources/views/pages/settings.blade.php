@extends('layouts.app', ['title' => 'System Settings | Aegis SOC'])

@section('content')
<div class="space-y-6" x-data="{
    ruleModalOpen: false,
    activeTab: 'rules'
}">
    <!-- Header -->
    <div class="flex flex-col gap-4 border-b border-zinc-800/80 pb-6 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2 font-mono text-[11px] uppercase tracking-wider text-cyan-400">
                <span class="inline-block h-1.5 w-1.5 rounded-full bg-cyan-400 shadow-[0_0_8px_#22d3ee]"></span>
                <span>AEGIS / ENGINE CONFIGURATION</span>
            </div>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-zinc-100 sm:text-3xl">System Settings & Detection Rules</h1>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" @click="ruleModalOpen = true" class="inline-flex items-center gap-1.5 rounded-md bg-cyan-600 px-3.5 py-1.5 font-mono text-xs font-semibold text-white hover:bg-cyan-500 transition-colors shadow-sm shadow-cyan-600/30">
                <span>+ NEW DETECTION RULE</span>
            </button>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-2 border-b border-zinc-800 font-mono text-xs">
        <button type="button" @click="activeTab = 'rules'" class="border-b-2 px-4 py-2.5 font-semibold transition-colors" :class="activeTab === 'rules' ? 'border-cyan-400 text-cyan-300' : 'border-transparent text-zinc-400 hover:text-zinc-200'">
            DETECTION RULES ({{ $rules->count() }})
        </button>
        <button type="button" @click="activeTab = 'diagnostics'" class="border-b-2 px-4 py-2.5 font-semibold transition-colors" :class="activeTab === 'diagnostics' ? 'border-cyan-400 text-cyan-300' : 'border-transparent text-zinc-400 hover:text-zinc-200'">
            ENGINE DIAGNOSTICS & TELEMETRY
        </button>
    </div>

    <!-- TAB 1: Detection Rules -->
    <div x-show="activeTab === 'rules'" class="space-y-6">
        <div class="overflow-hidden rounded-lg border border-zinc-800 bg-zinc-950">
            <div class="flex items-center justify-between border-b border-zinc-800 px-5 py-3.5">
                <div>
                    <h2 class="text-sm font-semibold text-zinc-100">Configured Correlation Rules</h2>
                    <p class="font-mono text-[11px] text-zinc-500">Heuristics evaluated whenever new telemetry logs are ingested</p>
                </div>
                <span class="rounded bg-zinc-900 px-2 py-0.5 font-mono text-[10px] text-zinc-400 border border-zinc-800">
                    Auto-Evaluation Active
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left text-xs font-mono">
                    <thead class="border-b border-zinc-800 bg-zinc-900/60 uppercase text-zinc-400">
                        <tr>
                            <th class="px-4 py-3 font-normal">Rule ID</th>
                            <th class="px-4 py-3 font-normal">Rule Name</th>
                            <th class="px-4 py-3 font-normal">Condition Model</th>
                            <th class="px-4 py-3 font-normal">Threshold & Time Window</th>
                            <th class="px-4 py-3 font-normal">Severity</th>
                            <th class="px-4 py-3 font-normal">Triggered Alerts</th>
                            <th class="px-4 py-3 font-normal text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800/60">
                        @forelse ($rules as $rule)
                            <tr class="transition-colors hover:bg-zinc-900/40">
                                <td class="px-4 py-3 text-zinc-400">
                                    #RULE-{{ str_pad($rule->id, 3, '0', STR_PAD_LEFT) }}
                                </td>

                                <td class="px-4 py-3 font-semibold text-zinc-100">
                                    {{ $rule->name }}
                                </td>

                                <td class="px-4 py-3">
                                    <span class="rounded bg-zinc-900 border border-zinc-800 px-2 py-0.5 text-[10px] uppercase font-bold text-cyan-300">
                                        {{ str_replace('_', ' ', $rule->condition_type) }}
                                    </span>
                                </td>

                                <td class="px-4 py-3 text-zinc-300">
                                    <span class="text-zinc-100 font-bold">&ge; {{ $rule->threshold }}</span> events /
                                    <span class="text-zinc-400">{{ $rule->time_window_minutes }} min(s)</span>
                                </td>

                                <td class="px-4 py-3">
                                    @if($rule->severity === 'critical')
                                        <span class="inline-flex items-center gap-1 rounded bg-rose-950 px-2 py-0.5 text-[10px] font-bold uppercase text-rose-300 border border-rose-800">
                                            CRITICAL
                                        </span>
                                    @elseif($rule->severity === 'high')
                                        <span class="inline-flex items-center rounded bg-orange-950 px-2 py-0.5 text-[10px] font-bold uppercase text-orange-300 border border-orange-800">
                                            HIGH
                                        </span>
                                    @elseif($rule->severity === 'medium')
                                        <span class="inline-flex items-center rounded bg-amber-950 px-2 py-0.5 text-[10px] font-bold uppercase text-amber-300 border border-amber-800">
                                            MEDIUM
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded bg-zinc-900 px-2 py-0.5 text-[10px] font-bold uppercase text-zinc-400 border border-zinc-800">
                                            INFO
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-3">
                                    <a href="{{ route('alerts.index', ['search' => $rule->name]) }}" class="font-bold text-cyan-400 hover:text-cyan-300 hover:underline">
                                        {{ $rule->security_alerts_count }} alert(s)
                                    </a>
                                </td>

                                <td class="px-4 py-3 text-right">
                                    <form method="POST" action="{{ route('settings.rules.destroy', $rule) }}" class="inline" onsubmit="return confirm('Retire detection rule {{ $rule->name }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded border border-rose-900/60 bg-rose-950/40 px-2 py-1 text-[10px] font-semibold text-rose-400 hover:bg-rose-900 hover:text-white transition-colors">
                                            RETIRE
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-zinc-500">
                                    No detection rules configured. Click "+ New Detection Rule" to define one.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 2: Engine Diagnostics -->
    <div x-show="activeTab === 'diagnostics'" class="space-y-6" style="display: none;">
        <!-- Environment Grid -->
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4 font-mono text-xs">
            <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-4">
                <span class="text-zinc-500 uppercase text-[10px] block">Framework Core</span>
                <span class="mt-1 block text-lg font-bold text-zinc-100">Laravel {{ $systemInfo['laravel_version'] }}</span>
                <span class="text-zinc-500 text-[10px]">PHP {{ $systemInfo['php_version'] }}</span>
            </div>

            <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-4">
                <span class="text-zinc-500 uppercase text-[10px] block">Database Connection</span>
                <span class="mt-1 block text-lg font-bold text-cyan-400 uppercase">{{ $systemInfo['database_driver'] }}</span>
                <span class="text-zinc-500 text-[10px]">Host: 127.0.0.1:3306</span>
            </div>

            <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-4">
                <span class="text-zinc-500 uppercase text-[10px] block">Queue Worker</span>
                <span class="mt-1 block text-lg font-bold text-emerald-400 uppercase">{{ $systemInfo['queue_driver'] }}</span>
                <span class="text-zinc-500 text-[10px]">Job: ParseLogJob</span>
            </div>

            <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-4">
                <span class="text-zinc-500 uppercase text-[10px] block">Host Platform</span>
                <span class="mt-1 block text-lg font-bold text-purple-400 uppercase">{{ $systemInfo['server_os'] }}</span>
                <span class="text-zinc-500 text-[10px]">Env: {{ $systemInfo['app_env'] }}</span>
            </div>
        </div>

        <!-- Active Log Parsers Table -->
        <div class="rounded-lg border border-zinc-800 bg-zinc-950 overflow-hidden font-mono text-xs">
            <div class="border-b border-zinc-800 px-5 py-3.5 bg-zinc-900/60">
                <h3 class="font-semibold text-zinc-100 uppercase tracking-wide">Registered Modular Parsers</h3>
                <p class="text-[11px] text-zinc-500">Autonomous log decoders loaded into LogParserManager</p>
            </div>
            <div class="divide-y divide-zinc-800/60">
                @foreach ($systemInfo['parsers'] as $parser)
                    <div class="flex items-center justify-between px-5 py-3">
                        <div>
                            <span class="font-bold text-cyan-300 block">{{ $parser['name'] }}</span>
                            <span class="text-[11px] text-zinc-500">{{ $parser['format'] }}</span>
                        </div>
                        <span class="inline-flex items-center gap-1 rounded bg-emerald-950/70 border border-emerald-800/60 px-2 py-0.5 text-[10px] font-bold text-emerald-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span> {{ $parser['status'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Database Records Metrics -->
        <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-5 font-mono text-xs">
            <h3 class="font-semibold text-zinc-100 uppercase tracking-wide mb-3">Database Records Count</h3>
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 text-center">
                <div class="rounded bg-zinc-900/80 p-3 border border-zinc-800">
                    <span class="text-zinc-500 text-[10px] uppercase block">Raw Logs</span>
                    <span class="text-lg font-bold text-cyan-400">{{ number_format($systemInfo['counts']['raw_logs']) }}</span>
                </div>
                <div class="rounded bg-zinc-900/80 p-3 border border-zinc-800">
                    <span class="text-zinc-500 text-[10px] uppercase block">Normalized</span>
                    <span class="text-lg font-bold text-zinc-100">{{ number_format($systemInfo['counts']['normalized_events']) }}</span>
                </div>
                <div class="rounded bg-zinc-900/80 p-3 border border-zinc-800">
                    <span class="text-zinc-500 text-[10px] uppercase block">Alerts</span>
                    <span class="text-lg font-bold text-rose-400">{{ number_format($systemInfo['counts']['security_alerts']) }}</span>
                </div>
                <div class="rounded bg-zinc-900/80 p-3 border border-zinc-800">
                    <span class="text-zinc-500 text-[10px] uppercase block">Sources</span>
                    <span class="text-lg font-bold text-emerald-400">{{ number_format($systemInfo['counts']['log_sources']) }}</span>
                </div>
                <div class="rounded bg-zinc-900/80 p-3 border border-zinc-800">
                    <span class="text-zinc-500 text-[10px] uppercase block">Rules</span>
                    <span class="text-lg font-bold text-purple-400">{{ number_format($systemInfo['counts']['detection_rules']) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Detection Rule Modal -->
    <div x-cloak x-show="ruleModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" @click.self="ruleModalOpen = false" @keydown.escape.window="ruleModalOpen = false">
        <div class="w-full max-w-lg rounded-lg border border-zinc-800 bg-zinc-950 shadow-2xl overflow-hidden font-mono text-xs" x-show="ruleModalOpen" x-transition>
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-zinc-800 px-5 py-4 bg-zinc-900/60">
                <h3 class="font-bold text-zinc-100 uppercase tracking-wide">Deploy New Detection Rule</h3>
                <button type="button" @click="ruleModalOpen = false" class="text-zinc-400 hover:text-white text-lg">&times;</button>
            </div>

            <!-- Body -->
            <form method="POST" action="{{ route('settings.rules.store') }}" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-[10px] uppercase text-zinc-500 mb-1">Rule Name</label>
                    <input type="text" name="name" placeholder="e.g. Anomalous Port Knocking Sequence" required class="w-full rounded border border-zinc-800 bg-zinc-900 px-3 py-2 text-zinc-200 focus:border-cyan-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] uppercase text-zinc-500 mb-1">Condition Model</label>
                        <select name="condition_type" required class="w-full rounded border border-zinc-800 bg-zinc-900 px-3 py-2 text-zinc-200 focus:border-cyan-500 focus:outline-none">
                            <option value="threshold_exceeded">Threshold Exceeded</option>
                            <option value="pattern_match">Pattern Match</option>
                            <option value="single_event">Single Critical Event</option>
                            <option value="volume_spike">Volume Spike</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] uppercase text-zinc-500 mb-1">Severity Rating</label>
                        <select name="severity" required class="w-full rounded border border-zinc-800 bg-zinc-900 px-3 py-2 text-zinc-200 focus:border-cyan-500 focus:outline-none">
                            <option value="critical">Critical</option>
                            <option value="high" selected>High</option>
                            <option value="medium">Medium</option>
                            <option value="low">Low</option>
                            <option value="info">Info</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] uppercase text-zinc-500 mb-1">Threshold (Count)</label>
                        <input type="number" name="threshold" value="5" min="1" max="10000" required class="w-full rounded border border-zinc-800 bg-zinc-900 px-3 py-2 text-zinc-200 focus:border-cyan-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-[10px] uppercase text-zinc-500 mb-1">Time Window (Minutes)</label>
                        <input type="number" name="time_window_minutes" value="5" min="1" max="1440" required class="w-full rounded border border-zinc-800 bg-zinc-900 px-3 py-2 text-zinc-200 focus:border-cyan-500 focus:outline-none">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-zinc-800/80 pt-4">
                    <button type="button" @click="ruleModalOpen = false" class="rounded border border-zinc-700 bg-zinc-800 px-3.5 py-1.5 text-xs text-zinc-300 hover:text-white transition-colors">
                        CANCEL
                    </button>
                    <button type="submit" class="rounded bg-cyan-600 px-4 py-1.5 text-xs font-semibold text-white hover:bg-cyan-500 transition-colors">
                        DEPLOY RULE
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
