@php use App\Support\Money; $t = $summary['totals']; @endphp

<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Battery Income &amp; Expense Report</h1>
        <div class="flex flex-wrap items-center gap-2">
            <div class="w-36"><x-ui.date-picker wire:model.live="startDate" placeholder="From" /></div>
            <div class="w-36"><x-ui.date-picker wire:model.live="endDate" placeholder="To" /></div>
            <x-ui.export-buttons table-id="batIncExpTable" filename="battery-income-expense" title="MARSS Battery Income & Expense" />
        </div>
    </div>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border overflow-hidden">
        <table id="batIncExpTable" class="w-full text-xs sm:text-sm text-left">
            <thead class="bg-[#15803d] text-white text-xs uppercase">
                <tr><th class="px-3 py-3">Date</th>@if($canViewProfit)<th class="px-3 py-3 text-right">Cost</th>@endif<th class="px-3 py-3 text-right">Sales</th><th class="px-3 py-3 text-right">Paid</th><th class="px-3 py-3 text-right">Expense</th>@if($canViewProfit)<th class="px-3 py-3 text-right">Net Profit</th>@endif</tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                @forelse($summary['rows'] as $r)
                    <tr><td class="px-3 py-2 font-bold">{{ $r['date'] }}</td>@if($canViewProfit)<td class="px-3 py-2 text-right">{{ Money::formatBd($r['cost']) }}</td>@endif<td class="px-3 py-2 text-right font-bold">{{ Money::formatBd($r['sales']) }}</td><td class="px-3 py-2 text-right text-emerald-700">{{ Money::formatBd($r['paid']) }}</td><td class="px-3 py-2 text-right text-rose-600">{{ Money::formatBd($r['expense']) }}</td>@if($canViewProfit)<td class="px-3 py-2 text-right font-extrabold text-teal-700">{{ Money::formatBd($r['net_profit']) }}</td>@endif</tr>
                @empty
                    <tr data-empty-row="true"><td colspan="6" class="px-4 py-8 text-center text-slate-500">No records in selected range.</td></tr>
                @endforelse
            </tbody>
            <tfoot class="bg-slate-100 dark:bg-slate-800 font-extrabold">
                <tr><th class="px-3 py-3">Totals</th>@if($canViewProfit)<th class="px-3 py-3 text-right">{{ Money::formatBd($t['cost']) }}</th>@endif<th class="px-3 py-3 text-right">{{ Money::formatBd($t['sales']) }}</th><th class="px-3 py-3 text-right text-emerald-700">{{ Money::formatBd($t['paid']) }}</th><th class="px-3 py-3 text-right text-rose-600">{{ Money::formatBd($t['expense']) }}</th>@if($canViewProfit)<th class="px-3 py-3 text-right text-teal-700">{{ Money::formatBd($t['net_profit']) }}</th>@endif</tr>
            </tfoot>
        </table>
    </div>
</div>
