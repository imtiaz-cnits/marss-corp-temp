@php use App\Support\Money; @endphp

<div class="space-y-6">
    {{-- TOPBAR / PAGE HEADER --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2.5">
                <span class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-[#15803d] flex items-center justify-center border border-emerald-200 dark:border-emerald-800 text-base shadow-xs">
                    <i class="fa-solid fa-credit-card"></i>
                </span>
                Expense &amp; Salary Management
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Company expenses and staff salary records &amp; reports</p>
        </div>
        <button type="button" wire:click="openCreate" class="h-10 px-5 rounded-xl bg-[#15803d] hover:bg-[#166534] text-white text-xs sm:text-sm font-bold flex items-center gap-2 cursor-pointer shadow-sm transition">
            <i class="fa-solid fa-plus"></i> Create Expense
        </button>
    </div>

    {{-- 4 METRIC CARDS (1:1 Reference Screenshot 1 & Image 1 Fix) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Card 1: TOTAL EXPENSES --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 border-l-[5px] border-l-rose-500 p-4 shadow-xs flex flex-col justify-between">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">TOTAL EXPENSES</span>
                    <h3 class="text-base sm:text-lg lg:text-xl font-black text-rose-600 mt-1 tabular-nums whitespace-nowrap">
                        {{ Money::formatBd($totalExpenses) }}
                    </h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/40 text-rose-500 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-file-invoice text-lg"></i>
                </div>
            </div>
            <div class="pt-3 mt-2 border-t border-slate-100 dark:border-slate-800 flex items-center gap-1.5 text-xs text-slate-500">
                <i class="fa-solid fa-list-ul text-slate-400 text-[11px]"></i>
                <span>All Entries</span>
            </div>
        </div>

        {{-- Card 2: TODAY'S EXPENSE --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 border-l-[5px] border-l-emerald-500 p-4 shadow-xs flex flex-col justify-between">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">TODAY'S EXPENSE</span>
                    <h3 class="text-base sm:text-lg lg:text-xl font-black text-emerald-600 mt-1 tabular-nums whitespace-nowrap">
                        {{ Money::formatBd($todayExpense) }}
                    </h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 flex items-center justify-center shrink-0">
                    <i class="fa-regular fa-calendar-check text-lg"></i>
                </div>
            </div>
            <div class="pt-3 mt-2 border-t border-slate-100 dark:border-slate-800 flex items-center gap-1.5 text-xs text-slate-500">
                <i class="fa-regular fa-calendar text-slate-400 text-[11px]"></i>
                <span>Today's Total</span>
            </div>
        </div>

        {{-- Card 3: THIS MONTH'S EXPENSE --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 border-l-[5px] border-l-cyan-500 p-4 shadow-xs flex flex-col justify-between">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">THIS MONTH'S EXPENSE</span>
                    <h3 class="text-base sm:text-lg lg:text-xl font-black text-cyan-600 dark:text-cyan-400 mt-1 tabular-nums whitespace-nowrap">
                        {{ Money::formatBd($monthExpense) }}
                    </h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-cyan-50 dark:bg-cyan-950/40 text-cyan-600 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-arrow-trend-up text-lg"></i>
                </div>
            </div>
            <div class="pt-3 mt-2 border-t border-slate-100 dark:border-slate-800 flex items-center gap-1.5 text-xs text-slate-500">
                <i class="fa-solid fa-chart-line text-slate-400 text-[11px]"></i>
                <span>Month Total</span>
            </div>
        </div>

        {{-- Card 4: STAFF TOTAL SALARY --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 border-l-[5px] border-l-indigo-500 p-4 shadow-xs flex flex-col justify-between">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">STAFF TOTAL SALARY</span>
                    <h3 class="text-base sm:text-lg lg:text-xl font-black text-indigo-600 dark:text-indigo-400 mt-1 tabular-nums whitespace-nowrap">
                        {{ Money::formatBd($staffTotalSalary) }}
                    </h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-dollar-sign text-lg"></i>
                </div>
            </div>
            <div class="pt-3 mt-2 border-t border-slate-100 dark:border-slate-800 flex items-center gap-1.5 text-xs text-slate-500">
                <i class="fa-regular fa-user text-slate-400 text-[11px]"></i>
                <span>Salary Paid</span>
            </div>
        </div>
    </div>

    {{-- EXPENSE & SALARY LIST SECTION (1:1 Reference Screenshot 1) --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 overflow-hidden shadow-xs">
        {{-- Toolbar --}}
        <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2 text-slate-800 dark:text-white font-bold text-sm sm:text-base">
                <i class="fa-solid fa-bars-staggered text-[#15803d]"></i>
                <span>Expense &amp; Salary List</span>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <div class="w-32">
                    <x-ui.date-picker wire:model.live="startDate" placeholder="From" />
                </div>
                <div class="w-32">
                    <x-ui.date-picker wire:model.live="endDate" placeholder="To" />
                </div>
                <div class="relative w-full sm:w-60">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search type/details/staff..." class="w-full h-9 pl-9 pr-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs sm:text-sm outline-none focus:border-emerald-600">
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">SHOW:</span>
                    <select wire:model.live="perPage" class="h-9 px-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-200 outline-none cursor-pointer">
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Desktop Table (Hidden on Mobile & Tablet) --}}
        <div class="overflow-x-auto hidden lg:block">
            <table class="w-full text-xs sm:text-sm text-left">
                <thead class="bg-[#15803d] text-white text-xs uppercase tracking-wider whitespace-nowrap">
                    <tr>
                        <th class="px-4 py-3">SL</th>
                        <th class="px-4 py-3">DATE</th>
                        <th class="px-4 py-3">EXPENSE TYPE</th>
                        <th class="px-4 py-3">STAFF PROFILE</th>
                        <th class="px-4 py-3">DETAILS</th>
                        <th class="px-4 py-3 text-right">AMOUNT (৳)</th>
                        <th class="px-4 py-3 text-right">ACTION</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($expenses as $idx => $e)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                            <td class="px-4 py-3 text-slate-500 font-medium">{{ $expenses->firstItem() + $idx }}</td>
                            <td class="px-4 py-3 whitespace-nowrap font-medium text-slate-700 dark:text-slate-300">
                                <span class="inline-flex items-center gap-1.5">
                                    <i class="fa-regular fa-calendar-check text-[#15803d]"></i>
                                    {{ \Carbon\Carbon::parse($e->date)->format('d/m/Y') }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-100 dark:border-emerald-900/40">
                                    {{ $e->expenseType?->type_name ?? 'General' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if($e->staff)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 text-xs font-bold border border-indigo-100 dark:border-indigo-900/40">
                                        <i class="fa-regular fa-user text-[11px]"></i>
                                        {{ $e->staff->name }}
                                    </span>
                                @else
                                    <span class="text-slate-400 dark:text-slate-500 font-medium">General Expense</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                {{ $e->expense_details ?: '-' }}
                            </td>
                            <td class="px-4 py-3 text-right font-black text-rose-600 dark:text-rose-400 tabular-nums whitespace-nowrap">
                                {{ Money::formatBd($e->expense_amount) }}
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <button type="button" wire:click="openEdit({{ $e->id }})" class="w-8 h-8 rounded-lg bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:hover:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 inline-flex items-center justify-center cursor-pointer transition border border-emerald-200 dark:border-emerald-800" title="Edit">
                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                </button>
                                <button type="button" wire:click="confirmDelete({{ $e->id }})" class="w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/50 text-rose-600 dark:text-rose-400 inline-flex items-center justify-center cursor-pointer transition border border-rose-200 dark:border-rose-800 ml-1" title="Delete">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-slate-500 dark:text-slate-400">
                                <i class="fa-solid fa-receipt text-3xl mb-2 text-slate-300 dark:text-slate-600"></i>
                                <p class="m-0 text-sm">No expense records found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile & Tablet Cards (1-col Mobile, 2-col Tablet) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:hidden gap-3 mb-3 p-3">
            @forelse($expenses as $idx => $e)
                <div class="unified-ui-border rounded-2xl p-3.5 bg-white dark:bg-slate-800/90 shadow-2xs transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-100 dark:border-slate-800">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold text-[11px]">#{{ $expenses->firstItem() + $idx }}</span>
                                <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-slate-800 text-[11px] font-semibold">
                                    <i class="fa-regular fa-calendar-check me-1 text-emerald-600"></i>{{ \Carbon\Carbon::parse($e->date)->format('d/m/Y') }}
                                </span>
                            </div>
                            <div>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-slate-800">
                                    {{ $e->expenseType?->type_name ?? 'General' }}
                                </span>
                            </div>
                        </div>

                        <div class="mb-2">
                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-1">
                                @if($e->staff)
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 text-xs font-bold border border-indigo-100 dark:border-indigo-900/40">
                                        <i class="fa-regular fa-user text-[10px]"></i>{{ $e->staff->name }}
                                    </span>
                                @else
                                    <span class="text-slate-400 dark:text-slate-500 font-medium text-xs">General Expense</span>
                                @endif
                            </div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-900/60 p-2 rounded-xl border border-slate-100 dark:border-slate-800">
                                <i class="fa-solid fa-align-left me-1 opacity-70"></i>{{ $e->expense_details ?: 'No details provided' }}
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2.5 mt-1 border-t border-slate-100 dark:border-slate-800">
                        <div>
                            <span class="text-slate-400 block text-[9px] font-bold uppercase tracking-wider">Amount</span>
                            <span class="font-black text-rose-600 dark:text-rose-400 text-sm whitespace-nowrap tabular-nums">৳ {{ Money::formatBd($e->expense_amount) }}</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <button type="button" wire:click="openEdit({{ $e->id }})" class="w-8 h-8 rounded-xl bg-emerald-50 hover:bg-emerald-600 text-emerald-600 hover:text-white border border-emerald-200 dark:bg-emerald-950/40 dark:border-slate-800 dark:text-emerald-400 flex items-center justify-center transition-all shadow-2xs cursor-pointer" title="Edit">
                                <i class="fa-solid fa-pen-to-square text-xs"></i>
                            </button>
                            <button type="button" wire:click="confirmDelete({{ $e->id }})" class="w-8 h-8 rounded-xl bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white border border-rose-200 dark:bg-rose-950/40 dark:border-slate-800 dark:text-rose-400 flex items-center justify-center transition-all shadow-2xs cursor-pointer" title="Delete">
                                <i class="fa-solid fa-trash-can text-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full p-8 text-center text-slate-400 dark:text-slate-500">
                    <i class="fa-solid fa-receipt text-3xl mb-2 text-slate-300 dark:text-slate-600"></i>
                    <p class="m-0 text-sm font-semibold">No expense records found.</p>
                </div>
            @endforelse
        </div>
        <div class="flex flex-col sm:flex-row items-center justify-between p-3.5 border-t border-slate-200 dark:border-slate-800 gap-3">
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

    {{-- CREATE EXPENSE MODAL (1:1 Reference Screenshot 2 & Images 2, 3, 4) --}}
    @if($showModal)
        <div data-marss-modal="true" class="fixed inset-0 z-[70] !m-0 overflow-hidden bg-slate-950/75 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4" wire:click.self="closeModal">
            <div class="w-full max-w-3xl max-h-[92vh] bg-white dark:bg-slate-900 rounded-2xl shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-800 flex flex-col">
                {{-- Modal Header --}}
                <div class="bg-[#15803d] px-5 py-3.5 text-white flex items-center justify-between shadow-sm shrink-0 sticky top-0 z-20">
                    <h3 class="text-base sm:text-lg font-bold text-white flex items-center gap-2.5 m-0">
                        <i class="fa-solid fa-credit-card"></i>
                        <span>{{ $editingId ? 'Edit Expense' : 'Create Expense' }}</span>
                    </h3>
                    <button type="button" wire:click="closeModal" class="w-7 h-7 rounded-full bg-red-600 hover:bg-red-700 text-white flex items-center justify-center border-0 cursor-pointer shadow-xs transition">
                        <i class="fa-solid fa-xmark text-xs"></i>
                    </button>
                </div>

                {{-- Modal Body --}}
                <form wire:submit="save" class="flex flex-col flex-1 overflow-hidden min-h-0 m-0">
                    <div class="p-5 overflow-y-auto flex-1 min-h-0 space-y-4">
                        {{-- Row 1: Date (with Calendar Picker) & Action Buttons --}}
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="w-full sm:w-64">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 flex items-center gap-1.5">
                                    <i class="fa-regular fa-calendar text-[#15803d]"></i>
                                    <span>Expense Date <span class="text-rose-500">*</span></span>
                                </label>
                                <x-ui.date-picker wire:model="date" />
                            </div>

                            <div class="flex items-center gap-2.5 pt-4 sm:pt-0">
                                <button type="button" wire:click="openQuickType" class="h-10 px-4 rounded-xl border border-emerald-500 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 text-xs sm:text-sm font-bold flex items-center gap-2 cursor-pointer transition">
                                    <i class="fa-solid fa-folder-plus text-emerald-600"></i>
                                    <span>+ Create New Type</span>
                                </button>
                                <button type="button" wire:click="openQuickStaff" class="h-10 px-4 rounded-xl border border-indigo-400 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 text-xs sm:text-sm font-bold flex items-center gap-2 cursor-pointer transition">
                                    <i class="fa-solid fa-user-plus text-indigo-600"></i>
                                    <span>+ Add New Staff</span>
                                </button>
                            </div>
                        </div>

                        {{-- Card: Select Expense Type and Enter Amount (Image 4) --}}
                        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 p-4 space-y-3.5">
                            <div class="flex items-center justify-between">
                                <h4 class="text-sm font-extrabold text-slate-800 dark:text-slate-100 flex items-center gap-2 m-0">
                                    <i class="fa-solid fa-bars-staggered text-[#15803d] text-xs"></i>
                                    <span>Select Expense Type and Enter Amount:</span>
                                </h4>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ count($selectedTypes) > 0 ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300' : 'bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300' }}">
                                    {{ count($selectedTypes) }} Selected
                                </span>
                            </div>

                            {{-- SELECTED ITEMS BLOCK (Image 4 1:1) --}}
                            @if(!empty($selectedTypes))
                                <div class="space-y-2.5">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-bold text-[#15803d] flex items-center gap-1.5">
                                            <i class="fa-solid fa-circle-check"></i> SELECTED ITEMS ({{ count($selectedTypes) }}):
                                        </span>
                                        <span class="text-slate-400 flex items-center gap-1">
                                            <i class="fa-solid fa-pen text-[10px]"></i> Click card to edit amount/details
                                        </span>
                                    </div>
                                    <div class="space-y-2">
                                        @foreach($selectedTypes as $sId)
                                            @php
                                                $tItem = $types->firstWhere('id', $sId);
                                                $sAmt = $typeAmounts[$sId] ?? '0.00';
                                                $sNote = $typeNotes[$sId] ?? '';
                                            @endphp
                                            <div class="p-3 rounded-2xl bg-emerald-50/60 dark:bg-emerald-950/30 border border-emerald-300 dark:border-emerald-800 flex items-center justify-between gap-3">
                                                <div class="flex items-start gap-2.5 min-w-0">
                                                    <i class="fa-solid fa-circle-check text-[#15803d] text-lg mt-0.5 shrink-0"></i>
                                                    <div class="min-w-0">
                                                        <h5 class="text-sm font-bold text-slate-900 dark:text-white m-0 truncate">{{ $tItem?->type_name ?? 'Expense' }}</h5>
                                                        <p class="text-xs text-slate-500 dark:text-slate-400 m-0 truncate">Note: {{ $sNote ?: '-' }}</p>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-2 shrink-0">
                                                    <span class="text-sm sm:text-base font-black text-rose-600 tabular-nums">
                                                        {{ Money::formatBd($sAmt) }}
                                                    </span>
                                                    <button type="button" wire:click="openTypeDetailsModal({{ $sId }})" class="w-8 h-8 rounded-full border border-emerald-400 bg-white dark:bg-slate-800 text-emerald-700 dark:text-emerald-300 flex items-center justify-center hover:bg-emerald-50 cursor-pointer shadow-2xs" title="Edit Amount/Details">
                                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                                    </button>
                                                    <button type="button" wire:click="removeSelectedType({{ $sId }})" class="w-8 h-8 rounded-full border border-rose-300 bg-white dark:bg-slate-800 text-rose-600 flex items-center justify-center hover:bg-rose-50 cursor-pointer shadow-2xs" title="Remove">
                                                        <i class="fa-solid fa-xmark text-xs"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- AVAILABLE EXPENSE TYPES HEADER --}}
                            <div class="flex items-center justify-between text-xs text-slate-400 pt-2 border-t border-slate-200 dark:border-slate-700">
                                <span class="font-bold tracking-wider uppercase text-[11px]">AVAILABLE EXPENSE TYPES:</span>
                                <span class="text-emerald-600 font-semibold flex items-center gap-1">
                                    <i class="fa-solid fa-arrow-pointer text-[10px]"></i> Click card to enter amount
                                </span>
                            </div>

                            {{-- Grid of Expense Type Cards (UNCHECKED BY DEFAULT, CLICK OPENS SUBMODAL) --}}
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2.5 max-h-56 overflow-y-auto p-1">
                                @foreach($types as $t)
                                    @php
                                        $isSelected = in_array($t->id, $selectedTypes, true);
                                        $isSalary = stripos((string)$t->type_name, 'salary') !== false || stripos((string)$t->type_name, 'stuff') !== false || stripos((string)$t->type_name, 'staff') !== false;
                                    @endphp
                                    <div wire:click="openTypeDetailsModal({{ $t->id }})" class="p-2.5 rounded-xl border cursor-pointer transition flex items-center justify-between gap-1.5 {{ $isSelected ? 'bg-emerald-50 dark:bg-emerald-950/50 border-emerald-500 shadow-xs ring-1 ring-emerald-500' : 'bg-white dark:bg-slate-800/70 border-slate-200 dark:border-slate-700 hover:border-emerald-400' }}">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <input type="checkbox" {{ $isSelected ? 'checked' : '' }} class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 pointer-events-none">
                                            <span class="text-xs font-bold text-slate-700 dark:text-slate-200 truncate">{{ $t->type_name }}</span>
                                        </div>
                                        <div class="flex items-center gap-1 shrink-0">
                                            @if($isSelected)
                                                <i class="fa-solid fa-pen text-[11px] text-[#15803d]"></i>
                                            @elseif($isSalary)
                                                <span class="px-1.5 py-0.2 rounded text-[10px] font-black bg-indigo-100 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300">Staff</span>
                                                <i class="fa-solid fa-arrow-up-right-from-square text-[10px] text-slate-400"></i>
                                            @else
                                                <i class="fa-solid fa-arrow-up-right-from-square text-[10px] text-slate-400"></i>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            @error('expense_amount')
                                <p class="text-xs text-rose-500 font-semibold m-0">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="px-5 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900 flex items-center justify-end gap-2.5 shrink-0 sticky bottom-0 z-20">
                        <button type="button" wire:click="closeModal" class="h-10 px-5 rounded-lg bg-[#ef4444] hover:bg-red-600 text-white text-xs sm:text-sm font-bold border-0 cursor-pointer transition">
                            Cancel
                        </button>
                        <button type="submit" class="h-10 px-6 rounded-lg bg-[#15803d] hover:bg-[#166534] text-white text-xs sm:text-sm font-bold border-0 cursor-pointer shadow-xs transition">
                            Submit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- SUB-MODAL 1: Expense Type Details (Image 3 1:1) --}}
    @if($showDetailsModal)
        <div data-marss-modal="true" class="fixed inset-0 z-[85] !m-0 overflow-hidden bg-slate-950/75 backdrop-blur-xs flex items-center justify-center p-4" wire:click.self="closeDetailsModal">
            <div class="w-full max-w-[480px] bg-white dark:bg-slate-900 rounded-2xl shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-700 flex flex-col animate-in fade-in zoom-in-95 duration-150">
                <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white flex items-center gap-2 m-0">
                        <i class="fa-solid fa-pen-to-square text-[#15803d]"></i>
                        <span><strong class="text-[#15803d]">{{ $activeTypeName }}</strong> - Details</span>
                    </h3>
                    <button type="button" wire:click="closeDetailsModal" class="w-7 h-7 rounded-full bg-red-600 hover:bg-red-700 text-white flex items-center justify-center border-0 cursor-pointer shadow-xs">
                        <i class="fa-solid fa-xmark text-xs"></i>
                    </button>
                </div>
                <form wire:submit.prevent="saveTypeDetails" class="p-5 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Amount (৳) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" min="0.01" wire:model="activeAmount" placeholder="0.00" autofocus class="w-full h-10 px-3.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-bold outline-none focus:border-[#16a34a] focus:ring-1 focus:ring-[#16a34a]">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Details / Note</label>
                            <input type="text" wire:model="activeNote" placeholder="Enter expense details..." class="w-full h-10 px-3.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm outline-none focus:border-[#16a34a]">
                        </div>
                    </div>
                    @if(stripos((string)$activeTypeName, 'salary') !== false || stripos((string)$activeTypeName, 'stuff') !== false || stripos((string)$activeTypeName, 'staff') !== false)
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Staff Member (for Salary)</label>
                            <select wire:model="activeStaffId" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm outline-none focus:border-[#16a34a]">
                                <option value="">Select Staff</option>
                                @foreach($staffMembers as $st)
                                    <option value="{{ $st->id }}">{{ $st->name }} ({{ $st->role }})</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" wire:click="closeDetailsModal" class="h-10 px-5 rounded-lg bg-[#ef4444] hover:bg-red-600 text-white text-xs sm:text-sm font-bold border-0 cursor-pointer">Cancel</button>
                        <button type="submit" class="h-10 px-6 rounded-lg bg-[#15803d] hover:bg-[#166534] text-white text-xs sm:text-sm font-bold border-0 cursor-pointer shadow-xs">Save Details</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- SUB-MODAL 2: Create New Type --}}
    @if($showQuickTypeModal)
        <div data-marss-modal="true" class="fixed inset-0 z-[80] !m-0 overflow-hidden bg-slate-950/75 backdrop-blur-xs flex items-center justify-center p-4" wire:click.self="$set('showQuickTypeModal', false)">
            <div class="w-full max-w-[420px] bg-white dark:bg-slate-900 rounded-2xl shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-700 flex flex-col">
                <div class="bg-[#15803d] px-4 py-3 text-white flex items-center justify-between shadow-sm shrink-0">
                    <h3 class="text-sm sm:text-base font-bold text-white flex items-center gap-2 m-0">
                        <i class="fa-solid fa-folder-plus"></i> Create New Expense Type
                    </h3>
                    <button type="button" wire:click="$set('showQuickTypeModal', false)" class="w-7 h-7 rounded-full bg-red-600 hover:bg-red-700 text-white flex items-center justify-center border-0 cursor-pointer shadow-xs">
                        <i class="fa-solid fa-xmark text-xs"></i>
                    </button>
                </div>
                <form wire:submit="saveQuickType" class="p-5 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Expense Type Name <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="quickTypeName" placeholder="e.g. Current Bill, Office Rent" class="w-full h-[40px] px-3.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm outline-none focus:border-[#16a34a]">
                        @error('quickTypeName') <span class="text-xs text-rose-500 font-semibold">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Status <span class="text-rose-500">*</span></label>
                        <select wire:model="quickTypeStatus" class="w-full h-[40px] px-3.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm outline-none focus:border-[#16a34a]">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" wire:click="$set('showQuickTypeModal', false)" class="h-9 px-4 rounded-lg bg-[#ef4444] hover:bg-red-600 text-white text-xs font-bold border-0 cursor-pointer">Cancel</button>
                        <button type="submit" class="h-9 px-5 rounded-lg bg-[#15803d] hover:bg-[#166534] text-white text-xs font-bold border-0 cursor-pointer shadow-xs">Save Type</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- SUB-MODAL 3: Add New Staff --}}
    @if($showQuickStaffModal)
        <div data-marss-modal="true" class="fixed inset-0 z-[80] !m-0 overflow-hidden bg-slate-950/75 backdrop-blur-xs flex items-center justify-center p-4" wire:click.self="$set('showQuickStaffModal', false)">
            <div class="w-full max-w-[440px] bg-white dark:bg-slate-900 rounded-2xl shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-700 flex flex-col">
                <div class="bg-[#15803d] px-4 py-3 text-white flex items-center justify-between shadow-sm shrink-0">
                    <h3 class="text-sm sm:text-base font-bold text-white flex items-center gap-2 m-0">
                        <i class="fa-solid fa-user-plus"></i> Add New Staff
                    </h3>
                    <button type="button" wire:click="$set('showQuickStaffModal', false)" class="w-7 h-7 rounded-full bg-red-600 hover:bg-red-700 text-white flex items-center justify-center border-0 cursor-pointer shadow-xs">
                        <i class="fa-solid fa-xmark text-xs"></i>
                    </button>
                </div>
                <form wire:submit="saveQuickStaff" class="p-5 space-y-3.5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Staff Name <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="quickStaffName" placeholder="Enter staff name" class="w-full h-[40px] px-3.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm outline-none focus:border-[#16a34a]">
                        @error('quickStaffName') <span class="text-xs text-rose-500 font-semibold">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Phone Number</label>
                        <input type="text" wire:model="quickStaffPhone" placeholder="Mobile / Phone number" class="w-full h-[40px] px-3.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm outline-none focus:border-[#16a34a]">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Email Address</label>
                        <input type="email" wire:model="quickStaffEmail" placeholder="staff@example.com (optional)" class="w-full h-[40px] px-3.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm outline-none focus:border-[#16a34a]">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Role <span class="text-rose-500">*</span></label>
                        <select wire:model="quickStaffRole" class="w-full h-[40px] px-3.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm outline-none focus:border-[#16a34a]">
                            <option value="Staff">Staff</option>
                            <option value="Manager">Manager</option>
                            <option value="Salesman">Salesman</option>
                        </select>
                    </div>
                    <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" wire:click="$set('showQuickStaffModal', false)" class="h-9 px-4 rounded-lg bg-[#ef4444] hover:bg-red-600 text-white text-xs font-bold border-0 cursor-pointer">Cancel</button>
                        <button type="submit" class="h-9 px-5 rounded-lg bg-[#15803d] hover:bg-[#166534] text-white text-xs font-bold border-0 cursor-pointer shadow-xs">Save Staff</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- CUSTOM DELETE CONFIRMATION MODAL (1:1 Reference) --}}
    @if($showDeleteModal)
        <div data-marss-modal="true" class="fixed inset-0 z-[80] !m-0 overflow-hidden bg-slate-950/75 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="w-full max-w-md bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 p-6 text-center animate-in fade-in zoom-in-95 duration-150">
                <div class="w-16 h-16 rounded-full bg-rose-50 dark:bg-rose-950/40 text-rose-500 flex items-center justify-center mx-auto mb-4 border border-rose-100 dark:border-rose-900/50">
                    <i class="fa-solid fa-triangle-exclamation text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Are you sure?</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-6 leading-relaxed">
                    Do you really want to delete <span class="font-bold text-slate-800 dark:text-slate-200">{{ $deletingLabel }}</span>? This action cannot be undone.
                </p>
                <div class="flex items-center justify-center gap-3">
                    <button type="button" wire:click="$set('showDeleteModal', false)" class="h-10 px-5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm font-bold hover:bg-slate-50 cursor-pointer">Cancel</button>
                    <button type="button" wire:click="executeDelete" class="h-10 px-5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-sm font-bold shadow-xs border-0 cursor-pointer">Yes, Delete</button>
                </div>
            </div>
        </div>
    @endif
</div>
