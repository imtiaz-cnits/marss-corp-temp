@php use App\Support\Money; @endphp

<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Battery Invoice List</h1>
            <p class="text-xs sm:text-sm text-slate-500">Isolated Battery sales invoices, due collection, editing, and bill printing.</p>
        </div>
        <a href="{{ route('battery.pos') }}" wire:navigate class="h-10 px-4 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-bold flex items-center gap-2"><i class="fa-solid fa-cash-register"></i> New Battery Sale</a>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 overflow-hidden">
        <div class="p-3.5 border-b border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-2">
            <div class="flex flex-wrap items-center gap-2">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Invoice #, customer..." class="h-[38px] px-3 w-52 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                <select wire:model.live="statusFilter" class="h-[38px] px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold"><option value="">All</option><option value="paid">Paid</option><option value="due">Due</option></select>
                <div class="w-32"><x-ui.date-picker wire:model.live="startDate" placeholder="From" /></div>
                <div class="w-32"><x-ui.date-picker wire:model.live="endDate" placeholder="To" /></div>
            </div>
            <x-ui.export-buttons table-id="batteryInvoiceTable" filename="battery-invoices" title="MARSS Battery Invoices" />
        </div>
        <div class="overflow-x-auto">
            <table id="batteryInvoiceTable" data-export-skip-last="true" class="w-full text-xs sm:text-sm text-left">
                <thead class="bg-[#15803d] text-white text-xs uppercase whitespace-nowrap">
                    <tr><th class="px-3 py-3">Invoice #</th><th class="px-3 py-3">Date</th><th class="px-3 py-3">Customer</th><th class="px-3 py-3 text-right">SubTotal</th><th class="px-3 py-3 text-right">Paid</th><th class="px-3 py-3 text-right">Due</th><th class="px-3 py-3 text-right">Actions</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($orders as $o)
                        @php $isDue = (float) ($o->due_amount ?? 0) > 0.009; @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="px-3 py-2.5 font-extrabold text-emerald-700">{{ $o->order_no }}</td>
                            <td class="px-3 py-2.5 text-slate-500">{{ \Carbon\Carbon::parse($o->invoice_date ?? $o->created_at)->format('d-m-Y') }}</td>
                            <td class="px-3 py-2.5 font-bold">{{ $o->customer?->customer_name ?? 'Walk-in' }}</td>
                            <td class="px-3 py-2.5 text-right tabular-nums">{{ Money::formatBd($o->sub_total) }}</td>
                            <td class="px-3 py-2.5 text-right font-bold text-emerald-700 tabular-nums">{{ Money::formatBd($o->paid_amount) }}</td>
                            <td class="px-3 py-2.5 text-right font-bold tabular-nums {{ $isDue ? 'text-rose-600' : 'text-slate-400' }}">{{ Money::formatBd($o->due_amount) }}</td>
                            <td class="px-3 py-2.5 text-right whitespace-nowrap space-x-1">
                                @if($isDue)<button type="button" wire:click="openPayment({{ $o->id }})" class="px-2 py-1 rounded-lg bg-amber-100 text-amber-700 text-xs font-bold cursor-pointer"><i class="fa-solid fa-hand-holding-dollar"></i></button>@endif
                                <button type="button" wire:click="openEdit({{ $o->id }})" class="px-2 py-1 rounded-lg bg-sky-100 text-sky-700 text-xs font-bold cursor-pointer"><i class="fa-solid fa-pen"></i></button>
                                <a href="{{ route('battery.invoice.show', $o->id) }}" target="_blank" class="inline-block px-2 py-1 rounded-lg bg-emerald-100 text-emerald-700 text-xs font-bold"><i class="fa-solid fa-print"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr data-empty-row="true"><td colspan="7" class="px-4 py-8 text-center text-slate-500">No Battery invoices found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-t border-slate-200 dark:border-slate-800">{{ $orders->links() }}</div>
    </div>

    <x-ui.modal :show="$showPaymentModal" :title="'Collect Battery Due — ' . $payOrderNo" close-method="closeModals">
        <form wire:submit="submitPayment" class="space-y-3">
            <div class="p-3 rounded-xl bg-amber-50 flex justify-between text-sm font-bold"><span>Remaining Due:</span><span class="text-rose-600">{{ Money::formatBd($payCurrentDue) }}</span></div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="block text-xs font-bold mb-1">Paid (৳) *</label><input type="number" step="0.01" wire:model="payAmount" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-bold"></div>
                <div><label class="block text-xs font-bold mb-1">Discount (৳)</label><input type="number" step="0.01" wire:model="payDiscount" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"></div>
                <div><label class="block text-xs font-bold mb-1">Method</label><select wire:model="payMethod" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"><option value="Cash">Cash</option><option value="bKash">bKash</option><option value="Nagad">Nagad</option><option value="Bank">Bank</option></select></div>
                <div><label class="block text-xs font-bold mb-1">Date</label><x-ui.date-picker wire:model="payDate" /></div>
            </div>
            <div class="flex justify-end gap-2"><button type="button" wire:click="closeModals" class="h-10 px-4 rounded-xl border text-sm font-bold">Cancel</button><button type="submit" class="h-10 px-5 rounded-xl bg-emerald-700 text-white text-sm font-bold">Save Payment</button></div>
        </form>
    </x-ui.modal>

    <x-ui.modal :show="$showEditModal" max-width="full-edit" :title="'Edit Battery Invoice — ' . $editOrderNo" close-method="closeModals">
        <form wire:submit="submitFullEdit" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div><label class="block text-xs font-bold mb-1">Customer</label><select wire:model="editCustomerId" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">@foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->customer_name }}</option>@endforeach</select></div>
                <div><label class="block text-xs font-bold mb-1">Date</label><x-ui.date-picker wire:model="editDate" /></div>
                <div><label class="block text-xs font-bold mb-1">Challan / Note</label><input type="text" wire:model="editNote" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"></div>
            </div>
            <div class="flex gap-2">
                <select wire:model="editAddProductId" class="flex-1 h-9 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs"><option value="">+ Add Battery Product...</option>@foreach($allProducts as $ap)<option value="{{ $ap->id }}">{{ $ap->product_name }} (Qty: {{ $ap->quantity }})</option>@endforeach</select>
                <button type="button" wire:click="addEditProduct" class="h-9 px-4 rounded-xl bg-emerald-700 text-white text-xs font-bold">Add</button>
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
            <div class="flex justify-end gap-2"><button type="button" wire:click="closeModals" class="h-10 px-4 rounded-xl border text-sm font-bold">Cancel</button><button type="submit" class="h-10 px-5 rounded-xl bg-emerald-700 text-white text-sm font-bold">Update</button></div>
        </form>
    </x-ui.modal>
</div>
