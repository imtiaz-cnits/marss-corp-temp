@php use App\Support\Money; @endphp

<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Retail Low &amp; Out-of-Stock Report</h1>
            <p class="text-xs sm:text-sm text-slate-500">Products with stock below threshold requiring reorder.</p>
        </div>
        <div class="flex items-center gap-2">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search product..." class="h-[38px] px-3 w-52 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
            <select wire:model.live="threshold" class="h-[38px] px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold">
                <option value="1">Out of Stock (&le; 0)</option>
                <option value="5">Below 5</option>
                <option value="10">Below 10</option>
                <option value="25">Below 25</option>
            </select>
            <x-ui.export-buttons table-id="retailLowStockTable" filename="retail-low-stock" title="MARSS Retail Low Stock Report" />
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table id="retailLowStockTable" class="w-full text-xs sm:text-sm text-left">
                <thead class="bg-[#15803d] text-white text-xs uppercase whitespace-nowrap">
                    <tr><th class="px-3 py-3">#</th><th class="px-3 py-3">Product</th><th class="px-3 py-3">Brand</th><th class="px-3 py-3">Category</th><th class="px-3 py-3 text-right">Cost</th><th class="px-3 py-3 text-center">Current Stock</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($products as $idx => $p)
                        @php $q = (int) round((float) ($p->quantity ?? 0)); @endphp
                        <tr>
                            <td class="px-3 py-2 text-slate-500">{{ $idx + 1 }}</td>
                            <td class="px-3 py-2 font-bold">{{ $p->product_name }}</td>
                            <td class="px-3 py-2">{{ $p->brand?->name ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $p->category?->category_name ?? '—' }}</td>
                            <td class="px-3 py-2 text-right tabular-nums">{{ Money::formatBd($p->cost_price) }}</td>
                            <td class="px-3 py-2 text-center"><span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $q <= 0 ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">{{ $q }} {{ $p->unit?->unit_name }}</span></td>
                        </tr>
                    @empty
                        <tr data-empty-row="true"><td colspan="6" class="px-4 py-8 text-center text-slate-500">All products exceed stock threshold.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
