<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} - MARSS CORPORATION</title>
    <link rel="shortcut icon" href="{{ asset('backend/assets/icons/favicon.svg') }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+Da+2:wght@400;500;600;700;800&family=Valley+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <script>
        (function(){
            var m = localStorage.getItem('lightMode') === 'dark' ? 'dark' : 'light';
            if (m === 'dark') document.documentElement.classList.add('dark');
            document.documentElement.setAttribute('light-mode', m);
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
@php
    $isBattery = request()->routeIs('battery.*') || request()->is('battery*');
    $user = auth()->user();
    $roleLower = strtolower(trim((string) ($user?->role ?? 'user')));
    $roleBadgeClass = match ($roleLower) {
        'super_admin', 'super admin' => 'bg-gradient-to-r from-orange-500 to-orange-600 text-white',
        'admin' => 'bg-gradient-to-r from-red-500 to-red-600 text-white',
        'manager' => 'bg-sky-100 text-sky-700 dark:bg-sky-900/50 dark:text-sky-300',
        'cashier' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300',
        'staff' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300',
        default => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
    };
@endphp
<body
    x-data="{
        sidebarCollapsed: localStorage.getItem('marss_sidebar_sm') === '1',
        mobileOpen: false,
        dark: localStorage.getItem('lightMode') === 'dark',
        fullscreen: false,
        dateText: '',
        timeText: '',
        init() {
            window.marssTheme?.apply();
            this.updateClock();
            if (!window._marssClockTimer) {
                window._marssClockTimer = setInterval(() => {
                    window.dispatchEvent(new CustomEvent('marss-tick'));
                }, 1000);
            }
            window.addEventListener('marss-tick', () => this.updateClock());
            window.addEventListener('marss-theme-changed', (e) => { this.dark = e.detail.dark; });
        },
        updateClock() {
            const now = new Date();
            this.dateText = now.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
            this.timeText = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
        },
        toggleSidebar() {
            if (window.innerWidth < 1024) {
                this.mobileOpen = !this.mobileOpen;
            } else {
                this.sidebarCollapsed = !this.sidebarCollapsed;
                localStorage.setItem('marss_sidebar_sm', this.sidebarCollapsed ? '1' : '0');
            }
        },
        toggleTheme() {
            window.marssTheme?.toggle();
            this.dark = window.marssTheme?.isDark() ?? false;
        },
        toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen?.();
                this.fullscreen = true;
            } else {
                document.exitFullscreen?.();
                this.fullscreen = false;
            }
        }
    }"
    class="font-sans antialiased bg-[#f8fafc] dark:bg-[#0b0f19] text-slate-900 dark:text-slate-100 min-h-screen"
