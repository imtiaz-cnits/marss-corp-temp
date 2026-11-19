@php use App\Support\Money; @endphp

<div class="space-y-4">
    <div>
        <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Retail Stock Overview</h1>
        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Real-time Retail inventory valuation, low-stock buckets, and audited manual stock adjustments.</p>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 p-4">
            <p class="text-xs font-bold uppercase text-slate-500">Total SKUs</p>
            <p class="text-2xl font-extrabold mt-1">{{ $summary['total_products'] }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 p-4">
            <p class="text-xs font-bold uppercase text-slate-500">Stock Valuation (Cost)</p>
            <p class="text-xl font-extrabold text-emerald-700 dark:text-emerald-400 mt-1 tabular-nums">{{ Money::formatBd($summary['cost_value']) }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 p-4">
            <p class="text-xs font-bold uppercase text-slate-500">Low Stock (1–9)</p>
            <p class="text-2xl font-extrabold text-amber-600 mt-1">{{ $summary['low_stock'] }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 p-4">
            <p class="text-xs font-bold uppercase text-slate-500">Out of Stock (&le; 0)</p>
            <p class="text-2xl font-extrabold text-rose-600 mt-1">{{ $summary['out_of_stock'] }}</p>
        </div>
    </div>

    <div class="flex flex-wrap gap-2">
        @foreach(['all' => 'All Products', 'out' => 'Out of Stock', 'low' => 'Low Stock (<10)', 'available' => 'Healthy (>=10)'] as $k => $lbl)
            <button type="button" wire:click="setTab('{{ $k }}')" class="h-9 px-4 rounded-xl text-xs font-bold cursor-pointer {{ $tab === $k ? 'bg-emerald-700 text-white' : 'bg-white dark:bg-slate-900 border-[1.5px] border-slate-300 dark:border-slate-700' }}">{{ $lbl }}</button>
        @endforeach
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 shadow-xs overflow-hidden">
        <div class="p-3.5 border-b border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-2">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Filter products..." class="h-9 px-3 w-64 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
            <x-ui.export-buttons table-id="retailStockTable" filename="retail-stock-overview" title="MARSS Retail Stock Overview" />
        </div>
        <div class="overflow-x-auto">
            <table id="retailStockTable" data-export-skip-last="true" class="w-full text-xs sm:text-sm text-left">
                <thead class="bg-[#15803d] text-white text-xs uppercase whitespace-nowrap">
                    <tr><th class="px-3 py-3">Product</th><th class="px-3 py-3">Brand</th><th class="px-3 py-3 text-right">Cost</th><th class="px-3 py-3 text-center">Qty</th><th class="px-3 py-3 text-right">Stock Value</th><th class="px-3 py-3 text-right">Adjust</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($products as $p)
                        @php $q = (int) round((float) ($p->quantity ?? 0)); @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="px-3 py-2.5 font-bold">{{ $p->product_name }}</td>
                            <td class="px-3 py-2.5 text-slate-500">{{ $p->brand?->name ?? '—' }}</td>
                            <td class="px-3 py-2.5 text-right tabular-nums">{{ Money::formatBd($p->cost_price) }}</td>
                            <td class="px-3 py-2.5 text-center">
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $q <= 0 ? 'bg-red-100 text-red-700' : ($q < 10 ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">{{ $q }}</span>
                            </td>
                            <td class="px-3 py-2.5 text-right font-bold tabular-nums">{{ Money::formatBd(Money::mul(max(0, $q), $p->cost_price ?? 0)) }}</td>
                            <td class="px-3 py-2.5 text-right">
                                <button type="button" wire:click="openAdjust({{ $p->id }})" class="px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 text-xs font-bold cursor-pointer">Adjust Stock</button>
                            </td>
                        </tr>
                    @empty
                        <tr data-empty-row="true"><td colspan="6" class="px-4 py-8 text-center text-slate-500">No products in this bucket.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-t border-slate-200 dark:border-slate-800">{{ $products->links() }}</div>
    </div>

    <x-ui.modal :show="$showAdjustModal" :title="'Manual Stock Adjustment — ' . $adjustProductName" close-method="closeModal">
        <form wire:submit="submitAdjust" class="space-y-3">
            <div>
                <label class="block text-xs font-bold mb-1">New Physical Stock Quantity *</label>
                <input type="number" wire:model="adjustQuantity" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-bold">
            </div>
            <div>
                <label class="block text-xs font-bold mb-1">Adjustment Reason</label>
                <input type="text" wire:model="adjustReason" placeholder="Physical count reconciliation..." class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" wire:click="closeModal" class="h-10 px-4 rounded-xl border border-slate-300 text-sm font-bold">Cancel</button>
                <button type="submit" class="h-10 px-5 rounded-xl bg-emerald-700 text-white text-sm font-bold cursor-pointer">Save Adjustment</button>
            </div>
        </form>
    </x-ui.modal>
</div>
