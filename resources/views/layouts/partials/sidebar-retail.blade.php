@php
    $user = auth()->user();
    $activePanel = 'main';
    if (request()->routeIs('retail.products', 'retail.brands', 'retail.categories', 'retail.sub-categories', 'retail.barcode', 'retail.product-barcode', 'retail.stock-overview')) {
        $activePanel = 'product';
    } elseif (request()->routeIs('retail.suppliers', 'retail.supplier.*')) {
        $activePanel = 'supplier';
    } elseif (request()->routeIs('retail.customers', 'retail.customer.*')) {
        $activePanel = 'customer';
    } elseif (request()->routeIs('retail.expenses', 'retail.expense-types', 'retail.staff-profile')) {
        $activePanel = 'expense';
    } elseif (request()->routeIs('retail.reports.*', 'retail.investors', 'retail.invests')) {
        $activePanel = 'report';
    } elseif (request()->routeIs('settings.*')) {
        $activePanel = 'settings';
    }
@endphp

<div
    id="sidebar-slider-wrapper"
    x-data="{ panel: '{{ $activePanel }}' }"
    class="relative flex-1 min-h-0 w-full overflow-hidden"
>
    {{-- Panel 1: Main Retail Menu (Exact 12-item hierarchy with Collapsed Hover Flyouts & Tooltips) --}}
    <div
        id="sidebar-main-panel"
        :class="(!sidebarCollapsed && panel !== 'main') ? '-translate-x-full pointer-events-none' : 'translate-x-0 pointer-events-auto'"
        class="absolute inset-0 overflow-y-auto overflow-x-hidden py-2 px-2.5 transition-transform duration-300 ease-in-out"
    >
        <ul class="space-y-1">
            {{-- 1. Dashboard --}}
            @if($user->canAccess('dashboard'))
            <li class="sidebar-item relative group">
                <a href="{{ route('retail.dashboard') }}" wire:navigate class="sidebar-link w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-[13.5px] font-medium transition-all {{ request()->routeIs('retail.dashboard') ? 'sidebar-active-gradient' : 'text-slate-100 hover:text-white hover:bg-white/15' }}">
                    <i class="fa-solid fa-gauge w-6 text-center text-emerald-200 group-hover:text-emerald-100 text-[15px] shrink-0 transition-transform group-hover:scale-110"></i>
                    <span class="sidebar-label truncate">Dashboard</span>
                </a>
                <div class="sidebar-mini-tooltip">Dashboard</div>
            </li>
            @endif

            {{-- 2. POS --}}
            @if($user->canAccess('pos_terminal'))
            <li class="sidebar-item relative group">
                <a href="{{ route('retail.pos') }}" wire:navigate class="sidebar-link w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-[13.5px] font-medium transition-all {{ request()->routeIs('retail.pos') ? 'sidebar-active-gradient' : 'text-slate-100 hover:text-white hover:bg-white/15' }}">
                    <i class="fa-solid fa-cash-register w-6 text-center text-emerald-200 group-hover:text-emerald-100 text-[15px] shrink-0 transition-transform group-hover:scale-110"></i>
                    <span class="sidebar-label truncate">POS</span>
                </a>
                <div class="sidebar-mini-tooltip">POS</div>
            </li>
            @endif

            {{-- 3. Invoice List --}}
            @if($user->canAccess('invoice_list'))
            <li class="sidebar-item relative group">
                <a href="{{ route('retail.invoices') }}" wire:navigate class="sidebar-link w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-[13.5px] font-medium transition-all {{ request()->routeIs('retail.invoices') ? 'sidebar-active-gradient' : 'text-slate-100 hover:text-white hover:bg-white/15' }}">
                    <i class="fa-solid fa-file-invoice-dollar w-6 text-center text-emerald-200 group-hover:text-emerald-100 text-[15px] shrink-0 transition-transform group-hover:scale-110"></i>
                    <span class="sidebar-label truncate">Invoice List</span>
                </a>
                <div class="sidebar-mini-tooltip">Invoice List</div>
            </li>
            @endif

            {{-- 4. Product (Has Submenu + Collapsed Flyout) --}}
            @if($user->canAccess('product'))
            <li class="sidebar-item has-submenu relative group">
                <button type="button" @click="if (!sidebarCollapsed) panel = 'product'" class="sidebar-drilldown-trigger w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-[13.5px] font-medium text-slate-100 hover:text-white hover:bg-white/15 transition-all cursor-pointer {{ $activePanel === 'product' ? 'sidebar-active-parent' : '' }}">
                    <span class="flex items-center gap-2.5 min-w-0">
                        <i class="fa-solid fa-boxes-stacked w-6 text-center text-emerald-200 group-hover:text-emerald-100 text-[15px] shrink-0 transition-transform group-hover:scale-110"></i>
                        <span class="sidebar-label truncate">Product</span>
                    </span>
                    <span class="sidebar-arrow shrink-0 w-5 h-5 rounded-md bg-white/10 flex items-center justify-center text-white/70 group-hover:text-white group-hover:bg-white/20"><i class="fa-solid fa-chevron-right text-[9px]"></i></span>
                </button>
                <div class="sidebar-flyout">
                    <div class="flex items-center justify-between px-3 py-2 border-b border-emerald-400/25 mb-1.5">
                        <span class="text-[11px] font-bold tracking-wider text-emerald-300 uppercase flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shadow-[0_0_6px_#34d399]"></span>
                            Product
                        </span>
                        <span class="text-[9.5px] font-semibold text-emerald-200/80 bg-emerald-500/20 px-1.5 py-0.5 rounded border border-emerald-400/30">Menu</span>
                    </div>
                    <ul class="py-0.5 px-1 space-y-0.5">
                        <li><a href="{{ route('retail.products') }}" wire:navigate class="sidebar-flyout-link {{ request()->routeIs('retail.products') ? 'active-flyout-link' : '' }}"><i class="fa-solid fa-list-ul text-[10px] text-emerald-300/80 w-4 text-center"></i><span>Product List</span></a></li>
                        <li><a href="{{ route('retail.brands') }}" wire:navigate class="sidebar-flyout-link {{ request()->routeIs('retail.brands') ? 'active-flyout-link' : '' }}"><i class="fa-solid fa-tag text-[10px] text-emerald-300/80 w-4 text-center"></i><span>Brand List</span></a></li>
                        <li><a href="{{ route('retail.categories') }}" wire:navigate class="sidebar-flyout-link {{ request()->routeIs('retail.categories') ? 'active-flyout-link' : '' }}"><i class="fa-solid fa-layer-group text-[10px] text-emerald-300/80 w-4 text-center"></i><span>Category List</span></a></li>
                        <li><a href="{{ route('retail.barcode') }}" wire:navigate class="sidebar-flyout-link {{ request()->routeIs('retail.barcode', 'retail.product-barcode') ? 'active-flyout-link' : '' }}"><i class="fa-solid fa-barcode text-[10px] text-emerald-300/80 w-4 text-center"></i><span>BarCode Print</span></a></li>
                        <li><a href="{{ route('retail.stock-overview') }}" wire:navigate class="sidebar-flyout-link {{ request()->routeIs('retail.stock-overview') ? 'active-flyout-link' : '' }}"><i class="fa-solid fa-chart-line text-[10px] text-emerald-300/80 w-4 text-center"></i><span>Stock Overview</span></a></li>
                    </ul>
                </div>
            </li>
            @endif

            {{-- 5. Supplier (Has Submenu + Collapsed Flyout) & 6. Purchase --}}
            @if($user->canAccess('purchase'))
            <li class="sidebar-item has-submenu relative group">
                <button type="button" @click="if (!sidebarCollapsed) panel = 'supplier'" class="sidebar-drilldown-trigger w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-[13.5px] font-medium text-slate-100 hover:text-white hover:bg-white/15 transition-all cursor-pointer {{ $activePanel === 'supplier' ? 'sidebar-active-parent' : '' }}">
                    <span class="flex items-center gap-2.5 min-w-0">
                        <i class="fa-solid fa-truck w-6 text-center text-emerald-200 group-hover:text-emerald-100 text-[15px] shrink-0 transition-transform group-hover:scale-110"></i>
                        <span class="sidebar-label truncate">Supplier</span>
                    </span>
                    <span class="sidebar-arrow shrink-0 w-5 h-5 rounded-md bg-white/10 flex items-center justify-center text-white/70 group-hover:text-white group-hover:bg-white/20"><i class="fa-solid fa-chevron-right text-[9px]"></i></span>
                </button>
                <div class="sidebar-flyout">
                    <div class="flex items-center justify-between px-3 py-2 border-b border-emerald-400/25 mb-1.5">
                        <span class="text-[11px] font-bold tracking-wider text-emerald-300 uppercase flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shadow-[0_0_6px_#34d399]"></span>
                            Supplier
                        </span>
                        <span class="text-[9.5px] font-semibold text-emerald-200/80 bg-emerald-500/20 px-1.5 py-0.5 rounded border border-emerald-400/30">Menu</span>
                    </div>
                    <ul class="py-0.5 px-1 space-y-0.5">
                        <li><a href="{{ route('retail.suppliers') }}" wire:navigate class="sidebar-flyout-link {{ request()->routeIs('retail.suppliers') ? 'active-flyout-link' : '' }}"><i class="fa-solid fa-address-book text-[10px] text-emerald-300/80 w-4 text-center"></i><span>Supplier List</span></a></li>
                        <li><a href="{{ route('retail.supplier.due') }}" wire:navigate class="sidebar-flyout-link {{ request()->routeIs('retail.supplier.due') ? 'active-flyout-link' : '' }}"><i class="fa-solid fa-file-invoice text-[10px] text-emerald-300/80 w-4 text-center"></i><span>Supplier Due List</span></a></li>
                        <li><a href="{{ route('retail.supplier.due-collection') }}" wire:navigate class="sidebar-flyout-link {{ request()->routeIs('retail.supplier.due-collection') ? 'active-flyout-link' : '' }}"><i class="fa-solid fa-hand-holding-dollar text-[10px] text-emerald-300/80 w-4 text-center"></i><span>Due collection List</span></a></li>
                    </ul>
                </div>
            </li>

            <li class="sidebar-item relative group">
                <a href="{{ route('retail.purchases') }}" wire:navigate class="sidebar-link w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-[13.5px] font-medium transition-all {{ request()->routeIs('retail.purchases') ? 'sidebar-active-gradient' : 'text-slate-100 hover:text-white hover:bg-white/15' }}">
                    <i class="fa-solid fa-cart-shopping w-6 text-center text-emerald-200 group-hover:text-emerald-100 text-[15px] shrink-0 transition-transform group-hover:scale-110"></i>
                    <span class="sidebar-label truncate">Purchase</span>
                </a>
                <div class="sidebar-mini-tooltip">Purchase</div>
            </li>
            @endif

            {{-- 7. Customer (Has Submenu + Collapsed Flyout) --}}
            @if($user->canAccess('customer'))
            <li class="sidebar-item has-submenu relative group">
                <button type="button" @click="if (!sidebarCollapsed) panel = 'customer'" class="sidebar-drilldown-trigger w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-[13.5px] font-medium text-slate-100 hover:text-white hover:bg-white/15 transition-all cursor-pointer {{ $activePanel === 'customer' ? 'sidebar-active-parent' : '' }}">
                    <span class="flex items-center gap-2.5 min-w-0">
                        <i class="fa-solid fa-users w-6 text-center text-emerald-200 group-hover:text-emerald-100 text-[15px] shrink-0 transition-transform group-hover:scale-110"></i>
                        <span class="sidebar-label truncate">Customer</span>
                    </span>
                    <span class="sidebar-arrow shrink-0 w-5 h-5 rounded-md bg-white/10 flex items-center justify-center text-white/70 group-hover:text-white group-hover:bg-white/20"><i class="fa-solid fa-chevron-right text-[9px]"></i></span>
                </button>
                <div class="sidebar-flyout">
                    <div class="flex items-center justify-between px-3 py-2 border-b border-emerald-400/25 mb-1.5">
                        <span class="text-[11px] font-bold tracking-wider text-emerald-300 uppercase flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shadow-[0_0_6px_#34d399]"></span>
                            Customer
                        </span>
                        <span class="text-[9.5px] font-semibold text-emerald-200/80 bg-emerald-500/20 px-1.5 py-0.5 rounded border border-emerald-400/30">Menu</span>
                    </div>
                    <ul class="py-0.5 px-1 space-y-0.5">
                        <li><a href="{{ route('retail.customers') }}" wire:navigate class="sidebar-flyout-link {{ request()->routeIs('retail.customers') ? 'active-flyout-link' : '' }}"><i class="fa-solid fa-user-group text-[10px] text-emerald-300/80 w-4 text-center"></i><span>Customer List</span></a></li>
                        <li><a href="{{ route('retail.customer.due') }}" wire:navigate class="sidebar-flyout-link {{ request()->routeIs('retail.customer.due') ? 'active-flyout-link' : '' }}"><i class="fa-solid fa-file-invoice text-[10px] text-emerald-300/80 w-4 text-center"></i><span>Customer Due List</span></a></li>
                    </ul>
                </div>
            </li>
            @endif

            {{-- 8. Expense (Has Submenu + Collapsed Flyout) --}}
            @if($user->canAccess('expense'))
            <li class="sidebar-item has-submenu relative group">
                <button type="button" @click="if (!sidebarCollapsed) panel = 'expense'" class="sidebar-drilldown-trigger w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-[13.5px] font-medium text-slate-100 hover:text-white hover:bg-white/15 transition-all cursor-pointer {{ $activePanel === 'expense' ? 'sidebar-active-parent' : '' }}">
                    <span class="flex items-center gap-2.5 min-w-0">
                        <i class="fa-solid fa-wallet w-6 text-center text-emerald-200 group-hover:text-emerald-100 text-[15px] shrink-0 transition-transform group-hover:scale-110"></i>
                        <span class="sidebar-label truncate">Expense</span>
                    </span>
                    <span class="sidebar-arrow shrink-0 w-5 h-5 rounded-md bg-white/10 flex items-center justify-center text-white/70 group-hover:text-white group-hover:bg-white/20"><i class="fa-solid fa-chevron-right text-[9px]"></i></span>
                </button>
                <div class="sidebar-flyout">
                    <div class="flex items-center justify-between px-3 py-2 border-b border-emerald-400/25 mb-1.5">
                        <span class="text-[11px] font-bold tracking-wider text-emerald-300 uppercase flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shadow-[0_0_6px_#34d399]"></span>
                            Expense
                        </span>
                        <span class="text-[9.5px] font-semibold text-emerald-200/80 bg-emerald-500/20 px-1.5 py-0.5 rounded border border-emerald-400/30">Menu</span>
                    </div>
                    <ul class="py-0.5 px-1 space-y-0.5">
                        <li><a href="{{ route('retail.expenses') }}" wire:navigate class="sidebar-flyout-link {{ request()->routeIs('retail.expenses') ? 'active-flyout-link' : '' }}"><i class="fa-solid fa-receipt text-[10px] text-emerald-300/80 w-4 text-center"></i><span>Expense List</span></a></li>
                        <li><a href="{{ route('retail.expense-types') }}" wire:navigate class="sidebar-flyout-link {{ request()->routeIs('retail.expense-types') ? 'active-flyout-link' : '' }}"><i class="fa-solid fa-tags text-[10px] text-emerald-300/80 w-4 text-center"></i><span>Expense Type</span></a></li>
                    </ul>
                </div>
            </li>
            @endif

            {{-- 9. Return Management --}}
            @if($user->canAccess('return'))
            <li class="sidebar-item relative group">
                <a href="{{ route('retail.returns') }}" wire:navigate class="sidebar-link w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-[13.5px] font-medium transition-all {{ request()->routeIs('retail.returns') ? 'sidebar-active-gradient' : 'text-slate-100 hover:text-white hover:bg-white/15' }}">
                    <i class="fa-solid fa-arrow-rotate-left w-6 text-center text-emerald-200 group-hover:text-emerald-100 text-[15px] shrink-0 transition-transform group-hover:scale-110"></i>
                    <span class="sidebar-label truncate">Return Management</span>
                </a>
                <div class="sidebar-mini-tooltip">Return Management</div>
            </li>
            @endif

            {{-- 10. Opening Balance --}}
            @if($user->canAccess('expense'))
            <li class="sidebar-item relative group">
                <a href="{{ route('retail.opening-balance') }}" wire:navigate class="sidebar-link w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-[13.5px] font-medium transition-all {{ request()->routeIs('retail.opening-balance') ? 'sidebar-active-gradient' : 'text-slate-100 hover:text-white hover:bg-white/15' }}">
                    <i class="fa-solid fa-scale-balanced w-6 text-center text-emerald-200 group-hover:text-emerald-100 text-[15px] shrink-0 transition-transform group-hover:scale-110"></i>
                    <span class="sidebar-label truncate">Opening Balance</span>
                </a>
                <div class="sidebar-mini-tooltip">Opening Balance</div>
            </li>
            @endif

            {{-- 11. Report (Has Submenu + Collapsed Flyout) --}}
            @if($user->canAccess('report'))
            <li class="sidebar-item has-submenu relative group">
                <button type="button" @click="if (!sidebarCollapsed) panel = 'report'" class="sidebar-drilldown-trigger w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-[13.5px] font-medium text-slate-100 hover:text-white hover:bg-white/15 transition-all cursor-pointer {{ $activePanel === 'report' ? 'sidebar-active-parent' : '' }}">
                    <span class="flex items-center gap-2.5 min-w-0">
                        <i class="fa-solid fa-chart-pie w-6 text-center text-emerald-200 group-hover:text-emerald-100 text-[15px] shrink-0 transition-transform group-hover:scale-110"></i>
                        <span class="sidebar-label truncate">Report</span>
                    </span>
                    <span class="sidebar-arrow shrink-0 w-5 h-5 rounded-md bg-white/10 flex items-center justify-center text-white/70 group-hover:text-white group-hover:bg-white/20"><i class="fa-solid fa-chevron-right text-[9px]"></i></span>
                </button>
                <div class="sidebar-flyout flyout-wide">
                    <div class="flex items-center justify-between px-3 py-2 border-b border-emerald-400/25 mb-1.5">
                        <span class="text-[11px] font-bold tracking-wider text-emerald-300 uppercase flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shadow-[0_0_6px_#34d399]"></span>
                            Report
                        </span>
                        <span class="text-[9.5px] font-semibold text-emerald-200/80 bg-emerald-500/20 px-1.5 py-0.5 rounded border border-emerald-400/30">Menu</span>
                    </div>
                    <ul class="py-0.5 px-1 space-y-0.5">
                        <li><a href="{{ route('retail.reports.daily-ledger') }}" wire:navigate class="sidebar-flyout-link {{ request()->routeIs('retail.reports.daily-ledger') ? 'active-flyout-link' : '' }}"><i class="fa-solid fa-book-bookmark text-[10px] text-emerald-300/80 w-4 text-center"></i><span>Daily Income &amp; Expense Ledger</span></a></li>
                        <li><a href="{{ route('retail.reports.sales') }}" wire:navigate class="sidebar-flyout-link {{ request()->routeIs('retail.reports.sales') ? 'active-flyout-link' : '' }}"><i class="fa-solid fa-chart-line text-[10px] text-emerald-300/80 w-4 text-center"></i><span>Sales Report</span></a></li>
                        <li><a href="{{ route('retail.reports.income-expense') }}" wire:navigate class="sidebar-flyout-link {{ request()->routeIs('retail.reports.income-expense') ? 'active-flyout-link' : '' }}"><i class="fa-solid fa-chart-column text-[10px] text-emerald-300/80 w-4 text-center"></i><span>Income &amp; Expense Report</span></a></li>
                        <li><a href="{{ route('retail.reports.daily-receipt-payment') }}" wire:navigate class="sidebar-flyout-link {{ request()->routeIs('retail.reports.daily-receipt-payment') ? 'active-flyout-link' : '' }}"><i class="fa-solid fa-file-waveform text-[10px] text-emerald-300/80 w-4 text-center"></i><span>Daily Receipt &amp; Payment</span></a></li>
                        <li><a href="{{ route('retail.reports.personal-transaction') }}" wire:navigate class="sidebar-flyout-link {{ request()->routeIs('retail.reports.personal-transaction') ? 'active-flyout-link' : '' }}"><i class="fa-solid fa-user-tag text-[10px] text-emerald-300/80 w-4 text-center"></i><span>Personal Transaction Report</span></a></li>
                        <li><a href="{{ route('retail.reports.stock-out') }}" wire:navigate class="sidebar-flyout-link text-red-300 hover:text-red-100 hover:bg-red-500/20 font-semibold {{ request()->routeIs('retail.reports.stock-out') ? 'bg-red-600 text-white' : '' }}"><i class="fa-solid fa-triangle-exclamation text-[10px] text-red-400 w-4 text-center"></i><span>Low Stock Report</span></a></li>
                    </ul>
                </div>
            </li>
            @endif

            {{-- 12. Settings (Profile for all users; User List & Activity Log strictly for Developer ONLY) --}}
            <li class="sidebar-item has-submenu relative group">
                <button type="button" @click="if (!sidebarCollapsed) panel = 'settings'" class="sidebar-drilldown-trigger w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-[13.5px] font-medium text-slate-100 hover:text-white hover:bg-white/15 transition-all cursor-pointer {{ $activePanel === 'settings' ? 'sidebar-active-parent' : '' }}">
                    <span class="flex items-center gap-2.5 min-w-0">
                        <i class="fa-solid fa-gear w-6 text-center text-emerald-200 group-hover:text-emerald-100 text-[15px] shrink-0 transition-transform group-hover:scale-110"></i>
                        <span class="sidebar-label truncate">Settings</span>
                    </span>
                    <span class="sidebar-arrow shrink-0 w-5 h-5 rounded-md bg-white/10 flex items-center justify-center text-white/70 group-hover:text-white group-hover:bg-white/20"><i class="fa-solid fa-chevron-right text-[9px]"></i></span>
                </button>
                <div class="sidebar-flyout">
                    <div class="flex items-center justify-between px-3 py-2 border-b border-emerald-400/25 mb-1.5">
                        <span class="text-[11px] font-bold tracking-wider text-emerald-300 uppercase flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shadow-[0_0_6px_#34d399]"></span>
                            Settings
                        </span>
                        <span class="text-[9.5px] font-semibold text-emerald-200/80 bg-emerald-500/20 px-1.5 py-0.5 rounded border border-emerald-400/30">Menu</span>
                    </div>
                    <ul class="py-0.5 px-1 space-y-0.5">
                        @if($user->isDeveloper())
                        <li><a href="{{ route('settings.users') }}" wire:navigate class="sidebar-flyout-link {{ request()->routeIs('settings.users') ? 'active-flyout-link' : '' }}"><i class="fa-solid fa-user-gear text-[10px] text-emerald-300/80 w-4 text-center"></i><span>User List &amp; Roles</span></a></li>
                        @endif
                        <li><a href="{{ route('settings.profile') }}" wire:navigate class="sidebar-flyout-link {{ request()->routeIs('settings.profile') ? 'active-flyout-link' : '' }}"><i class="fa-solid fa-circle-user text-[10px] text-emerald-300/80 w-4 text-center"></i><span>Profile</span></a></li>
                        @if($user->isDeveloper())
                        <li><a href="{{ route('settings.activity-log') }}" wire:navigate class="sidebar-flyout-link {{ request()->routeIs('settings.activity-log') ? 'active-flyout-link' : '' }}"><i class="fa-solid fa-clock-rotate-left text-[10px] text-emerald-300/80 w-4 text-center"></i><span>User Activity Log</span></a></li>
                        @endif
                    </ul>
                </div>
            </li>
        </ul>
    </div>

    {{-- Submenu Panel: Product --}}
    <div :class="(!sidebarCollapsed && panel === 'product') ? 'translate-x-0 pointer-events-auto' : 'translate-x-full pointer-events-none'" class="sidebar-submenu-panel absolute inset-0 overflow-y-auto py-2 px-2.5 transition-transform duration-300 ease-in-out">
        <button type="button" @click="panel = 'main'" class="w-full flex items-center gap-2.5 px-3 py-2.5 mb-2 rounded-xl bg-white/10 hover:bg-white/20 border border-white/15 text-emerald-100 text-sm font-semibold cursor-pointer">
            <i class="fa-solid fa-chevron-left text-xs text-emerald-300"></i><span class="truncate">Product</span>
        </button>
        <ul class="space-y-1 text-[13px]">
            <li><a href="{{ route('retail.products') }}" wire:navigate class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl {{ request()->routeIs('retail.products') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-list-ul w-4 text-center text-emerald-300"></i><span>Product List</span></a></li>
            <li><a href="{{ route('retail.brands') }}" wire:navigate class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl {{ request()->routeIs('retail.brands') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-tag w-4 text-center text-emerald-300"></i><span>Brand List</span></a></li>
            <li><a href="{{ route('retail.categories') }}" wire:navigate class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl {{ request()->routeIs('retail.categories') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-layer-group w-4 text-center text-emerald-300"></i><span>Category List</span></a></li>
            <li><a href="{{ route('retail.barcode') }}" wire:navigate class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl {{ request()->routeIs('retail.barcode', 'retail.product-barcode') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-barcode w-4 text-center text-emerald-300"></i><span>BarCode Print</span></a></li>
            <li><a href="{{ route('retail.stock-overview') }}" wire:navigate class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl {{ request()->routeIs('retail.stock-overview') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-chart-line w-4 text-center text-emerald-300"></i><span>Stock Overview</span></a></li>
        </ul>
    </div>

    {{-- Submenu Panel: Supplier --}}
    <div :class="(!sidebarCollapsed && panel === 'supplier') ? 'translate-x-0 pointer-events-auto' : 'translate-x-full pointer-events-none'" class="sidebar-submenu-panel absolute inset-0 overflow-y-auto py-2 px-2.5 transition-transform duration-300 ease-in-out">
        <button type="button" @click="panel = 'main'" class="w-full flex items-center gap-2.5 px-3 py-2.5 mb-2 rounded-xl bg-white/10 hover:bg-white/20 border border-white/15 text-emerald-100 text-sm font-semibold cursor-pointer">
            <i class="fa-solid fa-chevron-left text-xs text-emerald-300"></i><span class="truncate">Supplier</span>
        </button>
        <ul class="space-y-1 text-[13px]">
            <li><a href="{{ route('retail.suppliers') }}" wire:navigate class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl {{ request()->routeIs('retail.suppliers') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-address-book w-4 text-center text-emerald-300"></i><span>Supplier List</span></a></li>
            <li><a href="{{ route('retail.supplier.due') }}" wire:navigate class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl {{ request()->routeIs('retail.supplier.due') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-file-invoice w-4 text-center text-emerald-300"></i><span>Supplier Due List</span></a></li>
            <li><a href="{{ route('retail.supplier.due-collection') }}" wire:navigate class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl {{ request()->routeIs('retail.supplier.due-collection') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-hand-holding-dollar w-4 text-center text-emerald-300"></i><span>Due collection List</span></a></li>
        </ul>
    </div>

    {{-- Submenu Panel: Customer --}}
    <div :class="(!sidebarCollapsed && panel === 'customer') ? 'translate-x-0 pointer-events-auto' : 'translate-x-full pointer-events-none'" class="sidebar-submenu-panel absolute inset-0 overflow-y-auto py-2 px-2.5 transition-transform duration-300 ease-in-out">
        <button type="button" @click="panel = 'main'" class="w-full flex items-center gap-2.5 px-3 py-2.5 mb-2 rounded-xl bg-white/10 hover:bg-white/20 border border-white/15 text-emerald-100 text-sm font-semibold cursor-pointer">
            <i class="fa-solid fa-chevron-left text-xs text-emerald-300"></i><span class="truncate">Customer</span>
        </button>
        <ul class="space-y-1 text-[13px]">
            <li><a href="{{ route('retail.customers') }}" wire:navigate class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl {{ request()->routeIs('retail.customers') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-address-card w-4 text-center text-emerald-300"></i><span>Customer List</span></a></li>
            <li><a href="{{ route('retail.customer.due') }}" wire:navigate class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl {{ request()->routeIs('retail.customer.due') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-file-invoice w-4 text-center text-emerald-300"></i><span>Customer Due List</span></a></li>
        </ul>
    </div>

    {{-- Submenu Panel: Expense --}}
    <div :class="(!sidebarCollapsed && panel === 'expense') ? 'translate-x-0 pointer-events-auto' : 'translate-x-full pointer-events-none'" class="sidebar-submenu-panel absolute inset-0 overflow-y-auto py-2 px-2.5 transition-transform duration-300 ease-in-out">
        <button type="button" @click="panel = 'main'" class="w-full flex items-center gap-2.5 px-3 py-2.5 mb-2 rounded-xl bg-white/10 hover:bg-white/20 border border-white/15 text-emerald-100 text-sm font-semibold cursor-pointer">
            <i class="fa-solid fa-chevron-left text-xs text-emerald-300"></i><span class="truncate">Expense</span>
        </button>
        <ul class="space-y-1 text-[13px]">
            <li><a href="{{ route('retail.expenses') }}" wire:navigate class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl {{ request()->routeIs('retail.expenses') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-receipt w-4 text-center text-emerald-300"></i><span>Expense List</span></a></li>
            <li><a href="{{ route('retail.expense-types') }}" wire:navigate class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl {{ request()->routeIs('retail.expense-types') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-tags w-4 text-center text-emerald-300"></i><span>Expense Type</span></a></li>
        </ul>
    </div>

    {{-- Submenu Panel: Report --}}
    <div :class="(!sidebarCollapsed && panel === 'report') ? 'translate-x-0 pointer-events-auto' : 'translate-x-full pointer-events-none'" class="sidebar-submenu-panel absolute inset-0 overflow-y-auto py-2 px-2.5 transition-transform duration-300 ease-in-out">
        <button type="button" @click="panel = 'main'" class="w-full flex items-center gap-2.5 px-3 py-2.5 mb-2 rounded-xl bg-white/10 hover:bg-white/20 border border-white/15 text-emerald-100 text-sm font-semibold cursor-pointer">
            <i class="fa-solid fa-chevron-left text-xs text-emerald-300"></i><span class="truncate">Report</span>
        </button>
        <ul class="space-y-1 text-[13px]">
            <li><a href="{{ route('retail.reports.daily-ledger') }}" wire:navigate class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl {{ request()->routeIs('retail.reports.daily-ledger') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-book-bookmark w-4 text-center text-emerald-300"></i><span>Daily Income &amp; Expense Ledger</span></a></li>
            <li><a href="{{ route('retail.reports.sales') }}" wire:navigate class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl {{ request()->routeIs('retail.reports.sales') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-chart-line w-4 text-center text-emerald-300"></i><span>Sales Report</span></a></li>
            <li><a href="{{ route('retail.reports.income-expense') }}" wire:navigate class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl {{ request()->routeIs('retail.reports.income-expense') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-chart-column w-4 text-center text-emerald-300"></i><span>Income &amp; Expense Report</span></a></li>
            <li><a href="{{ route('retail.reports.daily-receipt-payment') }}" wire:navigate class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl {{ request()->routeIs('retail.reports.daily-receipt-payment') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-file-waveform w-4 text-center text-emerald-300"></i><span>Daily Receipt &amp; Payment</span></a></li>
            <li><a href="{{ route('retail.reports.personal-transaction') }}" wire:navigate class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl {{ request()->routeIs('retail.reports.personal-transaction') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-user-tag w-4 text-center text-emerald-300"></i><span>Personal Transaction Report</span></a></li>
            <li><a href="{{ route('retail.reports.stock-out') }}" wire:navigate class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl font-bold {{ request()->routeIs('retail.reports.stock-out') ? 'bg-red-600 text-white' : 'text-red-200 hover:bg-red-600/30 hover:text-white' }}"><i class="fa-solid fa-triangle-exclamation w-4 text-center text-red-300"></i><span>Low Stock Report</span></a></li>
        </ul>
    </div>

    {{-- Submenu Panel: Settings (Profile for all users; User List & Activity Log strictly for Developer ONLY) --}}
    <div :class="(!sidebarCollapsed && panel === 'settings') ? 'translate-x-0 pointer-events-auto' : 'translate-x-full pointer-events-none'" class="sidebar-submenu-panel absolute inset-0 overflow-y-auto py-2 px-2.5 transition-transform duration-300 ease-in-out">
        <button type="button" @click="panel = 'main'" class="w-full flex items-center gap-2.5 px-3 py-2.5 mb-2 rounded-xl bg-white/10 hover:bg-white/20 border border-white/15 text-emerald-100 text-sm font-semibold cursor-pointer">
            <i class="fa-solid fa-chevron-left text-xs text-emerald-300"></i><span class="truncate">Settings</span>
        </button>
        <ul class="space-y-1 text-[13px]">
            @if($user->isDeveloper())
            <li><a href="{{ route('settings.users') }}" wire:navigate class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl {{ request()->routeIs('settings.users') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-user-gear w-4 text-center text-emerald-300"></i><span>User List &amp; Roles</span></a></li>
            @endif
            <li><a href="{{ route('settings.profile') }}" wire:navigate class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl {{ request()->routeIs('settings.profile') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-circle-user w-4 text-center text-emerald-300"></i><span>Profile</span></a></li>
            @if($user->isDeveloper())
            <li><a href="{{ route('settings.activity-log') }}" wire:navigate class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl {{ request()->routeIs('settings.activity-log') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-clock-rotate-left w-4 text-center text-emerald-300"></i><span>User Activity Log</span></a></li>
            @endif
        </ul>
    </div>
</div>
