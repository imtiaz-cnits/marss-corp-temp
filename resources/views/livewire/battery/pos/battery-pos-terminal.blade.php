@php
    use App\Support\Money;
    $user = auth()->user();
    $custTotalDue = 0;
    if ($selectedCustomer) {
        $custTotalDue = (float) ($selectedCustomer->previous_due_amount ?? 0) + (float) ($selectedCustomer->orders_due_sum ?? 0);
    }
    $paidFloat = (float) ($paid_amount ?: 0);
    $netFloat = (float) ($netPayable ?: 0);
    $paymentStatusLabel = ($paidFloat >= $netFloat && $netFloat > 0)
        ? 'PAID'
        : ($paidFloat > 0 ? 'PARTIAL' : 'DUE');
@endphp

<div
    x-data="{
        scannerOpen: false,
        scanner: null,
        custDropdownOpen: false,
        custSearch: '',
        holdModalOpen: false,
        heldInvoices: [],
        mobileTab: 'products',
        dark: localStorage.getItem('lightMode') === 'dark',
        fullscreen: false,
        dateText: '',
        timeText: '',
        init() {
            this.loadHeldInvoices();
            this.updateClock();
            if (!window._marssPosClockTimer) {
                window._marssPosClockTimer = setInterval(() => {
                    window.dispatchEvent(new CustomEvent('marss-pos-tick'));
                }, 1000);
            }
            window.addEventListener('marss-pos-tick', () => this.updateClock());
            window.addEventListener('pos-beep', () => window.marssPlayBeep?.());
            window.addEventListener('marss-theme-changed', (e) => { this.dark = e.detail.dark; });
        },
        updateClock() {
            const now = new Date();
            this.dateText = now.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
            this.timeText = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
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
        },
        scrollBrands(dir) {
            const el = this.$refs.brandTrack;
            if (el) el.scrollBy({ left: dir * 240, behavior: 'smooth' });
        },
        loadHeldInvoices() {
            try {
                this.heldInvoices = JSON.parse(localStorage.getItem('battery_pos_held_invoices') || '[]');
            } catch {
                this.heldInvoices = [];
            }
        },
        holdCurrentOrder() {
            const cart = $wire.cart || [];
            if (!cart.length) {
                window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: 'Cart is empty! Add products before holding.' } }));
                return;
            }
            const holdId = 'HOLD-' + Math.floor(1000 + Math.random() * 9000);
            const now = new Date();
            const timeStr = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) + ', ' + now.toLocaleDateString('en-GB');
            const totalItems = cart.reduce((acc, i) => acc + (parseInt(i.quantity) || 1), 0);
            const subTotal = cart.reduce((acc, i) => acc + ((parseFloat(i.selling_price) || 0) * (parseInt(i.quantity) || 1)), 0) + (parseFloat($wire.delivery_charge) || 0);

            const entry = {
                id: holdId,
                customer_id: $wire.customer_id,
                customer_name: '{{ addslashes($selectedCustomer?->customer_name ?? 'Walk-in Customer') }}',
                customer_mobile: '{{ addslashes($selectedCustomer?->mobile ?? '') }}',
                invoice_date: $wire.invoice_date,
                discount_type: $wire.discount_type,
                discount_value: $wire.discount_value,
                delivery_charge: $wire.delivery_charge,
                exchange_amount: $wire.exchange_amount,
                paid_amount: $wire.paid_amount,
                order_note: $wire.order_note,
                cart: JSON.parse(JSON.stringify(cart)),
                total_items: totalItems,
                sub_total: subTotal.toFixed(2),
                time: timeStr
            };
            this.loadHeldInvoices();
            this.heldInvoices.push(entry);
            localStorage.setItem('battery_pos_held_invoices', JSON.stringify(this.heldInvoices));
            $wire.clearCart();
            window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Battery Invoice Held Successfully! Ref: #' + holdId } }));
        },
        restoreHeld(item) {
            $wire.restoreHeldOrder(item);
            this.deleteHeld(item.id, false);
            this.holdModalOpen = false;
        },
        deleteHeld(holdId, notify = true) {
            this.heldInvoices = this.heldInvoices.filter(i => i.id !== holdId);
            localStorage.setItem('battery_pos_held_invoices', JSON.stringify(this.heldInvoices));
            if (notify) {
                window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Held invoice #' + holdId + ' deleted.' } }));
            }
        },
        async openScanner() {
            this.scannerOpen = true;
            await this.$nextTick();
            if (!window.Html5Qrcode) return;
            try {
                this.scanner = new window.Html5Qrcode('battery-qr-reader');
                await this.scanner.start(
                    { facingMode: 'environment' },
                    { fps: 10, qrbox: { width: 230, height: 150 } },
                    (decoded) => {
                        window.marssPlayBeep?.();
                        $wire.scanBarcode(decoded);
                        this.closeScanner();
                    }
                );
            } catch (err) {
                window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: 'Camera unavailable: ' + err } }));
                this.scannerOpen = false;
            }
        },
        async closeScanner() {
            if (this.scanner) {
                try { await this.scanner.stop(); } catch {}
                this.scanner = null;
            }
            this.scannerOpen = false;
        }
    }"
    @keydown.f4.window.prevent="$wire.completeSale()"
    class="h-screen max-h-screen w-full overflow-hidden flex flex-col bg-[#f8fafc] dark:bg-[#0b0f19] p-1 sm:px-2.5 sm:py-1.5 select-none"
