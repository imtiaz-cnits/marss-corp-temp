@php use App\Support\Money; @endphp

<div class="space-y-4">
    {{-- Header with Export Actions (Image 3 1:1) --}}
    {{-- Header with Export Actions (Image 3 1:1) --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 no-print">
        <div class="flex items-center gap-2.5">
            <span class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-950/60 text-[#15803d] dark:text-emerald-400 flex items-center justify-center text-sm shadow-2xs">
                <i class="fa-solid fa-file-invoice-dollar"></i>
            </span>
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight m-0">Retail Daily Receipt &amp; Payment</h1>
            </div>
        </div>
        <div class="flex flex-wrap items-center justify-start gap-1.5">
            <x-ui.export-buttons table-id="retailReceiptStmtTable" filename="retail-daily-receipt-payment" title="MARSS Retail Daily Receipt & Payment Statement" />
        </div>
    </div>

    {{-- Filter Card (Image 3 1:1) --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-3 sm:p-4 shadow-2xs no-print">
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 sm:gap-3">
            <div>
                <label class="block text-[11px] font-black uppercase text-slate-500 tracking-wider mb-1.5">START DATE</label>
                <x-ui.date-picker wire:model.live="startDate" placeholder="YYYY-MM-DD" />
            </div>
            <div>
                <label class="block text-[11px] font-black uppercase text-slate-500 tracking-wider mb-1.5">END DATE</label>
                <x-ui.date-picker wire:model.live="endDate" placeholder="YYYY-MM-DD" />
            </div>
            <div class="col-span-2 sm:col-span-1">
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
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 no-print">
        <div class="bg-sky-50/70 dark:bg-sky-950/30 border border-sky-100 dark:border-sky-900/40 rounded-2xl p-3.5 sm:p-4 shadow-2xs min-w-0">
            <span class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 block tracking-wider truncate">OPENING CASH</span>
            <span class="text-base sm:text-lg font-black text-sky-700 dark:text-sky-400 mt-0.5 block tabular-nums whitespace-nowrap">{{ Money::formatBd($stmt['opening_balance']) }}</span>
        </div>

        <div class="bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-900/40 rounded-2xl p-3.5 sm:p-4 shadow-2xs min-w-0">
            <span class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 block tracking-wider truncate">TOTAL RECEIPTS</span>
            <span class="text-base sm:text-lg font-black text-emerald-700 dark:text-emerald-400 mt-0.5 block tabular-nums whitespace-nowrap">{{ Money::formatBd($stmt['total_receipts']) }}</span>
        </div>

        <div class="bg-rose-50/70 dark:bg-rose-950/30 border border-rose-100 dark:border-rose-900/40 rounded-2xl p-3.5 sm:p-4 shadow-2xs min-w-0">
            <span class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 block tracking-wider truncate">TOTAL PAYMENTS</span>
            <span class="text-base sm:text-lg font-black text-rose-600 dark:text-rose-400 mt-0.5 block tabular-nums whitespace-nowrap">{{ Money::formatBd($stmt['total_payments']) }}</span>
        </div>

        <div class="bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-900/40 rounded-2xl p-3.5 sm:p-4 shadow-2xs min-w-0">
            <span class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 block tracking-wider truncate">CLOSING CASH</span>
            <span class="text-base sm:text-lg font-black text-emerald-700 dark:text-emerald-400 mt-0.5 block tabular-nums whitespace-nowrap">{{ Money::formatBd($stmt['closing_balance']) }}</span>
        </div>
    </div>

    {{-- Statement Section (Image 3 1:1) --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 sm:p-6 shadow-2xs space-y-6">
        <div class="border-b border-slate-200 dark:border-slate-800 pb-3 flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <i class="fa-regular fa-calendar-days text-emerald-600"></i>
                <span class="text-sm font-black text-slate-800 dark:text-white">Daily Receipt &amp; Payment Statement</span>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                Period: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}{{ $startDate !== $endDate ? ' - ' . \Carbon\Carbon::parse($endDate)->format('d M Y') : '' }}
            </span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Receipts Side --}}
            <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-2xs">
                <div class="px-4 py-2.5 bg-[#15803d] text-white font-black text-xs uppercase tracking-wider flex items-center justify-between">
                    <span>RECEIPTS (Cash Inflow)</span>
                    <i class="fa-solid fa-arrow-down"></i>
                </div>
                {{-- Desktop Table --}}
                <div class="hidden sm:block overflow-x-auto">
                    <table id="retailReceiptStmtTable" class="w-full text-xs sm:text-sm">
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                            <tr class="bg-emerald-50/40 dark:bg-emerald-950/20 font-bold">
                                <td class="px-4 py-3 text-slate-800 dark:text-slate-200">Opening Cash Balance</td>
                                <td class="px-4 py-3 text-right tabular-nums text-slate-900 dark:text-white">{{ Money::formatBd($stmt['opening_balance']) }}</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-300">Sales &amp; Invoice Collections</td>
                                <td class="px-4 py-3 text-right font-black text-emerald-700 dark:text-emerald-400 tabular-nums">{{ Money::formatBd($stmt['sales_collection']) }}</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-300">Previous Due Collections</td>
                                <td class="px-4 py-3 text-right font-black text-emerald-700 dark:text-emerald-400 tabular-nums">{{ Money::formatBd($stmt['previous_due_collection']) }}</td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-slate-50/90 dark:bg-slate-800/80 font-black border-t border-slate-200 dark:border-slate-700">
                            <tr>
                                <td class="px-4 py-3 text-slate-900 dark:text-white">Total Available Cash:</td>
                                <td class="px-4 py-3 text-right text-emerald-700 dark:text-emerald-400 tabular-nums">{{ Money::formatBd(Money::add($stmt['opening_balance'], $stmt['total_receipts'])) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                {{-- Mobile / Tablet Box Cards --}}
                <div class="sm:hidden p-3 space-y-2.5 bg-slate-50/50 dark:bg-slate-900/50">
                    <div class="rounded-xl p-3 bg-white dark:bg-slate-800 shadow-2xs border border-slate-200 dark:border-slate-700/60 flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Opening Cash Balance</span>
                        <span class="text-xs font-black text-slate-900 dark:text-white tabular-nums">{{ Money::formatBd($stmt['opening_balance']) }}</span>
                    </div>
                    <div class="rounded-xl p-3 bg-white dark:bg-slate-800 shadow-2xs border border-slate-200 dark:border-slate-700/60 flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-700 dark:text-slate-300">Sales &amp; Collections</span>
                        <span class="text-xs font-black text-emerald-700 dark:text-emerald-400 tabular-nums">{{ Money::formatBd($stmt['sales_collection']) }}</span>
                    </div>
                    <div class="rounded-xl p-3 bg-white dark:bg-slate-800 shadow-2xs border border-slate-200 dark:border-slate-700/60 flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-700 dark:text-slate-300">Previous Due Collections</span>
                        <span class="text-xs font-black text-emerald-700 dark:text-emerald-400 tabular-nums">{{ Money::formatBd($stmt['previous_due_collection']) }}</span>
                    </div>
                    <div class="rounded-xl p-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 flex items-center justify-between font-black text-xs">
                        <span class="text-emerald-900 dark:text-emerald-200">Total Available Cash:</span>
                        <span class="text-emerald-700 dark:text-emerald-300 tabular-nums">{{ Money::formatBd(Money::add($stmt['opening_balance'], $stmt['total_receipts'])) }}</span>
                    </div>
                </div>
            </div>

            {{-- Payments Side --}}
            <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-2xs">
                <div class="px-4 py-2.5 bg-rose-700 text-white font-black text-xs uppercase tracking-wider flex items-center justify-between">
                    <span>PAYMENTS (Cash Outflow)</span>
                    <i class="fa-solid fa-arrow-up"></i>
                </div>
                {{-- Desktop Table --}}
                <div class="hidden sm:block overflow-x-auto">
                    <table class="w-full text-xs sm:text-sm">
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                            @foreach($stmt['supplier_payments'] as $suppName => $amt)
                                <tr>
                                    <td class="px-4 py-2.5 text-slate-700 dark:text-slate-300">Supplier Paid: <strong>{{ $suppName }}</strong></td>
                                    <td class="px-4 py-2.5 text-right font-bold text-rose-600 dark:text-rose-400 tabular-nums">{{ Money::formatBd($amt) }}</td>
                                </tr>
                            @endforeach
                            @foreach($stmt['expenses'] as $expType => $amt)
                                <tr>
                                    <td class="px-4 py-2.5 text-slate-700 dark:text-slate-300">Expense: <strong>{{ $expType }}</strong></td>
                                    <td class="px-4 py-2.5 text-right font-bold text-rose-600 dark:text-rose-400 tabular-nums">{{ Money::formatBd($amt) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-slate-50/90 dark:bg-slate-800/80 font-black border-t border-slate-200 dark:border-slate-700">
                            <tr>
                                <td class="px-4 py-3 text-slate-900 dark:text-white">Total Payments:</td>
                                <td class="px-4 py-3 text-right text-rose-600 dark:text-rose-400 tabular-nums">{{ Money::formatBd($stmt['total_payments']) }}</td>
                            </tr>
                            <tr class="bg-emerald-50/60 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300">
                                <td class="px-4 py-3 font-black">Closing Cash Balance:</td>
                                <td class="px-4 py-3 text-right font-black tabular-nums">{{ Money::formatBd($stmt['closing_balance']) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                {{-- Mobile / Tablet Box Cards --}}
                <div class="sm:hidden p-3 space-y-2.5 bg-slate-50/50 dark:bg-slate-900/50">
                    @forelse($stmt['supplier_payments'] as $suppName => $amt)
                        <div class="rounded-xl p-3 bg-white dark:bg-slate-800 shadow-2xs border border-slate-200 dark:border-slate-700/60 flex items-center justify-between">
                            <span class="text-xs text-slate-700 dark:text-slate-300 truncate me-2">Supplier: <strong>{{ $suppName }}</strong></span>
                            <span class="text-xs font-bold text-rose-600 dark:text-rose-400 tabular-nums shrink-0">{{ Money::formatBd($amt) }}</span>
                        </div>
                    @empty
                    @endforelse
                    @forelse($stmt['expenses'] as $expType => $amt)
                        <div class="rounded-xl p-3 bg-white dark:bg-slate-800 shadow-2xs border border-slate-200 dark:border-slate-700/60 flex items-center justify-between">
                            <span class="text-xs text-slate-700 dark:text-slate-300 truncate me-2">Expense: <strong>{{ $expType }}</strong></span>
                            <span class="text-xs font-bold text-rose-600 dark:text-rose-400 tabular-nums shrink-0">{{ Money::formatBd($amt) }}</span>
                        </div>
                    @empty
                    @endforelse
                    @if(empty($stmt['supplier_payments']) && empty($stmt['expenses']))
                        <div class="p-4 text-center text-xs text-slate-400">No payment records found.</div>
                    @endif
                    <div class="rounded-xl p-3 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 flex items-center justify-between font-black text-xs">
                        <span class="text-rose-900 dark:text-rose-200">Total Payments:</span>
                        <span class="text-rose-600 dark:text-rose-400 tabular-nums">{{ Money::formatBd($stmt['total_payments']) }}</span>
                    </div>
                    <div class="rounded-xl p-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 flex items-center justify-between font-black text-xs">
                        <span class="text-emerald-900 dark:text-emerald-200">Closing Cash Balance:</span>
                        <span class="text-emerald-700 dark:text-emerald-400 tabular-nums">{{ Money::formatBd($stmt['closing_balance']) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
