@php
    use App\Support\Money;
    $defaultProductImg = asset('backend/assets/img/product-img.svg');
@endphp

<div>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-300 dark:border-slate-800 shadow-xs overflow-hidden p-3 sm:p-5">
        {{-- 1. Top Section: Page Title & + Add Product Button --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-3">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400 flex items-center justify-center border border-emerald-100 dark:border-slate-800 shadow-2xs shrink-0">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                        <line x1="12" y1="22.08" x2="12" y2="12"></line>
                    </svg>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-800 dark:text-white tracking-tight leading-none m-0">Product List</h1>
            </div>

            <div class="flex items-center flex-wrap gap-2">
                <button
                    type="button"
                    wire:click="openCreate"
                    class="h-[38px] px-4 rounded-xl bg-[#15803d] hover:bg-[#166534] text-white text-sm font-semibold shadow-xs inline-flex items-center gap-1.5 transition-all cursor-pointer"
                >
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>Add Product</span>
                </button>
            </div>
        </div>

        {{-- 2. Controls & Filter Row: Search Product | SHOW: 15 | All Brands | All Categories --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-2.5 sm:gap-3 mb-4">
            <div class="flex items-center gap-2 sm:gap-2.5 w-full md:w-auto flex-1">
                <div class="unified-ui-border flex-1 md:w-[260px] lg:w-[320px] h-[38px] flex items-center px-3 bg-white dark:bg-slate-800/90 rounded-xl shadow-2xs focus-within:border-[#15803d]">
                    <svg class="w-4 h-4 text-slate-400 shrink-0 mr-2 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search Product..."
                        class="w-full h-full bg-transparent border-0 outline-none text-xs sm:text-[13px] text-slate-800 dark:text-slate-100 placeholder:text-slate-400"
                    >
                </div>

                <div class="unified-ui-border h-[38px] px-3 rounded-xl bg-white dark:bg-slate-800/90 flex items-center justify-between gap-1.5 text-xs font-semibold text-slate-600 dark:text-slate-300 shadow-2xs shrink-0">
                    <span class="text-slate-400 text-[11px] font-bold uppercase tracking-wider">SHOW:</span>
                    <select wire:model.live="perPage" class="no-custom-select bg-transparent border-0 text-xs sm:text-sm font-bold text-[#15803d] dark:text-emerald-400 focus:outline-none cursor-pointer pr-1">
                        <option value="15">15</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="200">200</option>
                        <option value="500">500</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-2.5 w-full md:w-auto">
                {{-- Brand Filter Dropdown --}}
                <div x-data="{ open: false, searchBrand: '' }" @click.outside="open = false" class="custom-filter-dropdown flex-1 md:w-[180px] lg:w-[210px] relative">
                    <button
                        type="button"
                        @click="open = !open"
                        class="w-full unified-ui-border h-[38px] px-3 rounded-xl bg-white dark:bg-slate-800/90 text-xs sm:text-[13px] font-medium text-slate-700 dark:text-slate-200 flex items-center justify-between gap-1.5 shadow-2xs cursor-pointer hover:border-emerald-500 transition-colors"
                    >
                        <span class="truncate">
                            {{ $brandFilter === '' ? 'All Brands' : ($brands->firstWhere('id', $brandFilter)?->name ?? 'Brand') }}
                        </span>
                        <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transition-transform" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open" x-transition style="display: none;" class="select-menu">
                        @if($brands->count() > 8)
                            <div class="p-1.5 border-b border-slate-100 dark:border-slate-700">
                                <input type="text" x-model="searchBrand" placeholder="Search Brand..." class="w-full h-8 px-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs outline-none">
                            </div>
                        @endif
                        <div class="max-h-48 overflow-y-auto">
                            <div wire:click="$set('brandFilter', '')" @click="open = false" class="select-option-item {{ $brandFilter === '' ? 'active' : '' }}">
                                <span>All Brands</span>
                                @if($brandFilter === '') <i class="fa-solid fa-check text-xs text-emerald-600"></i> @endif
                            </div>
                            @foreach($brands as $b)
                                <div x-show="!searchBrand || '{{ strtolower(addslashes($b->name)) }}'.includes(searchBrand.toLowerCase())" wire:click="$set('brandFilter', '{{ $b->id }}')" @click="open = false" class="select-option-item {{ (string)$brandFilter === (string)$b->id ? 'active' : '' }}">
                                    <span>{{ $b->name }}</span>
                                    @if((string)$brandFilter === (string)$b->id) <i class="fa-solid fa-check text-xs text-emerald-600"></i> @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Category Filter Dropdown --}}
                <div x-data="{ open: false, searchCat: '' }" @click.outside="open = false" class="custom-filter-dropdown flex-1 md:w-[180px] lg:w-[210px] relative">
                    <button
                        type="button"
                        @click="open = !open"
                        class="w-full unified-ui-border h-[38px] px-3 rounded-xl bg-white dark:bg-slate-800/90 text-xs sm:text-[13px] font-medium text-slate-700 dark:text-slate-200 flex items-center justify-between gap-1.5 shadow-2xs cursor-pointer hover:border-emerald-500 transition-colors"
                    >
                        <span class="truncate">
                            {{ $categoryFilter === '' ? 'All Categories' : ($categories->firstWhere('id', $categoryFilter)?->category_name ?? 'Category') }}
                        </span>
                        <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transition-transform" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open" x-transition style="display: none; right: 0; left: auto;" class="select-menu">
                        @if($categories->count() > 8)
                            <div class="p-1.5 border-b border-slate-100 dark:border-slate-700">
                                <input type="text" x-model="searchCat" placeholder="Search Category..." class="w-full h-8 px-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs outline-none">
                            </div>
                        @endif
                        <div class="max-h-48 overflow-y-auto">
                            <div wire:click="$set('categoryFilter', '')" @click="open = false" class="select-option-item {{ $categoryFilter === '' ? 'active' : '' }}">
                                <span>All Categories</span>
                                @if($categoryFilter === '') <i class="fa-solid fa-check text-xs text-emerald-600"></i> @endif
                            </div>
                            @foreach($categories as $c)
                                <div x-show="!searchCat || '{{ strtolower(addslashes($c->category_name)) }}'.includes(searchCat.toLowerCase())" wire:click="$set('categoryFilter', '{{ $c->id }}')" @click="open = false" class="select-option-item {{ (string)$categoryFilter === (string)$c->id ? 'active' : '' }}">
                                    <span>{{ $c->category_name }}</span>
                                    @if((string)$categoryFilter === (string)$c->id) <i class="fa-solid fa-check text-xs text-emerald-600"></i> @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. Desktop Table (10 Columns) --}}
        <div class="table-responsive unified-ui-border hidden lg:block w-full overflow-x-auto rounded-2xl shadow-2xs bg-white dark:bg-slate-900">
            <table id="printTable" data-export-skip-last="true" class="w-full text-xs sm:text-[13px] text-left border-collapse">
                <thead>
                    <tr class="bg-[#15803d] text-white text-xs font-semibold uppercase tracking-wider whitespace-nowrap">
                        <th class="p-[10px] text-center w-[40px] rounded-tl-2xl whitespace-nowrap">SL</th>
                        <th class="p-[10px] text-start w-[115px] whitespace-nowrap">Barcode</th>
                        <th class="p-[10px] text-start whitespace-nowrap">Name</th>
                        <th class="p-[10px] text-start w-[135px] whitespace-nowrap">Category</th>
                        <th class="p-[10px] text-center w-[85px] whitespace-nowrap">Quantity</th>
                        <th class="p-[10px] text-end w-[95px] whitespace-nowrap">Cost Price</th>
                        <th class="p-[10px] text-end w-[115px] whitespace-nowrap">Total Cost Price</th>
                        <th class="p-[10px] text-end w-[95px] whitespace-nowrap">Selling Price</th>
                        <th class="p-[10px] text-center w-[95px] whitespace-nowrap">Stock</th>
                        <th class="p-[10px] text-center w-[105px] rounded-tr-2xl whitespace-nowrap">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-200">
                    @forelse($products as $idx => $p)
                        @php
                            $qty = (int) round((float) ($p->quantity ?? 0));
                            $cost = (float) ($p->cost_price ?? 0);
                            $sell = (float) ($p->sell_price ?? 0);
                            $totalCost = $cost * $qty;
                            $unitName = $p->unit?->unit_name ?? '';
                            $codes = $p->decodedCodes();
                            $imgUrl = ($p->img_url && file_exists(public_path($p->img_url))) ? asset($p->img_url) : $defaultProductImg;
                        @endphp
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors border-b border-slate-100 dark:border-slate-800">
                            <td class="p-[10px] text-center font-semibold text-slate-400 dark:text-slate-500">{{ ($products->firstItem() ?? 1) + $idx }}</td>
                            <td class="p-[10px] text-start">
                                @if(!empty($codes))
                                    <div class="flex flex-wrap gap-1">
                                        @foreach(array_slice($codes, 0, 2) as $code)
                                            <span
                                                class="barcode-badge cursor-pointer"
                                                data-barcode="{{ $code }}"
                                                title="{{ $code }} (Click to copy)"
                                                onclick="navigator.clipboard?.writeText('{{ addslashes($code) }}'); window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Barcode copied: {{ addslashes($code) }}' } }));"
                                            >
                                                <span class="barcode-text">{{ $code }}</span>
                                            </span>
                                        @endforeach
                                        @if(count($codes) > 2)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300 border border-slate-300 dark:border-slate-700">+{{ count($codes) - 2 }}</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-400 font-mono text-xs select-none font-semibold">-</span>
                                @endif
                            </td>
                            <td class="p-[10px] text-start">
                                <div class="font-bold text-slate-800 dark:text-slate-100 text-sm leading-snug">
                                    {{ $p->product_name }}
                                    @if($unitName)
                                        <span class="text-slate-400 dark:text-slate-500 font-normal text-xs">({{ $unitName }})</span>
                                    @endif
                                </div>
                                @if($p->door_side)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-slate-800 mt-0.5">
                                        {{ $p->door_side }}
                                    </span>
                                @endif
                            </td>
                            <td class="p-[10px] text-start">
                                <span class="font-medium text-slate-700 dark:text-slate-200">{{ $p->category?->category_name ?? '-' }}</span>
                                @if($p->brand?->name)
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 flex items-center gap-1">
                                        <svg class="w-3 h-3 text-emerald-600 dark:text-emerald-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                                            <line x1="7" y1="7" x2="7.01" y2="7"></line>
                                        </svg>
                                        <span>{{ $p->brand->name }}</span>
                                    </div>
                                @endif
                            </td>
                            <td class="p-[10px] text-center">
                                <span class="font-bold text-slate-800 dark:text-slate-100 text-sm">{{ $qty }}</span>
                            </td>
                            <td class="p-[10px] text-end font-medium text-slate-600 dark:text-slate-300 whitespace-nowrap tabular-nums">{{ Money::formatBd($cost) }}</td>
                            <td class="p-[10px] text-end font-bold text-slate-900 dark:text-white whitespace-nowrap tabular-nums">{{ Money::formatBd($totalCost) }}</td>
                            <td class="p-[10px] text-end font-semibold text-emerald-600 dark:text-emerald-400 whitespace-nowrap tabular-nums">{{ Money::formatBd($sell) }}</td>
                            <td class="p-[10px] text-center">
                                <span class="status-pill {{ $qty > 0 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400' : 'bg-rose-100 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400' }}">
                                    {{ $qty > 0 ? 'Available' : 'Out of Stock' }}
                                </span>
                            </td>
                            <td class="p-[10px] text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" wire:click="openQuickView({{ $p->id }})" class="quick-view-btn w-[30px] h-[30px] rounded-lg border flex items-center justify-center transition-all shadow-sm cursor-pointer" title="Quick View Product">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                    </button>
                                    <button type="button" wire:click="openEdit({{ $p->id }})" class="action-btn-edit w-[30px] h-[30px] rounded-lg bg-emerald-50 hover:bg-emerald-600 text-emerald-600 hover:text-white border border-emerald-200/80 hover:border-emerald-600 dark:bg-emerald-950/40 dark:border-slate-800 dark:text-emerald-400 flex items-center justify-center transition-all shadow-sm cursor-pointer" title="Edit Product">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                                    </button>
                                    <button type="button" wire:click="confirmDelete({{ $p->id }})" class="w-[30px] h-[30px] rounded-lg bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white border border-rose-200/80 hover:border-rose-600 dark:bg-rose-950/40 dark:border-slate-800 dark:text-rose-400 flex items-center justify-center transition-all shadow-sm cursor-pointer" title="Delete Product">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr data-empty-row="true">
                            <td colspan="10" class="px-4 py-8 text-center text-rose-500 font-bold">❌ No products found.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-50/90 dark:bg-slate-800/70 text-slate-800 dark:text-slate-100 font-bold border-t-2 border-emerald-600/30 text-xs sm:text-[13px]">
                    <tr>
                        <td colspan="4" class="p-[10px] text-end font-bold text-slate-600 dark:text-slate-300">Total:</td>
                        <td class="p-[10px] text-center font-bold text-slate-900 dark:text-white whitespace-nowrap">{{ (int)$totalQuantity }}</td>
                        <td class="p-[10px] text-end font-bold text-slate-900 dark:text-white whitespace-nowrap tabular-nums">{{ Money::formatBd($totalCostPrice) }}</td>
                        <td class="p-[10px] text-end font-bold text-emerald-700 dark:text-emerald-400 whitespace-nowrap tabular-nums">{{ Money::formatBd($totalCostQuantityPrice) }}</td>
                        <td class="p-[10px] text-end font-bold text-slate-900 dark:text-white whitespace-nowrap tabular-nums">{{ Money::formatBd($totalSellingPrice) }}</td>
                        <td class="p-[10px]"></td>
                        <td class="p-[10px]"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- 4. Mobile & Tablet Card List View (1-col Mobile, 2-col Tablet) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:hidden gap-3 mb-3">
            @foreach($products as $idx => $p)
                @php
                    $qty = (int) round((float) ($p->quantity ?? 0));
                    $cost = (float) ($p->cost_price ?? 0);
                    $sell = (float) ($p->sell_price ?? 0);
                    $codes = $p->decodedCodes();
                    $primaryCode = $codes[0] ?? '';
                    $imgUrl = ($p->img_url && file_exists(public_path($p->img_url))) ? asset($p->img_url) : $defaultProductImg;
                @endphp
                <div class="unified-ui-border rounded-2xl p-3.5 bg-white dark:bg-slate-800/80 shadow-2xs space-y-2">
                    <div class="flex items-center justify-between pb-2 mb-1 border-b border-slate-100 dark:border-slate-700">
                        <div class="flex items-center gap-1.5">
                            <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs">#{{ ($products->firstItem() ?? 1) + $idx }}</span>
                            @if($primaryCode !== '')
                                <span
                                    class="barcode-badge cursor-pointer"
                                    data-barcode="{{ $primaryCode }}"
                                    onclick="navigator.clipboard?.writeText('{{ addslashes($primaryCode) }}'); window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Barcode copied: {{ addslashes($primaryCode) }}' } }));"
                                ><span class="barcode-text">{{ $primaryCode }}</span></span>
                            @endif
                        </div>
                        <span class="status-pill {{ $qty > 0 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400' : 'bg-rose-100 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400' }}">
                            {{ $qty > 0 ? 'Available' : 'Out of Stock' }}
                        </span>
                    </div>

                    <div class="flex items-start gap-2.5">
                        <img src="{{ $imgUrl }}" alt="{{ $p->product_name }}" wire:click="openImageModal('{{ $imgUrl }}', '{{ addslashes($p->product_name) }}')" class="w-10 h-10 rounded-lg object-cover border border-slate-200 dark:border-slate-700 shrink-0 cursor-pointer bg-white p-0.5">
                        <div>
                            <h4 class="font-bold text-slate-900 dark:text-white text-sm">{{ $p->product_name }}</h4>
                            <div class="flex items-center gap-2 text-xs text-slate-500 mt-0.5">
                                <span>{{ $p->category?->category_name ?? '-' }}</span>
                                @if($p->brand?->name)<span>&bull; {{ $p->brand->name }}</span>@endif
                            </div>
                        </div>
                    </div>

                    <div class="mobile-fin-grid">
                        <div class="flex items-center justify-between px-2.5 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-900/70 border border-slate-200 dark:border-slate-700">
                            <span class="text-slate-500 text-xs">Qty:</span>
                            <span class="font-bold text-slate-800 dark:text-slate-100 text-xs">{{ $qty }}</span>
                        </div>
                        <div class="flex items-center justify-between px-2.5 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-900/70 border border-slate-200 dark:border-slate-700">
                            <span class="text-slate-500 text-xs">Cost:</span>
                            <span class="font-bold text-slate-800 dark:text-slate-100 text-xs whitespace-nowrap">{{ Money::formatBd($cost) }}</span>
                        </div>
                        <div class="flex items-center justify-between px-2.5 py-1.5 rounded-xl bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-slate-700">
                            <span class="text-emerald-600 text-xs font-semibold">Sell:</span>
                            <span class="font-bold text-emerald-600 text-xs whitespace-nowrap">{{ Money::formatBd($sell) }}</span>
                        </div>
                        <div class="flex items-center justify-between px-2.5 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-900/70 border border-slate-200 dark:border-slate-700">
                            <span class="text-slate-500 text-xs">Total:</span>
                            <span class="font-bold text-slate-800 dark:text-slate-100 text-xs whitespace-nowrap">{{ Money::formatBd($cost * $qty) }}</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-1.5 pt-2 border-t border-slate-100 dark:border-slate-700">
                        <button type="button" wire:click="openQuickView({{ $p->id }})" class="action-btn quick-view-btn w-[30px] h-[30px] rounded-lg flex items-center justify-center shadow-sm cursor-pointer" title="Quick View">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </button>
                        <button type="button" wire:click="openEdit({{ $p->id }})" class="action-btn-edit w-[30px] h-[30px] rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center shadow-sm cursor-pointer" title="Edit">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                        </button>
                        <button type="button" wire:click="confirmDelete({{ $p->id }})" class="w-[30px] h-[30px] rounded-lg bg-rose-50 text-rose-600 border border-rose-200 flex items-center justify-center shadow-sm cursor-pointer" title="Delete">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- 5. Modern Smart Pagination and Display Info Footer --}}
        <div class="flex flex-col sm:flex-row items-center justify-between pt-4 mt-4 border-t border-slate-200 dark:border-slate-800 gap-3">
            <div class="flex items-center gap-1.5 text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400">
                <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
                <span>Showing</span>
                <span class="font-bold text-slate-800 dark:text-slate-100 px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700">{{ $products->firstItem() ?? 0 }} – {{ $products->lastItem() ?? 0 }}</span>
                <span>of</span>
                <span class="font-bold text-emerald-700 dark:text-emerald-400 px-1.5 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-slate-700">{{ $products->total() }}</span>
                <span>products</span>
            </div>
            <div class="w-full sm:w-auto flex items-center justify-center sm:justify-end">{{ $products->links() }}</div>
        </div>
    </div>

    {{-- Add New / Edit Product Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 bg-slate-900/65 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 overflow-y-auto" wire:click.self="closeModal">
            <div class="w-full max-w-[740px] max-h-[90vh] bg-white dark:bg-slate-900 rounded-2xl shadow-2xl overflow-hidden flex flex-col my-auto border-0">
                <div class="sticky top-0 z-20 px-4 py-3 bg-[#15803d] text-white flex items-center justify-between shadow-sm shrink-0">
                    <h5 class="text-base sm:text-lg font-bold text-white flex items-center gap-2 m-0">
                        <i class="fa-solid fa-cart-plus me-1"></i> {{ $editingId ? 'Edit Product' : 'Add New Product' }}
                    </h5>
                    <button type="button" wire:click="closeModal" class="w-6 h-6 rounded-full bg-red-500 hover:bg-red-600 text-white flex items-center justify-center border-0 shadow-sm cursor-pointer transition-transform hover:scale-110" title="Close">
                        <i class="fa-solid fa-xmark text-[10px]"></i>
                    </button>
                </div>

                <div class="p-4 sm:p-6 overflow-y-auto flex-1 max-h-[calc(90vh-120px)] space-y-3.5">
                    <form wire:submit="save" id="productForm">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            {{-- Brand Custom Select + Quick Add --}}
                            <div x-data="{ open: false, q: '' }" @click.outside="open = false" class="relative">
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Brand</label>
                                <div class="flex items-center gap-1.5">
                                    <div class="relative flex-1 min-w-0">
                                        <button type="button" @click="open = !open" class="w-full h-[42px] px-3 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium text-slate-800 dark:text-slate-100 flex items-center justify-between gap-1 cursor-pointer hover:border-[#15803d]">
                                            <span class="truncate">{{ $brand_id ? ($brands->firstWhere('id', $brand_id)?->name ?? 'Select Brand') : 'Select Brand' }}</span>
                                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 shrink-0" :class="open ? 'rotate-180' : ''"></i>
                                        </button>
                                        <div x-show="open" x-transition style="display: none;" class="absolute left-0 right-0 top-[calc(100%+4px)] z-50 bg-white dark:bg-slate-900 border-[1.5px] border-slate-300 dark:border-slate-700 rounded-xl shadow-xl p-1.5">
                                            @if($brands->count() > 8)
                                                <input type="text" x-model="q" placeholder="Search Brand..." class="w-full h-8 px-2 mb-1 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs outline-none">
                                            @endif
                                            <div class="max-h-44 overflow-y-auto">
                                                <div wire:click="$set('brand_id', null)" @click="open = false" class="px-2.5 py-1.5 rounded-lg text-xs cursor-pointer hover:bg-emerald-50 dark:hover:bg-slate-800 {{ !$brand_id ? 'bg-emerald-50 text-emerald-700 font-bold' : '' }}">Select Brand</div>
                                                @foreach($brands as $b)
                                                    <div x-show="!q || '{{ strtolower(addslashes($b->name)) }}'.includes(q.toLowerCase())" wire:click="$set('brand_id', {{ $b->id }})" @click="open = false; q = ''" class="px-2.5 py-1.5 rounded-lg text-xs cursor-pointer hover:bg-emerald-50 dark:hover:bg-slate-800 flex items-center justify-between {{ (int)$brand_id === (int)$b->id ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 font-bold' : '' }}">
                                                        <span class="truncate">{{ $b->name }}</span>
                                                        @if((int)$brand_id === (int)$b->id)<i class="fa-solid fa-check text-[10px] text-emerald-600"></i>@endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" wire:click="openQuickBrand" class="w-[42px] h-[42px] rounded-lg bg-[#16a34a] hover:bg-[#15803d] text-white flex items-center justify-center shrink-0 cursor-pointer border-0" title="Add New Brand">
                                        <i class="fa-solid fa-plus text-base font-bold"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- Category Custom Select + Quick Add --}}
                            <div x-data="{ open: false, q: '' }" @click.outside="open = false" class="relative">
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Category <span class="text-rose-500">*</span></label>
                                <div class="flex items-center gap-1.5">
                                    <div class="relative flex-1 min-w-0">
                                        <button type="button" @click="open = !open" class="w-full h-[42px] px-3 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium text-slate-800 dark:text-slate-100 flex items-center justify-between gap-1 cursor-pointer hover:border-[#15803d]">
                                            <span class="truncate">{{ $category_id ? ($categories->firstWhere('id', $category_id)?->category_name ?? 'Select Category') : 'Select Category' }}</span>
                                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 shrink-0" :class="open ? 'rotate-180' : ''"></i>
                                        </button>
                                        <div x-show="open" x-transition style="display: none;" class="absolute left-0 right-0 top-[calc(100%+4px)] z-50 bg-white dark:bg-slate-900 border-[1.5px] border-slate-300 dark:border-slate-700 rounded-xl shadow-xl p-1.5">
                                            @if($categories->count() > 8)
                                                <input type="text" x-model="q" placeholder="Search Category..." class="w-full h-8 px-2 mb-1 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs outline-none">
                                            @endif
                                            <div class="max-h-44 overflow-y-auto">
                                                <div wire:click="$set('category_id', null)" @click="open = false" class="px-2.5 py-1.5 rounded-lg text-xs cursor-pointer hover:bg-emerald-50 dark:hover:bg-slate-800 {{ !$category_id ? 'bg-emerald-50 text-emerald-700 font-bold' : '' }}">Select Category</div>
                                                @foreach($categories as $c)
                                                    <div x-show="!q || '{{ strtolower(addslashes($c->category_name)) }}'.includes(q.toLowerCase())" wire:click="$set('category_id', {{ $c->id }})" @click="open = false; q = ''" class="px-2.5 py-1.5 rounded-lg text-xs cursor-pointer hover:bg-emerald-50 dark:hover:bg-slate-800 flex items-center justify-between {{ (int)$category_id === (int)$c->id ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 font-bold' : '' }}">
                                                        <span class="truncate">{{ $c->category_name }}</span>
                                                        @if((int)$category_id === (int)$c->id)<i class="fa-solid fa-check text-[10px] text-emerald-600"></i>@endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" wire:click="openQuickCategory" class="w-[42px] h-[42px] rounded-lg bg-[#16a34a] hover:bg-[#15803d] text-white flex items-center justify-center shrink-0 cursor-pointer border-0" title="Add New Category">
                                        <i class="fa-solid fa-plus text-base font-bold"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- Sub-Category Custom Select + Quick Add --}}
                            <div x-data="{ open: false, q: '' }" @click.outside="open = false" class="relative">
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Sub-Category</label>
                                <div class="flex items-center gap-1.5">
                                    <div class="relative flex-1 min-w-0">
                                        <button type="button" @click="open = !open" class="w-full h-[42px] px-3 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium text-slate-800 dark:text-slate-100 flex items-center justify-between gap-1 cursor-pointer hover:border-[#15803d]">
                                            <span class="truncate">{{ $sub_category_id ? ($subCategories->firstWhere('id', $sub_category_id)?->sub_category_name ?? 'Select Sub-Category') : 'Select Sub-Category' }}</span>
                                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 shrink-0" :class="open ? 'rotate-180' : ''"></i>
                                        </button>
                                        <div x-show="open" x-transition style="display: none;" class="absolute left-0 right-0 top-[calc(100%+4px)] z-50 bg-white dark:bg-slate-900 border-[1.5px] border-slate-300 dark:border-slate-700 rounded-xl shadow-xl p-1.5">
                                            @if($subCategories->count() > 8)
                                                <input type="text" x-model="q" placeholder="Search Sub-Category..." class="w-full h-8 px-2 mb-1 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs outline-none">
                                            @endif
                                            <div class="max-h-44 overflow-y-auto">
                                                <div wire:click="$set('sub_category_id', null)" @click="open = false" class="px-2.5 py-1.5 rounded-lg text-xs cursor-pointer hover:bg-emerald-50 dark:hover:bg-slate-800 {{ !$sub_category_id ? 'bg-emerald-50 text-emerald-700 font-bold' : '' }}">Select Sub-Category</div>
                                                @foreach($subCategories as $sc)
                                                    <div x-show="!q || '{{ strtolower(addslashes($sc->sub_category_name)) }}'.includes(q.toLowerCase())" wire:click="$set('sub_category_id', {{ $sc->id }})" @click="open = false; q = ''" class="px-2.5 py-1.5 rounded-lg text-xs cursor-pointer hover:bg-emerald-50 dark:hover:bg-slate-800 flex items-center justify-between {{ (int)$sub_category_id === (int)$sc->id ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 font-bold' : '' }}">
                                                        <span class="truncate">{{ $sc->sub_category_name }}</span>
                                                        @if((int)$sub_category_id === (int)$sc->id)<i class="fa-solid fa-check text-[10px] text-emerald-600"></i>@endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" wire:click="openQuickSubCategory" class="w-[42px] h-[42px] rounded-lg bg-[#16a34a] hover:bg-[#15803d] text-white flex items-center justify-center shrink-0 cursor-pointer border-0" title="Add New Sub-Category">
                                        <i class="fa-solid fa-plus text-base font-bold"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Product Photo Preview & Reference Upload Button --}}
                        <div class="mt-3">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Product Photo</label>
                            <div class="flex items-center gap-3.5 p-3 rounded-xl border-[1.5px] border-dashed border-slate-300 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/40">
                                <div class="w-[72px] h-[64px] rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-2xs flex items-center justify-center overflow-hidden shrink-0">
                                    @if($image)
                                        <img src="{{ $image->temporaryUrl() }}" class="w-full h-full object-cover">
                                    @elseif($existingImage && file_exists(public_path($existingImage)))
                                        <img src="{{ asset($existingImage) }}" class="w-full h-full object-cover">
                                    @else
                                        <i class="fa-regular fa-image text-2xl text-slate-400"></i>
                                    @endif
                                </div>
                                <div>
                                    <label for="prodPhotoInput" class="inline-flex items-center gap-2 px-3.5 h-9 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:border-emerald-500 hover:text-emerald-700 shadow-2xs cursor-pointer transition-colors">
                                        <i class="fa-solid fa-cloud-arrow-up text-slate-600 dark:text-slate-300"></i>
                                        <span>Upload Photo</span>
                                    </label>
                                    <input type="file" wire:model="image" id="prodPhotoInput" accept="image/*" class="hidden">
                                    <p class="text-[11px] text-slate-400 mt-1.5 mb-0">PNG, JPEG or GIF (up to 1 MB)</p>
                                </div>
                            </div>
                        </div>

                        {{-- Product Name * --}}
                        <div class="mt-3">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Product Name <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="product_name" placeholder="Product Name *" class="w-full h-[42px] px-3 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium text-slate-800 dark:text-slate-100 outline-none focus:border-[#15803d]">
                            @error('product_name') <span class="text-xs text-rose-500 font-semibold">{{ $message }}</span> @enderror
                        </div>

                        {{-- 3 Columns: Quantity, Cost Price, Selling Price --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Product Quantity</label>
                                <input type="number" wire:model="quantity" placeholder="Quantity" class="w-full h-[42px] px-3 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium text-slate-800 dark:text-slate-100 outline-none focus:border-[#15803d]">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Cost Price</label>
                                <input type="number" step="0.01" wire:model="cost_price" placeholder="Cost Price" class="w-full h-[42px] px-3 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium text-slate-800 dark:text-slate-100 outline-none focus:border-[#15803d]">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Selling Price</label>
                                <input type="number" step="0.01" wire:model="sell_price" placeholder="Selling Price" class="w-full h-[42px] px-3 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium text-slate-800 dark:text-slate-100 outline-none focus:border-[#15803d]">
                            </div>
                        </div>

                        {{-- Barcode / Product Code + Camera Scan --}}
                        <div class="mt-3">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Barcode / Product Code</label>
                            <div class="flex items-center gap-2">
                                <input type="text" wire:model="product_code" placeholder="Enter or scan barcode..." class="flex-1 h-[42px] px-3 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium text-slate-800 dark:text-slate-100 outline-none focus:border-[#15803d]">
                                <button
                                    type="button"
                                    onclick="if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) { window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: 'Camera device unavailable on this browser. Please type or scan barcode with USB scanner.' } })); } else { navigator.mediaDevices.enumerateDevices().then(d => { const hasCam = d.some(x => x.kind === 'videoinput'); if (!hasCam) { window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: 'No camera device detected. You can type or scan with a USB barcode scanner.' } })); } else { window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'info', message: 'Camera detected. Focus the barcode field to scan.' } })); } }).catch(() => window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: 'Unable to access camera device.' } }))); }"
                                    class="h-[42px] px-3.5 rounded-lg bg-[#15803d] hover:bg-[#166534] text-white text-xs sm:text-sm font-bold flex items-center gap-2 shadow-xs shrink-0 cursor-pointer"
                                >
                                    <i class="fa-solid fa-camera"></i>
                                    <span class="hidden sm:inline">Camera Scan</span>
                                </button>
                            </div>
                            @error('product_code') <span class="text-xs text-rose-500 font-semibold">{{ $message }}</span> @enderror
                        </div>

                        @if($editingId)
                            <div class="mt-3">
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Status</label>
                                <select wire:model="status" class="w-full h-[42px] px-3 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium text-slate-800 dark:text-slate-100 outline-none focus:border-[#15803d]">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        @endif
                    </form>
                </div>

                {{-- Sticky Footer (Reset + Submit) --}}
                <div class="sticky bottom-0 z-20 px-4 py-2.5 border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 flex items-center justify-end gap-2 shrink-0">
                    <button type="button" wire:click="resetForm" class="h-[40px] px-4 rounded-lg bg-[#dc2626] hover:bg-red-700 text-white text-sm font-semibold cursor-pointer border-0">Reset</button>
                    <button type="submit" form="productForm" class="h-[40px] px-5 rounded-lg bg-[#15803d] hover:bg-[#166534] text-white text-sm font-bold cursor-pointer border-0 shadow-sm">Submit</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Reference Parity: Add New Brand Modal (Screenshot 2) --}}
    @if($showQuickBrandModal)
        <div class="fixed inset-0 z-[60] bg-slate-900/75 backdrop-blur-xs flex items-center justify-center p-4" wire:click.self="$set('showQuickBrandModal', false)">
            <div class="w-full max-w-[440px] bg-white dark:bg-slate-900 rounded-2xl shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-700 p-6">
                <div class="flex items-center justify-between pb-3.5 mb-4 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white m-0">Add New Brand</h3>
                    <button type="button" wire:click="$set('showQuickBrandModal', false)" class="w-7 h-7 rounded-full bg-[#ef4444] hover:bg-red-600 text-white flex items-center justify-center border-0 cursor-pointer shadow-xs">
                        <i class="fa-solid fa-xmark text-xs"></i>
                    </button>
                </div>
                <form wire:submit="saveQuickBrand" class="space-y-4">
                    <div class="flex items-center gap-3.5 p-3.5 rounded-xl border-[1.5px] border-dashed border-slate-300 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/40">
                        <div class="w-16 h-16 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-2xs flex items-center justify-center overflow-hidden shrink-0">
                            @if($quickBrandLogo)
                                <img src="{{ $quickBrandLogo->temporaryUrl() }}" class="w-full h-full object-cover">
                            @else
                                <i class="fa-regular fa-image text-2xl text-slate-400"></i>
                            @endif
                        </div>
                        <div>
                            <label for="quickRetailBrandImg" class="inline-flex items-center gap-2 px-3.5 h-9 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:border-emerald-500 hover:text-emerald-700 shadow-2xs cursor-pointer">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                                <span>Upload Photo</span>
                            </label>
                            <input type="file" wire:model="quickBrandLogo" id="quickRetailBrandImg" accept="image/*" class="hidden">
                            <p class="text-[11px] text-slate-400 mt-1.5 mb-0">PNG, JPG or GIF (up to 1 MB)</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Brand Name <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="quickBrandName" placeholder="Enter brand name" class="w-full h-[42px] px-3.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm outline-none focus:border-[#16a34a]">
                        @error('quickBrandName') <span class="text-xs text-rose-500 font-semibold">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Status <span class="text-rose-500">*</span></label>
                        <select wire:model="quickBrandStatus" class="w-full h-[42px] px-3.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm outline-none focus:border-[#16a34a]">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" wire:click="$set('showQuickBrandModal', false)" class="h-10 px-5 rounded-lg bg-[#ef4444] hover:bg-red-600 text-white text-xs sm:text-sm font-bold border-0 cursor-pointer">Cancel</button>
                        <button type="submit" class="h-10 px-5 rounded-lg bg-[#15803d] hover:bg-[#166534] text-white text-xs sm:text-sm font-bold border-0 cursor-pointer shadow-xs">Save Brand</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Reference Parity: Add New Category Modal --}}
    @if($showQuickCategoryModal)
        <div class="fixed inset-0 z-[60] bg-slate-900/75 backdrop-blur-xs flex items-center justify-center p-4" wire:click.self="$set('showQuickCategoryModal', false)">
            <div class="w-full max-w-[440px] bg-white dark:bg-slate-900 rounded-2xl shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-700 p-6">
                <div class="flex items-center justify-between pb-3.5 mb-4 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white m-0">Add New Category</h3>
                    <button type="button" wire:click="$set('showQuickCategoryModal', false)" class="w-7 h-7 rounded-full bg-[#ef4444] hover:bg-red-600 text-white flex items-center justify-center border-0 cursor-pointer shadow-xs">
                        <i class="fa-solid fa-xmark text-xs"></i>
                    </button>
                </div>
                <form wire:submit="saveQuickCategory" class="space-y-4">
                    <div class="flex items-center gap-3.5 p-3.5 rounded-xl border-[1.5px] border-dashed border-slate-300 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/40">
                        <div class="w-16 h-16 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-2xs flex items-center justify-center overflow-hidden shrink-0">
                            @if($quickCategoryImage)
                                <img src="{{ $quickCategoryImage->temporaryUrl() }}" class="w-full h-full object-cover">
                            @else
                                <i class="fa-regular fa-image text-2xl text-slate-400"></i>
                            @endif
                        </div>
                        <div>
                            <label for="quickRetailCategoryImg" class="inline-flex items-center gap-2 px-3.5 h-9 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:border-emerald-500 hover:text-emerald-700 shadow-2xs cursor-pointer">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                                <span>Upload Photo</span>
                            </label>
                            <input type="file" wire:model="quickCategoryImage" id="quickRetailCategoryImg" accept="image/*" class="hidden">
                            <p class="text-[11px] text-slate-400 mt-1.5 mb-0">PNG, JPG or GIF (up to 1 MB)</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Category Name <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="quickCategoryName" placeholder="Enter category name" class="w-full h-[42px] px-3.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm outline-none focus:border-[#16a34a]">
                        @error('quickCategoryName') <span class="text-xs text-rose-500 font-semibold">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Status <span class="text-rose-500">*</span></label>
                        <select wire:model="quickCategoryStatus" class="w-full h-[42px] px-3.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm outline-none focus:border-[#16a34a]">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" wire:click="$set('showQuickCategoryModal', false)" class="h-10 px-5 rounded-lg bg-[#ef4444] hover:bg-red-600 text-white text-xs sm:text-sm font-bold border-0 cursor-pointer">Cancel</button>
                        <button type="submit" class="h-10 px-5 rounded-lg bg-[#15803d] hover:bg-[#166534] text-white text-xs sm:text-sm font-bold border-0 cursor-pointer shadow-xs">Save Category</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Reference Parity: Add New Sub-Category Modal (Screenshot 3) --}}
    @if($showQuickSubCategoryModal)
        <div class="fixed inset-0 z-[60] bg-slate-900/75 backdrop-blur-xs flex items-center justify-center p-4" wire:click.self="$set('showQuickSubCategoryModal', false)">
            <div class="w-full max-w-[440px] bg-white dark:bg-slate-900 rounded-2xl shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-700 p-6">
                <div class="flex items-center justify-between pb-3.5 mb-4 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white m-0">Add New Sub-Category</h3>
                    <button type="button" wire:click="$set('showQuickSubCategoryModal', false)" class="w-7 h-7 rounded-full bg-[#ef4444] hover:bg-red-600 text-white flex items-center justify-center border-0 cursor-pointer shadow-xs">
                        <i class="fa-solid fa-xmark text-xs"></i>
                    </button>
                </div>
                <form wire:submit="saveQuickSubCategory" class="space-y-4">
                    <div x-data="{ open: false, q: '' }" @click.outside="open = false" class="relative">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Category <span class="text-rose-500">*</span></label>
                        <button type="button" @click="open = !open" class="w-full h-[42px] px-3.5 rounded-lg border-[1.5px] border-[#16a34a] bg-white dark:bg-slate-800 text-sm font-medium text-slate-700 dark:text-slate-100 flex items-center justify-between gap-2 cursor-pointer">
                            <span class="truncate">{{ $quickSubCategoryParentId ? ($categories->firstWhere('id', $quickSubCategoryParentId)?->category_name ?? 'Select Category *') : 'Select Category *' }}</span>
                            <i class="fa-solid fa-chevron-down text-xs text-slate-400" :class="open ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="open" x-transition style="display: none;" class="absolute left-0 right-0 top-[calc(100%+4px)] z-50 bg-white dark:bg-slate-900 border-[1.5px] border-slate-300 dark:border-slate-700 rounded-xl shadow-xl p-1.5">
                            @if($categories->count() > 8)
                                <input type="text" x-model="q" placeholder="Search Category..." class="w-full h-8 px-2 mb-1 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs outline-none">
                            @endif
                            <div class="max-h-44 overflow-y-auto">
                                @foreach($categories as $c)
                                    <div x-show="!q || '{{ strtolower(addslashes($c->category_name)) }}'.includes(q.toLowerCase())" wire:click="$set('quickSubCategoryParentId', {{ $c->id }})" @click="open = false; q = ''" class="px-2.5 py-1.5 rounded-lg text-xs cursor-pointer hover:bg-emerald-50 dark:hover:bg-slate-800 flex items-center justify-between {{ (int)$quickSubCategoryParentId === (int)$c->id ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 font-bold' : '' }}">
                                        <span>{{ $c->category_name }}</span>
                                        @if((int)$quickSubCategoryParentId === (int)$c->id)<i class="fa-solid fa-check text-[10px] text-emerald-600"></i>@endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @error('quickSubCategoryParentId') <span class="text-xs text-rose-500 font-semibold">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Sub-Category Name <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="quickSubCategoryName" placeholder="Enter sub-category name" class="w-full h-[42px] px-3.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm outline-none focus:border-[#16a34a]">
                        @error('quickSubCategoryName') <span class="text-xs text-rose-500 font-semibold">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Status <span class="text-rose-500">*</span></label>
                        <select wire:model="quickSubCategoryStatus" class="w-full h-[42px] px-3.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm outline-none focus:border-[#16a34a]">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" wire:click="$set('showQuickSubCategoryModal', false)" class="h-10 px-5 rounded-lg bg-[#ef4444] hover:bg-red-600 text-white text-xs sm:text-sm font-bold border-0 cursor-pointer">Cancel</button>
                        <button type="submit" class="h-10 px-5 rounded-lg bg-[#15803d] hover:bg-[#166534] text-white text-xs sm:text-sm font-bold border-0 cursor-pointer shadow-xs">Save Sub-Category</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Product Details Quick View Modal (With Barcode Badges + Hover Marquee) --}}
    @if($showQuickViewModal && $quickViewProduct)
        @php
            $qvQty = (int) round((float) ($quickViewProduct->quantity ?? 0));
            $qvCost = (float) ($quickViewProduct->cost_price ?? 0);
            $qvSell = (float) ($quickViewProduct->sell_price ?? 0);
            $qvTotal = $qvCost * $qvQty;
            $qvCodes = $quickViewProduct->decodedCodes();
        @endphp
        <div class="fixed inset-0 z-50 bg-slate-900/65 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4" wire:click.self="closeModal">
            <div class="w-full max-w-[760px] max-h-[88vh] bg-white dark:bg-slate-900 rounded-2xl shadow-2xl overflow-hidden flex flex-col border-0">
                <div class="sticky top-0 z-20 px-4 py-3 bg-[#15803d] text-white flex items-center justify-between shadow-sm shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-white/20 text-white flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </div>
                        <div>
                            <h5 class="text-base sm:text-lg font-bold text-white tracking-tight mb-0.5">{{ $quickViewProduct->product_name }}</h5>
                            <span class="text-xs text-emerald-100">{{ $quickViewProduct->category?->category_name ?? '-' }}</span>
                        </div>
                    </div>
                    <button type="button" wire:click="closeModal" class="w-7 h-7 rounded-full bg-red-500 hover:bg-red-600 text-white flex items-center justify-center border-0 shadow-sm cursor-pointer" title="Close">
                        <i class="fa-solid fa-xmark text-xs"></i>
                    </button>
                </div>

                <div class="p-4 space-y-3.5 overflow-y-auto flex-1 max-h-[calc(88vh-120px)]">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-center">
                            <span class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Stock Status</span>
                            <span class="status-pill {{ $qvQty > 0 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400' : 'bg-rose-100 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400' }}">
                                {{ $qvQty > 0 ? 'Available' : 'Out of Stock' }}
                            </span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-center">
                            <span class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Total Quantity</span>
                            <span class="text-sm sm:text-base font-bold text-slate-800 dark:text-slate-100">{{ $qvQty }} {{ $quickViewProduct->unit?->unit_name }}</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-center">
                            <span class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Cost Price</span>
                            <span class="text-sm sm:text-base font-bold text-slate-700 dark:text-slate-200 whitespace-nowrap">{{ Money::formatBd($qvCost) }}</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-emerald-50 dark:bg-slate-800 border border-emerald-200 dark:border-slate-700 text-center">
                            <span class="block text-[10px] font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider mb-1">Selling Price</span>
                            <span class="text-sm sm:text-base font-bold text-emerald-700 dark:text-emerald-300 whitespace-nowrap">{{ Money::formatBd($qvSell) }}</span>
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden mt-3">
                        <div class="bg-slate-100 dark:bg-slate-800 px-5 py-2.5 border-b border-slate-200 dark:border-slate-700 font-bold text-xs text-slate-700 dark:text-slate-200 uppercase tracking-wider flex items-center justify-between">
                            <span>Product Specification</span>
                            <span class="text-emerald-700 dark:text-emerald-400 font-bold whitespace-nowrap">Total Value: {{ Money::formatBd($qvTotal) }}</span>
                        </div>
                        <div class="divide-y divide-slate-200 dark:divide-slate-700 text-xs">
                            <div class="grid grid-cols-3 px-5 py-2.5 bg-white dark:bg-slate-800/90 items-center">
                                <span class="font-semibold text-slate-500">Barcode(s):</span>
                                <div class="col-span-2 flex flex-wrap gap-1.5">
                                    @forelse($qvCodes as $code)
                                        <span
                                            class="barcode-badge cursor-pointer"
                                            data-barcode="{{ $code }}"
                                            title="{{ $code }} (Click to copy)"
                                            onclick="navigator.clipboard?.writeText('{{ addslashes($code) }}'); window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Barcode copied: {{ addslashes($code) }}' } }));"
                                        >
                                            <span class="barcode-text">{{ $code }}</span>
                                        </span>
                                    @empty
                                        <span class="text-slate-400 font-mono">-</span>
                                    @endforelse
                                </div>
                            </div>
                            <div class="grid grid-cols-3 px-5 py-2.5 bg-slate-50 dark:bg-slate-800/50">
                                <span class="font-semibold text-slate-500">Category:</span>
                                <span class="col-span-2 font-medium text-slate-800 dark:text-slate-200">{{ $quickViewProduct->category?->category_name ?? '-' }}</span>
                            </div>
                            <div class="grid grid-cols-3 px-5 py-2.5 bg-white dark:bg-slate-800/90">
                                <span class="font-semibold text-slate-500">Brand:</span>
                                <span class="col-span-2 font-medium text-slate-800 dark:text-slate-200">{{ $quickViewProduct->brand?->name ?? '-' }}</span>
                            </div>
                            <div class="grid grid-cols-3 px-5 py-2.5 bg-slate-50 dark:bg-slate-800/50">
                                <span class="font-semibold text-slate-500">Unit:</span>
                                <span class="col-span-2 font-medium text-slate-800 dark:text-slate-200">{{ $quickViewProduct->unit?->unit_name ?? '-' }}</span>
                            </div>
                            @if($quickViewProduct->door_side)
                                <div class="grid grid-cols-3 px-5 py-2.5 bg-white dark:bg-slate-800/90">
                                    <span class="font-semibold text-slate-500">Door Side:</span>
                                    <span class="col-span-2 font-medium text-slate-800 dark:text-slate-200">{{ $quickViewProduct->door_side }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="sticky bottom-0 z-20 px-4 py-2.5 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 flex justify-end shrink-0">
                    <button type="button" wire:click="closeModal" class="px-4 h-[38px] rounded-xl bg-[#dc2626] hover:bg-red-700 text-white text-xs sm:text-sm font-semibold border-0 cursor-pointer">Cancel</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Reference Parity: Custom Delete Confirmation Modal (product-delete.blade.php) --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 z-[70] bg-slate-900/65 backdrop-blur-xs flex items-center justify-center p-4" wire:click.self="closeModal">
            <div class="relative w-full max-w-[420px] bg-white dark:bg-slate-900 rounded-[20px] border border-slate-200 dark:border-slate-800 shadow-2xl px-6 pt-7 pb-6 text-center">
                <button type="button" wire:click="closeModal" class="absolute top-3.5 right-3.5 w-8 h-8 rounded-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-400 hover:text-slate-700 dark:hover:text-white flex items-center justify-center cursor-pointer" title="Close">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
                <div class="w-[68px] h-[68px] rounded-full bg-rose-50 dark:bg-rose-950/40 border-2 border-rose-100 dark:border-rose-900/60 text-red-600 dark:text-red-400 flex items-center justify-center mx-auto mb-4 shadow-sm">
                    <i class="fa-solid fa-trash-can text-[26px]"></i>
                </div>
                <h4 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Delete Product?</h4>
                <p class="text-[13.5px] text-slate-500 dark:text-slate-400 leading-relaxed mb-5 px-2">
                    Are you sure you want to delete <strong class="text-slate-700 dark:text-slate-200">{{ $deletingName }}</strong>? This action cannot be undone.
                </p>
                <div class="flex items-center justify-center gap-3">
                    <button type="button" wire:click="closeModal" class="flex-1 h-[42px] rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-200 border border-slate-200 dark:border-slate-700 text-[13.5px] font-semibold cursor-pointer">Cancel</button>
                    <button type="button" wire:click="confirmDeleteAction" class="flex-1 h-[42px] rounded-xl bg-gradient-to-br from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white text-[13.5px] font-semibold shadow-md inline-flex items-center justify-center gap-2 border-0 cursor-pointer">
                        <i class="fa-solid fa-trash-can text-xs"></i>
                        <span>Yes, Delete</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Product Image Quick View Modal --}}
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

    <style>
        .barcode-badge {
            position: relative;
            display: inline-flex;
            align-items: center;
            max-width: 105px;
            height: 22px;
            padding: 0 6px;
            border-radius: 6px;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 11px;
            font-weight: 600;
            background-color: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
            overflow: hidden;
            transition: all 0.15s ease;
        }
        body[light-mode="dark"] .barcode-badge,
        html.dark .barcode-badge {
            background-color: #064e3b;
            color: #a7f3d0;
            border-color: #047857;
        }
        .barcode-badge:hover {
            border-color: #10b981;
            background-color: #d1fae5;
            box-shadow: 0 1px 3px rgba(16, 185, 129, 0.2);
        }
        .barcode-badge .barcode-text {
            display: inline-block;
            white-space: nowrap;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            will-change: transform;
            pointer-events: none;
        }
        .barcode-badge.is-overflowing:hover .barcode-text {
            overflow: visible;
            text-overflow: clip;
            max-width: none;
            animation: barcodeScroll var(--scroll-dur, 2.5s) ease-in-out infinite alternate;
        }
        @keyframes barcodeScroll {
            0%, 15% { transform: translateX(0); }
            85%, 100% { transform: translateX(var(--scroll-dist, -40px)); }
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
        .action-btn-edit { color: #15803d; }
        .action-btn-edit:hover { background-color: #dcfce7; border-color: #16a34a !important; color: #15803d; }
        .quick-view-btn {
            background-color: #f0f9ff !important;
            border: 1.5px solid #cbd5e1 !important;
            color: #0284c7 !important;
            transition: all 0.15s ease-in-out !important;
        }
        .quick-view-btn svg { stroke: #0284c7 !important; color: #0284c7 !important; }
        .quick-view-btn:hover { background-color: #e0f2fe !important; border-color: #0284c7 !important; color: #0284c7 !important; }
        body[light-mode="dark"] .quick-view-btn,
        html.dark .quick-view-btn { background-color: #0c4a6e !important; border-color: #0369a1 !important; color: #38bdf8 !important; }
        body[light-mode="dark"] .quick-view-btn svg,
        html.dark .quick-view-btn svg { stroke: #38bdf8 !important; color: #38bdf8 !important; }
        .mobile-fin-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 6px; margin-top: 8px; }
    </style>

    <script>
        if (!window.__marssBarcodeMarqueeBound) {
            window.__marssBarcodeMarqueeBound = true;
            document.addEventListener('mouseenter', function(e) {
                const badge = e.target.closest && e.target.closest('.barcode-badge');
                if (!badge) return;
                const textEl = badge.querySelector('.barcode-text');
                if (!textEl || badge.clientWidth <= 12) return;
                const available = badge.clientWidth - 12;
                const overflow = textEl.scrollWidth - available;
                if (overflow > 1) {
                    badge.classList.add('is-overflowing');
                    badge.style.setProperty('--scroll-dist', `-${overflow + 4}px`);
                    badge.style.setProperty('--scroll-dur', `${Math.max(1.8, overflow / 30)}s`);
                } else {
                    badge.classList.remove('is-overflowing');
                }
            }, true);
        }
    </script>
</div>
