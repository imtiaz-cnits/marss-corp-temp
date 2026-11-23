@php use App\Support\Money; @endphp

<div class="space-y-4">
    {{-- Header with Export Actions (Image 3 1:1) --}}
    {{-- Header with Export Actions (Image 3 1:1) --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <span class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-950/60 text-[#15803d] dark:text-emerald-400 flex items-center justify-center text-sm shadow-2xs">
                <i class="fa-solid fa-user-tie"></i>
            </span>
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight m-0">Personal &amp; Investor Transaction Report</h1>
            </div>
        </div>
        <div class="flex flex-wrap items-center justify-start gap-1.5">
            <x-ui.export-buttons table-id="retailPersonalTable" filename="retail-personal-transactions" title="MARSS Retail Personal Transactions" />
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

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div class="bg-rose-50/70 dark:bg-rose-950/30 border border-rose-100 dark:border-rose-900/40 rounded-2xl p-3.5 sm:p-4 shadow-2xs min-w-0">
            <span class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 block tracking-wider truncate">TOTAL PERSONAL EXPENSES</span>
            <span class="text-base sm:text-lg font-black text-rose-600 dark:text-rose-400 mt-0.5 block tabular-nums whitespace-nowrap">{{ Money::formatBd($data['total_expense']) }}</span>
        </div>

        <div class="bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-900/40 rounded-2xl p-3.5 sm:p-4 shadow-2xs min-w-0">
            <span class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 block tracking-wider truncate">TOTAL CAPITAL INVESTMENTS</span>
            <span class="text-base sm:text-lg font-black text-emerald-700 dark:text-emerald-400 mt-0.5 block tabular-nums whitespace-nowrap">{{ Money::formatBd($data['total_invest']) }}</span>
        </div>
    </div>

    {{-- Tables Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- Personal Expenses --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-3 sm:p-5 shadow-2xs">
            <div class="pb-3 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between mb-3">
                <span class="text-sm font-black text-slate-800 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-receipt text-rose-600"></i> Personal Expenses
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                    {{ $expenses->total() }} Entries
                </span>
            </div>
            {{-- Desktop Table (lg and above) --}}
            <div class="table-responsive unified-ui-border hidden lg:block w-full max-w-full overflow-x-auto rounded-2xl shadow-2xs bg-white dark:bg-slate-900 mb-4">
                <table id="retailPersonalTable" class="w-full text-xs text-left border-collapse">
                    <thead class="bg-[#15803d] text-white text-[11px] font-bold uppercase tracking-wider whitespace-nowrap">
                        <tr>
                            <th class="px-3.5 py-2.5 rounded-tl-2xl">DATE</th>
                            <th class="px-3.5 py-2.5">CATEGORY</th>
                            <th class="px-3.5 py-2.5">DETAILS</th>
                            <th class="px-3.5 py-2.5 text-right rounded-tr-2xl">AMOUNT</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                        @forelse($expenses as $e)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                                <td class="px-3.5 py-2 whitespace-nowrap text-slate-600 dark:text-slate-400">{{ $e->date }}</td>
                                <td class="px-3.5 py-2 font-bold text-slate-800 dark:text-slate-200">{{ $e->expenseType?->type_name }}</td>
                                <td class="px-3.5 py-2 text-slate-600 dark:text-slate-300">{{ $e->expense_details }}</td>
                                <td class="px-3.5 py-2 text-right font-black text-rose-600 dark:text-rose-400 tabular-nums">{{ Money::formatBd($e->expense_amount) }}</td>
                            </tr>
                        @empty
                            <tr data-empty-row="true">
                                <td colspan="4" class="px-4 py-8 text-center text-slate-500">No personal expenses found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile & Tablet Box Cards (1 col mobile, 2 col tablet) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:hidden gap-3 p-3">
                @forelse($expenses as $idx => $e)
                    <div class="rounded-2xl p-3 bg-white dark:bg-slate-800/90 shadow-2xs space-y-1.5 border border-slate-200 dark:border-slate-800">
                        <div class="flex items-center justify-between pb-1.5 border-b border-slate-100 dark:border-slate-800">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">
                                <i class="fa-regular fa-calendar text-slate-400 me-1"></i>{{ $e->date }}
                            </span>
                            <span class="px-2 py-0.5 rounded-md bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400 font-bold text-xs">
                                {{ $e->expenseType?->type_name }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between gap-2 pt-0.5">
                            <span class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ $e->expense_details ?: '-' }}</span>
                            <span class="text-xs font-black text-rose-600 dark:text-rose-400 tabular-nums shrink-0">{{ Money::formatBd($e->expense_amount) }}</span>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full p-6 text-center text-slate-400 text-xs">No personal expenses found.</div>
                @endforelse
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-between pt-4 mt-4 border-t border-slate-200 dark:border-slate-800 gap-3 px-3 pb-3">
                    <div class="flex items-center gap-1.5 text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
                        <span>Showing</span>
                        <span class="font-bold text-slate-800 dark:text-slate-100 px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700">{{ $expenses->firstItem() ?? 0 }} – {{ $expenses->lastItem() ?? 0 }}</span>
                        <span>of</span>
                        <span class="font-bold text-emerald-700 dark:text-emerald-400 px-1.5 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-slate-700">{{ $expenses->total() }}</span>
                        <span>expenses</span>
                    </div>
                    <div class="w-full sm:w-auto flex items-center justify-center sm:justify-end">
                        {{ $expenses->links() }}
                    </div>
                </div>
            
        </div>

        {{-- Retail Investments --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-3 sm:p-5 shadow-2xs">
            <div class="pb-3 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between mb-3">
                <span class="text-sm font-black text-slate-800 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-coins text-emerald-600"></i> Capital Investments
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                    {{ $invests->total() }} Entries
                </span>
            </div>
            {{-- Desktop Table (lg and above) --}}
            <div class="table-responsive unified-ui-border hidden lg:block w-full max-w-full overflow-x-auto rounded-2xl shadow-2xs bg-white dark:bg-slate-900 mb-4">
                <table id="retailInvestmentsTable" class="w-full text-xs text-left border-collapse">
                    <thead class="bg-[#15803d] text-white text-[11px] font-bold uppercase tracking-wider whitespace-nowrap">
                        <tr>
                            <th class="px-3.5 py-2.5 rounded-tl-2xl">DATE</th>
                            <th class="px-3.5 py-2.5">INVESTOR</th>
                            <th class="px-3.5 py-2.5">DETAILS</th>
                            <th class="px-3.5 py-2.5 text-right rounded-tr-2xl">AMOUNT</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                        @forelse($invests as $i)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                                <td class="px-3.5 py-2 whitespace-nowrap text-slate-600 dark:text-slate-400">{{ $i->date }}</td>
                                <td class="px-3.5 py-2 font-bold text-slate-800 dark:text-slate-200">{{ $i->investor?->name }}</td>
                                <td class="px-3.5 py-2 text-slate-600 dark:text-slate-300">{{ $i->invest_details }}</td>
                                <td class="px-3.5 py-2 text-right font-black text-emerald-700 dark:text-emerald-400 tabular-nums">{{ Money::formatBd($i->invest_amount) }}</td>
                            </tr>
                        @empty
                            <tr data-empty-row="true">
                                <td colspan="4" class="px-4 py-8 text-center text-slate-500">No capital investments found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile & Tablet Box Cards (1 col mobile, 2 col tablet) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:hidden gap-3 p-3">
                @forelse($invests as $idx => $i)
                    <div class="rounded-2xl p-3 bg-white dark:bg-slate-800/90 shadow-2xs space-y-1.5 border border-slate-200 dark:border-slate-800">
                        <div class="flex items-center justify-between pb-1.5 border-b border-slate-100 dark:border-slate-800">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">
                                <i class="fa-regular fa-calendar text-slate-400 me-1"></i>{{ $i->date }}
                            </span>
                            <span class="px-2 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 font-bold text-xs">
                                {{ $i->investor?->name }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between gap-2 pt-0.5">
                            <span class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ $i->invest_details ?: '-' }}</span>
                            <span class="text-xs font-black text-emerald-700 dark:text-emerald-400 tabular-nums shrink-0">{{ Money::formatBd($i->invest_amount) }}</span>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full p-6 text-center text-slate-400 text-xs">No capital investments found.</div>
                @endforelse
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-between pt-4 mt-4 border-t border-slate-200 dark:border-slate-800 gap-3 px-3 pb-3">
                    <div class="flex items-center gap-1.5 text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
                        <span>Showing</span>
                        <span class="font-bold text-slate-800 dark:text-slate-100 px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700">{{ $invests->firstItem() ?? 0 }} – {{ $invests->lastItem() ?? 0 }}</span>
                        <span>of</span>
                        <span class="font-bold text-emerald-700 dark:text-emerald-400 px-1.5 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-slate-700">{{ $invests->total() }}</span>
                        <span>investments</span>
                    </div>
                    <div class="w-full sm:w-auto flex items-center justify-center sm:justify-end">
                        {{ $invests->links() }}
                    </div>
                </div>
            
        </div>
    </div>
</div>
