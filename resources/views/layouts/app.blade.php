<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-zinc-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? __('Security Operations Center | Aegis SOC') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" referrerpolicy="no-referrer" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-zinc-950 text-zinc-100 antialiased" x-data="{ navOpen: false }">
    <div x-cloak x-show="navOpen" class="fixed inset-0 z-40 bg-black/60 lg:hidden" @click="navOpen = false" @keydown.escape.window="navOpen = false"></div>

    <aside class="fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col border-r border-zinc-800 bg-zinc-950 transition-transform duration-200 lg:translate-x-0" :class="navOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
        <div class="flex h-16 items-center gap-3 border-b border-zinc-800 px-5">
            <span class="grid h-8 w-8 place-items-center border border-cyan-800 bg-cyan-950/60 font-mono text-sm font-bold text-cyan-300">
                <i class="fa-solid fa-shield-halved text-cyan-400"></i>
            </span>
            <div>
                <p class="text-sm font-semibold tracking-wide text-zinc-100">AEGIS <span class="font-normal text-zinc-500">SOC</span></p>
                <p class="font-mono text-[10px] uppercase text-zinc-500">{{ __('Security operations') }}</p>
            </div>
        </div>

        @php
            $navigation = [
                ['label' => __('Dashboard'), 'route' => 'dashboard', 'icon' => 'fa-solid fa-gauge-high'],
                ['label' => __('Log Explorer'), 'route' => 'logs.index', 'icon' => 'fa-solid fa-magnifying-glass'],
                ['label' => __('Upload Logs'), 'route' => 'upload.index', 'badge' => 'NEW', 'icon' => 'fa-solid fa-cloud-arrow-up'],
                ['label' => __('Security Alerts'), 'route' => 'alerts.index', 'icon' => 'fa-solid fa-triangle-exclamation'],
                ['label' => __('Active Defense'), 'route' => 'containment.index', 'badge' => 'SOAR', 'icon' => 'fa-solid fa-shield-halved'],
                ['label' => __('Log Sources'), 'route' => 'sources.index', 'icon' => 'fa-solid fa-server'],
                ['label' => __('System Settings'), 'route' => 'settings.index', 'icon' => 'fa-solid fa-sliders'],
            ];
        @endphp
        <nav class="flex-1 space-y-1 px-3 py-5" aria-label="Primary navigation">
            <p class="px-3 pb-2 font-mono text-[10px] uppercase tracking-wider text-zinc-600">{{ __('Workspace') }}</p>
            @foreach ($navigation as $item)
                @php
                    $isActive = request()->routeIs($item['route']) || request()->routeIs(\Illuminate\Support\Str::before($item['route'], '.') . '.*');
                @endphp
                <a href="{{ route($item['route']) }}" @click="navOpen = false" class="flex h-9 items-center justify-between border-l-2 px-3 text-sm transition-colors {{ $isActive ? 'border-cyan-400 bg-zinc-900 text-zinc-100 font-medium' : 'border-transparent text-zinc-400 hover:bg-zinc-900 hover:text-zinc-200' }}">
                    <div class="flex items-center gap-2.5">
                        <i class="{{ $item['icon'] }} w-4 text-center {{ $isActive ? 'text-cyan-400' : 'text-zinc-500' }}"></i>
                        <span>{{ $item['label'] }}</span>
                    </div>
                    @if(!empty($item['badge']))
                        <span class="rounded px-1.5 py-0.5 font-mono text-[9px] font-bold
                            {{ $item['badge'] === 'NEW' ? 'bg-purple-950/80 border border-purple-800/60 text-purple-400' : 'bg-rose-950/80 border border-rose-800/60 text-rose-400' }}">
                            {{ $item['badge'] }}
                        </span>
                    @endif
                </a>
            @endforeach
        </nav>

        <div class="border-t border-zinc-800 px-5 py-4">
            <div class="flex items-center justify-between text-xs">
                <span class="text-zinc-500"><i class="fa-solid fa-microchip mr-1.5 text-zinc-600"></i>{{ __('Queue driver') }}</span>
                <span class="font-mono text-cyan-400">{{ strtoupper(config('queue.default')) }}</span>
            </div>
        </div>
    </aside>

    <div class="lg:pl-64">
        <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-zinc-800 bg-zinc-950/95 px-4 backdrop-blur-sm sm:px-6">
            <div class="flex items-center gap-3">
                <button type="button" class="grid h-9 w-9 place-items-center border border-zinc-800 text-zinc-300 hover:bg-zinc-900 lg:hidden" @click="navOpen = !navOpen" :aria-expanded="navOpen" aria-label="Toggle navigation">
                    <span class="flex flex-col gap-1" aria-hidden="true">
                        <span class="h-px w-4 bg-current"></span>
                        <span class="h-px w-4 bg-current"></span>
                        <span class="h-px w-4 bg-current"></span>
                    </span>
                </button>
                <span class="font-mono text-xs text-zinc-500">{{ request()->path() === '/' ? __('OVERVIEW') : __(strtoupper(request()->path())) }}</span>
            </div>
            <div class="flex items-center gap-4">
                <!-- Language Switcher (EN / ID) -->
                <div class="flex items-center rounded border border-zinc-800 bg-zinc-900 p-0.5 font-mono text-[10px]">
                    <a href="{{ route('locale.switch', 'en') }}" class="rounded px-2 py-0.5 transition-colors {{ app()->getLocale() === 'en' ? 'bg-cyan-600 text-white font-bold' : 'text-zinc-500 hover:text-zinc-300' }}" title="English">EN</a>
                    <a href="{{ route('locale.switch', 'id') }}" class="rounded px-2 py-0.5 transition-colors {{ app()->getLocale() === 'id' ? 'bg-cyan-600 text-white font-bold' : 'text-zinc-500 hover:text-zinc-300' }}" title="Bahasa Indonesia">ID</a>
                </div>

                <span class="hidden md:block h-5 border-l border-zinc-800"></span>

                <!-- Wallboard Live Auto-Refresh Controller -->
                <div x-data="{
                    autoRefresh: localStorage.getItem('aegis_auto_refresh') || 'off',
                    timer: null,
                    countdown: 0,
                    setMode(mode) {
                        this.autoRefresh = mode;
                        localStorage.setItem('aegis_auto_refresh', mode);
                        if (this.timer) clearInterval(this.timer);
                        if (mode !== 'off') {
                            this.countdown = parseInt(mode);
                            this.timer = setInterval(() => {
                                this.countdown--;
                                if (this.countdown <= 0) {
                                    if (window.aegisHasLazyLoader) {
                                        window.dispatchEvent(new CustomEvent('aegis-refresh'));
                                        this.countdown = parseInt(mode);
                                    } else {
                                        window.location.reload();
                                    }
                                }
                            }, 1000);
                        }
                    },
                    init() {
                        if (this.autoRefresh !== 'off') {
                            this.setMode(this.autoRefresh);
                        }
                    }
                }" class="hidden md:flex items-center gap-2 font-mono text-xs">
                    <span class="text-zinc-500 text-[10px]"><i class="fa-solid fa-arrows-rotate mr-1"></i>{{ __('WALLBOARD SYNC:') }}</span>
                    <div class="flex items-center rounded border border-zinc-800 bg-zinc-900 p-0.5">
                        <button type="button" @click="setMode('off')" class="rounded px-2 py-0.5 text-[10px] transition-colors" :class="autoRefresh === 'off' ? 'bg-zinc-800 text-zinc-300 font-bold' : 'text-zinc-500 hover:text-zinc-300'">OFF</button>
                        <button type="button" @click="setMode('10')" class="rounded px-2 py-0.5 text-[10px] transition-colors" :class="autoRefresh === '10' ? 'bg-cyan-600 text-white font-bold' : 'text-zinc-500 hover:text-zinc-300'">10s</button>
                        <button type="button" @click="setMode('30')" class="rounded px-2 py-0.5 text-[10px] transition-colors" :class="autoRefresh === '30' ? 'bg-cyan-600 text-white font-bold' : 'text-zinc-500 hover:text-zinc-300'">30s</button>
                    </div>
                    <span x-show="autoRefresh !== 'off'" class="text-[10px] text-cyan-400 font-bold" x-text="countdown + 's'"></span>
                </div>

                <span class="hidden md:block h-5 border-l border-zinc-800"></span>

                <div class="hidden items-center gap-2 sm:flex">
                    <span class="h-1.5 w-1.5 rounded-full bg-cyan-400"></span>
                    <span class="font-mono text-xs text-zinc-400"><i class="fa-solid fa-circle-nodes mr-1 text-cyan-400"></i>{{ __('AEGIS NODE ONLINE') }}</span>
                </div>
                <span class="h-5 border-l border-zinc-800"></span>
                <span class="font-mono text-xs text-zinc-500"><i class="fa-regular fa-clock mr-1"></i>UTC {{ now()->utc()->format('H:i') }}</span>
            </div>
        </header>

        <main class="min-h-[calc(100vh-4rem)] px-4 py-6 sm:px-6 lg:px-8">
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-transition class="mb-5 flex items-center justify-between rounded-lg border border-emerald-500/30 bg-emerald-950/40 px-4 py-3 text-xs font-mono text-emerald-300">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-emerald-400"></i>
                        <span>[ACTION_LOG]: {{ session('success') }}</span>
                    </div>
                    <button type="button" @click="show = false" class="text-emerald-500 hover:text-emerald-300">&times;</button>
                </div>
            @endif

            @if(session('error'))
                <div x-data="{ show: true }" x-show="show" x-transition class="mb-5 flex items-center justify-between rounded-lg border border-rose-500/30 bg-rose-950/40 px-4 py-3 text-xs font-mono text-rose-300">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-exclamation text-rose-400"></i>
                        <span>[ERROR]: {{ session('error') }}</span>
                    </div>
                    <button type="button" @click="show = false" class="text-rose-500 hover:text-rose-300">&times;</button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
