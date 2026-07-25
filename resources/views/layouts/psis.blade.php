<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ dark: localStorage.getItem('psis-dark') === 'true', sidebarOpen: false }" x-init="$watch('dark', v => { localStorage.setItem('psis-dark', v); document.documentElement.classList.toggle('dark', v) }); document.documentElement.classList.toggle('dark', dark)">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    {{-- Fallback so AI FAB shows even before Vite rebuild --}}
    <style>
        #psis-ai-widget.psis-ai-widget{position:fixed!important;right:20px!important;bottom:20px!important;z-index:9999!important;display:flex!important;flex-direction:column;align-items:flex-end;gap:12px}
        #psis-ai-widget .psis-ai-fab{width:64px;height:64px;padding:0;border:2px solid #F4B400;border-radius:9999px;background:#fff;box-shadow:0 10px 25px rgba(11,60,145,.35);cursor:pointer;display:inline-flex;align-items:center;justify-content:center;overflow:hidden}
        #psis-ai-widget .psis-ai-fab-img{width:100%;height:100%;object-fit:cover;display:block}
        [x-cloak]{display:none!important}
    </style>
    @stack('head')
</head>
<body class="font-sans antialiased bg-[var(--psis-bg)] text-[var(--psis-text)]">
    <div class="min-h-screen flex">
        <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 bg-black/40 z-40 lg:hidden" @click="sidebarOpen = false"></div>

        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'" class="fixed lg:static inset-y-0 left-0 z-50 w-64 flex flex-col text-white transition-transform duration-200" style="background: var(--psis-sidebar-bg);">
            <div class="p-5 border-b border-white/10">
                <div class="flex items-center gap-3">
                <img src="{{ asset('images/pecit-logo.png') }}" alt="PECIT" class="w-10 h-10 rounded-lg object-contain">
                    <div>
                        <p class="font-bold text-sm leading-tight">PECIT</p>
                        <p class="text-xs text-white/70">Smart Inventory System</p>
                    </div>
                </div>
            </div>
            <nav class="flex-1 overflow-y-auto p-3 space-y-1 scrollbar-thin">
                @php($menuItems = \App\Support\PsisMenu::itemsFor(auth()->user()))
                @foreach ($menuItems as $item)
                    @if ($item['route'])
                        <a href="{{ route($item['route']) }}" class="psis-sidebar-link {{ \App\Support\PsisMenu::isActive($item) ? 'psis-sidebar-link-active' : 'psis-sidebar-link-inactive' }}">
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endif
                @endforeach
            </nav>
            <div class="p-4 border-t border-white/10 text-xs text-white/60">
                Philippine Electronic and Communication Institute of Technology
            </div>
        </aside>

        <div class="flex-1 flex flex-col min-w-0">
            <header class="sticky top-0 z-30 bg-[var(--psis-surface)] border-b border-[var(--psis-border)] shadow-sm">
                <div class="flex items-center justify-between px-4 lg:px-8 h-16">
                    <button type="button" class="lg:hidden p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700" @click="sidebarOpen = !sidebarOpen">
                        <span class="sr-only">Menu</span>
                        ☰
                    </button>
                    <div class="hidden lg:block">
                        <h1 class="text-lg font-semibold">@yield('page-title', 'Dashboard')</h1>
                    </div>
                    <div class="flex items-center gap-2 sm:gap-4">
                        @php($unread = auth()->user()->psisNotifications()->where('is_read', false)->count())
                        <a href="{{ route('notifications.index') }}" class="relative p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700" title="Notifications">
                            🔔
                            @if ($unread > 0)
                                <span class="absolute -top-0.5 -right-0.5 min-w-[1.1rem] h-[1.1rem] px-1 text-[10px] font-bold bg-pecit-gold text-pecit-blue-900 rounded-full flex items-center justify-center">{{ $unread }}</span>
                            @endif
                        </a>
                        <button type="button" @click="dark = !dark" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700" title="Toggle dark mode">🌓</button>
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700">
                                <span class="text-sm font-medium hidden sm:inline">{{ auth()->user()->name }}</span>
                                <span class="text-xs px-2 py-0.5 rounded bg-pecit-blue/10 text-pecit-blue dark:text-pecit-gold">{{ auth()->user()->getRoleNames()->first() }}</span>
                            </button>
                            <div x-show="open" @click.outside="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-[var(--psis-surface)] border border-[var(--psis-border)] rounded-lg shadow-soft-lg py-1 z-50">
                                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm hover:bg-slate-50 dark:hover:bg-slate-700">Profile</a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm hover:bg-slate-50 dark:hover:bg-slate-700">Logout</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 p-4 lg:p-8">
                @if (session('success'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" class="mb-4 px-4 py-3 rounded-lg bg-green-50 dark:bg-green-900/30 text-green-800 dark:text-green-200 border border-green-200 dark:border-green-800">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-4 px-4 py-3 rounded-lg bg-red-50 dark:bg-red-900/30 text-red-800 dark:text-red-200 border border-red-200 dark:border-red-800">
                        {{ session('error') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="mb-4 px-4 py-3 rounded-lg bg-red-50 dark:bg-red-900/30 text-red-800 dark:text-red-200 border border-red-200 dark:border-red-800">
                        <ul class="list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
    @include('partials.ai-chat-widget')
    @stack('scripts')
</body>
</html>
