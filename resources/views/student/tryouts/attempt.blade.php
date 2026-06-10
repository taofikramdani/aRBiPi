@php
    $deadline = $attempt->started_at->copy()->addMinutes($attempt->tryout->duration_minutes);
@endphp

<x-app-layout>
    <x-slot:header>{{ $attempt->tryout->title }}</x-slot:header>

    <div x-data="tryoutTimer(@js($deadline->toIso8601String()))">
        <div class="sticky top-3 z-20 mb-5 flex flex-col gap-3 rounded-2xl border border-indigo-100 bg-white/95 p-4 shadow-lg shadow-indigo-100/50 backdrop-blur sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-indigo-500">Waktu Pengerjaan</p>
                <p class="mt-1 text-sm text-slate-500">
                    Durasi {{ $attempt->tryout->duration_minutes }} menit. Jawaban otomatis dikumpulkan saat waktu habis.
                </p>
            </div>
            <div class="rounded-xl bg-indigo-50 px-5 py-3 text-center">
                <p class="font-mono text-2xl font-bold tabular-nums text-indigo-600" x-text="formattedTime">00:00:00</p>
                <p class="text-[10px] font-semibold uppercase tracking-widest text-indigo-400">Jam : Menit : Detik</p>
            </div>
        </div>

        <form
            x-ref="attemptForm"
            method="POST"
            action="{{ route('student.attempts.submit', $attempt) }}"
            class="space-y-5"
            @submit="submitted = true"
        >
            @csrf

            @foreach($attempt->tryout->questions as $i => $q)
                <section class="card">
                    <p class="mb-4 font-bold">{{ $i + 1 }}. {{ $q->question_text }}</p>
                    <div class="grid gap-2">
                        @foreach($q->options as $option)
                            <label class="flex cursor-pointer gap-3 rounded-xl border p-3 hover:border-indigo-400 hover:bg-indigo-50">
                                <input type="radio" name="answers[{{ $q->id }}]" value="{{ $option->id }}">
                                <b>{{ $option->label }}.</b>
                                <span>{{ $option->option_text }}</span>
                            </label>
                        @endforeach
                    </div>
                </section>
            @endforeach

            <button class="btn-primary w-full py-3" onclick="return confirm('Kumpulkan jawaban sekarang?')">
                Kumpulkan Jawaban
            </button>
        </form>
    </div>
</x-app-layout>
