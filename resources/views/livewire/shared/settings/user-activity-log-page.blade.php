<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">User Activity Audit Log</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Administrator-only trail of logins, sales, purchases, payments, returns, and role changes.</p>
        </div>
        <button
            type="button"
            wire:click="clearAllLogs"
            wire:confirm="Permanently clear all recorded activity logs?"
            class="h-10 px-4 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-bold shadow-xs flex items-center gap-2 cursor-pointer"
        >
            <i class="fa-solid fa-trash-can"></i> Clear Logs
        </button>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 shadow-xs overflow-hidden">
        <div class="p-3.5 border-b border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-2">
            <div class="flex flex-wrap items-center gap-2">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search user, module, detail..." class="h-[38px] px-3 w-56 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs sm:text-sm outline-none focus:border-emerald-700">
                <select wire:model.live="actionFilter" class="h-[38px] px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold">
                    <option value="">All Actions</option>
                    @foreach($actions as $act)
                        <option value="{{ $act }}">{{ $act }}</option>
                    @endforeach
                </select>
                <div class="w-36"><x-ui.date-picker wire:model.live="dateFrom" placeholder="From Date" /></div>
                <div class="w-36"><x-ui.date-picker wire:model.live="dateTo" placeholder="To Date" /></div>
                <button type="button" wire:click="clearFilters" class="h-[38px] px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 text-xs font-bold hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer">Reset</button>
            </div>
            <x-ui.export-buttons table-id="activityLogTable" filename="marss-activity-log" title="MARSS User Activity Log" />
        </div>

        <div class="overflow-x-auto">
            <table id="activityLogTable" class="w-full text-xs sm:text-sm text-left">
                <thead class="bg-[#15803d] text-white text-xs uppercase tracking-wider whitespace-nowrap">
                    <tr>
                        <th class="px-3 py-3">Timestamp</th>
                        <th class="px-3 py-3">User</th>
                        <th class="px-3 py-3">Action</th>
                        <th class="px-3 py-3">Module</th>
                        <th class="px-3 py-3">Description</th>
                        <th class="px-3 py-3">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="px-3 py-2.5 whitespace-nowrap text-xs text-slate-500">{{ $log->created_at?->format('d-m-Y h:i A') }}</td>
                            <td class="px-3 py-2.5 whitespace-nowrap">
                                <div class="font-bold text-slate-800 dark:text-white">{{ $log->user_name }}</div>
                                <div class="text-[11px] text-slate-400 uppercase">{{ $log->user_role }}</div>
                            </td>
                            <td class="px-3 py-2.5 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">{{ $log->action }}</span>
                            </td>
                            <td class="px-3 py-2.5 whitespace-nowrap font-semibold text-slate-700 dark:text-slate-200">{{ $log->module }}</td>
                            <td class="px-3 py-2.5 text-slate-600 dark:text-slate-300">{{ $log->description }}</td>
                            <td class="px-3 py-2.5 whitespace-nowrap text-xs text-slate-400">{{ $log->ip_address ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr data-empty-row="true"><td colspan="6" class="px-4 py-8 text-center text-slate-500">No activity logs recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-t border-slate-200 dark:border-slate-800">{{ $logs->links() }}</div>
    </div>
</div>
