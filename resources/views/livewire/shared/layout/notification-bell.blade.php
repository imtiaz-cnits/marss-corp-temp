<div x-data="{ open: false }" @click.outside="open = false" class="relative">
    <button
        type="button"
        @click="open = !open"
        title="Notifications"
        class="relative w-[32px] h-[32px] rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 flex items-center justify-center transition-colors cursor-pointer"
    >
        <i class="fa-regular fa-bell text-sm"></i>
        @if($lowStockCount > 0)
            <span class="absolute -top-1.5 -right-1.5 min-w-[18px] h-[18px] px-1 rounded-full bg-red-600 text-white text-[10px] font-extrabold flex items-center justify-center shadow-xs">
                {{ $lowStockCount > 99 ? '99+' : $lowStockCount }}
            </span>
        @endif
    </button>

    <div
        x-show="open"
        x-transition
        style="display: none;"
        class="absolute right-0 mt-2 w-80 sm:w-96 rounded-2xl bg-white dark:bg-slate-900 border-[1.5px] border-slate-300 dark:border-slate-700 shadow-2xl overflow-hidden z-50"
    >
        <div class="px-4 py-3 bg-emerald-700 text-white flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider">
                {{ $isBattery ? 'Battery Alerts' : 'Retail Alerts' }}
            </span>
            <span class="px-2 py-0.5 rounded-full bg-white/20 text-[11px] font-bold">
                {{ $lowStockCount }} Low Stock
            </span>
        </div>

        @if($canSeeActivity)
            <div class="grid grid-cols-2 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/70 text-xs font-bold">
                <button
                    type="button"
                    wire:click="setTab('stock')"
                    class="py-2 transition-colors cursor-pointer {{ $tab === 'stock' ? 'text-emerald-700 dark:text-emerald-400 border-b-2 border-emerald-700 bg-white dark:bg-slate-900' : 'text-slate-500 hover:text-slate-700' }}"
                >
                    <i class="fa-solid fa-triangle-exclamation mr-1"></i> Low Stock
                </button>
                <button
                    type="button"
                    wire:click="setTab('activity')"
                    class="py-2 transition-colors cursor-pointer {{ $tab === 'activity' ? 'text-emerald-700 dark:text-emerald-400 border-b-2 border-emerald-700 bg-white dark:bg-slate-900' : 'text-slate-500 hover:text-slate-700' }}"
                >
                    <i class="fa-solid fa-clock-rotate-left mr-1"></i> Activity
                </button>
            </div>
        @endif

        <div class="max-h-72 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800">
            @if($tab === 'stock' || ! $canSeeActivity)
                @forelse($lowStockItems as $item)
                    @php $qty = (int) round((float) ($item->quantity ?? 0)); @endphp
                    <div class="px-4 py-2.5 flex items-center justify-between gap-2 hover:bg-slate-50 dark:hover:bg-slate-800/60">
                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-200 truncate">{{ $item->product_name }}</span>
                        <span class="shrink-0 px-2 py-0.5 rounded-full text-[11px] font-bold {{ $qty <= 0 ? 'bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-red-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300' }}">
                            Qty: {{ $qty }}
                        </span>
                    </div>
                @empty
                    <div class="p-6 text-center text-xs text-slate-500">All products are well-stocked.</div>
                @endforelse
            @else
                @forelse($recentLogs as $log)
                    <div class="px-4 py-2.5 hover:bg-slate-50 dark:hover:bg-slate-800/60">
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="font-bold text-emerald-700 dark:text-emerald-400">{{ $log->user_name }}</span>
                            <span class="text-slate-400">{{ $log->created_at?->diffForHumans() }}</span>
                        </div>
                        <p class="text-xs text-slate-600 dark:text-slate-300 mt-0.5 line-clamp-2">{{ $log->description }}</p>
                    </div>
                @empty
                    <div class="p-6 text-center text-xs text-slate-500">No recent activity logged yet.</div>
                @endforelse
            @endif
        </div>

        <div class="px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-200 dark:border-slate-800 text-center">
            @if($tab === 'activity' && $canSeeActivity)
                <a href="{{ route('settings.activity-log') }}" wire:navigate @click="open = false" class="text-xs font-bold text-emerald-700 dark:text-emerald-400 hover:underline">
                    View Full Activity Log &rarr;
                </a>
            @else
                <a href="{{ $isBattery ? route('battery.reports.stock-out') : route('retail.reports.stock-out') }}" wire:navigate @click="open = false" class="text-xs font-bold text-emerald-700 dark:text-emerald-400 hover:underline">
                    Open Low Stock Report &rarr;
                </a>
            @endif
        </div>
    </div>
</div>
