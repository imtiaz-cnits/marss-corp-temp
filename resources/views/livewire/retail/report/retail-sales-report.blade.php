@php
    use App\Support\Money;
    $t = $report['totals'];
@endphp

<div class="space-y-4">
    {{-- Header with Export Actions (Image 3 1:1) --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <span class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-950/60 text-[#15803d] dark:text-emerald-400 flex items-center justify-center text-sm shadow-2xs">
                <i class="fa-solid fa-chart-line"></i>
            </span>
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight m-0">Retail Sales Report</h1>
            </div>
        </div>
        <div class="flex flex-wrap items-center justify-start gap-1.5">
            <x-ui.export-buttons table-id="salesReportTable" filename="retail-sales-report" title="MARSS Retail Sales Report" />
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
        <div class="bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-900/40 rounded-2xl p-3.5 sm:p-4 shadow-2xs min-w-0">
            <span class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 block tracking-wider truncate">TOTAL SALES</span>
            <span class="text-base sm:text-lg font-black text-emerald-700 dark:text-emerald-400 mt-0.5 block tabular-nums whitespace-nowrap">{{ Money::formatBd($t['selling_price']) }}</span>
        </div>

        <div class="bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-900/40 rounded-2xl p-3.5 sm:p-4 shadow-2xs min-w-0">
            <span class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 block tracking-wider truncate">TOTAL PAID</span>
            <span class="text-base sm:text-lg font-black text-emerald-700 dark:text-emerald-400 mt-0.5 block tabular-nums whitespace-nowrap">{{ Money::formatBd($t['paid_amount']) }}</span>
        </div>

        <div class="bg-rose-50/70 dark:bg-rose-950/30 border border-rose-100 dark:border-rose-900/40 rounded-2xl p-3.5 sm:p-4 shadow-2xs min-w-0">
            <span class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 block tracking-wider truncate">TOTAL DUE</span>
            <span class="text-base sm:text-lg font-black text-rose-600 dark:text-rose-400 mt-0.5 block tabular-nums whitespace-nowrap">{{ Money::formatBd($t['due_amount']) }}</span>
        </div>

        <div class="bg-slate-50/70 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 rounded-2xl p-3.5 sm:p-4 shadow-2xs min-w-0">
            <span class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 block tracking-wider truncate">TOTAL ORDERS</span>
            <span class="text-base sm:text-lg font-black text-slate-800 dark:text-white mt-0.5 block whitespace-nowrap">{{ count($report['rows']) }} Entries</span>
        </div>
    </div>

    {{-- Table Section (Image 3 1:1) --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-3 sm:p-5 shadow-2xs">
        <div class="pb-3 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <i class="fa-regular fa-calendar-days text-emerald-600"></i>
                <span class="text-sm font-black text-slate-800 dark:text-white">Sales Ledger Sheet</span>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                Period: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}{{ $startDate !== $endDate ? ' - ' . \Carbon\Carbon::parse($endDate)->format('d M Y') : '' }}
            </span>
        </div>

        <div class="table-responsive unified-ui-border hidden lg:block w-full max-w-full overflow-x-auto rounded-2xl shadow-2xs bg-white dark:bg-slate-900 mb-4">
            <table id="salesReportTable" class="w-full text-xs sm:text-sm text-left border-collapse">
                <thead class="bg-[#15803d] text-white text-[11px] font-bold uppercase tracking-wider whitespace-nowrap">
                    <tr>
                        <th class="px-3 py-3 text-center w-12 rounded-tl-2xl">SL</th>
                        <th class="px-3 py-3">DATE</th>
                        <th class="px-3 py-3">INVOICE #</th>
                        <th class="px-3 py-3">CUSTOMER</th>
                        @if($canViewProfit)<th class="px-3 py-3 text-right">COST</th>@endif
                        <th class="px-3 py-3 text-right">SALES</th>
                        <th class="px-3 py-3 text-right">DISC</th>
                        <th class="px-3 py-3 text-right">RETURN</th>
                        <th class="px-3 py-3 text-right">PAID</th>
                        <th class="px-3 py-3 text-right">DUE</th>
                        @if($canViewProfit)<th class="px-3 py-3 text-right rounded-tr-2xl">PROFIT</th>@else<th class="px-3 py-3 text-right rounded-tr-2xl"></th>@endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                    @forelse($rows as $idx => $r)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                            <td class="px-3 py-2.5 text-center font-semibold text-slate-500">{{ $rows->firstItem() + $idx }}</td>
                            <td class="px-3 py-2.5 text-slate-600 dark:text-slate-400 whitespace-nowrap">{{ $r['date'] }}</td>
                            <td class="px-3 py-2.5 font-bold whitespace-nowrap">
                                <a href="{{ route('retail.invoice.show', $r['id']) }}" target="_blank" class="px-2 py-0.5 rounded-md text-xs font-bold text-[#15803d] border border-emerald-300 bg-emerald-50/60 dark:bg-emerald-950/40 dark:border-emerald-800 hover:underline">
                                    {{ str_starts_with((string)$r['order_no'], '#') ? $r['order_no'] : '#'.$r['order_no'] }}
                                </a>
                            </td>
                            <td class="px-3 py-2.5 font-medium text-slate-800 dark:text-slate-200">{{ $r['customer_name'] }}</td>
                            @if($canViewProfit)
                                <td class="px-3 py-2.5 text-right tabular-nums text-slate-600 dark:text-slate-400">{{ Money::formatBd($r['total_cost']) }}</td>
                            @endif
                            <td class="px-3 py-2.5 text-right font-black text-slate-900 dark:text-white tabular-nums">{{ Money::formatBd($r['selling_price']) }}</td>
                            <td class="px-3 py-2.5 text-right tabular-nums text-slate-600 dark:text-slate-400">{{ Money::formatBd($r['discount_amount']) }}</td>
                            <td class="px-3 py-2.5 text-right text-amber-600 dark:text-amber-400 tabular-nums">{{ Money::formatBd($r['return_amount']) }}</td>
                            <td class="px-3 py-2.5 text-right font-black text-emerald-700 dark:text-emerald-400 tabular-nums">{{ Money::formatBd($r['paid_amount']) }}</td>
                            <td class="px-3 py-2.5 text-right font-black text-rose-600 dark:text-rose-400 tabular-nums">{{ Money::formatBd($r['due_amount']) }}</td>
                            @if($canViewProfit)
                                <td class="px-3 py-2.5 text-right font-black text-teal-700 dark:text-teal-400 tabular-nums">{{ Money::formatBd($r['profit']) }}</td>
                            @endif
                        </tr>
                    @empty
                        <tr data-empty-row="true">
                            <td colspan="{{ $canViewProfit ? 11 : 9 }}" class="px-4 py-12 text-center text-slate-500 font-medium">
                                No sales found for the selected period.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-50/90 dark:bg-slate-800/80 font-black border-t border-slate-200 dark:border-slate-700">
                    <tr>
                        <td colspan="4" class="px-3.5 py-3 text-right text-xs text-slate-700 dark:text-slate-300 font-bold">Total Summary:</td>
                        @if($canViewProfit)<td class="px-3 py-3 text-right tabular-nums">{{ Money::formatBd($t['total_cost']) }}</td>@endif
                        <td class="px-3 py-3 text-right text-slate-900 dark:text-white font-black tabular-nums">{{ Money::formatBd($t['selling_price']) }}</td>
                        <td class="px-3 py-3 text-right tabular-nums">{{ Money::formatBd($t['discount_amount']) }}</td>
                        <td class="px-3 py-3 text-right text-amber-600 dark:text-amber-400 tabular-nums">{{ Money::formatBd($t['return_amount']) }}</td>
                        <td class="px-3 py-3 text-right text-emerald-700 dark:text-emerald-400 font-black tabular-nums">{{ Money::formatBd($t['paid_amount']) }}</td>
                        <td class="px-3 py-3 text-right text-rose-600 dark:text-rose-400 font-black tabular-nums">{{ Money::formatBd($t['due_amount']) }}</td>
                        @if($canViewProfit)<td class="px-3 py-3 text-right text-teal-700 dark:text-teal-400 font-black tabular-nums">{{ Money::formatBd($t['profit']) }}</td>@endif
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Mobile & Tablet Box Cards (1 col mobile, 2 col tablet) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:hidden gap-3 p-3">
            @forelse($rows as $idx => $r)
                <div class="unified-ui-border rounded-2xl p-3.5 bg-white dark:bg-slate-800/90 shadow-2xs space-y-2 border border-slate-200 dark:border-slate-800">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800 gap-1.5 flex-wrap">
                        <div class="flex items-center gap-1.5">
                            <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs">
                                #{{ $rows->firstItem() + $idx }}
                            </span>
                            <a href="{{ route('retail.invoice.show', $r['id']) }}" target="_blank" class="px-2 py-0.5 rounded-md text-xs font-bold text-[#15803d] border border-emerald-300 bg-emerald-50/60 dark:bg-emerald-950/40 dark:border-emerald-800 hover:underline">
                                {{ str_starts_with((string)$r['order_no'], '#') ? $r['order_no'] : '#'.$r['order_no'] }}
                            </a>
                        </div>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                            {{ $r['date'] }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-2">
                        <div class="text-xs font-bold text-slate-800 dark:text-slate-100 truncate">
                            <i class="fa-regular fa-user text-slate-400 me-1"></i>{{ $r['customer_name'] }}
                        </div>
                        <div class="text-xs font-black text-slate-900 dark:text-white tabular-nums shrink-0">
                            {{ Money::formatBd($r['selling_price']) }}
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-1.5 pt-1 text-xs bg-slate-50 dark:bg-slate-900/60 p-2 rounded-xl border border-slate-100 dark:border-slate-800">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 text-[11px]">Paid:</span>
                            <span class="font-bold text-emerald-700 dark:text-emerald-400 tabular-nums">{{ Money::formatBd($r['paid_amount']) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 text-[11px]">Due:</span>
                            <span class="font-bold text-rose-600 dark:text-rose-400 tabular-nums">{{ Money::formatBd($r['due_amount']) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 text-[11px]">Disc:</span>
                            <span class="font-medium text-slate-600 dark:text-slate-400 tabular-nums">{{ Money::formatBd($r['discount_amount']) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 text-[11px]">Return:</span>
                            <span class="font-medium text-amber-600 dark:text-amber-400 tabular-nums">{{ Money::formatBd($r['return_amount']) }}</span>
                        </div>
                        @if($canViewProfit)
                            <div class="flex items-center justify-between col-span-2 pt-1 border-t border-slate-200/60 dark:border-slate-700">
                                <span class="text-slate-400 text-[11px]">Net Profit:</span>
                                <span class="font-black text-teal-700 dark:text-teal-400 tabular-nums">{{ Money::formatBd($r['profit']) }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full py-8 text-center text-slate-400 text-xs">
                    No sales found for the selected period.
                </div>
            @endforelse
        </div>
        </div>
        <div class="flex flex-col sm:flex-row items-center justify-between pt-4 mt-4 border-t border-slate-200 dark:border-slate-800 gap-3">
                <div class="flex items-center gap-1.5 text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
                    <span>Showing</span>
                    <span class="font-bold text-slate-800 dark:text-slate-100 px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700">{{ $rows->firstItem() ?? 0 }} – {{ $rows->lastItem() ?? 0 }}</span>
                    <span>of</span>
                    <span class="font-bold text-emerald-700 dark:text-emerald-400 px-1.5 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-slate-700">{{ $rows->total() }}</span>
                    <span>sales</span>
                </div>
                <div class="w-full sm:w-auto flex items-center justify-center sm:justify-end">
                    {{ $rows->links() }}
                </div>
            </div>
        
    </div>
</div>
