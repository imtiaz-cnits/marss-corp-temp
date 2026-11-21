@php
    $user = auth()->user();
    $activePanel = 'main';
    if (request()->routeIs('battery.products', 'battery.brands', 'battery.categories', 'battery.sub-categories', 'battery.units', 'battery.barcode.*', 'battery.stock-overview')) {
        $activePanel = 'product';
    } elseif (request()->routeIs('battery.suppliers', 'battery.supplier.*')) {
        $activePanel = 'supplier';
    } elseif (request()->routeIs('battery.customers', 'battery.customer.*')) {
        $activePanel = 'customer';
    } elseif (request()->routeIs('battery.expenses', 'battery.expense.*')) {
        $activePanel = 'expense';
    } elseif (request()->routeIs('battery.reports.*')) {
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
    {{-- Panel 1: Main Battery Menu (Exact 12-item hierarchy with Collapsed Hover Flyouts & Tooltips) --}}
    <div
        id="sidebar-main-panel"
        :class="(!sidebarCollapsed && panel !== 'main') ? '-translate-x-full pointer-events-none' : 'translate-x-0 pointer-events-auto'"
        class="absolute inset-0 overflow-y-auto overflow-x-hidden py-2 px-2.5 transition-transform duration-300 ease-in-out"
    >
        <ul class="space-y-1">
            {{-- 1. Battery Dashboard --}}
            @if($user->canAccess('dashboard'))
            <li class="sidebar-item relative group">
                <a href="{{ route('battery.dashboard') }}" wire:navigate class="sidebar-link w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-[13.5px] font-medium transition-all {{ request()->routeIs('battery.dashboard') ? 'sidebar-active-gradient' : 'text-slate-100 hover:text-white hover:bg-white/15' }}">
                    <i class="fa-solid fa-gauge w-6 text-center text-emerald-200 group-hover:text-emerald-100 text-[15px] shrink-0 transition-transform group-hover:scale-110"></i>
                    <span class="sidebar-label truncate">Battery Dashboard</span>
                </a>
                <div class="sidebar-mini-tooltip">Battery Dashboard</div>
            </li>
            @endif

            {{-- 2. Battery POS --}}
            @if($user->canAccess('pos_terminal'))
            <li class="sidebar-item relative group">
                <a href="{{ route('battery.pos') }}" wire:navigate class="sidebar-link w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-[13.5px] font-medium transition-all {{ request()->routeIs('battery.pos') ? 'sidebar-active-gradient' : 'text-slate-100 hover:text-white hover:bg-white/15' }}">
                    <i class="fa-solid fa-cash-register w-6 text-center text-emerald-200 group-hover:text-emerald-100 text-[15px] shrink-0 transition-transform group-hover:scale-110"></i>
                    <span class="sidebar-label truncate">Battery POS</span>
                </a>
                <div class="sidebar-mini-tooltip">Battery POS</div>
            </li>
            @endif

            {{-- 3. Invoices --}}
            @if($user->canAccess('invoice_list'))
            <li class="sidebar-item relative group">
                <a href="{{ route('battery.invoices') }}" wire:navigate class="sidebar-link w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-[13.5px] font-medium transition-all {{ request()->routeIs('battery.invoices') ? 'sidebar-active-gradient' : 'text-slate-100 hover:text-white hover:bg-white/15' }}">
                    <i class="fa-solid fa-file-invoice-dollar w-6 text-center text-emerald-200 group-hover:text-emerald-100 text-[15px] shrink-0 transition-transform group-hover:scale-110"></i>
                    <span class="sidebar-label truncate">Invoices</span>
                </a>
                <div class="sidebar-mini-tooltip">Invoices</div>
            </li>
            @endif

            {{-- 4. Products (Has Submenu + Collapsed Flyout) --}}
            @if($user->canAccess('product'))
            <li class="sidebar-item has-submenu relative group">
                <button type="button" @click="if (!sidebarCollapsed) panel = 'product'" class="sidebar-drilldown-trigger w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-[13.5px] font-medium text-slate-100 hover:text-white hover:bg-white/15 transition-all cursor-pointer {{ $activePanel === 'product' ? 'sidebar-active-parent' : '' }}">
                    <span class="flex items-center gap-2.5 min-w-0">
                        <i class="fa-solid fa-boxes-stacked w-6 text-center text-emerald-200 group-hover:text-emerald-100 text-[15px] shrink-0 transition-transform group-hover:scale-110"></i>
                        <span class="sidebar-label truncate">Products</span>
                    </span>
                    <span class="sidebar-arrow shrink-0 w-5 h-5 rounded-md bg-white/10 flex items-center justify-center text-white/70 group-hover:text-white group-hover:bg-white/20"><i class="fa-solid fa-chevron-right text-[9px]"></i></span>
                </button>
                <div class="sidebar-flyout">
                    <div class="flex items-center justify-between px-3 py-2 border-b border-emerald-400/25 mb-1.5">
                        <span class="text-[11px] font-bold tracking-wider text-emerald-300 uppercase flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shadow-[0_0_6px_#34d399]"></span>
                            Products
                        </span>
                        <span class="text-[9.5px] font-semibold text-emerald-200/80 bg-emerald-500/20 px-1.5 py-0.5 rounded border border-emerald-400/30">Menu</span>
                    </div>
                    <ul class="py-0.5 px-1 space-y-0.5">
                        <li><a href="{{ route('battery.products') }}" wire:navigate class="sidebar-flyout-link {{ request()->routeIs('battery.products') ? 'active-flyout-link' : '' }}"><i class="fa-solid fa-list-ul text-[10px] text-emerald-300/80 w-4 text-center"></i><span>Product List</span></a></li>
                        <li><a href="{{ route('battery.brands') }}" wire:navigate class="sidebar-flyout-link {{ request()->routeIs('battery.brands') ? 'active-flyout-link' : '' }}"><i class="fa-solid fa-tag text-[10px] text-emerald-300/80 w-4 text-center"></i><span>Brand List</span></a></li>
                        <li><a href="{{ route('battery.categories') }}" wire:navigate class="sidebar-flyout-link {{ request()->routeIs('battery.categories') ? 'active-flyout-link' : '' }}"><i class="fa-solid fa-layer-group text-[10px] text-emerald-300/80 w-4 text-center"></i><span>Category List</span></a></li>
                        <li><a href="{{ route('battery.barcode.generate') }}" wire:navigate class="sidebar-flyout-link {{ request()->routeIs('battery.barcode.generate') ? 'active-flyout-link' : '' }}"><i class="fa-solid fa-barcode text-[10px] text-emerald-300/80 w-4 text-center"></i><span>Barcode Print</span></a></li>
                        <li><a href="{{ route('battery.stock-overview') }}" wire:navigate class="sidebar-flyout-link {{ request()->routeIs('battery.stock-overview') ? 'active-flyout-link' : '' }}"><i class="fa-solid fa-chart-line text-[10px] text-emerald-300/80 w-4 text-center"></i><span>Stock Overview</span></a></li>
                    </ul>
                </div>
            </li>
            @endif

            {{-- 5. Suppliers (Has Submenu + Collapsed Flyout) & 6. Purchase --}}
            @if($user->canAccess('purchase'))
            <li class="sidebar-item has-submenu relative group">
                <button type="button" @click="if (!sidebarCollapsed) panel = 'supplier'" class="sidebar-drilldown-trigger w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-[13.5px] font-medium text-slate-100 hover:text-white hover:bg-white/15 transition-all cursor-pointer {{ $activePanel === 'supplier' ? 'sidebar-active-parent' : '' }}">
                    <span class="flex items-center gap-2.5 min-w-0">
                        <i class="fa-solid fa-truck-field w-6 text-center text-emerald-200 group-hover:text-emerald-100 text-[15px] shrink-0 transition-transform group-hover:scale-110"></i>
                        <span class="sidebar-label truncate">Suppliers</span>
                    </span>
                    <span class="sidebar-arrow shrink-0 w-5 h-5 rounded-md bg-white/10 flex items-center justify-center text-white/70 group-hover:text-white group-hover:bg-white/20"><i class="fa-solid fa-chevron-right text-[9px]"></i></span>
                </button>
                <div class="sidebar-flyout">
                    <div class="flex items-center justify-between px-3 py-2 border-b border-emerald-400/25 mb-1.5">
                        <span class="text-[11px] font-bold tracking-wider text-emerald-300 uppercase flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shadow-[0_0_6px_#34d399]"></span>
                            Suppliers
                        </span>
                        <span class="text-[9.5px] font-semibold text-emerald-200/80 bg-emerald-500/20 px-1.5 py-0.5 rounded border border-emerald-400/30">Menu</span>
                    </div>
                    <ul class="py-0.5 px-1 space-y-0.5">
                        <li><a href="{{ route('battery.suppliers') }}" wire:navigate class="sidebar-flyout-link {{ request()->routeIs('battery.suppliers') ? 'active-flyout-link' : '' }}"><i class="fa-solid fa-address-book text-[10px] text-emerald-300/80 w-4 text-center"></i><span>Supplier List</span></a></li>
                        <li><a href="{{ route('battery.supplier.due') }}" wire:navigate class="sidebar-flyout-link {{ request()->routeIs('battery.supplier.due') ? 'active-flyout-link' : '' }}"><i class="fa-solid fa-file-invoice text-[10px] text-emerald-300/80 w-4 text-center"></i><span>Supplier Due</span></a></li>
                        <li><a href="{{ route('battery.supplier.due.collection') }}" wire:navigate class="sidebar-flyout-link {{ request()->routeIs('battery.supplier.due.collection') ? 'active-flyout-link' : '' }}"><i class="fa-solid fa-hand-holding-dollar text-[10px] text-emerald-300/80 w-4 text-center"></i><span>Due Collection</span></a></li>
                    </ul>
                </div>
            </li>

            <li class="sidebar-item relative group">
                <a href="{{ route('battery.purchases') }}" wire:navigate class="sidebar-link w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-[13.5px] font-medium transition-all {{ request()->routeIs('battery.purchases', 'battery.purchase.*') ? 'sidebar-active-gradient' : 'text-slate-100 hover:text-white hover:bg-white/15' }}">
                    <i class="fa-solid fa-cart-shopping w-6 text-center text-emerald-200 group-hover:text-emerald-100 text-[15px] shrink-0 transition-transform group-hover:scale-110"></i>
                    <span class="sidebar-label truncate">Purchase</span>
                </a>
                <div class="sidebar-mini-tooltip">Purchase</div>
            </li>
            @endif

            {{-- 7. Customers (Has Submenu + Collapsed Flyout) --}}
            @if($user->canAccess('customer'))
            <li class="sidebar-item has-submenu relative group">
                <button type="button" @click="if (!sidebarCollapsed) panel = 'customer'" class="sidebar-drilldown-trigger w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-[13.5px] font-medium text-slate-100 hover:text-white hover:bg-white/15 transition-all cursor-pointer {{ $activePanel === 'customer' ? 'sidebar-active-parent' : '' }}">
                    <span class="flex items-center gap-2.5 min-w-0">
                        <i class="fa-solid fa-users w-6 text-center text-emerald-200 group-hover:text-emerald-100 text-[15px] shrink-0 transition-transform group-hover:scale-110"></i>
                        <span class="sidebar-label truncate">Customers</span>
                    </span>
                    <span class="sidebar-arrow shrink-0 w-5 h-5 rounded-md bg-white/10 flex items-center justify-center text-white/70 group-hover:text-white group-hover:bg-white/20"><i class="fa-solid fa-chevron-right text-[9px]"></i></span>
                </button>
                <div class="sidebar-flyout">
                    <div class="flex items-center justify-between px-3 py-2 border-b border-emerald-400/25 mb-1.5">
                        <span class="text-[11px] font-bold tracking-wider text-emerald-300 uppercase flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shadow-[0_0_6px_#34d399]"></span>
                            Customers
                        </span>
                        <span class="text-[9.5px] font-semibold text-emerald-200/80 bg-emerald-500/20 px-1.5 py-0.5 rounded border border-emerald-400/30">Menu</span>
                    </div>
                    <ul class="py-0.5 px-1 space-y-0.5">
                        <li><a href="{{ route('battery.customers') }}" wire:navigate class="sidebar-flyout-link {{ request()->routeIs('battery.customers') ? 'active-flyout-link' : '' }}"><i class="fa-solid fa-user-group text-[10px] text-emerald-300/80 w-4 text-center"></i><span>Customer List</span></a></li>
                        <li><a href="{{ route('battery.customer.due') }}" wire:navigate class="sidebar-flyout-link {{ request()->routeIs('battery.customer.due') ? 'active-flyout-link' : '' }}"><i class="fa-solid fa-file-invoice-dollar text-[10px] text-emerald-300/80 w-4 text-center"></i><span>Customer Due List</span></a></li>
                    </ul>
                </div>
            </li>
            @endif

            {{-- 8. Expenses (Has Submenu + Collapsed Flyout) --}}
            @if($user->canAccess('expense'))
            <li class="sidebar-item has-submenu relative group">
                <button type="button" @click="if (!sidebarCollapsed) panel = 'expense'" class="sidebar-drilldown-trigger w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-[13.5px] font-medium text-slate-100 hover:text-white hover:bg-white/15 transition-all cursor-pointer {{ $activePanel === 'expense' ? 'sidebar-active-parent' : '' }}">
                    <span class="flex items-center gap-2.5 min-w-0">
                        <i class="fa-solid fa-wallet w-6 text-center text-emerald-200 group-hover:text-emerald-100 text-[15px] shrink-0 transition-transform group-hover:scale-110"></i>
                        <span class="sidebar-label truncate">Expenses</span>
                    </span>
                    <span class="sidebar-arrow shrink-0 w-5 h-5 rounded-md bg-white/10 flex items-center justify-center text-white/70 group-hover:text-white group-hover:bg-white/20"><i class="fa-solid fa-chevron-right text-[9px]"></i></span>
                </button>
                <div class="sidebar-flyout">
                    <div class="flex items-center justify-between px-3 py-2 border-b border-emerald-400/25 mb-1.5">
                        <span class="text-[11px] font-bold tracking-wider text-emerald-300 uppercase flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shadow-[0_0_6px_#34d399]"></span>
                            Expenses
                        </span>
                        <span class="text-[9.5px] font-semibold text-emerald-200/80 bg-emerald-500/20 px-1.5 py-0.5 rounded border border-emerald-400/30">Menu</span>
                    </div>
                    <ul class="py-0.5 px-1 space-y-0.5">
                        <li><a href="{{ route('battery.expenses') }}" wire:navigate class="sidebar-flyout-link {{ request()->routeIs('battery.expenses') ? 'active-flyout-link' : '' }}"><i class="fa-solid fa-receipt text-[10px] text-emerald-300/80 w-4 text-center"></i><span>Expense List</span></a></li>
                        <li><a href="{{ route('battery.expense.types') }}" wire:navigate class="sidebar-flyout-link {{ request()->routeIs('battery.expense.types') ? 'active-flyout-link' : '' }}"><i class="fa-solid fa-tags text-[10px] text-emerald-300/80 w-4 text-center"></i><span>Expense Types</span></a></li>
                    </ul>
                </div>
            </li>
            @endif

            {{-- 9. Return Management --}}
            @if($user->canAccess('return'))
            <li class="sidebar-item relative group">
                <a href="{{ route('battery.sales.returns') }}" wire:navigate class="sidebar-link w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-[13.5px] font-medium transition-all {{ request()->routeIs('battery.sales.returns', 'battery.purchase.returns') ? 'sidebar-active-gradient' : 'text-slate-100 hover:text-white hover:bg-white/15' }}">
                    <i class="fa-solid fa-arrow-rotate-left w-6 text-center text-emerald-200 group-hover:text-emerald-100 text-[15px] shrink-0 transition-transform group-hover:scale-110"></i>
                    <span class="sidebar-label truncate">Return Management</span>
                </a>
                <div class="sidebar-mini-tooltip">Return Management</div>
            </li>
            @endif

            {{-- 10. Opening Balance --}}
            @if($user->canAccess('expense'))
            <li class="sidebar-item relative group">
                <a href="{{ route('battery.opening.balance') }}" wire:navigate class="sidebar-link w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-[13.5px] font-medium transition-all {{ request()->routeIs('battery.opening.balance') ? 'sidebar-active-gradient' : 'text-slate-100 hover:text-white hover:bg-white/15' }}">
                    <i class="fa-solid fa-vault w-6 text-center text-emerald-200 group-hover:text-emerald-100 text-[15px] shrink-0 transition-transform group-hover:scale-110"></i>
                    <span class="sidebar-label truncate">Opening Balance</span>
                </a>
                <div class="sidebar-mini-tooltip">Opening Balance</div>
            </li>
            @endif

            {{-- 11. Reports (Has Submenu + Collapsed Flyout) --}}
            @if($user->canAccess('report'))
            <li class="sidebar-item has-submenu relative group">
                <button type="button" @click="if (!sidebarCollapsed) panel = 'report'" class="sidebar-drilldown-trigger w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-[13.5px] font-medium text-slate-100 hover:text-white hover:bg-white/15 transition-all cursor-pointer {{ $activePanel === 'report' ? 'sidebar-active-parent' : '' }}">
                    <span class="flex items-center gap-2.5 min-w-0">
                        <i class="fa-solid fa-chart-pie w-6 text-center text-emerald-200 group-hover:text-emerald-100 text-[15px] shrink-0 transition-transform group-hover:scale-110"></i>
                        <span class="sidebar-label truncate">Reports</span>
                    </span>
                    <span class="sidebar-arrow shrink-0 w-5 h-5 rounded-md bg-white/10 flex items-center justify-center text-white/70 group-hover:text-white group-hover:bg-white/20"><i class="fa-solid fa-chevron-right text-[9px]"></i></span>
                </button>
                <div class="sidebar-flyout flyout-wide">
                    <div class="flex items-center justify-between px-3 py-2 border-b border-emerald-400/25 mb-1.5">
                        <span class="text-[11px] font-bold tracking-wider text-emerald-300 uppercase flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shadow-[0_0_6px_#34d399]"></span>
                            Reports
                        </span>
                        <span class="text-[9.5px] font-semibold text-emerald-200/80 bg-emerald-500/20 px-1.5 py-0.5 rounded border border-emerald-400/30">Menu</span>
                    </div>
                    <ul class="py-0.5 px-1 space-y-0.5">
                        <li><a href="{{ route('battery.reports.daily-ledger') }}" wire:navigate class="sidebar-flyout-link {{ request()->routeIs('battery.reports.daily-ledger') ? 'active-flyout-link' : '' }}"><i class="fa-solid fa-book-bookmark text-[10px] text-emerald-300/80 w-4 text-center"></i><span>Daily Income &amp; Expense Ledger</span></a></li>
                        <li><a href="{{ route('battery.reports.sales') }}" wire:navigate class="sidebar-flyout-link {{ request()->routeIs('battery.reports.sales') ? 'active-flyout-link' : '' }}"><i class="fa-solid fa-chart-line text-[10px] text-emerald-300/80 w-4 text-center"></i><span>Sales Report</span></a></li>
                        <li><a href="{{ route('battery.reports.income-expense') }}" wire:navigate class="sidebar-flyout-link {{ request()->routeIs('battery.reports.income-expense') ? 'active-flyout-link' : '' }}"><i class="fa-solid fa-chart-column text-[10px] text-emerald-300/80 w-4 text-center"></i><span>Income &amp; Expense Report</span></a></li>
                        <li><a href="{{ route('battery.reports.daily-receipt-payment') }}" wire:navigate class="sidebar-flyout-link {{ request()->routeIs('battery.reports.daily-receipt-payment') ? 'active-flyout-link' : '' }}"><i class="fa-solid fa-file-waveform text-[10px] text-emerald-300/80 w-4 text-center"></i><span>Daily Receipt &amp; Payment</span></a></li>
                        <li><a href="{{ route('battery.reports.personal-transaction') }}" wire:navigate class="sidebar-flyout-link {{ request()->routeIs('battery.reports.personal-transaction') ? 'active-flyout-link' : '' }}"><i class="fa-solid fa-user-tag text-[10px] text-emerald-300/80 w-4 text-center"></i><span>Personal Transaction Report</span></a></li>
                        <li><a href="{{ route('battery.reports.stock-out') }}" wire:navigate class="sidebar-flyout-link text-red-300 hover:text-red-100 hover:bg-red-500/20 font-semibold {{ request()->routeIs('battery.reports.stock-out') ? 'bg-red-600 text-white' : '' }}"><i class="fa-solid fa-triangle-exclamation text-[10px] text-red-400 w-4 text-center"></i><span>Low Stock Report</span></a></li>
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

    {{-- Submenu Panel: Battery Product --}}
    <div :class="(!sidebarCollapsed && panel === 'product') ? 'translate-x-0 pointer-events-auto' : 'translate-x-full pointer-events-none'" class="sidebar-submenu-panel absolute inset-0 overflow-y-auto py-2 px-2.5 transition-transform duration-300 ease-in-out">
        <button type="button" @click="panel = 'main'" class="w-full flex items-center gap-2.5 px-3 py-2.5 mb-2 rounded-xl bg-white/10 hover:bg-white/20 border border-white/15 text-emerald-100 text-sm font-semibold cursor-pointer">
            <i class="fa-solid fa-chevron-left text-xs text-emerald-300"></i><span class="truncate">Products</span>
        </button>
        <ul class="space-y-1 text-[13px]">
            <li><a href="{{ route('battery.products') }}" wire:navigate class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl {{ request()->routeIs('battery.products') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-list-ul w-4 text-center text-emerald-300"></i><span>Product List</span></a></li>
            <li><a href="{{ route('battery.brands') }}" wire:navigate class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl {{ request()->routeIs('battery.brands') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-tag w-4 text-center text-emerald-300"></i><span>Brand List</span></a></li>
            <li><a href="{{ route('battery.categories') }}" wire:navigate class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl {{ request()->routeIs('battery.categories') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-layer-group w-4 text-center text-emerald-300"></i><span>Category List</span></a></li>
            <li><a href="{{ route('battery.barcode.generate') }}" wire:navigate class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl {{ request()->routeIs('battery.barcode.generate') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-barcode w-4 text-center text-emerald-300"></i><span>Barcode Print</span></a></li>
            <li><a href="{{ route('battery.stock-overview') }}" wire:navigate class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl {{ request()->routeIs('battery.stock-overview') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-chart-line w-4 text-center text-emerald-300"></i><span>Stock Overview</span></a></li>
        </ul>
    </div>

    {{-- Submenu Panel: Battery Supplier --}}
    <div :class="(!sidebarCollapsed && panel === 'supplier') ? 'translate-x-0 pointer-events-auto' : 'translate-x-full pointer-events-none'" class="sidebar-submenu-panel absolute inset-0 overflow-y-auto py-2 px-2.5 transition-transform duration-300 ease-in-out">
        <button type="button" @click="panel = 'main'" class="w-full flex items-center gap-2.5 px-3 py-2.5 mb-2 rounded-xl bg-white/10 hover:bg-white/20 border border-white/15 text-emerald-100 text-sm font-semibold cursor-pointer">
            <i class="fa-solid fa-chevron-left text-xs text-emerald-300"></i><span class="truncate">Suppliers</span>
        </button>
        <ul class="space-y-1 text-[13px]">
            <li><a href="{{ route('battery.suppliers') }}" wire:navigate class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl {{ request()->routeIs('battery.suppliers') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-address-book w-4 text-center text-emerald-300"></i><span>Supplier List</span></a></li>
            <li><a href="{{ route('battery.supplier.due') }}" wire:navigate class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl {{ request()->routeIs('battery.supplier.due') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-file-invoice w-4 text-center text-emerald-300"></i><span>Supplier Due List</span></a></li>
            <li><a href="{{ route('battery.supplier.due.collection') }}" wire:navigate class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl {{ request()->routeIs('battery.supplier.due.collection') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-hand-holding-dollar w-4 text-center text-emerald-300"></i><span>Due Collection</span></a></li>
        </ul>
    </div>

    {{-- Submenu Panel: Battery Customer --}}
    <div :class="(!sidebarCollapsed && panel === 'customer') ? 'translate-x-0 pointer-events-auto' : 'translate-x-full pointer-events-none'" class="sidebar-submenu-panel absolute inset-0 overflow-y-auto py-2 px-2.5 transition-transform duration-300 ease-in-out">
        <button type="button" @click="panel = 'main'" class="w-full flex items-center gap-2.5 px-3 py-2.5 mb-2 rounded-xl bg-white/10 hover:bg-white/20 border border-white/15 text-emerald-100 text-sm font-semibold cursor-pointer">
            <i class="fa-solid fa-chevron-left text-xs text-emerald-300"></i><span class="truncate">Customers</span>
        </button>
        <ul class="space-y-1 text-[13px]">
            <li><a href="{{ route('battery.customers') }}" wire:navigate class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl {{ request()->routeIs('battery.customers') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-user-group w-4 text-center text-emerald-300"></i><span>Customer List</span></a></li>
            <li><a href="{{ route('battery.customer.due') }}" wire:navigate class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl {{ request()->routeIs('battery.customer.due') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-file-invoice-dollar w-4 text-center text-emerald-300"></i><span>Customer Due List</span></a></li>
        </ul>
    </div>

    {{-- Submenu Panel: Battery Expense --}}
    <div :class="(!sidebarCollapsed && panel === 'expense') ? 'translate-x-0 pointer-events-auto' : 'translate-x-full pointer-events-none'" class="sidebar-submenu-panel absolute inset-0 overflow-y-auto py-2 px-2.5 transition-transform duration-300 ease-in-out">
        <button type="button" @click="panel = 'main'" class="w-full flex items-center gap-2.5 px-3 py-2.5 mb-2 rounded-xl bg-white/10 hover:bg-white/20 border border-white/15 text-emerald-100 text-sm font-semibold cursor-pointer">
            <i class="fa-solid fa-chevron-left text-xs text-emerald-300"></i><span class="truncate">Expenses</span>
        </button>
        <ul class="space-y-1 text-[13px]">
            <li><a href="{{ route('battery.expenses') }}" wire:navigate class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl {{ request()->routeIs('battery.expenses') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-receipt w-4 text-center text-emerald-300"></i><span>Expense List</span></a></li>
            <li><a href="{{ route('battery.expense.types') }}" wire:navigate class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl {{ request()->routeIs('battery.expense.types') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-tags w-4 text-center text-emerald-300"></i><span>Expense Types</span></a></li>
        </ul>
    </div>

    {{-- Submenu Panel: Battery Reports --}}
    <div :class="(!sidebarCollapsed && panel === 'report') ? 'translate-x-0 pointer-events-auto' : 'translate-x-full pointer-events-none'" class="sidebar-submenu-panel absolute inset-0 overflow-y-auto py-2 px-2.5 transition-transform duration-300 ease-in-out">
        <button type="button" @click="panel = 'main'" class="w-full flex items-center gap-2.5 px-3 py-2.5 mb-2 rounded-xl bg-white/10 hover:bg-white/20 border border-white/15 text-emerald-100 text-sm font-semibold cursor-pointer">
            <i class="fa-solid fa-chevron-left text-xs text-emerald-300"></i><span class="truncate">Reports</span>
        </button>
        <ul class="space-y-1 text-[13px]">
            <li><a href="{{ route('battery.reports.daily-ledger') }}" wire:navigate class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl {{ request()->routeIs('battery.reports.daily-ledger') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-book-bookmark w-4 text-center text-emerald-300"></i><span>Daily Income &amp; Expense Ledger</span></a></li>
            <li><a href="{{ route('battery.reports.sales') }}" wire:navigate class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl {{ request()->routeIs('battery.reports.sales') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-chart-line w-4 text-center text-emerald-300"></i><span>Sales Report</span></a></li>
            <li><a href="{{ route('battery.reports.income-expense') }}" wire:navigate class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl {{ request()->routeIs('battery.reports.income-expense') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-chart-column w-4 text-center text-emerald-300"></i><span>Income &amp; Expense Report</span></a></li>
            <li><a href="{{ route('battery.reports.daily-receipt-payment') }}" wire:navigate class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl {{ request()->routeIs('battery.reports.daily-receipt-payment') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-file-waveform w-4 text-center text-emerald-300"></i><span>Daily Receipt &amp; Payment</span></a></li>
            <li><a href="{{ route('battery.reports.personal-transaction') }}" wire:navigate class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl {{ request()->routeIs('battery.reports.personal-transaction') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-user-tag w-4 text-center text-emerald-300"></i><span>Personal Transaction Report</span></a></li>
            <li><a href="{{ route('battery.reports.stock-out') }}" wire:navigate class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl font-bold {{ request()->routeIs('battery.reports.stock-out') ? 'bg-red-600 text-white' : 'text-red-200 hover:bg-red-600/30 hover:text-white' }}"><i class="fa-solid fa-triangle-exclamation w-4 text-center text-red-300"></i><span>Low Stock Report</span></a></li>
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
