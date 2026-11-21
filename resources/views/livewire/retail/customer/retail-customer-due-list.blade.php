@php use App\Support\Money; @endphp

<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Retail Customer Due List</h1>
            <p class="text-xs sm:text-sm text-slate-500">Outstanding customer receivables across opening due and unpaid Retail invoices.</p>
        </div>
        <div class="px-4 py-2 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-sm font-extrabold text-rose-700 dark:text-rose-300">
            Total Receivable: {{ Money::formatBd($grandDue) }}
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 overflow-hidden">
        <div class="p-3.5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search customer..." class="h-9 px-3 w-64 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
        </div>
        <div class="overflow-x-auto">
            <table id="retailCustDueTable" data-export-skip-last="true" class="w-full text-xs sm:text-sm text-left">
                <thead class="bg-[#15803d] text-white text-xs uppercase whitespace-nowrap">
                    <tr><th class="px-3 py-3">Customer</th><th class="px-3 py-3">Mobile</th><th class="px-3 py-3 text-right">Prev Due</th><th class="px-3 py-3 text-right">Orders Due</th><th class="px-3 py-3 text-right">Total Due</th><th class="px-3 py-3 text-right">Action</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($customers as $c)
                        @php $tot = Money::add($c->previous_due_amount ?? 0, $c->orders_sum_due_amount ?? 0); @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="px-3 py-2.5 font-bold"><a href="{{ route('retail.customer.profile', $c->id) }}" wire:navigate class="text-emerald-700 hover:underline">{{ $c->customer_name }}</a></td>
                            <td class="px-3 py-2.5">{{ $c->mobile ?: '—' }}</td>
                            <td class="px-3 py-2.5 text-right tabular-nums">{{ Money::formatBd($c->previous_due_amount) }}</td>
                            <td class="px-3 py-2.5 text-right tabular-nums">{{ Money::formatBd($c->orders_sum_due_amount ?? 0) }}</td>
                            <td class="px-3 py-2.5 text-right font-extrabold text-rose-600 tabular-nums">{{ Money::formatBd($tot) }}</td>
                            <td class="px-3 py-2.5 text-right">
                                <button type="button" wire:click="openCollect({{ $c->id }})" class="px-3 py-1.5 rounded-lg bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold cursor-pointer">Collect Due</button>
                            </td>
                        </tr>
                    @empty
                        <tr data-empty-row="true"><td colspan="6" class="px-4 py-8 text-center text-slate-500">Zero customer dues pending.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <x-ui.modal :show="$showCollectModal" :title="'Collect Due — ' . $collectCustomerName" close-method="closeModal">
        <form wire:submit="submitCollect" class="space-y-3">
            <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/40 flex justify-between text-sm font-bold">
                <span>Total Due:</span><span class="text-rose-600">{{ Money::formatBd($collectTotalDue) }}</span>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="block text-xs font-bold mb-1">Paid Amount (৳) *</label><input type="number" step="0.01" min="0" wire:model="paid_amount" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-bold"></div>
                <div><label class="block text-xs font-bold mb-1">Discount (৳)</label><input type="number" step="0.01" min="0" wire:model="discount_amount" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"></div>
                <div><label class="block text-xs font-bold mb-1">Apply Against</label><select wire:model="collection_type" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"><option value="all">All (FIFO)</option><option value="previous">Previous Due Only</option><option value="invoice">Invoices Only</option></select></div>
                <div><label class="block text-xs font-bold mb-1">Method</label><select wire:model="payment_method" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"><option value="Cash">Cash</option><option value="bKash">bKash</option><option value="Nagad">Nagad</option><option value="Bank">Bank</option></select></div>
                <div class="col-span-2"><label class="block text-xs font-bold mb-1">Collection Date</label><x-ui.date-picker wire:model="date" /></div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" wire:click="closeModal" class="h-10 px-4 rounded-xl border border-slate-300 text-sm font-bold">Cancel</button>
                <button type="submit" class="h-10 px-5 rounded-xl bg-emerald-700 text-white text-sm font-bold cursor-pointer">Save Collection</button>
            </div>
        </form>
    </x-ui.modal>
</div>
