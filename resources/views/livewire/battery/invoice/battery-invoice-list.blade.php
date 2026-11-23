@php
    use App\Support\Money;
    $pageSubTotal = $orders->getCollection()->sum(fn($o) => (float) ($o->sub_total ?? 0));
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
        {{-- 1. Top Section --}}
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
                <h1 class="text-xl sm:text-2xl font-bold text-slate-800 dark:text-white tracking-tight leading-none m-0">Battery Invoice List</h1>
            </div>

            <div class="flex items-center flex-wrap gap-2">
                <a
                    href="{{ route('battery.pos') }}"
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

        {{-- 2. Controls & Filter Row --}}
        <div class="grid grid-cols-2 md:flex md:flex-nowrap items-center gap-2 mb-3.5">
            <div class="col-span-2 md:flex-1 unified-ui-border h-[38px] flex items-center px-3 bg-white dark:bg-slate-800/90 rounded-xl shadow-2xs focus-within:border-[#15803d]">
                <svg class="w-4 h-4 text-slate-400 shrink-0 mr-2 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search Invoice, Customer..."
                    class="w-full h-full bg-transparent border-0 outline-none text-xs sm:text-[13px] text-slate-800 dark:text-slate-100 placeholder:text-slate-400"
                >
            </div>

            <div class="w-full md:w-[140px]">
                <x-ui.date-picker wire:model.live="startDate" placeholder="Start Date" />
            </div>

            <div class="w-full md:w-[140px]">
                <x-ui.date-picker wire:model.live="endDate" placeholder="End Date" />
            </div>

            <div class="unified-ui-border h-[38px] px-3 rounded-xl bg-white dark:bg-slate-800/90 flex items-center justify-between gap-1.5 text-xs font-semibold text-slate-600 dark:text-slate-300 shadow-2xs shrink-0">
                <span class="text-slate-400 text-[11px] font-bold uppercase tracking-wider">SHOW:</span>
                <select wire:model.live="perPage" class="no-custom-select bg-transparent border-0 text-xs sm:text-sm font-bold text-[#15803d] dark:text-emerald-400 focus:outline-none cursor-pointer pr-1">
                    <option value="15">15</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="200">200</option>
                </select>
            </div>

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

        {{-- 3. Desktop Table (Reference Parity: 8 Columns: SL | INVOICE NO | CUSTOMER INFO | FINANCIAL SUMMARY | CREATED BY | DATE | STATUS | ACTION) --}}
        <div class="table-responsive unified-ui-border hidden lg:block w-full overflow-x-auto rounded-2xl shadow-2xs bg-white dark:bg-slate-900">
            <table id="batteryInvoiceTable" data-export-skip-last="true" class="w-full text-xs sm:text-[13px] text-left border-collapse">
                <thead>
                    <tr class="bg-[#15803d] text-white text-xs font-semibold uppercase tracking-wider whitespace-nowrap">
                        <th class="p-[10px] text-center w-[50px] rounded-tl-2xl whitespace-nowrap">SL</th>
                        <th class="p-[10px] text-start w-[125px] whitespace-nowrap">Invoice No</th>
                        <th class="p-[10px] text-start whitespace-nowrap">Customer Info</th>
                        <th class="p-[10px] text-start whitespace-nowrap">Financial Summary</th>
                        <th class="p-[10px] text-start w-[115px] whitespace-nowrap">Created By</th>
                        <th class="p-[10px] text-start w-[115px] whitespace-nowrap">Date</th>
                        <th class="p-[10px] text-center w-[110px] whitespace-nowrap">Status</th>
                        <th class="p-[10px] text-center w-[160px] rounded-tr-2xl whitespace-nowrap">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-200">
                    @forelse($orders as $idx => $order)
                        @php
                            $subTotal = (float) ($order->sub_total ?? 0);
                            $discountAmount = (float) ($order->discount_amount ?? 0);
                            $paidAmount = (float) ($order->paid_amount ?? 0);
                            $dueAmount = (float) ($order->due_amount ?? 0);
                            $discountType = $order->discount_type ?? 'fixed';
                            $discountRate = $order->discount_rate !== null ? (float) $order->discount_rate : null;
                            $discountDisplay = Money::formatBd($discountAmount);
                            if ($discountType === 'percent' && $discountRate !== null && $discountRate > 0) {
                                $cleanRate = (int) $discountRate == $discountRate ? (int) $discountRate : rtrim(rtrim(number_format($discountRate, 2, '.', ''), '0'), '.');
                                $discountDisplay = $cleanRate . '% (' . Money::formatBd($discountAmount, false) . '৳)';
                            }

                            if ($dueAmount <= 0.009 && $paidAmount > 0) {
                                $paymentStatus = 'Fully Paid';
                                $statusBadgeClass = 'status-badge-paid bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400';
                            } elseif ($dueAmount > 0.009 && $paidAmount > 0) {
                                $paymentStatus = 'Partial Paid';
                                $statusBadgeClass = 'status-badge-partial bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400';
                            } elseif ($dueAmount > 0.009 && $paidAmount <= 0.009) {
                                $paymentStatus = 'Unpaid';
                                $statusBadgeClass = 'status-badge-unpaid bg-rose-100 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400';
                            } else {
                                $paymentStatus = 'Return';
                                $statusBadgeClass = 'status-badge-return bg-purple-100 text-purple-700 dark:bg-purple-950/40 dark:text-purple-400';
                            }
                        @endphp
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors border-b border-slate-100 dark:border-slate-800">
                            <td class="p-[10px] text-center font-bold text-slate-500 dark:text-slate-400">{{ ($orders->firstItem() ?? 1) + $idx }}</td>
                            <td class="p-[10px] font-bold text-slate-800 dark:text-slate-100 whitespace-nowrap">{{ $order->order_no ?? '-' }}</td>
                            <td class="p-[10px]">
                                @if($order->customer?->id)
                                    <a href="{{ route('battery.customer.profile', $order->customer->id) }}" class="text-emerald-700 dark:text-emerald-400 font-bold hover:underline" title="View Customer Profile">
                                        <div>{{ $order->customer->customer_name ?? 'Walk-in Customer' }}</div>
                                    </a>
                                @else
                                    <div class="font-bold text-slate-800 dark:text-slate-100">{{ $order->customer?->customer_name ?? 'Walk-in Customer' }}</div>
                                @endif
                                <div class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1 mt-0.5">
                                    <i class="fa-solid fa-phone text-[10px]"></i>
                                    <span>{{ $order->customer?->mobile ?? '-' }}</span>
                                </div>
                                @if($order->customer?->customer_id)
                                    <a href="{{ route('battery.customer.profile', $order->customer->id) }}" class="inline-block mt-1">
                                        <span class="customer-id-badge inline-flex items-center gap-1 px-1.5 py-0.5 rounded font-bold text-[10px] bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                            ID: {{ $order->customer->customer_id }} <i class="fa-solid fa-arrow-up-right-from-square text-[8px]"></i>
                                        </span>
                                    </a>
                                @endif
                            </td>
                            <td class="p-[10px] text-xs">
                                <div class="text-slate-700 dark:text-slate-200 whitespace-nowrap">Total: <span class="font-bold text-slate-900 dark:text-white">{{ Money::formatBd($subTotal) }}</span></div>
                                <div class="text-slate-500 dark:text-slate-400 whitespace-nowrap">Discount: {{ $discountDisplay }}</div>
                                <div class="text-emerald-600 dark:text-emerald-400 font-medium whitespace-nowrap">Paid: <span class="font-bold">{{ Money::formatBd($paidAmount) }}</span></div>
                                @if($dueAmount > 0.009)
                                    <div class="text-rose-600 dark:text-rose-400 font-bold whitespace-nowrap">Due: {{ Money::formatBd($dueAmount) }}</div>
                                @endif
                            </td>
                            <td class="p-[10px] text-xs font-semibold text-slate-600 dark:text-slate-300 whitespace-nowrap">
                                <i class="fa-solid fa-user me-1 text-slate-400"></i>{{ $order->user?->name ?? 'System' }}
                            </td>
                            <td class="p-[10px] text-xs text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                <i class="fa-regular fa-calendar me-1 text-slate-400"></i>{{ $order->invoice_date ? \Carbon\Carbon::parse($order->invoice_date)->format('M d, Y') : ($order->created_at ? $order->created_at->format('M d, Y') : 'N/A') }}
                            </td>
                            <td class="p-[10px] text-center">
                                <span class="status-pill {{ $statusBadgeClass }}">
                                    {{ $paymentStatus }}
                                </span>
                            </td>
                            <td class="p-[10px] text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" wire:click="openView({{ $order->id }})" class="action-btn quick-view-btn w-[32px] h-[32px] rounded-lg flex items-center justify-center transition-all shadow-sm cursor-pointer" title="Quick View Invoice">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </button>
                                    <a href="{{ route('battery.invoice.show', $order->id) }}" target="_blank" class="action-btn action-btn-print w-[32px] h-[32px] rounded-lg flex items-center justify-center transition-all shadow-sm" title="Print Invoice">
                                        <i class="fa-solid fa-print text-xs"></i>
                                    </a>
                                    <button type="button" wire:click="openReturn({{ $order->id }})" class="action-btn action-btn-return w-[32px] h-[32px] rounded-lg flex items-center justify-center transition-all shadow-sm cursor-pointer" title="Sales Return">
                                        <i class="fa-solid fa-rotate-left text-xs"></i>
                                    </button>
                                    <button type="button" wire:click="openPayment({{ $order->id }})" class="action-btn action-btn-due w-[32px] h-[32px] rounded-lg flex items-center justify-center transition-all shadow-sm cursor-pointer" title="Due Collection / Payment">
                                        <i class="fa-solid fa-hand-holding-dollar text-xs"></i>
                                    </button>
                                    <button type="button" wire:click="openEdit({{ $order->id }})" class="action-btn action-btn-edit w-[32px] h-[32px] rounded-lg flex items-center justify-center transition-all shadow-sm cursor-pointer" title="Edit Invoice">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr data-empty-row="true"><td colspan="8" class="px-4 py-8 text-center text-rose-500 font-bold">❌ No Battery invoices found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- 4. Mobile & Tablet Cards (Reference Parity: 1-col Mobile, 2-col Tablet, 2x2 Financial Box) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:hidden gap-3 mb-3">
            @foreach($orders as $idx => $order)
                @php
                    $subTotal = (float) ($order->sub_total ?? 0);
                    $discountAmount = (float) ($order->discount_amount ?? 0);
                    $paidAmount = (float) ($order->paid_amount ?? 0);
                    $dueAmount = (float) ($order->due_amount ?? 0);
                    $discountType = $order->discount_type ?? 'fixed';
                    $discountRate = $order->discount_rate !== null ? (float) $order->discount_rate : null;
                    $discountDisplay = Money::formatBd($discountAmount);
                    if ($discountType === 'percent' && $discountRate !== null && $discountRate > 0) {
                        $cleanRate = (int) $discountRate == $discountRate ? (int) $discountRate : rtrim(rtrim(number_format($discountRate, 2, '.', ''), '0'), '.');
                        $discountDisplay = $cleanRate . '% (' . Money::formatBd($discountAmount, false) . '৳)';
                    }

                    if ($dueAmount <= 0.009 && $paidAmount > 0) {
                        $paymentStatus = 'Fully Paid';
                        $statusBadgeClass = 'status-badge-paid bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400';
                    } elseif ($dueAmount > 0.009 && $paidAmount > 0) {
                        $paymentStatus = 'Partial Paid';
                        $statusBadgeClass = 'status-badge-partial bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400';
                    } elseif ($dueAmount > 0.009 && $paidAmount <= 0.009) {
                        $paymentStatus = 'Unpaid';
                        $statusBadgeClass = 'status-badge-unpaid bg-rose-100 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400';
                    } else {
                        $paymentStatus = 'Return';
                        $statusBadgeClass = 'status-badge-return bg-purple-100 text-purple-700 dark:bg-purple-950/40 dark:text-purple-400';
                    }
                @endphp
                <div class="unified-ui-border rounded-2xl p-3.5 bg-white dark:bg-slate-800/80 shadow-2xs space-y-2 overflow-hidden">
                    <div class="flex items-center justify-between pb-2 mb-1 border-b border-slate-100 dark:border-slate-700 gap-1.5 flex-wrap">
                        <div class="flex items-center gap-1.5 flex-wrap min-w-0">
                            <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs shrink-0">#{{ ($orders->firstItem() ?? 1) + $idx }}</span>
                            <span class="font-bold text-slate-800 dark:text-slate-100 text-xs flex items-center gap-1 break-all">
                                <i class="fa-solid fa-file-invoice text-emerald-600 shrink-0"></i>{{ $order->order_no ?? '-' }}
                            </span>
                        </div>
                        <div class="shrink-0">
                            <span class="status-pill {{ $statusBadgeClass }}">
                                {{ $paymentStatus }}
                            </span>
                        </div>
                    </div>

                    <div class="min-w-0">
                        @if($order->customer?->id)
                            <a href="{{ route('battery.customer.profile', $order->customer->id) }}" class="text-emerald-700 dark:text-emerald-400 font-bold text-sm hover:underline block mb-0.5 break-words" title="View Customer Profile">
                                <i class="fa-solid fa-user-circle text-emerald-600 me-1"></i>{{ $order->customer->customer_name ?? 'Walk-in Customer' }}
                            </a>
                        @else
                            <div class="font-bold text-slate-800 dark:text-slate-100 text-sm mb-0.5 break-words">
                                <i class="fa-solid fa-user-circle text-emerald-600 me-1"></i>{{ $order->customer?->customer_name ?? 'Walk-in Customer' }}
                            </div>
                        @endif
                        <div class="flex items-center gap-2 text-slate-500 dark:text-slate-400 text-xs flex-wrap">
                            <span class="break-all"><i class="fa-solid fa-phone me-1"></i>{{ $order->customer?->mobile ?? '-' }}</span>
                            @if($order->customer?->customer_id)
                                <a href="{{ route('battery.customer.profile', $order->customer->id) }}" class="shrink-0">
                                    <span class="customer-id-badge px-1.5 py-0.5 rounded font-bold text-[10px] bg-slate-50 dark:bg-slate-800">ID: {{ $order->customer->customer_id }}</span>
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- 2x2 Grid Side-by-Side (Left-Right) with margin-top -->
                    <div class="mobile-fin-grid">
                        <div class="flex items-center justify-between px-2.5 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-900/70 border border-slate-200 dark:border-slate-700 min-w-0">
                            <span class="text-slate-500 dark:text-slate-400 text-xs font-semibold shrink-0">Total:</span>
                            <span class="font-bold text-slate-800 dark:text-slate-100 text-xs text-right break-words">{{ Money::formatBd($subTotal) }}</span>
                        </div>
                        <div class="flex items-center justify-between px-2.5 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-900/70 border border-slate-200 dark:border-slate-700 min-w-0">
                            <span class="text-slate-500 dark:text-slate-400 text-xs font-semibold shrink-0">Discount:</span>
                            <span class="font-bold text-slate-600 dark:text-slate-300 text-[11px] leading-tight text-right break-words" title="{{ $discountDisplay }}">{{ $discountDisplay }}</span>
                        </div>
                        <div class="flex items-center justify-between px-2.5 py-1.5 rounded-xl bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-slate-700 min-w-0">
                            <span class="text-emerald-600 dark:text-emerald-400 text-xs font-semibold shrink-0">Paid:</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400 text-xs text-right break-words">{{ Money::formatBd($paidAmount) }}</span>
                        </div>
                        <div class="flex items-center justify-between px-2.5 py-1.5 rounded-xl {{ $dueAmount > 0.009 ? 'bg-rose-50/50 dark:bg-rose-950/20 border border-rose-200 dark:border-slate-700' : 'bg-slate-50 dark:bg-slate-900/70 border border-slate-200 dark:border-slate-700' }} min-w-0">
                            <span class="{{ $dueAmount > 0.009 ? 'text-rose-600 dark:text-rose-400 font-bold' : 'text-slate-500 dark:text-slate-400' }} text-xs font-semibold shrink-0">Due:</span>
                            <span class="font-bold {{ $dueAmount > 0.009 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-500 dark:text-slate-400' }} text-xs text-right break-words">{{ Money::formatBd($dueAmount) }}</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2 mt-1 border-t border-slate-100 dark:border-slate-700">
                        <div class="text-slate-500 dark:text-slate-400 text-xs">
                            <i class="fa-regular fa-calendar me-1"></i>{{ $order->invoice_date ? \Carbon\Carbon::parse($order->invoice_date)->format('M d, Y') : ($order->created_at ? $order->created_at->format('M d, Y') : 'N/A') }}
                            <span class="ms-1">({{ $order->user?->name ?? 'System' }})</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <button type="button" wire:click="openView({{ $order->id }})" class="action-btn quick-view-btn w-[30px] h-[30px] rounded-lg flex items-center justify-center shadow-sm cursor-pointer" title="Quick View Invoice">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                            <a href="{{ route('battery.invoice.show', $order->id) }}" target="_blank" class="action-btn action-btn-print w-[30px] h-[30px] rounded-lg flex items-center justify-center shadow-sm" title="Print Invoice">
                                <i class="fa-solid fa-print text-xs"></i>
                            </a>
                            <button type="button" wire:click="openReturn({{ $order->id }})" class="action-btn action-btn-return w-[30px] h-[30px] rounded-lg flex items-center justify-center shadow-sm cursor-pointer" title="Sales Return">
                                <i class="fa-solid fa-rotate-left text-xs"></i>
                            </button>
                            <button type="button" wire:click="openPayment({{ $order->id }})" class="action-btn action-btn-due w-[30px] h-[30px] rounded-lg flex items-center justify-center shadow-sm cursor-pointer" title="Due Collection / Payment">
                                <i class="fa-solid fa-hand-holding-dollar text-xs"></i>
                            </button>
                            <button type="button" wire:click="openEdit({{ $order->id }})" class="action-btn action-btn-edit w-[30px] h-[30px] rounded-lg flex items-center justify-center shadow-sm cursor-pointer" title="Edit Invoice">
                                <i class="fa-solid fa-pen-to-square text-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- 5. Modern Smart Pagination & Display Info Footer (Reference Parity) --}}
        <div class="flex flex-col sm:flex-row items-center justify-between pt-4 mt-4 border-t border-slate-200 dark:border-slate-800 gap-3">
            <div class="flex items-center gap-1.5 text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400">
                <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
                <span>Showing</span>
                <span class="font-bold text-slate-800 dark:text-slate-100 px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700">{{ $orders->firstItem() ?? 0 }} – {{ $orders->lastItem() ?? 0 }}</span>
                <span>of</span>
                <span class="font-bold text-emerald-700 dark:text-emerald-400 px-1.5 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-slate-700">{{ $orders->total() }}</span>
                <span>invoices</span>
            </div>
            <div class="w-full sm:w-auto flex items-center justify-center sm:justify-end">
                {{ $orders->links() }}
            </div>
        </div>
    </div>

    {{-- Quick View Battery Invoice Details Modal (Exact Reference Project Design) --}}
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

    {{-- Battery Sales Return Modal --}}
    <x-ui.modal :show="$showReturnModal" max-width="lg" :title="'Battery Sales Return — ' . $returnOrderNo" icon="fa-solid fa-rotate-left" close-method="closeModals">
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

    <x-ui.modal :show="$showPaymentModal" :title="'Collect Battery Due — ' . $payOrderNo" icon="fa-solid fa-hand-holding-dollar" close-method="closeModals">
        <form wire:submit="submitPayment" class="space-y-3">
            <div class="p-3 rounded-xl bg-amber-50 flex justify-between text-sm font-bold"><span>Remaining Due:</span><span class="text-rose-600">{{ Money::formatBd($payCurrentDue) }}</span></div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="block text-xs font-bold mb-1">Paid (৳) *</label><input type="number" step="0.01" wire:model="payAmount" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-bold"></div>
                <div><label class="block text-xs font-bold mb-1">Discount (৳)</label><input type="number" step="0.01" wire:model="payDiscount" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"></div>
                <div><label class="block text-xs font-bold mb-1">Method</label><select wire:model="payMethod" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"><option value="Cash">Cash</option><option value="bKash">bKash</option><option value="Nagad">Nagad</option><option value="Bank">Bank</option></select></div>
                <div><label class="block text-xs font-bold mb-1">Date</label><x-ui.date-picker wire:model="payDate" /></div>
            </div>
            <div class="flex justify-end gap-2"><button type="button" wire:click="closeModals" class="h-[38px] px-4 rounded-lg bg-[#dc2626] text-white text-sm font-bold">Cancel</button><button type="submit" class="h-[38px] px-5 rounded-lg bg-[#15803d] text-white text-sm font-bold">Save Payment</button></div>
        </form>
    </x-ui.modal>

    <x-ui.modal :show="$showEditModal" max-width="full-edit" :title="'Edit Battery Invoice — ' . $editOrderNo" icon="fa-solid fa-pen-to-square" close-method="closeModals">
        <form wire:submit="submitFullEdit" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div><label class="block text-xs font-bold mb-1">Customer</label><select wire:model="editCustomerId" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">@foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->customer_name }}</option>@endforeach</select></div>
                <div><label class="block text-xs font-bold mb-1">Date</label><x-ui.date-picker wire:model="editDate" /></div>
                <div><label class="block text-xs font-bold mb-1">Challan / Note</label><input type="text" wire:model="editNote" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"></div>
            </div>
            <div class="flex gap-2">
                <select wire:model="editAddProductId" class="flex-1 h-9 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs"><option value="">+ Add Battery Product...</option>@foreach($allProducts as $ap)<option value="{{ $ap->id }}">{{ $ap->product_name }} (Qty: {{ $ap->quantity }})</option>@endforeach</select>
                <button type="button" wire:click="addEditProduct" class="h-9 px-4 rounded-xl bg-[#15803d] text-white text-xs font-bold">Add</button>
            </div>
            <table class="w-full text-xs border border-slate-200 dark:border-slate-700">
                <thead class="bg-[#15803d] text-white"><tr><th class="px-3 py-2 text-left">Product</th><th class="px-3 py-2 text-center w-24">Qty</th><th class="px-3 py-2 text-right w-32">Rate</th><th class="px-3 py-2 w-10"></th></tr></thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @foreach($editItems as $idx => $it)
                        <tr>
                            <td class="px-3 py-2 font-semibold">{{ $it['product_name'] }}</td>
                            <td class="px-3 py-2"><input type="number" min="1" wire:model="editItems.{{ $idx }}.quantity" class="w-20 h-8 text-center rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800"></td>
                            <td class="px-3 py-2"><input type="number" step="0.01" wire:model="editItems.{{ $idx }}.selling_price" class="w-28 h-8 text-right px-2 rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800"></td>
                            <td class="px-3 py-2 text-center"><button type="button" wire:click="removeEditItem({{ $idx }})" class="text-red-500"><i class="fa-solid fa-trash"></i></button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @php
                $editSubTotal = collect($editItems)->sum(fn ($it) => (float) ($it['selling_price'] ?? 0) * (float) ($it['quantity'] ?? 0));
                $editComputedDisc = $editDiscountType === 'percent' ? ($editSubTotal * (float) ($editDiscountValue ?: 0) / 100) : (float) ($editDiscountValue ?: 0);
            @endphp
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold">Discount</label>
                        @if($editDiscountType === 'percent' && $editComputedDisc > 0)
                            <span class="text-[11px] font-bold text-emerald-700 dark:text-emerald-400">({{ Money::formatBd($editComputedDisc, false) }}৳)</span>
                        @endif
                    </div>
                    <div class="flex">
                        <select wire:model.live="editDiscountType" class="no-custom-select h-10 px-2.5 rounded-l-xl border-[1.5px] border-r-0 border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold">
                            <option value="fixed">৳</option><option value="percent">%</option>
                        </select>
                        <input type="number" step="0.01" min="0" wire:model.live.debounce.200ms="editDiscountValue" class="w-full h-10 px-3 rounded-r-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                    </div>
                </div>
                <div><label class="block text-xs font-bold mb-1">Delivery</label><input type="number" step="0.01" wire:model="editDeliveryCharge" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"></div>
                <div><label class="block text-xs font-bold mb-1">Paid</label><input type="number" step="0.01" wire:model="editPaidAmount" @disabled($editHasMultiplePayments) class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm disabled:opacity-50"></div>
            </div>
            <div class="flex justify-end gap-2"><button type="button" wire:click="closeModals" class="h-[38px] px-4 rounded-lg bg-[#dc2626] text-white text-sm font-bold">Cancel</button><button type="submit" class="h-[38px] px-5 rounded-lg bg-[#15803d] text-white text-sm font-bold">Update</button></div>
        </form>
    </x-ui.modal>

    <style>
        .customer-id-badge {
            border: 1.5px solid #cbd5e1 !important;
        }
        body[light-mode="dark"] .customer-id-badge,
        html.dark .customer-id-badge {
            border-color: #334155 !important;
        }
        .status-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.25rem 0.65rem;
            border-radius: 9999px;
            font-size: 0.72rem;
            font-weight: 600;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            border: 1.5px solid #cbd5e1 !important;
        }
        body[light-mode="dark"] .status-pill,
        html.dark .status-pill {
            border-color: #334155 !important;
        }
        .action-btn {
            border: 1.5px solid #cbd5e1 !important;
            background-color: #f8fafc;
        }
        body[light-mode="dark"] .action-btn,
        html.dark .action-btn {
            border-color: #334155 !important;
            background-color: #1e293b;
        }
        .action-btn-print { color: #15803d; }
        .action-btn-print:hover { background-color: #dcfce7; border-color: #16a34a !important; color: #15803d; }
        .action-btn-return { color: #d97706; }
        .action-btn-return:hover { background-color: #fef3c7; border-color: #f59e0b !important; color: #d97706; }
        .action-btn-due { color: #2563eb; }
        .action-btn-due:hover { background-color: #dbeafe; border-color: #3b82f6 !important; color: #2563eb; }
        .action-btn-edit { color: #7c3aed; }
        .action-btn-edit:hover { background-color: #ede9fe; border-color: #8b5cf6 !important; color: #7c3aed; }

        .quick-view-btn {
            background-color: #f0f9ff !important;
            border: 1.5px solid #cbd5e1 !important;
            color: #0284c7 !important;
            transition: all 0.15s ease-in-out !important;
        }
        .quick-view-btn svg {
            stroke: #0284c7 !important;
            color: #0284c7 !important;
        }
        .quick-view-btn:hover {
            background-color: #e0f2fe !important;
            border-color: #0284c7 !important;
            color: #0284c7 !important;
        }
        body[light-mode="dark"] .quick-view-btn,
        html.dark .quick-view-btn {
            background-color: #0c4a6e !important;
            border-color: #0369a1 !important;
            color: #38bdf8 !important;
        }
        body[light-mode="dark"] .quick-view-btn svg,
        html.dark .quick-view-btn svg {
            stroke: #38bdf8 !important;
            color: #38bdf8 !important;
        }
        .mobile-fin-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 6px;
            margin-top: 8px;
        }
    </style>
</div>
