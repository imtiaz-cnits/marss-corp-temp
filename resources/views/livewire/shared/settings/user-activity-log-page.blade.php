<div class="space-y-4">
    {{-- Header Section (Image 3 1:1 Parity) --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                <i class="fa-solid fa-clock-rotate-left text-xl"></i>
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-800 dark:text-white tracking-tight m-0">User Activity Logs &amp; History</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 m-0">Real-time audit trail of user logins, logouts, sales, and system changes</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button
                type="button"
                wire:click="$refresh"
                title="Refresh logs"
                class="h-9 px-3.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#0f172a] hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-bold inline-flex items-center gap-1.5 shadow-2xs cursor-pointer transition-colors"
            >
                <i class="fa-solid fa-arrows-rotate text-[11px]" wire:loading.class="fa-spin"></i>
                <span>Refresh</span>
            </button>

            <button
                type="button"
                wire:click="clearAllLogs"
                wire:confirm="Are you sure you want to permanently clear all recorded activity logs?"
                class="h-9 px-3.5 rounded-xl border border-rose-200 dark:border-rose-900/50 bg-white dark:bg-[#0f172a] hover:bg-rose-50 dark:hover:bg-rose-950/30 text-rose-600 dark:text-rose-400 text-xs font-bold inline-flex items-center gap-1.5 shadow-2xs cursor-pointer transition-colors"
            >
                <i class="fa-solid fa-trash-can text-[11px]"></i>
                <span>Clear All Logs</span>
            </button>
        </div>
    </div>

    {{-- 4 Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        {{-- Card 1: Total Recorded Logs --}}
        <div class="bg-white dark:bg-[#0f172a] border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-2xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 m-0 mb-1">TOTAL RECORDED LOGS</p>
                <h3 class="text-2xl font-black text-slate-800 dark:text-white m-0 tabular-nums">{{ number_format($totalLogs) }}</h3>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/50 text-rose-500 dark:text-rose-400 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-list-check text-base"></i>
            </div>
        </div>

        {{-- Card 2: Page Logins & Sessions --}}
        <div class="bg-white dark:bg-[#0f172a] border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-2xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 m-0 mb-1">PAGE LOGINS &amp; SESSIONS</p>
                <h3 class="text-2xl font-black text-slate-800 dark:text-white m-0 tabular-nums">{{ number_format($loginSessionsCount) }}</h3>
            </div>
            <div class="w-10 h-10 rounded-xl bg-sky-50 dark:bg-sky-950/50 text-sky-500 dark:text-sky-400 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-user-clock text-base"></i>
            </div>
        </div>

        {{-- Card 3: Data Modifications --}}
        <div class="bg-white dark:bg-[#0f172a] border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-2xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 m-0 mb-1">DATA MODIFICATIONS</p>
                <h3 class="text-2xl font-black text-slate-800 dark:text-white m-0 tabular-nums">{{ number_format($modificationsCount) }}</h3>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-500 dark:text-amber-400 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-layer-group text-base"></i>
            </div>
        </div>

        {{-- Card 4: Registered Users --}}
        <div class="bg-white dark:bg-[#0f172a] border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-2xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 m-0 mb-1">REGISTERED USERS</p>
                <h3 class="text-2xl font-black text-slate-800 dark:text-white m-0 tabular-nums">{{ number_format($registeredUsersCount) }}</h3>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-500 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-shield-halved text-base"></i>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white dark:bg-[#0f172a] border border-slate-200 dark:border-slate-800 rounded-2xl p-3 shadow-2xs">
        <div class="flex flex-wrap items-center gap-2.5">
            {{-- Search Input --}}
            <div class="relative flex-1 min-w-[220px]">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search activities, descriptions, or IP..."
                    class="w-full h-10 pl-9 pr-3 text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 text-slate-800 dark:text-slate-100 placeholder:text-slate-400 focus:outline-none focus:border-emerald-600 focus:bg-white dark:focus:bg-slate-800 transition-colors"
                >
            </div>

            {{-- System Users Dropdown --}}
            <div class="w-full sm:w-auto min-w-[160px]">
                <select
                    wire:model.live="userFilter"
                    class="w-full h-10 px-3 text-xs sm:text-sm font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:outline-none focus:border-emerald-600 cursor-pointer"
                >
                    <option value="">All System Users</option>
                    @foreach($usersList as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Actions Dropdown --}}
            <div class="w-full sm:w-auto min-w-[140px]">
                <select
                    wire:model.live="actionFilter"
                    class="w-full h-10 px-3 text-xs sm:text-sm font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:outline-none focus:border-emerald-600 cursor-pointer"
                >
                    <option value="">All Actions</option>
                    @foreach($actionsList as $act)
                        <option value="{{ $act }}">{{ $act }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Modules Dropdown --}}
            <div class="w-full sm:w-auto min-w-[150px]">
                <select
                    wire:model.live="moduleFilter"
                    class="w-full h-10 px-3 text-xs sm:text-sm font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:outline-none focus:border-emerald-600 cursor-pointer"
                >
                    <option value="">All Modules</option>
                    @foreach($modulesList as $mod)
                        <option value="{{ $mod }}">{{ $mod }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Show Entries Dropdown --}}
            <div class="w-full sm:w-auto min-w-[110px]">
                <select
                    wire:model.live="perPage"
                    class="w-full h-10 px-3 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:outline-none focus:border-emerald-600 cursor-pointer"
                >
                    <option value="15">SHOW: 15</option>
                    <option value="25">SHOW: 25</option>
                    <option value="50">SHOW: 50</option>
                    <option value="100">SHOW: 100</option>
                </select>
            </div>

            {{-- Filter & Reset Buttons --}}
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    wire:click="$refresh"
                    class="h-10 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold inline-flex items-center gap-1.5 shadow-2xs transition-colors cursor-pointer"
                >
                    <i class="fa-solid fa-filter text-xs"></i>
                    <span>Filter</span>
                </button>

                <button
                    type="button"
                    wire:click="clearFilters"
                    title="Reset Filters"
                    class="h-10 w-10 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center transition-colors cursor-pointer"
                >
                    <i class="fa-solid fa-rotate-right text-xs"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- Activity Logs Table Card --}}
    <div class="bg-white dark:bg-[#0f172a] rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto hidden lg:block">
            <table id="activityLogTable" class="w-full text-left border-collapse">
                <thead class="bg-[#15803d] text-white text-xs font-bold uppercase tracking-wider whitespace-nowrap">
                    <tr>
                        <th class="px-3.5 py-3 text-center w-12">#</th>
                        <th class="px-4 py-3 min-w-[220px]">USER</th>
                        <th class="px-3.5 py-3 whitespace-nowrap">ROLE</th>
                        <th class="px-3.5 py-3 whitespace-nowrap">ACTION</th>
                        <th class="px-3.5 py-3 whitespace-nowrap">MODULE</th>
                        <th class="px-4 py-3 min-w-[280px]">ACTIVITY DESCRIPTION</th>
                        <th class="px-4 py-3 whitespace-nowrap text-right">DATE &amp; TIME</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs sm:text-sm">
                    @forelse($logs as $log)
                        @php
                            $roleLower = strtolower($log->user_role ?? '');
                            $isAdmin = in_array($roleLower, ['admin', 'super_admin', 'super admin', 'developer']);

                            $actionLower = strtolower($log->action ?? '');
                            $actionStyle = match(true) {
                                str_contains($actionLower, 'login') => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-800',
                                str_contains($actionLower, 'logout') => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
                                str_contains($actionLower, 'create') || str_contains($actionLower, 'add') => 'bg-sky-50 text-sky-700 border-sky-200 dark:bg-sky-950/50 dark:text-sky-300 dark:border-sky-800',
                                str_contains($actionLower, 'update') || str_contains($actionLower, 'edit') => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/50 dark:text-amber-300 dark:border-amber-800',
                                str_contains($actionLower, 'delete') || str_contains($actionLower, 'remove') => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/50 dark:text-rose-300 dark:border-rose-800',
                                str_contains($actionLower, 'sale') || str_contains($actionLower, 'pos') => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-800',
                                str_contains($actionLower, 'purchase') => 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-950/50 dark:text-indigo-300 dark:border-indigo-800',
                                str_contains($actionLower, 'payment') => 'bg-teal-50 text-teal-700 border-teal-200 dark:bg-teal-950/50 dark:text-teal-300 dark:border-teal-800',
                                default => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                            {{-- SL # --}}
                            <td class="px-3.5 py-3 text-center text-xs font-semibold text-slate-400 dark:text-slate-500 tabular-nums">
                                {{ ($logs->currentPage() - 1) * $logs->perPage() + $loop->iteration }}
                            </td>

                            {{-- USER --}}
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-emerald-500 to-teal-400 text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-2xs">
                                        {{ strtoupper(substr($log->user_name ?: 'U', 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs sm:text-sm font-bold text-slate-800 dark:text-white truncate m-0 leading-tight">
                                            {{ $log->user_name ?: 'System' }}
                                        </p>
                                        <p class="text-[11px] text-slate-400 dark:text-slate-500 truncate m-0">
                                            {{ $log->user_email ?: 'system@internal' }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            {{-- ROLE --}}
                            <td class="px-3.5 py-3 whitespace-nowrap">
                                @if($isAdmin)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-600 border border-rose-200 dark:bg-rose-950/40 dark:text-rose-400 dark:border-rose-900/40">
                                        <span>👑</span>
                                        <span>{{ ucwords(str_replace('_', ' ', $log->user_role ?: 'Admin')) }}</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700">
                                        {{ ucwords(str_replace('_', ' ', $log->user_role ?: 'User')) }}
                                    </span>
                                @endif
                            </td>

                            {{-- ACTION --}}
                            <td class="px-3.5 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $actionStyle }}">
                                    {{ $log->action }}
                                </span>
                            </td>

                            {{-- MODULE --}}
                            <td class="px-3.5 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700">
                                    {{ $log->module ?: 'General' }}
                                </span>
                            </td>

                            {{-- ACTIVITY DESCRIPTION --}}
                            <td class="px-4 py-3">
                                <div class="text-xs sm:text-sm text-slate-700 dark:text-slate-300 font-medium">
                                    {{ $log->description }}
                                    @if($log->ip_address)
                                        <span class="ml-1.5 text-[10px] text-slate-400 dark:text-slate-500 font-normal tabular-nums">({{ $log->ip_address }})</span>
                                    @endif
                                </div>
                            </td>

                            {{-- DATE & TIME --}}
                            <td class="px-4 py-3 whitespace-nowrap text-right">
                                <div class="text-xs font-bold text-slate-800 dark:text-slate-200 tabular-nums">
                                    {{ $log->created_at?->format('d M Y, h:i A') }}
                                </div>
                                <div class="text-[10px] text-slate-400 dark:text-slate-500">
                                    {{ $log->created_at?->diffForHumans() }}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr data-empty-row="true">
                            <td colspan="7" class="px-4 py-12 text-center text-slate-400 dark:text-slate-500">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <i class="fa-solid fa-clock-rotate-left text-3xl text-slate-300 dark:text-slate-600"></i>
                                    <p class="text-sm font-semibold m-0">No user activity logs found.</p>
                                    <p class="text-xs text-slate-400 m-0">Try clearing your search query or filters.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile & Tablet Cards View (1 col on mobile, 2 cols on tablet) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:hidden gap-3 p-3">
            @forelse($logs as $log)
                @php
                    $roleLower = strtolower($log->user_role ?? '');
                    $isAdmin = in_array($roleLower, ['admin', 'super_admin', 'super admin', 'developer']);

                    $actionLower = strtolower($log->action ?? '');
                    $actionStyle = match(true) {
                        str_contains($actionLower, 'login') => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-800',
                        str_contains($actionLower, 'logout') => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
                        str_contains($actionLower, 'create') || str_contains($actionLower, 'add') => 'bg-sky-50 text-sky-700 border-sky-200 dark:bg-sky-950/50 dark:text-sky-300 dark:border-sky-800',
                        str_contains($actionLower, 'update') || str_contains($actionLower, 'edit') => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/50 dark:text-amber-300 dark:border-amber-800',
                        str_contains($actionLower, 'delete') || str_contains($actionLower, 'remove') => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/50 dark:text-rose-300 dark:border-rose-800',
                        str_contains($actionLower, 'sale') || str_contains($actionLower, 'pos') => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-800',
                        str_contains($actionLower, 'purchase') => 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-950/50 dark:text-indigo-300 dark:border-indigo-800',
                        str_contains($actionLower, 'payment') => 'bg-teal-50 text-teal-700 border-teal-200 dark:bg-teal-950/50 dark:text-teal-300 dark:border-teal-800',
                        default => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
                    };
                @endphp
                <div class="unified-ui-border rounded-2xl p-3 sm:p-3.5 bg-white dark:bg-slate-800/90 shadow-2xs transition-all flex flex-col justify-between border border-slate-200 dark:border-slate-800">
                    <div>
                        <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-100 dark:border-slate-800 gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-emerald-500 to-teal-400 text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-2xs">
                                    {{ strtoupper(substr($log->user_name ?: 'U', 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-slate-800 dark:text-white truncate m-0 leading-tight">
                                        {{ $log->user_name ?: 'System' }}
                                    </p>
                                    <p class="text-[10px] text-slate-400 dark:text-slate-500 truncate m-0">
                                        {{ $log->user_email ?: 'system@internal' }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-1 shrink-0">
                                @if($isAdmin)
                                    <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-600 border border-rose-200 dark:bg-rose-950/40 dark:text-rose-400">
                                        <span>👑</span>
                                        <span>Admin</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:text-slate-300">
                                        {{ ucwords(str_replace('_', ' ', $log->user_role ?: 'User')) }}
                                    </span>
                                @endif
                                <span class="text-[10px] font-bold text-slate-400">#{{ ($logs->currentPage() - 1) * $logs->perPage() + $loop->iteration }}</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5 mb-2 flex-wrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $actionStyle }}">
                                {{ $log->action }}
                            </span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700">
                                {{ $log->module ?: 'General' }}
                            </span>
                        </div>

                        <div class="bg-slate-50 dark:bg-slate-900/50 p-2.5 rounded-xl border border-slate-100 dark:border-slate-800 mb-2">
                            <p class="text-xs text-slate-700 dark:text-slate-300 font-medium m-0 leading-relaxed">
                                {{ $log->description }}
                            </p>
                            @if($log->ip_address)
                                <span class="block mt-1 text-[10px] text-slate-400 font-normal tabular-nums">IP: {{ $log->ip_address }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-400">
                        <span class="font-bold text-slate-700 dark:text-slate-300 tabular-nums">{{ $log->created_at?->format('d M Y, h:i A') }}</span>
                        <span>{{ $log->created_at?->diffForHumans() }}</span>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-8 text-center text-slate-400">
                    No user activity logs found.
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($logs->hasPages())
            <div class="flex flex-col sm:flex-row items-center justify-between pt-4 mt-4 border-t border-slate-200 dark:border-slate-800 gap-3">
                <div class="flex items-center gap-1.5 text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
                    <span>Showing</span>
                    <span class="font-bold text-slate-800 dark:text-slate-100 px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700">{{ $logs->firstItem() ?? 0 }} – {{ $logs->lastItem() ?? 0 }}</span>
                    <span>of</span>
                    <span class="font-bold text-emerald-700 dark:text-emerald-400 px-1.5 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-slate-700">{{ $logs->total() }}</span>
                    <span>logs</span>
                </div>
                <div class="w-full sm:w-auto flex items-center justify-center sm:justify-end">
                    {{ $logs->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
