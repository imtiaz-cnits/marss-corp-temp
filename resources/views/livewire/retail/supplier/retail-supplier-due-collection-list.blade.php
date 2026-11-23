@php
    use App\Support\Money;
    use Carbon\Carbon;

    $filterLabels = [
        'all' => 'All time',
        'today' => 'Today',
        '7' => 'Last 7 Days',
        '30' => 'Last Month',
        '365' => 'Last Year',
    ];
    $activeFilterLabel = $filterLabels[$dateFilter] ?? 'All time';
@endphp

<div>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-300 dark:border-slate-800 shadow-xs overflow-hidden p-3 sm:p-5">
        {{-- 1. Top Section: Page Title (Screenshot 4) --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-3">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400 flex items-center justify-center border border-emerald-100 dark:border-slate-800 shadow-2xs shrink-0">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                    </svg>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-800 dark:text-white tracking-tight leading-none m-0">Supplier Due Collection List</h1>
            </div>
        </div>

        {{-- 2. Controls Row: Search | SHOW: 15 | All time Filter Dropdown (Screenshot 4) --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 sm:gap-3.5 mb-4">
            <div class="unified-ui-border flex-1 min-w-0 h-[38px] flex items-center px-3 py-2 bg-white dark:bg-slate-800/90 rounded-xl shadow-2xs focus-within:border-[#15803d]">
                <svg class="w-4 h-4 text-slate-400 shrink-0 mr-2 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Searching Invoice..."
                    class="w-full h-full bg-transparent border-0 outline-none text-xs sm:text-sm text-slate-800 dark:text-slate-100 placeholder:text-slate-400"
                >
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <div class="unified-ui-border flex-1 sm:flex-initial h-[38px] px-3 rounded-xl bg-white dark:bg-slate-800/90 flex items-center justify-between gap-1.5 text-xs font-semibold text-slate-600 dark:text-slate-300 shadow-2xs shrink-0">
                    <span class="text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider whitespace-nowrap">SHOW:</span>
                    <select wire:model.live="perPage" class="no-custom-select bg-transparent border-0 text-xs sm:text-sm font-bold text-[#15803d] dark:text-emerald-400 focus:outline-none cursor-pointer pr-1">
                        <option value="15">15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="200">200</option>
                    </select>
                </div>

                {{-- Custom Date Filter Dropdown --}}
                <div x-data="{ open: false }" @click.outside="open = false" class="custom-filter-dropdown flex-1 sm:w-[170px] relative">
                    <button
                        type="button"
                        @click="open = !open"
                        class="w-full unified-ui-border h-[38px] px-3 rounded-xl bg-white dark:bg-slate-800/90 text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 flex items-center justify-between gap-1.5 shadow-2xs cursor-pointer hover:border-emerald-500 transition-colors"
                    >
                        <span class="flex items-center gap-1.5 truncate">
                            <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                            <span class="truncate">{{ $activeFilterLabel }}</span>
                        </span>
                        <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transition-transform" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <div
                        x-show="open"
                        x-transition
                        style="display: none; right: 0; left: auto; min-width: 170px;"
                        class="select-menu"
                    >
                        @foreach($filterLabels as $val => $lbl)
                            <div
                                wire:click="setDateFilter('{{ $val }}')"
                                @click="open = false"
                                class="select-option-item {{ $dateFilter === $val ? 'active' : '' }}"
                            >
                                <span>{{ $lbl }}</span>
                                @if($dateFilter === $val)
                                    <i class="fa-solid fa-check text-xs text-emerald-600"></i>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. Desktop Table (8 Columns Reference Parity — Screenshot 4) --}}
        <div class="table-responsive unified-ui-border hidden lg:block w-full overflow-x-auto rounded-2xl shadow-2xs bg-white dark:bg-slate-900">
            <table id="retailSupplierDueCollectionsTable" class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#15803d] text-white text-xs font-semibold uppercase tracking-wider whitespace-nowrap">
                        <th class="p-[10px] text-center w-[50px] rounded-tl-2xl">SL</th>
                        <th class="p-[10px] text-start">Date</th>
                        <th class="p-[10px] text-start">Supplier ID</th>
                        <th class="p-[10px] text-start">Supplier Name</th>
                        <th class="p-[10px] text-end">Previous Due Amount</th>
                        <th class="p-[10px] text-end">Paid Amount</th>
                        <th class="p-[10px] text-end">Due Amount</th>
                        <th class="p-[10px] text-center w-[130px] rounded-tr-2xl">Payment Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm text-slate-700 dark:text-slate-200">
                    @forelse($rows as $idx => $r)
                        @php
                            $prevDue = (float) ($r->purchase_payable_amount ?? 0);
                            $paidAmt = (float) ($r->paid_amount ?? 0);
                            $dueAmt = (float) ($r->due_amount ?? 0);
                            $dateRaw = $r->due_collection_date ?: $r->created_at;
                            $dateFormatted = $dateRaw ? Carbon::parse($dateRaw)->format('M d, Y') : 'N/A';

                            if ($dueAmt <= 0.009 && $paidAmt > 0) {
                                $statusLabel = 'Fully Paid';
                                $statusClass = 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-800/40';
                            } elseif ($dueAmt > 0.009 && $paidAmt > 0) {
                                $statusLabel = 'Partial Paid';
                                $statusClass = 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-400 dark:border-amber-800/40';
                            } elseif ($dueAmt > 0.009 && $paidAmt <= 0.009) {
                                $statusLabel = 'Unpaid';
                                $statusClass = 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-400 dark:border-rose-800/40';
                            } else {
                                $statusLabel = 'Return';
                                $statusClass = 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700';
                            }
                        @endphp
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/60 transition-colors">
                            <td class="p-[10px] text-center font-semibold text-slate-500 dark:text-slate-400 text-xs">{{ ($rows->firstItem() ?? 1) + $idx }}</td>
                            <td class="p-[10px] text-start whitespace-nowrap text-slate-600 dark:text-slate-400 font-medium text-xs">{{ $dateFormatted }}</td>
                            <td class="p-[10px] text-start whitespace-nowrap">
                                @if($r->supplier)
                                    <a href="{{ route('retail.supplier.profile', $r->supplier->id) }}" wire:navigate class="inline-flex items-center gap-1 font-semibold text-emerald-600 hover:text-emerald-700 hover:underline dark:text-emerald-400 text-xs whitespace-nowrap" title="View Profile">
                                        <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="1" y="3" width="15" height="13"></rect>
                                            <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                                            <circle cx="5.5" cy="18.5" r="2.5"></circle>
                                            <circle cx="18.5" cy="18.5" r="2.5"></circle>
                                        </svg>
                                        <span>{{ $r->supplier->supplier_id ?: ('SUP-'.$r->supplier->id) }}</span>
                                    </a>
                                @else
                                    <span class="text-slate-400 text-xs">N/A</span>
                                @endif
                            </td>
                            <td class="p-[10px] text-start whitespace-nowrap">
                                @if($r->supplier)
                                    <a href="{{ route('retail.supplier.profile', $r->supplier->id) }}" wire:navigate class="font-bold text-slate-800 dark:text-slate-100 hover:text-emerald-600 transition-colors">{{ $r->supplier->name }}</a>
                                @else
                                    <span class="font-bold text-slate-800 dark:text-slate-100">N/A</span>
                                @endif
                            </td>
                            <td class="p-[10px] text-end font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap tabular-nums">{{ Money::formatBd($prevDue) }}</td>
                            <td class="p-[10px] text-end font-bold text-emerald-600 dark:text-emerald-400 whitespace-nowrap tabular-nums">{{ Money::formatBd($paidAmt) }}</td>
                            <td class="p-[10px] text-end font-bold whitespace-nowrap tabular-nums {{ $dueAmt > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-700 dark:text-slate-300' }}">{{ Money::formatBd($dueAmt) }}</td>
                            <td class="p-[10px] text-center whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $statusClass }}">{{ $statusLabel }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr data-empty-row="true">
                            <td colspan="8" class="px-4 py-8 text-center text-rose-500 font-bold">❌ No collection data found.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50/80 dark:bg-slate-800/80 font-bold text-sm border-t border-slate-200 dark:border-slate-800">
                        <th colspan="4" class="p-[10px] text-end font-bold text-slate-700 dark:text-slate-200">Sub Total:</th>
                        <th class="p-[10px] text-end font-bold text-slate-700 dark:text-slate-300 whitespace-nowrap tabular-nums">{{ Money::formatBd($subTotalPayable, false) }} TK</th>
                        <th class="p-[10px] text-end font-bold text-emerald-600 dark:text-emerald-400 whitespace-nowrap tabular-nums">{{ Money::formatBd($subTotalPaid, false) }} TK</th>
                        <th class="p-[10px] text-end font-bold text-rose-600 dark:text-rose-400 whitespace-nowrap tabular-nums">{{ Money::formatBd($subTotalDue, false) }} TK</th>
                        <th class="p-[10px]"></th>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- 4. Mobile & Tablet Card View (1-col Mobile, 2-col Tablet) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:hidden gap-3 mb-3">
            @forelse($rows as $idx => $r)
                @php
                    $prevDue = (float) ($r->purchase_payable_amount ?? 0);
                    $paidAmt = (float) ($r->paid_amount ?? 0);
                    $dueAmt = (float) ($r->due_amount ?? 0);
                    $dateRaw = $r->due_collection_date ?: $r->created_at;
                    $dateFormatted = $dateRaw ? Carbon::parse($dateRaw)->format('M d, Y') : 'N/A';

                    if ($dueAmt <= 0.009 && $paidAmt > 0) {
                        $statusLabel = 'Fully Paid';
                        $statusClass = 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400';
                    } elseif ($dueAmt > 0.009 && $paidAmt > 0) {
                        $statusLabel = 'Partial Paid';
                        $statusClass = 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-400';
                    } elseif ($dueAmt > 0.009 && $paidAmt <= 0.009) {
                        $statusLabel = 'Unpaid';
                        $statusClass = 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-400';
                    } else {
                        $statusLabel = 'Return';
                        $statusClass = 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300';
                    }
                @endphp
                <div class="unified-ui-border rounded-2xl p-3.5 bg-white dark:bg-slate-800/60 shadow-2xs">
                    <div class="flex items-center justify-between pb-2 mb-2.5 border-b border-slate-100 dark:border-slate-700/60">
                        <div class="flex items-center gap-1.5">
                            <span class="px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-[11px] font-bold">#{{ ($rows->firstItem() ?? 1) + $idx }}</span>
                            @if($r->supplier)
                                <a href="{{ route('retail.supplier.profile', $r->supplier->id) }}" wire:navigate class="px-2 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/40 text-emerald-700 dark:text-emerald-400 text-xs font-bold inline-flex items-center gap-1">
                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
                                    {{ $r->supplier->supplier_id ?: ('SUP-'.$r->supplier->id) }}
                                </a>
                            @endif
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border {{ $statusClass }}">{{ $statusLabel }}</span>
                    </div>

                    <div class="mb-2.5">
                        <h6 class="font-bold text-slate-800 dark:text-slate-100 text-sm m-0">{{ $r->supplier?->name ?? 'N/A' }}</h6>
                        <span class="text-slate-400 dark:text-slate-500 text-xs inline-flex items-center gap-1 mt-0.5">
                            <i class="fa-regular fa-calendar text-[11px]"></i> {{ $dateFormatted }}
                        </span>
                    </div>

                    <div class="grid grid-cols-3 gap-2 text-center">
                        <div class="p-2 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700/60">
                            <span class="text-[10px] font-bold uppercase text-slate-400 block">Prev Due</span>
                            <span class="font-bold text-xs text-slate-700 dark:text-slate-200 whitespace-nowrap tabular-nums">{{ Money::formatBd($prevDue) }}</span>
                        </div>
                        <div class="p-2 rounded-xl bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-200/70 dark:border-emerald-900/40">
                            <span class="text-[10px] font-bold uppercase text-emerald-600 dark:text-emerald-400 block">Paid</span>
                            <span class="font-bold text-xs text-emerald-700 dark:text-emerald-300 whitespace-nowrap tabular-nums">{{ Money::formatBd($paidAmt) }}</span>
                        </div>
                        <div class="p-2 rounded-xl bg-rose-50/70 dark:bg-rose-950/30 border border-rose-200/70 dark:border-rose-900/40">
                            <span class="text-[10px] font-bold uppercase text-rose-600 dark:text-rose-400 block">Due</span>
                            <span class="font-bold text-xs text-rose-600 dark:text-rose-400 whitespace-nowrap tabular-nums">{{ Money::formatBd($dueAmt) }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-rose-500 font-bold bg-white dark:bg-slate-800 rounded-2xl border border-slate-300 dark:border-slate-800">❌ No collection data found.</div>
            @endforelse
        </div>

        {{-- 5. Display Info & Pagination Footer (Screenshot 4) --}}
        <div class="flex flex-col sm:flex-row items-center justify-between pt-4 mt-4 border-t border-slate-200 dark:border-slate-800 gap-3">
            <div class="flex items-center gap-1.5 text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400">
                <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                <span>Showing</span>
                <span class="font-bold text-slate-800 dark:text-slate-100 px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700">{{ $rows->firstItem() ?? 0 }} – {{ $rows->lastItem() ?? 0 }}</span>
                <span>of</span>
                <span class="font-bold text-emerald-700 dark:text-emerald-400 px-1.5 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800">{{ $rows->total() }}</span>
                <span>collections</span>
            </div>
            <div class="w-full sm:w-auto">
                {{ $rows->links() }}
            </div>
        </div>
    </div>
</div>
