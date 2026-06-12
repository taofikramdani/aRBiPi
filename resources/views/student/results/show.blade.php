<x-app-layout>
    <x-slot:header>Hasil {{ $result->tryout->title }}</x-slot:header>

    <div class="grid gap-4 sm:grid-cols-4">
        @foreach(['Nilai' => $result->score, 'Benar' => $result->correct_answers, 'Salah' => $result->wrong_answers, 'Kosong' => $result->unanswered] as $label => $value)
            <div class="card text-center">
                <p class="text-sm text-slate-500">{{ $label }}</p>
                <p class="mt-2 text-3xl font-black text-indigo-600">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <div class="card mt-6">
        <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
            <span class="grid size-9 place-items-center rounded-lg bg-indigo-50 text-indigo-600">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M12 3v18m9-9H3"/>
                </svg>
            </span>
            <div>
                <p class="text-xs font-bold uppercase text-indigo-600">Rekomendasi AI</p>
                <p class="mt-0.5 text-[11px] text-slate-400">Saran belajar berdasarkan hasil tryout ini</p>
            </div>
        </div>
        <div class="mt-4 max-h-[32rem] overflow-y-auto rounded-xl bg-slate-50 p-4">
            <x-ai-content :content="$result->recommendation?->recommendation" />
        </div>
    </div>

    <div class="mt-6 space-y-4">
        @foreach($result->attempt->answers as $answer)
            <div class="card">
                <p class="font-bold">{{ $answer->question->question_text }}</p>
                <p class="mt-2 text-sm {{ $answer->is_correct ? 'text-emerald-600' : 'text-red-600' }}">
                    Jawaban Anda: {{ $answer->option?->label ?? '-' }} · {{ $answer->is_correct ? 'Benar' : 'Belum tepat' }}
                </p>
                <p class="mt-3 rounded-xl bg-slate-50 p-3 text-sm">
                    <b>Pembahasan:</b> {{ $answer->question->explanation }}
                </p>
            </div>
        @endforeach
    </div>
</x-app-layout>
