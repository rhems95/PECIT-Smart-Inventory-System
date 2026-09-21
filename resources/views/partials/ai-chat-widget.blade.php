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
        questionsOpen: false,
        message: '',
        loading: false,
        messages: [],
        userId: @js($aiUser?->id),
        askUrl: @js(route('ai.ask')),
        csrf: @js(csrf_token()),
        fabLeft: null,
        bottom: 16,
        dragging: false,
        moved: false,
        storageKey: 'psis-ai-fab-pos',
        init() {
            this.messages = window.psisAiChat ? window.psisAiChat.load(this.userId) : [];
            this.homePos();
            this.restoreParked();
            this.clamp();
            this.$watch('open', (isOpen) => {
                if (isOpen) {
                    this.homePos();
                    this.$nextTick(() => this.scrollBottom());
                } else {
                    this.restoreParked();
                }
                this.$nextTick(() => this.clamp());
                setTimeout(() => this.clamp(), 40);
            });
            this.$nextTick(() => this.bindDrag());
            window.addEventListener('resize', () => this.clamp());
        },
        persistChat() {
            if (window.psisAiChat) window.psisAiChat.save(this.userId, this.messages);
        },
        homePos() {
            this.fabLeft = Math.max(12, window.innerWidth - 64 - 20);
            this.bottom = 16;
        },
        restoreParked() {
            try {
                const saved = JSON.parse(localStorage.getItem(this.storageKey) || 'null');
                if (! saved) return;
                if (typeof saved.fabLeft === 'number') this.fabLeft = saved.fabLeft;
                else if (typeof saved.left === 'number') this.fabLeft = saved.left;
                else if (typeof saved.right === 'number') this.fabLeft = window.innerWidth - saved.right - 64;
                if (typeof saved.bottom === 'number') this.bottom = saved.bottom;
            } catch (e) {}
        },
        limits() {
            const fab = 64;
            const pad = 12;
            const maxFabLeft = Math.max(pad, window.innerWidth - fab - pad);
            const leftTravel = Math.min(200, Math.round(window.innerWidth * 0.18));
            const minFabLeft = Math.max(maxFabLeft - leftTravel, Math.round(window.innerWidth * 0.62));
            const maxBottom = Math.min(112, Math.round(window.innerHeight * 0.12));
            return { fab, pad, maxFabLeft, minFabLeft, minBottom: pad, maxBottom };
        },
        point(e) {
            if (e.touches && e.touches[0]) return { x: e.touches[0].clientX, y: e.touches[0].clientY };
            if (e.changedTouches && e.changedTouches[0]) return { x: e.changedTouches[0].clientX, y: e.changedTouches[0].clientY };
            return { x: e.clientX, y: e.clientY };
        },
        panelWidth() {
            const pad = 24;
            const fallback = Math.min(window.innerWidth - pad, 384);
            const panel = this.$refs.panel;
            if (this.open && panel) {
                const w = panel.getBoundingClientRect().width;
                if (w > 40) return w;
            }
            return fallback;
        },
        clamp() {
            const { fab, pad, maxFabLeft, minFabLeft, minBottom, maxBottom } = this.limits();
            this.fabLeft = Math.min(maxFabLeft, Math.max(minFabLeft, this.fabLeft));
            this.bottom = Math.min(maxBottom, Math.max(minBottom, this.bottom));

            let left = this.fabLeft;
            if (this.open) {
                const panelW = this.panelWidth();
                left = this.fabLeft + fab - panelW;
                const maxLeft = Math.max(pad, window.innerWidth - panelW - pad);
                left = Math.min(maxLeft, Math.max(pad, left));
            }

            this.$el.style.setProperty('left', left + 'px', 'important');
            this.$el.style.setProperty('right', 'auto', 'important');
            this.$el.style.setProperty('bottom', this.bottom + 'px', 'important');
            this.$el.style.setProperty('top', 'auto', 'important');
        },
        bindDrag() {
            const fab = this.$refs.fab;
            if (! fab) return;
            const onMove = (e) => {
                if (! this.dragging) return;
                e.preventDefault();
                const p = this.point(e);
                const dx = p.x - this.startX;
                const dy = p.y - this.startY;
                if (Math.abs(dx) > 3 || Math.abs(dy) > 3) this.moved = true;
                this.fabLeft = this.startFabLeft + dx;
                this.bottom = this.startBottom - dy;
                this.clamp();
            };
            const onUp = () => {
                if (! this.dragging) return;
                this.dragging = false;
                window.removeEventListener('mousemove', onMove);
                window.removeEventListener('mouseup', onUp);
                window.removeEventListener('touchmove', onMove);
                window.removeEventListener('touchend', onUp);
                try {
                    localStorage.setItem(this.storageKey, JSON.stringify({ fabLeft: this.fabLeft, bottom: this.bottom }));
                } catch (err) {}
                if (! this.moved) {
                    this.open = ! this.open;
                }
                this.moved = false;
            };
            const onDown = (e) => {
                if (e.type === 'mousedown' && e.button !== 0) return;
                e.preventDefault();
                e.stopPropagation();
                if (this.open) {
                    this.open = false;
                    return;
                }
                const p = this.point(e);
                this.dragging = true;
                this.moved = false;
                this.startX = p.x;
                this.startY = p.y;
                this.startFabLeft = this.fabLeft;
                this.startBottom = this.bottom;
                window.addEventListener('mousemove', onMove);
                window.addEventListener('mouseup', onUp);
                window.addEventListener('touchmove', onMove, { passive: false });
                window.addEventListener('touchend', onUp);
            };
            fab.addEventListener('mousedown', onDown);
            fab.addEventListener('touchstart', onDown, { passive: false });
        },
        async send(text = null) {
            const content = (text ?? this.message).trim();
            if (!content || this.loading) return;

            this.messages.push({ role: 'user', text: content });
            this.persistChat();
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
                this.persistChat();
                this.$nextTick(() => this.scrollBottom());
            }
        },
        askSuggestion(text) {
            this.open = true;
            this.questionsOpen = false;
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
        x-ref="panel"
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
                        Hi! Type a question or pick one below — I’ll answer from live PSIS data{{ config('psis.ollama.enabled') ? ' (local Ollama when needed)' : '' }}.
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

        <div class="psis-ai-questions" :class="questionsOpen ? 'is-open' : ''">
            <button
                type="button"
                class="psis-ai-questions-toggle"
                @click="questionsOpen = ! questionsOpen"
                :aria-expanded="questionsOpen.toString()"
            >
                <span>Choose a question</span>
                <span class="psis-ai-questions-caret" :class="questionsOpen ? 'is-open' : ''" aria-hidden="true">▾</span>
            </button>
            <div class="psis-ai-question-list" x-show="questionsOpen" x-cloak>
                @foreach ($aiSuggestions as $tip)
                    <button type="button" class="psis-ai-question" @click="askSuggestion(@js($tip))" :disabled="loading">
                        {{ $tip }}
                    </button>
                @endforeach
            </div>
        </div>

        <form class="psis-ai-form" @submit.prevent="send()">
            <input
                type="text"
                class="psis-ai-input"
                x-model="message"
                maxlength="1000"
                autocomplete="off"
                :disabled="loading"
                placeholder="Type a question…"
                aria-label="Type a question"
            >
            <button type="submit" class="psis-ai-send" :disabled="loading || !message.trim()">Send</button>
        </form>
    </div>

    <button
        type="button"
        class="psis-ai-fab"
        x-ref="fab"
        :class="dragging ? 'is-dragging' : ''"
        :aria-expanded="open.toString()"
        aria-label="Open AI Assistant"
        title="Drag to move · Click to open"
    >
        <img
            x-show="!open"
            src="{{ asset('images/chatbot.png') }}"
            alt="AI Assistant"
            class="psis-ai-fab-img"
            draggable="false"
        >
        <span x-show="open" x-cloak class="psis-ai-fab-close">✕</span>
    </button>
</div>
