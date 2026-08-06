@extends('layouts.psis')

@section('title', 'AI Assistant — PSIS')
@section('page-title', 'AI Assistant')

@section('content')
<div class="grid lg:grid-cols-2 gap-6">
    <div class="space-y-4">
        <div class="psis-card p-5">
            <h3 class="font-semibold mb-2">Monthly Insight</h3>
            <p class="text-sm text-slate-600 dark:text-slate-300">{{ $summary }}</p>
            @if ($role)
                <p class="text-xs text-slate-400 mt-2">Signed in as {{ $role }} — answers are tailored to your role.</p>
            @endif
        </div>

        <div class="psis-card p-5">
            <div class="flex items-center justify-between gap-2 mb-3">
                <h3 class="font-semibold">Restock Forecast</h3>
                @if (auth()->user()->hasAnyRole(['Supply Personnel', 'Administrator']))
                    <a href="{{ route('ai.restock') }}" class="text-sm text-pecit-blue dark:text-pecit-gold hover:underline">Full recommendations</a>
                @endif
            </div>
            <ul class="text-sm space-y-3">
                @forelse ($forecasts as $f)
                    <li class="border-b border-[var(--psis-border)] pb-2 last:border-0">
                        <p>{{ $f['message'] }}</p>
                        <p class="text-xs text-pecit-blue dark:text-pecit-gold mt-1">
                            Reorder {{ $f['recommended_reorder'] }} {{ $f['unit'] }}
                            · {{ ucfirst($f['urgency']) }}
                        </p>
                    </li>
                @empty
                    <li class="text-slate-500">No urgent forecasts.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <div
        class="psis-card p-5 flex flex-col"
        x-data="{
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
                this.$nextTick(() => { if (this.$refs.thread) this.$refs.thread.scrollTop = this.$refs.thread.scrollHeight; });
                try {
                    const res = await fetch(this.askUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf, 'Accept': 'application/json' },
                        body: JSON.stringify({ message: content }),
                    });
                    const data = await res.json();
                    this.messages.push({ role: 'assistant', text: data.reply || 'No reply received.' });
                } catch (e) {
                    this.messages.push({ role: 'assistant', text: 'Sorry, something went wrong.' });
                } finally {
                    this.loading = false;
                    this.$nextTick(() => { if (this.$refs.thread) this.$refs.thread.scrollTop = this.$refs.thread.scrollHeight; });
                }
            }
        }"
    >
        <div class="flex items-center gap-3 mb-4">
            <img src="{{ asset('images/chatbot.png') }}" alt="AI" class="w-10 h-10 rounded-full object-cover border-2 border-pecit-gold">
            <div>
                <h3 class="font-semibold">Chat</h3>
                <p class="text-xs text-slate-500">PSIS AI Assistant</p>
            </div>
        </div>

        <div x-ref="thread" class="min-h-[220px] max-h-[360px] overflow-y-auto space-y-3 mb-3 p-3 rounded-lg bg-slate-50 dark:bg-slate-900/40">
            <template x-if="messages.length === 0">
                <div class="flex items-end gap-2">
                    <img src="{{ asset('images/chatbot.png') }}" alt="" class="w-8 h-8 rounded-full object-cover border border-pecit-gold shrink-0">
                    <div class="rounded-2xl rounded-bl-md bg-white dark:bg-slate-800 px-3 py-2 text-sm shadow-sm">
                        Ask a question below, or tap a suggestion.
                    </div>
                </div>
            </template>
            <template x-for="(m, i) in messages" :key="i">
                <div class="flex items-end gap-2" :class="m.role === 'user' ? 'justify-end' : 'justify-start'">
                    <img
                        x-show="m.role !== 'user'"
                        src="{{ asset('images/chatbot.png') }}"
                        alt=""
                        class="w-8 h-8 rounded-full object-cover border border-pecit-gold shrink-0"
                    >
                    <div
                        class="max-w-[85%] rounded-2xl px-3 py-2 text-sm whitespace-pre-wrap shadow-sm"
                        :class="m.role === 'user' ? 'bg-pecit-blue text-white rounded-br-md' : 'bg-white dark:bg-slate-800 rounded-bl-md'"
                        x-text="m.text"
                    ></div>
                </div>
            </template>
            <div x-show="loading" class="flex items-end gap-2" x-cloak>
                <img src="{{ asset('images/chatbot.png') }}" alt="" class="w-8 h-8 rounded-full object-cover border border-pecit-gold shrink-0">
                <div class="psis-ai-bubble is-bot psis-ai-typing"><span></span><span></span><span></span></div>
            </div>
        </div>

        <div class="flex flex-wrap gap-2 mb-3">
            @foreach ($suggestions as $tip)
                <button type="button" class="psis-btn-outline text-xs py-1 px-2" @click="send(@js($tip))" :disabled="loading">{{ $tip }}</button>
            @endforeach
        </div>

        <form class="flex gap-2" @submit.prevent="send()">
            <input x-model="message" class="psis-input" placeholder="Type your question..." :disabled="loading">
            <button type="submit" class="psis-btn-primary shrink-0" :disabled="loading || !message.trim()" x-text="loading ? '...' : 'Ask'"></button>
        </form>
    </div>
</div>
@endsection
