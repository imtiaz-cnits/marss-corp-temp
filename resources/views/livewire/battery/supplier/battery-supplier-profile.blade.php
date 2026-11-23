@php
    use App\Support\Money;
    use Carbon\Carbon;

    $defaultSupplierImg = asset('backend/assets/img/demo-img.jpeg');
    $hasAvatar = $supplier->img_url && file_exists(public_path($supplier->img_url));
    $avatarUrl = $hasAvatar ? asset($supplier->img_url) : $defaultSupplierImg;

    $targetDueAmount = match ($collection_type) {
        'previous' => $balance['previous_due'],
        'purchase' => $balance['purchases_due'],
        default => $balance['total_due'],
    };

    $paymentMethodOptions = [
        'Cash' => ['label' => 'Cash', 'icon' => 'fa-solid fa-money-bill text-emerald-600'],
        'Bank' => ['label' => 'Bank Transfer', 'icon' => 'fa-solid fa-building-columns text-blue-600'],
        'bKash' => ['label' => 'bKash', 'icon' => 'fa-solid fa-mobile-screen text-pink-600'],
        'Nagad' => ['label' => 'Nagad', 'icon' => 'fa-solid fa-wallet text-orange-600'],
        'Rocket' => ['label' => 'Rocket', 'icon' => 'fa-solid fa-bolt text-purple-600'],
        'Cheque' => ['label' => 'Cheque', 'icon' => 'fa-solid fa-money-check text-slate-600'],
    ];
    $activePayMethod = $paymentMethodOptions[$payment_method] ?? $paymentMethodOptions['Cash'];
@endphp

