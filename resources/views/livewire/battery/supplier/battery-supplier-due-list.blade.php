@php
    use App\Support\Money;
    $defaultSupplierImg = asset('backend/assets/img/demo-img.jpeg');

    $payVal = (float) ($paid_amount !== '' ? $paid_amount : 0);
    $discVal = (float) ($discount_amount !== '' ? $discount_amount : 0);
    $totalInput = $payVal + $discVal;
    $totalPrevDueNum = (float) $payTotalDue;
    $isOverpaid = $totalInput > ($totalPrevDueNum + 0.009);
    $finalDueCalc = max(0, $totalPrevDueNum - $totalInput);
    if ($totalInput <= 0) {
        $liveStatus = 'Pending';
        $liveStatusColor = 'text-slate-700 dark:text-slate-200';
    } elseif ($finalDueCalc <= 0.009) {
        $liveStatus = 'Fully Paid';
        $liveStatusColor = 'text-[#15803d] dark:text-emerald-400';
    } else {
        $liveStatus = 'Partial Paid';
        $liveStatusColor = 'text-amber-600 dark:text-amber-400';
    }

    $paymentMethods = [
        ['key' => 'Cash', 'label' => 'Cash', 'img' => 'backend/assets/img/payment-cash.png'],
        ['key' => 'bKash', 'label' => 'bKash', 'img' => 'backend/assets/img/payment-bkash.png'],
        ['key' => 'Nagad', 'label' => 'Nagad', 'img' => 'backend/assets/img/payment-nagad.png'],
        ['key' => 'Rocket', 'label' => 'Rocket', 'img' => 'backend/assets/img/payment-rocket.png'],
        ['key' => 'Bank', 'label' => 'Bank', 'img' => 'backend/assets/img/payment-bank.png'],
        ['key' => 'Card', 'label' => 'Card', 'img' => 'backend/assets/img/payment-card.png'],
    ];
@endphp