>
    <style>
        .pos-product-scroll::-webkit-scrollbar { width: 5px; }
        .pos-product-scroll::-webkit-scrollbar-track { background: transparent; }
        .pos-product-scroll::-webkit-scrollbar-thumb { background: #15803d; border-radius: 9999px; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>

    {{-- 1. Top POS Navigation Bar --}}
    <nav class="shrink-0 mb-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-2.5 py-1 flex items-center justify-between gap-2 shadow-2xs min-h-[42px]">
        <a
            href="{{ route('battery.dashboard') }}"
            wire:navigate
            class="h-[32px] px-3.5 rounded-full text-white text-xs font-bold inline-flex items-center gap-1.5 shadow-xs transition-transform hover:-translate-y-0.5 shrink-0"
            style="background: linear-gradient(135deg, #15803d 0%, #16a34a 100%);"
        >
            <i class="fa-solid fa-arrow-left text-[11px]"></i>
            <span class="hidden sm:inline">Back to Dashboard</span>
            <span class="sm:hidden">Back</span>
        </a>

        <div class="flex items-center gap-2 px-3 py-0.5 rounded-full overflow-hidden">
            <img src="{{ asset('backend/assets/icons/favicon.svg') }}" alt="MARSS" class="w-[26px] h-[26px] object-contain shrink-0 rounded-md">
            <h5 class="m-0 p-0 flex items-center gap-1.5 truncate font-extrabold text-[#15803d] dark:text-emerald-400 text-[13px] sm:text-[14px]" style="font-family: 'Baloo Da 2', sans-serif;">
                <span class="truncate">MARSS CORPORATION</span>
                <span class="px-2 py-0.5 rounded-lg bg-[#15803d] text-white text-[11px] font-bold leading-none">POS</span>
            </h5>
        </div>

        <div class="flex items-center gap-1.5 shrink-0">
            <div class="hidden md:inline-flex items-center gap-2 h-[30px] px-3 rounded-full bg-[#f8fafc] dark:bg-slate-900 border border-[#cbd5e1] dark:border-slate-700 text-[#334155] dark:text-slate-200 text-[11.5px] font-semibold">
                <i class="fa-regular fa-calendar-days text-[#15803d] dark:text-emerald-400 text-xs"></i>
                <span x-text="dateText"></span>
                <span class="opacity-40">•</span>
                <i class="fa-regular fa-clock text-sky-600 dark:text-sky-400 text-xs"></i>
                <span x-text="timeText" class="tabular-nums"></span>
            </div>

            <button
                type="button"
                @click="toggleTheme()"
                title="Toggle Light/Dark Theme"
                class="w-[30px] h-[30px] rounded-lg bg-[#f1f5f9] dark:bg-slate-700 border border-[#cbd5e1] dark:border-slate-600 text-[#334155] dark:text-amber-400 hover:bg-slate-200 flex items-center justify-center cursor-pointer transition-colors"
            >
                <i x-show="!dark" class="fa-regular fa-moon text-[13px]"></i>
                <i x-show="dark" class="fa-regular fa-sun text-[13px]" style="display: none;"></i>
            </button>

            <button
                type="button"
                @click="toggleFullscreen()"
                title="Toggle Fullscreen"
                class="w-[30px] h-[30px] rounded-lg bg-[#f1f5f9] dark:bg-slate-700 border border-[#cbd5e1] dark:border-slate-600 text-[#334155] dark:text-slate-200 hover:bg-slate-200 flex items-center justify-center cursor-pointer transition-colors"
            >
                <svg x-show="!fullscreen" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7" />
                </svg>
                <svg x-show="fullscreen" style="display: none;" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 14h6v6M20 10h-6V4M14 10l7-7M3 21l7-7" />
                </svg>
            </button>

            <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                <button
                    type="button"
                    @click="open = !open"
                    class="flex items-center gap-1.5 p-0.5 rounded-full bg-white dark:bg-slate-800 cursor-pointer"
                >
                    <img
                        src="{{ $user?->avatarUrl() ?? asset('backend/assets/img/profile-img.png') }}"
                        alt="Avatar"
                        class="w-[32px] h-[32px] rounded-full object-cover border-[1.5px] border-[#16a34a]"
                    >
                </button>
                <div
                    x-show="open"
                    x-transition
                    style="display: none;"
                    class="absolute right-0 mt-1.5 w-52 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 shadow-xl overflow-hidden z-50"
                >
                    <div class="p-3 bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                        <p class="text-xs font-bold text-slate-800 dark:text-white truncate">{{ $user?->name ?? 'Admin' }}</p>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">{{ $user?->email ?? $user?->mobile }}</p>
                    </div>
                    <a href="{{ route('settings.profile') }}" wire:navigate class="flex items-center gap-2 px-3 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800">
                        <i class="fa-regular fa-user text-slate-400"></i> Profile
                    </a>
                    <a href="{{ route('battery.invoices') }}" wire:navigate class="flex items-center gap-2 px-3 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800">
                        <i class="fa-solid fa-file-invoice text-emerald-600"></i> Battery Invoices
                    </a>
                </div>
            </div>
        </div>
    </nav>

    {{-- 2. Main 2-Column Split POS Area --}}
    <div class="flex-1 min-h-0 grid grid-cols-1 lg:grid-cols-12 gap-2.5 overflow-hidden">
        {{-- LEFT COLUMN (6 cols) --}}
        <div
            :class="mobileTab === 'cart' ? 'hidden lg:flex' : 'flex'"
            class="lg:col-span-6 flex-col min-h-0 h-full overflow-hidden"
        >
            {{-- Top Search Bar + Camera Scan + New Product --}}
            <div class="shrink-0 mb-1.5 p-1.5 px-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-[10px] shadow-2xs flex flex-wrap sm:flex-nowrap items-center gap-1.5">
                <div class="relative flex-1 min-w-[180px]">
                    <input
                        type="text"
                        wire:model.live.debounce.200ms="search"
                        wire:keydown.enter.prevent="scanBarcode($event.target.value)"
                        placeholder="🔍 Scan barcode or enter code/name..."
                        autofocus
                        autocomplete="off"
                        class="w-full h-[38px] pl-3 pr-10 rounded-[8px] border border-[#cbd5e1] dark:border-slate-600 bg-white dark:bg-slate-900 text-[12.5px] font-medium text-slate-800 dark:text-slate-100 placeholder:text-slate-400 focus:border-[#16a34a] focus:ring-2 focus:ring-emerald-500/15 outline-none"
                    >
                    <button
                        type="button"
                        wire:click="scanBarcode"
                        title="Search / Scan"
                        class="absolute right-[5px] top-1/2 -translate-y-1/2 w-[28px] h-[28px] rounded-[6px] text-white flex items-center justify-center cursor-pointer"
                        style="background: linear-gradient(135deg, #15803d 0%, #16a34a 100%);"
                    >
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </button>
                </div>

                <div class="flex items-center gap-1.5 w-full sm:w-auto shrink-0">
                    <button
                        type="button"
                        @click="openScanner()"
                        class="flex-1 sm:flex-initial h-[38px] px-3 rounded-[8px] text-white text-[12.5px] font-bold inline-flex items-center justify-center gap-1.5 shadow-xs cursor-pointer whitespace-nowrap"
                        style="background: linear-gradient(135deg, #15803d 0%, #16a34a 100%);"
                    >
                        <i class="fa-solid fa-camera text-sm"></i>
                        <span>Camera Scan</span>
                    </button>
                    <button
                        type="button"
                        wire:click="$set('showProductModal', true)"
                        class="flex-1 sm:flex-initial h-[38px] px-3 rounded-[8px] bg-[#eab308] hover:bg-[#ca8a04] text-slate-950 text-[12.5px] font-bold inline-flex items-center justify-center gap-1.5 shadow-xs cursor-pointer whitespace-nowrap transition-colors"
                    >
                        <i class="fa-solid fa-circle-plus text-sm text-slate-950"></i>
                        <span>New Product</span>
                    </button>
                </div>
            </div>

            {{-- Horizontal Brand Slider --}}
            <div class="shrink-0 mb-1.5 relative bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-[12px] py-1.5 px-9 shadow-2xs flex items-center">
                <button
                    type="button"
                    @click="scrollBrands(-1)"
                    class="absolute left-1.5 top-1/2 -translate-y-1/2 w-[28px] h-[28px] rounded-full bg-[#15803d] hover:bg-[#166534] text-white flex items-center justify-center shadow-sm cursor-pointer z-10 transition-transform hover:scale-105"
                >
                    <i class="fa-solid fa-chevron-left text-xs"></i>
                </button>

                <div x-ref="brandTrack" class="flex items-center gap-2 overflow-x-auto no-scrollbar scroll-smooth w-full py-0.5">
                    <button
                        type="button"
                        wire:click="selectBrand('')"
                        class="h-[42px] min-w-[115px] px-3 rounded-[10px] border-[1.5px] text-xs font-bold flex items-center justify-center text-center shrink-0 cursor-pointer transition-all {{ $brandFilter === '' ? 'bg-gradient-to-br from-[#15803d] to-[#16a34a] border-[#15803d] text-white font-extrabold shadow-md -translate-y-0.5' : 'bg-[#f8fafc] dark:bg-slate-900 border-[#e2e8f0] dark:border-slate-700 text-[#1e293b] dark:text-slate-100 hover:border-[#16a34a] hover:bg-[#f0fdf4]' }}"
                    >
                        All Brands
                    </button>
                    @foreach($brands as $b)
                        <button
                            type="button"
                            wire:click="selectBrand('{{ $b->id }}')"
                            class="h-[42px] min-w-[115px] px-3 rounded-[10px] border-[1.5px] text-xs font-bold flex items-center justify-center text-center shrink-0 cursor-pointer transition-all {{ (string) $brandFilter === (string) $b->id ? 'bg-gradient-to-br from-[#15803d] to-[#16a34a] border-[#15803d] text-white font-extrabold shadow-md -translate-y-0.5' : 'bg-[#f8fafc] dark:bg-slate-900 border-[#e2e8f0] dark:border-slate-700 text-[#1e293b] dark:text-slate-100 hover:border-[#16a34a] hover:bg-[#f0fdf4]' }}"
                        >
                            {{ $b->name }}
                        </button>
                    @endforeach
                </div>

                <button
                    type="button"
                    @click="scrollBrands(1)"
                    class="absolute right-1.5 top-1/2 -translate-y-1/2 w-[28px] h-[28px] rounded-full bg-[#15803d] hover:bg-[#166534] text-white flex items-center justify-center shadow-sm cursor-pointer z-10 transition-transform hover:scale-105"
                >
                    <i class="fa-solid fa-chevron-right text-xs"></i>
                </button>
            </div>

            {{-- 4-Column Product Card Grid --}}
            <div class="flex-1 min-h-0 overflow-y-auto pr-1 pos-product-scroll">
                <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-2 content-start pb-2">
                    @forelse($products as $p)
                        @php
                            $stock = (int) round((float) ($p->quantity ?? 0));
                            $isOut = $stock <= 0;
                            $brandName = $p->brand?->name ?: 'General';
                            $rawCode = $p->primaryCode();
                            $shortCode = strlen($rawCode) > 6 ? substr($rawCode, 0, 4).'...' : ($rawCode ?: 'N/A');
                            $sellPrice = (float) ($p->sell_price ?? 0) > 0 ? $p->sell_price : $p->cost_price;
                        @endphp
                        <div
                            wire:key="bat-pos-prod-{{ $p->id }}"
                            wire:click="addToCart({{ $p->id }})"
                            class="group bg-white dark:bg-slate-800 rounded-[12px] border-[1.5px] {{ $isOut ? 'border-[#ef4444] hover:border-[#dc2626]' : 'border-[#e2e8f0] dark:border-slate-700 hover:border-[#16a34a]' }} p-2 px-2.5 min-h-[100px] flex flex-col justify-between shadow-2xs hover:shadow-md hover:-translate-y-0.5 active:scale-[0.98] transition-all cursor-pointer"
                        >
                            <div class="flex items-start justify-between gap-1 min-h-[24px]">
                                <div class="min-w-0 flex-1 overflow-hidden">
                                    <span class="inline-block group-hover:hidden text-[9.5px] font-semibold text-[#15803d] dark:text-emerald-300 bg-[#dcfce7] dark:bg-emerald-950/60 border border-[#bbf7d0] dark:border-emerald-800 rounded-[10px] px-1.5 py-0.5 truncate max-w-full leading-tight">
                                        {{ $brandName }}
                                    </span>
                                    <div class="hidden group-hover:flex flex-col leading-none">
                                        <span class="text-[12px] font-extrabold text-[#16a34a] dark:text-emerald-400">৳ {{ number_format((float) $sellPrice, 0) }}</span>
                                        <span class="text-[9.5px] font-semibold text-[#dc2626] dark:text-rose-400 mt-0.5">Cost: ৳ {{ number_format((float) ($p->cost_price ?? 0), 0) }}</span>
                                    </div>
                                </div>
                                <span class="shrink-0 text-[11px] font-bold px-2 py-0.5 rounded-[12px] leading-tight border {{ $isOut ? 'bg-[#fee2e2] text-[#991b1b] border-[#fca5a5] dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800' : 'bg-[#dcfce7] text-[#15803d] border-[#bbf7d0] dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800' }}">
                                    {{ $stock }} Pcs
                                </span>
                            </div>

                            <div class="my-1 flex-1 flex items-center">
                                <h3 class="text-[12.5px] font-bold text-[#0f172a] dark:text-slate-100 group-hover:text-[#15803d] dark:group-hover:text-emerald-400 leading-[1.3] line-clamp-2 m-0">
                                    {{ $p->product_name }}
                                </h3>
                            </div>

                            <div class="flex items-center justify-between gap-1 pt-0.5">
                                <span class="text-[12px] font-bold font-mono text-[#0d9488] dark:text-teal-400 truncate max-w-[70px]" title="{{ $rawCode }}">
                                    {{ $shortCode }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-12 text-center text-sm font-semibold text-slate-400 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
                            No Battery products found. Click "+ New Product" to create one.
                        </div>
                    @endforelse
                </div>

                @if($totalMatchingProducts > $productLimit)
                    <div class="text-center my-2">
                        <button
                            type="button"
                            wire:click="loadMoreProducts"
                            class="px-4 py-1.5 rounded-full border-[1.5px] border-[#15803d] text-[#15803d] dark:text-emerald-400 hover:bg-[#15803d] hover:text-white text-xs font-bold transition-colors cursor-pointer"
                        >
                            <i class="fa-solid fa-circle-plus mr-1"></i>
                            Show 25 more products ({{ $totalMatchingProducts - $productLimit }} remaining)
                        </button>
                    </div>
                @endif
            </div>
        </div>

        {{-- RIGHT COLUMN (6 cols) --}}
        <div
            :class="mobileTab === 'products' ? 'hidden lg:flex' : 'flex'"
            class="lg:col-span-6 flex-col min-h-0 h-full overflow-hidden"
        >
            {{-- Customer & Date Box + 4-Field Strip --}}
            <div class="shrink-0 mb-1 p-1.5 px-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-[10px] shadow-2xs">
                <div class="grid grid-cols-12 gap-1.5 items-center">
                    <div class="col-span-12 sm:col-span-7 flex items-center gap-1.5">
                        <div @click.outside="custDropdownOpen = false" class="relative flex-1 min-w-0">
                            <button
                                type="button"
                                @click="custDropdownOpen = !custDropdownOpen"
                                class="w-full h-[38px] px-3 rounded-[8px] border border-[#cbd5e1] dark:border-slate-600 bg-[#f8fafc] dark:bg-slate-900 hover:border-[#16a34a] hover:bg-white text-xs font-semibold text-[#334155] dark:text-slate-100 flex items-center justify-between gap-2 cursor-pointer transition-colors"
                            >
                                <span class="truncate">
                                    @if($selectedCustomer)
                                        {{ $selectedCustomer->customer_id }} - {{ $selectedCustomer->customer_name }} ({{ $selectedCustomer->mobile }})
                                    @else
                                        Select Customer
                                    @endif
                                </span>
                                <i class="fa-solid fa-angle-down text-xs text-slate-500 transition-transform" :class="custDropdownOpen ? 'rotate-180' : ''"></i>
                            </button>

                            <div
                                x-show="custDropdownOpen"
                                x-transition
                                style="display: none;"
                                class="absolute left-0 right-0 top-[calc(100%+4px)] z-50 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-[8px] shadow-xl p-1.5 max-h-[240px] flex flex-col"
                            >
                                <input
                                    type="text"
                                    x-model="custSearch"
                                    placeholder="Search customer..."
                                    class="w-full h-[32px] px-2.5 mb-1 rounded-[6px] border border-slate-200 dark:border-slate-600 bg-[#f8fafc] dark:bg-slate-900 text-xs text-slate-800 dark:text-slate-100 outline-none focus:border-[#16a34a]"
                                >
                                <div class="overflow-y-auto divide-y divide-slate-100 dark:divide-slate-700/50">
                                    @foreach($customers as $c)
                                        @php
                                            $cDue = (float) ($c->previous_due_amount ?? 0) + (float) ($c->orders_due_sum ?? 0);
                                            $searchText = strtolower($c->customer_id.' '.$c->customer_name.' '.$c->mobile);
                                        @endphp
                                        <div
                                            x-show="!custSearch || '{{ addslashes($searchText) }}'.includes(custSearch.toLowerCase())"
                                            wire:click="selectCustomer({{ $c->id }})"
                                            @click="custDropdownOpen = false; custSearch = ''"
                                            class="px-2.5 py-1.5 rounded-[6px] text-[12.5px] font-bold text-slate-900 dark:text-slate-100 hover:bg-[#f0fdf4] hover:text-[#15803d] dark:hover:bg-emerald-950/60 flex items-center justify-between gap-2 cursor-pointer"
                                        >
                                            <span class="truncate">{{ $c->customer_id }} - {{ $c->customer_name }} <span class="text-slate-500 font-semibold">({{ $c->mobile }})</span></span>
                                            @if($cDue > 0)
                                                <span class="shrink-0 px-1.5 py-0.5 rounded bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-300 border border-rose-200 dark:border-rose-800 text-[11px] font-bold">
                                                    Due: ৳{{ number_format($cDue, 0) }}
                                                </span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <button
                            type="button"
                            wire:click="$set('showCustomerModal', true)"
                            class="h-[38px] px-4 rounded-[8px] text-white text-xs font-bold shrink-0 inline-flex items-center justify-center shadow-xs cursor-pointer"
                            style="background: linear-gradient(135deg, #15803d 0%, #16a34a 100%);"
                        >
                            + New
                        </button>
                    </div>

                    <div class="col-span-12 sm:col-span-5 flex items-center gap-1.5">
                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400 shrink-0">Date:</span>
                        <div class="flex-1 min-w-0">
                            <x-ui.date-picker wire:model="invoice_date" placeholder="Date" />
                        </div>
                    </div>
                </div>

                {{-- 4-Field Customer Info Strip --}}
                <div class="grid grid-cols-12 gap-1 mt-1.5 pt-1.5 border-t border-slate-200 dark:border-slate-700 items-center">
                    <div class="col-span-6 sm:col-span-4 flex items-center h-[26px] rounded-[6px] border border-slate-200 dark:border-slate-700 overflow-hidden bg-[#f8fafc] dark:bg-slate-900">
                        <span class="px-1.5 h-full flex items-center bg-slate-100 dark:bg-slate-800 border-r border-slate-200 dark:border-slate-700 text-[12px] font-extrabold text-[#475569] dark:text-slate-300 shrink-0">Name</span>
                        <input type="text" readonly value="{{ $selectedCustomer?->customer_name ?? '' }}" placeholder="Customer Name" class="w-full h-full px-1.5 bg-transparent text-[11.5px] font-bold text-slate-800 dark:text-slate-100 placeholder:text-slate-400 placeholder:font-normal outline-none">
                    </div>
                    <div class="col-span-6 sm:col-span-3 flex items-center h-[26px] rounded-[6px] border border-slate-200 dark:border-slate-700 overflow-hidden bg-[#f8fafc] dark:bg-slate-900">
                        <span class="px-1.5 h-full flex items-center bg-slate-100 dark:bg-slate-800 border-r border-slate-200 dark:border-slate-700 text-[12px] font-extrabold text-[#475569] dark:text-slate-300 shrink-0">Mobile</span>
                        <input type="text" readonly value="{{ $selectedCustomer?->mobile ?? '' }}" placeholder="Mobile" class="w-full h-full px-1.5 bg-transparent text-[11.5px] font-bold text-slate-800 dark:text-slate-100 placeholder:text-slate-400 placeholder:font-normal outline-none">
                    </div>
                    <div class="col-span-7 sm:col-span-3 flex items-center h-[26px] rounded-[6px] border border-slate-200 dark:border-slate-700 overflow-hidden bg-[#f8fafc] dark:bg-slate-900">
                        <span class="px-1.5 h-full flex items-center bg-slate-100 dark:bg-slate-800 border-r border-slate-200 dark:border-slate-700 text-[12px] font-extrabold text-[#475569] dark:text-slate-300 shrink-0">Address</span>
                        <input type="text" readonly value="{{ $selectedCustomer?->address_details ?? '' }}" placeholder="Address" class="w-full h-full px-1.5 bg-transparent text-[11.5px] font-medium text-slate-800 dark:text-slate-100 placeholder:text-slate-400 placeholder:font-normal outline-none">
                    </div>
                    <div class="col-span-5 sm:col-span-2 flex items-center h-[26px] rounded-[6px] border border-rose-200 dark:border-rose-800/80 overflow-hidden bg-[#fff5f5] dark:bg-rose-950/30">
                        <span class="px-1.5 h-full flex items-center bg-rose-100/80 dark:bg-rose-950/70 border-r border-rose-200 dark:border-rose-800 text-[12px] font-extrabold text-[#dc2626] dark:text-rose-400 shrink-0">Due</span>
                        <input type="text" readonly value="{{ number_format($custTotalDue, 0) }}" class="w-full h-full px-1 bg-transparent text-[11.5px] font-extrabold text-[#dc2626] dark:text-rose-400 text-center outline-none">
                    </div>
                </div>
            </div>

            {{-- Middle Scrollable Cart Table --}}
            <div class="flex-1 min-h-[60px] overflow-y-auto bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-[10px] mb-1 shadow-2xs">
                <table class="w-full border-collapse table-fixed">
                    <thead class="sticky top-0 z-10 bg-[#f8fafc] dark:bg-slate-900 border-b-2 border-slate-200 dark:border-slate-700">
                        <tr class="text-[11px] font-extrabold uppercase text-[#475569] dark:text-slate-300 tracking-wide">
                            <th class="w-[26%] py-1.5 pl-3 pr-1.5 text-left">Product</th>
                            <th class="w-[22%] py-1.5 px-1 text-center">Quantity</th>
                            <th class="w-[14%] py-1.5 px-1 text-center">Cost Price</th>
                            <th class="w-[13%] py-1.5 px-1 text-center">Sell Price</th>
                            <th class="w-[15%] py-1.5 px-1 text-center">Sub Total</th>
                            <th class="w-[10%] py-1.5 px-1 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 text-xs">
                        @forelse($cart as $idx => $line)
                            @php
                                $rowTotal = (float) ($line['selling_price'] ?? 0) * (int) ($line['quantity'] ?? 1);
                            @endphp
                            <tr wire:key="bat-cart-row-{{ $line['product_id'] }}" class="hover:bg-[#f0fdf4] dark:hover:bg-slate-900/60 transition-colors">
                                <td class="py-1.5 pl-3 pr-1.5 align-middle">
                                    <span class="font-bold text-slate-800 dark:text-slate-100 text-[12px] leading-tight break-words">{{ $line['product_name'] }}</span>
                                </td>
                                <td class="py-1 px-1 align-middle text-center">
                                    <div class="inline-flex items-center justify-between border-[1.5px] border-[#cbd5e1] dark:border-slate-600 rounded-[6px] bg-[#f8fafc] dark:bg-slate-900 p-0.5 w-[74px] h-[26px]">
                                        <button type="button" wire:click="decrementQty({{ $idx }})" class="w-[21px] h-[21px] rounded-[4px] bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-extrabold text-xs flex items-center justify-center shadow-2xs cursor-pointer">-</button>
                                        <input
                                            type="number"
                                            min="1"
                                            max="{{ $line['stock'] }}"
                                            wire:model.live="cart.{{ $idx }}.quantity"
                                            class="w-[26px] bg-transparent border-0 text-center font-bold text-xs text-slate-900 dark:text-white p-0 outline-none"
                                        >
                                        <button type="button" wire:click="incrementQty({{ $idx }})" class="w-[21px] h-[21px] rounded-[4px] bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-extrabold text-xs flex items-center justify-center shadow-2xs cursor-pointer">+</button>
                                    </div>
                                </td>
                                <td class="py-1 px-1 align-middle text-center" x-data="{ showCost: false }">
                                    <div class="relative inline-flex items-center justify-center" @mouseenter="showCost = true" @mouseleave="showCost = false">
                                        <span x-show="!showCost" class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-300 border border-slate-200 dark:border-slate-600 text-[11px] font-semibold cursor-pointer">
                                            🔒 ***
                                        </span>
                                        <input
                                            x-show="showCost"
                                            style="display: none;"
                                            type="number"
                                            step="any"
                                            wire:model.live.debounce.300ms="cart.{{ $idx }}.cost_price"
                                            class="w-[54px] h-[26px] rounded-[6px] border border-emerald-500 bg-white dark:bg-slate-900 text-center font-extrabold text-[11.5px] text-[#15803d] dark:text-emerald-400 outline-none px-1"
                                        >
                                    </div>
                                </td>
                                <td class="py-1 px-1 align-middle text-center">
                                    <input
                                        type="number"
                                        step="any"
                                        min="0"
                                        wire:model.live.debounce.250ms="cart.{{ $idx }}.selling_price"
                                        class="w-[54px] h-[26px] rounded-[6px] border-[1.5px] border-[#cbd5e1] dark:border-slate-600 bg-white dark:bg-slate-900 text-center font-bold text-[11.5px] text-[#15803d] dark:text-emerald-400 focus:border-[#15803d] outline-none px-1"
                                    >
                                </td>
                                <td class="py-1 px-1 align-middle text-center font-extrabold text-[12px] text-[#0f172a] dark:text-white tabular-nums">
                                    {{ number_format($rowTotal, 2) }}
                                </td>
                                <td class="py-1 px-1 align-middle text-center">
                                    <button
                                        type="button"
                                        wire:click="removeItem({{ $idx }})"
                                        title="Remove"
                                        class="w-[28px] h-[28px] rounded-[7px] bg-[#f0fdf4] hover:bg-[#15803d] text-[#15803d] hover:text-white border border-[#bbf7d0] hover:border-[#15803d] inline-flex items-center justify-center transition-all cursor-pointer"
                                    >
                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-10 text-center text-slate-400 text-xs font-semibold"></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Sticky Bottom Payment & Confirmation Section --}}
            <div class="shrink-0 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-[12px] p-2 shadow-2xs">
                <div class="flex items-center justify-between mb-1 px-0.5">
                    <span class="text-[10px] font-extrabold text-[#64748b] dark:text-slate-400 uppercase tracking-wider">PAYMENT METHOD</span>
                    <span class="text-[10.5px] font-extrabold text-[#15803d] dark:text-emerald-400 uppercase tracking-wider">{{ strtoupper($payment_method) }}</span>
                </div>

                <div class="grid grid-cols-6 gap-1 mb-1">
                    @foreach(['Cash', 'bKash', 'Nagad', 'Rocket', 'Bank', 'Card'] as $m)
                        <button
                            type="button"
                            wire:click="$set('payment_method', '{{ $m }}')"
                            class="h-[25px] rounded-[6px] border text-[10.5px] font-bold flex items-center justify-center cursor-pointer transition-all {{ $payment_method === $m ? 'bg-[#15803d] border-[#15803d] text-white' : 'bg-white dark:bg-slate-900 border-[#cbd5e1] dark:border-slate-600 text-[#1e293b] dark:text-slate-200 hover:border-[#16a34a]' }}"
                        >
                            {{ $m }}
                        </button>
                    @endforeach
                </div>

                <div class="mb-1 space-y-1">
                    <input
                        type="text"
                        wire:model="order_note"
                        placeholder="Order Note / Remark"
                        class="w-full h-[25px] px-2 rounded-[6px] border-[1.5px] border-[#cbd5e1] dark:border-slate-600 bg-white dark:bg-slate-900 text-[10.5px] text-slate-800 dark:text-slate-100 outline-none focus:border-sky-600"
                    >
                    @if($payment_method !== 'Cash')
                        <input
                            type="text"
                            wire:model="transaction_id"
                            placeholder="Enter Transaction ID (for {{ $payment_method }})"
                            class="w-full h-[25px] px-2 rounded-[6px] border-[1.5px] border-emerald-400 dark:border-emerald-700 bg-emerald-50/40 dark:bg-slate-900 text-[10.5px] text-slate-800 dark:text-slate-100 outline-none"
                        >
                    @endif
                </div>

                <div class="border-[1.5px] border-[#e2e8f0] dark:border-slate-700 rounded-[8px] px-2 py-1 bg-white dark:bg-slate-900 mb-1.5 space-y-0.5">
                    <div class="flex items-center justify-between py-0.5">
                        <span class="text-[9.5px] font-extrabold text-[#64748b] dark:text-slate-400 uppercase">SUB-TOTAL:</span>
                        <span class="text-[11.5px] font-extrabold text-[#0284c7] dark:text-sky-400 tabular-nums">৳ {{ number_format((float) $subTotal, 2) }}</span>
                    </div>

                    <div class="flex items-center justify-between py-0.5">
                        <div class="flex items-center gap-1">
                            <span class="text-[9.5px] font-extrabold text-[#64748b] dark:text-slate-400 uppercase">DISCOUNT:</span>
                            <div class="inline-flex h-[18px] rounded border border-[#cbd5e1] dark:border-slate-600 overflow-hidden">
                                <button
                                    type="button"
                                    wire:click="$set('discount_type', 'fixed')"
                                    class="px-1.5 text-[9px] font-bold leading-none cursor-pointer {{ $discount_type === 'fixed' ? 'bg-[#15803d] text-white' : 'bg-[#f8fafc] dark:bg-slate-800 text-[#64748b]' }}"
                                >৳</button>
                                <button
                                    type="button"
                                    wire:click="$set('discount_type', 'percent')"
                                    class="px-1.5 text-[9px] font-bold leading-none cursor-pointer {{ $discount_type === 'percent' ? 'bg-[#15803d] text-white' : 'bg-[#f8fafc] dark:bg-slate-800 text-[#64748b]' }}"
                                >%</button>
                            </div>
                            @if($discount_type === 'percent' && (float) $discountAmt > 0)
                                <span class="px-1 py-0.2 rounded bg-emerald-50 text-emerald-700 border border-emerald-200 text-[9px] font-bold">= ৳ {{ number_format((float) $discountAmt, 2) }}</span>
                            @endif
                        </div>
                        <input
                            type="number"
                            step="any"
                            min="0"
                            wire:model.live.debounce.200ms="discount_value"
                            placeholder="0"
                            class="w-[72px] h-[20px] rounded-[4px] border-[1.5px] border-[#cbd5e1] dark:border-slate-600 bg-white dark:bg-slate-800 text-center text-[10.5px] font-bold text-slate-800 dark:text-white outline-none focus:border-[#15803d]"
                        >
                    </div>

                    <div class="flex items-center justify-between py-0.5">
                        <span class="text-[9.5px] font-extrabold text-[#64748b] dark:text-slate-400 uppercase">DELIVERY CHARGE:</span>
                        <input
                            type="number"
                            step="any"
                            min="0"
                            wire:model.live.debounce.200ms="delivery_charge"
                            placeholder="0"
                            class="w-[72px] h-[20px] rounded-[4px] border-[1.5px] border-[#cbd5e1] dark:border-slate-600 bg-white dark:bg-slate-800 text-center text-[10.5px] font-bold text-slate-800 dark:text-white outline-none focus:border-[#15803d]"
                        >
                    </div>

                    <div class="flex items-center justify-between py-0.5">
                        <span class="text-[9.5px] font-extrabold text-[#64748b] dark:text-slate-400 uppercase">EXCHANGE AMOUNT:</span>
                        <input
                            type="number"
                            step="any"
                            min="0"
                            wire:model.live.debounce.200ms="exchange_amount"
                            placeholder="0"
                            class="w-[72px] h-[20px] rounded-[4px] border-[1.5px] border-[#cbd5e1] dark:border-slate-600 bg-white dark:bg-slate-800 text-center text-[10.5px] font-bold text-slate-800 dark:text-white outline-none focus:border-[#15803d]"
                        >
                    </div>

                    <div class="border-t border-slate-200 dark:border-slate-700 my-0.5"></div>

                    <div class="flex items-center justify-between py-0.5">
                        <span class="text-[10px] font-black text-[#0f172a] dark:text-white uppercase">NET PAYABLE:</span>
                        <span class="text-[12.5px] font-black text-[#15803d] dark:text-emerald-400 tabular-nums">৳ {{ number_format((float) $netPayable, 2) }}</span>
                    </div>

                    <div class="flex items-center justify-between py-0.5">
                        <div class="flex items-center gap-1">
                            <span class="text-[9.5px] font-extrabold text-[#64748b] dark:text-slate-400 uppercase">PAID AMOUNT:</span>
                            <button
                                type="button"
                                wire:click="payExact"
                                class="h-[16px] px-1.5 rounded-[3px] bg-[#dcfce7] hover:bg-[#bbf7d0] text-[#15803d] text-[9px] font-bold leading-none cursor-pointer transition-colors"
                            >
                                Full Paid
                            </button>
                        </div>
                        <input
                            type="number"
                            step="any"
                            min="0"
                            wire:model.live.debounce.200ms="paid_amount"
                            placeholder="0"
                            class="w-[72px] h-[20px] rounded-[4px] border-[1.5px] border-[#16a34a] bg-white dark:bg-slate-800 text-center text-[10.5px] font-bold text-[#15803d] dark:text-emerald-400 outline-none focus:ring-1 focus:ring-emerald-500"
                        >
                    </div>

                    <div class="flex items-center justify-between py-0.5">
                        <div class="flex items-center gap-1">
                            <span class="text-[10px] font-black text-[#15803d] dark:text-emerald-400 uppercase">CURRENT DUE:</span>
                            <span class="h-[15px] px-1 rounded-[3px] bg-[#dcfce7] dark:bg-emerald-950/60 text-[#15803d] dark:text-emerald-300 text-[8.5px] font-extrabold inline-flex items-center">
                                {{ $paymentStatusLabel }}
                            </span>
                        </div>
                        <span class="text-[12.5px] font-black text-[#15803d] dark:text-emerald-400 tabular-nums">৳ {{ number_format((float) $dueAmt, 2) }}</span>
                    </div>
                </div>

                {{-- Bottom Action Buttons Row --}}
                <div class="flex items-center gap-1.5">
                    <button
                        type="button"
                        @click="holdCurrentOrder()"
                        class="h-[29px] px-2.5 rounded-[6px] bg-[#f59e0b] hover:bg-[#d97706] text-[#0f172a] text-[11px] font-extrabold inline-flex items-center justify-center gap-1 cursor-pointer transition-colors"
                        title="Hold Current Invoice"
                    >
                        <i class="fa-solid fa-circle-pause text-[11px]"></i>
                        <span>Hold</span>
                    </button>

                    <button
                        type="button"
                        @click="loadHeldInvoices(); holdModalOpen = true"
                        class="h-[29px] px-2.5 rounded-[6px] bg-[#fffbeb] dark:bg-amber-950/30 hover:bg-[#fef3c7] border-[1.5px] border-[#f59e0b] text-[#b45309] dark:text-amber-400 text-[11px] font-extrabold inline-flex items-center justify-center gap-1 cursor-pointer transition-colors"
                        title="Held Invoices History & Restore"
                    >
                        <i class="fa-solid fa-clock-rotate-left text-[11px]"></i>
                        <span>History</span>
                        <span
                            x-show="heldInvoices.length > 0"
                            x-text="heldInvoices.length"
                            style="display: none;"
                            class="ml-0.5 px-1.5 py-0.2 rounded-full bg-slate-900 dark:bg-amber-400 text-white dark:text-slate-900 text-[9.5px] font-extrabold leading-tight"
                        ></span>
                    </button>

                    <button
                        type="button"
                        wire:click="clearCart"
                        class="h-[29px] px-2.5 rounded-[6px] bg-[#f8fafc] dark:bg-slate-900 hover:bg-[#f1f5f9] border-[1.5px] border-[#cbd5e1] dark:border-slate-600 hover:border-[#16a34a] text-[#475569] dark:text-slate-300 hover:text-[#15803d] text-[11px] font-extrabold inline-flex items-center justify-center gap-1 cursor-pointer transition-colors"
                        title="Clear POS Cart"
                    >
                        <i class="fa-solid fa-arrows-rotate text-[11px]"></i>
                        <span>Clear</span>
                    </button>

                    <button
                        type="button"
                        wire:click="completeSale"
                        wire:loading.attr="disabled"
                        class="flex-1 h-[29px] px-3 rounded-[6px] text-white text-[11px] font-extrabold inline-flex items-center justify-center gap-1.5 shadow-sm hover:-translate-y-0.5 transition-all cursor-pointer"
                        style="background: linear-gradient(135deg, #15803d 0%, #16a34a 100%);"
                        title="Submit Order (Press F4)"
                    >
                        <i class="fa-solid fa-paper-plane text-[11px]"></i>
                        <span>SUBMIT ORDER (F4)</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Held Invoices History Modal --}}
    <div
        x-show="holdModalOpen"
        x-transition.opacity
        style="display: none;"
        class="fixed inset-0 z-50 bg-slate-900/65 backdrop-blur-xs flex items-center justify-center p-4"
    >
        <div @click.stop class="w-full max-w-3xl rounded-[20px] bg-[#f8fafc] dark:bg-slate-900 border border-slate-300 dark:border-slate-700 shadow-2xl overflow-hidden flex flex-col max-h-[85vh]">
            <div class="marss-modal-header px-5 py-3 flex items-center justify-between">
                <h5 class="m-0 text-white font-bold text-base flex items-center gap-2">
                    <i class="fa-solid fa-circle-pause text-white text-lg"></i>
                    <span>Held Invoices List</span>
                </h5>
                <button type="button" @click="holdModalOpen = false" class="marss-modal-close-btn">
                    <i class="fa-solid fa-xmark text-xs text-white"></i>
                </button>
            </div>
            <div class="p-4 overflow-y-auto space-y-3 flex-1">
                <template x-if="heldInvoices.length === 0">
                    <div class="py-10 text-center bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 text-slate-400 font-bold">
                        <i class="fa-solid fa-inbox text-3xl text-amber-400 block mb-2"></i>
                        No held invoices found.
                    </div>
                </template>
                <template x-for="item in heldInvoices.slice().reverse()" :key="item.id">
                    <div class="p-3.5 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-xs">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <span class="px-2.5 py-1.5 rounded-lg bg-[#fef08a] text-[#854d0e] border border-[#fde047] font-mono text-xs font-extrabold" x-text="item.id"></span>
                                <div>
                                    <h6 class="m-0 font-bold text-slate-800 dark:text-white text-sm" x-text="item.customer_name"></h6>
                                    <span class="text-xs text-slate-500"><i class="fa-solid fa-phone text-emerald-600 mr-1"></i><span x-text="item.customer_mobile || 'N/A'"></span></span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2.5">
                                <span class="text-xs text-slate-500"><i class="fa-regular fa-clock text-amber-500 mr-1"></i><span x-text="item.time"></span></span>
                                <span class="px-3 py-1 rounded-full bg-sky-100 text-sky-700 border border-sky-200 text-xs font-bold" x-text="item.total_items + ' Pcs'"></span>
                            </div>
                            <div class="flex items-center gap-2.5">
                                <span class="text-base font-extrabold text-[#15803d] dark:text-emerald-400" x-text="'৳ ' + item.sub_total"></span>
                                <button type="button" @click="restoreHeld(item)" class="h-9 px-3 rounded-xl bg-[#15803d] hover:bg-[#166534] text-white text-xs font-bold inline-flex items-center gap-1.5 cursor-pointer">
                                    <i class="fa-solid fa-rotate-left"></i> Restore
                                </button>
                                <button type="button" @click="deleteHeld(item.id)" class="w-9 h-9 rounded-xl border border-rose-300 text-rose-600 hover:bg-rose-600 hover:text-white inline-flex items-center justify-center cursor-pointer">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
            <div class="px-4 py-2.5 bg-white dark:bg-slate-800 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between">
                <span class="text-xs text-slate-500"><i class="fa-solid fa-circle-info text-sky-500 mr-1"></i> Click 'Restore' anytime to load a held invoice to the screen.</span>
                <button type="button" @click="holdModalOpen = false" class="h-[38px] px-4 rounded-xl bg-[#dc2626] hover:bg-[#b91c1c] text-white text-xs font-bold cursor-pointer">Close</button>
            </div>
        </div>
    </div>

    {{-- Camera Barcode Scanner Modal --}}
    <div x-show="scannerOpen" style="display: none;" class="fixed inset-0 z-50 bg-slate-900/75 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl w-full max-w-md border border-slate-300 dark:border-slate-700 overflow-hidden shadow-2xl">
            <div class="marss-modal-header px-4 py-3 flex items-center justify-between">
                <h3 class="text-sm font-bold text-white m-0 flex items-center gap-2">
                    <i class="fa-solid fa-camera"></i> Mobile Camera Barcode Scanner
                </h3>
                <button type="button" @click="closeScanner()" class="marss-modal-close-btn">
                    <i class="fa-solid fa-xmark text-xs text-white"></i>
                </button>
            </div>
            <div class="p-4 space-y-3">
                <div class="p-2 rounded-lg bg-sky-50 dark:bg-sky-950/50 text-sky-700 dark:text-sky-300 text-xs font-semibold text-center">
                    Hold barcode in front of the camera to scan automatically.
                </div>
                <div id="battery-qr-reader" class="w-full rounded-xl overflow-hidden bg-black min-h-[250px]"></div>
                <div class="flex justify-end pt-1">
                    <button type="button" @click="closeScanner()" class="h-[38px] px-5 rounded-xl bg-[#dc2626] text-white text-xs font-bold cursor-pointer">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Add Customer Modal --}}
    <x-ui.modal :show="$showCustomerModal" max-width="lg" title="Add New Customer" icon="fa-solid fa-user-plus" close-method="closeModals">
        <form wire:submit="createQuickCustomer" class="space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Customer Name <span class="text-rose-600">*</span></label>
                    <input type="text" wire:model="newCustName" placeholder="Enter Customer Name" required class="w-full h-[40px] px-3 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs sm:text-sm outline-none focus:border-[#16a34a]">
                    @error('newCustName') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Customer Number <span class="text-rose-600">*</span></label>
                    <input type="text" wire:model="newCustMobile" placeholder="Enter Customer Number" required class="w-full h-[40px] px-3 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs sm:text-sm outline-none focus:border-[#16a34a]">
                    @error('newCustMobile') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Customer Email</label>
                    <input type="email" wire:model="newCustEmail" placeholder="Enter Customer Email" class="w-full h-[40px] px-3 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs sm:text-sm outline-none focus:border-[#16a34a]">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">NID Number</label>
                    <input type="text" wire:model="newCustNid" placeholder="Enter NID Number" class="w-full h-[40px] px-3 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs sm:text-sm outline-none focus:border-[#16a34a]">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Previous Due Amount</label>
                    <input type="number" step="any" wire:model="newCustPrevDue" placeholder="0.00" class="w-full h-[40px] px-3 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs sm:text-sm outline-none focus:border-[#16a34a]">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Address Details</label>
                    <input type="text" wire:model="newCustAddress" placeholder="Enter Address Details" class="w-full h-[40px] px-3 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs sm:text-sm outline-none focus:border-[#16a34a]">
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t border-slate-200 dark:border-slate-800">
                <button type="button" wire:click="closeModals" class="h-[38px] px-4 rounded-lg bg-[#dc2626] hover:bg-[#b91c1c] text-white text-xs font-bold cursor-pointer">Cancel</button>
                <button type="submit" class="h-[38px] px-5 rounded-lg bg-[#15803d] hover:bg-[#166534] text-white text-xs font-bold cursor-pointer">Submit</button>
            </div>
        </form>
    </x-ui.modal>

    {{-- Quick Create Product Modal --}}
    <x-ui.modal :show="$showProductModal" max-width="lg" title="Quick Create Product from POS" icon="fa-solid fa-cart-plus" close-method="closeModals">
        <form wire:submit="createQuickProduct" class="space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                <div class="sm:col-span-7">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Product Name <span class="text-rose-600">*</span></label>
                    <input type="text" wire:model="newProdName" required placeholder="e.g.: HAMKO 12V 20AH Gel Battery" class="w-full h-[38px] px-3 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold outline-none focus:border-[#16a34a]">
                    @error('newProdName') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <div class="sm:col-span-5">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Barcode / Product Code</label>
                    <div class="relative">
                        <input type="text" wire:model="newProdCode" placeholder="e.g.: BAT-1002" class="w-full h-[38px] pl-3 pr-10 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold outline-none focus:border-[#16a34a]">
                        <button
                            type="button"
                            wire:click="generateQuickBarcode"
                            title="Generate Unique Barcode"
                            class="absolute right-1.5 top-1/2 -translate-y-1/2 w-[27px] h-[27px] rounded-md text-white flex items-center justify-center cursor-pointer"
                            style="background: linear-gradient(135deg, #15803d 0%, #16a34a 100%);"
                        >
                            <i class="fa-solid fa-arrows-rotate text-[11px]"></i>
                        </button>
                    </div>
                </div>

                <div class="sm:col-span-4">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Brand</label>
                    <select wire:model="newProdBrandId" class="w-full h-[38px] px-2.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold">
                        <option value="">Select Brand</option>
                        @foreach($brands as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-4">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Category</label>
                    <select wire:model="newProdCategoryId" class="w-full h-[38px] px-2.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold">
                        <option value="">Select Category</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-4">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Unit</label>
                    <select wire:model="newProdUnitId" class="w-full h-[38px] px-2.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold">
                        @foreach($units as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-4">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Cost Price (৳)</label>
                    <input type="number" step="any" min="0" wire:model="newProdCost" class="w-full h-[38px] px-3 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold text-rose-600">
                </div>
                <div class="sm:col-span-4">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Selling Price (৳)</label>
                    <input type="number" step="any" min="0" wire:model="newProdSell" class="w-full h-[38px] px-3 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold text-emerald-700">
                </div>
                <div class="sm:col-span-4">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Initial Stock (Quantity)</label>
                    <input type="number" step="any" min="0" wire:model="newProdQty" class="w-full h-[38px] px-3 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold text-sky-700">
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-slate-200 dark:border-slate-800">
                <button type="button" wire:click="closeModals" class="h-[38px] px-4 rounded-lg bg-[#dc2626] hover:bg-[#b91c1c] text-white text-xs font-bold cursor-pointer">Cancel</button>
                <button type="submit" class="h-[38px] px-5 rounded-lg bg-[#15803d] hover:bg-[#166534] text-white text-xs font-bold cursor-pointer">Save &amp; Add to Cart</button>
            </div>
        </form>
    </x-ui.modal>
</div>
