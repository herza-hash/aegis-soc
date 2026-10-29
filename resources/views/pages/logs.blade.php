@extends('layouts.app', ['title' => 'Log Explorer | Aegis SOC'])

@section('content')
<div class="space-y-6" x-data="{
    inspectModalOpen: false,
    selectedLog: null,
    copied: false,
    openInspect(log) {
        this.selectedLog = log;
        this.inspectModalOpen = true;
        this.copied = false;
    },
    copyRaw() {
        if (this.selectedLog?.raw_message) {
            navigator.clipboard.writeText(this.selectedLog.raw_message);
            this.copied = true;
            setTimeout(() => this.copied = false, 2000);
        }
    }
}">
    <!-- Header -->
    <div class="flex flex-col gap-4 border-b border-zinc-800/80 pb-6 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2 font-mono text-[11px] uppercase tracking-wider text-cyan-400">
                <span class="inline-block h-1.5 w-1.5 rounded-full bg-cyan-400 shadow-[0_0_8px_#22d3ee]"></span>
                <span>AEGIS / TELEMETRY ANALYSIS</span>
            </div>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-zinc-100 sm:text-3xl">Log Explorer & Event Analyzer</h1>
        </div>

        <div class="flex items-center gap-3">
            <div class="rounded-md border border-zinc-800 bg-zinc-900 px-3 py-1.5 font-mono text-xs text-zinc-400">
                <span>Matched:</span>
                <strong class="text-cyan-300">{{ number_format($metrics['total_matched']) }}</strong>
                <span class="text-zinc-600">/ {{ number_format($metrics['total_in_db']) }} Total</span>
            </div>
        </div>
    </div>

    <!-- Advanced Multi-Filter Search Panel -->
    <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-5 shadow-lg">
        <form method="GET" action="{{ route('logs.index') }}" class="space-y-4">
            <!-- First Row: Keyword Search Bar -->
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-zinc-500">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Search logs by keyword, payload, username, path, or raw message snippet..." class="w-full rounded-md border border-zinc-800 bg-zinc-900/90 py-2.5 pl-10 pr-4 font-mono text-xs text-zinc-200 placeholder-zinc-500 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500">
            </div>

            <!-- Second Row: Filters Grid -->
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6 font-mono text-xs">
                <!-- Source IP Filter -->
                <div>
                    <label class="block text-[10px] uppercase text-zinc-500 mb-1">Source IP</label>
                    <input type="text" name="source_ip" value="{{ $filters['source_ip'] }}" placeholder="e.g. 185.220.101.45" class="w-full rounded border border-zinc-800 bg-zinc-900/90 px-2.5 py-1.5 text-zinc-200 focus:border-cyan-500 focus:outline-none">
                </div>

                <!-- Category Filter -->
                <div>
                    <label class="block text-[10px] uppercase text-zinc-500 mb-1">Category</label>
                    <select name="category" class="w-full rounded border border-zinc-800 bg-zinc-900/90 px-2.5 py-1.5 text-zinc-200 focus:border-cyan-500 focus:outline-none">
                        <option value="all" {{ $filters['category'] === 'all' ? 'selected' : '' }}>All Categories</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat }}" {{ $filters['category'] === $cat ? 'selected' : '' }}>{{ strtoupper($cat) }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Severity Filter -->
                <div>
                    <label class="block text-[10px] uppercase text-zinc-500 mb-1">Severity</label>
                    <select name="severity" class="w-full rounded border border-zinc-800 bg-zinc-900/90 px-2.5 py-1.5 text-zinc-200 focus:border-cyan-500 focus:outline-none">
                        <option value="all" {{ $filters['severity'] === 'all' ? 'selected' : '' }}>All Severities</option>
                        <option value="critical" {{ $filters['severity'] === 'critical' ? 'selected' : '' }}>Critical</option>
                        <option value="high" {{ $filters['severity'] === 'high' ? 'selected' : '' }}>High</option>
                        <option value="medium" {{ $filters['severity'] === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="low" {{ $filters['severity'] === 'low' ? 'selected' : '' }}>Low</option>
                        <option value="info" {{ $filters['severity'] === 'info' ? 'selected' : '' }}>Info</option>
                    </select>
                </div>

                <!-- Outcome Filter -->
                <div>
                    <label class="block text-[10px] uppercase text-zinc-500 mb-1">Outcome</label>
                    <select name="outcome" class="w-full rounded border border-zinc-800 bg-zinc-900/90 px-2.5 py-1.5 text-zinc-200 focus:border-cyan-500 focus:outline-none">
                        <option value="all" {{ $filters['outcome'] === 'all' ? 'selected' : '' }}>All Outcomes</option>
                        <option value="success" {{ $filters['outcome'] === 'success' ? 'selected' : '' }}>Success</option>
                        <option value="failure" {{ $filters['outcome'] === 'failure' ? 'selected' : '' }}>Failure</option>
                        <option value="blocked" {{ $filters['outcome'] === 'blocked' ? 'selected' : '' }}>Blocked</option>
                    </select>
                </div>

                <!-- Date From -->
                <div>
                    <label class="block text-[10px] uppercase text-zinc-500 mb-1">Date From</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] }}" class="w-full rounded border border-zinc-800 bg-zinc-900/90 px-2.5 py-1.5 text-zinc-200 focus:border-cyan-500 focus:outline-none">
                </div>

                <!-- Date To -->
                <div>
                    <label class="block text-[10px] uppercase text-zinc-500 mb-1">Date To</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] }}" class="w-full rounded border border-zinc-800 bg-zinc-900/90 px-2.5 py-1.5 text-zinc-200 focus:border-cyan-500 focus:outline-none">
                </div>
            </div>

            <!-- Third Row: Action Buttons -->
            <div class="flex items-center justify-between border-t border-zinc-800/80 pt-3">
                <div class="flex items-center gap-2">
                    @if($filters['q'] || $filters['source_ip'] || $filters['category'] !== 'all' || $filters['severity'] !== 'all' || $filters['outcome'] !== 'all' || $filters['date_from'] || $filters['date_to'])
                        <span class="rounded bg-cyan-950/70 border border-cyan-800/50 px-2.5 py-1 font-mono text-[10px] text-cyan-300">
                            Active Query Filters Applied
                        </span>
                        <a href="{{ route('logs.index') }}" class="font-mono text-xs text-zinc-400 hover:text-white underline underline-offset-2">
                            Reset All
                        </a>
                    @endif
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('logs.export', request()->all()) }}" class="inline-flex items-center gap-1.5 rounded-md border border-zinc-700 bg-zinc-800 px-3.5 py-2 font-mono text-xs font-semibold text-zinc-300 hover:bg-zinc-700 hover:text-white transition-colors" title="Download matching logs as CSV">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span>EXPORT CSV</span>
                    </a>
                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-md bg-cyan-600 px-4 py-2 font-mono text-xs font-semibold text-white hover:bg-cyan-500 transition-colors shadow-sm shadow-cyan-600/30">
                        SEARCH TELEMETRY
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Telemetry Table -->
    <div class="overflow-hidden rounded-lg border border-zinc-800 bg-zinc-950">
        <div class="flex items-center justify-between border-b border-zinc-800 px-5 py-3">
            <span class="font-mono text-xs text-zinc-400">
                Page {{ $events->currentPage() }} &bull; Showing {{ $events->firstItem() ?? 0 }}-{{ $events->lastItem() ?? 0 }} of {{ number_format($events->total()) }} events
            </span>
            <span class="font-mono text-xs text-zinc-500">Live ingestion database</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[980px] text-left text-xs">
                <thead class="border-b border-zinc-800 bg-zinc-900/70 font-mono uppercase text-zinc-400">
                    <tr>
                        <th class="px-4 py-3 font-normal">Timestamp (UTC)</th>
                        <th class="px-4 py-3 font-normal">Source Node</th>
                        <th class="px-4 py-3 font-normal">Source IP</th>
                        <th class="px-4 py-3 font-normal">Destination</th>
                        <th class="px-4 py-3 font-normal">Category</th>
                        <th class="px-4 py-3 font-normal">Action & Payload</th>
                        <th class="px-4 py-3 font-normal">Outcome</th>
                        <th class="px-4 py-3 font-normal">Severity</th>
                        <th class="px-4 py-3 font-normal text-right">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/60 font-mono">
                    @forelse ($events as $event)
                        <tr class="transition-colors hover:bg-zinc-900/40">
                            <!-- Timestamp -->
                            <td class="whitespace-nowrap px-4 py-3 text-zinc-400">
                                <span>{{ $event->timestamp ? $event->timestamp->format('H:i:s') : 'N/A' }}</span>
                                <span class="block text-[10px] text-zinc-600">{{ $event->timestamp ? $event->timestamp->format('Y-m-d') : '' }}</span>
                            </td>

                            <!-- Log Source Device -->
                            <td class="whitespace-nowrap px-4 py-3 text-zinc-300">
                                <span class="block truncate max-w-[140px]" title="{{ $event->rawLog->logSource->name ?? 'Direct Pipeline' }}">
                                    {{ $event->rawLog->logSource->name ?? 'Direct Pipeline' }}
                                </span>
                                <span class="text-[10px] text-zinc-500 uppercase">{{ $event->rawLog->logSource->type ?? 'api' }}</span>
                            </td>

                            <!-- Source IP with Geolocation -->
                            <td class="whitespace-nowrap px-4 py-3">
                                @if($event->source_ip)
                                    @php
                                        $intel = \App\Services\ThreatIntelService::lookup($event->source_ip);
                                    @endphp
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-sm select-none" title="{{ $intel['country'] }} ({{ $intel['asn'] }})">{{ $intel['flag'] }}</span>
                                        <div>
                                            <span class="font-bold text-zinc-200">{{ $event->source_ip }}</span>
                                            @if($event->username)
                                                <span class="block text-[10px] text-zinc-500">user: {{ $event->username }}</span>
                                            @else
                                                <span class="block text-[9px] text-zinc-500 uppercase">{{ $intel['country'] }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <span class="text-zinc-600">Local / Gateway</span>
                                @endif
                            </td>

                            <!-- Destination IP -->
                            <td class="whitespace-nowrap px-4 py-3 text-zinc-400">
                                {{ $event->destination_ip ?? '10.0.1.x' }}
                            </td>

                            <!-- Category -->
                            <td class="whitespace-nowrap px-4 py-3">
                                <span class="rounded bg-zinc-900 px-2 py-0.5 text-[10px] uppercase font-bold text-zinc-300 border border-zinc-800">
                                    {{ $event->event_category }}
                                </span>
                            </td>

                            <!-- Action & Message Preview -->
                            <td class="px-4 py-3 text-zinc-300 max-w-xs truncate" title="{{ $event->event_action }}">
                                <span class="font-semibold text-cyan-300">{{ $event->event_action }}</span>
                                @if(!empty($event->parsed_data['raw_message']))
                                    <span class="block truncate text-[10px] text-zinc-500">{{ Str::limit($event->parsed_data['raw_message'], 65) }}</span>
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

                            <!-- Actions -->
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <button type="button" @click="openInspect({{ json_encode([
                                    'id' => $event->id,
                                    'timestamp' => $event->timestamp ? $event->timestamp->format('Y-m-d H:i:s') . ' UTC' : 'N/A',
                                    'source_node' => $event->rawLog->logSource->name ?? 'Ingest Endpoint',
                                    'source_ip' => $event->source_ip ?? 'Unknown',
                                    'dest_ip' => $event->destination_ip ?? 'Internal Node',
                                    'username' => $event->username ?? '-',
                                    'category' => $event->event_category,
                                    'action' => $event->event_action,
                                    'outcome' => $event->event_outcome,
                                    'severity' => $event->severity,
                                    'raw_message' => $event->rawLog->raw_message ?? ($event->parsed_data['raw_message'] ?? 'Raw message unavailable'),
                                    'parsed_data' => $event->parsed_data ?? [],
                                ]) }})" class="rounded border border-zinc-700 bg-zinc-800 px-2.5 py-1 text-[11px] font-semibold text-zinc-300 hover:border-zinc-500 hover:bg-zinc-700 hover:text-white transition-colors">
                                    INSPECT
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-16 text-center font-mono text-zinc-500">
                                No events found matching your search query. Try broadening your filter parameters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($events->hasPages())
            <div class="border-t border-zinc-800 px-5 py-4 bg-zinc-950">
                {{ $events->links() }}
            </div>
        @endif
    </div>

    <!-- Log Payload Inspector Modal -->
    <div x-cloak x-show="inspectModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" @click.self="inspectModalOpen = false" @keydown.escape.window="inspectModalOpen = false">
        <div class="w-full max-w-3xl rounded-lg border border-zinc-800 bg-zinc-950 shadow-2xl overflow-hidden font-mono text-xs max-h-[90vh] flex flex-col" x-show="inspectModalOpen" x-transition>
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-zinc-800 px-6 py-4 bg-zinc-900/60 flex-shrink-0">
                <div class="flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-cyan-400"></span>
                    <h3 class="font-bold text-zinc-100 uppercase tracking-wide">
                        Normalized Event Inspector #<span x-text="selectedLog?.id"></span>
                    </h3>
                </div>
                <button type="button" @click="inspectModalOpen = false" class="text-zinc-400 hover:text-white text-lg">&times;</button>
            </div>

            <!-- Modal Body (Scrollable) -->
            <div class="p-6 space-y-5 overflow-y-auto flex-1">
                <!-- Meta Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-zinc-900/70 p-4 rounded-lg border border-zinc-800 text-[11px]">
                    <div>
                        <span class="text-zinc-500 uppercase text-[10px] block">Timestamp</span>
                        <span class="text-zinc-200 font-semibold" x-text="selectedLog?.timestamp"></span>
                    </div>
                    <div>
                        <span class="text-zinc-500 uppercase text-[10px] block">Source IP</span>
                        <span class="text-cyan-400 font-semibold" x-text="selectedLog?.source_ip"></span>
                    </div>
                    <div>
                        <span class="text-zinc-500 uppercase text-[10px] block">Category</span>
                        <span class="text-zinc-200 uppercase font-semibold" x-text="selectedLog?.category"></span>
                    </div>
                    <div>
                        <span class="text-zinc-500 uppercase text-[10px] block">Severity</span>
                        <span class="font-bold uppercase" :class="{
                            'text-rose-400': selectedLog?.severity === 'critical',
                            'text-orange-400': selectedLog?.severity === 'high',
                            'text-amber-400': selectedLog?.severity === 'medium',
                            'text-zinc-400': selectedLog?.severity === 'info'
                        }" x-text="selectedLog?.severity"></span>
                    </div>
                </div>

                <!-- Action & User -->
                <div class="grid grid-cols-2 gap-3 text-[11px]">
                    <div class="rounded border border-zinc-800 p-3 bg-zinc-900/40">
                        <span class="text-zinc-500 uppercase text-[10px] block mb-1">Normalized Action</span>
                        <span class="font-bold text-cyan-300" x-text="selectedLog?.action"></span>
                    </div>
                    <div class="rounded border border-zinc-800 p-3 bg-zinc-900/40">
                        <span class="text-zinc-500 uppercase text-[10px] block mb-1">Target Node / User</span>
                        <span class="text-zinc-200" x-text="(selectedLog?.username !== '-' ? 'User: ' + selectedLog?.username + ' | ' : '') + 'Dest: ' + selectedLog?.dest_ip"></span>
                    </div>
                </div>

                <!-- Raw Log Payload Section -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-zinc-400 uppercase text-[10px] font-bold tracking-wider">Raw Ingest Message</span>
                        <button type="button" @click="copyRaw" class="text-cyan-400 hover:text-cyan-300 text-[11px] flex items-center gap-1">
                            <span x-text="copied ? 'COPIED TO CLIPBOARD!' : 'COPY RAW'"></span>
                        </button>
                    </div>
                    <div class="rounded-lg border border-zinc-800 bg-black p-4 text-[11px] text-zinc-300 overflow-x-auto whitespace-pre-wrap break-all leading-relaxed" x-text="selectedLog?.raw_message"></div>
                </div>

                <!-- Parsed Structured JSON -->
                <div>
                    <span class="text-zinc-400 uppercase text-[10px] font-bold tracking-wider block mb-2">Parsed JSON Attributes</span>
                    <pre class="rounded-lg border border-zinc-800 bg-zinc-900/90 p-4 text-[11px] text-cyan-200 overflow-x-auto max-h-48" x-text="JSON.stringify(selectedLog?.parsed_data, null, 2)"></pre>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-between border-t border-zinc-800 px-6 py-3.5 bg-zinc-900/40 flex-shrink-0">
                <a :href="'{{ route('alerts.index') }}?search=' + selectedLog?.source_ip" class="text-rose-400 hover:text-rose-300 text-xs">
                    CHECK IF THIS IP HAS ALERTS &rarr;
                </a>
                <button type="button" @click="inspectModalOpen = false" class="rounded border border-zinc-700 bg-zinc-800 px-4 py-1.5 text-xs text-zinc-300 hover:text-white transition-colors">
                    CLOSE
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
