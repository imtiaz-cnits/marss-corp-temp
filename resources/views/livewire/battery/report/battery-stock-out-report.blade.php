@php use App\Support\Money; @endphp

<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Battery Low Stock Report</h1>
        <div class="flex items-center gap-2">
            <select wire:model.live="threshold" class="h-[38px] px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold"><option value="1">Out of Stock (&le; 0)</option><option value="5">Below 5</option><option value="10">Below 10</option></select>
            <x-ui.export-buttons table-id="batLowStockTable" filename="battery-low-stock" title="MARSS Battery Low Stock Report" />
        </div>
    </div>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border overflow-hidden">
        <table id="batLowStockTable" class="w-full text-xs sm:text-sm text-left">
            <thead class="bg-[#15803d] text-white text-xs uppercase"><tr><th class="px-3 py-3">#</th><th class="px-3 py-3">Product</th><th class="px-3 py-3">Brand</th><th class="px-3 py-3 text-right">Cost</th><th class="px-3 py-3 text-center">Stock</th></tr></thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                @forelse($products as $idx => $p)
                    <tr><td class="px-3 py-2">{{ $products->firstItem() + $idx }}</td><td class="px-3 py-2 font-bold">{{ $p->product_name }}</td><td class="px-3 py-2">{{ $p->brand?->name ?? '—' }}</td><td class="px-3 py-2 text-right">{{ Money::formatBd($p->cost_price) }}</td><td class="px-3 py-2 text-center font-bold text-rose-600">{{ (int) round((float) ($p->quantity ?? 0)) }}</td></tr>
                @empty
                    <tr data-empty-row="true"><td colspan="5" class="px-4 py-8 text-center text-slate-500">All Battery products exceed threshold.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-3 border-t border-slate-200 dark:border-slate-800">{{ $products->links() }}</div>
    </div>
</div>
