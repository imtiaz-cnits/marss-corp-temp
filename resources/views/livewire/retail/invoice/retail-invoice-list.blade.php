@php
    use App\Support\Money;
    $pageSubTotal = $orders->getCollection()->sum(fn($o) => (float) ($o->sub_total ?? 0));
    $pageDiscTotal = $orders->getCollection()->sum(fn($o) => (float) ($o->discount_amount ?? 0));
    $pagePaidTotal = $orders->getCollection()->sum(fn($o) => (float) ($o->paid_amount ?? 0));
    $pageDueTotal = $orders->getCollection()->sum(fn($o) => (float) ($o->due_amount ?? 0));
    $quickFilterLabels = [
        'all' => 'All time',
        'today' => 'Today',
        '7' => 'Last 7 Days',
        '30' => 'Last Month',
        '365' => 'Last Year',
    ];
    $activeFilterLabel = $quickFilterLabels[$quickFilter] ?? 'Filter';
@endphp

<div>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-300 dark:border-slate-800 shadow-xs overflow-hidden p-3 sm:p-5">
        {{-- 1. Top Section: Page Title with Emerald Icon Badge, Export Toolbar & Create Invoice Button --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-3">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400 flex items-center justify-center border border-emerald-100 dark:border-slate-800 shadow-2xs shrink-0">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                    </svg>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-800 dark:text-white tracking-tight leading-none m-0">Invoice List</h1>
            </div>

            <div class="flex items-center flex-wrap gap-2">
                <a
                    href="{{ route('retail.pos') }}"
                    wire:navigate
                    class="h-[38px] px-4 rounded-xl bg-[#15803d] hover:bg-[#166534] text-white text-sm font-semibold shadow-xs inline-flex items-center gap-1.5 transition-all"
                >
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>Create Invoice</span>
                </a>
            </div>
        </div>

        {{-- 2. Controls & Filter Row (5 Columns: Search | Start Date | End Date | SHOW: 15 | Custom Status Filter) --}}
        <div class="grid grid-cols-2 md:flex md:flex-nowrap items-center gap-2 mb-3.5">
            {{-- Col 1: Search Bar --}}
            <div class="col-span-2 md:flex-1 unified-ui-border h-[38px] flex items-center px-3 bg-white dark:bg-slate-800/90 rounded-xl shadow-2xs focus-within:border-[#15803d]">
                <svg class="w-4 h-4 text-slate-400 shrink-0 mr-2 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search Invoice, Customer, Mobile..."
                    class="w-full h-full bg-transparent border-0 outline-none text-xs sm:text-[13px] text-slate-800 dark:text-slate-100 placeholder:text-slate-400"
                >
            </div>

            {{-- Col 2: Start Date --}}
            <div class="w-full md:w-[140px]">
                <x-ui.date-picker wire:model.live="startDate" placeholder="Start Date" />
            </div>

            {{-- Col 3: End Date --}}
            <div class="w-full md:w-[140px]">
                <x-ui.date-picker wire:model.live="endDate" placeholder="End Date" />
            </div>

            {{-- Col 4: SHOW Entries Selector --}}
            <div class="unified-ui-border h-[38px] px-3 rounded-xl bg-white dark:bg-slate-800/90 flex items-center justify-between gap-1.5 text-xs font-semibold text-slate-600 dark:text-slate-300 shadow-2xs shrink-0">
                <span class="text-slate-400 text-[11px] font-bold uppercase tracking-wider">SHOW:</span>
                <select wire:model.live="perPage" class="no-custom-select bg-transparent border-0 text-xs sm:text-sm font-bold text-[#15803d] dark:text-emerald-400 focus:outline-none cursor-pointer pr-1">
                    <option value="15">15</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="200">200</option>
                </select>
            </div>

            {{-- Col 5: Custom Quick Filter Dropdown (Reference Parity: All time / Today / Last 7 Days / Last Month / Last Year) --}}
            <div x-data="{ open: false }" @click.outside="open = false" class="custom-filter-dropdown shrink-0 w-full md:w-[152px]">
                <button
                    type="button"
                    @click="open = !open"
                    class="w-full unified-ui-border h-[38px] px-3 rounded-xl bg-white dark:bg-slate-800/90 text-xs sm:text-[13px] font-semibold text-slate-700 dark:text-slate-200 flex items-center justify-between gap-1.5 shadow-2xs cursor-pointer hover:border-emerald-500 transition-colors"
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
                    @foreach($quickFilterLabels as $val => $lbl)
                        <div
                            wire:click="setQuickFilter('{{ $val }}')"
                            @click="open = false"
                            class="select-option-item {{ $quickFilter === $val ? 'active' : '' }}"
                        >
                            <span>{{ $lbl }}</span>
                            @if($quickFilter === $val)
                                <i class="fa-solid fa-check text-xs text-emerald-600"></i>
                            @endif
                        </div>
                    @endforeach
                    @if($search !== '' || $quickFilter !== 'all' || $startDate !== '' || $endDate !== '')
                        <div
                            wire:click="resetFilters"
                            @click="open = false"
                            class="select-option-item text-rose-600 hover:!bg-rose-50 hover:!text-rose-700 hover:!border-l-rose-500 border-t border-slate-100 dark:border-slate-700 mt-1 pt-1.5"
                        >
                            <span>Reset Filters</span>
                            <i class="fa-solid fa-rotate-left text-xs"></i>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- 3. Desktop Table (Unified UI Border, Solid #15803d Header, Rounded Top Corners) --}}
        <div class="table-responsive unified-ui-border hidden md:block w-full overflow-x-auto rounded-2xl shadow-2xs bg-white dark:bg-slate-900">
            <table id="retailInvoiceTable" data-export-skip-last="true" class="w-full text-xs sm:text-[13px] text-left border-collapse">
                <thead>
                    <tr class="bg-[#15803d] text-white text-xs font-semibold uppercase tracking-wider whitespace-nowrap">
                        <th class="p-[10px] text-center w-[44px] rounded-tl-2xl">SL</th>
                        <th class="p-[10px]">Invoice No</th>
                        <th class="p-[10px]">Date</th>
                        <th class="p-[10px]">Customer</th>
                        <th class="p-[10px] text-right">SubTotal</th>
                        <th class="p-[10px] text-right">Disc</th>
                        <th class="p-[10px] text-right">Paid</th>
                        <th class="p-[10px] text-right">Due</th>
                        <th class="p-[10px] text-center">Status</th>
                        <th class="p-[10px] text-center rounded-tr-2xl">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-200">
                    @forelse($orders as $idx => $order)
                        @php $isDue = (float) ($order->due_amount ?? 0) > 0.009; @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="p-[10px] text-center font-bold text-slate-400">{{ ($orders->firstItem() ?? 1) + $idx }}</td>
                            <td class="p-[10px] font-extrabold text-[#15803d] dark:text-emerald-400 whitespace-nowrap">{{ $order->order_no }}</td>
                            <td class="p-[10px] whitespace-nowrap text-xs text-slate-500">{{ \Carbon\Carbon::parse($order->invoice_date ?? $order->created_at)->format('d-m-Y') }}</td>
                            <td class="p-[10px]">
                                <div class="font-bold text-slate-800 dark:text-white leading-tight">{{ $order->customer?->customer_name ?? 'Walk-in' }}</div>
                                <div class="text-[11px] text-slate-400">{{ $order->customer?->mobile }}</div>
                            </td>
                            <td class="p-[10px] text-right font-semibold tabular-nums whitespace-nowrap">{{ Money::formatBd($order->sub_total) }}</td>
                            <td class="p-[10px] text-right text-slate-500 tabular-nums whitespace-nowrap">{{ Money::formatBd($order->discount_amount) }}</td>
                            <td class="p-[10px] text-right font-bold text-[#15803d] dark:text-emerald-400 tabular-nums whitespace-nowrap">{{ Money::formatBd($order->paid_amount) }}</td>
                            <td class="p-[10px] text-right font-bold tabular-nums whitespace-nowrap {{ $isDue ? 'text-rose-600' : 'text-slate-400' }}">{{ Money::formatBd($order->due_amount) }}</td>
                            <td class="p-[10px] text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $isDue ? 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' }}">
                                    {{ $isDue ? 'Due' : 'Paid' }}
                                </span>
                            </td>
                            <td class="p-[10px] text-center whitespace-nowrap">
                                <div class="inline-flex items-center justify-center gap-1">
                                    <button type="button" wire:click="openView({{ $order->id }})" title="Quick View" class="w-[30px] h-[30px] rounded-lg bg-[#f0f9ff] dark:bg-slate-800 text-[#0284c7] dark:text-sky-400 border border-[#cbd5e1] dark:border-slate-700 hover:bg-[#e0f2fe] hover:border-[#0284c7] dark:hover:bg-sky-900/60 inline-flex items-center justify-center text-xs transition-colors cursor-pointer"><i class="fa-regular fa-eye"></i></button>
                                    <button type="button" wire:click="openReturn({{ $order->id }})" title="Sales Return" class="w-[30px] h-[30px] rounded-lg bg-[#fffbeb] dark:bg-amber-950/50 text-[#d97706] dark:text-amber-400 border border-amber-200 dark:border-amber-800 hover:bg-[#d97706] hover:text-white inline-flex items-center justify-center text-xs transition-colors cursor-pointer"><i class="fa-solid fa-rotate-left"></i></button>
                                    @if($isDue)
                                        <button type="button" wire:click="openPayment({{ $order->id }})" title="Collect Due" class="w-[30px] h-[30px] rounded-lg bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 hover:bg-blue-600 hover:text-white inline-flex items-center justify-center text-xs transition-colors cursor-pointer"><i class="fa-solid fa-hand-holding-dollar"></i></button>
                                    @endif
                                    <button type="button" wire:click="openEdit({{ $order->id }})" title="Full Edit" class="w-[30px] h-[30px] rounded-lg bg-purple-50 dark:bg-purple-950/50 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800 hover:bg-purple-600 hover:text-white inline-flex items-center justify-center text-xs transition-colors cursor-pointer"><i class="fa-solid fa-pen-to-square"></i></button>
                                    <a href="{{ route('retail.invoice.show', $order->id) }}" target="_blank" title="Print Bill" class="w-[30px] h-[30px] rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 hover:bg-[#15803d] hover:text-white inline-flex items-center justify-center text-xs transition-colors"><i class="fa-solid fa-print"></i></a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr data-empty-row="true"><td colspan="10" class="px-4 py-8 text-center text-rose-500 font-bold">❌ No Retail invoices found.</td></tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-50/90 dark:bg-slate-800/70 text-slate-800 dark:text-slate-100 font-bold border-t-2 border-emerald-600/30 text-xs sm:text-[13px]">
                    <tr>
                        <td colspan="4" class="p-[10px] text-right font-bold text-slate-600 dark:text-slate-300">Page Total:</td>
                        <td class="p-[10px] text-right font-extrabold tabular-nums whitespace-nowrap">{{ Money::formatBd($pageSubTotal) }}</td>
                        <td class="p-[10px] text-right font-bold text-slate-500 tabular-nums whitespace-nowrap">{{ Money::formatBd($pageDiscTotal) }}</td>
                        <td class="p-[10px] text-right font-extrabold text-[#15803d] dark:text-emerald-400 tabular-nums whitespace-nowrap">{{ Money::formatBd($pagePaidTotal) }}</td>
                        <td class="p-[10px] text-right font-extrabold text-rose-600 tabular-nums whitespace-nowrap">{{ Money::formatBd($pageDueTotal) }}</td>
                        <td colspan="2" class="p-[10px]"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- 4. Mobile Cards --}}
        <div class="md:hidden space-y-3">
            @foreach($orders as $order)
                @php $isDue = (float) ($order->due_amount ?? 0) > 0.009; @endphp
                <div class="unified-ui-border rounded-2xl p-3.5 bg-white dark:bg-slate-800/80 shadow-2xs space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-extrabold text-[#15803d] dark:text-emerald-400 text-sm">{{ $order->order_no }}</span>
                        <span class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($order->invoice_date ?? $order->created_at)->format('d-m-Y') }}</span>
                    </div>
                    <p class="text-xs font-bold text-slate-800 dark:text-white">{{ $order->customer?->customer_name ?? 'Walk-in' }}</p>
                    <div class="grid grid-cols-2 gap-1 text-xs">
                        <span>Total: <strong>{{ Money::formatBd($order->sub_total) }}</strong></span>
                        <span>Paid: <strong class="text-emerald-600">{{ Money::formatBd($order->paid_amount) }}</strong></span>
                        <span>Due: <strong class="{{ $isDue ? 'text-rose-600' : 'text-slate-400' }}">{{ Money::formatBd($order->due_amount) }}</strong></span>
                        <span>Status: <strong>{{ $isDue ? 'Due' : 'Paid' }}</strong></span>
                    </div>
                    <div class="flex flex-wrap gap-1.5 pt-1">
                        <button type="button" wire:click="openView({{ $order->id }})" class="px-2.5 py-1 rounded-lg bg-sky-50 dark:bg-slate-700 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-slate-600 text-xs font-bold cursor-pointer">View</button>
                        <button type="button" wire:click="openReturn({{ $order->id }})" class="px-2.5 py-1 rounded-lg bg-amber-500 text-white text-xs font-bold cursor-pointer">Return</button>
                        @if($isDue)
                            <button type="button" wire:click="openPayment({{ $order->id }})" class="px-2.5 py-1 rounded-lg bg-blue-600 text-white text-xs font-bold cursor-pointer">Pay Due</button>
                        @endif
                        <button type="button" wire:click="openEdit({{ $order->id }})" class="px-2.5 py-1 rounded-lg bg-purple-600 text-white text-xs font-bold cursor-pointer">Edit</button>
                        <a href="{{ route('retail.invoice.show', $order->id) }}" target="_blank" class="px-2.5 py-1 rounded-lg bg-emerald-700 text-white text-xs font-bold">Print</a>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- 5. Smart Pagination & Display Info Footer --}}
        <div class="pt-4 mt-4 border-t border-slate-200 dark:border-slate-800">
            {{ $orders->links() }}
        </div>
    </div>

    {{-- Quick View Invoice Details Modal (Exact Reference Project Design) --}}
    @if($showViewModal && $viewingOrder)
        @php
            $qvDue = (float) ($viewingOrder->due_amount ?? 0);
            $qvPaid = (float) ($viewingOrder->paid_amount ?? 0);
            $qvStatus = $qvDue <= 0.009 ? 'Paid' : ($qvPaid > 0.009 ? 'Partial' : 'Due');
            $qvStatusClass = $qvStatus === 'Paid'
                ? 'bg-emerald-100 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800'
                : ($qvStatus === 'Partial'
                    ? 'bg-amber-100 text-amber-700 border border-amber-200 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-800'
                    : 'bg-rose-100 text-rose-700 border border-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800');
            $qvFormattedDate = \Carbon\Carbon::parse($viewingOrder->invoice_date ?? $viewingOrder->created_at)->format('M d, Y');
            $qvTotalItems = (int) $viewingOrder->details->sum(fn ($d) => (int) round((float) $d->quantity));
            $qvOrderTitle = str_starts_with((string) $viewingOrder->order_no, '#') ? $viewingOrder->order_no : '#' . $viewingOrder->order_no;
        @endphp
        <div
            class="fixed inset-0 z-50 bg-slate-900/65 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4"
            wire:click.self="closeModals"
        >
            <div class="w-full max-w-[760px] max-h-[88vh] bg-white dark:bg-slate-900 rounded-2xl shadow-2xl overflow-hidden flex flex-col">
                {{-- Sticky Emerald Header --}}
                <div class="marss-modal-header sticky top-0 z-20 px-4 py-3 text-white flex items-center justify-between shadow-sm shrink-0" style="background-color: #15803d;">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-white/20 text-white flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h5 class="text-base sm:text-lg font-bold text-white tracking-tight mb-0.5 truncate">Invoice {{ $qvOrderTitle }}</h5>
                            <div class="flex items-center gap-1.5 flex-wrap text-[11px]">
                                <span class="px-2 py-0.5 rounded-full bg-white/15 border border-white/25 text-white font-semibold">
                                    {{ $viewingOrder->customer?->customer_name ?? 'Walk-in' }}
                                </span>
                                <span class="px-2 py-0.5 rounded-full bg-white/15 border border-white/25 text-white font-semibold inline-flex items-center gap-1">
                                    <i class="fa-regular fa-calendar text-[10px]"></i>
                                    <span>{{ $qvFormattedDate }}</span>
                                </span>
                            </div>
                        </div>
                    </div>
                    <button
                        type="button"
                        wire:click="closeModals"
                        aria-label="Close"
                        class="w-8 h-8 rounded-full bg-[#dc2626] hover:bg-[#b91c1c] text-white flex items-center justify-center shrink-0 cursor-pointer transition-colors"
                    >
                        <i class="fa-solid fa-xmark text-xs"></i>
                    </button>
                </div>

                {{-- Scrollable Body --}}
                <div class="p-4 space-y-4 overflow-y-auto flex-1">
                    {{-- 4 KPI Stat Cards --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-center">
                            <span class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Payment Status</span>
                            <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $qvStatusClass }}">{{ $qvStatus }}</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-center">
                            <span class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Total Items</span>
                            <span class="text-sm sm:text-base font-bold text-slate-800 dark:text-slate-100 tabular-nums">{{ $qvTotalItems }}</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-center">
                            <span class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Total Amount</span>
                            <span class="text-sm sm:text-base font-bold text-slate-800 dark:text-slate-100 tabular-nums whitespace-nowrap">{{ Money::formatBd($viewingOrder->sub_total) }}</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-center">
                            <span class="block text-[10px] font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider mb-1">Paid / Due</span>
                            <span class="text-xs sm:text-sm font-bold text-emerald-700 dark:text-emerald-300 tabular-nums">{{ Money::formatBd($viewingOrder->paid_amount) }} / {{ Money::formatBd($viewingOrder->due_amount) }}</span>
                        </div>
                    </div>

                    {{-- Invoice Specification Table --}}
                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                        <div class="bg-slate-100 dark:bg-slate-800 px-5 py-3 border-b border-slate-200 dark:border-slate-700 font-bold text-xs text-slate-700 dark:text-slate-200 uppercase tracking-wider flex items-center justify-between flex-wrap gap-2">
                            <span>Invoice Specification</span>
                            <span class="text-emerald-700 dark:text-emerald-400 font-bold">Total Value: {{ Money::formatBd($viewingOrder->sub_total) }}</span>
                        </div>
                        <div class="divide-y divide-slate-200 dark:divide-slate-700 text-xs">
                            <div class="grid grid-cols-3 px-5 py-2.5 bg-white dark:bg-slate-900">
                                <span class="font-semibold text-slate-500 dark:text-slate-400">Invoice No:</span>
                                <span class="col-span-2 font-mono font-bold text-slate-800 dark:text-slate-100">{{ $viewingOrder->order_no }}</span>
                            </div>
                            <div class="grid grid-cols-3 px-5 py-2.5 bg-slate-50 dark:bg-slate-800/50">
                                <span class="font-semibold text-slate-500 dark:text-slate-400">Customer Name:</span>
                                <span class="col-span-2 font-medium text-slate-800 dark:text-slate-200">{{ $viewingOrder->customer?->customer_name ?? 'Walk-in' }}</span>
                            </div>
                            <div class="grid grid-cols-3 px-5 py-2.5 bg-white dark:bg-slate-900">
                                <span class="font-semibold text-slate-500 dark:text-slate-400">Mobile Number:</span>
                                <span class="col-span-2 font-medium text-slate-800 dark:text-slate-200">{{ $viewingOrder->customer?->mobile ?: 'N/A' }}</span>
                            </div>
                            <div class="grid grid-cols-3 px-5 py-2.5 bg-slate-50 dark:bg-slate-800/50">
                                <span class="font-semibold text-slate-500 dark:text-slate-400">Customer ID:</span>
                                <span class="col-span-2 font-medium text-slate-800 dark:text-slate-200">{{ $viewingOrder->customer?->customer_id ?: 'N/A' }}</span>
                            </div>
                            <div class="grid grid-cols-3 px-5 py-2.5 bg-white dark:bg-slate-900">
                                <span class="font-semibold text-slate-500 dark:text-slate-400">Created By:</span>
                                <span class="col-span-2 font-medium text-slate-800 dark:text-slate-200">{{ $viewingOrder->user?->name ?? 'Admin' }}</span>
                            </div>
                            <div class="grid grid-cols-3 px-5 py-2.5 bg-slate-50 dark:bg-slate-800/50">
                                <span class="font-semibold text-slate-500 dark:text-slate-400">Invoice Date:</span>
                                <span class="col-span-2 font-medium text-slate-800 dark:text-slate-200">{{ $qvFormattedDate }}</span>
                            </div>
                            <div class="grid grid-cols-3 px-5 py-2.5 bg-white dark:bg-slate-900">
                                <span class="font-semibold text-slate-500 dark:text-slate-400">Sub Total:</span>
                                <span class="col-span-2 font-medium text-slate-800 dark:text-slate-200 tabular-nums">{{ Money::formatBd($viewingOrder->sub_total) }}</span>
                            </div>
                            <div class="grid grid-cols-3 px-5 py-2.5 bg-slate-50 dark:bg-slate-800/50">
                                <span class="font-semibold text-slate-500 dark:text-slate-400">Discount Amount:</span>
                                <span class="col-span-2 font-medium text-slate-800 dark:text-slate-200 tabular-nums">{{ Money::formatBd($viewingOrder->discount_amount ?? 0) }}</span>
                            </div>
                            <div class="grid grid-cols-3 px-5 py-2.5 bg-white dark:bg-slate-900">
                                <span class="font-semibold text-slate-500 dark:text-slate-400">Paid Amount:</span>
                                <span class="col-span-2 font-bold text-emerald-600 dark:text-emerald-400 tabular-nums">{{ Money::formatBd($viewingOrder->paid_amount ?? 0) }}</span>
                            </div>
                            <div class="grid grid-cols-3 px-5 py-2.5 bg-slate-50 dark:bg-slate-800/50">
                                <span class="font-semibold text-slate-500 dark:text-slate-400">Due Amount:</span>
                                <span class="col-span-2 font-bold text-rose-600 dark:text-rose-400 tabular-nums">{{ Money::formatBd($viewingOrder->due_amount ?? 0) }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Purchased Items & Products Breakdown --}}
                    <div class="space-y-2 pt-2">
                        <h6 class="font-bold text-xs uppercase tracking-wider text-slate-600 dark:text-slate-400 flex items-center gap-1.5 mb-0 px-1 mb-2">
                            <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                <line x1="3" y1="6" x2="21" y2="6"></line>
                                <path d="M16 10a4 4 0 0 1-8 0"></path>
                            </svg>
                            <span>Purchased Items &amp; Products Breakdown</span>
                        </h6>
                        <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
                            <table class="w-full text-left text-xs border-collapse">
                                <thead class="bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold border-b border-slate-200 dark:border-slate-700">
                                    <tr>
                                        <th class="px-4 py-2.5 text-center w-[50px]">SL</th>
                                        <th class="px-4 py-2.5">Product Name</th>
                                        <th class="px-4 py-2.5 text-center">Qty</th>
                                        <th class="px-4 py-2.5 text-right">Price</th>
                                        <th class="px-4 py-2.5 text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                    @forelse($viewingOrder->details as $dIdx => $d)
                                        <tr class="bg-white dark:bg-slate-900">
                                            <td class="px-4 py-2.5 text-center font-semibold text-slate-500">{{ $dIdx + 1 }}</td>
                                            <td class="px-4 py-2.5 font-semibold text-slate-800 dark:text-slate-100">
                                                {{ $d->product?->product_name ?? "#{$d->product_id}" }}
                                                @if((int) ($d->return_quantity ?? 0) > 0)
                                                    <span class="ml-1.5 px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 text-[10px] font-bold">Returned: {{ (int) $d->return_quantity }}</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-2.5 text-center font-semibold text-slate-700 dark:text-slate-200">{{ (int) round((float) $d->quantity) }}</td>
                                            <td class="px-4 py-2.5 text-right tabular-nums whitespace-nowrap">{{ Money::formatBd($d->selling_price) }}</td>
                                            <td class="px-4 py-2.5 text-right font-bold text-slate-800 dark:text-white tabular-nums whitespace-nowrap">{{ Money::formatBd(Money::mul($d->selling_price, $d->quantity)) }}</td>
                                        </tr>
                                    @empty
                                        <tr data-empty-row="true"><td colspan="5" class="text-center py-4 text-slate-400">No items found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Sticky Footer --}}
                <div class="sticky bottom-0 z-20 px-4 py-2.5 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 flex justify-end shrink-0">
                    <button
                        type="button"
                        wire:click="closeModals"
                        class="px-4 h-[38px] rounded-xl bg-[#dc2626] hover:bg-[#b91c1c] text-white text-xs sm:text-sm font-semibold shadow-sm flex items-center justify-center cursor-pointer transition-colors"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Sales Return Modal --}}
    <x-ui.modal :show="$showReturnModal" max-width="lg" :title="'Sales Return — ' . $returnOrderNo" icon="fa-solid fa-rotate-left" close-method="closeModals">
        <form wire:submit="submitReturn" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 text-xs">
                <div>
                    <span class="block text-amber-700 dark:text-amber-300 font-semibold">Customer</span>
                    <strong class="text-sm text-slate-800 dark:text-white">{{ $returnCustomerName }}</strong>
                </div>
                <div>
                    <label class="block text-amber-700 dark:text-amber-300 font-semibold mb-1">Return Date</label>
                    <x-ui.date-picker wire:model="returnDate" />
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
                <table class="w-full text-xs text-left border-collapse">
                    <thead class="bg-[#15803d] text-white">
                        <tr>
                            <th class="px-3 py-2">Product</th>
                            <th class="px-3 py-2 text-center">Sold</th>
                            <th class="px-3 py-2 text-center">Returned</th>
                            <th class="px-3 py-2 text-center">Returnable</th>
                            <th class="px-3 py-2 text-right">Unit Price</th>
                            <th class="px-3 py-2 text-center w-24">Return Qty</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse($returnItems as $idx => $row)
                            <tr>
                                <td class="px-3 py-2 font-semibold text-slate-800 dark:text-slate-100">{{ $row['product_name'] }}</td>
                                <td class="px-3 py-2 text-center">{{ $row['sold_qty'] }}</td>
                                <td class="px-3 py-2 text-center text-amber-600 font-bold">{{ $row['returned_qty'] }}</td>
                                <td class="px-3 py-2 text-center font-bold {{ $row['available_qty'] > 0 ? 'text-emerald-600' : 'text-slate-400' }}">{{ $row['available_qty'] }}</td>
                                <td class="px-3 py-2 text-right tabular-nums whitespace-nowrap">{{ Money::formatBd($row['selling_price']) }}</td>
                                <td class="px-3 py-2 text-center">
                                    <input
                                        type="number"
                                        min="0"
                                        max="{{ $row['available_qty'] }}"
                                        wire:model.live="returnItems.{{ $idx }}.quantity"
                                        @disabled($row['available_qty'] <= 0)
                                        class="w-20 h-8 text-center rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 font-bold disabled:opacity-45"
                                    >
                                </td>
                            </tr>
                        @empty
                            <tr data-empty-row="true"><td colspan="6" class="py-6 text-center text-slate-400">No items found for this invoice.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" wire:click="closeModals" class="h-[38px] px-4 rounded-lg bg-[#dc2626] hover:bg-[#b91c1c] text-white text-sm font-bold cursor-pointer">Cancel</button>
                <button type="submit" class="h-[38px] px-5 rounded-lg bg-[#d97706] hover:bg-[#b45309] text-white text-sm font-bold cursor-pointer">Confirm Return</button>
            </div>
        </form>
    </x-ui.modal>

    {{-- Direct Due Payment Modal --}}
    <x-ui.modal :show="$showPaymentModal" :title="'Collect Due — ' . $payOrderNo" icon="fa-solid fa-hand-holding-dollar" close-method="closeModals">
        <form wire:submit="submitPayment" class="space-y-3">
            <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 flex justify-between text-sm">
                <span class="font-bold text-amber-800 dark:text-amber-300">Remaining Due:</span>
                <span class="font-extrabold text-amber-900 dark:text-amber-200">{{ Money::formatBd($payCurrentDue) }}</span>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold mb-1">Paid Amount (৳) *</label>
                    <input type="number" step="0.01" min="0" wire:model="payAmount" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-bold">
                </div>
                <div>
                    <label class="block text-xs font-bold mb-1">Discount (৳)</label>
                    <input type="number" step="0.01" min="0" wire:model="payDiscount" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold mb-1">Method</label>
                    <select wire:model="payMethod" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                        <option value="Cash">Cash</option><option value="bKash">bKash</option><option value="Nagad">Nagad</option><option value="Rocket">Rocket</option><option value="Bank">Bank</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold mb-1">Collection Date</label>
                    <x-ui.date-picker wire:model="payDate" />
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold mb-1">Transaction ID / Note</label>
                <input type="text" wire:model="payTrxId" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" wire:click="closeModals" class="h-[38px] px-4 rounded-lg bg-[#dc2626] text-white text-sm font-bold">Cancel</button>
                <button type="submit" class="h-[38px] px-5 rounded-lg bg-[#15803d] text-white text-sm font-bold cursor-pointer">Save Payment</button>
            </div>
        </form>
    </x-ui.modal>

    {{-- Full Invoice Edit Modal (920px Standard) --}}
    <x-ui.modal :show="$showEditModal" max-width="full-edit" :title="'Full Edit Invoice — ' . $editOrderNo" icon="fa-solid fa-pen-to-square" close-method="closeModals">
        <form wire:submit="submitFullEdit" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold mb-1">Customer *</label>
                    <select wire:model="editCustomerId" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->customer_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold mb-1">Invoice Date</label>
                    <x-ui.date-picker wire:model="editDate" />
                </div>
                <div>
                    <label class="block text-xs font-bold mb-1">Challan / Note</label>
                    <input type="text" wire:model="editNote" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                </div>
            </div>

            {{-- Add Line Item Bar --}}
            <div class="flex gap-2">
                <select wire:model="editAddProductId" class="flex-1 h-9 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                    <option value="">+ Add another Retail product...</option>
                    @foreach($allProducts as $ap)
                        <option value="{{ $ap->id }}">{{ $ap->product_name }} (Stock: {{ $ap->quantity }})</option>
                    @endforeach
                </select>
                <button type="button" wire:click="addEditProduct" class="h-9 px-4 rounded-xl bg-[#15803d] text-white text-xs font-bold cursor-pointer">Add Item</button>
            </div>

            <div class="overflow-x-auto border border-slate-200 dark:border-slate-700 rounded-xl">
                <table class="w-full text-xs">
                    <thead class="bg-[#15803d] text-white">
                        <tr><th class="px-3 py-2 text-left">Product</th><th class="px-3 py-2 text-center w-24">Qty</th><th class="px-3 py-2 text-right w-32">Unit Price</th><th class="px-3 py-2 text-right">Total</th><th class="px-3 py-2 w-10"></th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @foreach($editItems as $idx => $item)
                            <tr>
                                <td class="px-3 py-2 font-semibold">
                                    {{ $item['product_name'] }}
                                    @if($item['return_quantity'] > 0)
                                        <span class="ml-1 text-[10px] font-bold text-rose-600">(Min Qty: {{ $item['return_quantity'] }} returned)</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" min="{{ max(1, $item['return_quantity']) }}" wire:model.live="editItems.{{ $idx }}.quantity" class="w-20 h-8 text-center rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 font-bold">
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" step="0.01" min="0" wire:model.live="editItems.{{ $idx }}.selling_price" class="w-28 h-8 text-right px-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 font-bold">
                                </td>
                                <td class="px-3 py-2 text-right font-bold tabular-nums">{{ Money::formatBd(Money::mul($item['selling_price'] ?? 0, $item['quantity'] ?? 0)) }}</td>
                                <td class="px-3 py-2 text-center">
                                    <button type="button" wire:click="removeEditItem({{ $idx }})" class="text-red-500 hover:text-red-700 cursor-pointer"><i class="fa-solid fa-trash"></i></button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold mb-1">Discount</label>
                    <div class="flex">
                        <select wire:model="editDiscountType" class="no-custom-select h-10 px-2.5 rounded-l-xl border-[1.5px] border-r-0 border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold">
                            <option value="fixed">৳</option><option value="percent">%</option>
                        </select>
                        <input type="number" step="0.01" min="0" wire:model="editDiscountValue" class="w-full h-10 px-3 rounded-r-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold mb-1">Delivery Charge (৳)</label>
                    <input type="number" step="0.01" min="0" wire:model="editDeliveryCharge" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold mb-1">Paid Amount (৳)</label>
                    <input type="number" step="0.01" min="0" wire:model="editPaidAmount" @disabled($editHasMultiplePayments) class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm disabled:opacity-50">
                    @if($editHasMultiplePayments)
                        <p class="text-[10px] text-amber-600 mt-0.5">Locked (multiple payments exist).</p>
                    @endif
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" wire:click="closeModals" class="h-[38px] px-4 rounded-lg bg-[#dc2626] text-sm font-bold text-white">Cancel</button>
                <button type="submit" class="h-[38px] px-5 rounded-lg bg-[#15803d] hover:bg-[#166534] text-white text-sm font-bold cursor-pointer">Update Invoice</button>
            </div>
        </form>
    </x-ui.modal>
</div>
