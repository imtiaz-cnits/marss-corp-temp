@php use App\Support\Money; @endphp

<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Battery Product List</h1>
            <p class="text-xs sm:text-sm text-slate-500">Isolated Dry &amp; Gel Battery catalog with multi-barcode support.</p>
        </div>
        <button type="button" wire:click="openCreate" class="h-10 px-4 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-bold flex items-center gap-2 cursor-pointer"><i class="fa-solid fa-plus"></i> Add Battery Product</button>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 overflow-hidden">
        <div class="p-3.5 border-b border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-2">
            <div class="flex flex-wrap items-center gap-2">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search battery name or code..." class="h-9 px-3 w-56 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                <select wire:model.live="brandFilter" class="h-9 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold"><option value="">All Brands</option>@foreach($brands as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach</select>
            </div>
            <x-ui.export-buttons table-id="batteryProductsTable" filename="battery-products" title="MARSS Battery Products" />
        </div>
        <div class="overflow-x-auto">
            <table id="batteryProductsTable" data-export-skip-last="true" class="w-full text-xs sm:text-sm text-left">
                <thead class="bg-[#15803d] text-white text-xs uppercase whitespace-nowrap">
                    <tr><th class="px-3 py-3">#</th><th class="px-3 py-3">Product</th><th class="px-3 py-3">Codes</th><th class="px-3 py-3">Brand / Cat</th><th class="px-3 py-3 text-right">Cost</th><th class="px-3 py-3 text-right">Sell</th><th class="px-3 py-3 text-center">Stock</th><th class="px-3 py-3 text-right">Actions</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($products as $idx => $p)
                        @php $q = (int) round((float) ($p->quantity ?? 0)); @endphp
                        <tr>
                            <td class="px-3 py-2.5 text-slate-500">{{ $products->firstItem() + $idx }}</td>
                            <td class="px-3 py-2.5 font-bold">{{ $p->product_name }}</td>
                            <td class="px-3 py-2.5 font-mono text-xs text-slate-500">{{ implode(', ', $p->decodedCodes()) ?: '—' }}</td>
                            <td class="px-3 py-2.5 text-xs"><div>{{ $p->brand?->name ?? '—' }}</div><div class="text-slate-400">{{ $p->category?->name ?? '—' }}</div></td>
                            <td class="px-3 py-2.5 text-right tabular-nums">{{ Money::formatBd($p->cost_price) }}</td>
                            <td class="px-3 py-2.5 text-right font-semibold tabular-nums">{{ Money::formatBd($p->sell_price) }}</td>
                            <td class="px-3 py-2.5 text-center"><span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $q <= 0 ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">{{ $q }} {{ $p->unit?->unit_name }}</span></td>
                            <td class="px-3 py-2.5 text-right whitespace-nowrap">
                                <button type="button" wire:click="openEdit({{ $p->id }})" class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-bold cursor-pointer">Edit</button>
                                <button type="button" wire:click="delete({{ $p->id }})" wire:confirm="Delete '{{ $p->product_name }}'?" class="ml-1 px-2.5 py-1 rounded-lg bg-red-50 text-red-600 text-xs font-bold cursor-pointer">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr data-empty-row="true"><td colspan="8" class="px-4 py-8 text-center text-slate-500">No Battery products found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-t border-slate-200 dark:border-slate-800">{{ $products->links() }}</div>
    </div>

    <x-ui.modal :show="$showModal" max-width="lg" :title="$editingId ? 'Edit Battery Product' : 'Add Battery Product'" close-method="closeModal">
        <form wire:submit="save" class="space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="sm:col-span-2"><label class="block text-xs font-bold mb-1">Product Name *</label><input type="text" wire:model="product_name" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"></div>
                <div class="sm:col-span-2"><label class="block text-xs font-bold mb-1">Product Codes (comma separated for multiple barcodes)</label><input type="text" wire:model="product_code" placeholder="BAT-1001, BAT-1002" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"></div>
                <div><label class="block text-xs font-bold mb-1">Brand</label><select wire:model="brand_id" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"><option value="">Select</option>@foreach($brands as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach</select></div>
                <div><label class="block text-xs font-bold mb-1">Category</label><select wire:model.live="category_id" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"><option value="">Select</option>@foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div>
                <div><label class="block text-xs font-bold mb-1">Sub-Category</label><select wire:model="sub_category_id" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"><option value="">Select</option>@foreach($subCategories as $sc)<option value="{{ $sc->id }}">{{ $sc->name }}</option>@endforeach</select></div>
                <div><label class="block text-xs font-bold mb-1">Unit</label><select wire:model="unit_id" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"><option value="">Select</option>@foreach($units as $u)<option value="{{ $u->id }}">{{ $u->unit_name }}</option>@endforeach</select></div>
                <div><label class="block text-xs font-bold mb-1">Stock Qty *</label><input type="number" wire:model="quantity" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"></div>
                <div><label class="block text-xs font-bold mb-1">Cost Price *</label><input type="number" step="0.01" wire:model="cost_price" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"></div>
                <div><label class="block text-xs font-bold mb-1">Sell Price *</label><input type="number" step="0.01" wire:model="sell_price" class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"></div>
                <div><label class="block text-xs font-bold mb-1">Image</label><input type="file" wire:model="image" accept="image/*" class="w-full text-xs"></div>
            </div>
            <div class="flex justify-end gap-2 pt-2"><button type="button" wire:click="closeModal" class="h-10 px-4 rounded-xl border text-sm font-bold">Cancel</button><button type="submit" class="h-10 px-5 rounded-xl bg-emerald-700 text-white text-sm font-bold cursor-pointer">Save</button></div>
        </form>
    </x-ui.modal>
</div>
