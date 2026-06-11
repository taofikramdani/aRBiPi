<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'aRBiPi') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f5f6fa] text-slate-700 antialiased">
@php
    $isAdmin = auth()->user()->hasRole('admin');
    $navIcon = fn ($path) => '<svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="'.$path.'"/></svg>';
@endphp
<div class="min-h-screen lg:flex">
    <aside class="border-b border-slate-100 bg-white lg:fixed lg:inset-y-0 lg:w-64 lg:border-b-0 lg:border-r">
        <div class="flex h-[72px] items-center gap-3 border-b border-slate-100 px-6">
            <span class="grid size-10 place-items-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-500 text-sm font-extrabold text-white shadow-lg shadow-indigo-200">aR</span>
            <div><b class="text-slate-900">aRBiPi</b><p class="text-[11px] text-slate-400">Rumah Belajar Pintar</p></div>
        </div>
        <div class="flex items-center gap-3 border-b border-slate-100 px-6 py-5">
            <span class="grid size-10 place-items-center rounded-full bg-indigo-50 font-bold text-indigo-600">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
            <div class="min-w-0"><p class="truncate text-sm font-semibold text-slate-800">{{ auth()->user()->name }}</p><p class="text-xs capitalize text-slate-400">{{ $isAdmin ? 'Administrator' : 'Siswa' }}</p></div>
        </div>
        <nav class="flex gap-1 overflow-x-auto p-3 lg:block lg:space-y-1 lg:p-4">
            <a class="nav-link {{ request()->routeIs('dashboard') ? 'nav-link-active' : '' }}" href="{{ route('dashboard') }}">{!! $navIcon('M4 13h6V4H4v9Zm10 7h6v-9h-6v9ZM4 20h6v-3H4v3Zm10-13h6V4h-6v3Z') !!}<span>Dashboard</span></a>
            @role('admin')
            <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'nav-link-active' : '' }}" href="{{ route('admin.users.index') }}">{!! $navIcon('M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m7-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm13 10v-2a4 4 0 0 0-3-3.87m0-7.26a4 4 0 0 1 0 7.75') !!}<span>Pengguna</span></a>
            <a class="nav-link {{ request()->routeIs('admin.subjects.*') ? 'nav-link-active' : '' }}" href="{{ route('admin.subjects.index') }}">{!! $navIcon('M4 19.5A2.5 2.5 0 0 1 6.5 17H20M4 4.5A2.5 2.5 0 0 1 6.5 2H20v15H6.5A2.5 2.5 0 0 0 4 19.5Zm0-15v15') !!}<span>Mata Pelajaran</span></a>
            <a class="nav-link {{ request()->routeIs('admin.learning-materials.*') ? 'nav-link-active' : '' }}" href="{{ route('admin.learning-materials.index') }}">{!! $navIcon('M6 2h9l5 5v15H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2Zm8 1v5h5M8 13h8m-8 4h8') !!}<span>Materi Pembelajaran</span></a>
            <a class="nav-link {{ request()->routeIs('admin.questions.*') ? 'nav-link-active' : '' }}" href="{{ route('admin.questions.index') }}">{!! $navIcon('M9 11h6m-6 4h4M9 7h6m4 14H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h10l4 4v12a2 2 0 0 1-2 2Z') !!}<span>Bank Soal</span></a>
            <a class="nav-link {{ request()->routeIs('admin.tryouts.*') ? 'nav-link-active' : '' }}" href="{{ route('admin.tryouts.index') }}">{!! $navIcon('m9 12 2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z') !!}<span>Paket Tryout</span></a>
            <a class="nav-link {{ request()->routeIs('admin.results.*') ? 'nav-link-active' : '' }}" href="{{ route('admin.results.index') }}">{!! $navIcon('M3 3v18h18M7 16v-4m5 4V8m5 8V5') !!}<span>Hasil Peserta</span></a>
            @else
            <a class="nav-link {{ request()->routeIs('student.learning-materials.*') ? 'nav-link-active' : '' }}" href="{{ route('student.learning-materials.index') }}">{!! $navIcon('M6 2h9l5 5v15H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2Zm8 1v5h5M8 13h8m-8 4h8') !!}<span>Materi Pembelajaran</span></a>
            <a class="nav-link {{ request()->routeIs('student.tryouts.*') ? 'nav-link-active' : '' }}" href="{{ route('student.tryouts.index') }}">{!! $navIcon('m9 12 2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z') !!}<span>Mulai Tryout</span></a>
            <a class="nav-link {{ request()->routeIs('student.results.*') ? 'nav-link-active' : '' }}" href="{{ route('student.results.index') }}">{!! $navIcon('M3 3v18h18M7 16v-4m5 4V8m5 8V5') !!}<span>Riwayat Saya</span></a>
            @endrole
        </nav>
    </aside>
    <div class="min-w-0 flex-1 lg:ml-64">
        <header class="flex h-[72px] items-center justify-between border-b border-slate-100 bg-white px-5 lg:px-7">
            <div class="hidden w-64 items-center gap-2 rounded-full bg-slate-100 px-4 py-2 text-slate-400 sm:flex">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
                <span class="text-xs">Cari menu...</span>
            </div>
            <div class="ml-auto flex items-center gap-4">
                <span class="relative grid size-9 place-items-center rounded-full text-slate-400 hover:bg-slate-50"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9Zm-8 13h4"/></svg><i class="absolute right-1 top-1 size-2 rounded-full bg-rose-500"></i></span>
                <div class="hidden text-right sm:block"><p class="text-sm font-semibold text-slate-700">{{ auth()->user()->name }}</p><p class="text-[11px] capitalize text-slate-400">{{ $isAdmin ? 'Administrator' : 'Siswa' }}</p></div>
                <span class="grid size-9 place-items-center rounded-full bg-indigo-100 text-sm font-bold text-indigo-600">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="text-xs font-semibold text-slate-400 hover:text-rose-500">Keluar</button></form>
            </div>
        </header>
        <main class="p-5 lg:p-7">
            <div class="mb-6"><h1 class="text-xl font-semibold text-slate-800">{{ $header ?? 'aRBiPi' }}</h1><p class="mt-1 text-xs text-slate-400"></p></div>
            @if(session('success'))<div class="mb-5 rounded-lg bg-emerald-50 p-4 text-sm text-emerald-700">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="mb-5 rounded-lg bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</div>@endif
            {{ $slot }}
        </main>
    </div>
</div>
@unless($isAdmin || request()->routeIs('student.attempts.show'))
    <x-student-assistant />
@endunless
</body>
</html>
