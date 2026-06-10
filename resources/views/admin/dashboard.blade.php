<x-app-layout>
<x-slot:header>Dashboard</x-slot:header>
@php
    $metricCards = [
        ['label' => 'Siswa', 'value' => number_format($students), 'caption' => 'Pengguna aktif', 'color' => 'from-[#ffae4c] to-[#ff9f43]', 'icon' => 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m7-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm13 10v-2a4 4 0 0 0-3-3.87m0-7.26a4 4 0 0 1 0 7.75'],
        ['label' => 'Mata Pelajaran', 'value' => number_format($subjects), 'caption' => 'Materi tersedia', 'color' => 'from-[#55d064] to-[#45c654]', 'icon' => 'M4 19.5A2.5 2.5 0 0 1 6.5 17H20M4 4.5A2.5 2.5 0 0 1 6.5 2H20v15H6.5A2.5 2.5 0 0 0 4 19.5Zm0-15v15'],
        ['label' => 'Bank Soal', 'value' => number_format($questions), 'caption' => 'Soal siap pakai', 'color' => 'from-[#ff6675] to-[#fb5263]', 'icon' => 'M9 11h6m-6 4h4M9 7h6m4 14H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h10l4 4v12a2 2 0 0 1-2 2Z'],
        ['label' => 'Paket Tryout', 'value' => number_format($tryouts), 'caption' => 'Evaluasi belajar', 'color' => 'from-[#2867ee] to-[#1759e8]', 'icon' => 'm9 12 2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
    ];
    $averageProgress = max(0, min(100, $average));
    $circumference = 289;
    $dashOffset = $circumference - ($circumference * $averageProgress / 100);
@endphp
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach($metricCards as $metric)
    <article class="relative overflow-hidden rounded-xl bg-gradient-to-br {{ $metric['color'] }} p-5 text-white shadow-lg shadow-slate-200/70">
        <div class="absolute -right-5 -top-8 size-28 rounded-full bg-white/10"></div>
        <div class="relative flex items-center justify-between">
            <div><p class="text-xs font-medium text-white/80">{{ $metric['label'] }}</p><p class="mt-1 text-2xl font-semibold">{{ $metric['value'] }}</p><p class="mt-2 text-[11px] text-white/70">{{ $metric['caption'] }}</p></div>
            <span class="grid size-12 place-items-center rounded-xl bg-white/15"><svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $metric['icon'] }}"/></svg></span>
        </div>
    </article>
    @endforeach
</div>

