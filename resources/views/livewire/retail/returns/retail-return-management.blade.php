@php use App\Support\Money; @endphp

<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Retail Return Management</h1>
            <p class="text-xs sm:text-sm text-slate-500">Process atomic Retail sales returns &amp; purchase returns with synchronized due adjustment.</p>
        </div>
        <button type="button" wire:click="openCreateModal" class="h-10 px-4 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-bold flex items-center gap-2 cursor-pointer">
            <i class="fa-solid fa-rotate-left"></i> Process {{ $tab === 'sales' ? 'Sales Return' : 'Purchase Return' }}
        </button>
    </div>

    <div class="flex gap-2">
        <button type="button" wire:click="switchTab('sales')" class="h-9 px-4 rounded-xl text-xs font-bold cursor-pointer {{ $tab === 'sales' ? 'bg-emerald-700 text-white' : 'bg-white dark:bg-slate-900 border-[1.5px] border-slate-300 dark:border-slate-700' }}">Sales Returns</button>
        <button type="button" wire:click="switchTab('purchase')" class="h-9 px-4 rounded-xl text-xs font-bold cursor-pointer {{ $tab === 'purchase' ? 'bg-emerald-700 text-white' : 'bg-white dark:bg-slate-900 border-[1.5px] border-slate-300 dark:border-slate-700' }}">Purchase Returns</button>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            @if($tab === 'sales')
                <table class="w-full text-xs sm:text-sm text-left">
                    <thead class="bg-[#15803d] text-white text-xs uppercase whitespace-nowrap">
                        <tr><th class="px-3 py-3">Return #</th><th class="px-3 py-3">Date</th><th class="px-3 py-3">Invoice</th><th class="px-3 py-3">Customer</th><th class="px-3 py-3">Product</th><th class="px-3 py-3 text-center">Qty</th><th class="px-3 py-3 text-right">Amount</th><th class="px-3 py-3 text-right">Voucher</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse($salesReturns as $r)
                            <tr>
                                <td class="px-3 py-2.5 font-extrabold text-emerald-700">{{ $r->return_number }}</td>
                                <td class="px-3 py-2.5 text-slate-500">{{ $r->date }}</td>
                                <td class="px-3 py-2.5 font-bold">{{ $r->order?->order_no }}</td>
                                <td class="px-3 py-2.5">{{ $r->customer?->customer_name ?? '—' }}</td>
                                <td class="px-3 py-2.5">{{ $r->product?->product_name ?? '—' }}</td>
                                <td class="px-3 py-2.5 text-center font-bold">{{ $r->quantity }}</td>
                                <td class="px-3 py-2.5 text-right font-bold tabular-nums">{{ Money::formatBd($r->amount) }}</td>
                                <td class="px-3 py-2.5 text-right"><a href="{{ route('retail.return.show', $r->id) }}" target="_blank" class="px-2.5 py-1 rounded-lg bg-emerald-100 text-emerald-700 text-xs font-bold">Print</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-8 text-center text-slate-500">No sales returns recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="p-3 border-t border-slate-200 dark:border-slate-800">{{ $salesReturns->links() }}</div>
            @else
                <table class="w-full text-xs sm:text-sm text-left">
                    <thead class="bg-[#15803d] text-white text-xs uppercase whitespace-nowrap">
                        <tr><th class="px-3 py-3">Return #</th><th class="px-3 py-3">Date</th><th class="px-3 py-3">Purchase</th><th class="px-3 py-3">Supplier</th><th class="px-3 py-3">Product</th><th class="px-3 py-3 text-center">Qty</th><th class="px-3 py-3 text-right">Amount</th><th class="px-3 py-3 text-right">Voucher</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse($purchaseReturns as $r)
                            <tr>
                                <td class="px-3 py-2.5 font-extrabold text-emerald-700">{{ $r->return_number }}</td>
                                <td class="px-3 py-2.5 text-slate-500">{{ $r->date }}</td>
                                <td class="px-3 py-2.5 font-bold">{{ $r->purchase?->purchase_id }}</td>
                                <td class="px-3 py-2.5">{{ $r->supplier?->name ?? '—' }}</td>
                                <td class="px-3 py-2.5">{{ $r->product?->product_name ?? '—' }}</td>
                                <td class="px-3 py-2.5 text-center font-bold">{{ $r->quantity }}</td>
                                <td class="px-3 py-2.5 text-right font-bold tabular-nums">{{ Money::formatBd($r->amount) }}</td>
                                <td class="px-3 py-2.5 text-right"><a href="{{ route('retail.purchase-return.show', $r->id) }}" target="_blank" class="px-2.5 py-1 rounded-lg bg-emerald-100 text-emerald-700 text-xs font-bold">Print</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-8 text-center text-slate-500">No purchase returns recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="p-3 border-t border-slate-200 dark:border-slate-800">{{ $purchaseReturns->links() }}</div>
            @endif
        </div>
    </div>

    <x-ui.modal :show="$showCreateModal" max-width="lg" :title="$tab === 'sales' ? 'Process Retail Sales Return' : 'Process Retail Purchase Return'" close-method="closeModal">
        <form wire:submit="submitReturn" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold mb-1">{{ $tab === 'sales' ? 'Select Sales Invoice *' : 'Select Purchase Invoice *' }}</label>
                    @if($tab === 'sales')
                        <select wire:model.live="selectedOrderId" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                            <option value="">Choose Invoice...</option>
                            @foreach($orders as $o)<option value="{{ $o->id }}">{{ $o->order_no }} — {{ $o->customer?->customer_name }}</option>@endforeach
                        </select>
                    @else
                        <select wire:model.live="selectedPurchaseId" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                            <option value="">Choose Purchase...</option>
                            @foreach($purchases as $p)<option value="{{ $p->id }}">{{ $p->purchase_id }} — {{ $p->supplier?->name }}</option>@endforeach
                        </select>
                    @endif
                </div>
                <div><label class="block text-xs font-bold mb-1">Return Date *</label><x-ui.date-picker wire:model="date" /></div>
            </div>

            @if(!empty($returnLines))
                <div class="overflow-x-auto border border-slate-200 dark:border-slate-700 rounded-xl">
                    <table class="w-full text-xs">
                        <thead class="bg-[#15803d] text-white"><tr><th class="px-3 py-2 text-left">Product</th><th class="px-3 py-2 text-center">Returnable</th><th class="px-3 py-2 text-right">Rate</th><th class="px-3 py-2 text-center w-28">Return Qty</th></tr></thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @foreach($returnLines as $idx => $line)
                                <tr>
                                    <td class="px-3 py-2 font-bold">{{ $line['product_name'] }}</td>
                                    <td class="px-3 py-2 text-center">{{ $line['max_qty'] }}</td>
                                    <td class="px-3 py-2 text-right">{{ Money::formatBd($line['unit_price']) }}</td>
                                    <td class="px-3 py-2 text-center"><input type="number" min="0" max="{{ $line['max_qty'] }}" wire:model="returnLines.{{ $idx }}.return_qty" class="w-20 h-8 text-center rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 font-bold"></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" wire:click="closeModal" class="h-10 px-4 rounded-xl border border-slate-300 text-sm font-bold">Cancel</button>
                <button type="submit" class="h-10 px-5 rounded-xl bg-emerald-700 text-white text-sm font-bold cursor-pointer">Confirm Return</button>
            </div>
        </form>
    </x-ui.modal>
</div>
