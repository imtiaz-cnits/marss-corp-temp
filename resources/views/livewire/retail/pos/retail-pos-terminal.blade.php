@php use App\Support\Money; @endphp

<div
    x-data="{
        scannerOpen: false,
        scanner: null,
        async openScanner() {
            this.scannerOpen = true;
            await this.$nextTick();
            if (!window.Html5Qrcode) return;
            try {
                this.scanner = new window.Html5Qrcode('retail-qr-reader');
                await this.scanner.start(
                    { facingMode: 'environment' },
                    { fps: 10, qrbox: { width: 220, height: 140 } },
                    (decoded) => {
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
    class="h-screen flex flex-col overflow-hidden"
>
    {{-- POS Top Bar --}}
    <header class="h-[54px] shrink-0 bg-gradient-to-r from-[#064e3b] via-[#047857] to-[#0f766e] text-white px-3 sm:px-4 flex items-center justify-between gap-2 shadow-md">
        <div class="flex items-center gap-2.5">
            <a href="{{ route('retail.dashboard') }}" wire:navigate class="h-8 px-3 rounded-lg bg-white/15 hover:bg-white/25 text-xs font-bold flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-left"></i> Dashboard
            </a>
            <span class="font-extrabold text-sm sm:text-base tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-cash-register text-emerald-200"></i> Retail POS Terminal
            </span>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('retail.invoices') }}" wire:navigate class="hidden sm:inline-flex h-8 px-3 rounded-lg bg-white/15 hover:bg-white/25 text-xs font-bold items-center gap-1.5">
                <i class="fa-solid fa-file-invoice"></i> Invoices
            </a>
            <button type="button" wire:click="$set('showProductModal', true)" class="h-8 px-3 rounded-lg bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs font-extrabold cursor-pointer">
                <i class="fa-solid fa-plus mr-1"></i> Quick Product
            </button>
            <button type="button" onclick="window.marssTheme?.toggle()" class="w-8 h-8 rounded-lg bg-white/15 hover:bg-white/25 flex items-center justify-center cursor-pointer" title="Theme">
                <i class="fa-solid fa-circle-half-stroke text-xs"></i>
            </button>
        </div>
    </header>

    {{-- Split Workspace --}}
    <div class="flex-1 grid grid-cols-1 lg:grid-cols-12 overflow-hidden">
        {{-- Left: Product Catalog (7 cols) --}}
        <div class="lg:col-span-7 flex flex-col overflow-hidden border-r border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-[#0b0f19]">
            <div class="p-3 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 grid grid-cols-1 sm:grid-cols-12 gap-2">
                <div class="sm:col-span-4 relative">
                    <i class="fa-solid fa-magnifying-glass text-xs text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input type="text" wire:model.live.debounce.250ms="search" placeholder="Search product or code..." class="w-full h-9 pl-8 pr-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs sm:text-sm outline-none focus:border-emerald-700">
                </div>
                <div class="sm:col-span-3 flex gap-1">
                    <input type="text" wire:model="barcodeInput" wire:keydown.enter.prevent="scanBarcode" placeholder="Scan barcode..." class="w-full h-9 px-2.5 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs outline-none focus:border-emerald-700">
                    <button type="button" @click="openScanner()" title="Camera Barcode Scanner" class="w-9 h-9 shrink-0 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white flex items-center justify-center cursor-pointer">
                        <i class="fa-solid fa-camera text-xs"></i>
                    </button>
                </div>
                <div class="sm:col-span-3">
                    <select wire:model.live="brandFilter" class="w-full h-9 px-2.5 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold">
                        <option value="">All Brands</option>
                        @foreach($brands as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <select wire:model.live="categoryFilter" class="w-full h-9 px-2.5 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold">
                        <option value="">All Cats</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}">{{ $c->category_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto p-3 grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-2.5 content-start">
                @forelse($products as $p)
                    @php
                        $stock = (int) round((float) ($p->quantity ?? 0));
                        $out = $stock <= 0;
                    @endphp
                    <button
                        type="button"
                        wire:click="addToCart({{ $p->id }})"
                        @disabled($out)
                        class="text-left p-3 rounded-2xl bg-white dark:bg-slate-900 border-[1.5px] {{ $out ? 'border-red-200 dark:border-red-900/50 opacity-55 cursor-not-allowed' : 'border-slate-200 dark:border-slate-700 hover:border-emerald-600 hover:shadow-md cursor-pointer' }} transition-all flex flex-col justify-between gap-2"
                    >
                        <div>
                            <div class="flex items-start justify-between gap-1">
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold {{ $out ? 'bg-red-100 text-red-700' : ($stock < 10 ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                                    Qty: {{ $stock }}
                                </span>
                                @if($p->door_side)
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-sky-100 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300">{{ $p->door_side }}</span>
                                @endif
                            </div>
                            <p class="mt-1.5 text-xs font-bold text-slate-800 dark:text-white line-clamp-2 leading-snug">{{ $p->product_name }}</p>
                            <p class="text-[10px] text-slate-400 truncate mt-0.5">{{ $p->brand?->name ?? 'General' }} • {{ is_array($p->product_code) ? ($p->product_code[0] ?? '') : $p->product_code }}</p>
                        </div>
                        <div class="pt-1 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                            <span class="text-slate-400">Cost: {{ Money::formatBd($p->cost_price, false) }}</span>
                            <span class="font-extrabold text-emerald-700 dark:text-emerald-400">{{ Money::formatBd((float) $p->sell_price > 0 ? $p->sell_price : $p->cost_price) }}</span>
                        </div>
                    </button>
                @empty
                    <div class="col-span-full py-12 text-center text-sm text-slate-500">No matching products found.</div>
                @endforelse
            </div>
        </div>

        {{-- Right: Cart & Checkout Panel (5 cols) --}}
        <div class="lg:col-span-5 flex flex-col overflow-hidden bg-white dark:bg-slate-900">
            {{-- Customer & Date --}}
            <div class="p-3 border-b border-slate-200 dark:border-slate-800 grid grid-cols-12 gap-2 items-center">
                <div class="col-span-7 flex gap-1">
                    <select wire:model="customer_id" class="w-full h-9 px-2.5 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold">
                        <option value="">Select Customer *</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->customer_name }} ({{ $c->mobile ?: $c->customer_id }})</option>
                        @endforeach
                    </select>
                    <button type="button" wire:click="$set('showCustomerModal', true)" title="Quick Add Customer" class="w-9 h-9 shrink-0 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white flex items-center justify-center cursor-pointer">
                        <i class="fa-solid fa-user-plus text-xs"></i>
                    </button>
                </div>
                <div class="col-span-5">
                    <x-ui.date-picker wire:model="invoice_date" placeholder="Invoice Date" />
                </div>
            </div>

            {{-- Cart Items --}}
            <div class="flex-1 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($cart as $idx => $line)
                    <div wire:key="cart-line-{{ $line['product_id'] }}" class="p-2.5 flex items-center gap-2 hover:bg-slate-50 dark:hover:bg-slate-800/40">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-slate-800 dark:text-white truncate">{{ $line['product_name'] }}</p>
                            <p class="text-[10px] text-slate-400">Stock: {{ $line['stock'] }} • Cost: ৳{{ $line['cost_price'] }} @if($line['door_side']) • {{ $line['door_side'] }} @endif</p>
                        </div>
                        <div class="flex items-center gap-1">
                            <button type="button" wire:click="decrementQty({{ $idx }})" class="w-6 h-6 rounded-md bg-slate-200 dark:bg-slate-700 text-xs font-bold cursor-pointer">-</button>
                            <input type="number" min="1" max="{{ $line['stock'] }}" wire:model.live="cart.{{ $idx }}.quantity" class="w-12 h-7 text-center rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold">
                            <button type="button" wire:click="incrementQty({{ $idx }})" class="w-6 h-6 rounded-md bg-slate-200 dark:bg-slate-700 text-xs font-bold cursor-pointer">+</button>
                        </div>
                        <input type="number" step="0.01" min="0" wire:model.live.debounce.300ms="cart.{{ $idx }}.selling_price" class="w-20 h-7 px-1.5 text-right rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold">
                        <button type="button" wire:click="removeItem({{ $idx }})" class="w-7 h-7 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-950/40 flex items-center justify-center cursor-pointer">
                            <i class="fa-solid fa-xmark text-xs"></i>
                        </button>
                    </div>
                @empty
                    <div class="h-full flex flex-col items-center justify-center text-slate-400 p-8 text-center">
                        <i class="fa-solid fa-cart-plus text-3xl mb-2 opacity-40"></i>
                        <p class="text-xs font-semibold">Click products on the left or scan a barcode to start billing.</p>
                    </div>
                @endforelse
            </div>

            {{-- Totals & Payment Footer --}}
            <div class="p-3 bg-slate-50 dark:bg-slate-950/70 border-t-[1.5px] border-slate-200 dark:border-slate-800 space-y-2.5 text-xs">
                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-500 mb-0.5">Discount</label>
                        <div class="flex">
                            <select wire:model.live="discount_type" class="h-8 px-1.5 rounded-l-lg border-[1.5px] border-r-0 border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold">
                                <option value="fixed">৳</option>
                                <option value="percent">%</option>
                            </select>
                            <input type="number" step="0.01" min="0" wire:model.live.debounce.250ms="discount_value" class="w-full h-8 px-2 rounded-r-lg border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-500 mb-0.5">Delivery (৳)</label>
                        <input type="number" step="0.01" min="0" wire:model.live.debounce.250ms="delivery_charge" class="w-full h-8 px-2 rounded-lg border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-500 mb-0.5">Method</label>
                        <select wire:model="payment_method" class="w-full h-8 px-2 rounded-lg border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold">
                            <option value="Cash">Cash</option>
                            <option value="bKash">bKash</option>
                            <option value="Nagad">Nagad</option>
                            <option value="Rocket">Rocket</option>
                            <option value="Card">Card</option>
                            <option value="Bank">Bank</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <div class="flex items-center justify-between mb-0.5">
                            <label class="text-[10px] font-bold uppercase text-slate-500">Paid Amount (৳)</label>
                            <button type="button" wire:click="payExact" class="text-[10px] font-extrabold text-emerald-700 dark:text-emerald-400 hover:underline cursor-pointer">Full Pay</button>
                        </div>
                        <input type="number" step="0.01" min="0" wire:model.live.debounce.200ms="paid_amount" class="w-full h-9 px-2.5 rounded-lg border-[1.5px] border-emerald-500 bg-white dark:bg-slate-800 text-sm font-extrabold text-emerald-700 dark:text-emerald-300">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-500 mb-0.5">Challan / Note</label>
                        <input type="text" wire:model="order_note" placeholder="Optional challan no..." class="w-full h-9 px-2.5 rounded-lg border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                    </div>
                </div>

                <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 grid grid-cols-3 gap-2 text-center">
                    <div>
                        <p class="text-[10px] uppercase text-slate-400 font-bold">SubTotal</p>
                        <p class="font-bold text-slate-800 dark:text-white tabular-nums">{{ Money::formatBd($subTotal) }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase text-emerald-600 font-bold">Net Payable</p>
                        <p class="text-sm font-extrabold text-emerald-700 dark:text-emerald-400 tabular-nums">{{ Money::formatBd($netPayable) }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase text-rose-500 font-bold">Due</p>
                        <p class="font-extrabold text-rose-600 tabular-nums">{{ Money::formatBd($dueAmt) }}</p>
                    </div>
                </div>

                <div class="flex gap-2">
                    <button type="button" wire:click="clearCart" class="h-10 px-3.5 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 text-xs font-bold hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer">
                        Clear
                    </button>
                    <button
                        type="button"
                        wire:click="completeSale"
                        wire:loading.attr="disabled"
                        class="flex-1 h-10 rounded-xl bg-gradient-to-r from-emerald-700 to-emerald-600 hover:from-emerald-800 hover:to-emerald-700 text-white text-sm font-extrabold shadow-md flex items-center justify-center gap-2 cursor-pointer"
                    >
                        <i class="fa-solid fa-check-double"></i> Complete Sale &amp; Print (F4)
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Camera Barcode Scanner Overlay --}}
    <div x-show="scannerOpen" style="display: none;" class="fixed inset-0 z-50 bg-slate-900/75 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 w-full max-w-md border-[1.5px] border-slate-300 dark:border-slate-700 space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-extrabold">Scan Product Barcode</h3>
                <button type="button" @click="closeScanner()" class="w-8 h-8 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div id="retail-qr-reader" class="w-full rounded-xl overflow-hidden bg-black min-h-[220px]"></div>
        </div>
    </div>

    {{-- Quick Add Customer Modal --}}
    <x-ui.modal :show="$showCustomerModal" title="Quick Add Retail Customer" close-method="closeModals">
        <form wire:submit="createQuickCustomer" class="space-y-3">
            <div>
                <label class="block text-xs font-bold mb-1">Customer Name *</label>
                <input type="text" wire:model="newCustName" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                @error('newCustName') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-xs font-bold mb-1">Mobile</label>
                <input type="text" wire:model="newCustMobile" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold mb-1">Address</label>
                <input type="text" wire:model="newCustAddress" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" wire:click="closeModals" class="h-9 px-4 rounded-xl border border-slate-300 text-xs font-bold">Cancel</button>
                <button type="submit" class="h-9 px-5 rounded-xl bg-emerald-700 text-white text-xs font-bold">Save Customer</button>
            </div>
        </form>
    </x-ui.modal>

    {{-- Quick Add Product Modal --}}
    <x-ui.modal :show="$showProductModal" title="Quick Add Retail Product" close-method="closeModals">
        <form wire:submit="createQuickProduct" class="space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold mb-1">Product Name *</label>
                    <input type="text" wire:model="newProdName" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                    @error('newProdName') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold mb-1">Product Code / Barcode</label>
                    <input type="text" wire:model="newProdCode" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold mb-1">Door Handedness</label>
                    <select wire:model="newProdDoorSide" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                        <option value="">None / Standard</option>
                        <option value="Left Handed">Left Handed</option>
                        <option value="Right Handed">Right Handed</option>
                        <option value="Both Handed">Both Handed</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold mb-1">Initial Qty *</label>
                    <input type="number" min="1" wire:model="newProdQty" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold mb-1">Cost Price *</label>
                    <input type="number" step="0.01" min="0" wire:model="newProdCost" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold mb-1">Sell Price *</label>
                    <input type="number" step="0.01" min="0" wire:model="newProdSell" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" wire:click="closeModals" class="h-9 px-4 rounded-xl border border-slate-300 text-xs font-bold">Cancel</button>
                <button type="submit" class="h-9 px-5 rounded-xl bg-emerald-700 text-white text-xs font-bold">Add &amp; Put in Cart</button>
            </div>
        </form>
    </x-ui.modal>
</div>
