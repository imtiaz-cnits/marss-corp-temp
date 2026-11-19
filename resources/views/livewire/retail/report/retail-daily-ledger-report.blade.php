@php use App\Support\Money; @endphp

<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Retail Daily Income &amp; Expense Ledger</h1>
            <p class="text-xs sm:text-sm text-slate-500">Chronological cash ledger including Sales Collections, Previous Due, Purchase Payments, and Expenses.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <div class="w-36"><x-ui.date-picker wire:model.live="startDate" placeholder="From Date" /></div>
            <div class="w-36"><x-ui.date-picker wire:model.live="endDate" placeholder="To Date" /></div>
            <x-ui.export-buttons table-id="retailLedgerTable" filename="retail-daily-ledger" title="MARSS Retail Daily Ledger" />
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 p-4"><p class="text-xs font-bold uppercase text-slate-500">Opening Balance</p><p class="text-lg font-extrabold mt-1 tabular-nums">{{ Money::formatBd($ledger['opening_balance']) }}</p></div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 p-4"><p class="text-xs font-bold uppercase text-slate-500">Total Inflow</p><p class="text-lg font-extrabold text-emerald-700 mt-1 tabular-nums">{{ Money::formatBd($ledger['total_inflow']) }}</p></div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 p-4"><p class="text-xs font-bold uppercase text-slate-500">Total Outflow</p><p class="text-lg font-extrabold text-rose-600 mt-1 tabular-nums">{{ Money::formatBd($ledger['total_outflow']) }}</p></div>
        <div class="bg-emerald-700 text-white rounded-2xl p-4"><p class="text-xs font-bold uppercase text-emerald-100">Closing Balance</p><p class="text-lg font-extrabold mt-1 tabular-nums">{{ Money::formatBd($ledger['closing_balance']) }}</p></div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table id="retailLedgerTable" class="w-full text-xs sm:text-sm text-left">
                <thead class="bg-[#15803d] text-white text-xs uppercase whitespace-nowrap">
                    <tr><th class="px-3 py-3">Date</th><th class="px-3 py-3">Type</th><th class="px-3 py-3">Reference</th><th class="px-3 py-3">Party / Detail</th><th class="px-3 py-3">Method</th><th class="px-3 py-3 text-right">Inflow</th><th class="px-3 py-3 text-right">Outflow</th><th class="px-3 py-3 text-right">Balance</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    <tr class="bg-emerald-50/60 dark:bg-emerald-950/20 font-bold">
                        <td class="px-3 py-2.5">{{ $startDate }}</td><td class="px-3 py-2.5" colspan="6">Opening Cash Balance Brought Forward</td>
                        <td class="px-3 py-2.5 text-right tabular-nums">{{ Money::formatBd($ledger['opening_balance']) }}</td>
                    </tr>
                    @forelse($ledger['entries'] as $row)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="px-3 py-2 whitespace-nowrap text-slate-500">{{ $row['date'] }}</td>
                            <td class="px-3 py-2 font-semibold">{{ $row['type'] }}</td>
                            <td class="px-3 py-2 font-mono text-xs">{{ $row['reference'] }}</td>
                            <td class="px-3 py-2">{{ $row['party'] }}</td>
                            <td class="px-3 py-2 text-xs">{{ $row['method'] }}</td>
                            <td class="px-3 py-2 text-right font-bold text-emerald-700 tabular-nums">{{ (float) $row['inflow'] > 0 ? Money::formatBd($row['inflow']) : '—' }}</td>
                            <td class="px-3 py-2 text-right font-bold text-rose-600 tabular-nums">{{ (float) $row['outflow'] > 0 ? Money::formatBd($row['outflow']) : '—' }}</td>
                            <td class="px-3 py-2 text-right font-extrabold tabular-nums">{{ Money::formatBd($row['running_balance']) }}</td>
                        </tr>
                    @empty
                        <tr data-empty-row="true"><td colspan="8" class="px-4 py-8 text-center text-slate-500">No ledger transactions in this range.</td></tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-100 dark:bg-slate-800 font-extrabold">
                    <tr>
                        <th colspan="5" class="px-3 py-3 text-right">Totals / Closing Balance:</th>
                        <th class="px-3 py-3 text-right text-emerald-700 tabular-nums">{{ Money::formatBd($ledger['total_inflow']) }}</th>
                        <th class="px-3 py-3 text-right text-rose-600 tabular-nums">{{ Money::formatBd($ledger['total_outflow']) }}</th>
                        <th class="px-3 py-3 text-right tabular-nums">{{ Money::formatBd($ledger['closing_balance']) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