<div>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-300 dark:border-slate-800 shadow-xs overflow-hidden p-3 sm:p-5">
        {{-- 1. Top Section: Page Title --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-3">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400 flex items-center justify-center border border-emerald-100 dark:border-slate-800 shadow-2xs shrink-0">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-800 dark:text-white tracking-tight leading-none m-0">Supplier Due List</h1>
            </div>
        </div>

        {{-- 2. Controls Row: Search | SHOW: 15 --}}
        <div class="flex flex-row items-center justify-between gap-2 sm:gap-3.5 mb-4">
            <div class="unified-ui-border flex-1 min-w-0 h-[38px] flex items-center px-3 bg-white dark:bg-slate-800/90 rounded-xl shadow-2xs focus-within:border-[#15803d]">
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

            <div class="unified-ui-border h-[38px] px-3 rounded-xl bg-white dark:bg-slate-800/90 flex items-center gap-1.5 text-xs font-semibold text-slate-600 dark:text-slate-300 shadow-2xs shrink-0">
                <span class="text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider whitespace-nowrap">SHOW:</span>
                <select wire:model.live="perPage" class="no-custom-select bg-transparent border-0 text-xs sm:text-sm font-bold text-[#15803d] dark:text-emerald-400 focus:outline-none cursor-pointer pr-1">
                    <option value="15">15</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="200">200</option>
                    <option value="300">300</option>
                    <option value="400">400</option>
                    <option value="500">500</option>
                </select>
            </div>
        </div>

        {{-- 3. Desktop Table (10 Columns Reference Parity) --}}
        <div class="table-responsive unified-ui-border hidden md:block w-full overflow-x-auto rounded-2xl shadow-2xs bg-white dark:bg-slate-900">
            <table id="batterySupplierDueTable" data-export-skip-last="true" class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#15803d] text-white text-xs font-semibold uppercase tracking-wider whitespace-nowrap">
                        <th class="p-[10px] text-center w-[50px] rounded-tl-2xl">SL</th>
                        <th class="p-[10px] text-center w-[65px]">Image</th>
                        <th class="p-[10px] text-start">Supplier ID</th>
                        <th class="p-[10px] text-start">Name</th>
                        <th class="p-[10px] text-start">Company</th>
                        <th class="p-[10px] text-end">Previous Amount</th>
                        <th class="p-[10px] text-end">Invoice Amount</th>
                        <th class="p-[10px] text-end">Total Due</th>
                        <th class="p-[10px] text-center w-[100px]">Status</th>
                        <th class="p-[10px] text-center w-[80px] rounded-tr-2xl">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm text-slate-700 dark:text-slate-200">
                    @forelse($suppliers as $idx => $s)
                        @php
                            $prevAmt = (float) ($s->purchase_payable_amount ?? 0);
                            $invAmt = (float) ($s->purchases_sum_due_amount ?? 0);
                            $totalDue = $prevAmt + $invAmt;
                            $hasImg = $s->img_url && file_exists(public_path($s->img_url));
                            $imgUrl = $hasImg ? asset($s->img_url) : $defaultSupplierImg;
                        @endphp
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/60 transition-colors">
                            <td class="p-[10px] text-center font-semibold text-slate-500 dark:text-slate-400 text-xs">{{ ($suppliers->firstItem() ?? 1) + $idx }}</td>
                            <td class="p-[10px] text-center w-[65px]">
                                <div class="flex justify-center">
                                    <img
                                        src="{{ $imgUrl }}"
                                        alt="{{ $s->name }}"
                                        wire:click="openImageModal('{{ $imgUrl }}', '{{ addslashes($s->name) }}')"
                                        class="w-[38px] h-[38px] rounded-full object-cover border border-slate-200 dark:border-slate-700 shadow-2xs cursor-pointer hover:scale-105 transition-transform"
                                        title="Click to preview image"
                                    >
                                </div>
                            </td>
                            <td class="p-[10px] text-start whitespace-nowrap">
                                <a href="{{ route('battery.supplier.profile', $s->id) }}" wire:navigate class="inline-flex items-center gap-1 font-semibold text-emerald-600 hover:text-emerald-700 hover:underline dark:text-emerald-400 text-xs whitespace-nowrap" title="View Profile">
                                    <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="1" y="3" width="15" height="13"></rect>
                                        <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                                        <circle cx="5.5" cy="18.5" r="2.5"></circle>
                                        <circle cx="18.5" cy="18.5" r="2.5"></circle>
                                    </svg>
                                    <span>{{ $s->supplier_id ?: ('BAT-SUP-'.$s->id) }}</span>
                                </a>
                            </td>
                            <td class="p-[10px] text-start whitespace-nowrap">
                                <a href="{{ route('battery.supplier.profile', $s->id) }}" wire:navigate class="font-bold text-slate-800 dark:text-slate-100 hover:text-emerald-600 transition-colors">{{ $s->name }}</a>
                            </td>
                            <td class="p-[10px] text-start whitespace-nowrap text-slate-600 dark:text-slate-400 font-medium">{{ $s->company ?: '-' }}</td>
                            <td class="p-[10px] text-end font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap tabular-nums">{{ Money::formatBd($prevAmt) }}</td>
                            <td class="p-[10px] text-end font-semibold text-amber-600 dark:text-amber-400 whitespace-nowrap tabular-nums">{{ Money::formatBd($invAmt) }}</td>
                            <td class="p-[10px] text-end font-bold whitespace-nowrap tabular-nums {{ $totalDue > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-700 dark:text-slate-300' }}">{{ Money::formatBd($totalDue) }}</td>
                            <td class="p-[10px] text-center w-[100px] whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400">Active</span>
                            </td>
                            <td class="p-[10px] text-center">
                                <div class="flex items-center justify-center">
                                    <button
                                        type="button"
                                        wire:click="openPay({{ $s->id }})"
                                        class="w-[30px] h-[30px] rounded-lg bg-emerald-50 hover:bg-emerald-600 text-emerald-600 hover:text-white border border-emerald-200/80 hover:border-emerald-600 dark:bg-emerald-950/40 dark:border-slate-800 dark:text-emerald-400 dark:hover:bg-emerald-600 dark:hover:text-white flex items-center justify-center transition-all shadow-2xs cursor-pointer"
                                        title="Collect / Pay Supplier Due"
                                    >
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 20h9"></path>
                                            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr data-empty-row="true">
                            <td colspan="10" class="px-4 py-8 text-center text-rose-500 font-bold">❌ No Battery supplier due data found.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50/80 dark:bg-slate-800/80 font-bold text-sm border-t border-slate-200 dark:border-slate-800">
                        <td colspan="5" class="p-[10px] text-end font-bold text-slate-700 dark:text-slate-200">Total:</td>
                        <td class="p-[10px] text-end font-bold text-rose-600 dark:text-rose-400 whitespace-nowrap tabular-nums">{{ Money::formatBd($totalPreviousDue) }}</td>
                        <td class="p-[10px] text-end font-bold text-amber-600 dark:text-amber-400 whitespace-nowrap tabular-nums">{{ Money::formatBd($totalInvoiceDue) }}</td>
                        <td class="p-[10px] text-end font-bold text-rose-600 dark:text-rose-400 whitespace-nowrap tabular-nums">{{ Money::formatBd($grandDue) }}</td>
                        <td colspan="2" class="p-[10px]"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- 4. Mobile Card View --}}
        <div class="block md:hidden space-y-3">
            @forelse($suppliers as $idx => $s)
                @php
                    $prevAmt = (float) ($s->purchase_payable_amount ?? 0);
                    $invAmt = (float) ($s->purchases_sum_due_amount ?? 0);
                    $totalDue = $prevAmt + $invAmt;
                    $hasImg = $s->img_url && file_exists(public_path($s->img_url));
                    $imgUrl = $hasImg ? asset($s->img_url) : $defaultSupplierImg;
                @endphp
                <div class="unified-ui-border rounded-2xl p-3.5 bg-white dark:bg-slate-800/60 shadow-2xs">
                    <div class="flex items-center justify-between pb-2 mb-2.5 border-b border-slate-100 dark:border-slate-700/60">
                        <div class="flex items-center gap-1.5">
                            <span class="px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-[11px] font-bold">#{{ ($suppliers->firstItem() ?? 1) + $idx }}</span>
                            <a href="{{ route('battery.supplier.profile', $s->id) }}" wire:navigate class="px-2 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/40 text-emerald-700 dark:text-emerald-400 text-xs font-bold inline-flex items-center gap-1">
                                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
                                {{ $s->supplier_id ?: ('BAT-SUP-'.$s->id) }}
                            </a>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400">Active</span>
                    </div>

                    <div class="flex items-center gap-3 mb-2.5">
                        <img src="{{ $imgUrl }}" alt="{{ $s->name }}" wire:click="openImageModal('{{ $imgUrl }}', '{{ addslashes($s->name) }}')" class="w-11 h-11 rounded-full object-cover border border-slate-200 dark:border-slate-700 shrink-0 cursor-pointer">
                        <div class="min-w-0">
                            <a href="{{ route('battery.supplier.profile', $s->id) }}" wire:navigate class="font-bold text-slate-800 dark:text-slate-100 text-sm block truncate hover:text-emerald-600">{{ $s->name }}</a>
                            <span class="text-slate-500 dark:text-slate-400 text-xs inline-flex items-center gap-1 truncate">
                                <i class="fa-solid fa-building text-[10px]"></i>
                                {{ $s->company ?: 'No Company' }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 my-2.5">
                        <div class="p-2 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700/60">
                            <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 block">Previous Due:</span>
                            <span class="font-bold text-xs text-slate-700 dark:text-slate-300 whitespace-nowrap tabular-nums">{{ Money::formatBd($prevAmt) }}</span>
                        </div>
                        <div class="p-2 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700/60">
                            <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 block">Invoice Due:</span>
                            <span class="font-bold text-xs text-amber-600 dark:text-amber-400 whitespace-nowrap tabular-nums">{{ Money::formatBd($invAmt) }}</span>
                        </div>
                    </div>

                    <div class="p-2.5 rounded-xl bg-rose-50/60 dark:bg-rose-950/30 border border-rose-200/60 dark:border-rose-900/40 my-2 flex items-center justify-between">
                        <span class="text-xs font-semibold text-rose-700 dark:text-rose-400">Total Due:</span>
                        <span class="font-bold text-sm text-rose-600 dark:text-rose-400 whitespace-nowrap tabular-nums">{{ Money::formatBd($totalDue) }}</span>
                    </div>

                    <div class="flex items-center justify-end pt-2.5 mt-1 border-t border-slate-100 dark:border-slate-700/60">
                        <button type="button" wire:click="openPay({{ $s->id }})" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#15803d] hover:bg-[#166534] text-white text-xs font-semibold rounded-lg shadow-2xs cursor-pointer border-0">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                            <span>Collect</span>
                        </button>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-rose-500 font-bold bg-white dark:bg-slate-800 rounded-2xl border border-slate-300 dark:border-slate-800">❌ No Battery supplier due data found.</div>
            @endforelse
        </div>

        {{-- 5. Display Info & Pagination Footer --}}
        <div class="flex flex-col sm:flex-row items-center justify-between pt-4 mt-4 border-t border-slate-200 dark:border-slate-800 gap-3">
            <div class="flex items-center gap-1.5 text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400">
                <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                <span>Showing</span>
                <span class="font-bold text-slate-800 dark:text-slate-100 px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700">{{ $suppliers->firstItem() ?? 0 }} – {{ $suppliers->lastItem() ?? 0 }}</span>
                <span>of</span>
                <span class="font-bold text-emerald-700 dark:text-emerald-400 px-1.5 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800">{{ $suppliers->total() }}</span>
                <span>entries</span>
            </div>
            <div class="w-full sm:w-auto">
                {{ $suppliers->links() }}
            </div>
        </div>
    </div>

    {{-- Reference Parity: Supplier Due Collection Modal (Screenshot 5) --}}
    @if($showPayModal)
        <div class="fixed inset-0 z-50 bg-slate-900/65 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 overflow-y-auto" wire:click.self="closeModal">
            <div class="w-full max-w-[620px] max-h-[90vh] bg-white dark:bg-slate-900 rounded-2xl shadow-2xl overflow-hidden flex flex-col my-auto border border-slate-200 dark:border-slate-800">
                <div class="sticky top-0 z-20 bg-[#15803d] px-5 py-3.5 flex items-center justify-between shrink-0">
                    <h2 class="text-lg font-bold text-white m-0 leading-tight">Supplier Due collection</h2>
                    <button type="button" wire:click="closeModal" class="w-7 h-7 rounded-full bg-[#dc2626] hover:bg-red-700 text-white flex items-center justify-center border-0 cursor-pointer shadow-sm" title="Close">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>

                <div class="p-5 sm:px-6 overflow-y-auto flex-1">
                    <form wire:submit="submitPay" id="batterySupplierDuePayForm" class="space-y-4">
                        <div>
                            <label class="block text-[13px] font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Due collection Date</label>
                            <x-ui.date-picker wire:model="date" placeholder="DD-MM-YYYY" alt-format="d-m-Y" class="h-[42px] pl-9 pr-3.5 rounded-lg border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-[13.5px] text-slate-800 dark:text-slate-100 outline-none focus:border-[#15803d]" />
                        </div>

                        <div class="rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 px-4 py-3 divide-y divide-dashed divide-slate-200 dark:divide-slate-700 text-[13px]">
                            <div class="flex items-center justify-between py-1.5">
                                <span class="text-slate-600 dark:text-slate-300 font-medium">Supplier Previous Due</span>
                                <span class="font-bold text-slate-800 dark:text-slate-100 tabular-nums whitespace-nowrap">{{ Money::formatBd($paySupplierPrevDue) }}</span>
                            </div>
                            <div class="flex items-center justify-between py-1.5">
                                <span class="text-slate-600 dark:text-slate-300 font-medium">Purchase Previous Due</span>
                                <span class="font-bold text-slate-800 dark:text-slate-100 tabular-nums whitespace-nowrap">{{ Money::formatBd($payPurchasePrevDue) }}</span>
                            </div>
                            <div class="flex items-center justify-between py-1.5">
                                <span class="text-slate-700 dark:text-slate-200 font-bold">Total Previous Due</span>
                                <span class="font-bold text-emerald-600 dark:text-emerald-400 tabular-nums whitespace-nowrap">{{ Money::formatBd($payTotalDue) }}</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block text-[13px] font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Enter Discount Amount</label>
                                <input type="number" step="any" min="0" wire:model.live="discount_amount" placeholder="Enter Discount" class="w-full h-[42px] px-3.5 rounded-lg border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-[13.5px] text-slate-800 dark:text-slate-100 outline-none focus:border-[#15803d]">
                            </div>
                            <div>
                                <label class="block text-[13px] font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Enter Pay Amount</label>
                                <input type="number" step="any" min="0" wire:model.live="paid_amount" placeholder="Enter Pay Amount" class="w-full h-[42px] px-3.5 rounded-lg border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-[13.5px] text-slate-800 dark:text-slate-100 outline-none focus:border-[#15803d]">
                            </div>
                        </div>

                        @if($isOverpaid)
                            <div class="p-2.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-xs font-bold text-rose-600 dark:text-rose-400">
                                The amount you paid is more than the Total Previous Due Amount.
                            </div>
                        @endif

                        <div class="rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 px-4 py-3 divide-y divide-dashed divide-slate-200 dark:divide-slate-700 text-[13px]">
                            <div class="flex items-center justify-between py-1.5">
                                <span class="text-slate-700 dark:text-slate-200 font-bold">Final Due Amount</span>
                                <span class="font-bold text-rose-600 dark:text-rose-400 text-sm tabular-nums whitespace-nowrap">{{ Money::formatBd($finalDueCalc) }}</span>
                            </div>
                            <div class="flex items-center justify-between py-1.5">
                                <span class="text-slate-700 dark:text-slate-200 font-bold">Status</span>
                                <span class="font-bold {{ $liveStatusColor }}">{{ $liveStatus }}</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[13px] font-semibold text-slate-700 dark:text-slate-300 mb-2">Payment Method</label>
                            <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                                @foreach($paymentMethods as $pm)
                                    @php $isSelected = strtolower($payment_method) === strtolower($pm['key']); @endphp
                                    <button
                                        type="button"
                                        wire:click="selectPaymentMethod('{{ $pm['key'] }}')"
                                        class="relative p-2.5 rounded-xl border-[1.5px] flex flex-col items-center justify-center gap-1.5 cursor-pointer transition-all {{ $isSelected ? 'border-[#15803d] bg-emerald-50/70 dark:bg-emerald-950/40 shadow-xs' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-emerald-400' }}"
                                    >
                                        @if($isSelected)
                                            <span class="absolute top-1 right-1 text-[#15803d] dark:text-emerald-400 text-[11px]"><i class="fa-solid fa-circle-check"></i></span>
                                        @endif
                                        <img src="{{ asset($pm['img']) }}" alt="{{ $pm['label'] }}" class="w-8 h-8 object-contain">
                                        <span class="text-[11px] font-bold text-slate-700 dark:text-slate-200">{{ $pm['label'] }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        @if(strtolower($payment_method) !== 'cash')
                            <div>
                                <label class="block text-[13px] font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Transaction ID</label>
                                <input type="text" wire:model="transaction_id" placeholder="Enter {{ $payment_method }} Transaction ID" class="w-full h-[42px] px-3.5 rounded-lg border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-[13.5px] text-slate-800 dark:text-slate-100 outline-none focus:border-[#15803d]">
                            </div>
                        @endif
                    </form>
                </div>

                <div class="sticky bottom-0 z-20 px-6 py-3.5 bg-white dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800 shrink-0">
                    <button
                        type="submit"
                        form="batterySupplierDuePayForm"
                        @if($isOverpaid) disabled @endif
                        class="w-full h-[42px] bg-[#15803d] hover:bg-[#166534] disabled:opacity-40 disabled:cursor-not-allowed text-white font-semibold text-[15px] rounded-lg border-0 cursor-pointer flex items-center justify-center transition-colors shadow-xs"
                    >
                        Submit
                    </button>
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
