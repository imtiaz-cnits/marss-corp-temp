@php use App\Support\Money; @endphp

<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Retail Invoice List</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">View, collect due payments, edit line items, and print MARSS Retail bills.</p>
        </div>
        <a href="{{ route('retail.pos') }}" wire:navigate class="h-10 px-4 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-bold shadow-xs flex items-center gap-2">
            <i class="fa-solid fa-cash-register"></i> New POS Sale
        </a>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 shadow-xs overflow-hidden">
        <div class="p-3.5 border-b border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-2">
            <div class="flex flex-wrap items-center gap-2">
                <select wire:model.live="perPage" class="h-[38px] px-2.5 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold">
                    <option value="15">15</option><option value="50">50</option><option value="100">100</option><option value="200">200</option>
                </select>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Invoice #, customer, mobile..." class="h-[38px] px-3 w-52 sm:w-64 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs sm:text-sm outline-none focus:border-emerald-700">
                <select wire:model.live="statusFilter" class="h-[38px] px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold">
                    <option value="">All Status</option>
                    <option value="paid">Paid</option>
                    <option value="due">Due</option>
                </select>
                <div class="w-32"><x-ui.date-picker wire:model.live="startDate" placeholder="From" /></div>
                <div class="w-32"><x-ui.date-picker wire:model.live="endDate" placeholder="To" /></div>
                <button type="button" wire:click="resetFilters" class="h-[38px] px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 text-xs font-bold cursor-pointer">Reset</button>
            </div>
            <x-ui.export-buttons table-id="retailInvoiceTable" filename="retail-invoices" title="MARSS Retail Invoice List" />
        </div>

        {{-- Desktop Table --}}
        <div class="hidden md:block overflow-x-auto">
            <table id="retailInvoiceTable" data-export-skip-last="true" class="w-full text-xs sm:text-sm text-left">
                <thead class="bg-[#15803d] text-white text-xs uppercase tracking-wider whitespace-nowrap">
                    <tr>
                        <th class="px-3 py-3">Invoice No</th>
                        <th class="px-3 py-3">Date</th>
                        <th class="px-3 py-3">Customer</th>
                        <th class="px-3 py-3 text-right">SubTotal</th>
                        <th class="px-3 py-3 text-right">Disc</th>
                        <th class="px-3 py-3 text-right">Paid</th>
                        <th class="px-3 py-3 text-right">Due</th>
                        <th class="px-3 py-3 text-center">Status</th>
                        <th class="px-3 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($orders as $order)
                        @php $isDue = (float) ($order->due_amount ?? 0) > 0.009; @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="px-3 py-2.5 font-extrabold text-emerald-700 dark:text-emerald-400 whitespace-nowrap">{{ $order->order_no }}</td>
                            <td class="px-3 py-2.5 whitespace-nowrap text-xs text-slate-500">{{ \Carbon\Carbon::parse($order->invoice_date ?? $order->created_at)->format('d-m-Y') }}</td>
                            <td class="px-3 py-2.5">
                                <div class="font-bold text-slate-800 dark:text-white">{{ $order->customer?->customer_name ?? 'Walk-in' }}</div>
                                <div class="text-[11px] text-slate-400">{{ $order->customer?->mobile }}</div>
                            </td>
                            <td class="px-3 py-2.5 text-right font-semibold tabular-nums">{{ Money::formatBd($order->sub_total) }}</td>
                            <td class="px-3 py-2.5 text-right text-slate-500 tabular-nums">{{ Money::formatBd($order->discount_amount) }}</td>
                            <td class="px-3 py-2.5 text-right font-bold text-emerald-700 dark:text-emerald-400 tabular-nums">{{ Money::formatBd($order->paid_amount) }}</td>
                            <td class="px-3 py-2.5 text-right font-bold tabular-nums {{ $isDue ? 'text-rose-600' : 'text-slate-400' }}">{{ Money::formatBd($order->due_amount) }}</td>
                            <td class="px-3 py-2.5 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $isDue ? 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' }}">
                                    {{ $isDue ? 'Due' : 'Paid' }}
                                </span>
                            </td>
                            <td class="px-3 py-2.5 text-right whitespace-nowrap space-x-1">
                                <button type="button" wire:click="openView({{ $order->id }})" title="Quick View" class="px-2 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-200 text-xs font-bold cursor-pointer"><i class="fa-regular fa-eye"></i></button>
                                @if($isDue)
                                    <button type="button" wire:click="openPayment({{ $order->id }})" title="Collect Due" class="px-2 py-1 rounded-lg bg-amber-100 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 hover:bg-amber-200 text-xs font-bold cursor-pointer"><i class="fa-solid fa-hand-holding-dollar"></i></button>
                                @endif
                                <button type="button" wire:click="openEdit({{ $order->id }})" title="Full Edit" class="px-2 py-1 rounded-lg bg-sky-100 dark:bg-sky-950/50 text-sky-700 dark:text-sky-300 hover:bg-sky-200 text-xs font-bold cursor-pointer"><i class="fa-solid fa-pen-to-square"></i></button>
                                <a href="{{ route('retail.invoice.show', $order->id) }}" target="_blank" title="Print Bill" class="inline-block px-2 py-1 rounded-lg bg-emerald-100 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-200 text-xs font-bold"><i class="fa-solid fa-print"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr data-empty-row="true"><td colspan="9" class="px-4 py-8 text-center text-slate-500">No Retail invoices found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards --}}
        <div class="md:hidden divide-y divide-slate-200 dark:divide-slate-800">
            @foreach($orders as $order)
                @php $isDue = (float) ($order->due_amount ?? 0) > 0.009; @endphp
                <div class="p-3.5 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-extrabold text-emerald-700 dark:text-emerald-400 text-sm">{{ $order->order_no }}</span>
                        <span class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($order->invoice_date ?? $order->created_at)->format('d-m-Y') }}</span>
                    </div>
                    <p class="text-xs font-bold text-slate-800 dark:text-white">{{ $order->customer?->customer_name ?? 'Walk-in' }}</p>
                    <div class="grid grid-cols-2 gap-1 text-xs">
                        <span>Total: <strong>{{ Money::formatBd($order->sub_total) }}</strong></span>
                        <span>Paid: <strong class="text-emerald-600">{{ Money::formatBd($order->paid_amount) }}</strong></span>
                        <span>Due: <strong class="{{ $isDue ? 'text-rose-600' : 'text-slate-400' }}">{{ Money::formatBd($order->due_amount) }}</strong></span>
                        <span>Status: <strong>{{ $isDue ? 'Due' : 'Paid' }}</strong></span>
                    </div>
                    <div class="flex flex-wrap gap-1.5 pt-1">
                        <button type="button" wire:click="openView({{ $order->id }})" class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-xs font-bold">View</button>
                        @if($isDue)
                            <button type="button" wire:click="openPayment({{ $order->id }})" class="px-2.5 py-1 rounded-lg bg-amber-600 text-white text-xs font-bold">Pay Due</button>
                        @endif
                        <button type="button" wire:click="openEdit({{ $order->id }})" class="px-2.5 py-1 rounded-lg bg-sky-600 text-white text-xs font-bold">Edit</button>
                        <a href="{{ route('retail.invoice.show', $order->id) }}" target="_blank" class="px-2.5 py-1 rounded-lg bg-emerald-700 text-white text-xs font-bold">Print</a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="p-3 border-t border-slate-200 dark:border-slate-800">{{ $orders->links() }}</div>
    </div>

    {{-- Quick View Modal --}}
    <x-ui.modal :show="$showViewModal && $viewingOrder" max-width="lg" :title="'Invoice Details — ' . ($viewingOrder?->order_no ?? '')" close-method="closeModals">
        @if($viewingOrder)
            <div class="space-y-4 text-xs sm:text-sm">
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/70">
                    <div><span class="text-slate-400 block text-[11px]">Customer</span><strong>{{ $viewingOrder->customer?->customer_name ?? 'N/A' }}</strong></div>
                    <div><span class="text-slate-400 block text-[11px]">Date</span><strong>{{ \Carbon\Carbon::parse($viewingOrder->invoice_date ?? $viewingOrder->created_at)->format('d-m-Y') }}</strong></div>
                    <div><span class="text-slate-400 block text-[11px]">Due Amount</span><strong class="text-rose-600">{{ Money::formatBd($viewingOrder->due_amount) }}</strong></div>
                </div>
                <table class="w-full text-xs border border-slate-200 dark:border-slate-700">
                    <thead class="bg-[#15803d] text-white">
                        <tr><th class="px-2.5 py-2 text-left">Product</th><th class="px-2.5 py-2 text-center">Qty</th><th class="px-2.5 py-2 text-center">Ret</th><th class="px-2.5 py-2 text-right">Rate</th><th class="px-2.5 py-2 text-right">Line Total</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @foreach($viewingOrder->details as $d)
                            <tr>
                                <td class="px-2.5 py-2 font-semibold">{{ $d->product?->product_name ?? "#{$d->product_id}" }}</td>
                                <td class="px-2.5 py-2 text-center">{{ $d->quantity }}</td>
                                <td class="px-2.5 py-2 text-center text-rose-600">{{ $d->return_quantity ?? 0 }}</td>
                                <td class="px-2.5 py-2 text-right tabular-nums">{{ Money::formatBd($d->selling_price) }}</td>
                                <td class="px-2.5 py-2 text-right font-bold tabular-nums">{{ Money::formatBd(Money::mul($d->selling_price, $d->quantity)) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="flex justify-between items-center">
                    <div class="text-xs text-slate-500">Payments recorded: <strong>{{ $viewingOrder->payments->count() }}</strong></div>
                    <a href="{{ route('retail.invoice.show', $viewingOrder->id) }}" target="_blank" class="h-9 px-4 rounded-xl bg-emerald-700 text-white text-xs font-bold inline-flex items-center gap-1.5"><i class="fa-solid fa-print"></i> Print Bill</a>
                </div>
            </div>
        @endif
    </x-ui.modal>

    {{-- Direct Due Payment Modal --}}
    <x-ui.modal :show="$showPaymentModal" :title="'Collect Due — ' . $payOrderNo" close-method="closeModals">
        <form wire:submit="submitPayment" class="space-y-3">
            <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 flex justify-between text-sm">
                <span class="font-bold text-amber-800 dark:text-amber-300">Remaining Due:</span>
                <span class="font-extrabold text-amber-900 dark:text-amber-200">{{ Money::formatBd($payCurrentDue) }}</span>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold mb-1">Paid Amount (৳) *</label>
                    <input type="number" step="0.01" min="0" wire:model="payAmount" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-bold">
                </div>
                <div>
                    <label class="block text-xs font-bold mb-1">Discount (৳)</label>
                    <input type="number" step="0.01" min="0" wire:model="payDiscount" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold mb-1">Method</label>
                    <select wire:model="payMethod" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                        <option value="Cash">Cash</option><option value="bKash">bKash</option><option value="Nagad">Nagad</option><option value="Rocket">Rocket</option><option value="Bank">Bank</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold mb-1">Collection Date</label>
                    <x-ui.date-picker wire:model="payDate" />
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold mb-1">Transaction ID / Note</label>
                <input type="text" wire:model="payTrxId" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" wire:click="closeModals" class="h-10 px-4 rounded-xl border border-slate-300 text-sm font-bold">Cancel</button>
                <button type="submit" class="h-10 px-5 rounded-xl bg-emerald-700 text-white text-sm font-bold cursor-pointer">Save Payment</button>
            </div>
        </form>
    </x-ui.modal>

    {{-- Full Invoice Edit Modal (920px Standard) --}}
    <x-ui.modal :show="$showEditModal" max-width="full-edit" :title="'Full Edit Invoice — ' . $editOrderNo" close-method="closeModals">
        <form wire:submit="submitFullEdit" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold mb-1">Customer *</label>
                    <select wire:model="editCustomerId" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->customer_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold mb-1">Invoice Date</label>
                    <x-ui.date-picker wire:model="editDate" />
                </div>
                <div>
                    <label class="block text-xs font-bold mb-1">Challan / Note</label>
                    <input type="text" wire:model="editNote" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                </div>
            </div>

            {{-- Add Line Item Bar --}}
            <div class="flex gap-2">
                <select wire:model="editAddProductId" class="flex-1 h-9 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                    <option value="">+ Add another Retail product...</option>
                    @foreach($allProducts as $ap)
                        <option value="{{ $ap->id }}">{{ $ap->product_name }} (Stock: {{ $ap->quantity }})</option>
                    @endforeach
                </select>
                <button type="button" wire:click="addEditProduct" class="h-9 px-4 rounded-xl bg-emerald-700 text-white text-xs font-bold cursor-pointer">Add Item</button>
            </div>

            <div class="overflow-x-auto border border-slate-200 dark:border-slate-700 rounded-xl">
                <table class="w-full text-xs">
                    <thead class="bg-[#15803d] text-white">
                        <tr><th class="px-3 py-2 text-left">Product</th><th class="px-3 py-2 text-center w-24">Qty</th><th class="px-3 py-2 text-right w-32">Unit Price</th><th class="px-3 py-2 text-right">Total</th><th class="px-3 py-2 w-10"></th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @foreach($editItems as $idx => $item)
                            <tr>
                                <td class="px-3 py-2 font-semibold">
                                    {{ $item['product_name'] }}
                                    @if($item['return_quantity'] > 0)
                                        <span class="ml-1 text-[10px] font-bold text-rose-600">(Min Qty: {{ $item['return_quantity'] }} returned)</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" min="{{ max(1, $item['return_quantity']) }}" wire:model.live="editItems.{{ $idx }}.quantity" class="w-20 h-8 text-center rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 font-bold">
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" step="0.01" min="0" wire:model.live="editItems.{{ $idx }}.selling_price" class="w-28 h-8 text-right px-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 font-bold">
                                </td>
                                <td class="px-3 py-2 text-right font-bold tabular-nums">{{ Money::formatBd(Money::mul($item['selling_price'] ?? 0, $item['quantity'] ?? 0)) }}</td>
                                <td class="px-3 py-2 text-center">
                                    <button type="button" wire:click="removeEditItem({{ $idx }})" class="text-red-500 hover:text-red-700 cursor-pointer"><i class="fa-solid fa-trash"></i></button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold mb-1">Discount</label>
                    <div class="flex">
                        <select wire:model="editDiscountType" class="h-10 px-2 rounded-l-xl border-[1.5px] border-r-0 border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold">
                            <option value="fixed">৳</option><option value="percent">%</option>
                        </select>
                        <input type="number" step="0.01" min="0" wire:model="editDiscountValue" class="w-full h-10 px-3 rounded-r-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold mb-1">Delivery Charge (৳)</label>
                    <input type="number" step="0.01" min="0" wire:model="editDeliveryCharge" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold mb-1">Paid Amount (৳)</label>
                    <input type="number" step="0.01" min="0" wire:model="editPaidAmount" @disabled($editHasMultiplePayments) class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm disabled:opacity-50">
                    @if($editHasMultiplePayments)
                        <p class="text-[10px] text-amber-600 mt-0.5">Locked (multiple payments exist).</p>
                    @endif
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" wire:click="closeModals" class="h-10 px-4 rounded-xl border border-slate-300 text-sm font-bold">Cancel</button>
                <button type="submit" class="h-10 px-5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-bold cursor-pointer">Update Invoice</button>
            </div>
        </form>
    </x-ui.modal>
</div>
