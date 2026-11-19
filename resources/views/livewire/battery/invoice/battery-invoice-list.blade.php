@php
    use App\Support\Money;
    $pageSubTotal = $orders->getCollection()->sum(fn($o) => (float) ($o->sub_total ?? 0));
    $pagePaidTotal = $orders->getCollection()->sum(fn($o) => (float) ($o->paid_amount ?? 0));
    $pageDueTotal = $orders->getCollection()->sum(fn($o) => (float) ($o->due_amount ?? 0));
@endphp

<div>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-300 dark:border-slate-800 shadow-xs overflow-hidden p-3 sm:p-5">
        {{-- 1. Top Section --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-3">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400 flex items-center justify-center border border-emerald-100 dark:border-slate-800 shadow-2xs shrink-0">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                    </svg>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-800 dark:text-white tracking-tight leading-none m-0">Battery Invoice List</h1>
            </div>

            <div class="flex items-center flex-wrap gap-2">
                <x-ui.export-buttons table-id="batteryInvoiceTable" filename="battery-invoices" title="MARSS Battery Invoices" />
                <a
                    href="{{ route('battery.pos') }}"
                    wire:navigate
                    class="h-[38px] px-4 rounded-xl bg-[#15803d] hover:bg-[#166534] text-white text-sm font-semibold shadow-xs inline-flex items-center gap-1.5 transition-all"
                >
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>Create Invoice</span>
                </a>
            </div>
        </div>

        {{-- 2. Controls & Filter Row --}}
        <div class="grid grid-cols-2 md:flex md:flex-nowrap items-center gap-2 mb-3.5">
            <div class="col-span-2 md:flex-1 unified-ui-border h-[38px] flex items-center px-3 bg-white dark:bg-slate-800/90 rounded-xl shadow-2xs focus-within:border-[#15803d]">
                <svg class="w-4 h-4 text-slate-400 shrink-0 mr-2 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search Invoice, Customer..."
                    class="w-full h-full bg-transparent border-0 outline-none text-xs sm:text-[13px] text-slate-800 dark:text-slate-100 placeholder:text-slate-400"
                >
            </div>

            <div class="w-full md:w-[140px]">
                <x-ui.date-picker wire:model.live="startDate" placeholder="Start Date" />
            </div>

            <div class="w-full md:w-[140px]">
                <x-ui.date-picker wire:model.live="endDate" placeholder="End Date" />
            </div>

            <div x-data="{ open: false }" @click.outside="open = false" class="custom-filter-dropdown shrink-0 w-full md:w-[138px]">
                <button
                    type="button"
                    @click="open = !open"
                    class="w-full unified-ui-border h-[38px] px-3 rounded-xl bg-white dark:bg-slate-800/90 text-xs sm:text-[13px] font-semibold text-slate-700 dark:text-slate-200 flex items-center justify-between gap-1.5 shadow-2xs cursor-pointer hover:border-emerald-500 transition-colors"
                >
                    <span class="flex items-center gap-1.5 truncate">
                        <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                        <span class="truncate">{{ $statusFilter === 'paid' ? 'Paid Only' : ($statusFilter === 'due' ? 'Due Only' : 'All Status') }}</span>
                    </span>
                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transition-transform" :class="open ? 'rotate-180' : ''"></i>
                </button>
                <div
                    x-show="open"
                    x-transition
                    style="display: none; right: 0; left: auto;"
                    class="select-menu"
                >
                    @foreach(['' => 'All Status', 'paid' => 'Paid Only', 'due' => 'Due Only'] as $val => $lbl)
                        <div
                            wire:click="$set('statusFilter', '{{ $val }}')"
                            @click="open = false"
                            class="select-option-item {{ $statusFilter === $val ? 'active' : '' }}"
                        >
                            <span>{{ $lbl }}</span>
                            @if($statusFilter === $val)
                                <i class="fa-solid fa-check text-xs text-emerald-600"></i>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- 3. Table --}}
        <div class="table-responsive unified-ui-border overflow-x-auto rounded-2xl shadow-2xs bg-white dark:bg-slate-900">
            <table id="batteryInvoiceTable" data-export-skip-last="true" class="w-full text-xs sm:text-[13px] text-left border-collapse">
                <thead>
                    <tr class="bg-[#15803d] text-white text-xs font-semibold uppercase tracking-wider whitespace-nowrap">
                        <th class="p-[10px] text-center w-[44px] rounded-tl-2xl">SL</th>
                        <th class="p-[10px]">Invoice #</th>
                        <th class="p-[10px]">Date</th>
                        <th class="p-[10px]">Customer</th>
                        <th class="p-[10px] text-right">SubTotal</th>
                        <th class="p-[10px] text-right">Paid</th>
                        <th class="p-[10px] text-right">Due</th>
                        <th class="p-[10px] text-center">Status</th>
                        <th class="p-[10px] text-center rounded-tr-2xl">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-200">
                    @forelse($orders as $idx => $o)
                        @php $isDue = (float) ($o->due_amount ?? 0) > 0.009; @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="p-[10px] text-center font-bold text-slate-400">{{ ($orders->firstItem() ?? 1) + $idx }}</td>
                            <td class="p-[10px] font-extrabold text-[#15803d] dark:text-emerald-400 whitespace-nowrap">{{ $o->order_no }}</td>
                            <td class="p-[10px] text-slate-500 whitespace-nowrap">{{ \Carbon\Carbon::parse($o->invoice_date ?? $o->created_at)->format('d-m-Y') }}</td>
                            <td class="p-[10px] font-bold">{{ $o->customer?->customer_name ?? 'Walk-in' }}</td>
                            <td class="p-[10px] text-right font-semibold tabular-nums whitespace-nowrap">{{ Money::formatBd($o->sub_total) }}</td>
                            <td class="p-[10px] text-right font-bold text-[#15803d] dark:text-emerald-400 tabular-nums whitespace-nowrap">{{ Money::formatBd($o->paid_amount) }}</td>
                            <td class="p-[10px] text-right font-bold tabular-nums whitespace-nowrap {{ $isDue ? 'text-rose-600' : 'text-slate-400' }}">{{ Money::formatBd($o->due_amount) }}</td>
                            <td class="p-[10px] text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $isDue ? 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' }}">
                                    {{ $isDue ? 'Due' : 'Paid' }}
                                </span>
                            </td>
                            <td class="p-[10px] text-center whitespace-nowrap">
                                <div class="inline-flex items-center justify-center gap-1">
                                    @if($isDue)<button type="button" wire:click="openPayment({{ $o->id }})" title="Collect Due" class="w-[30px] h-[30px] rounded-lg bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-600 hover:text-white inline-flex items-center justify-center text-xs cursor-pointer"><i class="fa-solid fa-hand-holding-dollar"></i></button>@endif
                                    <button type="button" wire:click="openEdit({{ $o->id }})" title="Edit" class="w-[30px] h-[30px] rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-600 hover:text-white inline-flex items-center justify-center text-xs cursor-pointer"><i class="fa-solid fa-pen-to-square"></i></button>
                                    <a href="{{ route('battery.invoice.show', $o->id) }}" target="_blank" title="Print" class="w-[30px] h-[30px] rounded-lg bg-sky-50 text-sky-700 border border-sky-200 hover:bg-sky-600 hover:text-white inline-flex items-center justify-center text-xs"><i class="fa-solid fa-print"></i></a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr data-empty-row="true"><td colspan="9" class="px-4 py-8 text-center text-rose-500 font-bold">❌ No Battery invoices found.</td></tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-50/90 dark:bg-slate-800/70 text-slate-800 dark:text-slate-100 font-bold border-t-2 border-emerald-600/30 text-xs sm:text-[13px]">
                    <tr>
                        <td colspan="4" class="p-[10px] text-right font-bold text-slate-600 dark:text-slate-300">Page Total:</td>
                        <td class="p-[10px] text-right font-extrabold tabular-nums whitespace-nowrap">{{ Money::formatBd($pageSubTotal) }}</td>
                        <td class="p-[10px] text-right font-extrabold text-[#15803d] dark:text-emerald-400 tabular-nums whitespace-nowrap">{{ Money::formatBd($pagePaidTotal) }}</td>
                        <td class="p-[10px] text-right font-extrabold text-rose-600 tabular-nums whitespace-nowrap">{{ Money::formatBd($pageDueTotal) }}</td>
                        <td colspan="2" class="p-[10px]"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="pt-4 mt-4 border-t border-slate-200 dark:border-slate-800">
            {{ $orders->links() }}
        </div>
    </div>

    <x-ui.modal :show="$showPaymentModal" :title="'Collect Battery Due — ' . $payOrderNo" icon="fa-solid fa-hand-holding-dollar" close-method="closeModals">
        <form wire:submit="submitPayment" class="space-y-3">
            <div class="p-3 rounded-xl bg-amber-50 flex justify-between text-sm font-bold"><span>Remaining Due:</span><span class="text-rose-600">{{ Money::formatBd($payCurrentDue) }}</span></div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="block text-xs font-bold mb-1">Paid (৳) *</label><input type="number" step="0.01" wire:model="payAmount" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-bold"></div>
                <div><label class="block text-xs font-bold mb-1">Discount (৳)</label><input type="number" step="0.01" wire:model="payDiscount" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"></div>
                <div><label class="block text-xs font-bold mb-1">Method</label><select wire:model="payMethod" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"><option value="Cash">Cash</option><option value="bKash">bKash</option><option value="Nagad">Nagad</option><option value="Bank">Bank</option></select></div>
                <div><label class="block text-xs font-bold mb-1">Date</label><x-ui.date-picker wire:model="payDate" /></div>
            </div>
            <div class="flex justify-end gap-2"><button type="button" wire:click="closeModals" class="h-[38px] px-4 rounded-lg bg-[#dc2626] text-white text-sm font-bold">Cancel</button><button type="submit" class="h-[38px] px-5 rounded-lg bg-[#15803d] text-white text-sm font-bold">Save Payment</button></div>
        </form>
    </x-ui.modal>

    <x-ui.modal :show="$showEditModal" max-width="full-edit" :title="'Edit Battery Invoice — ' . $editOrderNo" icon="fa-solid fa-pen-to-square" close-method="closeModals">
        <form wire:submit="submitFullEdit" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div><label class="block text-xs font-bold mb-1">Customer</label><select wire:model="editCustomerId" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">@foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->customer_name }}</option>@endforeach</select></div>
                <div><label class="block text-xs font-bold mb-1">Date</label><x-ui.date-picker wire:model="editDate" /></div>
                <div><label class="block text-xs font-bold mb-1">Challan / Note</label><input type="text" wire:model="editNote" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"></div>
            </div>
            <div class="flex gap-2">
                <select wire:model="editAddProductId" class="flex-1 h-9 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs"><option value="">+ Add Battery Product...</option>@foreach($allProducts as $ap)<option value="{{ $ap->id }}">{{ $ap->product_name }} (Qty: {{ $ap->quantity }})</option>@endforeach</select>
                <button type="button" wire:click="addEditProduct" class="h-9 px-4 rounded-xl bg-[#15803d] text-white text-xs font-bold">Add</button>
            </div>
            <table class="w-full text-xs border border-slate-200 dark:border-slate-700">
                <thead class="bg-[#15803d] text-white"><tr><th class="px-3 py-2 text-left">Product</th><th class="px-3 py-2 text-center w-24">Qty</th><th class="px-3 py-2 text-right w-32">Rate</th><th class="px-3 py-2 w-10"></th></tr></thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @foreach($editItems as $idx => $it)
                        <tr>
                            <td class="px-3 py-2 font-semibold">{{ $it['product_name'] }}</td>
                            <td class="px-3 py-2"><input type="number" min="1" wire:model="editItems.{{ $idx }}.quantity" class="w-20 h-8 text-center rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800"></td>
                            <td class="px-3 py-2"><input type="number" step="0.01" wire:model="editItems.{{ $idx }}.selling_price" class="w-28 h-8 text-right px-2 rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800"></td>
                            <td class="px-3 py-2 text-center"><button type="button" wire:click="removeEditItem({{ $idx }})" class="text-red-500"><i class="fa-solid fa-trash"></i></button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="grid grid-cols-3 gap-3">
                <div><label class="block text-xs font-bold mb-1">Discount</label><input type="number" step="0.01" wire:model="editDiscountValue" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"></div>
                <div><label class="block text-xs font-bold mb-1">Delivery</label><input type="number" step="0.01" wire:model="editDeliveryCharge" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"></div>
                <div><label class="block text-xs font-bold mb-1">Paid</label><input type="number" step="0.01" wire:model="editPaidAmount" @disabled($editHasMultiplePayments) class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm disabled:opacity-50"></div>
            </div>
            <div class="flex justify-end gap-2"><button type="button" wire:click="closeModals" class="h-[38px] px-4 rounded-lg bg-[#dc2626] text-white text-sm font-bold">Cancel</button><button type="submit" class="h-[38px] px-5 rounded-lg bg-[#15803d] text-white text-sm font-bold">Update</button></div>
        </form>
    </x-ui.modal>
</div>
