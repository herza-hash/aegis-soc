<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Incident Dossier #SEC-{{ str_pad($alert->id, 4, '0', STR_PAD_LEFT) }} | Aegis SOC</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background-color: #ffffff !important; color: #09090b !important; }
            .print-card { border-color: #e4e4e7 !important; background-color: #ffffff !important; color: #09090b !important; }
            .print-text-dark { color: #09090b !important; }
            .print-bg-light { background-color: #f4f4f5 !important; }
        }
    </style>
</head>
<body class="bg-zinc-950 text-zinc-100 font-sans antialiased min-h-screen p-4 sm:p-8">
    <!-- Action Controls (Hidden during Print) -->
    <div class="no-print max-w-4xl mx-auto mb-6 flex flex-wrap items-center justify-between gap-3 border-b border-zinc-800 pb-4">
        <a href="{{ route('alerts.index') }}" class="font-mono text-xs text-zinc-400 hover:text-white flex items-center gap-1.5">
            &larr; BACK TO INCIDENT QUEUE
        </a>

        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="inline-flex items-center gap-2 rounded-md bg-cyan-600 px-4 py-2 font-mono text-xs font-semibold text-white hover:bg-cyan-500 transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>PRINT / SAVE AS PDF</span>
            </button>
        </div>
    </div>

    <!-- Formal Printable Dossier Container -->
    <div class="max-w-4xl mx-auto rounded-xl border border-zinc-800 bg-zinc-900/90 p-8 sm:p-12 print-card space-y-8 font-mono text-xs">
        <!-- Document Header -->
        <div class="border-b-2 border-zinc-700 pb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-cyan-400 font-bold tracking-widest text-sm uppercase">
                    <span>AEGIS CYBER DEFENSE OPERATIONS CENTER</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-100 tracking-tight mt-1 print-text-dark">
                    FORENSIC INCIDENT ASSESSMENT DOSSIER
                </h1>
                <p class="text-zinc-500 text-[11px] mt-0.5">DOCUMENT ID: DOSSIER-{{ date('Y') }}-{{ str_pad($alert->id, 5, '0', STR_PAD_LEFT) }}</p>
            </div>

            <div class="text-right">
                <span class="inline-block rounded border border-rose-800 bg-rose-950/80 px-2.5 py-1 text-[11px] font-bold text-rose-300 uppercase">
                    TLP:AMBER // RESTRICTED
                </span>
                <span class="block text-zinc-400 text-[10px] mt-1.5">ASSESSMENT GENERATED: {{ now()->utc()->format('Y-m-d H:i') }} UTC</span>
            </div>
        </div>

        <!-- 1. Executive Summary -->
        <div class="space-y-3">
            <h2 class="text-xs uppercase font-bold tracking-wider text-cyan-400 border-b border-zinc-800 pb-1">
                1. Executive Summary & Case Classification
            </h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-black/50 print-bg-light p-4 rounded-lg border border-zinc-800">
                <div>
                    <span class="text-zinc-500 text-[10px] uppercase block">Incident ID</span>
                    <span class="font-bold text-zinc-200 print-text-dark text-sm">#SEC-{{ str_pad($alert->id, 4, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div>
                    <span class="text-zinc-500 text-[10px] uppercase block">Severity Rating</span>
                    <span class="font-bold text-sm uppercase {{ $alert->severity === 'critical' ? 'text-rose-400' : 'text-orange-400' }}">
                        {{ $alert->severity }}
                    </span>
                </div>
                <div>
                    <span class="text-zinc-500 text-[10px] uppercase block">Current Lifecycle</span>
                    <span class="font-bold text-zinc-200 print-text-dark text-sm uppercase">{{ $alert->status }}</span>
                </div>
                <div>
                    <span class="text-zinc-500 text-[10px] uppercase block">Trigger Timestamp</span>
                    <span class="text-zinc-300 print-text-dark text-[11px]">{{ $alert->triggered_at?->format('Y-m-d H:i:s') }} UTC</span>
                </div>
                <div class="col-span-2 sm:col-span-4 mt-2 pt-2 border-t border-zinc-800">
                    <span class="text-zinc-500 text-[10px] uppercase block">Triggered Heuristic / Rule</span>
                    <span class="font-bold text-zinc-100 print-text-dark text-sm">{{ $alert->detectionRule->name ?? 'Correlated Security Alert' }}</span>
                    <p class="text-zinc-400 text-[11px] mt-0.5">
                        Detection Model: {{ strtoupper($alert->detectionRule->condition_type ?? 'anomaly') }} 
                        (Threshold: &ge; {{ $alert->detectionRule->threshold ?? 1 }} occurrences in {{ $alert->detectionRule->time_window_minutes ?? 1 }}m)
                    </p>
                </div>
            </div>
        </div>

        <!-- 2. Threat Actor Profile & Attribution -->
        <div class="space-y-3">
            <h2 class="text-xs uppercase font-bold tracking-wider text-cyan-400 border-b border-zinc-800 pb-1">
                2. Threat Actor Attribution & Geolocation
            </h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-black/50 print-bg-light p-4 rounded-lg border border-zinc-800">
                <div>
                    <span class="text-zinc-500 text-[10px] uppercase block">Originating IP</span>
                    <span class="font-bold text-rose-400 text-sm">{{ $alert->source_ip }}</span>
                </div>
                <div>
                    <span class="text-zinc-500 text-[10px] uppercase block">Country of Origin</span>
                    <span class="font-bold text-zinc-200 print-text-dark text-sm">
                        {{ $threatIntel['flag'] }} {{ $threatIntel['country'] }} ({{ $threatIntel['country_code'] }})
                    </span>
                </div>
                <div>
                    <span class="text-zinc-500 text-[10px] uppercase block">Threat Classification</span>
                    <span class="rounded bg-rose-950/80 border border-rose-800 px-1.5 py-0.5 text-[10px] font-bold text-rose-300 uppercase">
                        {{ $threatIntel['threat_tag'] }}
                    </span>
                </div>
                <div>
                    <span class="text-zinc-500 text-[10px] uppercase block">Risk Score Rating</span>
                    <span class="font-bold text-sm {{ $threatIntel['risk_score'] > 75 ? 'text-rose-400' : 'text-amber-400' }}">
                        {{ $threatIntel['risk_score'] }} / 100
                    </span>
                </div>
                <div class="col-span-2 sm:col-span-4 mt-1">
                    <span class="text-zinc-500 text-[10px] uppercase block">Autonomous System (ASN / ISP)</span>
                    <span class="text-zinc-300 print-text-dark">{{ $threatIntel['asn'] }}</span>
                </div>
            </div>
        </div>

        <!-- 3. Forensics Telemetry & Correlated Artifacts -->
        <div class="space-y-3">
            <h2 class="text-xs uppercase font-bold tracking-wider text-cyan-400 border-b border-zinc-800 pb-1">
                3. Correlated Forensic Evidence (Telemetry Logs)
            </h2>
            <div class="overflow-x-auto rounded-lg border border-zinc-800">
                <table class="w-full text-left text-[11px]">
                    <thead class="bg-zinc-900 print-bg-light uppercase text-zinc-400 border-b border-zinc-800">
                        <tr>
                            <th class="p-2.5">Time (UTC)</th>
                            <th class="p-2.5">Category</th>
                            <th class="p-2.5">Action & Payload</th>
                            <th class="p-2.5">Outcome</th>
                            <th class="p-2.5">Severity</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800/80">
                        @forelse ($correlatedEvents as $ev)
                            <tr>
                                <td class="p-2.5 text-zinc-400 whitespace-nowrap">{{ $ev->timestamp?->format('H:i:s') ?? 'N/A' }}</td>
                                <td class="p-2.5 uppercase font-bold text-zinc-300">{{ $ev->event_category }}</td>
                                <td class="p-2.5 text-zinc-200 print-text-dark font-mono max-w-md truncate" title="{{ $ev->rawLog->raw_message ?? '' }}">
                                    <span class="text-cyan-300 font-semibold">{{ $ev->event_action }}</span>
                                    <span class="block text-[10px] text-zinc-500 truncate">{{ $ev->rawLog->raw_message ?? '' }}</span>
                                </td>
                                <td class="p-2.5 font-bold uppercase {{ $ev->event_outcome === 'blocked' ? 'text-purple-400' : ($ev->event_outcome === 'failure' ? 'text-rose-400' : 'text-emerald-400') }}">
                                    {{ $ev->event_outcome }}
                                </td>
                                <td class="p-2.5 uppercase font-bold {{ $ev->severity === 'critical' ? 'text-rose-400' : 'text-orange-400' }}">
                                    {{ $ev->severity }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-4 text-center text-zinc-500">No correlated events recorded.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 4. Containment Status & SOAR Response -->
        <div class="space-y-3">
            <h2 class="text-xs uppercase font-bold tracking-wider text-cyan-400 border-b border-zinc-800 pb-1">
                4. Containment & Mitigation Actions (SOAR Enforcement)
            </h2>
            <div class="bg-black/50 print-bg-light p-4 rounded-lg border border-zinc-800 space-y-2">
                @if($containment)
                    <div class="flex items-center gap-2 text-rose-400 font-bold">
                        <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                        <span>ACTIVE CONTAINMENT ENFORCED (INGRESS TRAFFIC QUARANTINED)</span>
                    </div>
                    <p class="text-zinc-300 print-text-dark text-[11px]">
                        Reason: {{ $containment->reason }} &bull; Enforced by: {{ $containment->blocked_by }} &bull;
                        {{ $containment->expires_at ? 'Expires ' . $containment->expires_at->diffForHumans() : 'PERMANENT BLOCKLIST' }}
                    </p>
                    <div class="rounded bg-zinc-950 p-2.5 border border-zinc-800 font-mono text-[10px] text-cyan-300 mt-2">
                        <code>iptables -I INPUT 1 -s {{ $alert->source_ip }} -j DROP -m comment --comment "Aegis-SOAR"</code>
                    </div>
                @else
                    <div class="flex items-center gap-2 text-amber-400 font-bold">
                        <span>● NO ACTIVE FIREWALL CONTAINMENT CURRENTLY ENFORCED</span>
                    </div>
                    <p class="text-zinc-400 text-[11px]">
                        IP address is currently operating under standard inspection telemetry. Containment can be enacted via the Active Defense portal.
                    </p>
                @endif
            </div>
        </div>

        <!-- 5. Chain of Custody & Sign-off -->
        <div class="border-t-2 border-zinc-700 pt-6 space-y-6">
            <h2 class="text-xs uppercase font-bold tracking-wider text-zinc-400">
                5. Chain of Custody & Authorization Signatures
            </h2>
            <div class="grid grid-cols-2 gap-8 pt-4">
                <div class="border-t border-zinc-600 pt-2">
                    <span class="block font-bold text-zinc-200 print-text-dark">Lead Incident Investigator</span>
                    <span class="text-[10px] text-zinc-500">Tier 2 SOC Incident Response Team</span>
                    <span class="block text-[10px] text-zinc-400 mt-4">Signature: _______________________</span>
                </div>
                <div class="border-t border-zinc-600 pt-2">
                    <span class="block font-bold text-zinc-200 print-text-dark">Security Operations Center Director</span>
                    <span class="text-[10px] text-zinc-500">Aegis Cyber Defense Authority</span>
                    <span class="block text-[10px] text-zinc-400 mt-4">Signature: _______________________</span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
