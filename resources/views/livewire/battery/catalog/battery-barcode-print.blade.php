<div>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-300 dark:border-slate-800 shadow-xs overflow-hidden p-3 sm:p-6 md:p-8 no-print">
        {{-- 1. Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 pb-3 border-b border-slate-200 dark:border-slate-800">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400 flex items-center justify-center border border-emerald-100 dark:border-slate-800 shadow-2xs shrink-0">
                    <i class="fa-solid fa-barcode text-base"></i>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-800 dark:text-white tracking-tight leading-none m-0">Battery Barcode Generate</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 mb-0">Generate Battery product MRP stickers or sequential barcodes to print on standard A4 format</p>
                </div>
            </div>

            {{-- Mode Switch Tabs --}}
            <div class="flex items-center gap-2 shrink-0">
                <button
                    type="button"
                    wire:click="$set('tab', 'product')"
                    class="h-[38px] px-4 rounded-xl text-xs font-bold transition-all cursor-pointer {{ $tab === 'product' ? 'bg-[#15803d] text-white shadow-xs' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border-[1.5px] border-slate-300 dark:border-slate-700 hover:border-emerald-500' }}"
                >
                    Product Barcode
                </button>
                <button
                    type="button"
                    wire:click="$set('tab', 'sequential')"
                    class="h-[38px] px-4 rounded-xl text-xs font-bold transition-all cursor-pointer {{ $tab === 'sequential' ? 'bg-[#15803d] text-white shadow-xs' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border-[1.5px] border-slate-300 dark:border-slate-700 hover:border-emerald-500' }}"
                >
                    Sequential Range
                </button>
            </div>
        </div>

        @if($tab === 'product')
            {{-- Searchable Product Selector --}}
            <div class="mb-3" x-data="{ open: false, q: '' }" @click.outside="open = false">
                <label class="block text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">
                    Add Product <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <button
                        type="button"
                        @click="open = !open"
                        class="w-full h-[38px] px-4 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs sm:text-sm font-medium text-slate-800 dark:text-slate-100 flex items-center justify-between gap-2 cursor-pointer hover:border-emerald-500"
                    >
                        <span class="truncate">
                            @if($selectedProduct)
                                @php $pCode = $selectedProduct->primaryCode(); @endphp
                                {{ $selectedProduct->product_name }} {{ $pCode ? '(' . $pCode . ')' : '' }}
                            @else
                                Search Battery Product by name or code...
                            @endif
                        </span>
                        <i class="fa-solid fa-chevron-down text-xs text-slate-400"></i>
                    </button>
                    <div
                        x-show="open"
                        x-transition
                        style="display: none;"
                        class="absolute left-0 right-0 top-[calc(100%+6px)] z-40 bg-white dark:bg-slate-900 border-[1.5px] border-slate-300 dark:border-slate-700 rounded-xl shadow-xl p-2"
                    >
                        <input
                            type="text"
                            x-model="q"
                            placeholder="Type product name or barcode..."
                            class="w-full h-9 px-3 mb-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs outline-none focus:border-[#15803d]"
                        >
                        <div class="max-h-56 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($products as $p)
                                @php
                                    $codeStr = $p->primaryCode();
                                    $searchHaystack = strtolower($p->product_name . ' ' . $codeStr);
                                @endphp
                                <button
                                    type="button"
                                    x-show="!q || '{{ addslashes($searchHaystack) }}'.includes(q.toLowerCase())"
                                    wire:click="selectProduct({{ $p->id }})"
                                    @click="open = false; q = ''"
                                    class="w-full text-left px-3 py-2 text-xs hover:bg-emerald-50 dark:hover:bg-slate-800 flex items-center justify-between gap-2 rounded-lg cursor-pointer {{ (int)$selectedProductId === (int)$p->id ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 font-bold' : 'text-slate-700 dark:text-slate-200' }}"
                                >
                                    <span class="truncate">{{ $p->product_name }}</span>
                                    <span class="font-mono text-[11px] text-slate-400 shrink-0">{{ $codeStr ?: '-' }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Product Summary Table (Reference Parity) --}}
            <div class="table-responsive rounded-xl border border-slate-200 dark:border-slate-800 mb-3 overflow-x-auto">
                <table class="w-full text-xs sm:text-sm text-left">
                    <thead class="bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-2.5 border-b border-slate-200 dark:border-slate-800">Product Name With Code</th>
                            <th class="px-4 py-2.5 border-b border-slate-200 dark:border-slate-800 text-center">Available</th>
                            <th class="px-4 py-2.5 border-b border-slate-200 dark:border-slate-800 text-center">Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="bg-white dark:bg-slate-900">
                            <td class="px-4 py-2.5 font-semibold text-slate-800 dark:text-white">
                                @if($selectedProduct)
                                    @php $spCode = $selectedProduct->primaryCode(); @endphp
                                    {{ $selectedProduct->product_name }} @if($spCode)<span class="font-mono text-xs text-emerald-700 dark:text-emerald-400">({{ $spCode }})</span>@endif
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-center font-bold text-slate-600 dark:text-slate-300">
                                {{ $selectedProduct ? (int) round((float) ($selectedProduct->quantity ?? 0)) : '-' }}
                            </td>
                            <td class="px-4 py-2.5 text-center">
                                <div class="inline-flex items-center gap-1">
                                    <button type="button" wire:click="decreaseCopies" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold flex items-center justify-center cursor-pointer">-</button>
                                    <input type="number" min="1" max="1000" wire:model="copies" class="w-16 h-8 text-center rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm font-semibold focus:outline-none">
                                    <button type="button" wire:click="increaseCopies" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold flex items-center justify-center cursor-pointer">+</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Display Fields Checkboxes (Reference Parity) --}}
            <div class="mb-4 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">Display Fields:</label>
                <div class="flex flex-wrap items-center gap-4 sm:gap-6">
                    <label class="inline-flex items-center gap-2 text-xs sm:text-sm font-medium text-slate-700 dark:text-slate-300 cursor-pointer">
                        <input type="checkbox" wire:model="showName" class="w-4 h-4 rounded text-emerald-600"> Product Name
                    </label>
                    <label class="inline-flex items-center gap-2 text-xs sm:text-sm font-medium text-slate-700 dark:text-slate-300 cursor-pointer">
                        <input type="checkbox" wire:model="showCode" class="w-4 h-4 rounded text-emerald-600"> Product Code
                    </label>
                    <label class="inline-flex items-center gap-2 text-xs sm:text-sm font-medium text-slate-700 dark:text-slate-300 cursor-pointer">
                        <input type="checkbox" wire:model="showPrice" class="w-4 h-4 rounded text-emerald-600"> Product Price
                    </label>
                    <label class="inline-flex items-center gap-2 text-xs sm:text-sm font-medium text-slate-700 dark:text-slate-300 cursor-pointer">
                        <input type="checkbox" wire:model="showBarcode" class="w-4 h-4 rounded text-emerald-600"> Barcode
                    </label>
                </div>
            </div>

            {{-- Action Buttons (Height 38px) --}}
            <div class="flex items-center gap-2 mb-4">
                <button type="button" wire:click="generateProductLabels" class="h-[38px] px-5 rounded-xl bg-[#15803d] hover:bg-[#166534] text-white text-xs sm:text-sm font-semibold inline-flex items-center gap-2 shadow-2xs cursor-pointer border-0">
                    <i class="fa-solid fa-wand-magic-sparkles text-xs"></i>
                    <span>Generate</span>
                </button>
                <button type="button" wire:click="resetBarcodes" class="h-[38px] px-5 rounded-xl bg-[#dc2626] hover:bg-red-700 text-white text-xs sm:text-sm font-semibold inline-flex items-center gap-2 shadow-2xs cursor-pointer border-0">
                    <i class="fa-solid fa-rotate-left text-xs"></i>
                    <span>Reset</span>
                </button>
            </div>
        @else
            {{-- Sequential Range Form (Reference Parity: Start BarCode & End BarCode) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                <div>
                    <label class="block text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Start BarCode <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        wire:model="startBarcode"
                        placeholder="e.g. K-1 or 1"
                        class="w-full h-[38px] px-4 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium text-slate-800 dark:text-white outline-none focus:border-[#15803d]"
                    >
                </div>
                <div>
                    <label class="block text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">
                        End BarCode <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        wire:model="endBarcode"
                        placeholder="e.g. K-100 or 100"
                        class="w-full h-[38px] px-4 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium text-slate-800 dark:text-white outline-none focus:border-[#15803d]"
                    >
                </div>
            </div>

            <div class="flex items-center gap-2 mb-4">
                <button type="button" wire:click="generateSequentialLabels" class="h-[38px] px-5 rounded-xl bg-[#15803d] hover:bg-[#166534] text-white text-xs sm:text-sm font-semibold inline-flex items-center gap-2 shadow-2xs cursor-pointer border-0">
                    <i class="fa-solid fa-wand-magic-sparkles text-xs"></i>
                    <span>Generate</span>
                </button>
                <button type="button" wire:click="resetBarcodes" class="h-[38px] px-5 rounded-xl bg-[#dc2626] hover:bg-red-700 text-white text-xs sm:text-sm font-semibold inline-flex items-center gap-2 shadow-2xs cursor-pointer border-0">
                    <i class="fa-solid fa-rotate-left text-xs"></i>
                    <span>Reset</span>
                </button>
            </div>
        @endif

        {{-- Preview Section Header --}}
        <div class="border-t border-slate-200 dark:border-slate-800 pt-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                <div>
                    <h3 class="text-base font-bold text-slate-800 dark:text-white m-0">Barcode Preview @if(!empty($labels))<span class="text-xs font-semibold text-emerald-600">({{ count($labels) }})</span>@endif</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 mb-0">Review generated barcodes below</p>
                </div>
                <button
                    type="button"
                    onclick="window.print()"
                    @disabled(empty($labels))
                    class="h-[38px] px-4 rounded-xl bg-[#15803d] hover:bg-[#166534] disabled:opacity-50 text-white text-xs sm:text-sm font-semibold inline-flex items-center gap-2 cursor-pointer border-0"
                >
                    <i class="fa-solid fa-print text-xs"></i>
                    <span>Print A4</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Barcode Grid (Visible on screen & print) --}}
    <div
        id="barcodeGrid"
        wire:key="battery-barcodes-{{ md5(json_encode($labels)) }}"
        x-data="{
            init() {
                this.$nextTick(() => {
                    this.$el.querySelectorAll('svg[data-code]').forEach((svg) => {
                        if (window.JsBarcode) {
                            window.JsBarcode(svg, svg.dataset.code, {
                                format: 'CODE128',
                                displayValue: svg.dataset.showCode === '1',
                                width: 1.5,
                                height: 38,
                                fontSize: 11,
                                margin: 6
                            });
                        }
                    });
                });
            }
        }"
        class="mt-4 min-h-[140px] p-4 rounded-2xl border border-dashed border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3.5"
    >
        @forelse($labels as $lbl)
            <div class="barcode-sticker-item bg-white text-black border border-slate-200 rounded-xl p-3 flex flex-col items-center justify-center text-center shadow-2xs">
                @if(($lbl['show_name'] ?? true) && !empty($lbl['name']))
                    <p class="text-[11px] font-extrabold tracking-tight line-clamp-1 mb-0.5">{{ $lbl['name'] }}</p>
                @endif
                @if($lbl['show_barcode'] ?? true)
                    <svg data-code="{{ $lbl['code'] }}" data-show-code="{{ ($lbl['show_code'] ?? true) ? '1' : '0' }}" class="max-w-full"></svg>
                @elseif($lbl['show_code'] ?? true)
                    <p class="font-mono text-xs font-bold my-1">{{ $lbl['code'] }}</p>
                @endif
                @if(($lbl['show_price'] ?? true) && $lbl['price'] !== null)
                    <p class="text-xs font-extrabold mt-0.5 whitespace-nowrap">MRP: ৳ {{ number_format((float) $lbl['price'], 2) }}</p>
                @endif
            </div>
        @empty
            <div class="col-span-full flex flex-col items-center justify-center py-8 text-slate-400 text-xs font-semibold no-print">
                <i class="fa-solid fa-barcode text-3xl mb-2 opacity-40"></i>
                <span>Generated barcodes will appear here ready for A4 printing.</span>
            </div>
        @endforelse
    </div>

    <style>
        @media print {
            @page { size: A4; margin: 16px; }
            .no-print { display: none !important; }
            #barcodeGrid {
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
                background: #ffffff !important;
                display: grid !important;
                grid-template-columns: repeat(3, 1fr) !important;
                gap: 12px !important;
            }
            .barcode-sticker-item {
                border: 1px dashed #94a3b8 !important;
                box-shadow: none !important;
                page-break-inside: avoid;
            }
        }
    </style>
</div>
