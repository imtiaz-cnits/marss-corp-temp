@php use App\Support\Money; @endphp

<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Best-Selling Retail Products</h1>
            <p class="text-xs sm:text-sm text-slate-500">Net units sold (returns subtracted) ranked by sales volume &amp; revenue.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <div class="w-36"><x-ui.date-picker wire:model.live="startDate" placeholder="From" /></div>
            <div class="w-36"><x-ui.date-picker wire:model.live="endDate" placeholder="To" /></div>
            <x-ui.export-buttons table-id="bestSellingTable" filename="retail-best-selling" title="MARSS Best-Selling Retail Products" />
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table id="bestSellingTable" class="w-full text-xs sm:text-sm text-left">
                <thead class="bg-[#15803d] text-white text-xs uppercase whitespace-nowrap">
                    <tr>
                        <th class="px-3 py-3">#</th><th class="px-3 py-3">Product</th><th class="px-3 py-3">Brand</th><th class="px-3 py-3">Category</th><th class="px-3 py-3 text-center">Net Qty Sold</th>
                        @if($canViewProfit)<th class="px-3 py-3 text-right">Total Cost</th>@endif
                        <th class="px-3 py-3 text-right">Total Revenue</th>
                        @if($canViewProfit)<th class="px-3 py-3 text-right">Gross Profit</th>@endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($rows as $idx => $r)
                        @php $profit = Money::sub($r->total_sales, $r->total_cost); @endphp
                        <tr>
                            <td class="px-3 py-2 font-bold text-slate-500">{{ $idx + 1 }}</td>
                            <td class="px-3 py-2 font-bold">{{ $r->product_name }}</td>
                            <td class="px-3 py-2">{{ $r->brand_name ?: '—' }}</td>
                            <td class="px-3 py-2">{{ $r->category_name ?: '—' }}</td>
                            <td class="px-3 py-2 text-center"><span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 font-bold">{{ (int) $r->total_quantity }}</span></td>
                            @if($canViewProfit)<td class="px-3 py-2 text-right tabular-nums">{{ Money::formatBd($r->total_cost) }}</td>@endif
                            <td class="px-3 py-2 text-right font-bold tabular-nums">{{ Money::formatBd($r->total_sales) }}</td>
                            @if($canViewProfit)<td class="px-3 py-2 text-right font-extrabold text-emerald-700 tabular-nums">{{ Money::formatBd($profit) }}</td>@endif
                        </tr>
                    @empty
                        <tr data-empty-row="true"><td colspan="8" class="px-4 py-8 text-center text-slate-500">No sales in selected date range.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