>
    {{-- Mobile Sidebar Backdrop --}}
    <div
        x-show="mobileOpen"
        x-transition.opacity
        @click="mobileOpen = false"
        class="fixed inset-0 z-30 bg-slate-900/50 backdrop-blur-xs lg:hidden no-print"
        style="display: none;"
    ></div>

    {{-- Left Vertical Sidebar (240px Expanded / 70px Collapsed Emerald-Teal Mesh Gradient) --}}
    <aside
        :class="[
            sidebarCollapsed ? 'lg:w-[70px]' : 'lg:w-[240px]',
            mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
        ]"
        class="fixed inset-y-0 left-0 z-40 w-[240px] flex flex-col bg-gradient-to-b from-[#064e3b] via-[#047857] to-[#0f766e] border-r border-white/15 shadow-[4px_0_25px_rgba(4,120,87,0.25)] transition-all duration-200 ease-in-out no-print"
    >
        {{-- Brand Logo Header (58px) --}}
        <div class="h-[58px] shrink-0 flex items-center justify-center px-3 bg-[#064e3b]/95 border-b border-white/15">
            <a
                href="{{ $isBattery ? route('battery.dashboard') : route('retail.dashboard') }}"
                wire:navigate
                class="flex items-center justify-center gap-2 overflow-hidden"
            >
                <img
                    x-show="sidebarCollapsed"
                    src="{{ asset('backend/assets/icons/favicon.svg') }}"
                    alt="MARSS"
                    class="w-8 h-8 object-contain rounded-md"
                    style="display: none;"
                >
                <img
                    x-show="!sidebarCollapsed"
                    src="{{ asset('backend/assets/img/marss-corporation-icon.svg') }}"
                    alt="MARSS CORPORATION"
                    class="h-10 w-auto max-w-[175px] object-contain rounded-lg"
                >
            </a>
        </div>

        {{-- Sliding Drilldown Sidebar Menu Panels --}}
        @if($isBattery)
            @include('layouts.partials.sidebar-battery')
        @else
            @include('layouts.partials.sidebar-retail')
        @endif

        {{-- Fixed Bottom Logout --}}
        <div class="shrink-0 p-2 bg-[#064e3b]/95 border-t border-white/15">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button
                    type="submit"
                    class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-xl bg-red-500/20 hover:bg-red-500/35 text-red-100 hover:text-white text-[13px] font-semibold transition-colors cursor-pointer"
                    title="Log Out"
                >
                    <i class="fa-solid fa-right-from-bracket text-sm shrink-0"></i>
                    <span x-show="!sidebarCollapsed" class="truncate">Log Out</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- Top Navbar (58px Compact) --}}
    <header
        :class="sidebarCollapsed ? 'lg:left-[70px]' : 'lg:left-[240px]'"
        class="fixed top-0 right-0 left-0 z-30 h-[58px] bg-white dark:bg-[#0b0f19] border-b border-slate-200 dark:border-slate-800 shadow-xs transition-all duration-200 flex items-center justify-between px-3 sm:px-4 no-print"
    >
        <div class="flex items-center gap-2 sm:gap-3">
            {{-- Sidebar Toggle Button --}}
            <button
                type="button"
                @click="toggleSidebar()"
                title="Toggle Sidebar"
                class="w-[38px] h-[38px] rounded-[10px] bg-emerald-50 dark:bg-slate-800 border border-emerald-200 dark:border-slate-700 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-slate-700 flex items-center justify-center shadow-xs transition-all cursor-pointer"
            >
                <i class="fa-solid fa-bars-staggered text-[15px]"></i>
            </button>

            {{-- Shop Context Switcher (Retail Shop <-> Battery) --}}
            <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                <button
                    type="button"
                    @click="open = !open"
                    class="h-[34px] inline-flex items-center gap-2 px-3 rounded-xl border font-semibold text-xs sm:text-[13px] transition-all shadow-xs cursor-pointer {{ $isBattery ? 'bg-amber-500/10 border-amber-500/30 text-amber-700 dark:text-amber-300' : 'bg-emerald-500/10 border-emerald-500/30 text-emerald-700 dark:text-emerald-300' }}"
                >
                    <i class="{{ $isBattery ? 'fa-solid fa-car-battery text-amber-500' : 'fa-solid fa-shop text-emerald-600' }}"></i>
                    <span class="hidden sm:inline">{{ $isBattery ? 'Battery' : 'Retail Shop' }}</span>
                    <i class="fa-solid fa-chevron-down text-[9px] opacity-70"></i>
                </button>
                <div
                    x-show="open"
                    x-transition
                    style="display: none;"
                    class="absolute left-0 mt-1.5 w-44 rounded-xl bg-white dark:bg-slate-900 border-[1.5px] border-slate-200 dark:border-slate-700 shadow-xl py-1 z-50"
                >
                    <a
                        href="{{ route('retail.dashboard') }}"
                        wire:navigate
                        @click="open = false"
                        class="flex items-center justify-between px-3 py-2 text-xs sm:text-sm transition-colors {{ !$isBattery ? 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 font-bold' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800' }}"
                    >
                        <span class="flex items-center gap-2"><i class="fa-solid fa-shop text-emerald-600"></i> Retail Shop</span>
                        @if(!$isBattery)<i class="fa-solid fa-check text-emerald-600 text-xs"></i>@endif
                    </a>
                    <a
                        href="{{ route('battery.dashboard') }}"
                        wire:navigate
                        @click="open = false"
                        class="flex items-center justify-between px-3 py-2 text-xs sm:text-sm transition-colors {{ $isBattery ? 'bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 font-bold' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800' }}"
                    >
                        <span class="flex items-center gap-2"><i class="fa-solid fa-car-battery text-amber-500"></i> Battery</span>
                        @if($isBattery)<i class="fa-solid fa-check text-amber-600 text-xs"></i>@endif
                    </a>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-1.5 sm:gap-2">
            {{-- Live Date & Time Pill --}}
            <div class="hidden md:inline-flex items-center gap-2 h-[34px] px-3 rounded-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-200">
                <i class="fa-regular fa-calendar-days text-emerald-600 dark:text-emerald-400"></i>
                <span x-text="dateText"></span>
                <span class="opacity-40">•</span>
                <i class="fa-regular fa-clock text-sky-600 dark:text-sky-400"></i>
                <span x-text="timeText" class="tabular-nums"></span>
            </div>

            {{-- Light/Dark Theme Toggle --}}
            <button
                type="button"
                @click="toggleTheme()"
                title="Toggle Light / Dark Theme"
                class="w-[32px] h-[32px] rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center transition-colors cursor-pointer"
            >
                <i x-show="!dark" class="fa-regular fa-moon text-slate-700 text-sm"></i>
                <i x-show="dark" class="fa-regular fa-sun text-amber-400 text-sm" style="display: none;"></i>
            </button>

            {{-- Fullscreen Toggle --}}
            <button
                type="button"
                @click="toggleFullscreen()"
                title="Toggle Fullscreen"
                class="hidden sm:flex w-[32px] h-[32px] rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 items-center justify-center transition-colors cursor-pointer"
            >
                <i :class="fullscreen ? 'fa-solid fa-compress' : 'fa-solid fa-expand'" class="text-xs"></i>
            </button>

            {{-- Low Stock & Activity Log Notification Bell --}}
            <livewire:shared.layout.notification-bell :module="$isBattery ? 'battery' : 'retail'" />

            {{-- User Profile Dropdown --}}
            <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                <button
                    type="button"
                    @click="open = !open"
                    class="flex items-center gap-2 p-1 pr-2 rounded-xl bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 border border-transparent hover:border-slate-200 dark:hover:border-slate-700 transition-all cursor-pointer"
                >
                    <img
                        src="{{ $user?->avatarUrl() ?? asset('backend/assets/img/profile-img.png') }}"
                        alt="Avatar"
                        class="w-[34px] h-[34px] rounded-full object-cover border-[1.5px] border-emerald-600"
                    >
                    <div class="hidden md:flex flex-col text-left leading-tight">
                        <span class="text-xs font-bold text-slate-800 dark:text-white max-w-[110px] truncate">{{ $user?->name ?? 'User' }}</span>
                        <span class="inline-block w-fit mt-0.5 px-1.5 py-0.5 rounded text-[9px] font-bold uppercase {{ $roleBadgeClass }}">
                            {{ $user?->role ?? 'user' }}
                        </span>
                    </div>
                    <i class="fa-solid fa-chevron-down text-[9px] text-slate-400 hidden md:inline"></i>
                </button>
                <div
                    x-show="open"
                    x-transition
                    style="display: none;"
                    class="absolute right-0 mt-1.5 w-56 rounded-2xl bg-white dark:bg-slate-900 border-[1.5px] border-slate-200 dark:border-slate-700 shadow-xl overflow-hidden z-50"
                >
                    <div class="p-3 bg-slate-50 dark:bg-slate-800/80 border-b border-slate-200 dark:border-slate-700">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-sm font-bold text-slate-800 dark:text-white truncate">{{ $user?->name ?? 'User' }}</span>
                            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase {{ $roleBadgeClass }}">{{ $user?->role ?? 'user' }}</span>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5">{{ $user?->email ?? $user?->mobile }}</p>
                    </div>
                    <div class="py-1">
                        <a
                            href="{{ route('settings.profile') }}"
                            wire:navigate
                            @click="open = false"
                            class="flex items-center gap-2 px-3 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800"
                        >
                            <i class="fa-solid fa-circle-user text-emerald-600 w-4"></i> Profile
                        </a>
                        <div class="my-1 border-t border-slate-200 dark:border-slate-800"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button
                                type="submit"
                                class="w-full flex items-center gap-2 px-3 py-2 text-xs font-bold text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30 cursor-pointer"
                            >
                                <i class="fa-solid fa-right-from-bracket w-4"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    {{-- Main Page Workspace (min-h-screen flex-col guarantees Sticky Footer at all zoom levels) --}}
    <div
        :class="sidebarCollapsed ? 'lg:ml-[70px]' : 'lg:ml-[240px]'"
        class="min-h-screen flex flex-col pt-[58px] transition-all duration-200"
    >
        <main class="flex-1 p-2.5 sm:p-4">
            {{ $slot }}
        </main>

        {{-- Sticky Footer Copyright Standard --}}
        <footer class="mt-auto bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-t border-slate-200 dark:border-slate-800 py-3 px-4 text-center text-xs sm:text-[13px] text-slate-500 dark:text-slate-400 no-print">
            &copy; {{ date('Y') }} MARSS CORPORATION | Software By:
            <a href="https://www.codenextit.com" target="_blank" class="text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 font-bold hover:underline transition-colors">CodeNext IT</a>
        </footer>
    </div>

    {{-- Global Alpine Toast Notification Stack --}}
    <div
        x-data="{
            toasts: [],
            add(e) {
                const d = e.detail || {};
                const payload = Array.isArray(d) ? d[0] : d;
                const id = Date.now() + Math.random();
                this.toasts.push({ id, type: payload.type || 'success', message: payload.message || '' });
                setTimeout(() => { this.toasts = this.toasts.filter(t => t.id !== id); }, 3800);
            }
        }"
        @toast.window="add($event)"
        class="fixed bottom-5 right-5 z-[100] flex flex-col gap-2 max-w-sm w-full pointer-events-none no-print"
    >
        <template x-for="t in toasts" :key="t.id">
            <div
                x-transition
                :class="t.type === 'error'
                    ? 'bg-gradient-to-r from-red-700 to-red-600 text-white'
                    : 'bg-gradient-to-r from-emerald-700 to-emerald-600 text-white'"
                class="pointer-events-auto flex items-center gap-2.5 px-4 py-3 rounded-xl shadow-xl text-sm font-semibold"
            >
                <i :class="t.type === 'error' ? 'fa-solid fa-circle-exclamation' : 'fa-solid fa-circle-check'"></i>
                <span x-text="t.message" class="flex-1"></span>
            </div>
        </template>
    </div>

    @livewireScripts
</body>
</html>
