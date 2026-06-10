<x-app-layout>
<x-slot:header>Bank Soal</x-slot:header>
@php
    $draft = session('ai_question_draft');
    $jobKey = session('ai_job_key');
    $isGenerating = $jobKey && !$draft;
    $difficultyLabel = ['easy' => 'Mudah', 'medium' => 'Sedang', 'hard' => 'Sulit'];
    $difficultyClass = ['easy' => 'bg-emerald-50 text-emerald-600', 'medium' => 'bg-amber-50 text-amber-600', 'hard' => 'bg-rose-50 text-rose-600'];
@endphp

@if(!config('services.huggingface.token'))
<div class="mb-5 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-700">
    <svg class="mt-0.5 size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 9v4m0 4h.01M10.3 4.2 2.5 18a2 2 0 0 0 1.7 3h15.6a2 2 0 0 0 1.7-3L13.7 4.2a2 2 0 0 0-3.4 0Z"/></svg>
    <div><b>Generator AI belum aktif.</b><p class="mt-1 text-xs text-amber-600">Isi <code>HUGGINGFACE_TOKEN</code> pada file <code>.env</code>, lalu jalankan <code>php artisan optimize:clear</code>.</p></div>
</div>
@endif

{{-- Banner sedang generate (polling) --}}
@if($isGenerating)
<div id="ai-generating-banner" class="mb-5 flex items-center gap-3 rounded-xl border border-violet-200 bg-violet-50 px-5 py-4">
    <span class="relative flex size-5 shrink-0">
        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-violet-400 opacity-75"></span>
        <span class="relative inline-flex size-5 rounded-full bg-violet-500"></span>
    </span>
    <div class="flex-1">
        <p class="text-sm font-semibold text-violet-700">Hugging Face sedang membuat soal...</p>
        <p class="mt-0.5 text-xs text-violet-500">Halaman akan otomatis diperbarui saat soal siap. Tidak perlu reload.</p>
    </div>
    <svg id="ai-spinner" class="size-5 animate-spin text-violet-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
</div>

