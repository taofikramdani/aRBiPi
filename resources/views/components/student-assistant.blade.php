<div x-data="arbipiAssistant()" x-init="load()" class="fixed bottom-5 right-5 z-50" x-cloak>
    <section x-show="open" x-transition.origin.bottom.right class="mb-3 flex h-[min(560px,calc(100vh-96px))] w-[min(420px,calc(100vw-24px))] flex-col overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-2xl shadow-slate-300/70">
        <header class="flex items-center gap-3 bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-3 text-white">
            <span class="grid size-9 place-items-center rounded-xl bg-white/15 text-sm font-bold">aR</span>
            <div class="min-w-0 flex-1"><h2 class="text-sm font-semibold">aRBi Assistant</h2><p class="text-[10px] text-white/70">Teman belajar berbasis Hugging Face</p></div>
            <button @click="clearHistory()" title="Hapus riwayat" class="grid size-8 place-items-center rounded-lg text-white/70 hover:bg-white/10 hover:text-white"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 6h18m-2 0-1 14H6L5 6m3 0V4h8v2m-6 4v6m4-6v6"/></svg></button>
            <button @click="open=false" class="grid size-8 place-items-center rounded-lg text-white/70 hover:bg-white/10 hover:text-white"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="m6 6 12 12M18 6 6 18"/></svg></button>
        </header>

        <div x-ref="messages" class="flex-1 space-y-3 overflow-y-auto bg-slate-50/70 p-3">
            <div x-show="messages.length === 0" class="py-8 text-center">
                <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-indigo-100 text-xl font-bold text-indigo-600">aR</span>
                <h3 class="mt-4 text-sm font-semibold text-slate-700">Halo, siap belajar?</h3>
                <p class="mx-auto mt-1 max-w-64 text-xs leading-relaxed text-slate-400">Tanyakan materi yang belum dipahami atau minta dibuatkan rencana belajar.</p>
                <div class="mt-5 flex flex-wrap justify-center gap-2">
                    <button @click="usePrompt('Bantu saya membuat jadwal belajar minggu ini')" class="rounded-full border border-indigo-100 bg-white px-3 py-2 text-[10px] text-indigo-600 hover:bg-indigo-50">Buat jadwal belajar</button>
                    <button @click="usePrompt('Jelaskan materi yang nilai saya masih rendah')" class="rounded-full border border-indigo-100 bg-white px-3 py-2 text-[10px] text-indigo-600 hover:bg-indigo-50">Evaluasi hasil saya</button>
                    <button @click="usePrompt('Berikan tips belajar yang efektif')" class="rounded-full border border-indigo-100 bg-white px-3 py-2 text-[10px] text-indigo-600 hover:bg-indigo-50">Tips belajar</button>
                </div>
            </div>
            <template x-for="item in messages" :key="item.id">
                <div class="flex gap-2" :class="item.role === 'user' ? 'justify-end' : 'justify-start'">
                    <span x-show="item.role === 'assistant'" class="mt-1 grid size-7 shrink-0 place-items-center rounded-lg bg-indigo-100 text-[9px] font-bold text-indigo-600">aR</span>
                    <div class="max-w-[88%] rounded-2xl px-3.5 py-2.5 text-xs shadow-sm" :class="item.role === 'user' ? 'rounded-br-md bg-indigo-600 text-white' : 'rounded-bl-md border border-slate-100 bg-white text-slate-600'">
                        <p x-show="item.role === 'user'" class="whitespace-pre-wrap leading-relaxed" x-text="item.content"></p>
                        <div x-show="item.role === 'assistant'" class="ai-content ai-content-chat" x-html="item.rendered_content"></div>
                    </div>
                </div>
            </template>
            <div x-show="loading" class="flex justify-start"><div class="flex items-center gap-1 rounded-2xl rounded-bl-md border border-slate-100 bg-white px-4 py-3 shadow-sm"><i class="size-1.5 animate-bounce rounded-full bg-indigo-400"></i><i class="size-1.5 animate-bounce rounded-full bg-indigo-400 [animation-delay:120ms]"></i><i class="size-1.5 animate-bounce rounded-full bg-indigo-400 [animation-delay:240ms]"></i></div></div>
            <div x-show="error" class="rounded-xl border border-rose-100 bg-rose-50 p-3 text-[11px] text-rose-600" x-text="error"></div>
        </div>

        <form @submit.prevent="send()" class="border-t border-slate-100 bg-white p-3">
            <div class="flex items-end gap-2 rounded-xl border border-slate-200 bg-slate-50 p-2 focus-within:border-indigo-300">
                <textarea x-model="input" @keydown.enter.prevent="if (!$event.shiftKey) send()" rows="1" maxlength="2000" placeholder="Tanyakan sesuatu..." class="max-h-28 min-h-9 flex-1 resize-none border-0 bg-transparent px-2 py-2 text-xs text-slate-600 placeholder:text-slate-400 focus:ring-0"></textarea>
                <button :disabled="loading || !input.trim()" class="grid size-9 shrink-0 place-items-center rounded-lg bg-indigo-600 text-white disabled:cursor-not-allowed disabled:opacity-40"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="m22 2-7 20-4-9-9-4 20-7ZM11 13l4-4"/></svg></button>
            </div>
            <p class="mt-2 text-center text-[9px] text-slate-300">Jawaban AI dapat keliru. Verifikasi informasi penting.</p>
        </form>
    </section>
    <button @click="open=!open" class="ml-auto flex items-center gap-3 rounded-2xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-3 text-white shadow-xl shadow-indigo-300 transition hover:-translate-y-0.5">
        <span class="grid size-9 place-items-center rounded-xl bg-white/15 font-bold">aR</span><span class="pr-1 text-left"><b class="block text-xs">aRBi Assistant</b><small class="text-[9px] text-white/70">Tanya teman belajarmu</small></span>
    </button>
</div>
