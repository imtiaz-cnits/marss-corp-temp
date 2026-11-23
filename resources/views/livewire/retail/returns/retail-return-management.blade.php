@php use App\Support\Money; @endphp

<div class="space-y-6">
    {{-- TOPBAR / PAGE HEADER --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2.5">
                <span class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-[#15803d] flex items-center justify-center border border-emerald-200 dark:border-emerald-800 text-base shadow-xs">
                    <i class="fa-solid fa-rotate-left"></i>
                </span>
                Return Management
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">MARSS CORPORATION - Sales &amp; Purchase Return Management</p>
        </div>
        <button type="button" wire:click="openCreateModal" class="h-10 px-5 rounded-xl bg-[#15803d] hover:bg-[#166534] text-white text-xs sm:text-sm font-bold flex items-center gap-2 cursor-pointer shadow-sm transition">
            <i class="fa-solid fa-plus"></i> {{ $tab === 'sales' ? 'New Sales Return' : 'New Purchase Return' }}
        </button>
    </div>

    {{-- TABS (Screenshot 3) --}}
    <div class="flex items-center gap-2.5">
        <button type="button" wire:click="switchTab('sales')" class="h-10 px-5 rounded-xl text-xs sm:text-sm font-bold flex items-center gap-2 cursor-pointer transition {{ $tab === 'sales' ? 'bg-[#15803d] text-white shadow-xs' : 'bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50' }}">
            <i class="fa-solid fa-cart-shopping"></i>
            <span>Sales Return</span>
        </button>
        <button type="button" wire:click="switchTab('purchase')" class="h-10 px-5 rounded-xl text-xs sm:text-sm font-bold flex items-center gap-2 cursor-pointer transition {{ $tab === 'purchase' ? 'bg-[#15803d] text-white shadow-xs' : 'bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50' }}">
            <i class="fa-solid fa-boxes-packing"></i>
            <span>Purchase Return</span>
        </button>
    </div>

    {{-- QUICK INVOICE SEARCH CARD (1:1 Reference Screenshot 3) --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 p-5 shadow-xs">
        <div class="flex items-center gap-2 text-slate-800 dark:text-white font-extrabold text-sm sm:text-base mb-1">
            <i class="fa-solid fa-magnifying-glass text-[#15803d]"></i>
            <span>Quick Invoice Search &amp; Process Return</span>
        </div>
        <p class="text-xs text-slate-400 dark:text-slate-500 mb-3.5">
            Search invoice number to process {{ $tab === 'sales' ? 'sales' : 'purchase' }} return:
        </p>

        <form wire:submit.prevent="quickSearchAndReturn" class="flex flex-wrap sm:flex-nowrap items-center gap-2.5">
            <div class="relative flex-1 min-w-[240px]">
                <input type="text" wire:model="quickInvoiceSearch" placeholder="Enter invoice number (e.g. #InvID00001)..." class="w-full h-11 px-4 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs sm:text-sm text-slate-900 dark:text-white outline-none focus:border-[#16a34a] transition">
            </div>
            <button type="submit" class="h-11 px-6 rounded-xl bg-[#15803d] hover:bg-[#166534] text-white text-xs sm:text-sm font-bold flex items-center gap-2 cursor-pointer shadow-xs transition shrink-0 border-0">
                <i class="fa-solid fa-arrow-right-to-bracket"></i>
                <span>Search &amp; Return</span>
            </button>
        </form>
    </div>

    {{-- RETURN HISTORY SECTION (1:1 Reference Screenshot 3) --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-300 dark:border-slate-800 p-3 sm:p-5 shadow-xs">
        {{-- Toolbar --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-2.5 sm:gap-3 mb-4">
            <div class="flex items-center gap-2 text-slate-800 dark:text-white font-bold text-sm sm:text-base">
                <i class="fa-solid fa-clock-rotate-left text-[#15803d]"></i>
                <span>{{ $tab === 'sales' ? 'Sales Return History' : 'Purchase Return History' }}</span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300">
                    {{ $tab === 'sales' ? ($salesReturns?->total() ?? 0) : ($purchaseReturns?->total() ?? 0) }} Records
                </span>
            </div>

            <div class="flex items-center flex-wrap gap-2 sm:gap-2.5 w-full md:w-auto">
                <div class="w-full sm:w-auto flex items-center gap-2">
                    <div class="w-32">
                        <x-ui.date-picker wire:model.live="startDate" placeholder="From" />
                    </div>
                    <div class="w-32">
                        <x-ui.date-picker wire:model.live="endDate" placeholder="To" />
                    </div>
                </div>
                <div class="relative flex-1 md:w-48 lg:w-56">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Filter returns..." class="w-full h-9 pl-8 pr-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs outline-none focus:border-emerald-600">
                </div>
                <div class="flex items-center gap-1.5 h-9 px-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-200">
                    <span class="text-slate-400 text-[11px] uppercase tracking-wider">SHOW:</span>
                    <select wire:model.live="perPage" class="no-custom-select bg-transparent border-0 text-xs sm:text-sm font-bold text-[#15803d] dark:text-emerald-400 focus:outline-none cursor-pointer pr-1">
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div>
            @if($tab === 'sales')
                <div class="table-responsive unified-ui-border hidden lg:block w-full max-w-full overflow-x-auto rounded-2xl shadow-2xs bg-white dark:bg-slate-900 mb-4">
                    <table class="w-full text-xs sm:text-sm text-left border-collapse">
                        <thead class="bg-[#15803d] text-white text-xs font-semibold uppercase tracking-wider whitespace-nowrap">
                            <tr>
                                <th class="p-[10px] text-center w-[40px] rounded-tl-2xl">SL</th>
                                <th class="p-[10px] text-start w-[120px]">RETURN DATE</th>
                                <th class="p-[10px] text-start w-[130px]">INVOICE NO</th>
                                <th class="p-[10px] text-start">CUSTOMER NAME</th>
                                <th class="p-[10px] text-start">RETURNED PRODUCT</th>
                                <th class="p-[10px] text-center w-[90px]">QTY</th>
                                <th class="p-[10px] text-right w-[130px]">REFUND AMOUNT</th>
                                <th class="p-[10px] text-center w-[110px] rounded-tr-2xl">ACTION</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @forelse($salesReturns as $idx => $r)
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                                    <td class="px-4 py-3 text-slate-500 font-medium">{{ $salesReturns->firstItem() + $idx }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap font-medium text-slate-700 dark:text-slate-300">
                                        <span class="inline-flex items-center gap-1.5">
                                            <i class="fa-regular fa-calendar-check text-[#15803d]"></i>
                                            {{ \Carbon\Carbon::parse($r->date)->format('M d, Y') }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="space-y-1">
                                            <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-black bg-[#15803d] text-white shadow-2xs">
                                                <span>{{ str_starts_with((string)$r->return_number, '#') ? $r->return_number : '#'.$r->return_number }}</span>
                                            </div>
                                            <div>
                                                <a href="{{ route('retail.invoice.show', $r->order_id) }}" target="_blank" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 hover:underline border border-emerald-200 dark:border-emerald-800">
                                                    <span>{{ str_starts_with((string)($r->order?->order_no ?? ''), '#') ? $r->order->order_no : '#'.($r->order?->order_no ?? '') }}</span>
                                                    <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 font-bold text-slate-900 dark:text-white">
                                        @if($r->customer_id)
                                            <a href="{{ route('retail.customer.profile', $r->customer_id) }}" class="text-slate-900 dark:text-white hover:text-emerald-600 hover:underline transition">
                                                {{ $r->customer?->customer_name ?? '-' }}
                                            </a>
                                        @else
                                            {{ $r->customer?->customer_name ?? '-' }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-slate-700 dark:text-slate-300 font-medium">
                                        {{ $r->product?->product_name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap font-bold text-slate-700 dark:text-slate-300">
                                        {{ (int)$r->quantity }} pcs
                                    </td>
                                    <td class="px-4 py-3 text-right font-black text-rose-600 dark:text-rose-400 tabular-nums whitespace-nowrap">
                                        {{ Money::formatBd($r->amount) }}
                                    </td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        <div class="inline-flex items-center gap-1.5">
                                            {{-- 1: Quick View Eye Icon --}}
                                            <button type="button" wire:click="openSummary({{ $r->id }})" class="w-8 h-8 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:hover:bg-emerald-900/50 dark:text-emerald-300 flex items-center justify-center border border-emerald-200 dark:border-emerald-800 cursor-pointer transition shadow-2xs" title="View Return Summary">
                                                <i class="fa-regular fa-eye text-xs"></i>
                                            </button>
                                            {{-- 2: Print Return Memo --}}
                                            <a href="{{ route('retail.return.show', $r->id) }}" target="_blank" class="w-8 h-8 rounded-lg bg-sky-50 hover:bg-sky-100 text-sky-700 dark:bg-sky-950/40 dark:hover:bg-sky-900/50 dark:text-sky-300 flex items-center justify-center border border-sky-200 dark:border-sky-800 cursor-pointer transition shadow-2xs" title="Print Return Memo">
                                                <i class="fa-regular fa-file-lines text-xs"></i>
                                            </a>
                                            {{-- 3: Customer Profile Link --}}
                                            @if($r->customer_id)
                                                <a href="{{ route('retail.customer.profile', $r->customer_id) }}" class="w-8 h-8 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 dark:bg-indigo-950/40 dark:hover:bg-indigo-900/50 dark:text-indigo-300 flex items-center justify-center border border-indigo-200 dark:border-indigo-800 cursor-pointer transition shadow-2xs" title="View Customer Profile">
                                                <i class="fa-regular fa-user text-xs"></i>
                                            </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-10 text-center text-slate-500 dark:text-slate-400">
                                        <i class="fa-solid fa-rotate-left text-3xl mb-2 text-slate-300 dark:text-slate-600"></i>
                                        <p class="m-0 text-sm">No sales return records found.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Mobile & Tablet Cards View (1 col on mobile, 2 cols on tablet) --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:hidden gap-3 p-3">
                    @forelse($salesReturns as $idx => $r)
                        <div class="unified-ui-border rounded-2xl p-3 sm:p-3.5 bg-white dark:bg-slate-800/90 shadow-2xs transition-all flex flex-col justify-between border border-slate-200 dark:border-slate-800">
                            <div>
                                <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-100 dark:border-slate-800 gap-1.5 flex-wrap">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">#{{ $salesReturns->firstItem() + $idx }}</span>
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-black bg-[#15803d] text-white shadow-2xs">
                                            {{ str_starts_with((string)$r->return_number, '#') ? $r->return_number : '#'.$r->return_number }}
                                        </span>
                                        <a href="{{ route('retail.invoice.show', $r->order_id) }}" target="_blank" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 hover:underline border border-emerald-200 dark:border-emerald-800">
                                            <span>{{ str_starts_with((string)($r->order?->order_no ?? ''), '#') ? $r->order->order_no : '#'.($r->order?->order_no ?? '') }}</span>
                                            <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                                        </a>
                                    </div>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                        <i class="fa-regular fa-calendar-check text-[#15803d] text-[10px]"></i>
                                        {{ \Carbon\Carbon::parse($r->date)->format('M d, Y') }}
                                    </span>
                                </div>

                                <div class="mb-2">
                                    <h6 class="font-bold text-slate-800 dark:text-slate-100 mb-0.5 text-sm leading-snug">
                                        @if($r->customer_id)
                                            <a href="{{ route('retail.customer.profile', $r->customer_id) }}" class="text-slate-900 dark:text-white hover:text-emerald-600 hover:underline transition">
                                                {{ $r->customer?->customer_name ?? '-' }}
                                            </a>
                                        @else
                                            {{ $r->customer?->customer_name ?? '-' }}
                                        @endif
                                    </h6>
                                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                        <span class="font-medium text-slate-700 dark:text-slate-300">{{ $r->product?->product_name ?? '-' }}</span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-2 bg-slate-50 dark:bg-slate-900/50 p-2.5 rounded-xl my-2 text-center items-center border border-slate-100 dark:border-slate-800">
                                    <div class="border-r border-slate-300 dark:border-slate-800 text-start px-2">
                                        <span class="text-slate-400 block text-[9px] font-bold uppercase tracking-wider">Quantity</span>
                                        <span class="font-bold text-slate-800 dark:text-slate-100 text-sm">{{ (int)$r->quantity }} pcs</span>
                                    </div>
                                    <div class="text-start px-2">
                                        <span class="text-slate-400 block text-[9px] font-bold uppercase tracking-wider">Refund Amount</span>
                                        <span class="font-extrabold text-rose-600 dark:text-rose-400 text-sm whitespace-nowrap tabular-nums">{{ Money::formatBd($r->amount) }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-slate-800 gap-2">
                                <span class="text-[11px] text-slate-400 font-medium">Actions:</span>
                                <div class="flex items-center gap-1.5">
                                    <button type="button" wire:click="openSummary({{ $r->id }})" class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 hover:bg-emerald-600 hover:text-white flex items-center justify-center transition-all cursor-pointer shadow-2xs" title="Quick View">
                                        <i class="fa-regular fa-eye text-xs"></i>
                                    </button>
                                    <a href="{{ route('retail.return.show', $r->id) }}" target="_blank" class="w-8 h-8 rounded-lg bg-sky-50 text-sky-700 dark:bg-sky-950/50 dark:text-sky-300 border border-sky-200 dark:border-sky-800 hover:bg-sky-600 hover:text-white flex items-center justify-center transition-all shadow-2xs" title="Invoice">
                                        <i class="fa-regular fa-file-lines text-xs"></i>
                                    </a>
                                    @if($r->customer_id)
                                        <a href="{{ route('retail.customer.profile', $r->customer_id) }}" class="w-8 h-8 rounded-lg bg-purple-50 text-purple-700 dark:bg-purple-950/50 dark:text-purple-300 border border-purple-200 dark:border-purple-800 hover:bg-purple-600 hover:text-white flex items-center justify-center transition-all shadow-2xs" title="Profile">
                                            <i class="fa-regular fa-user text-xs"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-8 text-center text-slate-500">
                            No sales return records found.
                        </div>
                    @endforelse
                </div>
                <div class="flex flex-col sm:flex-row items-center justify-between pt-4 mt-4 border-t border-slate-200 dark:border-slate-800 gap-3">
                    <div class="flex items-center gap-1.5 text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
                        <span>Showing</span>
                        <span class="font-bold text-slate-800 dark:text-slate-100 px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700">{{ $salesReturns->firstItem() ?? 0 }} – {{ $salesReturns->lastItem() ?? 0 }}</span>
                        <span>of</span>
                        <span class="font-bold text-emerald-700 dark:text-emerald-400 px-1.5 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-slate-700">{{ $salesReturns->total() }}</span>
                        <span>returns</span>
                    </div>
                    <div class="w-full sm:w-auto flex items-center justify-center sm:justify-end">
                        {{ $salesReturns->links() }}
                    </div>
                </div>
            @else
                <div class="table-responsive unified-ui-border hidden lg:block w-full max-w-full overflow-x-auto rounded-2xl shadow-2xs bg-white dark:bg-slate-900 mb-4">
                    <table class="w-full text-xs sm:text-sm text-left border-collapse">
                        <thead class="bg-[#15803d] text-white text-xs font-semibold uppercase tracking-wider whitespace-nowrap">
                            <tr>
                                <th class="p-[10px] text-center w-[40px] rounded-tl-2xl">SL</th>
                                <th class="p-[10px] text-start w-[120px]">RETURN DATE</th>
                                <th class="p-[10px] text-start w-[130px]">PURCHASE NO</th>
                                <th class="p-[10px] text-start">SUPPLIER NAME</th>
                                <th class="p-[10px] text-start">RETURNED PRODUCT</th>
                                <th class="p-[10px] text-center w-[90px]">QTY</th>
                                <th class="p-[10px] text-right w-[130px]">REFUND AMOUNT</th>
                                <th class="p-[10px] text-center w-[110px] rounded-tr-2xl">ACTION</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @forelse($purchaseReturns as $idx => $r)
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                                    <td class="px-4 py-3 text-slate-500 font-medium">{{ $purchaseReturns->firstItem() + $idx }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap font-medium text-slate-700 dark:text-slate-300">
                                        <span class="inline-flex items-center gap-1.5">
                                            <i class="fa-regular fa-calendar-check text-[#15803d]"></i>
                                            {{ \Carbon\Carbon::parse($r->date)->format('M d, Y') }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="space-y-1">
                                            <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-black bg-[#15803d] text-white shadow-2xs">
                                                <span>{{ str_starts_with((string)$r->return_number, '#') ? $r->return_number : '#'.$r->return_number }}</span>
                                            </div>
                                            <div>
                                                <a href="{{ route('retail.purchase.show', $r->purchase_id) }}" target="_blank" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 hover:underline border border-emerald-200 dark:border-emerald-800">
                                                    <span>{{ str_starts_with((string)($r->purchase?->purchase_id ?? ''), '#') ? $r->purchase->purchase_id : '#'.($r->purchase?->purchase_id ?? '') }}</span>
                                                    <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 font-bold text-slate-900 dark:text-white">
                                        @if($r->supplier_id)
                                            <a href="{{ route('retail.supplier.profile', $r->supplier_id) }}" class="text-slate-900 dark:text-white hover:text-emerald-600 hover:underline transition">
                                                {{ $r->supplier?->name ?? '-' }}
                                            </a>
                                        @else
                                            {{ $r->supplier?->name ?? '-' }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-slate-700 dark:text-slate-300 font-medium">
                                        {{ $r->product?->product_name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap font-bold text-slate-700 dark:text-slate-300">
                                        {{ (int)$r->quantity }} pcs
                                    </td>
                                    <td class="px-4 py-3 text-right font-black text-rose-600 dark:text-rose-400 tabular-nums whitespace-nowrap">
                                        {{ Money::formatBd($r->amount) }}
                                    </td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        <div class="inline-flex items-center gap-1.5">
                                            <button type="button" wire:click="openSummary({{ $r->id }})" class="w-8 h-8 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:hover:bg-emerald-900/50 dark:text-emerald-300 flex items-center justify-center border border-emerald-200 dark:border-emerald-800 cursor-pointer transition shadow-2xs" title="View Return Summary">
                                                <i class="fa-regular fa-eye text-xs"></i>
                                            </button>
                                            <a href="{{ route('retail.purchase-return.show', $r->id) }}" target="_blank" class="w-8 h-8 rounded-lg bg-sky-50 hover:bg-sky-100 text-sky-700 dark:bg-sky-950/40 dark:hover:bg-sky-900/50 dark:text-sky-300 flex items-center justify-center border border-sky-200 dark:border-sky-800 cursor-pointer transition shadow-2xs" title="Print Return Memo">
                                                <i class="fa-regular fa-file-lines text-xs"></i>
                                            </a>
                                            @if($r->supplier_id)
                                                <a href="{{ route('retail.supplier.profile', $r->supplier_id) }}" class="w-8 h-8 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 dark:bg-indigo-950/40 dark:hover:bg-indigo-900/50 dark:text-indigo-300 flex items-center justify-center border border-indigo-200 dark:border-indigo-800 cursor-pointer transition shadow-2xs" title="View Supplier Profile">
                                                    <i class="fa-regular fa-user text-xs"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-10 text-center text-slate-500 dark:text-slate-400">
                                        <i class="fa-solid fa-boxes-packing text-3xl mb-2 text-slate-300 dark:text-slate-600"></i>
                                        <p class="m-0 text-sm">No purchase return records found.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Mobile & Tablet Cards View (1 col on mobile, 2 cols on tablet) --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:hidden gap-3 p-3">
                    @forelse($purchaseReturns as $idx => $r)
                        <div class="unified-ui-border rounded-2xl p-3 sm:p-3.5 bg-white dark:bg-slate-800/90 shadow-2xs transition-all flex flex-col justify-between border border-slate-200 dark:border-slate-800">
                            <div>
                                <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-100 dark:border-slate-800 gap-1.5 flex-wrap">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">#{{ $purchaseReturns->firstItem() + $idx }}</span>
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-black bg-[#15803d] text-white shadow-2xs">
                                            {{ str_starts_with((string)$r->return_number, '#') ? $r->return_number : '#'.$r->return_number }}
                                        </span>
                                        <a href="{{ route('retail.purchase.show', $r->purchase_id) }}" target="_blank" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 hover:underline border border-emerald-200 dark:border-emerald-800">
                                            <span>{{ str_starts_with((string)($r->purchase?->purchase_id ?? ''), '#') ? $r->purchase->purchase_id : '#'.($r->purchase?->purchase_id ?? '') }}</span>
                                            <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                                        </a>
                                    </div>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                        <i class="fa-regular fa-calendar-check text-[#15803d] text-[10px]"></i>
                                        {{ \Carbon\Carbon::parse($r->date)->format('M d, Y') }}
                                    </span>
                                </div>

                                <div class="mb-2">
                                    <h6 class="font-bold text-slate-800 dark:text-slate-100 mb-0.5 text-sm leading-snug">
                                        @if($r->supplier_id)
                                            <a href="{{ route('retail.supplier.profile', $r->supplier_id) }}" class="text-slate-900 dark:text-white hover:text-emerald-600 hover:underline transition">
                                                {{ $r->supplier?->name ?? '-' }}
                                            </a>
                                        @else
                                            {{ $r->supplier?->name ?? '-' }}
                                        @endif
                                    </h6>
                                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                        <span class="font-medium text-slate-700 dark:text-slate-300">{{ $r->product?->product_name ?? '-' }}</span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-2 bg-slate-50 dark:bg-slate-900/50 p-2.5 rounded-xl my-2 text-center items-center border border-slate-100 dark:border-slate-800">
                                    <div class="border-r border-slate-300 dark:border-slate-800 text-start px-2">
                                        <span class="text-slate-400 block text-[9px] font-bold uppercase tracking-wider">Quantity</span>
                                        <span class="font-bold text-slate-800 dark:text-slate-100 text-sm">{{ (int)$r->quantity }} pcs</span>
                                    </div>
                                    <div class="text-start px-2">
                                        <span class="text-slate-400 block text-[9px] font-bold uppercase tracking-wider">Refund Amount</span>
                                        <span class="font-extrabold text-rose-600 dark:text-rose-400 text-sm whitespace-nowrap tabular-nums">{{ Money::formatBd($r->amount) }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-slate-800 gap-2">
                                <span class="text-[11px] text-slate-400 font-medium">Actions:</span>
                                <div class="flex items-center gap-1.5">
                                    <button type="button" wire:click="openSummary({{ $r->id }})" class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 hover:bg-emerald-600 hover:text-white flex items-center justify-center transition-all cursor-pointer shadow-2xs" title="Quick View">
                                        <i class="fa-regular fa-eye text-xs"></i>
                                    </button>
                                    <a href="{{ route('retail.purchase-return.show', $r->id) }}" target="_blank" class="w-8 h-8 rounded-lg bg-sky-50 text-sky-700 dark:bg-sky-950/50 dark:text-sky-300 border border-sky-200 dark:border-sky-800 hover:bg-sky-600 hover:text-white flex items-center justify-center transition-all shadow-2xs" title="Invoice">
                                        <i class="fa-regular fa-file-lines text-xs"></i>
                                    </a>
                                    @if($r->supplier_id)
                                        <a href="{{ route('retail.supplier.profile', $r->supplier_id) }}" class="w-8 h-8 rounded-lg bg-purple-50 text-purple-700 dark:bg-purple-950/50 dark:text-purple-300 border border-purple-200 dark:border-purple-800 hover:bg-purple-600 hover:text-white flex items-center justify-center transition-all shadow-2xs" title="Profile">
                                            <i class="fa-regular fa-user text-xs"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-8 text-center text-slate-500">
                            No purchase return records found.
                        </div>
                    @endforelse
                </div>
                <div class="flex flex-col sm:flex-row items-center justify-between pt-4 mt-4 border-t border-slate-200 dark:border-slate-800 gap-3">
                    <div class="flex items-center gap-1.5 text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
                        <span>Showing</span>
                        <span class="font-bold text-slate-800 dark:text-slate-100 px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700">{{ $purchaseReturns->firstItem() ?? 0 }} – {{ $purchaseReturns->lastItem() ?? 0 }}</span>
                        <span>of</span>
                        <span class="font-bold text-emerald-700 dark:text-emerald-400 px-1.5 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-slate-700">{{ $purchaseReturns->total() }}</span>
                        <span>returns</span>
                    </div>
                    <div class="w-full sm:w-auto flex items-center justify-center sm:justify-end">
                        {{ $purchaseReturns->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- MODAL 1: RETURN PROCESSING MODAL (1:1 Reference Screenshot 4) --}}
    @if($showCreateModal)
        <div data-marss-modal="true" class="fixed inset-0 z-[70] !m-0 overflow-hidden bg-slate-950/75 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4" wire:click.self="closeModal">
            <div class="w-full max-w-4xl max-h-[92vh] bg-white dark:bg-slate-900 rounded-2xl shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-800 flex flex-col">
                {{-- Header --}}
                <div class="bg-[#15803d] px-5 py-3.5 text-white flex items-center justify-between shadow-sm shrink-0 sticky top-0 z-20">
                    <h3 class="text-base sm:text-lg font-bold text-white flex items-center gap-2.5 m-0">
                        <i class="fa-solid {{ $tab === 'sales' ? 'fa-cart-shopping' : 'fa-boxes-packing' }}"></i>
                        <span>{{ $tab === 'sales' ? 'Sales Return Processing' : 'Purchase Return Processing' }}</span>
                    </h3>
                    <button type="button" wire:click="closeModal" class="w-7 h-7 rounded-full bg-red-600 hover:bg-red-700 text-white flex items-center justify-center border-0 cursor-pointer shadow-xs transition">
                        <i class="fa-solid fa-xmark text-xs"></i>
                    </button>
                </div>

                <form wire:submit="submitReturn" class="flex flex-col flex-1 overflow-hidden min-h-0 m-0">
                    <div class="p-5 overflow-y-auto flex-1 min-h-0 space-y-4">
                        {{-- Top Lookup Box --}}
                        <div class="p-4 rounded-2xl bg-emerald-50/50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/50 space-y-2">
                            <span class="text-[11px] font-black uppercase text-emerald-800 dark:text-emerald-300 tracking-wider">
                                {{ $tab === 'sales' ? 'INVOICE LOOKUP & AUTO-FILL' : 'PURCHASE LOOKUP & AUTO-FILL' }}
                            </span>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        {{ $tab === 'sales' ? 'Select Sales Invoice *' : 'Select Purchase Order *' }}
                                    </label>
                                    @if($tab === 'sales')
                                        <select wire:model.live="selectedOrderId" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs sm:text-sm font-semibold outline-none focus:border-[#16a34a]">
                                            <option value="">Choose or search order...</option>
                                            @foreach($orders as $o)
                                                <option value="{{ $o->id }}">{{ $o->order_no }} — {{ $o->customer?->customer_name }} ({{ $o->order_date }})</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <select wire:model.live="selectedPurchaseId" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs sm:text-sm font-semibold outline-none focus:border-[#16a34a]">
                                            <option value="">Choose or search purchase...</option>
                                            @foreach($purchases as $p)
                                                <option value="{{ $p->id }}">{{ $p->purchase_id }} — {{ $p->supplier?->name }} ({{ $p->purchase_date }})</option>
                                            @endforeach
                                        </select>
                                    @endif
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Return Date <span class="text-rose-500">*</span></label>
                                    <x-ui.date-picker wire:model="date" />
                                </div>
                            </div>
                        </div>

                        {{-- Order Info Summary Card --}}
                        @if($selectedOrderModel)
                            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                                <div>
                                    <span class="text-slate-400 block text-[11px]">Customer:</span>
                                    <span class="font-bold text-slate-900 dark:text-white">{{ $selectedOrderModel->customer?->customer_name ?? 'Walk-in' }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 block text-[11px]">Invoice Total:</span>
                                    <span class="font-bold text-emerald-700 dark:text-emerald-400">{{ Money::formatBd($selectedOrderModel->net_total ?? $selectedOrderModel->total) }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 block text-[11px]">Paid Amount:</span>
                                    <span class="font-bold text-slate-700 dark:text-slate-300">{{ Money::formatBd($selectedOrderModel->pay_amount ?? 0) }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 block text-[11px]">Due Balance:</span>
                                    <span class="font-bold text-rose-600">{{ Money::formatBd($selectedOrderModel->due_amount ?? 0) }}</span>
                                </div>
                            </div>
                        @elseif($selectedPurchaseModel)
                            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                                <div>
                                    <span class="text-slate-400 block text-[11px]">Supplier:</span>
                                    <span class="font-bold text-slate-900 dark:text-white">{{ $selectedPurchaseModel->supplier?->name ?? '-' }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 block text-[11px]">Purchase Total:</span>
                                    <span class="font-bold text-emerald-700 dark:text-emerald-400">{{ Money::formatBd($selectedPurchaseModel->total_amount) }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 block text-[11px]">Paid Amount:</span>
                                    <span class="font-bold text-slate-700 dark:text-slate-300">{{ Money::formatBd($selectedPurchaseModel->paid_amount ?? 0) }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 block text-[11px]">Payable Balance:</span>
                                    <span class="font-bold text-rose-600">{{ Money::formatBd($selectedPurchaseModel->due_amount ?? 0) }}</span>
                                </div>
                            </div>
                        @endif

                        {{-- Return Items Table (Screenshot 4) --}}
                        @if(!empty($returnLines))
                            <div class="space-y-2">
                                <h4 class="text-xs font-black uppercase text-slate-500 tracking-wider mb-2.5">RETURNABLE ITEMS IN THIS ORDER:</h4>
                                <div class="hidden md:block overflow-x-auto border border-slate-200 dark:border-slate-700 rounded-xl">
                                    <table class="w-full text-xs">
                                        <thead class="bg-[#15803d] text-white">
                                            <tr>
                                                <th class="px-3 py-2.5 w-10 text-center">
                                                    <input type="checkbox" wire:model.live="selectAllLines" class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 cursor-pointer">
                                                </th>
                                                <th class="px-3 py-2.5 text-left">Product Name</th>
                                                <th class="px-3 py-2.5 text-center">Sold Qty</th>
                                                <th class="px-3 py-2.5 text-right">Unit Price</th>
                                                <th class="px-3 py-2.5 text-center">Returnable</th>
                                                <th class="px-3 py-2.5 text-center w-32">Return Qty *</th>
                                                <th class="px-3 py-2.5 text-right">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700 bg-white dark:bg-slate-800">
                                            @php $grandRefund = 0; @endphp
                                            @foreach($returnLines as $idx => $line)
                                                @php
                                                    $isChecked = !empty($line['checked']);
                                                    $rQty = $isChecked ? max(0, min((int)($line['return_qty'] ?? 0), (int)$line['max_qty'])) : 0;
                                                    $subtotal = $rQty * (float)$line['unit_price'];
                                                    $grandRefund += $subtotal;
                                                @endphp
                                                <tr class="{{ $isChecked ? 'bg-emerald-50/40 dark:bg-emerald-950/20' : '' }}">
                                                    <td class="px-3 py-2.5 text-center">
                                                        <input type="checkbox" wire:model.live="returnLines.{{ $idx }}.checked" class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 cursor-pointer">
                                                    </td>
                                                    <td class="px-3 py-2.5 font-bold text-slate-900 dark:text-white">{{ $line['product_name'] }}</td>
                                                    <td class="px-3 py-2.5 text-center text-slate-500">{{ $line['sold_qty'] ?? $line['max_qty'] }}</td>
                                                    <td class="px-3 py-2.5 text-right tabular-nums">{{ Money::formatBd($line['unit_price']) }}</td>
                                                    <td class="px-3 py-2.5 text-center font-bold text-emerald-700">{{ $line['max_qty'] }} pcs</td>
                                                    <td class="px-3 py-2.5 text-center">
                                                        <input type="number" min="0" max="{{ $line['max_qty'] }}" wire:model.live="returnLines.{{ $idx }}.return_qty" class="w-24 h-8 px-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-center font-bold text-xs outline-none focus:border-emerald-500">
                                                    </td>
                                                    <td class="px-3 py-2.5 text-right font-black text-rose-600 tabular-nums">
                                                        {{ Money::formatBd($subtotal) }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                {{-- Mobile & Tablet Box Cards (md:hidden) --}}
                                <div class="space-y-2 md:hidden">
                                    <div class="flex items-center justify-between pb-1.5 border-b border-slate-200 dark:border-slate-700 px-1">
                                        <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-700 dark:text-slate-300">
                                            <input type="checkbox" wire:model.live="selectAllLines" class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 cursor-pointer">
                                            <span>Select All Items</span>
                                        </label>
                                    </div>
                                    @foreach($returnLines as $idx => $line)
                                        @php
                                            $isChecked = !empty($line['checked']);
                                            $rQty = $isChecked ? max(0, min((int)($line['return_qty'] ?? 0), (int)$line['max_qty'])) : 0;
                                            $subtotal = $rQty * (float)$line['unit_price'];
                                        @endphp
                                        <div class="p-3 rounded-xl border {{ $isChecked ? 'border-emerald-300 bg-emerald-50/40 dark:border-emerald-700 dark:bg-emerald-950/20' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800' }} shadow-2xs space-y-2">
                                            <div class="flex items-start justify-between gap-2">
                                                <label class="flex items-center gap-2 cursor-pointer flex-1 min-w-0">
                                                    <input type="checkbox" wire:model.live="returnLines.{{ $idx }}.checked" class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 cursor-pointer shrink-0">
                                                    <span class="text-xs font-bold text-slate-900 dark:text-white break-words">{{ $line['product_name'] }}</span>
                                                </label>
                                                <span class="shrink-0 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/50 text-emerald-800 dark:text-emerald-300">
                                                    Max: {{ $line['max_qty'] }} pcs
                                                </span>
                                            </div>
                                            <div class="grid grid-cols-2 gap-2 text-xs bg-slate-50 dark:bg-slate-900/60 p-2 rounded-lg border border-slate-100 dark:border-slate-800">
                                                <div>
                                                    <span class="text-slate-400 block text-[10px]">Sold Qty:</span>
                                                    <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $line['sold_qty'] ?? $line['max_qty'] }} pcs</span>
                                                </div>
                                                <div class="text-right">
                                                    <span class="text-slate-400 block text-[10px]">Unit Price:</span>
                                                    <span class="font-semibold text-slate-700 dark:text-slate-300 tabular-nums">{{ Money::formatBd($line['unit_price']) }}</span>
                                                </div>
                                            </div>
                                            <div class="flex items-center justify-between gap-2 pt-1">
                                                <div class="flex items-center gap-1.5">
                                                    <label class="text-[11px] font-bold text-slate-600 dark:text-slate-400">Return Qty:</label>
                                                    <input
                                                        type="number"
                                                        min="0"
                                                        max="{{ $line['max_qty'] }}"
                                                        wire:model.live="returnLines.{{ $idx }}.return_qty"
                                                        class="w-20 h-8 px-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-center font-bold text-xs outline-none focus:border-emerald-500"
                                                    >
                                                </div>
                                                <div class="text-right">
                                                    <span class="text-[10px] text-slate-400 block font-semibold">Subtotal:</span>
                                                    <span class="font-black text-rose-600 dark:text-rose-400 text-xs tabular-nums">{{ Money::formatBd($subtotal) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                {{-- Total Calculation Banner --}}
                                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
                                    <div class="flex-1 max-w-md">
                                        <input type="text" wire:model="returnReason" placeholder="Return reason or internal note (optional)..." class="w-full h-9 px-3 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs outline-none focus:border-emerald-500">
                                    </div>
                                    <div class="text-right">
                                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Total Refund Amount:</span>
                                        <span class="text-base sm:text-lg font-black text-rose-600 tabular-nums">
                                            {{ Money::formatBd($grandRefund) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="p-8 text-center text-slate-400 border border-dashed border-slate-200 dark:border-slate-700 rounded-2xl">
                                <i class="fa-solid fa-cart-arrow-down text-3xl mb-2 text-slate-300 dark:text-slate-600"></i>
                                <p class="text-xs font-semibold m-0">Please select an invoice above to view returnable items.</p>
                            </div>
                        @endif
                    </div>

                    {{-- Footer --}}
                    <div class="px-5 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900 flex items-center justify-end gap-2.5 shrink-0 sticky bottom-0 z-20">
                        <button type="button" wire:click="closeModal" class="h-10 px-5 rounded-lg bg-[#ef4444] hover:bg-red-600 text-white text-xs sm:text-sm font-bold border-0 cursor-pointer transition">
                            Cancel
                        </button>
                        <button type="submit" class="h-10 px-6 rounded-lg bg-[#15803d] hover:bg-[#166534] text-white text-xs sm:text-sm font-bold border-0 cursor-pointer shadow-xs transition">
                            Process Return
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- MODAL 2: RETURN SUMMARY MODAL (1:1 Reference Screenshot 3) --}}
    @if($showSummaryModal && ($summaryReturn || $summaryPurchaseReturn))
        @php
            $isSalesSummary = (bool)$summaryReturn;
            $retObj = $summaryReturn ?: $summaryPurchaseReturn;
            $retNum = $retObj->return_number;
            $invNum = $isSalesSummary ? ($retObj->order?->order_no ?? ('#'.$retObj->order_id)) : ($retObj->purchase?->purchase_id ?? ('#'.$retObj->purchase_id));
            $partyName = $isSalesSummary ? ($retObj->customer?->customer_name ?? 'Walk-in') : ($retObj->supplier?->name ?? 'Supplier');
            $partyPhone = $isSalesSummary ? ($retObj->customer?->mobile ?? $retObj->customer?->phone ?? '-') : ($retObj->supplier?->mobile ?? $retObj->supplier?->phone ?? '-');
            $partyId = $isSalesSummary ? $retObj->customer_id : $retObj->supplier_id;
            $sourceId = $isSalesSummary ? $retObj->order_id : $retObj->purchase_id;
            $itemsList = !empty($summaryItems) && count($summaryItems) > 0 ? $summaryItems : collect([$retObj]);
            $totalSummaryRefund = $itemsList->sum('amount');
            $totalSummaryQty = $itemsList->sum('quantity');
            $totalSummaryDue = $itemsList->sum('due_amount');
            $productNames = $itemsList->map(fn($it) => $it->product?->product_name)->filter()->values();
            $productsSummary = $productNames->take(2)->implode(', ') . ($productNames->count() > 2 ? ' (+'.($productNames->count() - 2).' more)' : '');
            $processedOn = $retObj->created_at ? $retObj->created_at->format('d M Y, h:i A') : \Carbon\Carbon::parse($retObj->date)->format('d M Y, h:i A');
        @endphp
        <div data-marss-modal="true" class="fixed inset-0 z-[80] !m-0 overflow-hidden bg-slate-950/75 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4" wire:click.self="closeSummaryModal">
            <div class="w-full max-w-2xl max-h-[92vh] bg-white dark:bg-slate-900 rounded-2xl shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-800 flex flex-col">
                {{-- Header (Image 3 1:1) --}}
                <div class="bg-[#15803d] px-5 py-3.5 text-white flex items-center justify-between shadow-sm shrink-0 sticky top-0 z-20">
                    <div class="flex items-center gap-3">
                        <span class="w-9 h-9 rounded-xl bg-emerald-600/60 border border-white/20 text-white flex items-center justify-center shrink-0 text-base shadow-xs">
                            <i class="fa-regular fa-eye"></i>
                        </span>
                        <div>
                            <h3 class="text-base font-bold text-white leading-tight m-0">
                                {{ $isSalesSummary ? 'Sales Return Summary' : 'Purchase Return Summary' }}
                            </h3>
                            <div class="flex items-center gap-1.5 mt-1">
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-800/90 border border-white/30 text-white leading-none">
                                    {{ str_starts_with((string)$retNum, '#') ? $retNum : '#'.$retNum }}
                                </span>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-800/90 border border-white/30 text-white leading-none">
                                    {{ $isSalesSummary ? 'Customer Return' : 'Supplier Return' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <button type="button" wire:click="closeSummaryModal" class="w-7 h-7 rounded-full bg-red-600 hover:bg-red-700 text-white flex items-center justify-center border-0 cursor-pointer shadow-xs transition">
                        <i class="fa-solid fa-xmark text-xs"></i>
                    </button>
                </div>

                <div class="p-5 overflow-y-auto flex-1 min-h-0 space-y-4">
                    {{-- 4 Metric Cards (Image 3 1:1) --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 text-center">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">RETURN DATE</span>
                            <span class="text-xs sm:text-sm font-bold text-slate-800 dark:text-white mt-1 block">
                                {{ \Carbon\Carbon::parse($retObj->date ?? $retObj->created_at)->format('M d, Y') }}
                            </span>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 text-center">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">RETURNED QTY</span>
                            <span class="text-xs sm:text-sm font-bold text-slate-800 dark:text-white mt-1 block">
                                {{ (int)$totalSummaryQty }} pcs
                            </span>
                        </div>
                        <div class="p-3 rounded-xl bg-rose-50/70 dark:bg-rose-950/30 border border-rose-100 dark:border-rose-900/40 text-center">
                            <span class="text-[10px] font-bold text-rose-500 uppercase tracking-wider block">REFUND AMOUNT</span>
                            <span class="text-xs sm:text-sm font-black text-rose-600 mt-1 block tabular-nums">
                                {{ Money::formatBd($totalSummaryRefund) }}
                            </span>
                        </div>
                        <div class="p-3 rounded-xl bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-900/40 text-center">
                            <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider block">DUE ADJUSTMENT</span>
                            <span class="text-xs sm:text-sm font-black text-emerald-600 mt-1 block tabular-nums">
                                {{ Money::formatBd($totalSummaryDue) }}
                            </span>
                        </div>
                    </div>

                    {{-- Return Specifications Box (Image 3 1:1) --}}
                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden bg-white dark:bg-slate-900 shadow-2xs">
                        <div class="px-4 py-2.5 bg-slate-50/80 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                            <span class="text-xs font-black text-slate-700 dark:text-slate-200 uppercase tracking-wider">RETURN SPECIFICATIONS</span>
                            <span class="text-[11px] font-black text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">
                                {{ $isSalesSummary ? 'SALES RETURN' : 'PURCHASE RETURN' }}
                            </span>
                        </div>
                        <table class="w-full text-xs">
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                <tr>
                                    <td class="w-1/3 px-4 py-2 text-slate-500 dark:text-slate-400 font-medium">Return ID:</td>
                                    <td class="px-4 py-2 font-bold text-slate-900 dark:text-white">
                                        {{ str_starts_with((string)$retNum, '#') ? $retNum : '#'.$retNum }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2 text-slate-500 dark:text-slate-400 font-medium">Original Invoice:</td>
                                    <td class="px-4 py-2">
                                        @if($isSalesSummary && $sourceId)
                                            <a href="{{ route('retail.invoice.show', $sourceId) }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 hover:underline">
                                                <span>{{ str_starts_with((string)$invNum, '#') ? $invNum : '#'.$invNum }}</span>
                                                <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                                            </a>
                                        @elseif(!$isSalesSummary && $sourceId)
                                            <a href="{{ route('retail.purchase.show', $sourceId) }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 hover:underline">
                                                <span>{{ str_starts_with((string)$invNum, '#') ? $invNum : '#'.$invNum }}</span>
                                                <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                                            </a>
                                        @else
                                            <span class="font-bold text-[#15803d]">{{ str_starts_with((string)$invNum, '#') ? $invNum : '#'.$invNum }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2 text-slate-500 dark:text-slate-400 font-medium">{{ $isSalesSummary ? 'Customer Name:' : 'Supplier Name:' }}</td>
                                    <td class="px-4 py-2">
                                        @if($isSalesSummary && $partyId)
                                            <a href="{{ route('retail.customer.profile', $partyId) }}" class="font-bold text-slate-900 dark:text-white hover:text-emerald-600 inline-flex items-center gap-1">
                                                <span>{{ $partyName }}</span>
                                                <i class="fa-solid fa-arrow-up-right-from-square text-[9px] text-emerald-600"></i>
                                            </a>
                                        @elseif(!$isSalesSummary && $partyId)
                                            <a href="{{ route('retail.supplier.profile', $partyId) }}" class="font-bold text-slate-900 dark:text-white hover:text-emerald-600 inline-flex items-center gap-1">
                                                <span>{{ $partyName }}</span>
                                                <i class="fa-solid fa-arrow-up-right-from-square text-[9px] text-emerald-600"></i>
                                            </a>
                                        @else
                                            <span class="font-bold text-slate-900 dark:text-white">{{ $partyName }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2 text-slate-500 dark:text-slate-400 font-medium">Contact Mobile:</td>
                                    <td class="px-4 py-2 font-semibold text-slate-700 dark:text-slate-300">{{ $partyPhone }}</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2 text-slate-500 dark:text-slate-400 font-medium">Returned Products:</td>
                                    <td class="px-4 py-2 font-medium text-slate-700 dark:text-slate-300">{{ $productsSummary ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2 text-slate-500 dark:text-slate-400 font-medium">Return Quantity:</td>
                                    <td class="px-4 py-2 font-bold text-slate-900 dark:text-white">{{ (int)$totalSummaryQty }} pcs</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2 text-slate-500 dark:text-slate-400 font-medium">Refund Amount:</td>
                                    <td class="px-4 py-2 font-black text-rose-600 tabular-nums">{{ Money::formatBd($totalSummaryRefund) }}</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2 text-slate-500 dark:text-slate-400 font-medium">Due Adjustment:</td>
                                    <td class="px-4 py-2 font-black text-emerald-600 tabular-nums">{{ Money::formatBd($totalSummaryDue) }}</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2 text-slate-500 dark:text-slate-400 font-medium">Processed On:</td>
                                    <td class="px-4 py-2 font-medium text-slate-600 dark:text-slate-400">{{ $processedOn }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Returned Products Breakdown Box (Image 3 1:1) --}}
                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden bg-white dark:bg-slate-900 shadow-2xs">
                        <div class="px-4 py-2.5 bg-slate-50/80 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                            <span class="text-xs font-black text-slate-700 dark:text-slate-200 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fa-solid fa-cubes-stacked text-emerald-600"></i>
                                RETURNED PRODUCTS BREAKDOWN
                            </span>
                            <span class="text-[11px] font-black text-emerald-700 dark:text-emerald-400 uppercase tracking-wider bg-emerald-50 dark:bg-emerald-950/50 px-2.5 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-800">
                                {{ $itemsList->count() }} PRODUCTS
                            </span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs">
                                <thead class="bg-slate-50/60 dark:bg-slate-800/40 text-[11px] font-black uppercase text-slate-500 tracking-wider">
                                    <tr>
                                        <th class="px-3 py-2 text-center w-12">SL</th>
                                        <th class="px-3 py-2 text-left">PRODUCT NAME</th>
                                        <th class="px-3 py-2 text-center">RETURN QTY</th>
                                        <th class="px-3 py-2 text-right">SALE RATE</th>
                                        <th class="px-3 py-2 text-right">REFUND AMOUNT</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    @foreach($itemsList as $idx => $it)
                                        <tr>
                                            <td class="px-3 py-2 text-center text-slate-500 font-medium">{{ $idx + 1 }}</td>
                                            <td class="px-3 py-2 font-bold text-slate-900 dark:text-white">{{ $it->product?->product_name ?? 'Product' }}</td>
                                            <td class="px-3 py-2 text-center">
                                                <span class="px-2.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 font-bold text-xs text-slate-700 dark:text-slate-300">
                                                    {{ (int)$it->quantity }} pcs
                                                </span>
                                            </td>
                                            <td class="px-3 py-2 text-right tabular-nums font-semibold text-slate-600 dark:text-slate-400">
                                                {{ Money::formatBd($it->unit_price) }}
                                            </td>
                                            <td class="px-3 py-2 text-right font-black text-rose-600 tabular-nums">
                                                {{ Money::formatBd($it->amount) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="bg-slate-50/80 dark:bg-slate-800/60 font-bold border-t border-slate-200 dark:border-slate-700">
                                        <td colspan="2" class="px-3 py-2.5 text-right text-xs font-bold text-slate-700 dark:text-slate-300">Total:</td>
                                        <td class="px-3 py-2.5 text-center font-black text-slate-900 dark:text-white text-xs">{{ (int)$totalSummaryQty }} pcs</td>
                                        <td class="px-3 py-2.5"></td>
                                        <td class="px-3 py-2.5 text-right font-black text-rose-600 text-xs sm:text-sm tabular-nums">{{ Money::formatBd($totalSummaryRefund) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Footer Buttons (Image 3 1:1) --}}
                <div class="px-5 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900 flex items-center justify-between gap-2.5 shrink-0 sticky bottom-0 z-20">
                    <div class="flex items-center gap-2">
                        @if($isSalesSummary && $sourceId)
                            <a href="{{ route('retail.invoice.show', $sourceId) }}" target="_blank" class="h-9 px-3.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-[#15803d] text-xs font-bold inline-flex items-center gap-1.5 border border-emerald-200 dark:border-emerald-800 transition shadow-2xs">
                                <i class="fa-solid fa-file-invoice"></i>
                                <span>Open Invoice</span>
                            </a>
                        @elseif(!$isSalesSummary && $sourceId)
                            <a href="{{ route('retail.purchase.show', $sourceId) }}" target="_blank" class="h-9 px-3.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-[#15803d] text-xs font-bold inline-flex items-center gap-1.5 border border-emerald-200 dark:border-emerald-800 transition shadow-2xs">
                                <i class="fa-solid fa-file-invoice"></i>
                                <span>Open Purchase</span>
                            </a>
                        @endif

                        @if($isSalesSummary && $partyId)
                            <a href="{{ route('retail.customer.profile', $partyId) }}" class="h-9 px-3.5 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 dark:bg-purple-950/40 dark:text-purple-300 text-xs font-bold inline-flex items-center gap-1.5 border border-purple-200 dark:border-purple-800 transition shadow-2xs">
                                <i class="fa-solid fa-user"></i>
                                <span>Customer Profile</span>
                            </a>
                        @elseif(!$isSalesSummary && $partyId)
                            <a href="{{ route('retail.supplier.profile', $partyId) }}" class="h-9 px-3.5 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 dark:bg-purple-950/40 dark:text-purple-300 text-xs font-bold inline-flex items-center gap-1.5 border border-purple-200 dark:border-purple-800 transition shadow-2xs">
                                <i class="fa-solid fa-user"></i>
                                <span>Supplier Profile</span>
                            </a>
                        @endif
                    </div>

                    <button type="button" wire:click="closeSummaryModal" class="h-9 px-6 rounded-xl bg-[#dc2626] hover:bg-red-700 text-white text-xs font-bold border-0 cursor-pointer shadow-xs transition">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
