<x-app-layout>
    <x-slot:header>Materi Pembelajaran</x-slot:header>

    <div class="mb-5 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 p-6 text-white shadow-lg shadow-indigo-200">
        <p class="text-xs font-semibold uppercase tracking-widest text-indigo-100">Perpustakaan Digital</p>
        <h2 class="mt-2 text-xl font-bold">Pelajari materi sesuai mata pelajaranmu</h2>
        <p class="mt-2 text-sm text-indigo-100">Buka modul PDF, pelajari konsepnya, lalu uji kemampuanmu melalui tryout.</p>
    </div>

    <div class="space-y-3" x-data="{ open: {{ $subjects->first()?->id ?? 'null' }} }">
        @forelse($subjects as $subject)
            <section class="dashboard-panel overflow-hidden">
                <button type="button" class="flex w-full items-center gap-4 px-5 py-4 text-left hover:bg-slate-50" @click="open = open === {{ $subject->id }} ? null : {{ $subject->id }}">
                    <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-indigo-50 font-semibold text-indigo-600">{{ strtoupper(substr($subject->name, 0, 2)) }}</span>
                    <div class="min-w-0 flex-1">
                        <h2 class="font-semibold text-slate-700">{{ $subject->name }}</h2>
                        <p class="mt-1 text-xs text-slate-400">{{ $subject->learning_materials_count }} modul pembelajaran</p>
                    </div>
                    <svg class="size-4 text-slate-400 transition" :class="open === {{ $subject->id }} && 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="m6 9 6 6 6-6"/></svg>
                </button>
                <div x-show="open === {{ $subject->id }}" x-cloak class="grid gap-3 border-t border-slate-100 p-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach($subject->learningMaterials as $material)
                        <a target="_blank" href="{{ route('student.learning-materials.open', $material) }}" class="group rounded-xl border border-slate-100 bg-slate-50/70 p-4 transition hover:border-indigo-200 hover:bg-indigo-50/50">
                            <div class="flex items-start gap-3">
                                <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-rose-50 text-[10px] font-bold text-rose-500">PDF</span>
                                <div class="min-w-0">
                                    <h3 class="font-semibold text-slate-700 group-hover:text-indigo-600">{{ $material->title }}</h3>
                                    <p class="mt-1 line-clamp-2 text-xs leading-relaxed text-slate-400">{{ $material->description ?: 'Buka dan pelajari modul ini.' }}</p>
                                    <p class="mt-3 text-[10px] font-semibold uppercase tracking-wider text-indigo-500">{{ $material->formatted_size }} · Buka Modul</p>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @empty
            <div class="dashboard-panel p-10 text-center text-sm text-slate-400">Belum ada materi pembelajaran yang dipublikasikan.</div>
        @endforelse
    </div>
</x-app-layout>
