@php use App\Support\Money; @endphp

<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="{{ route('battery.suppliers') }}" wire:navigate class="text-xs font-bold text-emerald-700 hover:underline">&larr; Back to Battery Suppliers</a>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ $supplier->name }} <span class="text-sm font-mono text-slate-400">{{ $supplier->supplier_id }}</span></h1>
        </div>
        @if((float) $balance['total_due'] > 0)
            <button type="button" wire:click="openPay" class="h-10 px-4 rounded-xl bg-emerald-700 text-white text-sm font-bold cursor-pointer">Pay Supplier Due</button>
        @endif
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border p-4"><p class="text-[11px] font-bold uppercase text-slate-500">Purchased</p><p class="text-lg font-extrabold">{{ Money::formatBd($balance['total_purchased']) }}</p></div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border p-4"><p class="text-[11px] font-bold uppercase text-slate-500">Paid</p><p class="text-lg font-extrabold text-emerald-700">{{ Money::formatBd($balance['total_paid']) }}</p></div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border p-4"><p class="text-[11px] font-bold uppercase text-slate-500">Prev Due</p><p class="text-lg font-extrabold text-amber-600">{{ Money::formatBd($balance['previous_due']) }}</p></div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border p-4"><p class="text-[11px] font-bold uppercase text-slate-500">Purchase Due</p><p class="text-lg font-extrabold text-rose-600">{{ Money::formatBd($balance['purchases_due']) }}</p></div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-rose-300 p-4"><p class="text-[11px] font-bold uppercase text-rose-500">Total Payable</p><p class="text-lg font-extrabold text-rose-600">{{ Money::formatBd($balance['total_due']) }}</p></div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border overflow-hidden">
        <div class="px-4 py-3 border-b font-extrabold text-sm">Battery Purchases ({{ $purchases->count() }})</div>
        <table class="w-full text-xs sm:text-sm text-left">
            <thead class="bg-[#15803d] text-white text-xs uppercase"><tr><th class="px-3 py-2.5">Purchase #</th><th class="px-3 py-2.5">Date</th><th class="px-3 py-2.5 text-right">Subtotal</th><th class="px-3 py-2.5 text-right">Paid</th><th class="px-3 py-2.5 text-right">Due</th><th class="px-3 py-2.5 text-right">Print</th></tr></thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                @forelse($purchases as $p)
                    <tr><td class="px-3 py-2 font-bold">{{ $p->purchase_id }}</td><td class="px-3 py-2">{{ $p->date }}</td><td class="px-3 py-2 text-right">{{ Money::formatBd($p->grand_subtotal) }}</td><td class="px-3 py-2 text-right text-emerald-700">{{ Money::formatBd($p->paid_amount) }}</td><td class="px-3 py-2 text-right text-rose-600 font-bold">{{ Money::formatBd($p->due_amount) }}</td><td class="px-3 py-2 text-right"><a href="{{ route('battery.purchase.invoice.show', $p->id) }}" target="_blank" class="text-emerald-700 font-bold">Print</a></td></tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">No purchases found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-ui.modal :show="$showPayModal" :title="'Pay Battery Supplier — ' . $supplier->name" close-method="closeModal">
        <form wire:submit="submitPay" class="space-y-3">
            <div class="grid grid-cols-2 gap-3">
                <div><label class="block text-xs font-bold mb-1">Paid (৳) *</label><input type="number" step="0.01" wire:model="paid_amount" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-bold"></div>
                <div><label class="block text-xs font-bold mb-1">Discount (৳)</label><input type="number" step="0.01" wire:model="discount_amount" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"></div>
                <div><label class="block text-xs font-bold mb-1">Apply Against</label><select wire:model="collection_type" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"><option value="all">All (FIFO)</option><option value="previous">Prev Due</option><option value="purchase">Purchases</option></select></div>
                <div><label class="block text-xs font-bold mb-1">Date</label><x-ui.date-picker wire:model="date" /></div>
            </div>
            <div class="flex justify-end gap-2"><button type="button" wire:click="closeModal" class="h-10 px-4 rounded-xl border text-sm font-bold">Cancel</button><button type="submit" class="h-10 px-5 rounded-xl bg-emerald-700 text-white text-sm font-bold">Confirm</button></div>
        </form>
    </x-ui.modal>
</div>
