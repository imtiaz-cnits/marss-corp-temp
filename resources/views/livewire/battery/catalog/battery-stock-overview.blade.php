@php
    use App\Support\Money;
    $sortLabels = [
        'category_asc' => 'Category (A-Z)',
        'name_asc' => 'Alphabet (A - Z)',
        'name_desc' => 'Alphabet (Z - A)',
        'stock_asc' => 'Stock: Low to High',
        'stock_desc' => 'Stock: High to Low',
        'default' => 'Default (Newest)',
    ];
    $activeSortLabel = $sortLabels[$sort] ?? 'Category (A-Z)';
    $adjustReasons = [
        'Physical Inventory Recount',
        'Damaged / Broken Goods',
        'Customer Return / Cancellation',
        'Supplier Replacement',
        'Showroom Display / Sample',
    ];
@endphp

<div>
    {{-- Dynamic Export Payload for Excel / PDF / Print (All Filtered Rows) --}}
    <div
        id="stockExportPayload"
        class="hidden"
        data-module-label="Battery Module"
        data-file-prefix="battery_stock_overview"
        data-filter-key="{{ $tab }}"
        data-filter-label="{{ $tab === 'low' ? 'Low Stock' : ($tab === 'out' ? 'Out of Stock' : 'All Stock') }}"
        data-rows="{{ json_encode($exportRows) }}"
    ></div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-300 dark:border-slate-800 shadow-xs overflow-hidden p-3 sm:p-5">
        {{-- 1. Page Title & Export Buttons (Reference Parity: Excel, PDF, Print) --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-4">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400 flex items-center justify-center border border-emerald-100 dark:border-slate-700 shadow-2xs shrink-0">
                    <i class="fa-solid fa-chart-line text-base"></i>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-800 dark:text-white tracking-tight leading-none m-0">Battery Stock Overview</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 mb-0 font-medium">Battery Module — Real-time inventory levels, valuation, and stock adjustment</p>
                </div>
            </div>

            <div class="flex items-center flex-wrap gap-2">
                <button
                    type="button"
                    onclick="window.marssStockOverviewExport.excel()"
                    class="inline-flex items-center gap-1.5 h-9 px-3.5 rounded-[10px] text-xs font-bold cursor-pointer border-[1.5px] bg-[#f0fdf4] text-[#15803d] border-[#bbf7d0] hover:bg-[#dcfce7] hover:border-[#4ade80] dark:bg-[#022c1e] dark:text-[#4ade80] dark:border-[#065f46] transition-all whitespace-nowrap"
                >
                    <i class="fa-solid fa-file-excel"></i>
                    <span>Excel</span>
                </button>
                <button
                    type="button"
                    onclick="window.marssStockOverviewExport.pdf()"
                    class="inline-flex items-center gap-1.5 h-9 px-3.5 rounded-[10px] text-xs font-bold cursor-pointer border-[1.5px] bg-[#fff1f2] text-[#be123c] border-[#fecaca] hover:bg-[#ffe4e6] hover:border-[#f87171] dark:bg-[#3b0006] dark:text-[#f87171] dark:border-[#7f1d1d] transition-all whitespace-nowrap"
                >
                    <i class="fa-solid fa-file-pdf"></i>
                    <span>PDF</span>
                </button>
                <button
                    type="button"
                    onclick="window.marssStockOverviewExport.print()"
                    class="inline-flex items-center gap-1.5 h-9 px-3.5 rounded-[10px] text-xs font-bold cursor-pointer border-[1.5px] bg-[#eff6ff] text-[#1d4ed8] border-[#bfdbfe] hover:bg-[#dbeafe] hover:border-[#60a5fa] dark:bg-[#0c1a3b] dark:text-[#60a5fa] dark:border-[#1e3a5f] transition-all whitespace-nowrap"
                >
                    <i class="fa-solid fa-print"></i>
                    <span>Print</span>
                </button>
            </div>
        </div>

        {{-- 2. Summary Cards (Reference Parity: Low Stock, Out of Stock, Total Cost Value, Total Sell Value) --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-3 mb-5">
            <div class="rounded-xl p-3.5 border-[1.5px] bg-amber-50/80 border-amber-200 dark:bg-amber-950/20 dark:border-amber-900/60">
                <p class="text-[11px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400 mb-1">Low Stock Items</p>
                <p class="text-lg sm:text-xl font-extrabold text-amber-800 dark:text-amber-300 m-0">{{ $summary['low_stock'] }} Products</p>
            </div>
            <div class="rounded-xl p-3.5 border-[1.5px] bg-rose-50/80 border-rose-200 dark:bg-rose-950/20 dark:border-rose-900/60">
                <p class="text-[11px] font-bold uppercase tracking-wider text-rose-700 dark:text-rose-400 mb-1">Out of Stock</p>
                <p class="text-lg sm:text-xl font-extrabold text-rose-700 dark:text-rose-300 m-0">{{ $summary['out_of_stock'] }} Products</p>
            </div>
            <div class="rounded-xl p-3.5 border-[1.5px] bg-slate-50 border-slate-200 dark:bg-slate-800/70 dark:border-slate-700">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Total Cost Value</p>
                <p class="text-lg sm:text-xl font-extrabold text-slate-800 dark:text-slate-100 tabular-nums whitespace-nowrap m-0">{{ Money::formatBd($summary['cost_value']) }}</p>
            </div>
            <div class="rounded-xl p-3.5 border-[1.5px] bg-emerald-50/80 border-emerald-200 dark:bg-emerald-950/20 dark:border-emerald-900/60">
                <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400 mb-1">Total Sell Value</p>
                <p class="text-lg sm:text-xl font-extrabold text-emerald-800 dark:text-emerald-300 tabular-nums whitespace-nowrap m-0">{{ Money::formatBd($summary['sell_value']) }}</p>
            </div>
        </div>

        {{-- 3. Controls Row: Search | Sort Custom Dropdown | SHOW Entries | 3 Stock Filter Tab Buttons --}}
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-2.5 sm:gap-3 mb-4">
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 flex-1 min-w-0">
                {{-- Search --}}
                <div class="unified-ui-border w-full sm:flex-1 lg:max-w-xs h-[38px] px-3 bg-white dark:bg-slate-800 rounded-xl flex items-center shadow-2xs focus-within:border-[#15803d]">
                    <svg class="w-4 h-4 text-slate-400 shrink-0 mr-2 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search product or barcode..."
                        class="w-full h-full bg-transparent border-0 outline-none text-xs sm:text-[13px] text-slate-800 dark:text-slate-100 placeholder:text-slate-400"
                    >
                </div>

                {{-- Sort + Entries --}}
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    {{-- Custom SORT Dropdown --}}
                    <div x-data="{ open: false }" @click.outside="open = false" class="custom-filter-dropdown flex-1 sm:w-[215px] relative">
                        <button
                            type="button"
                            @click="open = !open"
                            class="w-full unified-ui-border h-[38px] px-3 rounded-xl bg-white dark:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-200 flex items-center justify-between gap-1.5 shadow-2xs cursor-pointer hover:border-emerald-500 transition-colors"
                        >
                            <span class="flex items-center gap-1.5 overflow-hidden">
                                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider shrink-0">SORT:</span>
                                <span class="font-bold text-[#15803d] dark:text-emerald-400 truncate">{{ $activeSortLabel }}</span>
                            </span>
                            <i class="fa-solid fa-chevron-down text-[10px] text-[#15803d] dark:text-emerald-400 transition-transform shrink-0" :class="open ? 'rotate-180' : ''"></i>
                        </button>
                        <div
                            x-show="open"
                            x-transition
                            style="display: none; min-width: 200px;"
                            class="select-menu"
                        >
                            @foreach($sortLabels as $val => $lbl)
                                <div
                                    wire:click="setSort('{{ $val }}')"
                                    @click="open = false"
                                    class="select-option-item {{ $sort === $val ? 'active' : '' }}"
                                >
                                    <span>{{ $lbl }}</span>
                                    @if($sort === $val)
                                        <i class="fa-solid fa-check text-xs text-emerald-600"></i>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- SHOW Entries --}}
                    <div class="unified-ui-border h-[38px] px-3 rounded-xl bg-white dark:bg-slate-800 flex items-center gap-1.5 text-xs font-semibold text-slate-600 dark:text-slate-300 shadow-2xs shrink-0">
                        <span class="text-slate-400 text-[11px] font-bold uppercase tracking-wider">SHOW:</span>
                        <select wire:model.live="perPage" class="no-custom-select bg-transparent border-0 text-xs sm:text-sm font-bold text-[#15803d] dark:text-emerald-400 focus:outline-none cursor-pointer pr-1">
                            <option value="15">15</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="200">200</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- 3 Stock Filter Tab Buttons with Count Badges (Reference Parity) --}}
            <div class="flex items-center gap-2 overflow-x-auto shrink-0 pb-1 lg:pb-0">
                <button
                    type="button"
                    wire:click="setTab('all')"
                    class="h-[38px] px-3.5 rounded-xl text-xs font-bold flex items-center gap-2 transition-all cursor-pointer whitespace-nowrap border-[1.5px] {{ $tab === 'all' ? 'bg-[#15803d] text-white border-[#15803d] shadow-xs' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border-slate-300 dark:border-slate-700 hover:border-emerald-500' }}"
                >
                    <span>All Stock</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ $tab === 'all' ? 'bg-white/25 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-100' }}">{{ $summary['total_products'] }}</span>
                </button>
                <button
                    type="button"
                    wire:click="setTab('low')"
                    class="h-[38px] px-3.5 rounded-xl text-xs font-bold flex items-center gap-2 transition-all cursor-pointer whitespace-nowrap border-[1.5px] {{ $tab === 'low' ? 'bg-[#15803d] text-white border-[#15803d] shadow-xs' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border-slate-300 dark:border-slate-700 hover:border-emerald-500' }}"
                >
                    <span>Low Stock</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ $tab === 'low' ? 'bg-white/25 text-white' : 'bg-amber-100 dark:bg-amber-900/60 text-amber-800 dark:text-amber-300' }}">{{ $summary['low_stock'] }}</span>
                </button>
                <button
                    type="button"
                    wire:click="setTab('out')"
                    class="h-[38px] px-3.5 rounded-xl text-xs font-bold flex items-center gap-2 transition-all cursor-pointer whitespace-nowrap border-[1.5px] {{ $tab === 'out' ? 'bg-[#15803d] text-white border-[#15803d] shadow-xs' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border-slate-300 dark:border-slate-700 hover:border-emerald-500' }}"
                >
                    <span>Out of Stock</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ $tab === 'out' ? 'bg-white/25 text-white' : 'bg-rose-100 dark:bg-rose-900/60 text-rose-800 dark:text-rose-300' }}">{{ $summary['out_of_stock'] }}</span>
                </button>
            </div>
        </div>

        {{-- 4. Desktop Table (10 Columns Reference Parity: SL | Img | Product Name | Barcode / Code | Category | Stock Qty | Cost Price | Sell Price | Status | Adjust) --}}
        <div class="table-responsive unified-ui-border hidden md:block w-full overflow-x-auto rounded-2xl shadow-2xs bg-white dark:bg-slate-900">
            <table id="batteryStockTable" data-export-skip-last="true" class="w-full text-xs sm:text-[13px] text-left border-collapse">
                <thead>
                    <tr class="bg-[#15803d] text-white text-xs font-semibold uppercase tracking-wider whitespace-nowrap">
                        <th class="p-[10px] text-center w-[46px] rounded-tl-2xl">SL</th>
                        <th class="p-[10px] text-center w-[54px]">Img</th>
                        <th class="p-[10px] text-start">Product Name</th>
                        <th class="p-[10px] text-start">Barcode / Code</th>
                        <th class="p-[10px] text-start">Category</th>
                        <th class="p-[10px] text-center w-[95px]">Stock Qty</th>
                        <th class="p-[10px] text-end w-[115px]">Cost Price (৳)</th>
                        <th class="p-[10px] text-end w-[115px]">Sell Price (৳)</th>
                        <th class="p-[10px] text-center w-[115px]">Status</th>
                        <th class="p-[10px] text-center w-[75px] rounded-tr-2xl">Adjust</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-200">
                    @forelse($products as $idx => $p)
                        @php
                            $qty = (int) round((float) ($p->quantity ?? 0));
                            $isOut = $qty <= 0;
                            $isLow = $qty > 0 && $qty <= 5;
                            $qtyColor = $isOut ? 'text-rose-600 dark:text-rose-400' : ($isLow ? 'text-amber-600 dark:text-amber-400' : 'text-slate-800 dark:text-slate-200');
                            $unitName = $p->unit?->unit_name ?: 'pcs';
                            $codes = $p->decodedCodes();
                            $barcodeDisplay = !empty($codes) ? implode(', ', $codes) : 'N/A';
                            $hasImg = $p->img_url && file_exists(public_path($p->img_url));
                            $imgUrl = $hasImg ? asset($p->img_url) : asset('backend/assets/img/product-img.svg');
                        @endphp
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="p-[10px] text-center text-slate-400 font-semibold">{{ ($products->firstItem() ?? 1) + $idx }}</td>
                            <td class="p-[10px] text-center">
                                <img
                                    src="{{ $imgUrl }}"
                                    alt="{{ $p->product_name }}"
                                    wire:click="openImageModal('{{ $imgUrl }}', '{{ addslashes($p->product_name) }}')"
                                    class="w-9 h-9 rounded-lg object-cover border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 mx-auto cursor-pointer hover:scale-105 transition-transform"
                                    title="Click to view image"
                                >
                            </td>
                            <td class="p-[10px] font-semibold text-slate-800 dark:text-slate-100" title="{{ $p->product_name }}">{{ $p->product_name }}</td>
                            <td class="p-[10px]">
                                @if($barcodeDisplay !== 'N/A')
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-[11px] font-mono border border-slate-200 dark:border-slate-700 cursor-pointer hover:border-emerald-500"
                                        title="{{ $barcodeDisplay }} (Click to copy)"
                                        onclick="navigator.clipboard?.writeText('{{ addslashes($barcodeDisplay) }}')"
                                    >{{ $barcodeDisplay }}</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg bg-slate-50 dark:bg-slate-800/60 text-slate-400 dark:text-slate-500 text-[11px] font-mono border border-slate-200 dark:border-slate-700">N/A</span>
                                @endif
                            </td>
                            <td class="p-[10px] text-slate-600 dark:text-slate-400">{{ $p->category?->name ?? 'N/A' }}</td>
                            <td class="p-[10px] text-center font-bold whitespace-nowrap {{ $qtyColor }}">{{ $qty }} {{ $unitName }}</td>
                            <td class="p-[10px] text-end font-semibold text-rose-600 dark:text-rose-400 whitespace-nowrap tabular-nums">{{ Money::formatBd($p->cost_price) }}</td>
                            <td class="p-[10px] text-end font-semibold text-emerald-600 dark:text-emerald-400 whitespace-nowrap tabular-nums">{{ Money::formatBd($p->sell_price) }}</td>
                            <td class="p-[10px] text-center whitespace-nowrap">
                                @if($isOut)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/50 dark:text-rose-300 dark:border-rose-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 shrink-0"></span>Out of Stock
                                    </span>
                                @elseif($isLow)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/50 dark:text-amber-300 dark:border-amber-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 shrink-0"></span>Low Stock
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>Available
                                    </span>
                                @endif
                            </td>
                            <td class="p-[10px] text-center">
                                <button
                                    type="button"
                                    wire:click="openAdjust({{ $p->id }})"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-[#15803d] hover:bg-[#166534] text-white transition-all shadow-2xs cursor-pointer"
                                    title="Adjust Stock Quantity"
                                >
                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr data-empty-row="true">
                            <td colspan="10" class="px-4 py-10 text-center">
                                <div class="flex flex-col items-center gap-1.5 text-emerald-600 dark:text-emerald-400">
                                    <i class="fa-solid fa-box-open text-2xl text-slate-300 dark:text-slate-600"></i>
                                    <p class="font-bold text-sm m-0">No Battery products found for active filter!</p>
                                    <p class="text-xs text-slate-400 m-0">Try switching tabs or changing search query.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-50 dark:bg-slate-800/80 font-bold border-t-2 border-emerald-600/30 text-xs sm:text-sm">
                    <tr>
                        <td colspan="5" class="p-[10px] text-end font-bold text-slate-600 dark:text-slate-300">Total Filtered Qty:</td>
                        <td class="p-[10px] text-center font-bold text-rose-600 dark:text-rose-400 whitespace-nowrap">{{ $filteredTotalQty }}</td>
                        <td class="p-[10px] text-end font-bold text-slate-700 dark:text-slate-100 whitespace-nowrap tabular-nums">{{ Money::formatBd($filteredTotalCost) }}</td>
                        <td class="p-[10px] text-end font-bold text-emerald-600 dark:text-emerald-400 whitespace-nowrap tabular-nums">{{ Money::formatBd($filteredTotalSell) }}</td>
                        <td colspan="2" class="p-[10px]"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- 5. Mobile Cards (Reference Parity) --}}
        <div class="block md:hidden space-y-3">
            @forelse($products as $p)
                @php
                    $qty = (int) round((float) ($p->quantity ?? 0));
                    $isOut = $qty <= 0;
                    $isLow = $qty > 0 && $qty <= 5;
                    $qtyColor = $isOut ? 'text-rose-600' : ($isLow ? 'text-amber-600' : 'text-slate-800 dark:text-slate-100');
                    $unitName = $p->unit?->unit_name ?: 'pcs';
                    $codes = $p->decodedCodes();
                    $barcodeDisplay = !empty($codes) ? implode(', ', $codes) : 'N/A';
                    $hasImg = $p->img_url && file_exists(public_path($p->img_url));
                    $imgUrl = $hasImg ? asset($p->img_url) : asset('backend/assets/img/product-img.svg');
                @endphp
                <div class="unified-ui-border rounded-xl p-3 bg-white dark:bg-slate-800/80 shadow-2xs flex flex-col gap-2.5">
                    <div class="flex gap-3 items-center">
                        <img src="{{ $imgUrl }}" alt="{{ $p->product_name }}" wire:click="openImageModal('{{ $imgUrl }}', '{{ addslashes($p->product_name) }}')" class="w-12 h-12 rounded-lg object-cover border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shrink-0 cursor-pointer">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-1 mb-0.5">
                                <p class="font-bold text-sm text-slate-800 dark:text-slate-100 m-0 truncate">{{ $p->product_name }}</p>
                                <span class="text-xs font-bold {{ $qtyColor }} shrink-0 whitespace-nowrap">{{ $qty }} {{ $unitName }}</span>
                            </div>
                            <p class="text-xs text-slate-400 m-0 truncate">{{ $p->category?->name ?? 'N/A' }} &bull; <span class="font-mono">{{ $barcodeDisplay }}</span></p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-slate-700 text-xs">
                        <div class="flex gap-3">
                            <span class="text-rose-600 font-semibold whitespace-nowrap">Cost: {{ Money::formatBd($p->cost_price) }}</span>
                            <span class="text-emerald-600 font-semibold whitespace-nowrap">Sell: {{ Money::formatBd($p->sell_price) }}</span>
                        </div>
                        <button type="button" wire:click="openAdjust({{ $p->id }})" class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg bg-[#15803d] hover:bg-[#166534] text-white text-xs font-bold gap-1 shadow-2xs cursor-pointer">
                            <i class="fa-solid fa-pen-to-square text-xs"></i>
                            <span>Adjust</span>
                        </button>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-slate-500 font-semibold bg-white dark:bg-slate-800 rounded-2xl border border-slate-300 dark:border-slate-700">No Battery products found for active filter.</div>
            @endforelse
        </div>

        {{-- 6. Display Info & Pagination Footer --}}
        <div class="flex flex-col sm:flex-row items-center justify-between pt-4 mt-4 border-t border-slate-200 dark:border-slate-800 gap-3">
            <div class="px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1.5 flex-wrap shadow-2xs border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60">
                <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>Showing</span>
                <span class="px-1.5 py-0.5 rounded font-bold text-slate-800 dark:text-slate-100 bg-white dark:bg-slate-700">{{ $products->firstItem() ?? 0 }} – {{ $products->lastItem() ?? 0 }}</span>
                <span>of</span>
                <span class="px-1.5 py-0.5 rounded font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/50">{{ $products->total() }}</span>
                <span>entries</span>
            </div>
            <div class="w-full sm:w-auto">
                {{ $products->links() }}
            </div>
        </div>
    </div>

    {{-- Adjust Stock Quantity Modal (Reference Parity) --}}
    @if($showAdjustModal)
        <div class="fixed inset-0 z-50 bg-slate-900/75 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto" wire:click.self="closeModal">
            <div class="w-full max-w-[460px] bg-white dark:bg-slate-900 rounded-[20px] shadow-2xl overflow-hidden flex flex-col my-auto border border-slate-200 dark:border-slate-700">
                {{-- Forest Green Sticky Header --}}
                <div class="sticky top-0 z-20 flex items-center justify-between bg-[#15803d] text-white px-5 py-3.5">
                    <div class="flex items-center gap-2.5">
                        <i class="fa-solid fa-sliders text-sm"></i>
                        <h3 class="text-sm sm:text-base font-bold text-white tracking-wide uppercase m-0">ADJUST STOCK QUANTITY</h3>
                    </div>
                    <button type="button" wire:click="closeModal" title="Close" class="w-8 h-8 rounded-full bg-[#dc2626] hover:bg-red-700 text-white flex items-center justify-center border-0 shadow cursor-pointer">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>

                {{-- Body --}}
                <form wire:submit="submitAdjust" id="batteryAdjustStockForm" class="p-5 space-y-4">
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700">
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white uppercase truncate m-0">{{ $adjustProductName }}</h4>
                        <div class="flex items-center justify-between mt-2 pt-1.5 border-t border-slate-200/80 dark:border-slate-700/60">
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Current Registered Stock:</span>
                            <span class="text-xs font-bold text-rose-600 dark:text-rose-400">{{ $adjustCurrentStock }} {{ $adjustUnitName }}</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            New Physical Stock Quantity <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                type="number"
                                min="0"
                                wire:model="adjustQuantity"
                                required
                                class="w-full h-11 pl-3.5 pr-16 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-bold text-slate-900 dark:text-white outline-none focus:border-[#15803d]"
                            >
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-700 text-[11px] font-bold text-slate-600 dark:text-slate-300">{{ $adjustUnitName }}</span>
                        </div>
                    </div>

                    <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            Adjustment Reason <span class="text-rose-500">*</span>
                        </label>
                        <button
                            type="button"
                            @click="open = !open"
                            class="w-full h-11 px-3.5 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium text-slate-800 dark:text-slate-100 flex items-center justify-between gap-2 cursor-pointer hover:border-emerald-500"
                        >
                            <span class="truncate">{{ $adjustReason }}</span>
                            <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform" :class="open ? 'rotate-180' : ''"></i>
                        </button>
                        <div
                            x-show="open"
                            x-transition
                            style="display: none;"
                            class="absolute left-0 right-0 top-[calc(100%+6px)] z-50 bg-white dark:bg-slate-900 border-[1.5px] border-slate-300 dark:border-slate-700 rounded-xl shadow-xl p-1.5"
                        >
                            @foreach($adjustReasons as $reason)
                                <div
                                    wire:click="$set('adjustReason', '{{ $reason }}')"
                                    @click="open = false"
                                    class="px-3 py-2 rounded-lg text-xs font-medium cursor-pointer flex items-center justify-between hover:bg-emerald-50 dark:hover:bg-slate-800 {{ $adjustReason === $reason ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 font-bold' : 'text-slate-700 dark:text-slate-200' }}"
                                >
                                    <span>{{ $reason }}</span>
                                    @if($adjustReason === $reason)
                                        <i class="fa-solid fa-check text-xs text-emerald-600"></i>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </form>

                {{-- Sticky Footer --}}
                <div class="sticky bottom-0 z-20 flex items-center justify-center gap-3 px-5 py-3.5 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900">
                    <button type="button" wire:click="closeModal" class="h-10 px-6 rounded-xl bg-[#dc2626] hover:bg-red-700 text-white font-bold text-xs shadow-xs cursor-pointer border-0">Cancel</button>
                    <button type="submit" form="batteryAdjustStockForm" class="h-10 px-6 rounded-xl bg-[#15803d] hover:bg-[#166534] text-white font-bold text-xs shadow-sm cursor-pointer border-0">Save Stock Update</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Product Image Preview Popup Modal (Reference Parity — Screenshot 4) --}}
    @if($showImageModal && $viewingImageUrl)
        <div class="fixed inset-0 z-50 bg-slate-900/85 backdrop-blur-xs flex items-center justify-center p-4" wire:click.self="closeModal">
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
