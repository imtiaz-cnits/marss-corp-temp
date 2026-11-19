@php use App\Support\Money; $t = $report['totals']; @endphp

<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Battery Sales Report</h1>
        <div class="flex flex-wrap items-center gap-2">
            <div class="w-36"><x-ui.date-picker wire:model.live="startDate" placeholder="From" /></div>
            <div class="w-36"><x-ui.date-picker wire:model.live="endDate" placeholder="To" /></div>
            <x-ui.export-buttons table-id="batSalesRepTable" filename="battery-sales-report" title="MARSS Battery Sales Report" />
        </div>
    </div>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border overflow-hidden">
        <table id="batSalesRepTable" class="w-full text-xs sm:text-sm text-left">
            <thead class="bg-[#15803d] text-white text-xs uppercase">
                <tr><th class="px-3 py-3">Date</th><th class="px-3 py-3">Invoice</th><th class="px-3 py-3">Customer</th>@if($canViewProfit)<th class="px-3 py-3 text-right">Cost</th>@endif<th class="px-3 py-3 text-right">Sales</th><th class="px-3 py-3 text-right">Paid</th><th class="px-3 py-3 text-right">Due</th>@if($canViewProfit)<th class="px-3 py-3 text-right">Profit</th>@endif</tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                @forelse($report['rows'] as $r)
                    <tr>
                        <td class="px-3 py-2">{{ $r['date'] }}</td>
                        <td class="px-3 py-2 font-bold"><a href="{{ route('battery.invoice.show', $r['id']) }}" target="_blank" class="text-emerald-700 hover:underline">{{ $r['order_no'] }}</a></td>
                        <td class="px-3 py-2">{{ $r['customer_name'] }}</td>
                        @if($canViewProfit)<td class="px-3 py-2 text-right">{{ Money::formatBd($r['total_cost']) }}</td>@endif
                        <td class="px-3 py-2 text-right font-bold">{{ Money::formatBd($r['selling_price']) }}</td>
                        <td class="px-3 py-2 text-right text-emerald-700 font-bold">{{ Money::formatBd($r['paid_amount']) }}</td>
                        <td class="px-3 py-2 text-right text-rose-600 font-bold">{{ Money::formatBd($r['due_amount']) }}</td>
                        @if($canViewProfit)<td class="px-3 py-2 text-right font-extrabold text-teal-700">{{ Money::formatBd($r['profit']) }}</td>@endif
                    </tr>
                @empty
                    <tr data-empty-row="true"><td colspan="8" class="px-4 py-8 text-center text-slate-500">No Battery sales in range.</td></tr>
                @endforelse
            </tbody>
            <tfoot class="bg-slate-100 dark:bg-slate-800 font-extrabold">
                <tr><th colspan="3" class="px-3 py-3 text-right">Totals:</th>@if($canViewProfit)<th class="px-3 py-3 text-right">{{ Money::formatBd($t['total_cost']) }}</th>@endif<th class="px-3 py-3 text-right">{{ Money::formatBd($t['selling_price']) }}</th><th class="px-3 py-3 text-right text-emerald-700">{{ Money::formatBd($t['paid_amount']) }}</th><th class="px-3 py-3 text-right text-rose-600">{{ Money::formatBd($t['due_amount']) }}</th>@if($canViewProfit)<th class="px-3 py-3 text-right text-teal-700">{{ Money::formatBd($t['profit']) }}</th>@endif</tr>
            </tfoot>
        </table>
    </div>
</div>
