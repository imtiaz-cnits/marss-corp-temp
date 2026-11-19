<div
    x-data="{
        showPassword: false,
        dark: localStorage.getItem('lightMode') === 'dark',
        toggleTheme() {
            window.marssTheme?.toggle();
            this.dark = window.marssTheme?.isDark() ?? false;
        }
    }"
    class="min-h-screen w-full flex flex-col lg:flex-row bg-slate-50 dark:bg-[#0b0f19]"
>
    {{-- Left Hero Panel (46% Desktop) --}}
    <div
        class="relative lg:w-[46%] min-h-[260px] lg:min-h-screen flex flex-col justify-between p-8 sm:p-12 text-white overflow-hidden bg-cover bg-center"
        style="background-image: url('{{ asset('backend/assets/img/login-bg.jpg') }}');"
    >
        <div class="absolute inset-0 bg-gradient-to-br from-[#064e3b]/92 via-[#047857]/85 to-[#0f766e]/88"></div>

        <div class="relative z-10 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-white/95 p-1.5 shadow-lg flex items-center justify-center">
                    <img src="{{ asset('backend/assets/icons/marss-corporation-icon.svg') }}" alt="MARSS" class="w-full h-full object-contain">
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight leading-none">
                        <span class="text-red-300">MARSS</span> CORPORATION
                    </h1>
                    <p class="text-xs text-emerald-100/90 font-medium mt-0.5">Enterprise Retail &amp; Battery Management</p>
                </div>
            </div>
        </div>

        <div class="relative z-10 my-8 lg:my-0">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/15 backdrop-blur-md border border-white/20 text-xs font-semibold text-emerald-100 mb-5">
                <span class="w-2 h-2 rounded-full bg-emerald-300 animate-pulse"></span>
                Dual-Module Isolated Financial Platform
            </div>
            <h2 class="text-2xl sm:text-4xl font-extrabold leading-tight tracking-tight">
                Precision Accounting,<br>POS &amp; Inventory Control
            </h2>
            <p class="mt-3 text-sm sm:text-base text-emerald-50/85 max-w-md leading-relaxed">
                Manage Retail Shop &amp; Battery operations independently with real-time stock tracking, FIFO due collection, and audit-grade financial reporting.
            </p>

            <div class="mt-7 grid grid-cols-3 gap-3 max-w-md">
                <div class="p-3 rounded-2xl bg-white/10 backdrop-blur-md border border-white/15">
                    <i class="fa-solid fa-shop text-emerald-300 text-lg mb-1.5"></i>
                    <p class="text-xs font-bold">Retail Shop</p>
                    <p class="text-[11px] text-emerald-100/80">Hardware &amp; POS</p>
                </div>
                <div class="p-3 rounded-2xl bg-white/10 backdrop-blur-md border border-white/15">
                    <i class="fa-solid fa-car-battery text-amber-300 text-lg mb-1.5"></i>
                    <p class="text-xs font-bold">Battery Unit</p>
                    <p class="text-[11px] text-emerald-100/80">Isolated Ledger</p>
                </div>
                <div class="p-3 rounded-2xl bg-white/10 backdrop-blur-md border border-white/15">
                    <i class="fa-solid fa-shield-halved text-sky-300 text-lg mb-1.5"></i>
                    <p class="text-xs font-bold">RBAC Ready</p>
                    <p class="text-[11px] text-emerald-100/80">33 Permissions</p>
                </div>
            </div>
        </div>

        <div class="relative z-10 text-xs text-emerald-100/75 flex items-center justify-between">
            <span>&copy; {{ date('Y') }} MARSS CORPORATION</span>
            <span>Software By: <strong class="text-white">CodeNext IT</strong></span>
        </div>
    </div>

    {{-- Right Login Form Panel (54% Desktop) --}}
    <div class="flex-1 flex flex-col justify-center items-center p-6 sm:p-10 relative">
        {{-- Theme Toggle --}}
        <button
            type="button"
            @click="toggleTheme()"
            title="Toggle Light / Dark Theme"
            class="absolute top-5 right-5 w-10 h-10 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 hover:border-emerald-600 flex items-center justify-center shadow-xs transition-colors cursor-pointer"
        >
            <i x-show="!dark" class="fa-regular fa-moon text-slate-700"></i>
            <i x-show="dark" class="fa-regular fa-sun text-amber-400" style="display: none;"></i>
        </button>

        <div class="w-full max-w-[440px]">
            <div class="bg-white dark:bg-slate-900 rounded-3xl border-[1.5px] border-slate-300 dark:border-slate-700 shadow-xl p-7 sm:p-9">
                <div class="mb-7">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-xs font-bold mb-3">
                        <i class="fa-solid fa-lock text-[11px]"></i> Secure Portal Access
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Welcome Back</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Sign in with your authorized MARSS credentials.</p>
                </div>

                @if($errorMessage)
                    <div class="mb-5 flex items-start gap-2.5 p-3.5 rounded-xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 text-xs sm:text-sm font-semibold">
                        <i class="fa-solid fa-circle-exclamation mt-0.5 shrink-0"></i>
                        <span>{{ $errorMessage }}</span>
                    </div>
                @endif

                <form wire:submit="authenticate" class="space-y-5">
                    <div>
                        <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                            Email or Mobile Number
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                                <i class="fa-regular fa-envelope"></i>
                            </span>
                            <input
                                id="email"
                                type="text"
                                wire:model="email"
                                autocomplete="username"
                                placeholder="admin@gmail.com"
                                class="w-full h-11 pl-10 pr-4 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium text-slate-900 dark:text-white placeholder-slate-400 focus:border-emerald-700 focus:ring-2 focus:ring-emerald-700/20 outline-none transition-all"
                            >
                        </div>
                        @error('email')
                            <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                            Password
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                                <i class="fa-solid fa-key"></i>
                            </span>
                            <input
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                wire:model="password"
                                autocomplete="current-password"
                                placeholder="••••••••"
                                class="w-full h-11 pl-10 pr-11 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium text-slate-900 dark:text-white placeholder-slate-400 focus:border-emerald-700 focus:ring-2 focus:ring-emerald-700/20 outline-none transition-all"
                            >
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-emerald-600 cursor-pointer"
                                tabindex="-1"
                            >
                                <i :class="showPassword ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye'"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="inline-flex items-center gap-2 text-xs sm:text-sm font-medium text-slate-600 dark:text-slate-300 cursor-pointer select-none">
                            <input
                                type="checkbox"
                                wire:model="remember"
                                class="w-4 h-4 rounded border-slate-300 text-emerald-700 focus:ring-emerald-600"
                            >
                            Remember me
                        </label>
                        <span class="text-xs text-slate-400">Pabna, Bangladesh</span>
                    </div>

                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="w-full h-11 rounded-xl bg-gradient-to-r from-emerald-700 to-emerald-600 hover:from-emerald-800 hover:to-emerald-700 text-white font-bold text-sm shadow-lg shadow-emerald-700/25 flex items-center justify-center gap-2 transition-all cursor-pointer disabled:opacity-60"
                    >
                        <span wire:loading.remove wire:target="authenticate" class="flex items-center gap-2">
                            <i class="fa-solid fa-right-to-bracket"></i> Sign In to Workspace
                        </span>
                        <span wire:loading wire:target="authenticate" class="flex items-center gap-2">
                            <i class="fa-solid fa-spinner fa-spin"></i> Verifying...
                        </span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
