@php
    $aiUser = auth()->user();
    $aiSuggestions = $aiUser
        ? app(\App\Services\AiInsightService::class)->chatSuggestions($aiUser)
        : [];
@endphp

<div
    id="psis-ai-widget"
    class="psis-ai-widget"
    x-data="{
        open: false,
        message: '',
        loading: false,
        messages: [],
        askUrl: @js(route('ai.ask')),
        csrf: @js(csrf_token()),
        async send(text = null) {
            const content = (text ?? this.message).trim();
            if (!content || this.loading) return;

            this.messages.push({ role: 'user', text: content });
            this.message = '';
            this.loading = true;
            this.$nextTick(() => this.scrollBottom());

            try {
                const res = await fetch(this.askUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrf,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ message: content }),
                });
                const data = await res.json();
                this.messages.push({ role: 'assistant', text: data.reply || 'No reply received.' });
            } catch (e) {
                this.messages.push({ role: 'assistant', text: 'Sorry, something went wrong. Please try again.' });
            } finally {
                this.loading = false;
                this.$nextTick(() => this.scrollBottom());
            }
        },
        askSuggestion(text) {
            this.open = true;
            this.send(text);
        },
        scrollBottom() {
            const el = this.$refs.thread;
            if (el) el.scrollTop = el.scrollHeight;
        }
    }"
    @keydown.escape.window="open = false"
>
    <div
        class="psis-ai-panel"
        x-show="open"
        x-cloak
        x-transition
        role="dialog"
        aria-label="AI Assistant chat"
    >
        <div class="psis-ai-panel-header">
            <div class="psis-ai-panel-identity">
                <img src="{{ asset('images/chatbot.png') }}" alt="" class="psis-ai-avatar">
                <div>
                    <p class="psis-ai-panel-title">PSIS AI Assistant</p>
                    <p class="psis-ai-panel-sub">{{ $aiUser?->getRoleNames()->first() }} tips & answers</p>
                </div>
            </div>
            <div class="psis-ai-panel-actions">
                <a href="{{ route('ai.chat') }}" class="psis-ai-link">Expand</a>
                <button type="button" class="psis-ai-icon-btn" @click="open = false" aria-label="Close chat">✕</button>
            </div>
        </div>

        <div class="psis-ai-thread" x-ref="thread">
            <template x-if="messages.length === 0">
                <div class="psis-ai-row is-bot">
                    <img src="{{ asset('images/chatbot.png') }}" alt="" class="psis-ai-avatar">
                    <div class="psis-ai-bubble is-bot">
                        Hi! Choose a question below — I’ll answer from live PSIS data.
                    </div>
                </div>
            </template>
            <template x-for="(m, i) in messages" :key="i">
                <div class="psis-ai-row" :class="m.role === 'user' ? 'is-user' : 'is-bot'">
                    <img
                        x-show="m.role !== 'user'"
                        src="{{ asset('images/chatbot.png') }}"
                        alt=""
                        class="psis-ai-avatar"
                    >
                    <div class="psis-ai-bubble" :class="m.role === 'user' ? 'is-user' : 'is-bot'" x-text="m.text"></div>
                </div>
            </template>
            <div x-show="loading" class="psis-ai-row is-bot" x-cloak>
                <img src="{{ asset('images/chatbot.png') }}" alt="" class="psis-ai-avatar">
                <div class="psis-ai-bubble is-bot psis-ai-typing">
                    <span></span><span></span><span></span>
                </div>
            </div>
        </div>

        <div class="psis-ai-questions">
            <p class="psis-ai-suggestions-label">Choose a question</p>
            <div class="psis-ai-question-list">
                @foreach ($aiSuggestions as $tip)
                    <button type="button" class="psis-ai-question" @click="askSuggestion(@js($tip))" :disabled="loading">
                        {{ $tip }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    <button
        type="button"
        class="psis-ai-fab"
        @click="open = !open"
        :aria-expanded="open.toString()"
        aria-label="Open AI Assistant"
        title="AI Assistant"
    >
        <img
            x-show="!open"
            src="{{ asset('images/chatbot.png') }}"
            alt="AI Assistant"
            class="psis-ai-fab-img"
        >
        <span x-show="open" x-cloak class="psis-ai-fab-close">✕</span>
    </button>
</div>
