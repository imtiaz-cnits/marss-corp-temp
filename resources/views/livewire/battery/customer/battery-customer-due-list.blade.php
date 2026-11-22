@php
    use App\Support\Money;
    use Carbon\Carbon;

    $paymentOptions = [
        'Cash' => ['label' => 'Cash', 'icon' => 'fa-solid fa-money-bill text-emerald-600'],
        'bKash' => ['label' => 'bKash', 'icon' => 'fa-solid fa-mobile-screen text-pink-600'],
        'Nagad' => ['label' => 'Nagad', 'icon' => 'fa-solid fa-wallet text-orange-600'],
        'Rocket' => ['label' => 'Rocket', 'icon' => 'fa-solid fa-bolt text-purple-600'],
        'Bank' => ['label' => 'Bank Transfer', 'icon' => 'fa-solid fa-building-columns text-blue-600'],
    ];
    $activePaymentMeta = $paymentOptions[$payment_method] ?? $paymentOptions['Cash'];
@endphp

<div class="space-y-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-300 dark:border-slate-800 shadow-sm overflow-hidden transition-colors">
        <div class="p-3 sm:p-5 md:p-6">
            {{-- 1. Top Section: Page Title --}}
            <div class="flex items-center gap-2.5 mb-3">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400 flex items-center justify-center border border-emerald-100 dark:border-slate-800 shadow-xs shrink-0">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                        <line x1="2" y1="10" x2="22" y2="10"></line>
                    </svg>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-800 dark:text-white tracking-tight leading-none m-0">
                    {{ $activeTab === 'collection-list' ? 'Due Collection List' : 'Customer Due List' }}
                </h1>
            </div>

            {{-- 2. Single-Row Desktop Toolbar: Tabs on Left | Search + Show Entry on Right --}}
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-2.5 mb-4">
                <div class="flex items-center gap-2 overflow-x-auto pb-0.5 shrink-0">
                    <button
                        type="button"
                        wire:click="switchTab('due-list')"
                        class="inline-flex items-center gap-2 h-[38px] px-4 rounded-xl text-[13px] font-semibold border-[1.5px] whitespace-nowrap shrink-0 cursor-pointer transition-all {{ $activeTab === 'due-list' ? 'bg-[#15803d] border-[#15803d] text-white shadow-sm' : 'bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:border-emerald-600 hover:text-emerald-700' }}"
                    >
                        <i class="fa-regular fa-credit-card text-xs"></i>
                        <span>Due List</span>
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $activeTab === 'due-list' ? 'bg-white/25 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200' }}">{{ $dueListCount }}</span>
                    </button>

                    <button
                        type="button"
                        wire:click="switchTab('collection-list')"
                        class="inline-flex items-center gap-2 h-[38px] px-4 rounded-xl text-[13px] font-semibold border-[1.5px] whitespace-nowrap shrink-0 cursor-pointer transition-all {{ $activeTab === 'collection-list' ? 'bg-[#15803d] border-[#15803d] text-white shadow-sm' : 'bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:border-emerald-600 hover:text-emerald-700' }}"
                    >
                        <i class="fa-regular fa-file-lines text-xs"></i>
                        <span>Collection List</span>
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $activeTab === 'collection-list' ? 'bg-white/25 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200' }}">{{ $collectionListCount }}</span>
                    </button>
                </div>

                <div class="flex items-center gap-2.5 w-full lg:w-auto lg:justify-end">
                    <div class="flex-1 lg:flex-none lg:w-[270px] xl:w-[320px] h-[38px] flex items-center px-3 bg-white dark:bg-slate-800/90 rounded-xl unified-ui-border shadow-2xs focus-within:border-emerald-600 focus-within:ring-2 focus-within:ring-emerald-600/20 transition-all">
                        <i class="fa-solid fa-magnifying-glass text-slate-400 text-xs mr-2.5 shrink-0"></i>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="{{ $activeTab === 'collection-list' ? 'Search Due Collection...' : 'Search Customer Due...' }}"
                            class="w-full h-full bg-transparent border-0 outline-none text-xs sm:text-sm text-slate-800 dark:text-slate-100 placeholder:text-slate-400 focus:ring-0"
                        >
                    </div>

                    <div class="h-[38px] flex items-center gap-1.5 bg-white dark:bg-slate-800/90 px-3 rounded-xl unified-ui-border text-xs font-semibold text-slate-600 dark:text-slate-300 shadow-2xs hover:border-emerald-500 transition-all shrink-0">
                        <span class="text-slate-400 dark:text-slate-400 text-[11px] uppercase tracking-wider font-bold">SHOW:</span>
                        <select
                            wire:model.live="perPage"
                            class="no-custom-select bg-transparent border-0 text-xs sm:text-sm font-bold text-emerald-700 dark:text-emerald-400 focus:outline-none cursor-pointer pr-1"
                        >
                            <option value="15" class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">15</option>
                            <option value="50" class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">50</option>
                            <option value="100" class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">100</option>
                            <option value="200" class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">200</option>
                            <option value="500" class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">500</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- TAB PANE 1: CUSTOMER DUE LIST --}}
            @if($activeTab === 'due-list')
                <div class="hidden md:block w-full overflow-x-auto rounded-2xl unified-ui-border shadow-2xs bg-white dark:bg-slate-900">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-[#15803d] text-white text-xs font-semibold uppercase tracking-wider">
                                <th class="p-[10px] text-center w-[46px] rounded-tl-2xl whitespace-nowrap">SL</th>
                                <th class="p-[10px] text-start w-[135px] whitespace-nowrap">Customer ID</th>
                                <th class="p-[10px] text-start whitespace-nowrap">Customer Name</th>
                                <th class="p-[10px] text-start w-[150px] whitespace-nowrap">Customer Mobile</th>
                                <th class="p-[10px] text-end w-[150px] whitespace-nowrap">Previous Due Amount</th>
                                <th class="p-[10px] text-end w-[145px] whitespace-nowrap">Order Due Amount</th>
                                <th class="p-[10px] text-end w-[150px] whitespace-nowrap">Total Due Amount</th>
                                <th class="p-[10px] text-center w-[75px] rounded-tr-2xl whitespace-nowrap">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm text-slate-700 dark:text-slate-200">
                            @forelse($customers as $idx => $c)
                                @php
                                    $prevDue = Money::round($c->previous_due_amount ?? 0);
                                    $ordDue = Money::round($c->orders_sum_due_amount ?? 0);
                                    $totDue = Money::add($prevDue, $ordDue);
                                    $custCode = $c->customer_id ?: ('BCUST-' . str_pad((string) $c->id, 4, '0', STR_PAD_LEFT));
                                @endphp
                                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                                    <td class="p-[10px] text-center font-semibold text-slate-400 dark:text-slate-500">
                                        {{ ($customers->firstItem() ?? 1) + $idx }}
                                    </td>
                                    <td class="p-[10px] text-start whitespace-nowrap">
                                        <a href="{{ route('battery.customer.profile', $c->id) }}" wire:navigate class="font-mono font-bold text-xs text-emerald-600 dark:text-emerald-400 hover:underline inline-flex items-center gap-1.5" title="View Customer Profile">
                                            <i class="fa-regular fa-user text-[11px]"></i>
                                            <span>{{ $custCode }}</span>
                                        </a>
                                    </td>
                                    <td class="p-[10px] text-start">
                                        <a href="{{ route('battery.customer.profile', $c->id) }}" wire:navigate class="font-bold text-slate-800 dark:text-slate-100 hover:text-emerald-600 dark:hover:text-emerald-400 text-sm leading-snug">
                                            {{ $c->customer_name }}
                                        </a>
                                    </td>
                                    <td class="p-[10px] text-start font-medium text-slate-600 dark:text-slate-300 whitespace-nowrap font-mono text-xs sm:text-sm">{{ $c->mobile ?: '-' }}</td>
                                    <td class="p-[10px] text-end font-medium text-slate-700 dark:text-slate-300 whitespace-nowrap font-mono tabular-nums">{{ Money::formatBd($prevDue) }}</td>
                                    <td class="p-[10px] text-end font-medium text-slate-700 dark:text-slate-300 whitespace-nowrap font-mono tabular-nums">{{ Money::formatBd($ordDue) }}</td>
                                    <td class="p-[10px] text-end font-bold text-rose-600 dark:text-rose-400 whitespace-nowrap font-mono tabular-nums">{{ Money::formatBd($totDue) }}</td>
                                    <td class="p-[10px] text-center">
                                        <button
                                            type="button"
                                            wire:click="openCollect({{ $c->id }})"
                                            class="w-[30px] h-[30px] rounded-lg bg-emerald-50 hover:bg-emerald-600 text-emerald-600 hover:text-white border border-emerald-200/80 hover:border-emerald-600 dark:bg-emerald-950/40 dark:border-slate-700 dark:text-emerald-400 dark:hover:bg-emerald-600 dark:hover:text-white inline-flex items-center justify-center transition-all shadow-2xs cursor-pointer"
                                            title="Collect Customer Due"
                                        >
                                            <i class="fa-solid fa-dollar-sign text-xs"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="p-8 text-center text-rose-500 font-semibold">No customer dues found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-slate-50/90 dark:bg-slate-800/70 text-slate-800 dark:text-slate-100 font-bold border-t-2 border-emerald-600/30 dark:border-emerald-600/20 text-xs sm:text-sm">
                            <tr>
                                <td colspan="4" class="p-[10px] text-end text-slate-600 dark:text-slate-300">Total:</td>
                                <td class="p-[10px] text-end whitespace-nowrap font-mono tabular-nums">{{ Money::formatBd($totalPreviousDue) }}</td>
                                <td class="p-[10px] text-end whitespace-nowrap font-mono tabular-nums">{{ Money::formatBd($totalOrderDue) }}</td>
                                <td class="p-[10px] text-end text-rose-600 dark:text-rose-400 whitespace-nowrap font-mono tabular-nums">{{ Money::formatBd($grandDue) }}</td>
                                <td class="p-[10px]"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Mobile Cards (Tab 1) --}}
                <div class="block md:hidden space-y-3 mb-3">
                    @forelse($customers as $idx => $c)
                        @php
                            $prevDue = Money::round($c->previous_due_amount ?? 0);
                            $ordDue = Money::round($c->orders_sum_due_amount ?? 0);
                            $totDue = Money::add($prevDue, $ordDue);
                            $custCode = $c->customer_id ?: ('BCUST-' . str_pad((string) $c->id, 4, '0', STR_PAD_LEFT));
                        @endphp
                        <div class="unified-ui-border rounded-2xl p-3.5 bg-white dark:bg-slate-800/90 shadow-xs">
                            <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-100 dark:border-slate-700">
                                <div class="flex items-center gap-1.5">
                                    <span class="px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-[10px] font-bold">#{{ ($customers->firstItem() ?? 1) + $idx }}</span>
                                    <a href="{{ route('battery.customer.profile', $c->id) }}" wire:navigate class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold font-mono">
                                        <i class="fa-solid fa-user mr-1"></i>{{ $custCode }}
                                    </a>
                                </div>
                                <span class="px-2 py-0.5 rounded-full bg-rose-50 text-rose-600 border border-rose-200 text-[10px] font-bold">Due</span>
                            </div>
                            <div class="mb-2">
                                <a href="{{ route('battery.customer.profile', $c->id) }}" wire:navigate class="font-bold text-slate-800 dark:text-white text-sm hover:text-emerald-600">{{ $c->customer_name }}</a>
                                <p class="text-xs text-slate-500 mt-0.5 m-0">{{ $c->mobile ?: '-' }}</p>
                            </div>
                            <div class="grid grid-cols-3 gap-1.5 bg-slate-50 dark:bg-slate-900/60 p-2 rounded-xl text-center text-xs mb-2.5 border border-slate-100 dark:border-slate-700">
                                <div><span class="block text-[9px] font-bold text-slate-400 uppercase">Prev Due</span><strong class="font-mono">{{ Money::formatBd($prevDue) }}</strong></div>
                                <div><span class="block text-[9px] font-bold text-slate-400 uppercase">Order Due</span><strong class="font-mono">{{ Money::formatBd($ordDue) }}</strong></div>
                                <div><span class="block text-[9px] font-bold text-rose-500 uppercase">Total Due</span><strong class="font-mono text-rose-600">{{ Money::formatBd($totDue) }}</strong></div>
                            </div>
                            <div class="flex justify-end">
                                <button type="button" wire:click="openCollect({{ $c->id }})" class="px-3.5 h-8 rounded-lg bg-[#15803d] hover:bg-[#166534] text-white text-xs font-bold inline-flex items-center gap-1.5 border-0 cursor-pointer">
                                    <i class="fa-solid fa-dollar-sign"></i> Collect Due
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-rose-500 font-semibold bg-white dark:bg-slate-800 rounded-2xl unified-ui-border">No customer dues found.</div>
                    @endforelse
                </div>

                <div class="flex flex-col sm:flex-row items-center justify-between pt-4 mt-4 border-t border-slate-200 dark:border-slate-800 gap-3">
                    <div class="flex items-center gap-1.5 text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
                        <span>Showing</span>
                        <span class="font-bold text-slate-800 dark:text-slate-100 px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700">{{ $customers->firstItem() ?? 0 }} – {{ $customers->lastItem() ?? 0 }}</span>
                        <span>of</span>
                        <span class="font-bold text-emerald-700 dark:text-emerald-400 px-1.5 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-slate-700">{{ $customers->total() }}</span>
                        <span>customers</span>
                    </div>
                    <div class="max-w-full overflow-x-auto">{{ $customers->links() }}</div>
                </div>

            {{-- TAB PANE 2: DUE COLLECTION LIST --}}
            @else
                <div class="hidden md:block w-full overflow-x-auto rounded-2xl unified-ui-border shadow-2xs bg-white dark:bg-slate-900">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-[#15803d] text-white text-xs font-semibold uppercase tracking-wider">
                                <th class="p-[10px] text-center w-[46px] rounded-tl-2xl whitespace-nowrap">SL</th>
                                <th class="p-[10px] text-start w-[120px] whitespace-nowrap">Date</th>
                                <th class="p-[10px] text-start w-[130px] whitespace-nowrap">Customer ID</th>
                                <th class="p-[10px] text-start whitespace-nowrap">Customer Name</th>
                                <th class="p-[10px] text-end w-[150px] whitespace-nowrap">Previous Due Amount</th>
                                <th class="p-[10px] text-end w-[140px] whitespace-nowrap">Paid Amount</th>
                                <th class="p-[10px] text-end w-[140px] whitespace-nowrap">Due Amount</th>
                                <th class="p-[10px] text-center w-[125px] rounded-tr-2xl whitespace-nowrap">Payment Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm text-slate-700 dark:text-slate-200">
                            @forelse($collections as $idx => $row)
                                @php
                                    $prevAmt = Money::round($row->previous_due_amount ?? 0);
                                    $paidAmt = Money::round($row->paid_amount ?? 0);
                                    $dueAmt = Money::round($row->due_amount ?? 0);
                                    $st = $row->payment_status ?: (Money::cmp($dueAmt, 0) <= 0 ? 'Fully Paid' : (Money::cmp($paidAmt, 0) > 0 ? 'Partial Paid' : 'Unpaid'));
                                    $stClass = str_contains(strtolower($st), 'full') || strtolower($st) === 'paid'
                                        ? 'bg-emerald-500 text-white'
                                        : (str_contains(strtolower($st), 'part') ? 'bg-amber-500 text-white' : 'bg-rose-500 text-white');
                                    $custCode = $row->customer?->customer_id ?: ('BCUST-' . str_pad((string) ($row->customer_id ?? 0), 4, '0', STR_PAD_LEFT));
                                    $dateFmt = $row->due_collection_date ? Carbon::parse($row->due_collection_date)->format('d M Y') : '-';
                                @endphp
                                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                                    <td class="p-[10px] text-center font-semibold text-slate-400 dark:text-slate-500">{{ ($collections->firstItem() ?? 1) + $idx }}</td>
                                    <td class="p-[10px] text-start text-xs font-medium text-slate-600 dark:text-slate-300 whitespace-nowrap">{{ $dateFmt }}</td>
                                    <td class="p-[10px] text-start whitespace-nowrap">
                                        @if($row->customer_id)
                                            <a href="{{ route('battery.customer.profile', $row->customer_id) }}" wire:navigate class="font-mono font-bold text-xs text-emerald-600 dark:text-emerald-400 hover:underline">{{ $custCode }}</a>
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td class="p-[10px] text-start font-bold text-slate-800 dark:text-slate-100">
                                        @if($row->customer_id)
                                            <a href="{{ route('battery.customer.profile', $row->customer_id) }}" wire:navigate class="hover:text-emerald-600 dark:hover:text-emerald-400">{{ $row->customer?->customer_name ?? 'N/A' }}</a>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td class="p-[10px] text-end font-mono tabular-nums">{{ Money::formatBd($prevAmt) }}</td>
                                    <td class="p-[10px] text-end font-bold text-emerald-700 dark:text-emerald-400 font-mono tabular-nums">{{ Money::formatBd($paidAmt) }}</td>
                                    <td class="p-[10px] text-end font-bold text-rose-600 dark:text-rose-400 font-mono tabular-nums">{{ Money::formatBd($dueAmt) }}</td>
                                    <td class="p-[10px] text-center whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10.5px] font-bold {{ $stClass }}">{{ $st }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="p-8 text-center text-rose-500 font-semibold">No due collections found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-slate-50/90 dark:bg-slate-800/70 text-slate-800 dark:text-slate-100 font-bold border-t-2 border-emerald-600/30 dark:border-emerald-600/20 text-xs sm:text-sm">
                            <tr>
                                <td colspan="4" class="p-[10px] text-end text-slate-600 dark:text-slate-300">Sub Total:</td>
                                <td class="p-[10px] text-end whitespace-nowrap font-mono tabular-nums">{{ Money::formatBd($subTotalPayable) }}</td>
                                <td class="p-[10px] text-end text-emerald-700 dark:text-emerald-400 whitespace-nowrap font-mono tabular-nums">{{ Money::formatBd($subTotalPaid) }}</td>
                                <td class="p-[10px] text-end text-rose-600 dark:text-rose-400 whitespace-nowrap font-mono tabular-nums">{{ Money::formatBd($subTotalDue) }}</td>
                                <td class="p-[10px]"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Mobile Cards (Tab 2) --}}
                <div class="block md:hidden space-y-3 mb-3">
                    @forelse($collections as $idx => $row)
                        @php
                            $prevAmt = Money::round($row->previous_due_amount ?? 0);
                            $paidAmt = Money::round($row->paid_amount ?? 0);
                            $dueAmt = Money::round($row->due_amount ?? 0);
                            $dateFmt = $row->due_collection_date ? Carbon::parse($row->due_collection_date)->format('d M Y') : '-';
                        @endphp
                        <div class="unified-ui-border rounded-2xl p-3.5 bg-white dark:bg-slate-800/90 shadow-xs text-xs">
                            <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-100 dark:border-slate-700">
                                <span class="font-bold text-slate-800 dark:text-white">{{ $row->customer?->customer_name ?? 'N/A' }}</span>
                                <span class="text-slate-500">{{ $dateFmt }}</span>
                            </div>
                            <div class="grid grid-cols-3 gap-2 text-center">
                                <div><span class="block text-[9px] text-slate-400 uppercase font-bold">Prev Due</span><strong class="font-mono">{{ Money::formatBd($prevAmt) }}</strong></div>
                                <div><span class="block text-[9px] text-emerald-600 uppercase font-bold">Paid</span><strong class="font-mono text-emerald-600">{{ Money::formatBd($paidAmt) }}</strong></div>
                                <div><span class="block text-[9px] text-rose-500 uppercase font-bold">Due</span><strong class="font-mono text-rose-600">{{ Money::formatBd($dueAmt) }}</strong></div>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-rose-500 font-semibold bg-white dark:bg-slate-800 rounded-2xl unified-ui-border">No due collections found.</div>
                    @endforelse
                </div>

                <div class="flex flex-col sm:flex-row items-center justify-between pt-4 mt-4 border-t border-slate-200 dark:border-slate-800 gap-3">
                    <div class="flex items-center gap-1.5 text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
                        <span>Showing</span>
                        <span class="font-bold text-slate-800 dark:text-slate-100 px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700">{{ $collections->firstItem() ?? 0 }} – {{ $collections->lastItem() ?? 0 }}</span>
                        <span>of</span>
                        <span class="font-bold text-emerald-700 dark:text-emerald-400 px-1.5 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-slate-700">{{ $collections->total() }}</span>
                        <span>collections</span>
                    </div>
                    <div class="max-w-full overflow-x-auto">{{ $collections->links() }}</div>
                </div>
            @endif
        </div>
    </div>

    {{-- MODAL: Collect Customer Due --}}
    @if($showCollectModal)
        <div data-marss-modal="true" class="fixed inset-0 z-50 !m-0 overflow-hidden bg-slate-950/60 backdrop-blur-[2px] flex items-center justify-center p-3 sm:p-4" wire:click.self="closeModal">
            <div class="w-full max-w-[540px] max-h-[90vh] bg-white dark:bg-slate-900 rounded-[18px] shadow-2xl overflow-hidden flex flex-col border border-slate-200 dark:border-slate-800">
                <div class="bg-gradient-to-r from-[#16a34a] to-[#15803d] px-4 py-3 flex items-center justify-between shrink-0 sticky top-0 z-20">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-white/20 text-white flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-money-bill-wave text-sm"></i>
                        </div>
                        <h3 class="text-base font-bold text-white tracking-tight m-0">Collect Customer Due</h3>
                    </div>
                    <button type="button" wire:click="closeModal" class="w-8 h-8 rounded-full bg-red-600 hover:bg-red-700 text-white flex items-center justify-center border-0 shadow-xs cursor-pointer transition-transform hover:scale-105" title="Close">
                        <i class="fa-solid fa-xmark text-xs"></i>
                    </button>
                </div>

                <form wire:submit="submitCollect" class="flex flex-col flex-1 overflow-hidden min-h-0 m-0">
                    <div class="p-4 sm:p-5 space-y-3.5 overflow-y-auto flex-1 min-h-0 text-left">
                        <div class="p-3.5 rounded-2xl bg-emerald-50/80 dark:bg-slate-800/80 border-[1.5px] border-emerald-200 dark:border-slate-700">
                            <div class="flex items-center justify-between gap-2 mb-2.5">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 inline-flex items-center justify-center text-xs shrink-0">
                                        <i class="fa-solid fa-user-tie"></i>
                                    </span>
                                    <span class="font-bold text-slate-800 dark:text-slate-100 text-sm truncate">{{ $collectCustomerName }}</span>
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full bg-white dark:bg-slate-900 text-emerald-700 dark:text-emerald-400 border border-emerald-300 dark:border-emerald-800 font-mono text-xs font-bold shrink-0">
                                    {{ $collectCustomerCode }}
                                </span>
                            </div>

                            <div class="rounded-xl overflow-hidden border-[1.5px] border-emerald-200 dark:border-slate-700 bg-white dark:bg-slate-900">
                                <table class="w-full text-left border-collapse">
                                    <thead class="bg-slate-100 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                                        <tr class="text-[10.5px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                            <th class="px-3 py-2">Previous Due</th>
                                            <th class="px-3 py-2">Invoice Due</th>
                                            <th class="px-3 py-2 text-end">Total Target Due</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="text-[13px] font-bold text-slate-700 dark:text-slate-200 font-mono">
                                            <td class="px-3 py-2.5">{{ Money::formatBd($collectPreviousDue) }}</td>
                                            <td class="px-3 py-2.5">{{ Money::formatBd($collectOrderDue) }}</td>
                                            <td class="px-3 py-2.5 text-end font-extrabold text-rose-600 dark:text-rose-400">{{ Money::formatBd($collectTotalDue) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-2.5 pt-2.5 border-t border-emerald-200/80 dark:border-slate-700 flex items-center justify-between gap-2">
                                <span class="text-xs font-bold text-slate-600 dark:text-slate-300">
                                    Remaining Due:
                                    <strong class="text-emerald-700 dark:text-emerald-400 font-extrabold text-sm font-mono ml-1">{{ Money::formatBd($remainingDue) }}</strong>
                                </span>
                                <span class="px-3 py-0.5 rounded-full text-xs font-bold text-white {{ $collectStatus === 'Fully Paid' ? 'bg-emerald-600' : ($collectStatus === 'Partial Paid' ? 'bg-amber-500' : 'bg-rose-500') }}">
                                    {{ $collectStatus }}
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Collected Amount (৳) <span class="text-rose-500">*</span></label>
                                <input type="number" step="any" min="0" wire:model.live="paid_amount" placeholder="0.00" required class="w-full h-[38px] px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-bold text-emerald-700 dark:text-emerald-400 outline-none focus:border-[#15803d]">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Discount / Waiver (৳)</label>
                                <input type="number" step="any" min="0" wire:model.live="discount_amount" placeholder="0.00" class="w-full h-[38px] px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-semibold text-slate-800 dark:text-slate-100 outline-none focus:border-[#15803d]">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div
                                x-data="{
                                    open: false,
                                    top: 0,
                                    left: 0,
                                    width: 200,
                                    toggle() {
                                        if (this.open) { this.open = false; return; }
                                        const r = this.$refs.pmBtn.getBoundingClientRect();
                                        const menuH = 205;
                                        this.width = r.width;
                                        this.left = r.left;
                                        this.top = (r.bottom + menuH > window.innerHeight - 10)
                                            ? Math.max(10, r.top - menuH - 4)
                                            : (r.bottom + 4);
                                        this.open = true;
                                    }
                                }"
                                @click.outside="open = false"
                                @scroll.window="open = false"
                                @resize.window="open = false"
                                class="relative"
                            >
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Payment Method</label>
                                <button
                                    type="button"
                                    x-ref="pmBtn"
                                    @click="toggle()"
                                    class="w-full h-[38px] px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 flex items-center justify-between text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 hover:border-emerald-500 cursor-pointer"
                                >
                                    <span class="flex items-center gap-2">
                                        <i class="{{ $activePaymentMeta['icon'] }}"></i>
                                        <span>{{ $activePaymentMeta['label'] }}</span>
                                    </span>
                                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transition-transform" :class="open ? 'rotate-180' : ''"></i>
                                </button>
                                <template x-teleport="body">
                                    <div
                                        x-show="open"
                                        x-transition.opacity.duration.120ms
                                        @click.outside="open = false"
                                        :style="`position: fixed; top: ${top}px; left: ${left}px; width: ${width}px; z-index: 999999;`"
                                        style="display: none;"
                                        class="rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-2xl p-1"
                                    >
                                        @foreach($paymentOptions as $pmKey => $pmMeta)
                                            <button
                                                type="button"
                                                wire:click="$set('payment_method', '{{ $pmKey }}')"
                                                @click="open = false"
                                                class="w-full px-3 py-2 rounded-lg text-left text-xs sm:text-sm font-medium flex items-center justify-between border-0 bg-transparent cursor-pointer {{ $payment_method === $pmKey ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 font-bold' : 'text-slate-700 dark:text-slate-200 hover:bg-emerald-50/70 dark:hover:bg-slate-700' }}"
                                            >
                                                <span class="flex items-center gap-2">
                                                    <i class="{{ $pmMeta['icon'] }} w-4 text-center"></i>
                                                    <span>{{ $pmMeta['label'] }}</span>
                                                </span>
                                                @if($payment_method === $pmKey)<i class="fa-solid fa-check text-xs text-emerald-600"></i>@endif
                                            </button>
                                        @endforeach
                                    </div>
                                </template>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Collection Date</label>
                                <x-ui.date-picker
                                    wire:model="date"
                                    placeholder="DD-MM-YYYY"
                                    alt-format="d-m-Y"
                                    class="h-[38px] pl-8 pr-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs sm:text-sm font-medium text-slate-800 dark:text-slate-100 outline-none cursor-pointer"
                                />
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Transaction ID / Note</label>
                            <input type="text" wire:model="transaction_id" placeholder="e.g. TrxID / Receipt No" class="w-full h-[38px] px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs sm:text-sm text-slate-800 dark:text-slate-100 outline-none focus:border-[#15803d]">
                        </div>
                    </div>

                    <div class="px-5 py-3 bg-slate-50 dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between gap-2 shrink-0 sticky bottom-0 z-20">
                        <button type="button" wire:click="closeModal" class="inline-flex items-center gap-1.5 h-[38px] px-4 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold border-0 cursor-pointer">
                            <i class="fa-solid fa-xmark"></i>
                            <span>Cancel</span>
                        </button>
                        <button type="submit" class="inline-flex items-center gap-1.5 h-[38px] px-5 rounded-xl bg-[#15803d] hover:bg-[#166534] text-white text-xs font-bold border-0 cursor-pointer shadow-xs">
                            <i class="fa-solid fa-check"></i>
                            <span>Submit Collection</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
