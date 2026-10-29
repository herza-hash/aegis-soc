@extends('layouts.app', ['title' => 'Log File Upload & Analysis | Aegis SOC'])

@section('content')
<div class="space-y-6" x-data="{
    dragOver: false,
    selectedFile: null,
    fileName: '',
    fileSize: '',
    fileFormat: 'auto',
    isUploading: false,
    updateFile(event) {
        const file = event.target.files[0];
        if (file) {
            this.selectedFile = file;
            this.fileName = file.name;
            this.fileSize = this.formatSize(file.size);
            this.autoDetectFormat(file.name);
        }
    },
    handleDrop(event) {
        this.dragOver = false;
        const file = event.dataTransfer.files[0];
        if (file) {
            this.selectedFile = file;
            this.fileName = file.name;
            this.fileSize = this.formatSize(file.size);
            this.autoDetectFormat(file.name);
            // Set the file to the hidden input
            const dt = new DataTransfer();
            dt.items.add(file);
            document.getElementById('log_file_input').files = dt.files;
        }
    },
    autoDetectFormat(name) {
        const ext = name.split('.').pop().toLowerCase();
        if (ext === 'json') this.fileFormat = 'json_array';
        else if (ext === 'csv') this.fileFormat = 'csv';
        else this.fileFormat = 'line_by_line';
    },
    formatSize(bytes) {
        if (bytes >= 1048576) return (bytes / 1048576).toFixed(2) + ' MB';
        if (bytes >= 1024) return (bytes / 1024).toFixed(2) + ' KB';
        return bytes + ' B';
    },
    submitUpload() {
        if (this.selectedFile) {
            this.isUploading = true;
        }
    }
}">
    <!-- Header -->
    <div class="flex flex-col gap-4 border-b border-zinc-800/80 pb-6 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2 font-mono text-[11px] uppercase tracking-wider text-purple-400">
                <span class="inline-block h-1.5 w-1.5 rounded-full bg-purple-400"></span>
                <span>AEGIS / FILE UPLOAD & BATCH ANALYSIS</span>
            </div>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-zinc-100 sm:text-3xl">Log File Upload & Analysis</h1>
            <p class="mt-1 font-mono text-xs text-zinc-500">Upload log files from external sources, SIEM exports, or other security tools for automated parsing, normalization, and threat detection.</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('logs.index') }}" class="inline-flex items-center gap-1.5 rounded-md border border-zinc-700 bg-zinc-800/80 px-3 py-1.5 font-mono text-xs text-zinc-200 transition-colors hover:border-zinc-500 hover:bg-zinc-700 hover:text-white">
                <span>LOG EXPLORER →</span>
            </a>
            <a href="{{ route('alerts.index') }}" class="inline-flex items-center gap-1.5 rounded-md border border-zinc-700 bg-zinc-800/80 px-3 py-1.5 font-mono text-xs text-zinc-200 transition-colors hover:border-zinc-500 hover:bg-zinc-700 hover:text-white">
                <span>ALERTS →</span>
            </a>
        </div>
    </div>

    <!-- Pipeline Overview Cards -->
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-4">
            <span class="font-mono text-xs uppercase text-cyan-400">Raw Logs Stored</span>
            <span class="mt-2 block font-mono text-2xl font-extrabold text-cyan-300">{{ number_format($metrics['total_raw_logs']) }}</span>
            <span class="text-[11px] font-mono text-zinc-500">Total ingested to database</span>
        </div>
        <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-4">
            <span class="font-mono text-xs uppercase text-emerald-400">Normalized Events</span>
            <span class="mt-2 block font-mono text-2xl font-extrabold text-emerald-300">{{ number_format($metrics['total_events']) }}</span>
            <span class="text-[11px] font-mono text-zinc-500">Parsed & classified</span>
        </div>
        <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-4">
            <span class="font-mono text-xs uppercase text-rose-400">Alerts Generated</span>
            <span class="mt-2 block font-mono text-2xl font-extrabold text-rose-300">{{ number_format($metrics['total_alerts']) }}</span>
            <span class="text-[11px] font-mono text-zinc-500">From all uploaded logs</span>
        </div>
        <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-4">
            <span class="font-mono text-xs uppercase text-purple-400">Active Pipelines</span>
            <span class="mt-2 block font-mono text-2xl font-extrabold text-purple-300">{{ $metrics['active_sources'] }}</span>
            <span class="text-[11px] font-mono text-zinc-500">Online ingestion nodes</span>
        </div>
    </div>

    @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-transition class="flex items-center justify-between rounded-lg border border-rose-500/30 bg-rose-950/40 px-4 py-3 text-xs font-mono text-rose-300">
            <div class="flex items-center gap-2">
                <span class="h-2 w-2 rounded-full bg-rose-400"></span>
                <span>[ERROR]: {{ session('error') }}</span>
            </div>
            <button type="button" @click="show = false" class="text-rose-500 hover:text-rose-300">&times;</button>
        </div>
    @endif

    <!-- Upload Section -->
    <div class="rounded-lg border border-purple-900/40 bg-gradient-to-b from-purple-950/10 to-zinc-950/80 p-6">
        <div class="flex items-center justify-between border-b border-zinc-800/80 pb-3 mb-5">
            <div class="flex items-center gap-2">
                <span class="h-2 w-2 rounded-full bg-purple-400 animate-pulse"></span>
                <h2 class="text-sm font-semibold text-zinc-100 uppercase tracking-wide font-mono">Upload Log File for Analysis</h2>
            </div>
            <span class="rounded bg-purple-950/80 px-2.5 py-0.5 font-mono text-[10px] text-purple-300 border border-purple-800/60">
                BATCH INGEST
            </span>
        </div>

        <form method="POST" action="{{ route('upload.process') }}" enctype="multipart/form-data" @submit="submitUpload()" class="space-y-5">
            @csrf

            <!-- Drag & Drop Zone -->
            <div class="relative"
                 @dragover.prevent="dragOver = true"
                 @dragleave.prevent="dragOver = false"
                 @drop.prevent="handleDrop($event)">

                <label for="log_file_input" class="flex flex-col items-center justify-center gap-3 cursor-pointer rounded-lg border-2 border-dashed p-8 transition-all duration-200"
                       :class="dragOver ? 'border-purple-400 bg-purple-950/30' : (selectedFile ? 'border-emerald-500/50 bg-emerald-950/10' : 'border-zinc-700 bg-zinc-900/30 hover:border-purple-500/50 hover:bg-purple-950/10')">

                    <!-- Icon -->
                    <div class="flex h-16 w-16 items-center justify-center rounded-full transition-colors"
                         :class="selectedFile ? 'bg-emerald-950/60 border border-emerald-700' : 'bg-zinc-800 border border-zinc-700'">
                        <template x-if="!selectedFile">
                            <svg class="h-8 w-8 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                            </svg>
                        </template>
                        <template x-if="selectedFile">
                            <svg class="h-8 w-8 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </template>
                    </div>

                    <!-- Text Content -->
                    <template x-if="!selectedFile">
                        <div class="text-center">
                            <p class="font-mono text-sm font-semibold text-zinc-200">
                                <span class="text-purple-400">Click to browse</span> or drag & drop log file
                            </p>
                            <p class="mt-1 font-mono text-xs text-zinc-500">
                                Supported: <span class="text-zinc-400">.log</span>, <span class="text-zinc-400">.txt</span>, <span class="text-zinc-400">.csv</span>, <span class="text-zinc-400">.json</span> — Max 10 MB
                            </p>
                        </div>
                    </template>

                    <template x-if="selectedFile">
                        <div class="text-center">
                            <p class="font-mono text-sm font-semibold text-emerald-300" x-text="fileName"></p>
                            <p class="mt-1 font-mono text-xs text-zinc-400">
                                Size: <span class="text-zinc-200" x-text="fileSize"></span> — Ready for analysis
                            </p>
                        </div>
                    </template>
                </label>

                <input type="file" name="log_file" id="log_file_input" accept=".txt,.log,.csv,.json,.text" required
                       class="sr-only" @change="updateFile($event)">
            </div>

            <!-- Configuration Row -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 font-mono text-xs">
                <!-- Target Pipeline -->
                <div>
                    <label class="block text-[10px] uppercase text-zinc-500 mb-1.5">Target Ingestion Pipeline</label>
                    <select name="log_source_id" required class="w-full rounded border border-zinc-800 bg-zinc-900 px-3 py-2 text-zinc-200 focus:border-purple-500 focus:outline-none">
                        @foreach ($sources as $source)
                            <option value="{{ $source->id }}">
                                {{ $source->name }} ({{ $source->ip_address }})
                            </option>
                        @endforeach
                        @if($sources->isEmpty())
                            <option value="" disabled>No active sources — register one first</option>
                        @endif
                    </select>
                </div>

                <!-- File Format -->
                <div>
                    <label class="block text-[10px] uppercase text-zinc-500 mb-1.5">File Format</label>
                    <select name="file_format" x-model="fileFormat" class="w-full rounded border border-zinc-800 bg-zinc-900 px-3 py-2 text-zinc-200 focus:border-purple-500 focus:outline-none">
                        <option value="auto">Auto-Detect (by extension)</option>
                        <option value="line_by_line">Line-by-Line (syslog, access.log)</option>
                        <option value="json_array">JSON Array / NDJSON</option>
                        <option value="csv">CSV (comma-separated)</option>
                    </select>
                </div>

                <!-- Format Help -->
                <div class="flex items-end">
                    <div class="w-full rounded border border-zinc-800/50 bg-zinc-900/50 px-3 py-2 text-zinc-500">
                        <span class="block text-[10px] uppercase text-zinc-600 mb-0.5">Selected Format</span>
                        <template x-if="fileFormat === 'auto'"><span class="text-purple-300">Auto-detect from file extension</span></template>
                        <template x-if="fileFormat === 'line_by_line'"><span class="text-cyan-300">Each line = 1 raw log entry</span></template>
                        <template x-if="fileFormat === 'json_array'"><span class="text-amber-300">JSON array or NDJSON objects</span></template>
                        <template x-if="fileFormat === 'csv'"><span class="text-emerald-300">CSV rows (skip header)</span></template>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex flex-col gap-3 border-t border-zinc-800/80 pt-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="font-mono text-[11px] text-zinc-500 space-y-1">
                    <p><i class="fa-solid fa-clipboard-list text-cyan-400 mr-1.5"></i>Each line/entry is processed through the <span class="text-zinc-300">full SIEM pipeline</span>: Parser → Normalizer → Detection Engine</p>
                    <p><i class="fa-solid fa-bolt text-cyan-400 mr-1.5"></i>Detection rules are correlated in <span class="text-cyan-300">real-time</span> — alerts are generated immediately for matching threats.</p>
                </div>
                <button type="submit" :disabled="!selectedFile || isUploading"
                        class="inline-flex items-center justify-center gap-2 rounded bg-purple-600 px-6 py-2 font-mono text-xs font-semibold text-white hover:bg-purple-500 transition-colors disabled:opacity-40 disabled:cursor-not-allowed whitespace-nowrap"
                        :class="isUploading ? 'animate-pulse' : ''">
                    <template x-if="!isUploading">
                        <span><i class="fa-solid fa-cloud-arrow-up mr-1.5"></i>UPLOAD & ANALYZE</span>
                    </template>
                    <template x-if="isUploading">
                        <span><i class="fa-solid fa-spinner fa-spin mr-1.5"></i>PROCESSING PIPELINE...</span>
                    </template>
                </button>
            </div>
        </form>
    </div>

    <!-- Supported Formats Documentation -->
    <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-5">
        <h3 class="text-sm font-semibold text-zinc-100 mb-4 font-mono uppercase tracking-wide">Supported Log Formats & Sources</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Syslog / Auth Logs -->
            <div class="rounded-lg border border-cyan-900/40 bg-cyan-950/10 p-4">
                <div class="flex items-center gap-2 mb-2">
                    <span class="rounded bg-cyan-950/80 border border-cyan-800/60 px-2 py-0.5 text-[10px] font-bold text-cyan-300 font-mono uppercase">SYSLOG</span>
                </div>
                <p class="font-mono text-[11px] text-zinc-300 mb-2">Linux auth.log, syslog, sshd events</p>
                <div class="rounded bg-black/60 p-2 font-mono text-[10px] text-cyan-400 border border-zinc-800/50 overflow-x-auto">
                    <code>Failed password for root from 185.220.101.45 port 22 ssh2</code>
                </div>
            </div>

            <!-- Web Access Logs -->
            <div class="rounded-lg border border-amber-900/40 bg-amber-950/10 p-4">
                <div class="flex items-center gap-2 mb-2">
                    <span class="rounded bg-amber-950/80 border border-amber-800/60 px-2 py-0.5 text-[10px] font-bold text-amber-300 font-mono uppercase">WEB LOG</span>
                </div>
                <p class="font-mono text-[11px] text-zinc-300 mb-2">Nginx, Apache access.log (CLF/Combined)</p>
                <div class="rounded bg-black/60 p-2 font-mono text-[10px] text-amber-400 border border-zinc-800/50 overflow-x-auto">
                    <code>10.0.1.5 - - [29/Oct/2026:13:30:15 +0000] "GET /.env" 404</code>
                </div>
            </div>

            <!-- JSON Logs -->
            <div class="rounded-lg border border-emerald-900/40 bg-emerald-950/10 p-4">
                <div class="flex items-center gap-2 mb-2">
                    <span class="rounded bg-emerald-950/80 border border-emerald-800/60 px-2 py-0.5 text-[10px] font-bold text-emerald-300 font-mono uppercase">JSON</span>
                </div>
                <p class="font-mono text-[11px] text-zinc-300 mb-2">Structured JSON, Cloud audit, NDJSON</p>
                <div class="rounded bg-black/60 p-2 font-mono text-[10px] text-emerald-400 border border-zinc-800/50 overflow-x-auto">
                    <code>{"level":"error","ip":"10.0.0.1","msg":"auth failed"}</code>
                </div>
            </div>

            <!-- CSV Logs -->
            <div class="rounded-lg border border-purple-900/40 bg-purple-950/10 p-4">
                <div class="flex items-center gap-2 mb-2">
                    <span class="rounded bg-purple-950/80 border border-purple-800/60 px-2 py-0.5 text-[10px] font-bold text-purple-300 font-mono uppercase">CSV</span>
                </div>
                <p class="font-mono text-[11px] text-zinc-300 mb-2">SIEM exports, firewall CSV dumps</p>
                <div class="rounded bg-black/60 p-2 font-mono text-[10px] text-purple-400 border border-zinc-800/50 overflow-x-auto">
                    <code>2026-10-29,10.0.1.5,DROP,445,SSH brute force</code>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Batch History -->
    <div class="rounded-lg border border-zinc-800 bg-zinc-950 overflow-hidden">
        <div class="flex items-center justify-between border-b border-zinc-800 px-5 py-3.5">
            <div>
                <h2 class="text-sm font-semibold text-zinc-100">Upload Batch History</h2>
                <p class="font-mono text-[11px] text-zinc-500">Recent file uploads and their analysis results</p>
            </div>
            <span class="rounded bg-zinc-900 px-2 py-0.5 font-mono text-[10px] text-zinc-400 border border-zinc-800">
                Last {{ count($uploadBatches) }} batches
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[700px] text-left text-xs font-mono">
                <thead class="border-b border-zinc-800 bg-zinc-900/60 uppercase text-zinc-400">
                    <tr>
                        <th class="px-4 py-3 font-normal">Batch ID</th>
                        <th class="px-4 py-3 font-normal">Source File</th>
                        <th class="px-4 py-3 font-normal">Lines Processed</th>
                        <th class="px-4 py-3 font-normal">Severity Breakdown</th>
                        <th class="px-4 py-3 font-normal">Alerts</th>
                        <th class="px-4 py-3 font-normal">Timestamp</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/60">
                    @forelse ($uploadBatches as $batch)
                        <tr class="transition-colors hover:bg-zinc-900/40">
                            <td class="px-4 py-3">
                                <span class="rounded bg-purple-950/60 border border-purple-800/40 px-2 py-0.5 text-[10px] font-bold text-purple-300">
                                    {{ $batch['batch_id'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-zinc-200">
                                {{ $batch['first_event']->parsed_data['source_file'] ?? 'Unknown' }}
                            </td>
                            <td class="px-4 py-3 text-zinc-300">
                                <span class="font-bold">{{ $batch['total_lines'] }}</span> entries
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1.5">
                                    @if(isset($batch['severities']['critical']) && $batch['severities']['critical'] > 0)
                                        <span class="rounded bg-rose-950/60 border border-rose-800/40 px-1.5 py-0.5 text-[9px] font-bold text-rose-400">{{ $batch['severities']['critical'] }} CRIT</span>
                                    @endif
                                    @if(isset($batch['severities']['high']) && $batch['severities']['high'] > 0)
                                        <span class="rounded bg-orange-950/60 border border-orange-800/40 px-1.5 py-0.5 text-[9px] font-bold text-orange-400">{{ $batch['severities']['high'] }} HIGH</span>
                                    @endif
                                    @if(isset($batch['severities']['medium']) && $batch['severities']['medium'] > 0)
                                        <span class="rounded bg-amber-950/60 border border-amber-800/40 px-1.5 py-0.5 text-[9px] font-bold text-amber-400">{{ $batch['severities']['medium'] }} MED</span>
                                    @endif
                                    @if(isset($batch['severities']['low']) && $batch['severities']['low'] > 0)
                                        <span class="rounded bg-cyan-950/60 border border-cyan-800/40 px-1.5 py-0.5 text-[9px] font-bold text-cyan-400">{{ $batch['severities']['low'] }} LOW</span>
                                    @endif
                                    @if(isset($batch['severities']['info']) && $batch['severities']['info'] > 0)
                                        <span class="rounded bg-zinc-900 border border-zinc-800 px-1.5 py-0.5 text-[9px] font-bold text-zinc-400">{{ $batch['severities']['info'] }} INFO</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @if($batch['alerts_triggered'] > 0)
                                    <span class="inline-flex items-center gap-1 rounded bg-rose-950/60 border border-rose-800/40 px-2 py-0.5 text-[10px] font-bold text-rose-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-rose-400 animate-pulse"></span>
                                        {{ $batch['alerts_triggered'] }}
                                    </span>
                                @else
                                    <span class="text-zinc-500">0</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-zinc-400">
                                {{ $batch['first_event']->timestamp?->diffForHumans() ?? 'N/A' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-zinc-500">
                                <div class="space-y-2">
                                    <p class="text-sm">No log files have been uploaded yet.</p>
                                    <p class="text-[11px]">Upload a <span class="text-purple-400">.log</span>, <span class="text-purple-400">.txt</span>, <span class="text-purple-400">.csv</span>, or <span class="text-purple-400">.json</span> file above to start analyzing external logs.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- How It Works: Pipeline Documentation -->
    <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-5">
        <h3 class="text-sm font-semibold text-zinc-100 mb-4 font-mono uppercase tracking-wide">How the Analysis Pipeline Works</h3>

        <div class="grid grid-cols-1 gap-0.5 sm:grid-cols-5">
            <!-- Step 1: Upload -->
            <div class="relative flex flex-col items-center p-4">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-purple-950/80 border border-purple-800/60 text-purple-300 font-mono font-bold text-sm">1</div>
                <div class="mt-2 text-center">
                    <p class="font-mono text-xs font-semibold text-purple-300">UPLOAD</p>
                    <p class="mt-1 font-mono text-[10px] text-zinc-500">Log file is read & split into individual entries</p>
                </div>
                <div class="hidden sm:block absolute right-0 top-1/2 -translate-y-1/2 text-zinc-700 text-lg">→</div>
            </div>

            <!-- Step 2: Ingest -->
            <div class="relative flex flex-col items-center p-4">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-cyan-950/80 border border-cyan-800/60 text-cyan-300 font-mono font-bold text-sm">2</div>
                <div class="mt-2 text-center">
                    <p class="font-mono text-xs font-semibold text-cyan-300">INGEST</p>
                    <p class="mt-1 font-mono text-[10px] text-zinc-500">Stored as RawLog with checksum verification</p>
                </div>
                <div class="hidden sm:block absolute right-0 top-1/2 -translate-y-1/2 text-zinc-700 text-lg">→</div>
            </div>

            <!-- Step 3: Parse -->
            <div class="relative flex flex-col items-center p-4">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-950/80 border border-amber-800/60 text-amber-300 font-mono font-bold text-sm">3</div>
                <div class="mt-2 text-center">
                    <p class="font-mono text-xs font-semibold text-amber-300">PARSE</p>
                    <p class="mt-1 font-mono text-[10px] text-zinc-500">Auto-routed through WebLog, SSH, or JSON parser</p>
                </div>
                <div class="hidden sm:block absolute right-0 top-1/2 -translate-y-1/2 text-zinc-700 text-lg">→</div>
            </div>

            <!-- Step 4: Normalize -->
            <div class="relative flex flex-col items-center p-4">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-950/80 border border-emerald-800/60 text-emerald-300 font-mono font-bold text-sm">4</div>
                <div class="mt-2 text-center">
                    <p class="font-mono text-xs font-semibold text-emerald-300">NORMALIZE</p>
                    <p class="mt-1 font-mono text-[10px] text-zinc-500">Structured event with IP, severity, category</p>
                </div>
                <div class="hidden sm:block absolute right-0 top-1/2 -translate-y-1/2 text-zinc-700 text-lg">→</div>
            </div>

            <!-- Step 5: Detect -->
            <div class="flex flex-col items-center p-4">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-rose-950/80 border border-rose-800/60 text-rose-300 font-mono font-bold text-sm">5</div>
                <div class="mt-2 text-center">
                    <p class="font-mono text-xs font-semibold text-rose-300">DETECT</p>
                    <p class="mt-1 font-mono text-[10px] text-zinc-500">Correlated against detection rules → Alerts</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Sample Files Documentation -->
    <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-5">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-zinc-100 font-mono uppercase tracking-wide">Sample Log Files for Testing</h3>
            <span class="font-mono text-[10px] text-zinc-500">Copy & save as a file to test upload</span>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <!-- SSH Brute Force Sample -->
            <div>
                <span class="block font-mono text-[10px] uppercase text-rose-400 mb-1.5"><i class="fa-solid fa-key text-rose-400 mr-1.5"></i>SSH Brute Force Simulation (auth.log)</span>
                <div class="rounded bg-black p-3 font-mono text-[10px] text-zinc-300 border border-zinc-800 overflow-x-auto max-h-36 overflow-y-auto">
                    <pre>Failed password for invalid user root from 185.220.101.45 port 42812 ssh2
Failed password for admin from 45.154.255.89 port 38110 ssh2
Failed password for invalid user root from 194.26.29.112 port 59910 ssh2
Accepted publickey for lead_analyst from 10.0.1.105 port 55122 ssh2: RSA SHA256:7uKm
Failed password for invalid user ubnt from 91.240.118.17 port 33201 ssh2
Failed password for support from 198.51.100.84 port 49102 ssh2
Failed password for invalid user root from 185.220.101.45 port 42818 ssh2</pre>
                </div>
            </div>

            <!-- Web Access Log Sample -->
            <div>
                <span class="block font-mono text-[10px] uppercase text-amber-400 mb-1.5"><i class="fa-solid fa-globe text-amber-400 mr-1.5"></i>Web Attack Probe (access.log)</span>
                <div class="rounded bg-black p-3 font-mono text-[10px] text-zinc-300 border border-zinc-800 overflow-x-auto max-h-36 overflow-y-auto">
                    <pre>185.220.101.45 - - [29/Oct/2026:13:30:15 +0000] "GET /.env HTTP/1.1" 404 196 "-" "curl/7.68.0"
194.26.29.112 - - [29/Oct/2026:13:02:40 +0000] "GET /wp-admin/setup-config.php HTTP/1.1" 404 153 "-" "Nikto/2.1.6"
10.0.1.105 - - [29/Oct/2026:12:45:00 +0000] "GET /api/v1/dashboard HTTP/1.1" 200 4820 "-" "Mozilla/5.0"
185.220.101.45 - - [29/Oct/2026:12:15:33 +0000] "GET /api/v1/users?id=1%20UNION%20SELECT HTTP/1.1" 500 524 "-" "sqlmap/1.5.2"
45.154.255.89 - - [29/Oct/2026:04:12:08 +0000] "GET /cgi-bin/test.cgi HTTP/1.1" 404 180 "-" "Shellshock-Scanner"</pre>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
