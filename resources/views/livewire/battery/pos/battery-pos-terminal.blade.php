@php use App\Support\Money; @endphp

<div @keydown.f4.window.prevent="$wire.completeSale()" class="h-screen flex flex-col overflow-hidden">
    <header class="h-[54px] shrink-0 bg-gradient-to-r from-[#064e3b] via-[#047857] to-[#0f766e] text-white px-4 flex items-center justify-between gap-2 shadow-md">
        <div class="flex items-center gap-2.5">
            <a href="{{ route('battery.dashboard') }}" wire:navigate class="h-8 px-3 rounded-lg bg-white/15 hover:bg-white/25 text-xs font-bold flex items-center gap-1.5"><i class="fa-solid fa-arrow-left"></i> Battery Dashboard</a>
            <span class="font-extrabold text-sm sm:text-base flex items-center gap-2"><i class="fa-solid fa-car-battery text-amber-300"></i> Battery POS Terminal</span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('battery.invoices') }}" wire:navigate class="hidden sm:inline-flex h-8 px-3 rounded-lg bg-white/15 text-xs font-bold items-center gap-1.5">Invoices</a>
            <button type="button" wire:click="$set('showProductModal', true)" class="h-8 px-3 rounded-lg bg-amber-400 hover:bg-amber-300 text-slate-950 text-xs font-extrabold cursor-pointer">+ Quick Battery</button>
        </div>
    </header>

    <div class="flex-1 grid grid-cols-1 lg:grid-cols-12 overflow-hidden">
        <div class="lg:col-span-7 flex flex-col overflow-hidden border-r border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-[#0b0f19]">
            <div class="p-3 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 grid grid-cols-1 sm:grid-cols-12 gap-2">
                <div class="sm:col-span-4"><input type="text" wire:model.live.debounce.250ms="search" placeholder="Search battery product..." class="w-full h-9 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs sm:text-sm"></div>
                <div class="sm:col-span-3"><input type="text" wire:model="barcodeInput" wire:keydown.enter.prevent="scanBarcode" placeholder="Scan barcode..." class="w-full h-9 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs"></div>
                <div class="sm:col-span-3"><select wire:model.live="brandFilter" class="w-full h-9 px-2 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold"><option value="">All Brands</option>@foreach($brands as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach</select></div>
                <div class="sm:col-span-2"><select wire:model.live="categoryFilter" class="w-full h-9 px-2 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold"><option value="">All Cats</option>@foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div>
            </div>

            <div class="flex-1 overflow-y-auto p-3 grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-2.5 content-start">
                @forelse($products as $p)
                    @php $stock = (int) round((float) ($p->quantity ?? 0)); @endphp
                    <button type="button" wire:click="addToCart({{ $p->id }})" @disabled($stock <= 0) class="text-left p-3 rounded-2xl bg-white dark:bg-slate-900 border-[1.5px] {{ $stock <= 0 ? 'border-red-200 opacity-55' : 'border-slate-200 dark:border-slate-700 hover:border-emerald-600 cursor-pointer' }} flex flex-col justify-between gap-2">
                        <div>
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold {{ $stock <= 0 ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">Qty: {{ $stock }}</span>
                            <p class="mt-1.5 text-xs font-bold line-clamp-2">{{ $p->product_name }}</p>
                            <p class="text-[10px] text-slate-400 truncate">{{ $p->brand?->name ?? 'Battery' }} • {{ $p->primaryCode() }}</p>
                        </div>
                        <div class="pt-1 border-t border-slate-100 dark:border-slate-800 flex justify-between text-xs">
                            <span class="text-slate-400">{{ Money::formatBd($p->cost_price, false) }}</span>
                            <span class="font-extrabold text-emerald-700">{{ Money::formatBd((float) $p->sell_price > 0 ? $p->sell_price : $p->cost_price) }}</span>
                        </div>
                    </button>
                @empty
                    <div class="col-span-full py-12 text-center text-sm text-slate-500">No Battery products found. Add one using "+ Quick Battery".</div>
                @endforelse
            </div>
        </div>

        <div class="lg:col-span-5 flex flex-col overflow-hidden bg-white dark:bg-slate-900">
            <div class="p-3 border-b border-slate-200 dark:border-slate-800 grid grid-cols-12 gap-2">
                <div class="col-span-7 flex gap-1">
                    <select wire:model="customer_id" class="w-full h-9 px-2 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold">
                        <option value="">Select Battery Customer *</option>
                        @foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->customer_name }} ({{ $c->mobile ?: $c->customer_id }})</option>@endforeach
                    </select>
                    <button type="button" wire:click="$set('showCustomerModal', true)" class="w-9 h-9 shrink-0 rounded-xl bg-emerald-700 text-white flex items-center justify-center cursor-pointer"><i class="fa-solid fa-user-plus text-xs"></i></button>
                </div>
                <div class="col-span-5"><x-ui.date-picker wire:model="invoice_date" /></div>
            </div>

            <div class="flex-1 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($cart as $idx => $line)
                    <div class="p-2.5 flex items-center gap-2">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold truncate">{{ $line['product_name'] }}</p>
                            <p class="text-[10px] text-slate-400">Stock: {{ $line['stock'] }}</p>
                        </div>
                        <div class="flex items-center gap-1">
                            <button type="button" wire:click="decrementQty({{ $idx }})" class="w-6 h-6 rounded bg-slate-200 dark:bg-slate-700 font-bold">-</button>
                            <input type="number" min="1" max="{{ $line['stock'] }}" wire:model.live="cart.{{ $idx }}.quantity" class="w-12 h-7 text-center rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold">
                            <button type="button" wire:click="incrementQty({{ $idx }})" class="w-6 h-6 rounded bg-slate-200 dark:bg-slate-700 font-bold">+</button>
                        </div>
                        <input type="number" step="0.01" min="0" wire:model.live.debounce.300ms="cart.{{ $idx }}.selling_price" class="w-20 h-7 px-1.5 text-right rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold">
                        <button type="button" wire:click="removeItem({{ $idx }})" class="text-red-500 px-1 cursor-pointer"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                @empty
                    <div class="h-full flex items-center justify-center text-xs text-slate-400 p-8">Select Battery products to bill.</div>
                @endforelse
            </div>

            <div class="p-3 bg-slate-50 dark:bg-slate-950/70 border-t border-slate-200 dark:border-slate-800 space-y-2 text-xs">
                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-500">Discount</label>
                        <div class="flex"><select wire:model.live="discount_type" class="h-8 px-1 rounded-l-lg border border-r-0 border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800"><option value="fixed">৳</option><option value="percent">%</option></select><input type="number" step="0.01" wire:model.live="discount_value" class="w-full h-8 px-2 rounded-r-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 font-bold"></div>
                    </div>
                    <div><label class="block text-[10px] font-bold uppercase text-slate-500">Delivery</label><input type="number" step="0.01" wire:model.live="delivery_charge" class="w-full h-8 px-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 font-bold"></div>
                    <div><label class="block text-[10px] font-bold uppercase text-slate-500">Method</label><select wire:model="payment_method" class="w-full h-8 px-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 font-bold"><option value="Cash">Cash</option><option value="bKash">bKash</option><option value="Nagad">Nagad</option><option value="Bank">Bank</option></select></div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div><div class="flex justify-between"><label class="text-[10px] font-bold uppercase text-slate-500">Paid (৳)</label><button type="button" wire:click="payExact" class="text-[10px] font-bold text-emerald-700 cursor-pointer">Full</button></div><input type="number" step="0.01" wire:model.live="paid_amount" class="w-full h-9 px-2 rounded-lg border-[1.5px] border-emerald-500 bg-white dark:bg-slate-800 text-sm font-extrabold text-emerald-700"></div>
                    <div><label class="block text-[10px] font-bold uppercase text-slate-500">Challan / Note</label><input type="text" wire:model="order_note" class="w-full h-9 px-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800"></div>
                </div>
                <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 grid grid-cols-3 text-center">
                    <div><p class="text-[10px] text-slate-400">SubTotal</p><p class="font-bold">{{ Money::formatBd($subTotal) }}</p></div>
                    <div><p class="text-[10px] text-emerald-600 font-bold">Net Payable</p><p class="text-sm font-extrabold text-emerald-700">{{ Money::formatBd($netPayable) }}</p></div>
                    <div><p class="text-[10px] text-rose-500 font-bold">Due</p><p class="font-extrabold text-rose-600">{{ Money::formatBd($dueAmt) }}</p></div>
                </div>
                <div class="flex gap-2">
                    <button type="button" wire:click="clearCart" class="h-10 px-3 rounded-xl border border-slate-300 font-bold cursor-pointer">Clear</button>
                    <button type="button" wire:click="completeSale" class="flex-1 h-10 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-extrabold cursor-pointer">Complete Battery Sale (F4)</button>
                </div>
            </div>
        </div>
    </div>

    <x-ui.modal :show="$showCustomerModal" title="Quick Add Battery Customer" close-method="closeModals">
        <form wire:submit="createQuickCustomer" class="space-y-3">
            <div><label class="block text-xs font-bold mb-1">Customer Name *</label><input type="text" wire:model="newCustName" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"></div>
            <div><label class="block text-xs font-bold mb-1">Mobile</label><input type="text" wire:model="newCustMobile" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"></div>
            <div><label class="block text-xs font-bold mb-1">Address</label><input type="text" wire:model="newCustAddress" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"></div>
            <div class="flex justify-end gap-2"><button type="button" wire:click="closeModals" class="h-9 px-4 rounded-xl border text-xs font-bold">Cancel</button><button type="submit" class="h-9 px-5 rounded-xl bg-emerald-700 text-white text-xs font-bold">Save</button></div>
        </form>
    </x-ui.modal>

    <x-ui.modal :show="$showProductModal" title="Quick Add Battery Product" close-method="closeModals">
        <form wire:submit="createQuickProduct" class="space-y-3">
            <div><label class="block text-xs font-bold mb-1">Product Name *</label><input type="text" wire:model="newProdName" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"></div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="block text-xs font-bold mb-1">Barcode</label><input type="text" wire:model="newProdCode" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"></div>
                <div><label class="block text-xs font-bold mb-1">Initial Qty *</label><input type="number" min="1" wire:model="newProdQty" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"></div>
                <div><label class="block text-xs font-bold mb-1">Cost Price *</label><input type="number" step="0.01" wire:model="newProdCost" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"></div>
                <div><label class="block text-xs font-bold mb-1">Sell Price *</label><input type="number" step="0.01" wire:model="newProdSell" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"></div>
            </div>
            <div class="flex justify-end gap-2"><button type="button" wire:click="closeModals" class="h-9 px-4 rounded-xl border text-xs font-bold">Cancel</button><button type="submit" class="h-9 px-5 rounded-xl bg-emerald-700 text-white text-xs font-bold">Save &amp; Add</button></div>
        </form>
    </x-ui.modal>
</div>
