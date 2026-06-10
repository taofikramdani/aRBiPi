import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('arbipiAssistant', () => ({
    open: false,
    loading: false,
    input: '',
    error: '',
    messages: [],
    csrf: document.querySelector('meta[name="csrf-token"]')?.content,
    async load() {
        const response = await fetch('/student/assistant/messages', { headers: { Accept: 'application/json' } });
        if (response.ok) this.messages = (await response.json()).messages;
        this.scroll();
    },
    usePrompt(prompt) {
        this.input = prompt;
        this.send();
    },
    async send() {
        const message = this.input.trim();
        if (!message || this.loading) return;
        this.input = '';
        this.error = '';
        this.loading = true;
        this.messages.push({ id: `temp-${Date.now()}`, role: 'user', content: message });
        this.scroll();
        try {
            const response = await fetch('/student/assistant/messages', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': this.csrf },
                body: JSON.stringify({ message }),
            });
            const data = await response.json();
            this.messages = this.messages.filter(item => !String(item.id).startsWith('temp-'));
            if (!response.ok) {
                if (data.user_message) this.messages.push(data.user_message);
                throw new Error(data.message || 'aRBi Assistant belum dapat menjawab.');
            }
            this.messages.push(data.user_message, data.assistant_message);
        } catch (error) {
            this.error = error.message;
        } finally {
            this.loading = false;
            this.scroll();
        }
    },
    async clearHistory() {
        if (!confirm('Hapus seluruh riwayat percakapan?')) return;
        const response = await fetch('/student/assistant/messages', {
            method: 'DELETE',
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': this.csrf },
        });
        if (response.ok) {
            this.messages = [];
            this.error = '';
        }
    },
    scroll() {
        this.$nextTick(() => {
            if (this.$refs.messages) this.$refs.messages.scrollTop = this.$refs.messages.scrollHeight;
        });
    },
}));

Alpine.data('tryoutTimer', (deadline) => ({
    remaining: 0,
    interval: null,
    submitted: false,
    init() {
        this.tick();
        this.interval = window.setInterval(() => this.tick(), 1000);
    },
    tick() {
        this.remaining = Math.max(0, Math.ceil((new Date(deadline).getTime() - Date.now()) / 1000));

        if (this.remaining === 0 && !this.submitted) {
            this.submitted = true;
            window.clearInterval(this.interval);
            this.$refs.attemptForm.submit();
        }
    },
    get formattedTime() {
        const hours = Math.floor(this.remaining / 3600);
        const minutes = Math.floor((this.remaining % 3600) / 60);
        const seconds = this.remaining % 60;

        return [hours, minutes, seconds].map(value => String(value).padStart(2, '0')).join(':');
    },
}));

Alpine.start();
