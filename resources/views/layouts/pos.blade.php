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
<body class="font-sans antialiased bg-slate-100 dark:bg-[#0b0f19] text-slate-900 dark:text-slate-100 h-screen overflow-hidden flex flex-col">
    {{ $slot }}

    {{-- Global Alpine Toast --}}
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
        class="fixed bottom-5 right-5 z-[100] flex flex-col gap-2 max-w-sm w-full pointer-events-none"
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
