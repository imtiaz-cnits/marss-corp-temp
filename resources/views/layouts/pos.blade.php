<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'POS Terminal' }} - MARSS CORPORATION</title>
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
<body class="font-sans antialiased bg-slate-100 dark:bg-[#0b0f19] text-slate-900 dark:text-slate-100 h-screen overflow-hidden flex flex-col relative">
    {{-- POS Navigation Loading Placeholder Skeleton --}}
    <div
        id="marss-pos-skeleton"
        style="display: none;"
        class="fixed inset-0 z-50 bg-[#f8fafc] dark:bg-[#0b0f19] p-2 flex-col gap-2"
        aria-hidden="true"
    >
        <div class="h-[42px] bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 flex items-center justify-between">
            <div class="h-7 w-36 rounded-full marss-skeleton"></div>
            <div class="h-6 w-48 rounded-full marss-skeleton"></div>
            <div class="flex items-center gap-2">
                <div class="h-7 w-36 rounded-full marss-skeleton hidden md:block"></div>
                <div class="h-7 w-7 rounded-lg marss-skeleton"></div>
                <div class="h-7 w-7 rounded-lg marss-skeleton"></div>
                <div class="h-8 w-8 rounded-full marss-skeleton"></div>
            </div>
        </div>
        <div class="flex-1 min-h-0 grid grid-cols-1 lg:grid-cols-12 gap-2.5 overflow-hidden">
            <div class="lg:col-span-6 flex flex-col gap-2">
                <div class="h-[52px] bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-2 flex items-center gap-2">
                    <div class="flex-1 h-9 rounded-lg marss-skeleton"></div>
                    <div class="w-28 h-9 rounded-lg marss-skeleton"></div>
                    <div class="w-28 h-9 rounded-lg marss-skeleton"></div>
                </div>
                <div class="h-[56px] bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-2 flex items-center gap-2">
                    @for($b = 0; $b < 5; $b++)
                        <div class="h-10 flex-1 rounded-lg marss-skeleton"></div>
                    @endfor
                </div>
                <div class="flex-1 grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-2 content-start overflow-hidden">
                    @for($p = 0; $p < 12; $p++)
                        <div class="h-[104px] bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-2.5 flex flex-col justify-between">
                            <div class="flex items-center justify-between">
                                <div class="h-4 w-14 rounded-full marss-skeleton"></div>
                                <div class="h-4 w-12 rounded-full marss-skeleton"></div>
                            </div>
                            <div class="h-4 w-4/5 marss-skeleton"></div>
                            <div class="h-3 w-16 marss-skeleton"></div>
                        </div>
                    @endfor
                </div>
            </div>
            <div class="lg:col-span-6 hidden lg:flex flex-col gap-2">
                <div class="h-[82px] bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-2.5 space-y-2">
                    <div class="flex gap-2">
                        <div class="flex-1 h-9 rounded-lg marss-skeleton"></div>
                        <div class="w-16 h-9 rounded-lg marss-skeleton"></div>
                        <div class="w-36 h-9 rounded-lg marss-skeleton"></div>
                    </div>
                    <div class="grid grid-cols-4 gap-1.5">
                        <div class="h-6 rounded marss-skeleton"></div>
                        <div class="h-6 rounded marss-skeleton"></div>
                        <div class="h-6 rounded marss-skeleton"></div>
                        <div class="h-6 rounded marss-skeleton"></div>
                    </div>
                </div>
                <div class="flex-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-3 space-y-3">
                    <div class="h-7 w-full marss-skeleton"></div>
                    @for($c = 0; $c < 5; $c++)
                        <div class="h-8 w-full marss-skeleton"></div>
                    @endfor
                </div>
                <div class="h-[220px] bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-2.5 space-y-2">
                    <div class="grid grid-cols-6 gap-1.5">
                        @for($m = 0; $m < 6; $m++)
                            <div class="h-6 rounded marss-skeleton"></div>
                        @endfor
                    </div>
                    <div class="h-28 w-full rounded-lg marss-skeleton"></div>
                    <div class="h-8 w-full rounded-lg marss-skeleton"></div>
                </div>
            </div>
        </div>
    </div>

    {{ $slot }}

    {{-- Global Alpine Toast (Top-Right so it NEVER overlaps Cart Totals or Submit Order Button) --}}
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
        class="fixed top-14 right-4 z-[100] flex flex-col gap-2 max-w-sm w-full pointer-events-none"
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