<div class="mt-5 grid gap-5 xl:grid-cols-[280px_1fr]">
    <section class="dashboard-panel overflow-hidden">
        <div class="border-b border-slate-100 px-5 py-4"><h2 class="text-sm font-semibold text-slate-700">Performa Rata-rata</h2><p class="mt-1 text-[11px] text-slate-400">Nilai seluruh peserta</p></div>
        <div class="grid place-items-center px-5 py-7">
            <div class="relative size-40">
                <svg class="size-40 -rotate-90" viewBox="0 0 110 110"><circle cx="55" cy="55" r="46" fill="none" stroke="#eef1f7" stroke-width="7"/><circle cx="55" cy="55" r="46" fill="none" stroke="#2463eb" stroke-width="7" stroke-linecap="round" stroke-dasharray="{{ $circumference }}" stroke-dashoffset="{{ $dashOffset }}"/></svg>
                <div class="absolute inset-0 grid place-items-center text-center"><div><strong class="text-2xl font-semibold text-slate-700">{{ $average }}%</strong><p class="text-[10px] text-slate-400">Rata-rata</p></div></div>
            </div>
        </div>
        <div class="flex items-center gap-2 border-t border-slate-100 px-5 py-4 text-xs text-slate-400"><span class="size-2 rounded-full bg-blue-600"></span> Nilai tryout peserta</div>
    </section>

    <section class="dashboard-panel overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4"><div><h2 class="text-sm font-semibold text-slate-700">Aktivitas Pembelajaran</h2><p class="mt-1 text-[11px] text-slate-400">Ringkasan ekosistem belajar aRBiPi</p></div><span class="rounded-full bg-emerald-50 px-3 py-1 text-[10px] font-semibold text-emerald-600">Live Data</span></div>
        <div class="relative min-h-[265px] overflow-hidden bg-gradient-to-b from-white to-slate-50 px-6 py-6">
            <div class="absolute inset-x-6 bottom-10 top-8 flex flex-col justify-between">@foreach(range(1,4) as $line)<span class="border-t border-dashed border-slate-200"></span>@endforeach</div>
            <svg class="relative h-[190px] w-full" viewBox="0 0 700 190" preserveAspectRatio="none">
                <defs><linearGradient id="activityArea" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#4f7cf3" stop-opacity=".28"/><stop offset="100%" stop-color="#4f7cf3" stop-opacity="0"/></linearGradient></defs>
                <path d="M0 155 C70 150 100 115 165 125 S260 65 335 95 S430 118 505 65 S610 78 700 25 V190 H0Z" fill="url(#activityArea)"/>
                <path d="M0 155 C70 150 100 115 165 125 S260 65 335 95 S430 118 505 65 S610 78 700 25" fill="none" stroke="#4776ed" stroke-width="3" stroke-linecap="round"/>
                @foreach([[0,155],[165,125],[335,95],[505,65],[700,25]] as $point)<circle cx="{{ $point[0] }}" cy="{{ $point[1] }}" r="4" fill="#fff" stroke="#4776ed" stroke-width="3"/>@endforeach
            </svg>
            <div class="relative mt-3 grid grid-cols-4 text-center text-[10px] text-slate-400"><span>Siswa</span><span>Mata Pelajaran</span><span>Bank Soal</span><span>Tryout</span></div>
        </div>
    </section>
</div>

<div class="mt-5 grid gap-5 xl:grid-cols-[1fr_330px]">
    <section class="dashboard-panel overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4"><h2 class="text-sm font-semibold text-slate-700">Hasil Tryout Terbaru</h2><a href="{{ route('admin.results.index') }}" class="text-xs font-semibold text-indigo-600">Lihat semua</a></div>
        <div class="divide-y divide-slate-100">
            @forelse($recentResults as $result)
            <div class="flex items-center gap-3 px-5 py-4"><span class="grid size-9 place-items-center rounded-full bg-indigo-50 text-xs font-semibold text-indigo-600">{{ strtoupper(substr($result->user->name, 0, 1)) }}</span><div class="min-w-0 flex-1"><p class="truncate text-sm font-medium text-slate-700">{{ $result->user->name }}</p><p class="truncate text-[11px] text-slate-400">{{ $result->tryout->title }}</p></div><span class="rounded-lg bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-600">{{ $result->score }}</span></div>
            @empty
            <div class="grid min-h-36 place-items-center px-5 py-8 text-center"><div><span class="mx-auto grid size-11 place-items-center rounded-full bg-slate-50 text-slate-300"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 3v18h18M7 16v-4m5 4V8m5 8V5"/></svg></span><p class="mt-3 text-sm text-slate-400">Belum ada hasil tryout.</p></div></div>
            @endforelse
        </div>
    </section>
    <section class="dashboard-panel p-5">
        <h2 class="text-sm font-semibold text-slate-700">Komposisi Konten</h2><p class="mt-1 text-[11px] text-slate-400">Kesiapan materi pembelajaran</p>
        <div class="mt-6 space-y-5">
            @foreach([['Soal tersedia',$questions,max(1,$questions),'bg-cyan-400'],['Paket tryout',$tryouts,max(1,$questions),'bg-indigo-500'],['Mata pelajaran',$subjects,max(1,$questions),'bg-emerald-500']] as [$label,$value,$max,$color])
            <div><div class="mb-2 flex justify-between text-xs"><span class="text-slate-500">{{ $label }}</span><b class="text-slate-700">{{ $value }}</b></div><div class="h-1.5 rounded-full bg-slate-100"><div class="h-1.5 rounded-full {{ $color }}" style="width: {{ max(8,min(100,$value/$max*100)) }}%"></div></div></div>
            @endforeach
        </div>
    </section>
</div>
</x-app-layout>
