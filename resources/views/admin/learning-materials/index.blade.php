<x-app-layout>
    <x-slot:header>Materi Pembelajaran</x-slot:header>

    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm text-slate-500">Kelola modul PDF berdasarkan mata pelajaran.</p>
            <p class="mt-1 text-xs text-slate-400">{{ $subjects->sum('learning_materials_count') }} PDF tersedia dalam {{ $subjects->count() }} mata pelajaran.</p>
        </div>
        <a class="btn-primary" href="{{ route('admin.learning-materials.create') }}">+ Upload PDF</a>
    </div>

    <div class="space-y-3" x-data="{ open: {{ $subjects->first()?->id ?? 'null' }} }">
        @forelse($subjects as $subject)
            <section class="dashboard-panel overflow-hidden">
                <button type="button" class="flex w-full items-center gap-4 px-5 py-4 text-left hover:bg-slate-50" @click="open = open === {{ $subject->id }} ? null : {{ $subject->id }}">
                    <span class="grid size-11 place-items-center rounded-xl bg-indigo-50 font-semibold text-indigo-600">{{ strtoupper(substr($subject->name, 0, 2)) }}</span>
                    <div class="min-w-0 flex-1">
                        <h2 class="text-sm font-semibold text-slate-700">{{ $subject->name }}</h2>
                        <p class="mt-1 text-xs text-slate-400">{{ $subject->learning_materials_count }} materi PDF</p>
                    </div>
                    <svg class="size-4 text-slate-400 transition" :class="open === {{ $subject->id }} && 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="m6 9 6 6 6-6"/></svg>
                </button>
                <div x-show="open === {{ $subject->id }}" x-cloak class="border-t border-slate-100">
                    @forelse($subject->learningMaterials as $material)
                        <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 last:border-0 sm:flex-row sm:items-center">
                            <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-rose-50 text-xs font-bold text-rose-500">PDF</span>
                            <div class="min-w-0 flex-1">
                                <p class="font-medium text-slate-700">{{ $material->title }}</p>
                                <p class="mt-1 text-xs text-slate-400">{{ $material->original_name }} · {{ $material->formatted_size }} · {{ $material->is_published ? 'Dipublikasikan' : 'Draft' }}</p>
                            </div>
                            <div class="flex gap-3 text-xs font-semibold">
                                <a class="text-indigo-600" target="_blank" href="{{ route('admin.learning-materials.open', $material) }}">Buka</a>
                                <a class="text-amber-600" href="{{ route('admin.learning-materials.edit', $material) }}">Edit</a>
                                <form method="POST" action="{{ route('admin.learning-materials.destroy', $material) }}" onsubmit="return confirm('Hapus materi dan file PDF ini?')">
                                    @csrf @method('DELETE')
                                    <button class="text-rose-500">Hapus</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center text-sm text-slate-400">Belum ada PDF untuk mata pelajaran ini.</div>
                    @endforelse
                </div>
            </section>
        @empty
            <div class="dashboard-panel p-8 text-center text-sm text-slate-400">Tambahkan mata pelajaran terlebih dahulu.</div>
        @endforelse
    </div>
</x-app-layout>