<div>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-300 dark:border-slate-800 shadow-xs overflow-hidden p-3 sm:p-6">
        {{-- 1. Header Bar: Title Left, Pay Due Right --}}
        <div class="flex flex-wrap items-center justify-between gap-3 pb-3 mb-4 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400 flex items-center justify-center border border-emerald-100 dark:border-slate-800 shadow-2xs shrink-0">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="1" y="3" width="15" height="13"></rect>
                        <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                        <circle cx="5.5" cy="18.5" r="2.5"></circle>
                        <circle cx="18.5" cy="18.5" r="2.5"></circle>
                    </svg>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-800 dark:text-white tracking-tight leading-none m-0">Supplier Profile</h1>
            </div>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    wire:click="openPay"
                    class="inline-flex items-center justify-center gap-1.5 h-[38px] px-4 rounded-xl bg-[#15803d] hover:bg-[#166534] text-white font-bold text-[13px] shadow-xs cursor-pointer border-0 transition-all whitespace-nowrap"
                >
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="6" width="20" height="12" rx="2"></rect>
                        <circle cx="12" cy="12" r="2"></circle>
                        <path d="M6 12h.01M18 12h.01"></path>
                    </svg>
                    <span>Pay Due</span>
                </button>
            </div>
        </div>

        {{-- 2. Supplier Details & Net Payable Balance Box --}}
        <div class="unified-ui-border rounded-2xl bg-white dark:bg-slate-900 p-4 sm:p-5 mb-4 shadow-2xs">
            <div class="flex flex-col md:flex-row items-center justify-between gap-5">
                <div class="flex flex-col md:flex-row items-center md:items-start gap-4 md:gap-5 flex-1 min-w-0">
                    <div class="shrink-0 pt-0.5">
                        <img
                            src="{{ $avatarUrl }}"
                            alt="{{ $supplier->name }}"
                            wire:click="openImageModal('{{ $avatarUrl }}', '{{ addslashes($supplier->name) }}')"
                            class="w-20 h-20 rounded-full object-cover border-2 border-emerald-300 dark:border-emerald-600 shadow-sm cursor-pointer hover:scale-105 transition-transform"
                            title="Click to preview photo"
                        >
                    </div>
                    <div class="flex flex-col items-center md:items-start text-center md:text-left space-y-1.5 min-w-0">
                        <div class="flex flex-wrap items-center justify-center md:justify-start gap-2">
                            <h2 class="text-lg sm:text-xl font-bold text-slate-800 dark:text-white m-0 leading-tight">{{ $supplier->name }}</h2>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold font-mono bg-emerald-50 text-[#15803d] border border-emerald-300 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-700">{{ $supplier->supplier_id ?: ('BAT-SUP-'.$supplier->id) }}</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold border bg-emerald-100 text-emerald-700 border-emerald-300 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-700">Active</span>
                        </div>
                        <div class="text-slate-700 dark:text-slate-300 text-xs sm:text-sm font-medium inline-flex items-center gap-2">
                            <span class="w-6 h-6 rounded-md bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400 inline-flex items-center justify-center text-xs shrink-0"><i class="fa-solid fa-building"></i></span>
                            <span>Company: {{ $supplier->company ?: 'N/A' }}</span>
                        </div>
                        <div class="text-slate-700 dark:text-slate-300 text-xs sm:text-sm font-medium inline-flex items-center gap-2">
                            <span class="w-6 h-6 rounded-md bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400 inline-flex items-center justify-center text-xs shrink-0"><i class="fa-solid fa-phone"></i></span>
                            <span>{{ $supplier->mobile ?: 'N/A' }}</span>
                        </div>
                        <div class="text-slate-700 dark:text-slate-300 text-xs sm:text-sm font-medium inline-flex items-center gap-2">
                            <span class="w-6 h-6 rounded-md bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400 inline-flex items-center justify-center text-xs shrink-0"><i class="fa-solid fa-location-dot"></i></span>
                            <span>{{ $supplier->address ?: 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Right: Net Payable Balance Box --}}
                <div class="p-4 sm:p-5 rounded-2xl bg-gradient-to-br from-rose-50/80 to-slate-50 dark:from-rose-950/30 dark:to-slate-800/80 border border-rose-200/80 dark:border-rose-900/40 min-w-[230px] text-center md:text-right flex flex-col justify-center shadow-2xs shrink-0">
                    <span class="text-[10.5px] uppercase font-bold tracking-wider text-slate-500 dark:text-slate-400 block mb-1">Net Payable Balance</span>
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-rose-600 dark:text-rose-400 m-0 leading-none tabular-nums whitespace-nowrap">{{ Money::formatBd($balance['total_due']) }}</h3>
                    <span class="text-[11px] text-slate-400 dark:text-slate-500 block mt-1.5">Total Outstanding Due</span>
                </div>
            </div>
        </div>

        {{-- 3. 5 KPI Stat Cards Row --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-2.5 sm:gap-3 mb-4">
            <div class="unified-ui-border rounded-[14px] bg-white dark:bg-slate-900 px-3 py-2.5 flex items-center justify-between gap-2 shadow-2xs" style="border-left: 4px solid #0284c7 !important;">
                <div class="min-w-0">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Purchases</div>
                    <div class="text-base sm:text-lg font-extrabold text-sky-600 dark:text-sky-400 leading-tight">{{ $purchases->count() }}</div>
                    <div class="text-[10px] text-slate-400 font-medium">Invoices</div>
                </div>
                <div class="w-9 h-9 rounded-xl bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400 flex items-center justify-center shrink-0"><i class="fa-solid fa-cart-shopping text-sm"></i></div>
            </div>

            <div class="unified-ui-border rounded-[14px] bg-white dark:bg-slate-900 px-3 py-2.5 flex items-center justify-between gap-2 shadow-2xs" style="border-left: 4px solid #16a34a !important;">
                <div class="min-w-0">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Billed</div>
                    <div class="text-base sm:text-lg font-extrabold text-emerald-600 dark:text-emerald-400 leading-tight tabular-nums whitespace-nowrap">{{ Money::formatBd($balance['total_purchased']) }}</div>
                    <div class="text-[10px] text-slate-400 font-medium">Grand total</div>
                </div>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0"><i class="fa-solid fa-money-bill-wave text-sm"></i></div>
            </div>

            <div class="unified-ui-border rounded-[14px] bg-white dark:bg-slate-900 px-3 py-2.5 flex items-center justify-between gap-2 shadow-2xs" style="border-left: 4px solid #059669 !important;">
                <div class="min-w-0">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Paid</div>
                    <div class="text-base sm:text-lg font-extrabold text-teal-600 dark:text-teal-400 leading-tight tabular-nums whitespace-nowrap">{{ Money::formatBd($balance['total_paid']) }}</div>
                    <div class="text-[10px] text-slate-400 font-medium">Completed</div>
                </div>
                <div class="w-9 h-9 rounded-xl bg-teal-50 dark:bg-teal-950/50 text-teal-600 dark:text-teal-400 flex items-center justify-center shrink-0"><i class="fa-solid fa-circle-check text-sm"></i></div>
            </div>

            <div class="unified-ui-border rounded-[14px] bg-white dark:bg-slate-900 px-3 py-2.5 flex items-center justify-between gap-2 shadow-2xs" style="border-left: 4px solid #0d9488 !important;">
                <div class="min-w-0">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Return Credit</div>
                    <div class="text-base sm:text-lg font-extrabold text-teal-700 dark:text-teal-300 leading-tight tabular-nums whitespace-nowrap">{{ Money::formatBd($availableCredit) }}</div>
                    <div class="text-[10px] text-slate-400 font-medium truncate">Total: {{ Money::formatBd($balance['total_returns']) }} | Adj: {{ Money::formatBd($totalReturnsAdjusted) }}</div>
                </div>
                <div class="w-9 h-9 rounded-xl bg-teal-50 dark:bg-teal-950/50 text-teal-600 dark:text-teal-400 flex items-center justify-center shrink-0"><i class="fa-solid fa-truck-ramp-box text-sm"></i></div>
            </div>

            <div class="unified-ui-border rounded-[14px] bg-white dark:bg-slate-900 px-3 py-2.5 flex items-center justify-between gap-2 shadow-2xs" style="border-left: 4px solid #dc2626 !important;">
                <div class="min-w-0">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Net Payable Due</div>
                    <div class="text-base sm:text-lg font-extrabold text-rose-600 dark:text-rose-400 leading-tight tabular-nums whitespace-nowrap">{{ Money::formatBd($balance['total_due']) }}</div>
                    <div class="text-[10px] text-slate-400 font-medium">Outstanding</div>
                </div>
                <div class="w-9 h-9 rounded-xl bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0"><i class="fa-solid fa-circle-exclamation text-sm"></i></div>
            </div>
        </div>

        {{-- 4. Reference 3 Tab Buttons --}}
        <div class="flex items-center gap-2 overflow-x-auto pb-2 mb-3">
            <button
                type="button"
                wire:click="setTab('purchases')"
                class="inline-flex items-center gap-2 h-[38px] px-4 rounded-xl text-[13px] font-semibold border-[1.5px] whitespace-nowrap shrink-0 cursor-pointer transition-all {{ $activeTab === 'purchases' ? 'bg-[#15803d] border-[#15803d] text-white shadow-xs' : 'bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:border-emerald-500 hover:text-emerald-700' }}"
            >
                <i class="fa-regular fa-file-lines text-xs"></i>
                <span>Purchase Invoices</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $activeTab === 'purchases' ? 'bg-white/25 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200' }}">{{ $purchases->count() }}</span>
            </button>

            <button
                type="button"
                wire:click="setTab('returns')"
                class="inline-flex items-center gap-2 h-[38px] px-4 rounded-xl text-[13px] font-semibold border-[1.5px] whitespace-nowrap shrink-0 cursor-pointer transition-all {{ $activeTab === 'returns' ? 'bg-[#15803d] border-[#15803d] text-white shadow-xs' : 'bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:border-emerald-500 hover:text-emerald-700' }}"
            >
                <i class="fa-solid fa-rotate-left text-xs"></i>
                <span>Purchase Returns / Credit</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $activeTab === 'returns' ? 'bg-white/25 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200' }}">{{ $returns->count() }}</span>
            </button>

            <button
                type="button"
                wire:click="setTab('transactions')"
                class="inline-flex items-center gap-2 h-[38px] px-4 rounded-xl text-[13px] font-semibold border-[1.5px] whitespace-nowrap shrink-0 cursor-pointer transition-all {{ $activeTab === 'transactions' ? 'bg-[#15803d] border-[#15803d] text-white shadow-xs' : 'bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:border-emerald-500 hover:text-emerald-700' }}"
            >
                <i class="fa-solid fa-dollar-sign text-xs"></i>
                <span>Payment & Transaction History</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $activeTab === 'transactions' ? 'bg-white/25 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200' }}">{{ $transactions->count() }}</span>
            </button>
        </div>

        {{-- 5. Tab Panes --}}
        @if($activeTab === 'purchases')
            <div class="table-responsive unified-ui-border hidden lg:block w-full overflow-x-auto rounded-2xl shadow-2xs bg-white dark:bg-slate-900">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#15803d] text-white text-[11px] font-bold uppercase tracking-wider whitespace-nowrap">
                            <th class="p-[10px] text-center w-[46px] rounded-tl-2xl">SL</th>
                            <th class="p-[10px] text-center w-[46px]">Img</th>
                            <th class="p-[10px] text-center">Purchase ID</th>
                            <th class="p-[10px] text-center">Date</th>
                            <th class="p-[10px] text-start">Reference</th>
                            <th class="p-[10px] text-end">Grand Total</th>
                            <th class="p-[10px] text-end">Paid Amount</th>
                            <th class="p-[10px] text-end">Due Amount</th>
                            <th class="p-[10px] text-center">Status</th>
                            <th class="p-[10px] text-center w-[60px] rounded-tr-2xl">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-[13px] text-slate-700 dark:text-slate-200">
                        @forelse($purchases as $idx => $p)
                            @php
                                $grand = (float) ($p->grand_subtotal ?? 0);
                                $paid = (float) ($p->paid_amount ?? 0);
                                $due = (float) ($p->due_amount ?? 0);
                                $retAdj = (float) ($p->return_adjustment_amount ?? 0);
                                $pStatus = $due <= 0.009 && ($paid + $retAdj) > 0 ? 'Fully Paid' : (($paid + $retAdj) > 0 ? 'Partial Paid' : 'Unpaid');
                                $pStatusClass = $pStatus === 'Fully Paid'
                                    ? 'bg-emerald-100 text-emerald-700 border-emerald-300 dark:bg-emerald-950/50 dark:text-emerald-300'
                                    : ($pStatus === 'Partial Paid'
                                        ? 'bg-amber-100 text-amber-800 border-amber-300 dark:bg-amber-950/50 dark:text-amber-300'
                                        : 'bg-rose-100 text-rose-700 border-rose-300 dark:bg-rose-950/50 dark:text-rose-300');
                                $hasDoc = $p->attach_document && file_exists(public_path($p->attach_document));
                                $docUrl = $hasDoc ? asset($p->attach_document) : null;
                            @endphp
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/60 transition-colors">
                                <td class="p-[10px] text-center font-bold text-slate-500">{{ $idx + 1 }}</td>
                                <td class="p-[10px] text-center">
                                    @if($docUrl)
                                        <button type="button" wire:click="openImageModal('{{ $docUrl }}', 'Purchase #{{ $p->purchase_id }}')" class="w-7 h-7 rounded-full border border-slate-200 dark:border-slate-700 overflow-hidden mx-auto shadow-2xs cursor-pointer hover:scale-110 transition-transform bg-slate-100 flex items-center justify-center" title="Click to view document">
                                            <img src="{{ $docUrl }}" alt="Doc" class="w-full h-full object-cover">
                                        </button>
                                    @else
                                        <div class="w-7 h-7 rounded-full border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-300 dark:text-slate-600 flex items-center justify-center mx-auto text-[10px]" title="No Document"><i class="fa-solid fa-image"></i></div>
                                    @endif
                                </td>
                                <td class="p-[10px] text-center whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold font-mono bg-emerald-50 text-[#15803d] border border-emerald-300 dark:bg-emerald-950/50 dark:text-emerald-300">{{ $p->purchase_id }}</span>
                                </td>
                                <td class="p-[10px] text-center text-slate-600 dark:text-slate-300 whitespace-nowrap">{{ $p->date ? Carbon::parse($p->date)->format('d-m-Y') : 'N/A' }}</td>
                                <td class="p-[10px] text-start text-slate-600 dark:text-slate-300 font-medium">{{ $p->referance_no ?: 'N/A' }}</td>
                                <td class="p-[10px] text-end font-bold text-slate-800 dark:text-white whitespace-nowrap tabular-nums">{{ Money::formatBd($grand) }}</td>
                                <td class="p-[10px] text-end font-bold text-emerald-700 dark:text-emerald-400 whitespace-nowrap tabular-nums">
                                    {{ Money::formatBd($paid) }}
                                    @if($retAdj > 0)
                                        <div class="mt-0.5"><span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-teal-50 text-teal-700 border border-teal-200">+{{ Money::formatBd($retAdj) }} Adj</span></div>
                                    @endif
                                </td>
                                <td class="p-[10px] text-end font-bold whitespace-nowrap tabular-nums {{ $due > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-400' }}">{{ Money::formatBd($due) }}</td>
                                <td class="p-[10px] text-center whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold border {{ $pStatusClass }}">{{ $pStatus }}</span>
                                </td>
                                <td class="p-[10px] text-center">
                                    <a href="{{ route('battery.purchase.invoice.show', $p->id) }}" class="w-[30px] h-[30px] rounded-lg bg-emerald-50 hover:bg-[#15803d] text-[#15803d] hover:text-white border border-emerald-200 dark:bg-emerald-950/50 dark:border-emerald-800 dark:text-emerald-400 inline-flex items-center justify-center transition-all shadow-2xs" title="View Invoice">
                                        <i class="fa-regular fa-eye text-xs"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr data-empty-row="true">
                                <td colspan="10" class="py-10 text-center text-slate-400 font-medium"><i class="fa-solid fa-receipt block text-3xl opacity-40 mb-2"></i>No Battery purchase records found for this supplier</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:hidden gap-3 mb-3">
                @forelse($purchases as $idx => $p)
                    @php
                        $grand = (float) ($p->grand_subtotal ?? 0);
                        $paid = (float) ($p->paid_amount ?? 0);
                        $due = (float) ($p->due_amount ?? 0);
                        $retAdj = (float) ($p->return_adjustment_amount ?? 0);
                        $pStatus = $due <= 0.009 && ($paid + $retAdj) > 0 ? 'Fully Paid' : (($paid + $retAdj) > 0 ? 'Partial Paid' : 'Unpaid');
                        $pStatusClass = $pStatus === 'Fully Paid' ? 'bg-emerald-100 text-emerald-700 border-emerald-300' : ($pStatus === 'Partial Paid' ? 'bg-amber-100 text-amber-800 border-amber-300' : 'bg-rose-100 text-rose-700 border-rose-300');
                    @endphp
                    <div class="unified-ui-border rounded-2xl p-3.5 bg-white dark:bg-slate-800/70 shadow-2xs">
                        <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-100 dark:border-slate-700">
                            <div class="flex items-center gap-1.5">
                                <span class="px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-[11px] font-bold">#{{ $idx + 1 }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold font-mono bg-emerald-50 text-[#15803d] border border-emerald-300">{{ $p->purchase_id }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold border {{ $pStatusClass }}">{{ $pStatus }}</span>
                                <a href="{{ route('battery.purchase.invoice.show', $p->id) }}" class="w-7 h-7 rounded-lg bg-emerald-50 text-[#15803d] border border-emerald-200 inline-flex items-center justify-center" title="View Invoice"><i class="fa-regular fa-eye text-xs"></i></a>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-xs mb-2">
                            <div><span class="text-slate-400 block text-[11px]">Date:</span><span class="font-semibold">{{ $p->date ? Carbon::parse($p->date)->format('d-m-Y') : 'N/A' }}</span></div>
                            <div><span class="text-slate-400 block text-[11px]">Ref:</span><span class="font-medium">{{ $p->referance_no ?: 'N/A' }}</span></div>
                        </div>
                        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-between text-xs">
                            <div><span class="text-slate-400 block text-[10px] uppercase font-bold">Total</span><span class="font-extrabold whitespace-nowrap tabular-nums">{{ Money::formatBd($grand) }}</span></div>
                            <div class="text-center"><span class="text-slate-400 block text-[10px] uppercase font-bold">Paid</span><span class="font-extrabold text-emerald-600 whitespace-nowrap tabular-nums">{{ Money::formatBd($paid) }}</span></div>
                            <div class="text-end"><span class="text-slate-400 block text-[10px] uppercase font-bold">Due</span><span class="font-extrabold whitespace-nowrap tabular-nums {{ $due > 0 ? 'text-rose-600' : 'text-slate-400' }}">{{ Money::formatBd($due) }}</span></div>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-slate-400 rounded-2xl border border-slate-200 dark:border-slate-800">No Battery purchase records found</div>
                @endforelse
            </div>
        @elseif($activeTab === 'returns')
            <div class="table-responsive unified-ui-border hidden lg:block w-full overflow-x-auto rounded-2xl shadow-2xs bg-white dark:bg-slate-900">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#15803d] text-white text-[11px] font-bold uppercase tracking-wider whitespace-nowrap">
                            <th class="p-[10px] text-center w-[46px] rounded-tl-2xl">SL</th>
                            <th class="p-[10px] text-center">Return Date</th>
                            <th class="p-[10px] text-center">Purchase ID</th>
                            <th class="p-[10px] text-start">Returned Product</th>
                            <th class="p-[10px] text-center">Qty</th>
                            <th class="p-[10px] text-end rounded-tr-2xl">Credit / Refund</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-[13px] text-slate-700 dark:text-slate-200">
                        @forelse($returns as $idx => $ret)
                            @php
                                $purNo = $ret->purchase?->purchase_id ?: ($ret->return_number ?: ('#PurID'.str_pad((string) ($ret->purchase_id ?? 0), 5, '0', STR_PAD_LEFT)));
                                $retDate = $ret->created_at ? $ret->created_at->format('d-m-Y h:i A') : ($ret->date ? Carbon::parse($ret->date)->format('d-m-Y') : 'N/A');
                            @endphp
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/60 transition-colors">
                                <td class="p-[10px] text-center font-bold text-slate-500">{{ $idx + 1 }}</td>
                                <td class="p-[10px] text-center text-slate-700 dark:text-slate-300 whitespace-nowrap">{{ $retDate }}</td>
                                <td class="p-[10px] text-center whitespace-nowrap"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold font-mono bg-teal-50 text-teal-700 border border-teal-200 dark:bg-teal-950/50 dark:text-teal-300">{{ $purNo }}</span></td>
                                <td class="p-[10px] text-start font-bold text-slate-800 dark:text-white">{{ $ret->product?->product_name ?? 'N/A' }}</td>
                                <td class="p-[10px] text-center whitespace-nowrap"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/50 dark:text-blue-300">{{ (int) ($ret->quantity ?? 1) }} pcs</span></td>
                                <td class="p-[10px] text-end font-bold text-teal-700 dark:text-teal-300 whitespace-nowrap tabular-nums">{{ Money::formatBd($ret->amount ?? 0) }}</td>
                            </tr>
                        @empty
                            <tr data-empty-row="true">
                                <td colspan="6" class="py-10 text-center text-slate-400 font-medium"><i class="fa-solid fa-truck-ramp-box block text-3xl opacity-40 mb-2"></i>No purchase return records found for this supplier</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:hidden gap-3 mb-3">
                @forelse($returns as $idx => $ret)
                    @php
                        $purNo = $ret->purchase?->purchase_id ?: ($ret->return_number ?: ('#PurID'.str_pad((string) ($ret->purchase_id ?? 0), 5, '0', STR_PAD_LEFT)));
                        $retDate = $ret->created_at ? $ret->created_at->format('d-m-Y h:i A') : ($ret->date ? Carbon::parse($ret->date)->format('d-m-Y') : 'N/A');
                    @endphp
                    <div class="unified-ui-border rounded-2xl p-3.5 bg-white dark:bg-slate-800/70 shadow-2xs">
                        <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-100 dark:border-slate-700">
                            <div class="flex items-center gap-1.5">
                                <span class="px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 text-[11px] font-bold">#{{ $idx + 1 }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold font-mono bg-teal-50 text-teal-700 border border-teal-200">{{ $purNo }}</span>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">{{ (int) ($ret->quantity ?? 1) }} pcs</span>
                        </div>
                        <h4 class="font-bold text-slate-800 dark:text-white text-sm m-0">{{ $ret->product?->product_name ?? 'N/A' }}</h4>
                        <span class="text-slate-400 text-xs block mb-2">{{ $retDate }}</span>
                        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-between text-xs">
                            <span class="text-teal-800 dark:text-teal-300 font-semibold">Credit Amount</span>
                            <span class="font-extrabold text-teal-700 dark:text-teal-300 text-sm whitespace-nowrap tabular-nums">{{ Money::formatBd($ret->amount ?? 0) }}</span>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-slate-400 rounded-2xl border border-slate-200 dark:border-slate-800">No return records found</div>
                @endforelse
            </div>
        @else
            <div class="table-responsive unified-ui-border hidden lg:block w-full overflow-x-auto rounded-2xl shadow-2xs bg-white dark:bg-slate-900">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#15803d] text-white text-[11px] font-bold uppercase tracking-wider whitespace-nowrap">
                            <th class="p-[10px] text-center w-[46px] rounded-tl-2xl">SL</th>
                            <th class="p-[10px] text-center">Date & Time</th>
                            <th class="p-[10px] text-center">Invoice / Ref</th>
                            <th class="p-[10px] text-end">Paid Amount</th>
                            <th class="p-[10px] text-end">Discount</th>
                            <th class="p-[10px] text-start">Payment Method</th>
                            <th class="p-[10px] text-start">Transaction ID / Note</th>
                            <th class="p-[10px] text-center rounded-tr-2xl">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-[13px] text-slate-700 dark:text-slate-200">
                        @forelse($transactions as $idx => $trx)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/60 transition-colors">
                                <td class="p-[10px] text-center font-bold text-slate-500">{{ $idx + 1 }}</td>
                                <td class="p-[10px] text-center text-slate-600 dark:text-slate-300 whitespace-nowrap">{{ $trx['date_formatted'] }}</td>
                                <td class="p-[10px] text-center whitespace-nowrap"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold font-mono bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/50 dark:text-blue-300">{{ $trx['ref'] }}</span></td>
                                <td class="p-[10px] text-end font-bold text-emerald-700 dark:text-emerald-400 whitespace-nowrap tabular-nums">{{ Money::formatBd($trx['paid_amount']) }}</td>
                                <td class="p-[10px] text-end text-slate-500 dark:text-slate-400 whitespace-nowrap tabular-nums">{{ Money::formatBd($trx['discount_amount']) }}</td>
                                <td class="p-[10px] text-start text-slate-700 dark:text-slate-300 whitespace-nowrap"><i class="fa-solid fa-wallet mr-1.5 text-slate-400"></i>{{ $trx['payment_method'] }}</td>
                                <td class="p-[10px] text-start text-slate-500 dark:text-slate-400 text-xs">{{ $trx['transaction_id'] }}</td>
                                <td class="p-[10px] text-center whitespace-nowrap"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-300 dark:bg-emerald-950/50 dark:text-emerald-300">{{ $trx['status'] }}</span></td>
                            </tr>
                        @empty
                            <tr data-empty-row="true">
                                <td colspan="8" class="py-10 text-center text-slate-400 font-medium"><i class="fa-solid fa-hand-holding-dollar block text-3xl opacity-40 mb-2"></i>No payment transaction records found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:hidden gap-3 mb-3">
                @forelse($transactions as $idx => $trx)
                    <div class="unified-ui-border rounded-2xl p-3.5 bg-white dark:bg-slate-800/70 shadow-2xs">
                        <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-100 dark:border-slate-700">
                            <div class="flex items-center gap-1.5">
                                <span class="px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 text-[11px] font-bold">#{{ $idx + 1 }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold font-mono bg-blue-50 text-blue-700 border border-blue-200">{{ $trx['ref'] }}</span>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-300">{{ $trx['status'] }}</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-xs mb-2">
                            <div><span class="text-slate-400 block text-[11px]">Date:</span><span class="font-semibold">{{ $trx['date_formatted'] }}</span></div>
                            <div><span class="text-slate-400 block text-[11px]">Method:</span><span class="font-medium"><i class="fa-solid fa-wallet mr-1 text-slate-400"></i>{{ $trx['payment_method'] }}</span></div>
                        </div>
                        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-between text-xs">
                            <span class="text-emerald-800 dark:text-emerald-300 font-semibold">Paid: <strong class="text-emerald-700 dark:text-emerald-400 text-sm whitespace-nowrap tabular-nums">{{ Money::formatBd($trx['paid_amount']) }}</strong></span>
                            <span class="text-slate-500">Discount: <strong class="whitespace-nowrap tabular-nums">{{ Money::formatBd($trx['discount_amount']) }}</strong></span>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-slate-400 rounded-2xl border border-slate-200 dark:border-slate-800">No transactions found</div>
                @endforelse
            </div>
        @endif
    </div>

    {{-- Reference Parity: Pay Supplier Due Modal --}}
    @if($showPayModal)
        <div class="fixed inset-0 z-50 bg-slate-900/65 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 overflow-y-auto" wire:click.self="closeModal">
            <div class="w-full max-w-[620px] max-h-[90vh] bg-white dark:bg-slate-900 rounded-2xl shadow-2xl overflow-hidden flex flex-col my-auto border border-slate-200 dark:border-slate-800">
                <div class="sticky top-0 z-20 bg-[#15803d] text-white px-4 py-3 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-white/20 text-white flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-money-bill-wave text-sm"></i>
                        </div>
                        <h5 class="text-base font-bold text-white m-0">Pay Supplier Due</h5>
                    </div>
                    <button type="button" wire:click="closeModal" class="w-8 h-8 rounded-full bg-[#dc2626] hover:bg-red-700 text-white flex items-center justify-center border-0 cursor-pointer shadow-sm" title="Close">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>

                <form wire:submit="submitPay" id="batteryProfilePayForm" class="p-4 sm:p-5 overflow-y-auto flex-1 space-y-4">
                    <div class="rounded-[14px] bg-emerald-50/70 dark:bg-slate-800/80 border-[1.5px] border-emerald-200 dark:border-slate-700 p-3.5">
                        <div class="flex items-center justify-between mb-2.5">
                            <div class="flex items-center gap-2">
                                <span class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 inline-flex items-center justify-center text-xs"><i class="fa-solid fa-truck"></i></span>
                                <span class="font-bold text-slate-800 dark:text-slate-100 text-sm sm:text-base">{{ $supplier->name }}</span>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold font-mono bg-emerald-50 text-[#15803d] border border-emerald-300 dark:bg-emerald-950/50 dark:text-emerald-300">{{ $supplier->supplier_id ?: ('BAT-SUP-'.$supplier->id) }}</span>
                        </div>

                        <div class="rounded-xl overflow-hidden border-[1.5px] border-emerald-200 dark:border-slate-700 bg-white dark:bg-slate-900">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="bg-slate-100 dark:bg-slate-800 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                        <th class="py-2 px-3">Previous Due</th>
                                        <th class="py-2 px-3">Purchase Due</th>
                                        <th class="py-2 px-3 text-end text-rose-600 dark:text-rose-400">Total Target Due</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="font-semibold">
                                        <td class="py-2.5 px-3 text-slate-700 dark:text-slate-200 whitespace-nowrap tabular-nums">{{ Money::formatBd($balance['previous_due']) }}</td>
                                        <td class="py-2.5 px-3 text-slate-700 dark:text-slate-200 whitespace-nowrap tabular-nums">{{ Money::formatBd($balance['purchases_due']) }}</td>
                                        <td class="py-2.5 px-3 text-end font-extrabold text-rose-600 dark:text-rose-400 text-sm whitespace-nowrap tabular-nums">{{ Money::formatBd($targetDueAmount) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider mb-1.5">Payment Target <span class="text-rose-500">*</span></label>
                        <div class="grid grid-cols-3 gap-2">
                            <button type="button" wire:click="setCollectionType('all')" class="h-[38px] px-2.5 rounded-xl text-xs font-semibold border-[1.5px] flex items-center justify-center gap-1.5 cursor-pointer transition-all {{ $collection_type === 'all' ? 'bg-[#15803d] border-[#15803d] text-white' : 'bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-300' }}">
                                <i class="fa-solid fa-layer-group"></i><span>Both Dues</span>
                            </button>
                            <button type="button" wire:click="setCollectionType('previous')" class="h-[38px] px-2.5 rounded-xl text-xs font-semibold border-[1.5px] flex items-center justify-center gap-1.5 cursor-pointer transition-all {{ $collection_type === 'previous' ? 'bg-sky-600 border-sky-600 text-white' : 'bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-300' }}">
                                <i class="fa-solid fa-clock-rotate-left"></i><span>Previous Due</span>
                            </button>
                            <button type="button" wire:click="setCollectionType('purchase')" class="h-[38px] px-2.5 rounded-xl text-xs font-semibold border-[1.5px] flex items-center justify-center gap-1.5 cursor-pointer transition-all {{ $collection_type === 'purchase' ? 'bg-amber-600 border-amber-600 text-white' : 'bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-300' }}">
                                <i class="fa-solid fa-cart-shopping"></i><span>Purchase Due</span>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider mb-1.5">Payment Amount (৳) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" min="0" wire:model="paid_amount" placeholder="0.00" required class="w-full h-[38px] px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 font-bold text-emerald-700 dark:text-emerald-400 text-sm outline-none focus:border-[#15803d]">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider mb-1.5">Discount (৳)</label>
                            <input type="number" step="0.01" min="0" wire:model="discount_amount" placeholder="0.00" class="w-full h-[38px] px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm outline-none focus:border-[#15803d]">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider mb-1.5">Payment Date</label>
                            <x-ui.date-picker wire:model="date" placeholder="DD-MM-YYYY" alt-format="d-m-Y" class="h-[38px] pl-8 pr-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm outline-none focus:border-[#15803d]" />
                        </div>
                    </div>

                    <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider mb-1.5">Payment Method</label>
                        <button type="button" @click="open = !open" class="w-full h-[38px] px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-semibold text-slate-700 dark:text-slate-200 flex items-center justify-between cursor-pointer hover:border-emerald-500">
                            <span class="flex items-center gap-2"><i class="{{ $activePayMethod['icon'] }}"></i>{{ $activePayMethod['label'] }}</span>
                            <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform" :class="open ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="open" x-transition style="display: none;" class="absolute left-0 right-0 top-[calc(100%+4px)] z-50 bg-white dark:bg-slate-900 border-[1.5px] border-slate-300 dark:border-slate-700 rounded-xl shadow-xl p-1">
                            @foreach($paymentMethodOptions as $val => $opt)
                                <div wire:click="setPaymentMethod('{{ $val }}')" @click="open = false" class="px-3 py-2 rounded-lg text-xs font-semibold flex items-center justify-between cursor-pointer hover:bg-emerald-50 dark:hover:bg-slate-800 {{ $payment_method === $val ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300' : 'text-slate-700 dark:text-slate-200' }}">
                                    <span class="flex items-center gap-2"><i class="{{ $opt['icon'] }}"></i>{{ $opt['label'] }}</span>
                                    @if($payment_method === $val) <i class="fa-solid fa-check text-xs text-emerald-600"></i> @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider mb-1.5">Transaction ID / Note</label>
                        <input type="text" wire:model="transaction_id" placeholder="Enter transaction ID or note..." class="w-full h-[38px] px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm outline-none focus:border-[#15803d]">
                    </div>
                </form>

                <div class="sticky bottom-0 z-20 px-4 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 flex items-center justify-end gap-2 shrink-0">
                    <button type="button" wire:click="closeModal" class="h-[38px] px-4 rounded-xl bg-[#dc2626] hover:bg-red-700 text-white text-[13px] font-bold border-0 cursor-pointer shadow-xs">Cancel</button>
                    <button type="submit" form="batteryProfilePayForm" class="h-[38px] px-5 rounded-xl bg-[#15803d] hover:bg-[#166534] text-white text-[13px] font-bold border-0 cursor-pointer shadow-xs">Confirm Payment</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Image Preview Quick View Modal --}}
    @if($showImageModal && $viewingImageUrl)
        <div class="fixed inset-0 z-[80] bg-slate-900/85 backdrop-blur-xs flex items-center justify-center p-4" wire:click.self="closeModal">
            <div class="relative flex items-center justify-center max-w-[90vw] max-h-[90vh]">
                <button type="button" wire:click="closeModal" title="Close" class="absolute -top-3.5 -right-3.5 w-[34px] h-[34px] rounded-full bg-[#dc2626] hover:bg-red-700 text-white flex items-center justify-center border-2 border-white shadow-xl cursor-pointer z-10">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
                <div class="rounded-2xl overflow-hidden bg-white p-1.5 border-2 border-white shadow-2xl max-w-[90vw] max-h-[90vh]">
                    <img src="{{ $viewingImageUrl }}" alt="{{ $viewingImageTitle }}" class="block max-w-[calc(90vw-24px)] max-h-[calc(90vh-24px)] object-contain rounded-xl">
                </div>
            </div>
        </div>
    @endif
</div>
