@extends('layouts.app', ['title' => __('Log Sources & Ingest | Aegis SOC')])

@section('content')
<script>
function sourcesApp() {
    return {
        createModalOpen: false,
        selectedPreset: null,
        targetSourceId: '{{ $sources->first()?->id ?? '' }}',
        rawMessage: '',
        presets: @json($presets),
        loadPreset(index) {
            const p = this.presets[index];
            if (p) {
                this.selectedPreset = index;
                this.rawMessage = p.message;
            }
        }
    };
}
</script>

<div class="space-y-6" x-data="sourcesApp()">
    <!-- Header -->
    <div class="flex flex-col gap-4 border-b border-zinc-800/80 pb-6 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2 font-mono text-[11px] uppercase tracking-wider text-emerald-400">
                <span class="inline-block h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                <span>{{ __('AEGIS / INGESTION TOPOLOGY') }}</span>
            </div>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-zinc-100 sm:text-3xl">{{ __('Log Sources & Ingestion Pipelines') }}</h1>
        </div>

        <div class="flex items-center gap-3">
            <button type="button" @click="createModalOpen = true" class="inline-flex items-center gap-1.5 rounded-md bg-emerald-600 px-3.5 py-1.5 font-mono text-xs font-semibold text-white hover:bg-emerald-500 transition-colors">
                <span>+ {{ __('REGISTER SOURCE') }}</span>
            </button>
        </div>
    </div>

    <!-- Overview Cards -->
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <!-- Connected Sources -->
        <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-4">
            <span class="font-mono text-xs uppercase text-zinc-400">{{ __('Total Pipelines') }}</span>
            <span class="mt-2 block font-mono text-2xl font-extrabold text-zinc-100">{{ $metrics['total'] }}</span>
            <span class="text-[11px] font-mono text-zinc-500">{{ $metrics['active'] }} {{ __('Online') }} &bull; {{ $metrics['inactive'] }} {{ __('Inactive') }}</span>
        </div>

        <!-- Ingested Volume -->
        <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-4">
            <span class="font-mono text-xs uppercase text-cyan-400">{{ __('Raw Logs Ingested') }}</span>
            <span class="mt-2 block font-mono text-2xl font-extrabold text-cyan-300">{{ number_format($metrics['total_logs']) }}</span>
            <span class="text-[11px] font-mono text-zinc-500">{{ __('Persisted in database') }}</span>
        </div>

        <!-- Protocol Breakdown -->
        <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-4">
            <span class="font-mono text-xs uppercase text-emerald-400">{{ __('Syslog Nodes') }}</span>
            <span class="mt-2 block font-mono text-2xl font-extrabold text-emerald-300">{{ $metrics['syslog'] }}</span>
            <span class="text-[11px] font-mono text-zinc-500">{{ __('UDP/TCP 514 streams') }}</span>
        </div>

        <!-- File & API Breakdown -->
        <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-4">
            <span class="font-mono text-xs uppercase text-purple-400">{{ __('File & API Agents') }}</span>
            <span class="mt-2 block font-mono text-2xl font-extrabold text-purple-300">{{ $metrics['file'] + $metrics['api'] }}</span>
            <span class="text-[11px] font-mono text-zinc-500">{{ $metrics['file'] }} {{ __('File') }} &bull; {{ $metrics['api'] }} REST API</span>
        </div>
    </div>

    <!-- Interactive Live Ingestion Test Bench / Simulator -->
    <div class="rounded-lg border border-cyan-900/40 bg-zinc-950 p-5">
        <div class="flex items-center justify-between border-b border-zinc-800/80 pb-3 mb-4">
            <div class="flex items-center gap-2">
                <span class="h-2 w-2 rounded-full bg-cyan-400 animate-pulse"></span>
                <h2 class="text-sm font-semibold text-zinc-100 uppercase tracking-wide font-mono">{{ __('Live Ingestion Simulator & Test Bench') }}</h2>
            </div>
            <span class="rounded bg-cyan-950/80 px-2.5 py-0.5 font-mono text-[10px] text-cyan-300 border border-cyan-800/60">
                {{ __('ACTIVE LAB') }}
            </span>
        </div>

        <p class="font-mono text-xs text-zinc-400 mb-4">
            {{ __('Test the end-to-end SIEM pipeline in real-time. Dispatching a log will immediately trigger the parser, generate a normalized telemetry event, and evaluate detection correlation rules.') }}
        </p>

        <!-- Presets Bar -->
        <div class="mb-4">
            <span class="block font-mono text-[10px] uppercase text-zinc-500 mb-1.5">{{ __('Load Attack & Activity Simulation Presets:') }}</span>
            <div class="flex flex-wrap gap-2">
                @foreach ($presets as $idx => $preset)
                    <button type="button" @click="loadPreset({{ $idx }})" class="rounded border border-zinc-800 bg-zinc-900/90 px-2.5 py-1 font-mono text-[11px] text-zinc-300 hover:border-cyan-500 hover:text-cyan-300 transition-colors" :class="selectedPreset === {{ $idx }} ? 'border-cyan-400 bg-cyan-950/50 text-cyan-200' : ''">
                        <i class="fa-solid fa-bolt text-amber-400 mr-1"></i> {{ $preset['title'] }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Simulator Form -->
        <form method="POST" action="{{ route('sources.simulate') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 font-mono text-xs">
                <!-- Target Source -->
                <div>
                    <label class="block text-[10px] uppercase text-zinc-500 mb-1">{{ __('Target Ingestion Node') }}</label>
                    <select name="log_source_id" x-model="targetSourceId" class="w-full rounded border border-zinc-800 bg-zinc-900 px-3 py-2 text-zinc-200 focus:border-cyan-500 focus:outline-none">
                        @foreach ($sources as $source)
                            <option value="{{ $source->id }}">
                                {{ $source->name }} ({{ $source->ip_address }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Raw Log Textarea (2 cols) -->
                <div class="sm:col-span-2">
                    <label class="block text-[10px] uppercase text-zinc-500 mb-1">{{ __('Raw Log Entry Message') }}</label>
                    <textarea name="raw_message" x-model="rawMessage" rows="2" placeholder="{{ __('Paste or type raw syslog / web / ssh log entry...') }}" required class="w-full rounded border border-zinc-800 bg-black px-3 py-2 text-cyan-200 font-mono text-xs focus:border-cyan-500 focus:outline-none"></textarea>
                </div>
            </div>

            <div class="flex items-center justify-between border-t border-zinc-800/80 pt-3">
                <span class="font-mono text-[11px] text-zinc-500">
                    {{ __('Runs synchronously') }} &bull; {{ __('Immediate rule correlation') }}
                </span>
                <button type="submit" class="inline-flex items-center gap-1.5 rounded bg-cyan-600 px-4 py-1.5 font-mono text-xs font-semibold text-white hover:bg-cyan-500 transition-colors">
                    <i class="fa-solid fa-paper-plane mr-1.5"></i> {{ __('DISPATCH LOG ENTRY') }}
                </button>
            </div>
        </form>
    </div>

    <!-- Sources Table -->
    <div class="overflow-hidden rounded-lg border border-zinc-800 bg-zinc-950">
        <div class="flex items-center justify-between border-b border-zinc-800 px-5 py-3.5">
            <h2 class="text-sm font-semibold text-zinc-100">{{ __('Registered Telemetry Endpoints') }}</h2>
            <span class="font-mono text-xs text-zinc-400">{{ $sources->count() }} {{ __('active connections') }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-left text-xs font-mono">
                <thead class="border-b border-zinc-800 bg-zinc-900/60 uppercase text-zinc-400">
                    <tr>
                        <th class="px-4 py-3 font-normal">{{ __('Source Name') }}</th>
                        <th class="px-4 py-3 font-normal">{{ __('IP Address') }}</th>
                        <th class="px-4 py-3 font-normal">{{ __('Transport Type') }}</th>
                        <th class="px-4 py-3 font-normal">{{ __('Logs Ingested') }}</th>
                        <th class="px-4 py-3 font-normal">{{ __('Status') }}</th>
                        <th class="px-4 py-3 font-normal text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/60">
                    @forelse ($sources as $source)
                        <tr class="transition-colors hover:bg-zinc-900/40">
                            <!-- Name -->
                            <td class="px-4 py-3 font-semibold text-zinc-200">
                                {{ $source->name }}
                            </td>

                            <!-- IP -->
                            <td class="px-4 py-3 text-cyan-400">
                                {{ $source->ip_address }}
                            </td>

                            <!-- Type -->
                            <td class="px-4 py-3">
                                @if($source->type === 'syslog')
                                    <span class="rounded bg-cyan-950/70 border border-cyan-800/60 px-2 py-0.5 text-[10px] font-bold text-cyan-300 uppercase">SYSLOG</span>
                                @elseif($source->type === 'file')
                                    <span class="rounded bg-amber-950/70 border border-amber-800/60 px-2 py-0.5 text-[10px] font-bold text-amber-300 uppercase">FILE TAIL</span>
                                @else
                                    <span class="rounded bg-purple-950/70 border border-purple-800/60 px-2 py-0.5 text-[10px] font-bold text-purple-300 uppercase">REST API</span>
                                @endif
                            </td>

                            <!-- Count -->
                            <td class="px-4 py-3 text-zinc-300">
                                <span class="font-bold">{{ number_format($source->raw_logs_count) }}</span> {{ __('logs') }}
                            </td>

                            <!-- Status -->
                            <td class="px-4 py-3">
                                @if($source->status === 'active')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-950/70 px-2 py-0.5 text-[10px] font-bold text-emerald-400 border border-emerald-800/60">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse"></span> {{ __('ONLINE') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-zinc-900 px-2 py-0.5 text-[10px] font-bold text-zinc-500 border border-zinc-800">
                                        {{ __('DISABLED') }}
                                    </span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <!-- Toggle Status Form -->
                                    <form method="POST" action="{{ route('sources.toggle', $source) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="rounded border border-zinc-700 bg-zinc-800 px-2 py-1 text-[10px] font-semibold text-zinc-300 hover:text-white transition-colors" title="Toggle Connection">
                                            {{ $source->status === 'active' ? __('DISABLE') : __('ENABLE') }}
                                        </button>
                                    </form>

                                    <!-- Delete Form -->
                                    <form method="POST" action="{{ route('sources.destroy', $source) }}" class="inline" onsubmit="return confirm('{{ __('Remove this log source? All associated raw logs will be detached.') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded border border-rose-900/60 bg-rose-950/40 px-2 py-1 text-[10px] font-semibold text-rose-400 hover:bg-rose-900 hover:text-white transition-colors">
                                            {{ __('DELETE') }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-zinc-500">
                                {{ __('No log sources configured. Click "+ Register Source" to add one.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- API Ingestion Guide Card -->
    <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-5">
        <h3 class="text-sm font-semibold text-zinc-200 mb-2 font-mono">{{ __('External Agent Ingestion API') }}</h3>
        <p class="font-mono text-xs text-zinc-400 mb-3">
            {{ __('External log shippers (e.g. Fluentd, Logstash, or custom bash scripts) can POST raw logs directly to the endpoint below without authentication for local testing:') }}
        </p>
        <div class="rounded bg-black p-3 font-mono text-xs text-cyan-300 border border-zinc-800 overflow-x-auto">
            <code>curl -X POST http://127.0.0.1:8000/api/logs/ingest -H "Content-Type: application/json" -d '{"raw_message": "Failed password for root from 185.220.101.45 port 22 ssh2"}'</code>
        </div>
    </div>

    <!-- Create Source Modal -->
    <div x-cloak x-show="createModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" @click.self="createModalOpen = false" @keydown.escape.window="createModalOpen = false">
        <div class="w-full max-w-lg rounded-lg border border-zinc-800 bg-zinc-950 overflow-hidden font-mono text-xs" x-show="createModalOpen" x-transition>
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-zinc-800 px-5 py-4 bg-zinc-900/60">
                <h3 class="font-bold text-zinc-100 uppercase tracking-wide">{{ __('Register New Log Source') }}</h3>
                <button type="button" @click="createModalOpen = false" class="text-zinc-400 hover:text-white text-lg">&times;</button>
            </div>

            <!-- Body -->
            <form method="POST" action="{{ route('sources.store') }}" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-[10px] uppercase text-zinc-500 mb-1">{{ __('Friendly Name') }}</label>
                    <input type="text" name="name" placeholder="e.g. Core App Server 01" required class="w-full rounded border border-zinc-800 bg-zinc-900 px-3 py-2 text-zinc-200 focus:border-cyan-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-[10px] uppercase text-zinc-500 mb-1">{{ __('IP Address') }}</label>
                    <input type="text" name="ip_address" placeholder="e.g. 192.168.1.55" required class="w-full rounded border border-zinc-800 bg-zinc-900 px-3 py-2 text-zinc-200 focus:border-cyan-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] uppercase text-zinc-500 mb-1">{{ __('Transport Type') }}</label>
                        <select name="type" required class="w-full rounded border border-zinc-800 bg-zinc-900 px-3 py-2 text-zinc-200 focus:border-cyan-500 focus:outline-none">
                            <option value="syslog">Syslog (UDP/TCP 514)</option>
                            <option value="file">File Tail / Agent</option>
                            <option value="api">REST Ingestion API</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] uppercase text-zinc-500 mb-1">{{ __('Initial Status') }}</label>
                        <select name="status" required class="w-full rounded border border-zinc-800 bg-zinc-900 px-3 py-2 text-zinc-200 focus:border-cyan-500 focus:outline-none">
                            <option value="active">{{ __('Active (Listening)') }}</option>
                            <option value="inactive">{{ __('Inactive') }}</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-zinc-800/80 pt-4">
                    <button type="button" @click="createModalOpen = false" class="rounded border border-zinc-700 bg-zinc-800 px-3.5 py-1.5 text-xs text-zinc-300 hover:text-white transition-colors">
                        {{ __('CANCEL') }}
                    </button>
                    <button type="submit" class="rounded bg-emerald-600 px-4 py-1.5 text-xs font-semibold text-white hover:bg-emerald-500 transition-colors">
                        {{ __('SAVE PIPELINE') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
