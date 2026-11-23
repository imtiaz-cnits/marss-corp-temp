@php use App\Support\Money; @endphp

<div class="space-y-4">
    {{-- Header with Export Actions & Parity Pills (Image 2 1:1) --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <span class="w-9 h-9 rounded-xl bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center text-sm shadow-2xs">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </span>
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight m-0">Low Stock &amp; Stock-Out Report</h1>
            </div>
        </div>
        <div class="flex flex-wrap items-center justify-start gap-2.5">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-amber-700 dark:text-amber-300">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                Low Stock: {{ $metrics->low_count ?? 0 }}
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300">
                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                Out of Stock: {{ $metrics->out_count ?? 0 }}
            </span>
            <x-ui.export-buttons table-id="batteryLowStockTable" filename="battery-low-stock-report" title="MARSS Battery Low Stock & Stock-Out Report" />
        </div>
    </div>

    {{-- 4 Banner Stat Cards (Image 2 1:1) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 no-print">
        {{-- Card 1: LOW STOCK ITEMS (Yellow) --}}
        <div class="bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-900/40 rounded-2xl p-3.5 sm:p-4 shadow-2xs min-w-0">
            <span class="text-[10px] font-bold uppercase text-amber-800 dark:text-amber-400 block tracking-wider truncate">LOW STOCK ITEMS</span>
            <span class="text-base sm:text-lg font-black text-amber-700 dark:text-amber-300 mt-0.5 block tabular-nums whitespace-nowrap">{{ $metrics->low_count ?? 0 }} Products</span>
        </div>

        {{-- Card 2: OUT OF STOCK (Rose) --}}
        <div class="bg-rose-50/70 dark:bg-rose-950/30 border border-rose-200/80 dark:border-rose-900/40 rounded-2xl p-3.5 sm:p-4 shadow-2xs min-w-0">
            <span class="text-[10px] font-bold uppercase text-rose-800 dark:text-rose-400 block tracking-wider truncate">OUT OF STOCK</span>
            <span class="text-base sm:text-lg font-black text-rose-600 dark:text-rose-400 mt-0.5 block tabular-nums whitespace-nowrap">{{ $metrics->out_count ?? 0 }} Products</span>
        </div>

        {{-- Card 3: TOTAL COST VALUE (Border / White) --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-3.5 sm:p-4 shadow-2xs min-w-0">
            <span class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 block tracking-wider truncate">TOTAL COST VALUE</span>
            <span class="text-base sm:text-lg font-black text-slate-900 dark:text-white mt-0.5 block tabular-nums whitespace-nowrap">{{ Money::formatBd($metrics->total_cost ?? 0) }}</span>
        </div>

        {{-- Card 4: TOTAL SELL VALUE (Mint / Green) --}}
        <div class="bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-900/40 rounded-2xl p-3.5 sm:p-4 shadow-2xs min-w-0">
            <span class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 block tracking-wider truncate">TOTAL SELL VALUE</span>
            <span class="text-base sm:text-lg font-black text-emerald-700 dark:text-emerald-400 mt-0.5 block tabular-nums whitespace-nowrap">{{ Money::formatBd($metrics->total_sell ?? 0) }}</span>
        </div>
    </div>

    {{-- Filter Card (Image 2 1:1) --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-3 sm:p-4 shadow-2xs no-print">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="relative flex-1 min-w-[240px]">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </span>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search product or barcode..." class="w-full h-10 pl-8 pr-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-medium text-slate-700 dark:text-slate-200 shadow-2xs outline-none focus:border-[#15803d]">
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400">SHOW:</span>
                <select wire:model.live="perPage" class="h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-200 shadow-2xs outline-none">
                    <option value="15">15</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Table Section (Image 2 1:1) --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-3 sm:p-5 shadow-2xs">
        <div class="pb-3 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between no-print mb-3">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-boxes-stacked text-emerald-600"></i>
                <span class="text-sm font-black text-slate-800 dark:text-white">Low Stock &amp; Stock-Out Items List</span>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                Total {{ $products->total() }} Products
            </span>
        </div>

        {{-- Desktop Table (lg and above) --}}
        <div class="table-responsive unified-ui-border hidden lg:block w-full max-w-full overflow-x-auto rounded-2xl shadow-2xs bg-white dark:bg-slate-900 mb-4">
            <table id="batteryLowStockTable" class="w-full text-xs sm:text-sm text-left border-collapse">
                <thead class="bg-[#15803d] text-white text-[11px] font-bold uppercase tracking-wider whitespace-nowrap">
                    <tr>
                        <th class="px-3.5 py-3 text-center w-12 rounded-tl-2xl">SL</th>
                        <th class="px-3.5 py-3 text-center w-14">IMG</th>
                        <th class="px-3.5 py-3">PRODUCT NAME</th>
                        <th class="px-3.5 py-3 text-center">BARCODE / CODE</th>
                        <th class="px-3.5 py-3">CATEGORY</th>
                        <th class="px-3.5 py-3 text-center">STOCK QTY</th>
                        <th class="px-3.5 py-3 text-right">COST PRICE (৳)</th>
                        <th class="px-3.5 py-3 text-right">SELL PRICE (৳)</th>
                        <th class="px-3.5 py-3 text-center">STATUS</th>
                        <th class="px-3.5 py-3 text-center no-print w-16 rounded-tr-2xl">ACTION</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                    @forelse($products as $idx => $p)
                        @php
                            $qty = (int) round((float) ($p->quantity ?? 0));
                            $isOut = $qty <= 0;
                            $unitName = $p->unit?->unit_name ?: 'pcs';
                            $codes = method_exists($p, 'decodedCodes') ? $p->decodedCodes() : [];
                            $barcodeDisplay = !empty($codes) ? implode(', ', $codes) : 'N/A';
                            $hasImg = $p->img_url && file_exists(public_path($p->img_url));
                            $imgUrl = $hasImg ? asset($p->img_url) : asset('backend/assets/img/product-img.svg');
                        @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                            <td class="px-3.5 py-2.5 text-center font-semibold text-slate-500">{{ ($products->firstItem() ?? 1) + $idx }}</td>
                            <td class="px-3.5 py-2.5 text-center">
                                <img
                                    src="{{ $imgUrl }}"
                                    alt="{{ $p->product_name }}"
                                    wire:click="openImageModal('{{ $imgUrl }}', '{{ addslashes($p->product_name) }}')"
                                    class="w-9 h-9 rounded-lg object-cover border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 mx-auto cursor-pointer hover:scale-105 transition-transform"
                                    title="Click to view image"
                                >
                            </td>
                            <td class="px-3.5 py-2.5 font-bold text-slate-900 dark:text-white">
                                <div>{{ $p->product_name }}</div>
                                @if($p->brand)
                                    <span class="text-[11px] font-normal text-slate-400 block">{{ $p->brand->name }}</span>
                                @endif
                            </td>
                            <td class="px-3.5 py-2.5 text-center">
                                @if($barcodeDisplay !== 'N/A')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-[11px] font-mono border border-slate-200 dark:border-slate-700">
                                        {{ $barcodeDisplay }}
                                    </span>
                                @else
                                    <span class="text-slate-400 text-xs">N/A</span>
                                @endif
                            </td>
                            <td class="px-3.5 py-2.5 text-slate-600 dark:text-slate-400">{{ $p->category?->category_name ?? $p->category?->name ?? '—' }}</td>
                            <td class="px-3.5 py-2.5 text-center font-black tabular-nums">
                                <span class="{{ $isOut ? 'text-rose-600' : 'text-amber-600' }}">
                                    {{ $qty }} {{ $unitName }}
                                </span>
                            </td>
                            <td class="px-3.5 py-2.5 text-right font-bold text-slate-800 dark:text-slate-200 tabular-nums">{{ Money::formatBd($p->cost_price) }}</td>
                            <td class="px-3.5 py-2.5 text-right font-bold text-slate-800 dark:text-slate-200 tabular-nums">{{ Money::formatBd($p->sell_price) }}</td>
                            <td class="px-3.5 py-2.5 text-center">
                                @if($isOut)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                                        Out of Stock
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                        Low Stock
                                    </span>
                                @endif
                            </td>
                            <td class="px-3.5 py-2.5 text-center no-print">
                                <a
                                    href="{{ route('battery.purchases') }}"
                                    class="w-8 h-8 rounded-lg bg-[#15803d] hover:bg-[#166534] text-white inline-flex items-center justify-center shadow-2xs transition cursor-pointer"
                                    title="Go to Purchases"
                                >
                                    <i class="fa-solid fa-cart-shopping text-xs"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-8 text-center text-slate-400 text-sm">No low stock or out-of-stock items found.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if($products->count() > 0)
                    <tfoot class="bg-slate-100 dark:bg-slate-800/80 font-black text-slate-900 dark:text-white border-t-2 border-slate-300 dark:border-slate-700">
                        <tr>
                            <td colspan="6" class="px-3.5 py-3 text-right uppercase text-xs tracking-wider">Total Summary:</td>
                            <td class="px-3.5 py-3 text-right tabular-nums text-xs sm:text-sm">{{ Money::formatBd($products->sum(fn ($i) => (float)$i->cost_price)) }}</td>
                            <td class="px-3.5 py-3 text-right tabular-nums text-xs sm:text-sm">{{ Money::formatBd($products->sum(fn ($i) => (float)$i->sell_price)) }}</td>
                            <td colspan="2" class="px-3.5 py-3"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        {{-- Mobile & Tablet Box Cards (1 col mobile, 2 col tablet) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:hidden gap-3 p-3">
            @forelse($products as $idx => $p)
                @php
                    $qty = (int) round((float) ($p->quantity ?? 0));
                    $isOut = $qty <= 0;
                    $unitName = $p->unit?->unit_name ?: 'pcs';
                    $codes = method_exists($p, 'decodedCodes') ? $p->decodedCodes() : [];
                    $barcodeDisplay = !empty($codes) ? implode(', ', $codes) : 'N/A';
                    $hasImg = $p->img_url && file_exists(public_path($p->img_url));
                    $imgUrl = $hasImg ? asset($p->img_url) : asset('backend/assets/img/product-img.svg');
                @endphp
                <div class="rounded-2xl p-3 bg-white dark:bg-slate-800/90 shadow-2xs border border-slate-200 dark:border-slate-800 flex gap-3">
                    <img
                        src="{{ $imgUrl }}"
                        alt="{{ $p->product_name }}"
                        wire:click="openImageModal('{{ $imgUrl }}', '{{ addslashes($p->product_name) }}')"
                        class="w-12 h-12 rounded-lg object-contain border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shrink-0 p-0.5 cursor-pointer hover:scale-105 transition-transform"
                    />
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-1 mb-1">
                            <p class="font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-100 truncate">{{ $p->product_name }}</p>
                            <span class="text-xs font-bold {{ $isOut ? 'text-rose-600 dark:text-rose-400' : 'text-amber-600 dark:text-amber-400' }} shrink-0">
                                {{ $qty }} {{ $unitName }}
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-400 mb-1.5 truncate">
                            {{ $p->category?->category_name ?? $p->category?->name ?? '—' }} &bull; <span class="font-mono text-slate-500 dark:text-slate-400">{{ $barcodeDisplay }}</span>
                        </p>
                        <div class="flex items-center justify-between">
                            <div class="flex gap-2 text-xs">
                                <span class="text-rose-600 dark:text-rose-400 font-semibold tabular-nums text-[11px]">Cost: {{ Money::formatBd($p->cost_price) }}</span>
                                <span class="text-emerald-600 dark:text-emerald-400 font-semibold tabular-nums text-[11px]">Sell: {{ Money::formatBd($p->sell_price) }}</span>
                            </div>
                            <a
                                href="{{ route('battery.purchases') }}"
                                class="w-7 h-7 rounded-lg bg-[#15803d] hover:bg-[#166534] text-white inline-flex items-center justify-center shadow-2xs transition"
                                title="Go to Purchases"
                            >
                                <i class="fa-solid fa-cart-shopping text-[11px]"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full p-8 text-center text-slate-400 text-sm">
                    No low stock or out-of-stock items found.
                </div>
            @endforelse
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-between pt-4 mt-4 border-t border-slate-200 dark:border-slate-800 gap-3 no-print">
                <div class="flex items-center gap-1.5 text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
                    <span>Showing</span>
                    <span class="font-bold text-slate-800 dark:text-slate-100 px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700">{{ $products->firstItem() ?? 0 }} – {{ $products->lastItem() ?? 0 }}</span>
                    <span>of</span>
                    <span class="font-bold text-emerald-700 dark:text-emerald-400 px-1.5 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-slate-700">{{ $products->total() }}</span>
                    <span>products</span>
                </div>
                <div class="w-full sm:w-auto flex items-center justify-center sm:justify-end">
                    {{ $products->links() }}
                </div>
            </div>
        
    </div>

    {{-- Product Image Preview Modal --}}
    @if($showImageModal && $modalImage)
        <div class="fixed inset-0 z-50 bg-slate-900/85 backdrop-blur-xs flex items-center justify-center p-4" wire:click.self="closeModal">
            <div class="relative flex items-center justify-center max-w-[90vw] max-h-[90vh]">
                <button type="button" wire:click="closeModal" title="Close" class="absolute -top-3.5 -right-3.5 w-[34px] h-[34px] rounded-full bg-[#dc2626] hover:bg-red-700 text-white flex items-center justify-center border-2 border-white shadow-xl cursor-pointer z-10">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
                <div class="rounded-2xl overflow-hidden bg-white p-1.5 border-2 border-white shadow-2xl max-w-[90vw] max-h-[90vh]">
                    <img src="{{ $modalImage }}" alt="{{ $modalTitle }}" class="block max-w-[calc(90vw-24px)] max-h-[calc(90vh-24px)] object-contain rounded-xl">
                </div>
            </div>
        </div>
    @endif
</div>
