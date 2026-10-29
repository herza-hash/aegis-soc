@extends('layouts.app', ['title' => $pageTitle.' | Aegis SOC'])

@section('content')
    <div class="mb-6">
        <p class="mb-1 font-mono text-[11px] uppercase tracking-wider text-cyan-500">Aegis / Workspace</p>
        <h1 class="text-xl font-semibold text-zinc-100">{{ $pageTitle }}</h1>
    </div>
    <section class="border border-zinc-800 px-4 py-8">
        <p class="font-mono text-sm text-zinc-500">No records to display.</p>
    </section>
@endsection