<script>
(function () {
    const pollUrl = '{{ route('admin.ai.questions.poll') }}';
    const interval = setInterval(async () => {
        try {
            const res = await fetch(pollUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await res.json();
            if (data.status === 'done' || data.status === 'failed' || data.status === 'expired' || data.status === 'none') {
                clearInterval(interval);
                window.location.reload();
            }
        } catch (e) { /* jaringan error, coba lagi nanti */ }
    }, 3000);
})();
</script>
@endif

{{-- Error message --}}
@if($errors->has('ai'))
<div class="mb-5 flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
    <svg class="mt-0.5 size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 9v4m0 4h.01M10.3 4.2 2.5 18a2 2 0 0 0 1.7 3h15.6a2 2 0 0 0 1.7-3L13.7 4.2a2 2 0 0 0-3.4 0Z"/></svg>
    <div><b>Gagal generate soal.</b> {{ $errors->first('ai') }}</div>
</div>
@endif
@if(session('ai_generation_error'))
<div class="mb-5 flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
    <svg class="mt-0.5 size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 9v4m0 4h.01M10.3 4.2 2.5 18a2 2 0 0 0 1.7 3h15.6a2 2 0 0 0 1.7-3L13.7 4.2a2 2 0 0 0-3.4 0Z"/></svg>
    <div><b>Generator AI gagal.</b><p class="mt-1 text-xs">{{ session('ai_generation_error') }}</p></div>
</div>
@endif

<section class="dashboard-panel overflow-hidden">
    <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-5 sm:flex-row sm:items-center sm:justify-between">
        <div><div class="flex items-center gap-2"><span class="grid size-9 place-items-center rounded-lg bg-violet-50 text-violet-600"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 3v3m0 12v3M3 12h3m12 0h3m-3.64-6.36-2.12 2.12M8.76 15.24l-2.12 2.12m0-11.72 2.12 2.12m6.48 7.48 2.12 2.12M9 12a3 3 0 1 0 6 0 3 3 0 0 0-6 0Z"/></svg></span><div><h2 class="text-sm font-semibold text-slate-700">AI Question Generator</h2><p class="mt-0.5 text-[11px] text-slate-400">Buat draft soal di background, tinjau, lalu simpan massal ke bank soal</p></div></div></div>
        <a class="btn-secondary text-xs" href="{{ route('admin.questions.create') }}">+ Tambah Soal Manual</a>
    </div>
    <form method="POST" action="{{ route('admin.ai.questions') }}" class="grid gap-4 p-5 lg:grid-cols-[220px_1fr_170px_auto]">
        @csrf
        <div><label class="label text-xs">Mata Pelajaran</label><select class="input text-sm" name="subject_id" required><option value="">Pilih mapel</option>@foreach($subjects as $subject)<option value="{{ $subject->id }}" @selected(old('subject_id', $draft['subject_id'] ?? null) == $subject->id)>{{ $subject->name }}</option>@endforeach</select></div>
        <div><label class="label text-xs">Materi / Kompetensi</label><input class="input text-sm" name="material" value="{{ old('material', $draft['material'] ?? '') }}" placeholder="Contoh: Persamaan linear satu variabel" required></div>
        <div><label class="label text-xs">Kesulitan</label><select class="input text-sm" name="difficulty">@foreach($difficultyLabel as $value => $label)<option value="{{ $value }}" @selected(old('difficulty', $draft['difficulty'] ?? 'medium') === $value)>{{ $label }}</option>@endforeach</select></div>
        <div class="flex items-end gap-2">
            @if($isGenerating)
                <button type="button" disabled class="btn-secondary h-[42px] cursor-not-allowed text-xs opacity-50">Sedang proses...</button>
            @else
                <button name="count" value="1" class="btn-secondary h-[42px] text-xs">Generate 1</button>
                <button name="count" value="5" class="btn-secondary h-[42px] text-xs">Generate 5</button>
                <button name="count" value="10" class="btn-primary h-[42px] whitespace-nowrap text-xs"><svg class="mr-2 size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 3v3m0 12v3M3 12h3m12 0h3m-3.64-6.36-2.12 2.12M8.76 15.24l-2.12 2.12m0-11.72 2.12 2.12m6.48 7.48 2.12 2.12"/></svg>Generate 10 Soal</button>
            @endif
        </div>
    </form>
</section>

@if($draft && !empty($draft['questions']))
<section class="dashboard-panel mt-5 overflow-hidden">
    <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <div><div class="flex items-center gap-2"><h2 class="text-sm font-semibold text-slate-700">Review Draft AI</h2><span class="rounded-full bg-violet-50 px-2.5 py-1 text-[10px] font-semibold text-violet-600">{{ count($draft['questions']) }} soal</span></div><p class="mt-1 text-[11px] text-slate-400">Periksa soal sebelum dimasukkan ke bank soal.</p></div>
        <div class="flex gap-2"><form method="POST" action="{{ route('admin.ai.questions.discard') }}">@csrf @method('DELETE')<button class="btn-secondary text-xs">Buang Draft</button></form><form method="POST" action="{{ route('admin.ai.questions.store') }}">@csrf<button class="btn-primary text-xs">Simpan Semua Soal</button></form></div>
    </div>
    <div class="grid gap-4 p-5 lg:grid-cols-2">
        @foreach($draft['questions'] as $index => $item)
        <article class="rounded-xl border border-slate-100 bg-slate-50/60 p-4">
            <div class="mb-3 flex items-center justify-between"><span class="text-[10px] font-semibold uppercase tracking-wider text-violet-600">Draft {{ $index + 1 }}</span><span class="rounded-full {{ $difficultyClass[$draft['difficulty']] }} px-2 py-1 text-[10px] font-semibold">{{ $difficultyLabel[$draft['difficulty']] }}</span></div>
            <p class="text-sm font-medium leading-relaxed text-slate-700">{{ $item['question'] }}</p>
            <div class="mt-3 grid gap-2 sm:grid-cols-2">@foreach($item['options'] as $label => $option)<div class="rounded-lg border px-3 py-2 text-xs {{ $label === $item['correct_answer'] ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-slate-100 bg-white text-slate-500' }}"><b class="mr-1">{{ $label }}.</b>{{ $option }}</div>@endforeach</div>
            <p class="mt-3 text-[11px] leading-relaxed text-slate-400"><b>Pembahasan:</b> {{ $item['explanation'] }}</p>
        </article>
        @endforeach
    </div>
</section>
@endif

<div class="mb-3 mt-7 flex items-end justify-between"><div><h2 class="text-sm font-semibold text-slate-700">Koleksi Soal per Mata Pelajaran</h2><p class="mt-1 text-[11px] text-slate-400">{{ $subjects->sum('questions_count') }} soal tersimpan dalam {{ $subjects->count() }} mata pelajaran</p></div></div>
<div class="space-y-3" x-data="{ open: {{ $subjects->first()?->id ?? 'null' }} }">
    @forelse($subjects as $subject)
    @php
        $easy = $subject->questions->where('difficulty', 'easy')->count();
        $medium = $subject->questions->where('difficulty', 'medium')->count();
        $hard = $subject->questions->where('difficulty', 'hard')->count();
    @endphp
    <section class="dashboard-panel overflow-hidden">
        <button type="button" class="flex w-full items-center gap-4 px-5 py-4 text-left hover:bg-slate-50/70" @click="open = open === {{ $subject->id }} ? null : {{ $subject->id }}">
            <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-indigo-50 font-semibold text-indigo-600">{{ strtoupper(substr($subject->name, 0, 2)) }}</span>
            <div class="min-w-0 flex-1"><div class="flex flex-wrap items-center gap-2"><h3 class="text-sm font-semibold text-slate-700">{{ $subject->name }}</h3><span class="rounded-full bg-slate-100 px-2 py-1 text-[10px] font-semibold text-slate-500">{{ $subject->questions_count }} soal</span></div><div class="mt-2 flex gap-3 text-[10px]"><span class="text-emerald-600">{{ $easy }} mudah</span><span class="text-amber-600">{{ $medium }} sedang</span><span class="text-rose-600">{{ $hard }} sulit</span></div></div>
            <svg class="size-4 shrink-0 text-slate-400 transition" :class="open === {{ $subject->id }} && 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="m6 9 6 6 6-6"/></svg>
        </button>
        <div x-show="open === {{ $subject->id }}">
            <div class="overflow-x-auto border-t border-slate-100">
                <table class="min-w-full text-sm"><thead><tr class="bg-slate-50/80 text-left text-[10px] uppercase tracking-wider text-slate-400"><th class="px-5 py-3 font-semibold">Pertanyaan</th><th class="px-5 py-3 font-semibold">Kesulitan</th><th class="px-5 py-3 font-semibold">Sumber</th><th class="px-5 py-3 text-right font-semibold">Aksi</th></tr></thead><tbody class="divide-y divide-slate-100">
                    @forelse($subject->questions as $question)
                    <tr class="hover:bg-slate-50/60"><td class="max-w-2xl px-5 py-4"><p class="line-clamp-2 text-sm text-slate-600">{{ $question->question_text }}</p><p class="mt-1 text-[10px] text-slate-400">Digunakan di {{ $question->tryouts_count }} paket tryout</p></td><td class="px-5 py-4"><span class="rounded-full {{ $difficultyClass[$question->difficulty] }} px-2.5 py-1 text-[10px] font-semibold">{{ $difficultyLabel[$question->difficulty] }}</span></td><td class="px-5 py-4"><span class="text-[11px] {{ $question->is_ai_generated ? 'text-violet-600' : 'text-slate-400' }}">{{ $question->is_ai_generated ? 'AI Generated' : 'Manual' }}</span></td><td class="px-5 py-4"><div class="flex justify-end gap-3"><a class="text-xs font-semibold text-indigo-600" href="{{ route('admin.questions.edit', $question) }}">Edit</a><form method="POST" action="{{ route('admin.questions.destroy', $question) }}" onsubmit="return confirm('Hapus soal ini?')">@csrf @method('DELETE')<button class="text-xs font-semibold text-rose-500">Hapus</button></form></div></td></tr>
                    @empty<tr><td colspan="4" class="px-5 py-8 text-center text-xs text-slate-400">Belum ada soal pada mata pelajaran ini.</td></tr>@endforelse
                </tbody></table>
            </div>
        </div>
    </section>
    @empty
    <div class="dashboard-panel p-8 text-center text-sm text-slate-400">Tambahkan mata pelajaran terlebih dahulu.</div>
    @endforelse
</div>
</x-app-layout>
