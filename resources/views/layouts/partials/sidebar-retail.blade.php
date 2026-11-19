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
    x-data="{ panel: '{{ $activePanel }}' }"
    class="relative flex-1 w-full overflow-hidden"
>
    {{-- Panel 1: Main Retail Menu (Exact 12-item hierarchy) --}}
    <div
        :class="panel === 'main' ? 'translate-x-0 pointer-events-auto' : '-translate-x-full pointer-events-none'"
        class="absolute inset-0 overflow-y-auto overflow-x-hidden py-2 px-2 transition-transform duration-300 ease-in-out"
    >
        <ul class="space-y-1">
            @if($user->canAccess('dashboard'))
            <li>
                <a href="{{ route('retail.dashboard') }}" wire:navigate class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl text-[13.5px] font-medium transition-all {{ request()->routeIs('retail.dashboard') ? 'bg-gradient-to-r from-emerald-500 to-emerald-600 text-white border-l-[3px] border-emerald-300 shadow-md' : 'text-slate-100 hover:text-white hover:bg-white/15' }}">
                    <i class="fa-solid fa-gauge w-6 text-center text-emerald-200 shrink-0"></i>
                    <span x-show="!sidebarCollapsed" class="truncate">Dashboard</span>
                </a>
            </li>
            @endif

            @if($user->canAccess('pos_terminal'))
            <li>
                <a href="{{ route('retail.pos') }}" wire:navigate class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl text-[13.5px] font-medium transition-all {{ request()->routeIs('retail.pos') ? 'bg-gradient-to-r from-emerald-500 to-emerald-600 text-white border-l-[3px] border-emerald-300 shadow-md' : 'text-slate-100 hover:text-white hover:bg-white/15' }}">
                    <i class="fa-solid fa-cash-register w-6 text-center text-emerald-200 shrink-0"></i>
                    <span x-show="!sidebarCollapsed" class="truncate">POS</span>
                </a>
            </li>
            @endif

            @if($user->canAccess('invoice_list'))
            <li>
                <a href="{{ route('retail.invoices') }}" wire:navigate class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl text-[13.5px] font-medium transition-all {{ request()->routeIs('retail.invoices') ? 'bg-gradient-to-r from-emerald-500 to-emerald-600 text-white border-l-[3px] border-emerald-300 shadow-md' : 'text-slate-100 hover:text-white hover:bg-white/15' }}">
                    <i class="fa-solid fa-file-invoice-dollar w-6 text-center text-emerald-200 shrink-0"></i>
                    <span x-show="!sidebarCollapsed" class="truncate">Invoice List</span>
                </a>
            </li>
            @endif

            @if($user->canAccess('product'))
            <li>
                <button type="button" @click="panel = 'product'" class="w-full flex items-center justify-between px-2.5 py-2 rounded-xl text-[13.5px] font-medium text-slate-100 hover:text-white hover:bg-white/15 transition-all cursor-pointer {{ $activePanel === 'product' ? 'bg-white/15 text-white' : '' }}">
                    <span class="flex items-center gap-2.5 min-w-0">
                        <i class="fa-solid fa-boxes-stacked w-6 text-center text-emerald-200 shrink-0"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">Product</span>
                    </span>
                    <span x-show="!sidebarCollapsed" class="w-5 h-5 rounded-md bg-white/10 flex items-center justify-center"><i class="fa-solid fa-chevron-right text-[9px]"></i></span>
                </button>
            </li>
            @endif

            @if($user->canAccess('purchase'))
            <li>
                <button type="button" @click="panel = 'supplier'" class="w-full flex items-center justify-between px-2.5 py-2 rounded-xl text-[13.5px] font-medium text-slate-100 hover:text-white hover:bg-white/15 transition-all cursor-pointer {{ $activePanel === 'supplier' ? 'bg-white/15 text-white' : '' }}">
                    <span class="flex items-center gap-2.5 min-w-0">
                        <i class="fa-solid fa-truck w-6 text-center text-emerald-200 shrink-0"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">Supplier</span>
                    </span>
                    <span x-show="!sidebarCollapsed" class="w-5 h-5 rounded-md bg-white/10 flex items-center justify-center"><i class="fa-solid fa-chevron-right text-[9px]"></i></span>
                </button>
            </li>
            <li>
                <a href="{{ route('retail.purchases') }}" wire:navigate class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl text-[13.5px] font-medium transition-all {{ request()->routeIs('retail.purchases') ? 'bg-gradient-to-r from-emerald-500 to-emerald-600 text-white border-l-[3px] border-emerald-300 shadow-md' : 'text-slate-100 hover:text-white hover:bg-white/15' }}">
                    <i class="fa-solid fa-cart-shopping w-6 text-center text-emerald-200 shrink-0"></i>
                    <span x-show="!sidebarCollapsed" class="truncate">Purchase</span>
                </a>
            </li>
            @endif

            @if($user->canAccess('customer'))
            <li>
                <button type="button" @click="panel = 'customer'" class="w-full flex items-center justify-between px-2.5 py-2 rounded-xl text-[13.5px] font-medium text-slate-100 hover:text-white hover:bg-white/15 transition-all cursor-pointer {{ $activePanel === 'customer' ? 'bg-white/15 text-white' : '' }}">
                    <span class="flex items-center gap-2.5 min-w-0">
                        <i class="fa-solid fa-users w-6 text-center text-emerald-200 shrink-0"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">Customer</span>
                    </span>
                    <span x-show="!sidebarCollapsed" class="w-5 h-5 rounded-md bg-white/10 flex items-center justify-center"><i class="fa-solid fa-chevron-right text-[9px]"></i></span>
                </button>
            </li>
            @endif

            @if($user->canAccess('expense'))
            <li>
                <button type="button" @click="panel = 'expense'" class="w-full flex items-center justify-between px-2.5 py-2 rounded-xl text-[13.5px] font-medium text-slate-100 hover:text-white hover:bg-white/15 transition-all cursor-pointer {{ $activePanel === 'expense' ? 'bg-white/15 text-white' : '' }}">
                    <span class="flex items-center gap-2.5 min-w-0">
                        <i class="fa-solid fa-wallet w-6 text-center text-emerald-200 shrink-0"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">Expense</span>
                    </span>
                    <span x-show="!sidebarCollapsed" class="w-5 h-5 rounded-md bg-white/10 flex items-center justify-center"><i class="fa-solid fa-chevron-right text-[9px]"></i></span>
                </button>
            </li>
            @endif

            @if($user->canAccess('return'))
            <li>
                <a href="{{ route('retail.returns') }}" wire:navigate class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl text-[13.5px] font-medium transition-all {{ request()->routeIs('retail.returns') ? 'bg-gradient-to-r from-emerald-500 to-emerald-600 text-white border-l-[3px] border-emerald-300 shadow-md' : 'text-slate-100 hover:text-white hover:bg-white/15' }}">
                    <i class="fa-solid fa-arrow-rotate-left w-6 text-center text-emerald-200 shrink-0"></i>
                    <span x-show="!sidebarCollapsed" class="truncate">Return Management</span>
                </a>
            </li>
            @endif

            @if($user->canAccess('expense'))
            <li>
                <a href="{{ route('retail.opening-balance') }}" wire:navigate class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl text-[13.5px] font-medium transition-all {{ request()->routeIs('retail.opening-balance') ? 'bg-gradient-to-r from-emerald-500 to-emerald-600 text-white border-l-[3px] border-emerald-300 shadow-md' : 'text-slate-100 hover:text-white hover:bg-white/15' }}">
                    <i class="fa-solid fa-scale-balanced w-6 text-center text-emerald-200 shrink-0"></i>
                    <span x-show="!sidebarCollapsed" class="truncate">Opening Balance</span>
                </a>
            </li>
            @endif

            @if($user->canAccess('report'))
            <li>
                <button type="button" @click="panel = 'report'" class="w-full flex items-center justify-between px-2.5 py-2 rounded-xl text-[13.5px] font-medium text-slate-100 hover:text-white hover:bg-white/15 transition-all cursor-pointer {{ $activePanel === 'report' ? 'bg-white/15 text-white' : '' }}">
                    <span class="flex items-center gap-2.5 min-w-0">
                        <i class="fa-solid fa-chart-pie w-6 text-center text-emerald-200 shrink-0"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">Report</span>
                    </span>
                    <span x-show="!sidebarCollapsed" class="w-5 h-5 rounded-md bg-white/10 flex items-center justify-center"><i class="fa-solid fa-chevron-right text-[9px]"></i></span>
                </button>
            </li>
            @endif

            @if($user->canAccess('user'))
            <li>
                <button type="button" @click="panel = 'settings'" class="w-full flex items-center justify-between px-2.5 py-2 rounded-xl text-[13.5px] font-medium text-slate-100 hover:text-white hover:bg-white/15 transition-all cursor-pointer {{ $activePanel === 'settings' ? 'bg-white/15 text-white' : '' }}">
                    <span class="flex items-center gap-2.5 min-w-0">
                        <i class="fa-solid fa-gear w-6 text-center text-emerald-200 shrink-0"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">Settings</span>
                    </span>
                    <span x-show="!sidebarCollapsed" class="w-5 h-5 rounded-md bg-white/10 flex items-center justify-center"><i class="fa-solid fa-chevron-right text-[9px]"></i></span>
                </button>
            </li>
            @endif
        </ul>
    </div>

    {{-- Submenu Panel: Product --}}
    <div :class="panel === 'product' ? 'translate-x-0 pointer-events-auto' : 'translate-x-full pointer-events-none'" class="absolute inset-0 overflow-y-auto py-2 px-2 transition-transform duration-300 ease-in-out">
        <button type="button" @click="panel = 'main'" class="w-full flex items-center gap-2 px-2.5 py-2 mb-2 rounded-xl bg-white/10 hover:bg-white/20 border border-white/15 text-emerald-100 text-sm font-semibold cursor-pointer">
            <i class="fa-solid fa-chevron-left text-xs text-emerald-300"></i><span x-show="!sidebarCollapsed">Product</span>
        </button>
        <ul class="space-y-1 text-[13px]">
            <li><a href="{{ route('retail.products') }}" wire:navigate class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl {{ request()->routeIs('retail.products') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-list-ul w-4 text-center text-emerald-300"></i><span x-show="!sidebarCollapsed">Product List</span></a></li>
            <li><a href="{{ route('retail.brands') }}" wire:navigate class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl {{ request()->routeIs('retail.brands') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-tag w-4 text-center text-emerald-300"></i><span x-show="!sidebarCollapsed">Brand List</span></a></li>
            <li><a href="{{ route('retail.categories') }}" wire:navigate class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl {{ request()->routeIs('retail.categories') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-layer-group w-4 text-center text-emerald-300"></i><span x-show="!sidebarCollapsed">Category List</span></a></li>
            <li><a href="{{ route('retail.sub-categories') }}" wire:navigate class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl {{ request()->routeIs('retail.sub-categories') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-sitemap w-4 text-center text-emerald-300"></i><span x-show="!sidebarCollapsed">Sub-Category List</span></a></li>
            <li><a href="{{ route('retail.barcode') }}" wire:navigate class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl {{ request()->routeIs('retail.barcode', 'retail.product-barcode') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-barcode w-4 text-center text-emerald-300"></i><span x-show="!sidebarCollapsed">BarCode Print</span></a></li>
            <li><a href="{{ route('retail.stock-overview') }}" wire:navigate class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl {{ request()->routeIs('retail.stock-overview') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-chart-line w-4 text-center text-emerald-300"></i><span x-show="!sidebarCollapsed">Stock Overview</span></a></li>
        </ul>
    </div>

    {{-- Submenu Panel: Supplier --}}
    <div :class="panel === 'supplier' ? 'translate-x-0 pointer-events-auto' : 'translate-x-full pointer-events-none'" class="absolute inset-0 overflow-y-auto py-2 px-2 transition-transform duration-300 ease-in-out">
        <button type="button" @click="panel = 'main'" class="w-full flex items-center gap-2 px-2.5 py-2 mb-2 rounded-xl bg-white/10 hover:bg-white/20 border border-white/15 text-emerald-100 text-sm font-semibold cursor-pointer">
            <i class="fa-solid fa-chevron-left text-xs text-emerald-300"></i><span x-show="!sidebarCollapsed">Supplier</span>
        </button>
        <ul class="space-y-1 text-[13px]">
            <li><a href="{{ route('retail.suppliers') }}" wire:navigate class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl {{ request()->routeIs('retail.suppliers') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-address-book w-4 text-center text-emerald-300"></i><span x-show="!sidebarCollapsed">Supplier List</span></a></li>
            <li><a href="{{ route('retail.supplier.due') }}" wire:navigate class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl {{ request()->routeIs('retail.supplier.due') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-file-invoice w-4 text-center text-emerald-300"></i><span x-show="!sidebarCollapsed">Supplier Due List</span></a></li>
            <li><a href="{{ route('retail.supplier.due-collection') }}" wire:navigate class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl {{ request()->routeIs('retail.supplier.due-collection') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-hand-holding-dollar w-4 text-center text-emerald-300"></i><span x-show="!sidebarCollapsed">Due Collection List</span></a></li>
        </ul>
    </div>

    {{-- Submenu Panel: Customer --}}
    <div :class="panel === 'customer' ? 'translate-x-0 pointer-events-auto' : 'translate-x-full pointer-events-none'" class="absolute inset-0 overflow-y-auto py-2 px-2 transition-transform duration-300 ease-in-out">
        <button type="button" @click="panel = 'main'" class="w-full flex items-center gap-2 px-2.5 py-2 mb-2 rounded-xl bg-white/10 hover:bg-white/20 border border-white/15 text-emerald-100 text-sm font-semibold cursor-pointer">
            <i class="fa-solid fa-chevron-left text-xs text-emerald-300"></i><span x-show="!sidebarCollapsed">Customer</span>
        </button>
        <ul class="space-y-1 text-[13px]">
            <li><a href="{{ route('retail.customers') }}" wire:navigate class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl {{ request()->routeIs('retail.customers') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-address-card w-4 text-center text-emerald-300"></i><span x-show="!sidebarCollapsed">Customer List</span></a></li>
            <li><a href="{{ route('retail.customer.due') }}" wire:navigate class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl {{ request()->routeIs('retail.customer.due') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-file-invoice w-4 text-center text-emerald-300"></i><span x-show="!sidebarCollapsed">Customer Due List</span></a></li>
            <li><a href="{{ route('retail.customer.due-collection') }}" wire:navigate class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl {{ request()->routeIs('retail.customer.due-collection') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-hand-holding-dollar w-4 text-center text-emerald-300"></i><span x-show="!sidebarCollapsed">Due Collections</span></a></li>
        </ul>
    </div>

    {{-- Submenu Panel: Expense --}}
    <div :class="panel === 'expense' ? 'translate-x-0 pointer-events-auto' : 'translate-x-full pointer-events-none'" class="absolute inset-0 overflow-y-auto py-2 px-2 transition-transform duration-300 ease-in-out">
        <button type="button" @click="panel = 'main'" class="w-full flex items-center gap-2 px-2.5 py-2 mb-2 rounded-xl bg-white/10 hover:bg-white/20 border border-white/15 text-emerald-100 text-sm font-semibold cursor-pointer">
            <i class="fa-solid fa-chevron-left text-xs text-emerald-300"></i><span x-show="!sidebarCollapsed">Expense</span>
        </button>
        <ul class="space-y-1 text-[13px]">
            <li><a href="{{ route('retail.expenses') }}" wire:navigate class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl {{ request()->routeIs('retail.expenses') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-receipt w-4 text-center text-emerald-300"></i><span x-show="!sidebarCollapsed">Expense List</span></a></li>
            <li><a href="{{ route('retail.expense-types') }}" wire:navigate class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl {{ request()->routeIs('retail.expense-types') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-tags w-4 text-center text-emerald-300"></i><span x-show="!sidebarCollapsed">Expense Type</span></a></li>
            <li><a href="{{ route('retail.staff-profile') }}" wire:navigate class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl {{ request()->routeIs('retail.staff-profile') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-id-badge w-4 text-center text-emerald-300"></i><span x-show="!sidebarCollapsed">Staff Salary Profile</span></a></li>
        </ul>
    </div>

    {{-- Submenu Panel: Report --}}
    <div :class="panel === 'report' ? 'translate-x-0 pointer-events-auto' : 'translate-x-full pointer-events-none'" class="absolute inset-0 overflow-y-auto py-2 px-2 transition-transform duration-300 ease-in-out">
        <button type="button" @click="panel = 'main'" class="w-full flex items-center gap-2 px-2.5 py-2 mb-2 rounded-xl bg-white/10 hover:bg-white/20 border border-white/15 text-emerald-100 text-sm font-semibold cursor-pointer">
            <i class="fa-solid fa-chevron-left text-xs text-emerald-300"></i><span x-show="!sidebarCollapsed">Report</span>
        </button>
        <ul class="space-y-1 text-[13px]">
            <li><a href="{{ route('retail.reports.daily-ledger') }}" wire:navigate class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl {{ request()->routeIs('retail.reports.daily-ledger') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-book-bookmark w-4 text-center text-emerald-300"></i><span x-show="!sidebarCollapsed">Daily Income &amp; Expense Ledger</span></a></li>
            <li><a href="{{ route('retail.reports.sales') }}" wire:navigate class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl {{ request()->routeIs('retail.reports.sales') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-chart-line w-4 text-center text-emerald-300"></i><span x-show="!sidebarCollapsed">Sales Report</span></a></li>
            <li><a href="{{ route('retail.reports.income-expense') }}" wire:navigate class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl {{ request()->routeIs('retail.reports.income-expense') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-chart-column w-4 text-center text-emerald-300"></i><span x-show="!sidebarCollapsed">Income &amp; Expense Report</span></a></li>
            <li><a href="{{ route('retail.reports.daily-receipt-payment') }}" wire:navigate class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl {{ request()->routeIs('retail.reports.daily-receipt-payment') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-file-waveform w-4 text-center text-emerald-300"></i><span x-show="!sidebarCollapsed">Daily Receipt &amp; Payment</span></a></li>
            <li><a href="{{ route('retail.reports.personal-transaction') }}" wire:navigate class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl {{ request()->routeIs('retail.reports.personal-transaction') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-user-tag w-4 text-center text-emerald-300"></i><span x-show="!sidebarCollapsed">Personal Transaction Report</span></a></li>
            <li><a href="{{ route('retail.reports.best-selling') }}" wire:navigate class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl {{ request()->routeIs('retail.reports.best-selling') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-fire w-4 text-center text-amber-300"></i><span x-show="!sidebarCollapsed">Best Selling Products</span></a></li>
            <li><a href="{{ route('retail.reports.due-invoice') }}" wire:navigate class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl {{ request()->routeIs('retail.reports.due-invoice') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-file-circle-exclamation w-4 text-center text-amber-300"></i><span x-show="!sidebarCollapsed">Due Invoice Report</span></a></li>
            <li><a href="{{ route('retail.reports.stock-out') }}" wire:navigate class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl font-bold {{ request()->routeIs('retail.reports.stock-out') ? 'bg-red-600 text-white' : 'text-red-200 hover:bg-red-600/30 hover:text-white' }}"><i class="fa-solid fa-triangle-exclamation w-4 text-center text-red-300"></i><span x-show="!sidebarCollapsed">Low Stock Report</span></a></li>
            <li class="pt-1 border-t border-white/10"><a href="{{ route('retail.investors') }}" wire:navigate class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl {{ request()->routeIs('retail.investors') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-handshake w-4 text-center text-emerald-300"></i><span x-show="!sidebarCollapsed">Investor Info</span></a></li>
            <li><a href="{{ route('retail.invests') }}" wire:navigate class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl {{ request()->routeIs('retail.invests') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-sack-dollar w-4 text-center text-emerald-300"></i><span x-show="!sidebarCollapsed">Investments</span></a></li>
        </ul>
    </div>

    {{-- Submenu Panel: Settings --}}
    <div :class="panel === 'settings' ? 'translate-x-0 pointer-events-auto' : 'translate-x-full pointer-events-none'" class="absolute inset-0 overflow-y-auto py-2 px-2 transition-transform duration-300 ease-in-out">
        <button type="button" @click="panel = 'main'" class="w-full flex items-center gap-2 px-2.5 py-2 mb-2 rounded-xl bg-white/10 hover:bg-white/20 border border-white/15 text-emerald-100 text-sm font-semibold cursor-pointer">
            <i class="fa-solid fa-chevron-left text-xs text-emerald-300"></i><span x-show="!sidebarCollapsed">Settings</span>
        </button>
        <ul class="space-y-1 text-[13px]">
            <li><a href="{{ route('settings.users') }}" wire:navigate class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl {{ request()->routeIs('settings.users') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-user-gear w-4 text-center text-emerald-300"></i><span x-show="!sidebarCollapsed">User List &amp; Roles</span></a></li>
            <li><a href="{{ route('settings.profile') }}" wire:navigate class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl {{ request()->routeIs('settings.profile') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-circle-user w-4 text-center text-emerald-300"></i><span x-show="!sidebarCollapsed">Profile</span></a></li>
            @if($user->isAdmin())
            <li><a href="{{ route('settings.activity-log') }}" wire:navigate class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl {{ request()->routeIs('settings.activity-log') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-clock-rotate-left w-4 text-center text-emerald-300"></i><span x-show="!sidebarCollapsed">User Activity Log</span></a></li>
            @endif
            <li class="pt-1 border-t border-white/10"><a href="{{ route('settings.districts') }}" wire:navigate class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl {{ request()->routeIs('settings.districts', 'settings.upazilas', 'settings.thanas', 'settings.locations') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"><i class="fa-solid fa-map-location-dot w-4 text-center text-emerald-300"></i><span x-show="!sidebarCollapsed">Geographic Masters</span></a></li>
        </ul>
    </div>
</div>
