@php use App\Support\Money; @endphp

<div class="space-y-4">
    {{-- Header with Export Actions (Image 3 1:1) --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <span class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-950/60 text-[#15803d] dark:text-emerald-400 flex items-center justify-center text-sm shadow-2xs">
                <i class="fa-solid fa-book-open"></i>
            </span>
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight m-0">Battery Daily Income &amp; Expense Ledger</h1>
            </div>
        </div>
        <div class="flex flex-wrap items-center justify-start gap-1.5">
            <x-ui.export-buttons table-id="batteryDailyLedgerTable" filename="battery-daily-income-expense-ledger" title="MARSS Battery Daily Income & Expense Ledger" />
        </div>
    </div>

    {{-- Filter Card (Image 3 1:1) --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-3 sm:p-4 shadow-2xs">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-3">
            <div>
                <label class="block text-[11px] font-black uppercase text-slate-500 tracking-wider mb-1.5">START DATE</label>
                <x-ui.date-picker wire:model.live="startDate" placeholder="YYYY-MM-DD" />
            </div>
            <div>
                <label class="block text-[11px] font-black uppercase text-slate-500 tracking-wider mb-1.5">END DATE</label>
                <x-ui.date-picker wire:model.live="endDate" placeholder="YYYY-MM-DD" />
            </div>
            <div>
                <label class="block text-[11px] font-black uppercase text-slate-500 tracking-wider mb-1.5">SHOW</label>
                <select wire:model.live="perPage" class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-200 shadow-2xs">
                    <option value="15">SHOW: 15</option>
                    <option value="25">SHOW: 25</option>
                    <option value="50">SHOW: 50</option>
                    <option value="100">SHOW: 100</option>
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-black uppercase text-slate-500 tracking-wider mb-1.5">QUICK FILTER</label>
                <select wire:model.live="quickFilter" class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-200 shadow-2xs">
                    <option value="today">Today</option>
                    <option value="yesterday">Yesterday</option>
                    <option value="this_week">This Week</option>
                    <option value="this_month">This Month</option>
                    <option value="last_month">Last Month</option>
                    <option value="this_year">This Year</option>
                    <option value="all_time">All Time</option>
                </select>
            </div>
        </div>
    </div>

    {{-- 4 Stat Cards in a row (Image 3 1:1) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        {{-- Card 1: TOTAL INFLOW --}}
        <div class="bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-900/40 rounded-2xl p-3.5 sm:p-4 shadow-2xs min-w-0">
            <span class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 block tracking-wider truncate">TOTAL INFLOW</span>
            <span class="text-base sm:text-lg font-black text-emerald-700 dark:text-emerald-400 mt-0.5 block tabular-nums whitespace-nowrap">{{ Money::formatBd($ledger['total_inflow']) }}</span>
        </div>

        {{-- Card 2: TOTAL OUTFLOW --}}
        <div class="bg-rose-50/70 dark:bg-rose-950/30 border border-rose-100 dark:border-rose-900/40 rounded-2xl p-3.5 sm:p-4 shadow-2xs min-w-0">
            <span class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 block tracking-wider truncate">TOTAL OUTFLOW</span>
            <span class="text-base sm:text-lg font-black text-rose-600 dark:text-rose-400 mt-0.5 block tabular-nums whitespace-nowrap">{{ Money::formatBd($ledger['total_outflow']) }}</span>
        </div>

        {{-- Card 3: NET CASH BALANCE --}}
        <div class="bg-sky-50/70 dark:bg-sky-950/30 border border-sky-100 dark:border-sky-900/40 rounded-2xl p-3.5 sm:p-4 shadow-2xs min-w-0">
            <span class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 block tracking-wider truncate">NET CASH BALANCE</span>
            <span class="text-base sm:text-lg font-black text-sky-700 dark:text-sky-400 mt-0.5 block tabular-nums whitespace-nowrap">{{ Money::formatBd($ledger['closing_balance']) }}</span>
        </div>

        {{-- Card 4: TOTAL TRANSACTIONS --}}
        <div class="bg-slate-50/70 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 rounded-2xl p-3.5 sm:p-4 shadow-2xs min-w-0">
            <span class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 block tracking-wider truncate">TOTAL TRANSACTIONS</span>
            <span class="text-base sm:text-lg font-black text-slate-800 dark:text-white mt-0.5 block whitespace-nowrap">{{ count($ledger['entries']) }} Entries</span>
        </div>
    </div>

    {{-- Table Section (Image 3 1:1) --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-3 sm:p-5 shadow-2xs">
        <div class="pb-3 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <i class="fa-regular fa-calendar-days text-emerald-600"></i>
                <span class="text-sm font-black text-slate-800 dark:text-white">Transactions Ledger Sheet</span>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                Period: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}{{ $startDate !== $endDate ? ' - ' . \Carbon\Carbon::parse($endDate)->format('d M Y') : '' }}
            </span>
        </div>

        <div class="table-responsive unified-ui-border hidden lg:block w-full max-w-full overflow-x-auto rounded-2xl shadow-2xs bg-white dark:bg-slate-900 mb-4">
            <table id="batteryDailyLedgerTable" class="w-full text-xs sm:text-sm text-left border-collapse">
                <thead class="bg-[#15803d] text-white text-[11px] font-bold uppercase tracking-wider whitespace-nowrap">
                    <tr>
                        <th class="px-3.5 py-3 w-12 text-center rounded-tl-2xl">SL</th>
                        <th class="px-3.5 py-3">DATE &amp; TIME</th>
                        <th class="px-3.5 py-3">REF / VOUCHER</th>
                        <th class="px-3.5 py-3">PARTICULARS</th>
                        <th class="px-3.5 py-3">PARTY / CATEGORY</th>
                        <th class="px-3.5 py-3 text-right">CASH IN</th>
                        <th class="px-3.5 py-3 text-right">CASH OUT</th>
                        <th class="px-3.5 py-3 text-right rounded-tr-2xl">RUNNING CASH</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                    @forelse($entries as $idx => $row)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                            <td class="px-3.5 py-2.5 text-center font-semibold text-slate-500">{{ $entries->firstItem() + $idx }}</td>
                            <td class="px-3.5 py-2.5 whitespace-nowrap text-slate-600 dark:text-slate-400">
                                {{ $row['date'] }}
                            </td>
                            <td class="px-3.5 py-2.5 font-bold whitespace-nowrap text-[#15803d]">
                                {{ $row['reference'] }}
                            </td>
                            <td class="px-3.5 py-2.5 font-semibold text-slate-800 dark:text-slate-200">
                                {{ $row['type'] }}
                            </td>
                            <td class="px-3.5 py-2.5 text-slate-700 dark:text-slate-300">
                                {{ $row['party'] }}
                            </td>
                            <td class="px-3.5 py-2.5 text-right font-black text-emerald-700 dark:text-emerald-400 tabular-nums">
                                {{ (float) $row['inflow'] > 0 ? Money::formatBd($row['inflow']) : '—' }}
                            </td>
                            <td class="px-3.5 py-2.5 text-right font-black text-rose-600 dark:text-rose-400 tabular-nums">
                                {{ (float) $row['outflow'] > 0 ? Money::formatBd($row['outflow']) : '—' }}
                            </td>
                            <td class="px-3.5 py-2.5 text-right font-black text-slate-900 dark:text-white tabular-nums">
                                {{ Money::formatBd($row['running_balance']) }}
                            </td>
                        </tr>
                    @empty
                        <tr data-empty-row="true">
                            <td colspan="8" class="px-4 py-12 text-center text-slate-500 font-medium">
                                No transactions found for the selected period.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-50/90 dark:bg-slate-800/80 font-black border-t border-slate-200 dark:border-slate-700">
                    <tr>
                        <td colspan="5" class="px-3.5 py-3 text-right text-xs text-slate-700 dark:text-slate-300 font-bold">Total Summary:</td>
                        <td class="px-3.5 py-3 text-right text-emerald-700 dark:text-emerald-400 font-black tabular-nums">{{ Money::formatBd($ledger['total_inflow']) }}</td>
                        <td class="px-3.5 py-3 text-right text-rose-600 dark:text-rose-400 font-black tabular-nums">{{ Money::formatBd($ledger['total_outflow']) }}</td>
                        <td class="px-3.5 py-3 text-right text-sky-700 dark:text-sky-400 font-black tabular-nums">{{ Money::formatBd($ledger['closing_balance']) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Mobile & Tablet Box Cards (1 col mobile, 2 col tablet) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:hidden gap-3 p-3">
            @forelse($entries as $idx => $row)
                <div class="unified-ui-border rounded-2xl p-3.5 bg-white dark:bg-slate-800/90 shadow-2xs space-y-2 border border-slate-200 dark:border-slate-800">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800 gap-1.5 flex-wrap">
                        <div class="flex items-center gap-1.5">
                            <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs">
                                #{{ $entries->firstItem() + $idx }}
                            </span>
                            <span class="font-bold text-[#15803d] text-xs font-mono">
                                {{ $row['reference'] }}
                            </span>
                        </div>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                            {{ $row['date'] }}
                        </span>
                    </div>

                    <div class="space-y-0.5">
                        <div class="text-xs font-bold text-slate-900 dark:text-white break-words">
                            {{ $row['type'] }}
                        </div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 break-words">
                            {{ $row['party'] }}
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-1.5 pt-1 text-center">
                        <div class="p-1.5 rounded-lg bg-emerald-50/60 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-900/40">
                            <span class="text-[10px] font-bold text-slate-400 block uppercase">Cash In</span>
                            <span class="text-xs font-black text-emerald-700 dark:text-emerald-400 tabular-nums">
                                {{ (float) $row['inflow'] > 0 ? Money::formatBd($row['inflow']) : '—' }}
                            </span>
                        </div>
                        <div class="p-1.5 rounded-lg bg-rose-50/60 dark:bg-rose-950/30 border border-rose-100 dark:border-rose-900/40">
                            <span class="text-[10px] font-bold text-slate-400 block uppercase">Cash Out</span>
                            <span class="text-xs font-black text-rose-600 dark:text-rose-400 tabular-nums">
                                {{ (float) $row['outflow'] > 0 ? Money::formatBd($row['outflow']) : '—' }}
                            </span>
                        </div>
                        <div class="p-1.5 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                            <span class="text-[10px] font-bold text-slate-400 block uppercase">Running</span>
                            <span class="text-xs font-black text-slate-900 dark:text-white tabular-nums">
                                {{ Money::formatBd($row['running_balance']) }}
                            </span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-8 text-center text-slate-400 text-xs">
                    No transactions found for the selected period.
                </div>
            @endforelse
        </div>

        {{-- Mobile Total Summary Banner --}}
        <div class="lg:hidden p-3 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-200 dark:border-slate-800 grid grid-cols-3 gap-2 text-center text-xs">
            <div>
                <span class="text-[10px] font-bold text-slate-400 block uppercase">Total Inflow</span>
                <span class="font-black text-emerald-700 dark:text-emerald-400">{{ Money::formatBd($ledger['total_inflow']) }}</span>
            </div>
            <div>
                <span class="text-[10px] font-bold text-slate-400 block uppercase">Total Outflow</span>
                <span class="font-black text-rose-600 dark:text-rose-400">{{ Money::formatBd($ledger['total_outflow']) }}</span>
            </div>
            <div>
                <span class="text-[10px] font-bold text-slate-400 block uppercase">Closing Cash</span>
                <span class="font-black text-sky-700 dark:text-sky-400">{{ Money::formatBd($ledger['closing_balance']) }}</span>
            </div>
        </div>
        </div>
        <div class="flex flex-col sm:flex-row items-center justify-between pt-4 mt-4 border-t border-slate-200 dark:border-slate-800 gap-3">
                <div class="flex items-center gap-1.5 text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
                    <span>Showing</span>
                    <span class="font-bold text-slate-800 dark:text-slate-100 px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700">{{ $entries->firstItem() ?? 0 }} – {{ $entries->lastItem() ?? 0 }}</span>
                    <span>of</span>
                    <span class="font-bold text-emerald-700 dark:text-emerald-400 px-1.5 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-slate-700">{{ $entries->total() }}</span>
                    <span>transactions</span>
                </div>
                <div class="w-full sm:w-auto flex items-center justify-center sm:justify-end">
                    {{ $entries->links() }}
                </div>
            </div>
        
    </div>
</div>
