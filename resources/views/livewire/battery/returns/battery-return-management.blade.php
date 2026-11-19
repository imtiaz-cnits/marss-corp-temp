@php use App\Support\Money; @endphp

<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Battery Return Management</h1>
        <button type="button" wire:click="openCreateModal" class="h-10 px-4 rounded-xl bg-emerald-700 text-white text-sm font-bold cursor-pointer">Process {{ $tab === 'sales' ? 'Sales Return' : 'Purchase Return' }}</button>
    </div>

    <div class="flex gap-2">
        <button type="button" wire:click="switchTab('sales')" class="h-9 px-4 rounded-xl text-xs font-bold cursor-pointer {{ $tab === 'sales' ? 'bg-emerald-700 text-white' : 'bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700' }}">Sales Returns</button>
        <button type="button" wire:click="switchTab('purchase')" class="h-9 px-4 rounded-xl text-xs font-bold cursor-pointer {{ $tab === 'purchase' ? 'bg-emerald-700 text-white' : 'bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700' }}">Purchase Returns</button>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 overflow-hidden">
        @if($tab === 'sales')
            <table class="w-full text-xs sm:text-sm text-left">
                <thead class="bg-[#15803d] text-white text-xs uppercase"><tr><th class="px-3 py-3">Return #</th><th class="px-3 py-3">Date</th><th class="px-3 py-3">Invoice</th><th class="px-3 py-3">Product</th><th class="px-3 py-3 text-center">Qty</th><th class="px-3 py-3 text-right">Amount</th><th class="px-3 py-3 text-right">Print</th></tr></thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($salesReturns as $r)
                        <tr><td class="px-3 py-2.5 font-bold">{{ $r->return_number }}</td><td class="px-3 py-2.5">{{ $r->date }}</td><td class="px-3 py-2.5">{{ $r->order?->order_no }}</td><td class="px-3 py-2.5">{{ $r->product?->product_name }}</td><td class="px-3 py-2.5 text-center">{{ $r->quantity }}</td><td class="px-3 py-2.5 text-right font-bold">{{ Money::formatBd($r->amount) }}</td><td class="px-3 py-2.5 text-right"><a href="{{ route('battery.return.show', $r->id) }}" target="_blank" class="text-emerald-700 font-bold">Print</a></td></tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">No Battery sales returns.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-3">{{ $salesReturns->links() }}</div>
        @else
            <table class="w-full text-xs sm:text-sm text-left">
                <thead class="bg-[#15803d] text-white text-xs uppercase"><tr><th class="px-3 py-3">Return #</th><th class="px-3 py-3">Date</th><th class="px-3 py-3">Purchase</th><th class="px-3 py-3">Product</th><th class="px-3 py-3 text-center">Qty</th><th class="px-3 py-3 text-right">Amount</th><th class="px-3 py-3 text-right">Print</th></tr></thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($purchaseReturns as $r)
                        <tr><td class="px-3 py-2.5 font-bold">{{ $r->return_number }}</td><td class="px-3 py-2.5">{{ $r->date }}</td><td class="px-3 py-2.5">{{ $r->purchase?->purchase_id }}</td><td class="px-3 py-2.5">{{ $r->product?->product_name }}</td><td class="px-3 py-2.5 text-center">{{ $r->quantity }}</td><td class="px-3 py-2.5 text-right font-bold">{{ Money::formatBd($r->amount) }}</td><td class="px-3 py-2.5 text-right"><a href="{{ route('battery.purchase-return.show', $r->id) }}" target="_blank" class="text-emerald-700 font-bold">Print</a></td></tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">No Battery purchase returns.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-3">{{ $purchaseReturns->links() }}</div>
        @endif
    </div>

    <x-ui.modal :show="$showCreateModal" max-width="lg" :title="$tab === 'sales' ? 'Battery Sales Return' : 'Battery Purchase Return'" close-method="closeModal">
        <form wire:submit="submitReturn" class="space-y-3">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold mb-1">{{ $tab === 'sales' ? 'Battery Invoice *' : 'Battery Purchase *' }}</label>
                    @if($tab === 'sales')
                        <select wire:model.live="selectedOrderId" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"><option value="">Select...</option>@foreach($orders as $o)<option value="{{ $o->id }}">{{ $o->order_no }} — {{ $o->customer?->customer_name }}</option>@endforeach</select>
                    @else
                        <select wire:model.live="selectedPurchaseId" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"><option value="">Select...</option>@foreach($purchases as $p)<option value="{{ $p->id }}">{{ $p->purchase_id }} — {{ $p->supplier?->name }}</option>@endforeach</select>
                    @endif
                </div>
                <div><label class="block text-xs font-bold mb-1">Date *</label><x-ui.date-picker wire:model="date" /></div>
            </div>
            @if(!empty($returnLines))
                <table class="w-full text-xs border">
                    <thead class="bg-[#15803d] text-white"><tr><th class="px-3 py-2 text-left">Product</th><th class="px-3 py-2 text-center">Max</th><th class="px-3 py-2 text-center">Return Qty</th></tr></thead>
                    <tbody>
                        @foreach($returnLines as $idx => $l)
                            <tr><td class="px-3 py-2 font-bold">{{ $l['product_name'] }}</td><td class="px-3 py-2 text-center">{{ $l['max_qty'] }}</td><td class="px-3 py-2 text-center"><input type="number" min="0" max="{{ $l['max_qty'] }}" wire:model="returnLines.{{ $idx }}.return_qty" class="w-20 h-8 text-center border rounded"></td></tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
            <div class="flex justify-end gap-2"><button type="button" wire:click="closeModal" class="h-10 px-4 rounded-xl border text-sm font-bold">Cancel</button><button type="submit" class="h-10 px-5 rounded-xl bg-emerald-700 text-white text-sm font-bold">Confirm Return</button></div>
        </form>
    </x-ui.modal>
</div>
