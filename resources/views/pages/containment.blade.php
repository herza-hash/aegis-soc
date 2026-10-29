@extends('layouts.app', ['title' => __('Active Defense & SOAR | Aegis SOC')])

@section('content')
<script>
function containmentApp() {
    return {
        blockModalOpen: false,
        activeFwTab: 'iptables',
        rules: @json($firewallRules),
        copied: false,
        copyFw() {
            const text = this.rules[this.activeFwTab] || '';
            navigator.clipboard.writeText(text);
            this.copied = true;
            setTimeout(() => this.copied = false, 2000);
        }
    };
}
</script>

<div class="space-y-6" x-data="containmentApp()">
    <!-- Header -->
    <div class="flex flex-col gap-4 border-b border-zinc-800/80 pb-6 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2 font-mono text-[11px] uppercase tracking-wider text-rose-400">
                <span class="inline-block h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                <span>{{ __('AEGIS / SOAR AUTOMATED CONTAINMENT') }}</span>
            </div>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-zinc-100 sm:text-3xl">{{ __('Active Defense & Firewall Quarantine') }}</h1>
        </div>

        <div class="flex items-center gap-3">
            <button type="button" @click="blockModalOpen = true" class="inline-flex items-center gap-1.5 rounded-md bg-rose-600 px-3.5 py-1.5 font-mono text-xs font-semibold text-white hover:bg-rose-500 transition-colors">
                <span>+ {{ __('QUARANTINE IP') }}</span>
            </button>
        </div>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <!-- Active Blacklisted IPs -->
        <div class="rounded-lg border border-rose-900/40 bg-zinc-950 p-4">
            <div class="flex items-center justify-between">
                <span class="font-mono text-xs uppercase text-rose-400 flex items-center gap-1.5">
                    <span class="h-1.5 w-1.5 rounded-full bg-rose-400 animate-pulse"></span> {{ __('Active Blacklist') }}
                </span>
                <span class="rounded bg-rose-950/80 px-2 py-0.5 font-mono text-[10px] text-rose-300 border border-rose-800/60">
                    {{ __('INGRESS DROP') }}
                </span>
            </div>
            <span class="mt-2 block font-mono text-3xl font-extrabold text-rose-200">{{ $metrics['active'] }}</span>
            <span class="text-[11px] font-mono text-zinc-500">{{ __('Live Quarantined IPs') }}</span>
        </div>

        <!-- Mitigated Attacks -->
        <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-4">
            <span class="font-mono text-xs uppercase text-purple-400">{{ __('Dropped Ingress Events') }}</span>
            <span class="mt-2 block font-mono text-3xl font-extrabold text-purple-300">{{ number_format($metrics['mitigated_attacks']) }}</span>
            <span class="text-[11px] font-mono text-zinc-500">{{ __('Blocked by policy rule') }}</span>
        </div>

        <!-- Permanent Blocks -->
        <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-4">
            <span class="font-mono text-xs uppercase text-zinc-400">{{ __('Permanent Quarantine') }}</span>
            <span class="mt-2 block font-mono text-3xl font-extrabold text-zinc-100">{{ $metrics['permanent'] }}</span>
            <span class="text-[11px] font-mono text-zinc-500">{{ __('No expiration time') }}</span>
        </div>

        <!-- Temporary Quarantine -->
        <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-4">
            <span class="font-mono text-xs uppercase text-amber-400">{{ __('Time-Bound Quarantine') }}</span>
            <span class="mt-2 block font-mono text-3xl font-extrabold text-amber-300">{{ $metrics['quarantined'] }}</span>
            <span class="text-[11px] font-mono text-zinc-500">{{ __('Expiring isolation') }}</span>
        </div>
    </div>

    <!-- Automated Firewall Rules Export (SOAR Integration) -->
    <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-b border-zinc-800 pb-3 mb-4">
            <div>
                <h2 class="text-sm font-semibold text-zinc-100 font-mono uppercase tracking-wide">{{ __('Automated Firewall Enforcement Sync (SOAR)') }}</h2>
                <p class="font-mono text-xs text-zinc-500">{{ __('Export active containment blackhole rules for edge routers & perimeter firewalls') }}</p>
            </div>

            <!-- Tab Switcher -->
            <div class="flex items-center gap-1 font-mono text-xs bg-zinc-900 p-1 rounded-md border border-zinc-800">
                <button type="button" @click="activeFwTab = 'iptables'" class="rounded px-2.5 py-1 transition-colors" :class="activeFwTab === 'iptables' ? 'bg-cyan-600 text-white font-semibold' : 'text-zinc-400 hover:text-white'">
                    iptables
                </button>
                <button type="button" @click="activeFwTab = 'ufw'" class="rounded px-2.5 py-1 transition-colors" :class="activeFwTab === 'ufw' ? 'bg-cyan-600 text-white font-semibold' : 'text-zinc-400 hover:text-white'">
                    UFW
                </button>
                <button type="button" @click="activeFwTab = 'nftables'" class="rounded px-2.5 py-1 transition-colors" :class="activeFwTab === 'nftables' ? 'bg-cyan-600 text-white font-semibold' : 'text-zinc-400 hover:text-white'">
                    nftables
                </button>
                <button type="button" @click="activeFwTab = 'cisco'" class="rounded px-2.5 py-1 transition-colors" :class="activeFwTab === 'cisco' ? 'bg-cyan-600 text-white font-semibold' : 'text-zinc-400 hover:text-white'">
                    Cisco ACL
                </button>
            </div>
        </div>

        <!-- Terminal Output -->
        <div class="relative rounded-lg border border-zinc-800 bg-black p-4 font-mono text-xs">
            <div class="flex items-center justify-between mb-2 pb-2 border-b border-zinc-900 text-[10px] text-zinc-500">
                <span x-text="'{{ __('RULESET GENERATOR:') }} ' + activeFwTab.toUpperCase()"></span>
                <button type="button" @click="copyFw" class="text-cyan-400 hover:text-cyan-300 font-bold flex items-center gap-1">
                    <span x-text="copied ? '{{ __('COPIED TO CLIPBOARD!') }}' : '{{ __('COPY FIREWALL SCRIPT') }}'"></span>
                </button>
            </div>
            <pre class="overflow-x-auto text-cyan-300 whitespace-pre leading-relaxed max-h-36" x-text="rules[activeFwTab] || '{{ __('# No active IP blocks in blacklist.') }}'"></pre>
        </div>
    </div>

    <!-- Blocked IPs Table -->
    <div class="overflow-hidden rounded-lg border border-zinc-800 bg-zinc-950">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-b border-zinc-800 px-5 py-3.5">
            <div>
                <h2 class="text-sm font-semibold text-zinc-100">{{ __('Quarantine & Blacklist Repository') }}</h2>
                <span class="font-mono text-xs text-zinc-500">{{ __('Tracking') }} {{ $blockedIps->total() }} {{ __('containment records') }}</span>
            </div>

            <!-- Filter / Search Form -->
            <form method="GET" action="{{ route('containment.index') }}" class="flex items-center gap-2 font-mono text-xs">
                <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="{{ __('Search IP or reason...') }}" class="rounded border border-zinc-800 bg-zinc-900 px-2.5 py-1 text-zinc-200 focus:border-cyan-500 focus:outline-none">
                <select name="status" class="rounded border border-zinc-800 bg-zinc-900 px-2.5 py-1 text-zinc-200 focus:border-cyan-500 focus:outline-none">
                    <option value="all" {{ $filters['status'] === 'all' ? 'selected' : '' }}>{{ __('All') }}</option>
                    <option value="active" {{ $filters['status'] === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                    <option value="revoked" {{ $filters['status'] === 'revoked' ? 'selected' : '' }}>{{ __('Revoked') }}</option>
                </select>
                <button type="submit" class="rounded bg-zinc-800 hover:bg-zinc-700 px-3 py-1 text-zinc-200">
                    {{ __('FILTER') }}
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[840px] text-left text-xs font-mono">
                <thead class="border-b border-zinc-800 bg-zinc-900/60 uppercase text-zinc-400">
                    <tr>
                        <th class="px-4 py-3 font-normal">{{ __('Quarantined IP') }}</th>
                        <th class="px-4 py-3 font-normal">{{ __('Containment Reason') }}</th>
                        <th class="px-4 py-3 font-normal">{{ __('Enforced By') }}</th>
                        <th class="px-4 py-3 font-normal">{{ __('Duration / Expiry') }}</th>
                        <th class="px-4 py-3 font-normal">{{ __('Status') }}</th>
                        <th class="px-4 py-3 font-normal text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y border-zinc-800/60 divide-zinc-800/60">
                    @forelse ($blockedIps as $item)
                        <tr class="transition-colors hover:bg-zinc-900/40">
                            <!-- IP with Geolocation -->
                            <td class="px-4 py-3.5">
                                @php
                                    $intel = \App\Services\ThreatIntelService::lookup($item->ip_address);
                                @endphp
                                <div class="flex items-center gap-2">
                                    <span class="text-base select-none leading-none" title="{{ $intel['country'] }} ({{ $intel['asn'] }})"><i class="{{ $intel['icon'] ?? 'fa-solid fa-globe text-cyan-400' }}"></i></span>
                                    <div>
                                        <span class="font-bold text-rose-400 block">{{ $item->ip_address }}</span>
                                        <span class="text-[9px] text-zinc-500 uppercase">{{ __($intel['country']) }} &bull; {{ $intel['threat_tag'] }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Reason -->
                            <td class="px-4 py-3.5 text-zinc-300 max-w-xs truncate" title="{{ $item->reason }}">
                                {{ $item->reason }}
                            </td>

                            <!-- Enforced By -->
                            <td class="px-4 py-3.5 text-zinc-400">
                                {{ $item->blocked_by }}
                                <span class="block text-[10px] text-zinc-600">{{ $item->created_at->diffForHumans() }}</span>
                            </td>

                            <!-- Expiry -->
                            <td class="px-4 py-3.5">
                                @if($item->expires_at)
                                    <span class="text-amber-300 font-semibold">{{ $item->expires_at->diffForHumans() }}</span>
                                    <span class="block text-[10px] text-zinc-500">{{ $item->expires_at->format('Y-m-d H:i') }} UTC</span>
                                @else
                                    <span class="text-rose-400 font-bold uppercase">{{ __('PERMANENT') }}</span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="px-4 py-3.5">
                                @if($item->isActive())
                                    <span class="inline-flex items-center gap-1 rounded-full bg-rose-950/70 px-2 py-0.5 text-[10px] font-bold text-rose-400 border border-rose-800/60">
                                        <span class="h-1.5 w-1.5 rounded-full bg-rose-400 animate-pulse"></span> {{ __('INGRESS DROP') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-zinc-900 px-2 py-0.5 text-[10px] font-bold text-zinc-500 border border-zinc-800">
                                        {{ __('REVOKED') }}
                                    </span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="px-4 py-3.5 text-right">
                                @if($item->status === 'active')
                                    <form method="POST" action="{{ route('containment.unblock', $item) }}" class="inline" onsubmit="return confirm('{{ __('Revoke containment and restore traffic from') }} {{ $item->ip_address }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded border border-emerald-800/80 bg-emerald-950/60 px-2.5 py-1 text-[11px] font-semibold text-emerald-300 hover:bg-emerald-900 transition-colors">
                                            {{ __('UNBLOCK') }}
                                        </button>
                                    </form>
                                @else
                                    <span class="text-[10px] text-zinc-600">{{ __('Archived') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-zinc-500">
                                {{ __('No IP addresses currently quarantined. Click "+ Quarantine IP" to isolate a threat.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($blockedIps->hasPages())
            <div class="border-t border-zinc-800 px-5 py-4 bg-zinc-950">
                {{ $blockedIps->links() }}
            </div>
        @endif
    </div>

    <!-- Quarantine Modal -->
    <div x-cloak x-show="blockModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" @click.self="blockModalOpen = false" @keydown.escape.window="blockModalOpen = false">
        <div class="w-full max-w-lg rounded-lg border border-zinc-800 bg-zinc-950 overflow-hidden font-mono text-xs" x-show="blockModalOpen" x-transition>
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-zinc-800 px-5 py-4 bg-zinc-900/60">
                <h3 class="font-bold text-zinc-100 uppercase tracking-wide flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-rose-500 animate-pulse"></span>
                    {{ __('Quarantine New Suspicious IP Address') }}
                </h3>
                <button type="button" @click="blockModalOpen = false" class="text-zinc-400 hover:text-white text-lg">&times;</button>
            </div>

            <!-- Body -->
            <form method="POST" action="{{ route('containment.block') }}" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-[10px] uppercase text-zinc-500 mb-1">{{ __('Target IP Address to Quarantine') }}</label>
                    <input type="text" name="ip_address" placeholder="e.g. 185.220.101.45" required class="w-full rounded border border-zinc-800 bg-zinc-900 px-3 py-2 text-rose-300 font-bold focus:border-rose-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-[10px] uppercase text-zinc-500 mb-1">{{ __('Containment Reason / Justification') }}</label>
                    <input type="text" name="reason" placeholder="{{ __('e.g. Repeated automated Brute Force attacks against Auth Gateway') }}" required class="w-full rounded border border-zinc-800 bg-zinc-900 px-3 py-2 text-zinc-200 focus:border-rose-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] uppercase text-zinc-500 mb-1">{{ __('Quarantine Duration') }}</label>
                        <select name="duration" required class="w-full rounded border border-zinc-800 bg-zinc-900 px-3 py-2 text-zinc-200 focus:border-rose-500 focus:outline-none">
                            <option value="1h">1 {{ __('Hour') }} ({{ __('Cooling period') }})</option>
                            <option value="24h" selected>24 {{ __('Hours') }} ({{ __('Standard Quarantine') }})</option>
                            <option value="7d">7 {{ __('Days') }} ({{ __('Extended Threat Lock') }})</option>
                            <option value="permanent">{{ __('Permanent Blacklist') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] uppercase text-zinc-500 mb-1">{{ __('Enforced By') }}</label>
                        <input type="text" name="blocked_by" value="SOC Analyst (Incident Containment)" class="w-full rounded border border-zinc-800 bg-zinc-900 px-3 py-2 text-zinc-200 focus:border-rose-500 focus:outline-none">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-zinc-800/80 pt-4">
                    <button type="button" @click="blockModalOpen = false" class="rounded border border-zinc-700 bg-zinc-800 px-3.5 py-1.5 text-xs text-zinc-300 hover:text-white transition-colors">
                        {{ __('CANCEL') }}
                    </button>
                    <button type="submit" class="rounded bg-rose-600 px-4 py-1.5 text-xs font-semibold text-white hover:bg-rose-500 transition-colors">
                        {{ __('ENFORCE CONTAINMENT') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
