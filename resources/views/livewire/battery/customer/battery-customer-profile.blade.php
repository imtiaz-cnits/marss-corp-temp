@php use App\Support\Money; @endphp

<div class="space-y-4">
    {{-- Topbar Header (Image 2 1:1) --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('battery.customers') }}" class="h-9 px-3.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 text-slate-700 dark:text-slate-200 text-xs font-bold inline-flex items-center gap-1.5 shadow-2xs transition">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Back</span>
            </a>
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-950/60 text-[#15803d] dark:text-emerald-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-user"></i>
                </span>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight m-0">Battery Customer Profile</h1>
            </div>
        </div>
        <button type="button" wire:click="openCollect" class="h-10 px-4 rounded-xl bg-[#15803d] hover:bg-emerald-800 text-white text-xs sm:text-sm font-bold flex items-center gap-2 cursor-pointer shadow-xs transition">
            <i class="fa-solid fa-plus"></i>
            <span>Collect Due</span>
        </button>
    </div>

    {{-- Customer Information Card (Image 2 1:1) --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 sm:p-6 shadow-2xs flex flex-wrap lg:flex-nowrap items-center justify-between gap-6">
        <div class="flex items-center gap-4 sm:gap-5">
            @if(!empty($customer->image) && file_exists(public_path($customer->image)))
                <img src="{{ asset($customer->image) }}" alt="{{ $customer->customer_name }}" class="w-20 h-20 rounded-full object-cover border-2 border-emerald-500 shadow-xs shrink-0">
            @else
                <div class="w-20 h-20 rounded-full bg-sky-100 text-sky-600 dark:bg-sky-950/60 dark:text-sky-300 font-bold text-xs flex items-center justify-center text-center p-2 border-2 border-sky-200 dark:border-sky-800 shrink-0">
                    Demo<br>Image
                </div>
            @endif
            <div class="space-y-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-lg sm:text-xl font-black text-slate-900 dark:text-white tracking-tight m-0">{{ $customer->customer_name }}</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                        {{ $customer->customer_id }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300">
                        Active
                    </span>
                </div>
                <div class="text-xs text-slate-600 dark:text-slate-400 flex items-center gap-2 pt-0.5">
                    <i class="fa-solid fa-phone text-emerald-600 w-3.5"></i>
                    <span class="font-semibold">{{ $customer->mobile ?: '-' }}</span>
                </div>
                <div class="text-xs text-slate-600 dark:text-slate-400 flex items-center gap-2">
                    <i class="fa-solid fa-location-dot text-rose-500 w-3.5"></i>
                    <span>{{ $customer->address_details ?: 'N/A' }}</span>
                </div>
                <div class="text-xs text-slate-600 dark:text-slate-400 flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-amber-500 w-3.5"></i>
                    <span class="font-medium text-amber-700 dark:text-amber-400">Opening Due: {{ Money::formatBd($customer->previous_due_amount ?? 0) }}</span>
                </div>
            </div>
        </div>

        {{-- Net Current Due Right Card (Image 2 1:1) --}}
        <div class="bg-rose-50/70 dark:bg-rose-950/30 border border-rose-100 dark:border-rose-900/40 rounded-2xl p-4 sm:p-5 text-center min-w-[220px] shrink-0 w-full sm:w-auto">
            <span class="text-[10px] font-black uppercase text-slate-500 tracking-wider block">NET CURRENT DUE</span>
            <span class="text-2xl sm:text-3xl font-black text-rose-600 mt-1 block tabular-nums">{{ Money::formatBd($balance['total_due']) }}</span>
            <span class="text-[11px] font-bold text-slate-500 mt-1.5 block">
                Prev: {{ Money::formatBd($balance['previous_due']) }} | Inv: {{ Money::formatBd($balance['orders_due']) }}
            </span>
        </div>
    </div>

    {{-- 5 Metric Cards (Image 2 1:1) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
        {{-- Card 1: INVOICES --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 shadow-2xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold uppercase text-slate-400 block tracking-wider">INVOICES</span>
                <span class="text-xl font-black text-slate-800 dark:text-white mt-1 block">{{ $orders->count() }}</span>
                <span class="text-[10px] font-semibold text-slate-400 mt-0.5 block">Total Orders</span>
            </div>
            <div class="w-9 h-9 rounded-xl bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400 flex items-center justify-center shrink-0">
                <i class="fa-regular fa-file-lines text-sm"></i>
            </div>
        </div>

        {{-- Card 2: TOTAL BILLED --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 shadow-2xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold uppercase text-slate-400 block tracking-wider">TOTAL BILLED</span>
                <span class="text-xl font-black text-slate-800 dark:text-white mt-1 block tabular-nums">{{ Money::formatBd($balance['total_billed']) }}</span>
                <span class="text-[10px] font-semibold text-slate-400 mt-0.5 block">Grand total</span>
            </div>
            <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-money-bill-wave text-sm"></i>
            </div>
        </div>

        {{-- Card 3: TOTAL PAID --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 shadow-2xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold uppercase text-slate-400 block tracking-wider">TOTAL PAID</span>
                <span class="text-xl font-black text-emerald-600 mt-1 block tabular-nums">{{ Money::formatBd($balance['total_paid']) }}</span>
                <span class="text-[10px] font-semibold text-slate-400 mt-0.5 block">Completed</span>
            </div>
            <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-circle-check text-sm"></i>
            </div>
        </div>

        {{-- Card 4: RETURN CREDIT --}}
        @php $returnSum = $returns->sum('amount'); @endphp
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 shadow-2xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold uppercase text-slate-400 block tracking-wider">RETURN CREDIT</span>
                <span class="text-xl font-black text-slate-800 dark:text-white mt-1 block tabular-nums">{{ Money::formatBd($returnSum) }}</span>
                <span class="text-[10px] font-semibold text-slate-400 mt-0.5 block">Total: {{ Money::formatBd($returnSum) }}</span>
            </div>
            <div class="w-9 h-9 rounded-xl bg-cyan-50 dark:bg-cyan-950/50 text-cyan-600 dark:text-cyan-400 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-rotate-left text-sm"></i>
            </div>
        </div>

        {{-- Card 5: NET CURRENT DUE --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-rose-200 dark:border-rose-900/50 p-4 shadow-2xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold uppercase text-rose-500 block tracking-wider">NET CURRENT DUE</span>
                <span class="text-xl font-black text-rose-600 mt-1 block tabular-nums">{{ Money::formatBd($balance['total_due']) }}</span>
                <span class="text-[10px] font-semibold text-slate-400 mt-0.5 block">Prev: {{ Money::formatBd($balance['previous_due']) }}</span>
            </div>
            <div class="w-9 h-9 rounded-xl bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-circle-exclamation text-sm"></i>
            </div>
        </div>
    </div>

    {{-- Tabs (Image 2 1:1) --}}
    <div class="flex flex-wrap items-center gap-2 pt-1">
        <button type="button" wire:click="setTab('invoices')" class="h-10 px-4 rounded-xl text-xs sm:text-sm font-bold flex items-center gap-2 border transition-all cursor-pointer {{ $activeTab === 'invoices' ? 'bg-[#15803d] text-white border-[#15803d] shadow-xs' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-50' }}">
            <i class="fa-regular fa-file-lines"></i>
            <span>Invoice History</span>
            <span class="px-2 py-0.5 rounded-full text-xs font-black {{ $activeTab === 'invoices' ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                {{ $orders->count() }}
            </span>
        </button>

        <button type="button" wire:click="setTab('returns')" class="h-10 px-4 rounded-xl text-xs sm:text-sm font-bold flex items-center gap-2 border transition-all cursor-pointer {{ $activeTab === 'returns' ? 'bg-[#15803d] text-white border-[#15803d] shadow-xs' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-50' }}">
            <i class="fa-solid fa-rotate-left"></i>
            <span>Sales Returns / Credit</span>
            <span class="px-2 py-0.5 rounded-full text-xs font-black {{ $activeTab === 'returns' ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                {{ $returns->count() }}
            </span>
        </button>

        <button type="button" wire:click="setTab('payments')" class="h-10 px-4 rounded-xl text-xs sm:text-sm font-bold flex items-center gap-2 border transition-all cursor-pointer {{ $activeTab === 'payments' ? 'bg-[#15803d] text-white border-[#15803d] shadow-xs' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-50' }}">
            <i class="fa-solid fa-dollar-sign"></i>
            <span>Payment &amp; Transaction History</span>
            <span class="px-2 py-0.5 rounded-full text-xs font-black {{ $activeTab === 'payments' ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                {{ $payments->count() }}
            </span>
        </button>
    </div>

    {{-- Main Content Table (Image 2 1:1) --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-2xs">
        @if($activeTab === 'invoices')
            {{-- TAB 1: INVOICES TABLE --}}
            <div class="overflow-x-auto">
                <table class="w-full text-xs sm:text-sm text-left">
                    <thead class="bg-[#15803d] text-white text-[11px] font-bold uppercase tracking-wider whitespace-nowrap">
                        <tr>
                            <th class="px-3.5 py-3 w-12">SL</th>
                            <th class="px-3.5 py-3">INVOICE NO</th>
                            <th class="px-3.5 py-3">DATE</th>
                            <th class="px-3.5 py-3 text-right">SUBTOTAL</th>
                            <th class="px-3.5 py-3 text-right">PAID</th>
                            <th class="px-3.5 py-3 text-right">DUE</th>
                            <th class="px-3.5 py-3 text-center">STATUS</th>
                            <th class="px-3.5 py-3 text-center w-20">ACTION</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                        @forelse($orders as $idx => $o)
                            @php
                                $due = (float) $o->due_amount;
                                $paid = (float) $o->paid_amount;
                            @endphp
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                                <td class="px-3.5 py-3 font-semibold text-slate-500">{{ $idx + 1 }}</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">
                                    <a href="{{ route('battery.invoice.show', $o->id) }}" target="_blank" class="px-2.5 py-1 rounded-md text-xs font-bold text-[#15803d] border border-emerald-300 bg-emerald-50/60 dark:bg-emerald-950/40 dark:border-emerald-800 hover:underline">
                                        {{ str_starts_with((string)$o->order_no, '#') ? $o->order_no : '#'.$o->order_no }}
                                    </a>
                                </td>
                                <td class="px-3.5 py-3 text-slate-600 dark:text-slate-400 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($o->invoice_date ?? $o->created_at)->format('M d, Y') }}
                                </td>
                                <td class="px-3.5 py-3 text-right font-bold text-slate-900 dark:text-white tabular-nums">
                                    {{ Money::formatBd($o->sub_total) }}
                                </td>
                                <td class="px-3.5 py-3 text-right font-bold text-emerald-700 dark:text-emerald-400 tabular-nums">
                                    {{ Money::formatBd($o->paid_amount) }}
                                </td>
                                <td class="px-3.5 py-3 text-right font-bold text-slate-700 dark:text-slate-300 tabular-nums">
                                    {{ Money::formatBd($o->due_amount) }}
                                </td>
                                <td class="px-3.5 py-3 text-center whitespace-nowrap">
                                    @if($due <= 0)
                                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                            Fully Paid
                                        </span>
                                    @elseif($paid > 0)
                                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                            Partial Paid
                                        </span>
                                    @else
                                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                                            Unpaid
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3.5 py-3 text-center whitespace-nowrap">
                                    <a href="{{ route('battery.invoice.show', $o->id) }}" target="_blank" class="w-8 h-8 rounded-lg bg-emerald-50 hover:bg-[#15803d] text-[#15803d] hover:text-white border border-emerald-200 dark:bg-emerald-950/50 dark:border-emerald-800 dark:text-emerald-400 inline-flex items-center justify-center transition shadow-2xs" title="View / Print Invoice">
                                        <i class="fa-regular fa-eye text-xs"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-slate-500">No invoices found for this customer.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        @elseif($activeTab === 'returns')
            {{-- TAB 2: RETURNS TABLE --}}
            <div class="overflow-x-auto">
                <table class="w-full text-xs sm:text-sm text-left">
                    <thead class="bg-[#15803d] text-white text-[11px] font-bold uppercase tracking-wider whitespace-nowrap">
                        <tr>
                            <th class="px-3.5 py-3 w-12">SL</th>
                            <th class="px-3.5 py-3">DATE</th>
                            <th class="px-3.5 py-3">INVOICE REF</th>
                            <th class="px-3.5 py-3">PRODUCT</th>
                            <th class="px-3.5 py-3 text-center">QTY</th>
                            <th class="px-3.5 py-3 text-right">AMOUNT</th>
                            <th class="px-3.5 py-3 text-center w-20">ACTION</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                        @forelse($returns as $idx => $ret)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                                <td class="px-3.5 py-3 font-semibold text-slate-500">{{ $idx + 1 }}</td>
                                <td class="px-3.5 py-3 whitespace-nowrap text-slate-600 dark:text-slate-400 font-medium">
                                    {{ \Carbon\Carbon::parse($ret->date ?? $ret->created_at)->format('M d, Y') }}
                                </td>
                                <td class="px-3.5 py-3 font-bold whitespace-nowrap">
                                    @if($ret->order_id)
                                        <a href="{{ route('battery.invoice.show', $ret->order_id) }}" target="_blank" class="px-2.5 py-1 rounded-md text-xs font-bold text-[#15803d] border border-emerald-300 bg-emerald-50/60 dark:bg-emerald-950/40 dark:border-emerald-800 hover:underline">
                                            {{ str_starts_with((string)($ret->order?->order_no ?? ''), '#') ? $ret->order->order_no : '#'.($ret->order?->order_no ?? ('InvID'.$ret->order_id)) }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-3.5 py-3 font-semibold text-slate-800 dark:text-slate-200">
                                    {{ $ret->product?->product_name ?? 'Product' }}
                                </td>
                                <td class="px-3.5 py-3 text-center font-bold text-slate-700 dark:text-slate-300">
                                    <span class="px-2.5 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-xs">
                                        {{ (int)$ret->quantity }} pcs
                                    </span>
                                </td>
                                <td class="px-3.5 py-3 text-right font-black text-rose-600 dark:text-rose-400 tabular-nums">
                                    {{ Money::formatBd($ret->amount) }}
                                </td>
                                <td class="px-3.5 py-3 text-center whitespace-nowrap">
                                    @if($ret->order_id)
                                        <a href="{{ route('battery.invoice.show', $ret->order_id) }}" target="_blank" class="w-8 h-8 rounded-lg bg-emerald-50 hover:bg-[#15803d] text-[#15803d] hover:text-white border border-emerald-200 dark:bg-emerald-950/50 dark:border-emerald-800 dark:text-emerald-400 inline-flex items-center justify-center transition shadow-2xs" title="View Source Invoice">
                                            <i class="fa-regular fa-eye text-xs"></i>
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-slate-500">No sales return records for this customer.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        @elseif($activeTab === 'payments')
            {{-- TAB 3: PAYMENTS TABLE --}}
            <div class="overflow-x-auto">
                <table class="w-full text-xs sm:text-sm text-left">
                    <thead class="bg-[#15803d] text-white text-[11px] font-bold uppercase tracking-wider whitespace-nowrap">
                        <tr>
                            <th class="px-3.5 py-3 w-12">SL</th>
                            <th class="px-3.5 py-3">DATE</th>
                            <th class="px-3.5 py-3">INVOICE NO</th>
                            <th class="px-3.5 py-3">PAYMENT METHOD</th>
                            <th class="px-3.5 py-3 text-right">PAID AMOUNT</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                        @forelse($payments as $idx => $pay)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                                <td class="px-3.5 py-3 font-semibold text-slate-500">{{ $idx + 1 }}</td>
                                <td class="px-3.5 py-3 text-slate-600 dark:text-slate-400 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($pay->due_collection_date ?? $pay->created_at)->format('M d, Y') }}
                                </td>
                                <td class="px-3.5 py-3 font-bold whitespace-nowrap">
                                    @if($pay->order_id)
                                        <a href="{{ route('battery.invoice.show', $pay->order_id) }}" target="_blank" class="px-2.5 py-1 rounded-md text-xs font-bold text-[#15803d] border border-emerald-300 bg-emerald-50/60 dark:bg-emerald-950/40 dark:border-emerald-800 hover:underline">
                                            {{ str_starts_with((string)($pay->order?->order_no ?? ''), '#') ? $pay->order->order_no : '#'.($pay->order?->order_no ?? ('InvID'.$pay->order_id)) }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-3.5 py-3">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                        {{ $pay->payment_method ?: 'Cash' }}
                                    </span>
                                </td>
                                <td class="px-3.5 py-3 text-right font-black text-emerald-700 dark:text-emerald-400 tabular-nums">
                                    {{ Money::formatBd($pay->paid_amount) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-500">No payment transaction records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Collect Due Modal --}}
    <x-ui.modal :show="$showCollectModal" :title="'Collect Due — ' . $customer->customer_name" close-method="closeModal">
        <form wire:submit="submitCollect" class="space-y-3">
            <div class="p-3.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 flex justify-between items-center text-sm font-bold">
                <span class="text-slate-700 dark:text-slate-300">Total Due Receivable:</span>
                <span class="text-rose-600 text-base tabular-nums">{{ Money::formatBd($balance['total_due']) }}</span>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Collected Amount (৳) *</label>
                    <input type="number" step="0.01" min="0" wire:model="paid_amount" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-bold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Discount (৳)</label>
                    <input type="number" step="0.01" min="0" wire:model="discount_amount" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Apply Against</label>
                    <select wire:model="collection_type" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium">
                        <option value="all">All (FIFO)</option>
                        <option value="previous">Previous Due Only</option>
                        <option value="invoice">Invoices Only</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Method</label>
                    <select wire:model="payment_method" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium">
                        <option value="Cash">Cash</option>
                        <option value="bKash">bKash</option>
                        <option value="Nagad">Nagad</option>
                        <option value="Bank">Bank</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Date</label>
                    <x-ui.date-picker wire:model="date" />
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" wire:click="closeModal" class="h-10 px-4 rounded-xl border border-slate-300 text-sm font-bold cursor-pointer">Cancel</button>
                <button type="submit" class="h-10 px-5 rounded-xl bg-[#15803d] hover:bg-emerald-800 text-white text-sm font-bold cursor-pointer shadow-xs transition">Confirm Collection</button>
            </div>
        </form>
    </x-ui.modal>
</div>
