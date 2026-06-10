<x-app-layout>
    <x-slot:header>{{ $learningMaterial->exists ? 'Edit' : 'Upload' }} Materi Pembelajaran</x-slot:header>

    <form class="card max-w-3xl space-y-5" method="POST" enctype="multipart/form-data" action="{{ $learningMaterial->exists ? route('admin.learning-materials.update', $learningMaterial) : route('admin.learning-materials.store') }}">
        @csrf
        @if($learningMaterial->exists) @method('PUT') @endif

        <div>
            <label class="label">Mata Pelajaran</label>
            <select class="input" name="subject_id" required>
                <option value="">Pilih mata pelajaran</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}" @selected(old('subject_id', $learningMaterial->subject_id) == $subject->id)>{{ $subject->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label">Judul Materi</label>
            <input class="input" name="title" value="{{ old('title', $learningMaterial->title) }}" placeholder="Contoh: Docker untuk Pemula" required>
        </div>
        <div>
            <label class="label">Deskripsi</label>
            <textarea class="input" name="description" rows="4" placeholder="Ringkasan materi pembelajaran">{{ old('description', $learningMaterial->description) }}</textarea>
        </div>
        <div>
            <label class="label">File PDF {{ $learningMaterial->exists ? '(opsional jika tidak diganti)' : '' }}</label>
            <label class="mt-2 flex cursor-pointer flex-col items-center rounded-xl border-2 border-dashed border-slate-200 bg-slate-50 px-6 py-8 text-center hover:border-indigo-300 hover:bg-indigo-50/40">
                <span class="grid size-12 place-items-center rounded-xl bg-white text-rose-500 shadow-sm">PDF</span>
                <span class="mt-3 text-sm font-semibold text-slate-600">Pilih file PDF</span>
                <span class="mt-1 text-xs text-slate-400">Maksimal 15 MB</span>
                <input class="sr-only" type="file" name="pdf" accept="application/pdf" @required(!$learningMaterial->exists)>
            </label>
            @if($learningMaterial->exists)<p class="mt-2 text-xs text-slate-400">File saat ini: {{ $learningMaterial->original_name }}</p>@endif
        </div>
        <label class="flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $learningMaterial->is_published ?? true))>
            Publikasikan untuk siswa
        </label>
        <div class="flex gap-3">
            <button class="btn-primary">Simpan Materi</button>
            <a class="btn-secondary" href="{{ route('admin.learning-materials.index') }}">Batal</a>
        </div>
    </form>
</x-app-layout>
