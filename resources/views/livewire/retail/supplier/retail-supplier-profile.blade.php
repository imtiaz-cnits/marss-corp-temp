@php use App\Support\Money; @endphp

<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="{{ route('retail.suppliers') }}" wire:navigate class="text-xs font-bold text-emerald-700 dark:text-emerald-400 hover:underline">&larr; Back to Suppliers</a>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight mt-0.5">{{ $supplier->name }} <span class="text-sm font-mono text-slate-400">{{ $supplier->supplier_id }}</span></h1>
            <p class="text-xs text-slate-500">{{ $supplier->company }} • {{ $supplier->mobile }} • {{ $supplier->address }}</p>
        </div>
        @if((float) $balance['total_due'] > 0)
            <button type="button" wire:click="openPayModal" class="h-10 px-4 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-bold flex items-center gap-2 cursor-pointer">
                <i class="fa-solid fa-hand-holding-dollar"></i> Pay Supplier Due
            </button>
        @endif
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 p-4"><p class="text-[11px] font-bold uppercase text-slate-500">Total Purchased</p><p class="text-lg font-extrabold mt-1 tabular-nums">{{ Money::formatBd($balance['total_purchased']) }}</p></div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 p-4"><p class="text-[11px] font-bold uppercase text-slate-500">Total Paid</p><p class="text-lg font-extrabold text-emerald-700 mt-1 tabular-nums">{{ Money::formatBd($balance['total_paid']) }}</p></div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 p-4"><p class="text-[11px] font-bold uppercase text-slate-500">Prev Payable</p><p class="text-lg font-extrabold text-amber-600 mt-1 tabular-nums">{{ Money::formatBd($balance['previous_due']) }}</p></div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 p-4"><p class="text-[11px] font-bold uppercase text-slate-500">Purchase Due</p><p class="text-lg font-extrabold text-rose-600 mt-1 tabular-nums">{{ Money::formatBd($balance['purchases_due']) }}</p></div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-rose-300 dark:border-rose-800 p-4"><p class="text-[11px] font-bold uppercase text-rose-500">Net Total Due</p><p class="text-lg font-extrabold text-rose-600 mt-1 tabular-nums">{{ Money::formatBd($balance['total_due']) }}</p></div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-800 font-extrabold text-sm">Purchase Invoices ({{ $purchases->count() }})</div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs sm:text-sm text-left">
                <thead class="bg-[#15803d] text-white text-xs uppercase whitespace-nowrap">
                    <tr><th class="px-3 py-2.5">Purchase ID</th><th class="px-3 py-2.5">Date</th><th class="px-3 py-2.5 text-right">Subtotal</th><th class="px-3 py-2.5 text-right">Paid</th><th class="px-3 py-2.5 text-right">Due</th><th class="px-3 py-2.5 text-right">Print</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($purchases as $p)
                        <tr>
                            <td class="px-3 py-2 font-bold">{{ $p->purchase_id }}</td>
                            <td class="px-3 py-2 text-slate-500">{{ $p->date }}</td>
                            <td class="px-3 py-2 text-right tabular-nums">{{ Money::formatBd($p->grand_subtotal) }}</td>
                            <td class="px-3 py-2 text-right font-bold text-emerald-700 tabular-nums">{{ Money::formatBd($p->paid_amount) }}</td>
                            <td class="px-3 py-2 text-right font-bold text-rose-600 tabular-nums">{{ Money::formatBd($p->due_amount) }}</td>
                            <td class="px-3 py-2 text-right"><a href="{{ route('retail.purchase.show', $p->id) }}" target="_blank" class="text-emerald-700 font-bold hover:underline">Print</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">No purchases found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-800 font-extrabold text-sm">Purchase Payment Ledger ({{ $payments->count() }})</div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-[#15803d] text-white uppercase"><tr><th class="px-3 py-2">Date</th><th class="px-3 py-2">Purchase</th><th class="px-3 py-2">Method</th><th class="px-3 py-2 text-right">Paid</th></tr></thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse($payments as $pay)
                            <tr><td class="px-3 py-2">{{ $pay->purchase_due_collection_date }}</td><td class="px-3 py-2 font-bold">{{ $pay->purchase?->purchase_id }}</td><td class="px-3 py-2">{{ $pay->payment_method }}</td><td class="px-3 py-2 text-right font-bold text-emerald-700">{{ Money::formatBd($pay->paid_amount) }}</td></tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-6 text-center text-slate-500">No direct payments.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-800 font-extrabold text-sm">Purchase Returns ({{ $returns->count() }})</div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-[#15803d] text-white uppercase"><tr><th class="px-3 py-2">Return #</th><th class="px-3 py-2">Product</th><th class="px-3 py-2 text-center">Qty</th><th class="px-3 py-2 text-right">Amount</th></tr></thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse($returns as $ret)
                            <tr><td class="px-3 py-2 font-bold">{{ $ret->return_number }}</td><td class="px-3 py-2">{{ $ret->product?->product_name }}</td><td class="px-3 py-2 text-center">{{ $ret->quantity }}</td><td class="px-3 py-2 text-right font-bold">{{ Money::formatBd($ret->amount) }}</td></tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-6 text-center text-slate-500">No returns.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <x-ui.modal :show="$showPayModal" :title="'Pay Supplier Due — ' . $supplier->name" close-method="closeModal">
        <form wire:submit="submitPayment" class="space-y-3">
            <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/40 flex justify-between text-sm font-bold">
                <span>Total Due Payable:</span><span class="text-rose-600">{{ Money::formatBd($balance['total_due']) }}</span>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="block text-xs font-bold mb-1">Paid Amount (৳) *</label><input type="number" step="0.01" min="0" wire:model="paid_amount" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-bold"></div>
                <div><label class="block text-xs font-bold mb-1">Discount (৳)</label><input type="number" step="0.01" min="0" wire:model="discount_amount" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"></div>
                <div><label class="block text-xs font-bold mb-1">Apply Against</label><select wire:model="collection_type" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"><option value="all">All (FIFO)</option><option value="previous">Previous Due Only</option><option value="purchase">Purchases Only</option></select></div>
                <div><label class="block text-xs font-bold mb-1">Method</label><select wire:model="payment_method" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"><option value="Cash">Cash</option><option value="Bank">Bank</option><option value="bKash">bKash</option><option value="Nagad">Nagad</option></select></div>
                <div><label class="block text-xs font-bold mb-1">Date</label><x-ui.date-picker wire:model="date" /></div>
                <div><label class="block text-xs font-bold mb-1">Trx ID</label><input type="text" wire:model="transaction_id" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"></div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" wire:click="closeModal" class="h-10 px-4 rounded-xl border border-slate-300 text-sm font-bold">Cancel</button>
                <button type="submit" class="h-10 px-5 rounded-xl bg-emerald-700 text-white text-sm font-bold cursor-pointer">Confirm Payment</button>
            </div>
        </form>
    </x-ui.modal>
</div>
