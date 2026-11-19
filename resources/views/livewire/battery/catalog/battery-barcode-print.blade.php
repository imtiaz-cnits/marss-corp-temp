<div class="space-y-4">
    <div class="flex items-center justify-between no-print">
        <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Battery Barcode Generator</h1>
        @if(!empty($labels))
            <button type="button" onclick="window.print()" class="h-10 px-5 rounded-xl bg-emerald-700 text-white text-sm font-bold cursor-pointer"><i class="fa-solid fa-print mr-1.5"></i> Print ({{ count($labels) }})</button>
        @endif
    </div>
    <div class="flex gap-2 no-print">
        <button type="button" wire:click="$set('tab', 'product')" class="h-9 px-4 rounded-xl text-xs font-bold cursor-pointer {{ $tab === 'product' ? 'bg-emerald-700 text-white' : 'bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700' }}">Product Barcode</button>
        <button type="button" wire:click="$set('tab', 'sequential')" class="h-9 px-4 rounded-xl text-xs font-bold cursor-pointer {{ $tab === 'sequential' ? 'bg-emerald-700 text-white' : 'bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700' }}">Sequential Range</button>
    </div>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 p-4 no-print">
        @if($tab === 'product')
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                <div class="sm:col-span-6"><label class="block text-xs font-bold mb-1">Battery Product</label><select wire:model="selectedProductId" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"><option value="">Select...</option>@foreach($products as $p)<option value="{{ $p->id }}">{{ $p->product_name }}</option>@endforeach</select></div>
                <div class="sm:col-span-2"><label class="block text-xs font-bold mb-1">Copies</label><input type="number" min="1" max="120" wire:model="copies" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"></div>
                <div class="sm:col-span-2 flex items-center h-10"><label class="flex items-center gap-2 text-xs font-bold"><input type="checkbox" wire:model="showPrice"> Show MRP</label></div>
                <div class="sm:col-span-2"><button type="button" wire:click="generateProductLabels" class="w-full h-10 rounded-xl bg-emerald-700 text-white text-xs font-bold cursor-pointer">Generate</button></div>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                <div class="sm:col-span-3"><label class="block text-xs font-bold mb-1">Prefix</label><input type="text" wire:model="seqPrefix" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"></div>
                <div class="sm:col-span-3"><label class="block text-xs font-bold mb-1">Start</label><input type="number" wire:model="seqStart" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"></div>
                <div class="sm:col-span-3"><label class="block text-xs font-bold mb-1">Count</label><input type="number" min="1" max="120" wire:model="seqCount" class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm"></div>
                <div class="sm:col-span-3"><button type="button" wire:click="generateSequentialLabels" class="w-full h-10 rounded-xl bg-emerald-700 text-white text-xs font-bold cursor-pointer">Generate</button></div>
            </div>
        @endif
    </div>
    @if(!empty($labels))
        <div wire:key="bat-bar-{{ md5(json_encode($labels)) }}" x-data="{ init() { this.$nextTick(() => this.$el.querySelectorAll('svg[data-code]').forEach(s => window.JsBarcode?.(s, s.dataset.code, { format: 'CODE128', width: 1.4, height: 38, fontSize: 11, margin: 4 }))); } }" class="bg-white text-black rounded-2xl border border-slate-300 p-4 grid grid-cols-2 sm:grid-cols-3 gap-3">
            @foreach($labels as $lbl)
                <div class="border border-dashed border-slate-400 rounded-lg p-2.5 flex flex-col items-center text-center">
                    <p class="text-[11px] font-extrabold line-clamp-1">{{ $lbl['name'] }}</p>
                    <svg data-code="{{ $lbl['code'] }}" class="max-w-full"></svg>
                    @if($lbl['price'])<p class="text-xs font-extrabold">MRP: ৳ {{ number_format((float) $lbl['price'], 2) }}</p>@endif
                </div>
            @endforeach
        </div>
    @endif
</div>
