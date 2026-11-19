@php use App\Support\Money; @endphp

<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Retail Product Catalog</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Manage Retail hardware &amp; door products, codes, pricing, handedness, and inventory.</p>
        </div>
        <button type="button" wire:click="openCreate" class="h-10 px-4 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-bold shadow-xs flex items-center gap-2 cursor-pointer">
            <i class="fa-solid fa-plus"></i> Add Product
        </button>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 shadow-xs overflow-hidden">
        <div class="p-3.5 border-b border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-2">
            <div class="flex flex-wrap items-center gap-2">
                <select wire:model.live="perPage" class="h-[38px] px-2.5 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold">
                    <option value="15">15</option><option value="50">50</option><option value="100">100</option><option value="500">500</option>
                </select>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search name, code, door side..." class="h-[38px] px-3 w-52 sm:w-64 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs sm:text-sm outline-none focus:border-emerald-700">
                <select wire:model.live="brandFilter" class="h-[38px] px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold">
                    <option value="">All Brands</option>
                    @foreach($brands as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
                </select>
                <select wire:model.live="categoryFilter" class="h-[38px] px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold">
                    <option value="">All Categories</option>
                    @foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->category_name }}</option>@endforeach
                </select>
            </div>
            <x-ui.export-buttons table-id="retailProductsTable" filename="retail-products" title="MARSS Retail Products" />
        </div>

        <div class="hidden md:block overflow-x-auto">
            <table id="retailProductsTable" data-export-skip-last="true" class="w-full text-xs sm:text-sm text-left">
                <thead class="bg-[#15803d] text-white text-xs uppercase tracking-wider whitespace-nowrap">
                    <tr>
                        <th class="px-3 py-3">#</th>
                        <th class="px-3 py-3">Product</th>
                        <th class="px-3 py-3">Code</th>
                        <th class="px-3 py-3">Brand / Cat</th>
                        <th class="px-3 py-3 text-right">Cost</th>
                        <th class="px-3 py-3 text-right">Sell</th>
                        <th class="px-3 py-3 text-center">Stock</th>
                        <th class="px-3 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($products as $idx => $p)
                        @php $q = (int) round((float) ($p->quantity ?? 0)); @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="px-3 py-2.5 text-slate-500">{{ $products->firstItem() + $idx }}</td>
                            <td class="px-3 py-2.5">
                                <div class="font-bold text-slate-800 dark:text-white">{{ $p->product_name }}</div>
                                @if($p->door_side)
                                    <span class="inline-block mt-0.5 px-1.5 py-0.5 rounded bg-sky-100 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300 text-[10px] font-bold">{{ $p->door_side }}</span>
                                @endif
                            </td>
                            <td class="px-3 py-2.5 font-mono text-xs text-slate-500">{{ is_array($p->product_code) ? implode(', ', $p->product_code) : ($p->product_code ?: '—') }}</td>
                            <td class="px-3 py-2.5 text-xs">
                                <div class="font-semibold">{{ $p->brand?->name ?? '—' }}</div>
                                <div class="text-slate-400">{{ $p->category?->category_name ?? '—' }}</div>
                            </td>
                            <td class="px-3 py-2.5 text-right tabular-nums">{{ Money::formatBd($p->cost_price) }}</td>
                            <td class="px-3 py-2.5 text-right font-semibold tabular-nums">{{ Money::formatBd($p->sell_price) }}</td>
                            <td class="px-3 py-2.5 text-center">
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $q <= 0 ? 'bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-red-300' : ($q < 10 ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300') }}">
                                    {{ $q }} {{ $p->unit?->unit_name }}
                                </span>
                            </td>
                            <td class="px-3 py-2.5 text-right whitespace-nowrap">
                                <button type="button" wire:click="openEdit({{ $p->id }})" class="px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 text-xs font-bold cursor-pointer">Edit</button>
                                <button type="button" wire:click="delete({{ $p->id }})" wire:confirm="Delete '{{ $p->product_name }}'?" class="ml-1 px-2.5 py-1 rounded-lg bg-red-50 dark:bg-red-950/40 text-red-600 text-xs font-bold cursor-pointer">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr data-empty-row="true"><td colspan="8" class="px-4 py-8 text-center text-slate-500">No Retail products found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="md:hidden divide-y divide-slate-200 dark:divide-slate-800">
            @foreach($products as $p)
                <div class="p-3.5 space-y-1.5">
                    <div class="flex justify-between gap-2">
                        <p class="font-bold text-sm text-slate-900 dark:text-white">{{ $p->product_name }}</p>
                        <span class="px-2 py-0.5 h-fit rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">Qty: {{ (int) round((float) ($p->quantity ?? 0)) }}</span>
                    </div>
                    <div class="grid grid-cols-2 text-xs text-slate-500">
                        <span>Cost: {{ Money::formatBd($p->cost_price) }}</span>
                        <span>Sell: {{ Money::formatBd($p->sell_price) }}</span>
                    </div>
                    <div class="flex gap-1.5 pt-1">
                        <button type="button" wire:click="openEdit({{ $p->id }})" class="px-3 py-1 rounded-lg bg-emerald-700 text-white text-xs font-bold">Edit</button>
                        <button type="button" wire:click="delete({{ $p->id }})" wire:confirm="Delete '{{ $p->product_name }}'?" class="px-3 py-1 rounded-lg bg-red-600 text-white text-xs font-bold">Delete</button>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="p-3 border-t border-slate-200 dark:border-slate-800">{{ $products->links() }}</div>
    </div>

    <x-ui.modal :show="$showModal" max-width="lg" :title="$editingId ? 'Edit Retail Product' : 'Add Retail Product'" close-method="closeModal">
        <form wire:submit="save" class="space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold mb-1">Product Name *</label>
                    <input type="text" wire:model="product_name" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                    @error('product_name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold mb-1">Product Code / Barcode</label>
                    <input type="text" wire:model="product_code" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                    @error('product_code') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold mb-1">Door Handedness</label>
                    <select wire:model="door_side" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                        <option value="">None / Standard</option>
                        <option value="Left Handed">Left Handed</option>
                        <option value="Right Handed">Right Handed</option>
                        <option value="Both Handed">Both Handed</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold mb-1">Brand</label>
                    <select wire:model="brand_id" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                        <option value="">Select Brand</option>
                        @foreach($brands as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold mb-1">Category</label>
                    <select wire:model.live="category_id" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                        <option value="">Select Category</option>
                        @foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->category_name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold mb-1">Sub-Category</label>
                    <select wire:model="sub_category_id" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                        <option value="">Select Sub-Category</option>
                        @foreach($subCategories as $sc)<option value="{{ $sc->id }}">{{ $sc->sub_category_name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold mb-1">Unit</label>
                    <select wire:model="unit_id" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                        @foreach($units as $u)<option value="{{ $u->id }}">{{ $u->unit_name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold mb-1">Stock Quantity *</label>
                    <input type="number" wire:model="quantity" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold mb-1">Cost Price (৳) *</label>
                    <input type="number" step="0.01" min="0" wire:model="cost_price" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold mb-1">Sell Price (৳) *</label>
                    <input type="number" step="0.01" min="0" wire:model="sell_price" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold mb-1">Product Image</label>
                    <input type="file" wire:model="image" accept="image/*" class="w-full text-xs">
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" wire:click="closeModal" class="h-10 px-4 rounded-xl border border-slate-300 text-sm font-bold">Cancel</button>
                <button type="submit" class="h-10 px-5 rounded-xl bg-emerald-700 text-white text-sm font-bold cursor-pointer">Save Product</button>
            </div>
        </form>
    </x-ui.modal>
</div>
