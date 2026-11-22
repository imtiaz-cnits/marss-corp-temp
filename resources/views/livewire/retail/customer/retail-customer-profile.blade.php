@php use App\Support\Money; @endphp

<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ $customer->customer_name }} <span class="text-sm font-mono text-slate-400">{{ $customer->customer_id }}</span></h1>
            <p class="text-xs text-slate-500">{{ $customer->mobile }} • {{ $customer->address_details }}</p>
        </div>
        @if((float) $balance['total_due'] > 0)
            <button type="button" wire:click="openCollect" class="h-10 px-4 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-bold flex items-center gap-2 cursor-pointer">
                <i class="fa-solid fa-hand-holding-dollar"></i> Collect Due
            </button>
        @endif
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 p-4"><p class="text-[11px] font-bold uppercase text-slate-500">Total Billed</p><p class="text-lg font-extrabold mt-1 tabular-nums">{{ Money::formatBd($balance['total_billed']) }}</p></div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 p-4"><p class="text-[11px] font-bold uppercase text-slate-500">Total Paid</p><p class="text-lg font-extrabold text-emerald-700 mt-1 tabular-nums">{{ Money::formatBd($balance['total_paid']) }}</p></div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 p-4"><p class="text-[11px] font-bold uppercase text-slate-500">Prev Due</p><p class="text-lg font-extrabold text-amber-600 mt-1 tabular-nums">{{ Money::formatBd($balance['previous_due']) }}</p></div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 p-4"><p class="text-[11px] font-bold uppercase text-slate-500">Orders Due</p><p class="text-lg font-extrabold text-rose-600 mt-1 tabular-nums">{{ Money::formatBd($balance['orders_due']) }}</p></div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-rose-300 dark:border-rose-800 p-4"><p class="text-[11px] font-bold uppercase text-rose-500">Total Due</p><p class="text-lg font-extrabold text-rose-600 mt-1 tabular-nums">{{ Money::formatBd($balance['total_due']) }}</p></div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-800 font-extrabold text-sm">Customer Invoices ({{ $orders->count() }})</div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs sm:text-sm text-left">
                <thead class="bg-[#15803d] text-white text-xs uppercase whitespace-nowrap">
                    <tr><th class="px-3 py-2.5">Invoice #</th><th class="px-3 py-2.5">Date</th><th class="px-3 py-2.5 text-right">SubTotal</th><th class="px-3 py-2.5 text-right">Paid</th><th class="px-3 py-2.5 text-right">Due</th><th class="px-3 py-2.5 text-center">Print</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($orders as $o)
                        <tr>
                            <td class="px-3 py-2 font-bold">{{ $o->order_no }}</td>
                            <td class="px-3 py-2 text-slate-500">{{ \Carbon\Carbon::parse($o->invoice_date ?? $o->created_at)->format('d-m-Y') }}</td>
                            <td class="px-3 py-2 text-right tabular-nums">{{ Money::formatBd($o->sub_total) }}</td>
                            <td class="px-3 py-2 text-right font-bold text-emerald-700 tabular-nums">{{ Money::formatBd($o->paid_amount) }}</td>
                            <td class="px-3 py-2 text-right font-bold text-rose-600 tabular-nums">{{ Money::formatBd($o->due_amount) }}</td>
                            <td class="px-3 py-2 text-center">
                                <a href="{{ route('retail.invoice.show', $o->id) }}" target="_blank" class="w-[30px] h-[30px] rounded-lg bg-emerald-50 hover:bg-[#15803d] text-[#15803d] hover:text-white border border-emerald-200 dark:bg-emerald-950/50 dark:border-emerald-800 dark:text-emerald-400 inline-flex items-center justify-center transition-all shadow-2xs" title="Print Invoice">
                                    <i class="fa-solid fa-print text-xs"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">No invoices yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-800 font-extrabold text-sm">Payment History ({{ $payments->count() }})</div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-[#15803d] text-white uppercase"><tr><th class="px-3 py-2">Date</th><th class="px-3 py-2">Invoice</th><th class="px-3 py-2">Method</th><th class="px-3 py-2 text-right">Paid</th></tr></thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse($payments as $pay)
                            <tr><td class="px-3 py-2">{{ $pay->due_collection_date }}</td><td class="px-3 py-2 font-bold">{{ $pay->order?->order_no }}</td><td class="px-3 py-2">{{ $pay->payment_method }}</td><td class="px-3 py-2 text-right font-bold text-emerald-700">{{ Money::formatBd($pay->paid_amount) }}</td></tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-6 text-center text-slate-500">No payment entries.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-800 font-extrabold text-sm">Sales Returns ({{ $returns->count() }})</div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-[#15803d] text-white uppercase">
                        <tr>
                            <th class="px-3 py-2">DATE</th>
                            <th class="px-3 py-2">INVOICE REF</th>
                            <th class="px-3 py-2">PRODUCT</th>
                            <th class="px-3 py-2 text-center">QTY</th>
                            <th class="px-3 py-2 text-right">AMOUNT</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse($returns as $ret)
                            <tr>
                                <td class="px-3 py-2 whitespace-nowrap text-slate-600 dark:text-slate-400 font-medium">
                                    {{ \Carbon\Carbon::parse($ret->date ?? $ret->created_at)->format('Y-m-d') }}
                                </td>
                                <td class="px-3 py-2 font-bold whitespace-nowrap">
                                    @if($ret->order_id)
                                        <a href="{{ route('retail.invoice.show', $ret->order_id) }}" target="_blank" class="text-emerald-700 dark:text-emerald-400 hover:underline">
                                            {{ str_starts_with((string)($ret->order?->order_no ?? ''), '#') ? $ret->order->order_no : '#'.($ret->order?->order_no ?? ('InvID'.$ret->order_id)) }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-slate-800 dark:text-slate-200">{{ $ret->product?->product_name ?? '-' }}</td>
                                <td class="px-3 py-2 text-center font-bold text-slate-700 dark:text-slate-300">{{ (int)$ret->quantity }}</td>
                                <td class="px-3 py-2 text-right font-black text-rose-600 dark:text-rose-400 tabular-nums">{{ Money::formatBd($ret->amount) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">No returns.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <x-ui.modal :show="$showCollectModal" :title="'Collect Due — ' . $customer->customer_name" close-method="closeModal">
        <form wire:submit="submitCollect" class="space-y-3">
            <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/40 flex justify-between text-sm font-bold">
                <span>Total Due Receivable:</span><span class="text-rose-600">{{ Money::formatBd($balance['total_due']) }}</span>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="block text-xs font-bold mb-1">Collected Amount (৳) *</label><input type="number" step="0.01" min="0" wire:model="paid_amount" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-bold"></div>
                <div><label class="block text-xs font-bold mb-1">Discount (৳)</label><input type="number" step="0.01" min="0" wire:model="discount_amount" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"></div>
                <div><label class="block text-xs font-bold mb-1">Apply Against</label><select wire:model="collection_type" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"><option value="all">All (FIFO)</option><option value="previous">Previous Due Only</option><option value="invoice">Invoices Only</option></select></div>
                <div><label class="block text-xs font-bold mb-1">Method</label><select wire:model="payment_method" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"><option value="Cash">Cash</option><option value="bKash">bKash</option><option value="Nagad">Nagad</option><option value="Bank">Bank</option></select></div>
                <div><label class="block text-xs font-bold mb-1">Date</label><x-ui.date-picker wire:model="date" /></div>
                <div><label class="block text-xs font-bold mb-1">Trx ID</label><input type="text" wire:model="transaction_id" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"></div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" wire:click="closeModal" class="h-10 px-4 rounded-xl border border-slate-300 text-sm font-bold">Cancel</button>
                <button type="submit" class="h-10 px-5 rounded-xl bg-emerald-700 text-white text-sm font-bold cursor-pointer">Confirm Collection</button>
            </div>
        </form>
    </x-ui.modal>
</div>
