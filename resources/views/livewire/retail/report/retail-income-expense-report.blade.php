@php use App\Support\Money; $t = $summary['totals']; @endphp

<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Retail Income &amp; Expense Summary</h1>
            <p class="text-xs sm:text-sm text-slate-500">Daily P&amp;L comparison of sales, COGS, discounts, returns, and operating expenses.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <div class="w-36"><x-ui.date-picker wire:model.live="startDate" placeholder="From" /></div>
            <div class="w-36"><x-ui.date-picker wire:model.live="endDate" placeholder="To" /></div>
            <x-ui.export-buttons table-id="retailIncExpTable" filename="retail-income-expense" title="MARSS Retail Income & Expense" />
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table id="retailIncExpTable" class="w-full text-xs sm:text-sm text-left">
                <thead class="bg-[#15803d] text-white text-xs uppercase whitespace-nowrap">
                    <tr>
                        <th class="px-3 py-3">Date</th>
                        @if($canViewProfit)<th class="px-3 py-3 text-right">Cost</th>@endif
                        <th class="px-3 py-3 text-right">Sales</th><th class="px-3 py-3 text-right">Paid</th><th class="px-3 py-3 text-right">Disc</th><th class="px-3 py-3 text-right">Due</th><th class="px-3 py-3 text-right">Return</th><th class="px-3 py-3 text-right">Expense</th>
                        @if($canViewProfit)<th class="px-3 py-3 text-right">Net Profit</th>@endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($rows as $r)
                        <tr>
                            <td class="px-3 py-2 font-bold">{{ $r['date'] }}</td>
                            @if($canViewProfit)<td class="px-3 py-2 text-right tabular-nums">{{ Money::formatBd($r['cost']) }}</td>@endif
                            <td class="px-3 py-2 text-right font-bold tabular-nums">{{ Money::formatBd($r['sales']) }}</td>
                            <td class="px-3 py-2 text-right text-emerald-700 tabular-nums">{{ Money::formatBd($r['paid']) }}</td>
                            <td class="px-3 py-2 text-right tabular-nums">{{ Money::formatBd($r['discount']) }}</td>
                            <td class="px-3 py-2 text-right text-amber-600 tabular-nums">{{ Money::formatBd($r['due']) }}</td>
                            <td class="px-3 py-2 text-right tabular-nums">{{ Money::formatBd($r['return']) }}</td>
                            <td class="px-3 py-2 text-right text-rose-600 font-bold tabular-nums">{{ Money::formatBd($r['expense']) }}</td>
                            @if($canViewProfit)<td class="px-3 py-2 text-right font-extrabold text-teal-700 tabular-nums">{{ Money::formatBd($r['net_profit']) }}</td>@endif
                        </tr>
                    @empty
                        <tr data-empty-row="true"><td colspan="9" class="px-4 py-8 text-center text-slate-500">No financial records in range.</td></tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-100 dark:bg-slate-800 font-extrabold">
                    <tr>
                        <th class="px-3 py-3">Totals:</th>
                        @if($canViewProfit)<th class="px-3 py-3 text-right tabular-nums">{{ Money::formatBd($t['cost']) }}</th>@endif
                        <th class="px-3 py-3 text-right tabular-nums">{{ Money::formatBd($t['sales']) }}</th>
                        <th class="px-3 py-3 text-right text-emerald-700 tabular-nums">{{ Money::formatBd($t['paid']) }}</th>
                        <th class="px-3 py-3 text-right tabular-nums">{{ Money::formatBd($t['discount']) }}</th>
                        <th class="px-3 py-3 text-right tabular-nums">{{ Money::formatBd($t['due']) }}</th>
                        <th class="px-3 py-3 text-right tabular-nums">{{ Money::formatBd($t['return']) }}</th>
                        <th class="px-3 py-3 text-right text-rose-600 tabular-nums">{{ Money::formatBd($t['expense']) }}</th>
                        @if($canViewProfit)<th class="px-3 py-3 text-right text-teal-700 tabular-nums">{{ Money::formatBd($t['net_profit']) }}</th>@endif
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="p-3 border-t border-slate-200 dark:border-slate-800">{{ $rows->links() }}</div>
    </div>
</div>
