@php
    use App\Support\Money;
    $b = $metrics['banner'];
    $m = $metrics['monthly'];
    $f = $metrics['financial'];
    $e = $metrics['entities'];
    $t = $metrics['trend'];
    $chartPayload = [
        'labels' => $t['labels'],
        'sales' => $t['sales'],
        'profit' => $t['profit'],
        'includeProfit' => $canViewProfit,
        'cash' => (float) $b['cash_collection'],
        'expense' => (float) $b['expense'],
        'supplierPay' => (float) $b['supplier_payment'],
        'due' => (float) $b['customer_due'],
    ];
    $filterLabels = [
        'today' => 'Today',
        'last_week' => 'Last 7 Days',
        'this_month' => 'This Month',
        'last_month' => 'Last 30 Days',
        'date_wise' => 'Date Wise',
        'all_time' => 'All Time',
    ];
    $currentFilterLabel = $filterLabels[$filterType] ?? 'Today';
@endphp

<div class="space-y-3">
    {{-- Top Overview Dashboard Banner Card --}}
    <div class="bg-white dark:bg-[#0f172a] border border-[#e2e8f0] dark:border-[#1e293b] rounded-[16px] p-3.5 px-4 shadow-2xs flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-[44px] h-[44px] rounded-[12px] bg-[#e0f2fe] dark:bg-sky-950/60 text-[#0284c7] dark:text-sky-400 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-arrow-trend-up text-lg"></i>
            </div>
            <div>
                <h1 class="text-[18px] font-bold text-[#1e293b] dark:text-slate-100 leading-tight m-0">Battery Overview Dashboard</h1>
                <p class="text-[12px] font-medium text-[#64748b] dark:text-slate-400 m-0 mt-0.5">
                    Real-time battery business performance &amp; financial summary
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <div class="flex items-center gap-2 pr-2 sm:border-r border-slate-200 dark:border-slate-800">
                <a
                    href="{{ route('battery.pos') }}"
                    wire:navigate
                    class="h-[38px] px-4 rounded-full text-white text-[13px] font-semibold inline-flex items-center gap-2 shadow-xs hover:-translate-y-0.5 transition-all"
                    style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: 1px solid #059669;"
                >
                    <i class="fa-solid fa-desktop text-xs"></i>
                    <span>POS Terminal</span>
                </a>
                <a
                    href="{{ route('battery.products') }}"
                    wire:navigate
                    class="h-[38px] px-3.5 rounded-full bg-white dark:bg-[#1e293b] hover:bg-slate-50 dark:hover:bg-slate-800 border border-[#cbd5e1] dark:border-[#334155] text-[#334155] dark:text-slate-200 text-[13px] font-semibold hidden sm:inline-flex items-center gap-1.5 shadow-2xs transition-colors"
                >
                    <i class="fa-solid fa-circle-plus text-emerald-600"></i>
                    <span>Products</span>
                </a>
            </div>

            @if($filterType === 'date_wise')
                <div class="flex flex-wrap items-center gap-1.5">
                    <div class="w-[138px]">
                        <x-ui.date-picker wire:model.live="startDate" placeholder="From Date" class="!rounded-full !h-[38px] !text-xs" />
                    </div>
                    <span class="text-xs font-semibold text-slate-400">to</span>
                    <div class="w-[138px]">
                        <x-ui.date-picker wire:model.live="endDate" placeholder="To Date" class="!rounded-full !h-[38px] !text-xs" />
                    </div>
                    <button
                        type="button"
                        wire:click="clearDateRange"
                        title="Reset Filter"
                        class="w-[32px] h-[32px] rounded-full bg-[#fee2e2] hover:bg-rose-200 text-[#dc2626] border border-[#fecaca] flex items-center justify-center cursor-pointer transition-colors"
                    >
                        <i class="fa-solid fa-xmark text-xs"></i>
                    </button>
                </div>
            @endif

            <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                <button
                    type="button"
                    @click="open = !open"
                    class="h-[38px] min-w-[135px] px-4 rounded-full bg-white dark:bg-[#1e293b] border border-[#cbd5e1] dark:border-[#334155] hover:border-[#94a3b8] text-[#334155] dark:text-slate-100 text-[13px] font-medium inline-flex items-center justify-between gap-3 shadow-2xs cursor-pointer transition-all"
                >
                    <span>{{ $currentFilterLabel }}</span>
                    <i class="fa-solid fa-chevron-down text-[10px] opacity-60 transition-transform" :class="open ? 'rotate-180' : ''"></i>
                </button>

                <div
                    x-show="open"
                    x-transition
                    style="display: none;"
                    class="absolute right-0 top-[calc(100%+6px)] min-w-[170px] bg-white dark:bg-[#1e293b] border border-[#e2e8f0] dark:border-[#334155] rounded-[12px] shadow-xl p-1.5 z-50 space-y-0.5"
                >
                    @foreach($filterLabels as $key => $label)
                        <button
                            type="button"
                            wire:click="setFilter('{{ $key }}')"
                            @click="open = false"
                            class="w-full px-3 py-2 rounded-[8px] text-[13px] font-medium flex items-center justify-between text-left cursor-pointer transition-colors {{ $filterType === $key ? 'bg-[#059669] text-white font-semibold' : 'text-[#334155] dark:text-slate-200 hover:bg-[#f1f5f9] dark:hover:bg-[#334155]' }}"
                        >
                            <span>{{ $label }}</span>
                            @if($filterType === $key)
                                <i class="fa-solid fa-check text-[11px] text-white"></i>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>

            <button
                type="button"
                wire:click="$refresh"
                title="Refresh Dashboard"
                class="w-[38px] h-[38px] rounded-full bg-white dark:bg-[#1e293b] hover:bg-slate-50 dark:hover:bg-[#334155] border border-[#cbd5e1] dark:border-[#334155] text-[#475569] dark:text-slate-200 inline-flex items-center justify-center shadow-2xs cursor-pointer transition-colors"
            >
                <i class="fa-solid fa-arrows-rotate text-xs" wire:loading.class="fa-spin"></i>
            </button>
        </div>
    </div>

    {{-- Row 1: 4 Main Financial Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3">
        <div class="bg-white dark:bg-[#0f172a] border border-[#f1f5f9] dark:border-[#1e293b] rounded-[14px] p-4 shadow-2xs">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-[#64748b] dark:text-slate-400 m-0 mb-1">INCOME</p>
                    <h3 class="text-[22px] font-bold text-[#1e293b] dark:text-white m-0 tabular-nums">{{ Money::formatBd($b['sales_amount']) }}</h3>
                </div>
                <div class="w-[42px] h-[42px] rounded-[10px] bg-[#d1fae5] dark:bg-emerald-950/60 text-[#059669] dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-sack-dollar text-lg"></i>
                </div>
            </div>
            <span class="px-2 py-1 rounded-[6px] bg-[#ecfdf5] dark:bg-emerald-950/50 text-[#059669] dark:text-emerald-300 text-[11px] font-semibold inline-flex items-center gap-1">
                <i class="fa-solid fa-file-invoice"></i>
                <span>{{ $b['sales_count'] }} Invoices (Net Sales)</span>
            </span>
        </div>

        <div class="bg-white dark:bg-[#0f172a] border border-[#f1f5f9] dark:border-[#1e293b] rounded-[14px] p-4 shadow-2xs">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-[#64748b] dark:text-slate-400 m-0 mb-1">CASH COLLECTION</p>
                    <h3 class="text-[22px] font-bold text-[#1e293b] dark:text-white m-0 tabular-nums">{{ Money::formatBd($b['cash_collection']) }}</h3>
                </div>
                <div class="w-[42px] h-[42px] rounded-[10px] bg-[#e0f2fe] dark:bg-sky-950/60 text-[#0284c7] dark:text-sky-400 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-hand-holding-dollar text-lg"></i>
                </div>
            </div>
            <span class="px-2 py-1 rounded-[6px] bg-[#f0f9ff] dark:bg-sky-950/50 text-[#0284c7] dark:text-sky-300 text-[11px] font-semibold inline-flex items-center gap-1">
                <i class="fa-solid fa-circle-check"></i>
                <span>Received in Hand</span>
            </span>
        </div>

        <div class="bg-white dark:bg-[#0f172a] border border-[#f1f5f9] dark:border-[#1e293b] rounded-[14px] p-4 shadow-2xs">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-[#64748b] dark:text-slate-400 m-0 mb-1">EXPENSE</p>
                    <h3 class="text-[22px] font-bold text-[#1e293b] dark:text-white m-0 tabular-nums">{{ Money::formatBd($b['expense']) }}</h3>
                </div>
                <div class="w-[42px] h-[42px] rounded-[10px] bg-[#ffe4e6] dark:bg-rose-950/60 text-[#e11d48] dark:text-rose-400 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-money-bill-transfer text-lg"></i>
                </div>
            </div>
            <span class="px-2 py-1 rounded-[6px] bg-[#fff1f2] dark:bg-rose-950/50 text-[#e11d48] dark:text-rose-300 text-[11px] font-semibold inline-flex items-center gap-1">
                <i class="fa-solid fa-arrow-trend-down"></i>
                <span>Costs &amp; Payments</span>
            </span>
        </div>

        <div class="bg-white dark:bg-[#0f172a] border border-[#f1f5f9] dark:border-[#1e293b] rounded-[14px] p-4 shadow-2xs">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-[#64748b] dark:text-slate-400 m-0 mb-1">
                        {{ $canViewProfit ? 'PROFIT' : 'PERIOD DUE' }}
                    </p>
                    <h3 class="text-[22px] font-bold text-[#1e293b] dark:text-white m-0 tabular-nums">
                        {{ Money::formatBd($canViewProfit ? $b['net_profit'] : $b['customer_due']) }}
                    </h3>
                </div>
                <div class="w-[42px] h-[42px] rounded-[10px] bg-[#ede9fe] dark:bg-purple-950/60 text-[#7c3aed] dark:text-purple-400 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-chart-pie text-lg"></i>
                </div>
            </div>
            <span class="px-2 py-1 rounded-[6px] bg-[#f5f3ff] dark:bg-purple-950/50 text-[#7c3aed] dark:text-purple-300 text-[11px] font-semibold inline-flex items-center gap-1">
                <i class="fa-solid fa-coins"></i>
                <span>{{ $canViewProfit ? 'Net Earnings' : 'Unpaid Invoices' }}</span>
                @if($canViewProfit)<span class="sr-only">Net Profit (Admin)</span>@endif
            </span>
        </div>
    </div>

    {{-- Row 2: 4 Secondary Metric Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3">
        <div class="bg-white dark:bg-[#0f172a] border border-[#f1f5f9] dark:border-[#1e293b] rounded-[14px] p-4 shadow-2xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-[#64748b] dark:text-slate-400 m-0 mb-1">RECEIVABLE DUE</p>
                <h4 class="text-[19px] font-bold text-[#1e293b] dark:text-white m-0 tabular-nums">{{ Money::formatBd($b['customer_due']) }}</h4>
                <span class="text-[11px] text-[#94a3b8]">Customer unpaid balance</span>
            </div>
            <div class="w-[38px] h-[38px] rounded-[10px] bg-[#fef3c7] dark:bg-amber-950/60 text-[#d97706] dark:text-amber-400 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-user-clock text-sm"></i>
            </div>
        </div>

        <div class="bg-white dark:bg-[#0f172a] border border-[#f1f5f9] dark:border-[#1e293b] rounded-[14px] p-4 shadow-2xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-[#64748b] dark:text-slate-400 m-0 mb-1">PAYABLE DUE</p>
                <h4 class="text-[19px] font-bold text-[#1e293b] dark:text-white m-0 tabular-nums">{{ Money::formatBd($f['supplier_payable']) }}</h4>
                <span class="text-[11px] text-[#94a3b8]">Supplier unpaid balance</span>
            </div>
            <div class="w-[38px] h-[38px] rounded-[10px] bg-[#ffedd5] dark:bg-orange-950/60 text-[#ea580c] dark:text-orange-400 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-truck-ramp-box text-sm"></i>
            </div>
        </div>

        <div class="bg-white dark:bg-[#0f172a] border border-[#f1f5f9] dark:border-[#1e293b] rounded-[14px] p-4 shadow-2xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-[#64748b] dark:text-slate-400 m-0 mb-1">RETURN</p>
                <h4 class="text-[19px] font-bold text-[#1e293b] dark:text-white m-0 tabular-nums">{{ Money::formatBd($b['return_amount']) }}</h4>
                <span class="text-[11px] text-[#94a3b8]">Customer returned items</span>
            </div>
            <div class="w-[38px] h-[38px] rounded-[10px] bg-[#f1f5f9] dark:bg-slate-800 text-[#475569] dark:text-slate-300 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-rotate-left text-sm"></i>
            </div>
        </div>

        <div class="bg-white dark:bg-[#0f172a] border border-[#f1f5f9] dark:border-[#1e293b] rounded-[14px] p-4 shadow-2xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-[#64748b] dark:text-slate-400 m-0 mb-1">DISCOUNT</p>
                <h4 class="text-[19px] font-bold text-[#1e293b] dark:text-white m-0 tabular-nums">{{ Money::formatBd($b['discount_amount']) }}</h4>
                <span class="text-[11px] text-[#94a3b8]">Total discount granted</span>
            </div>
            <div class="w-[38px] h-[38px] rounded-[10px] bg-[#fce7f3] dark:bg-pink-950/60 text-[#db2777] dark:text-pink-400 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-tags text-sm"></i>
            </div>
        </div>
    </div>

    {{-- Row 3: Unified 3-Column Entities Card --}}
    <div class="bg-white dark:bg-[#0f172a] border border-[#f1f5f9] dark:border-[#1e293b] rounded-[14px] shadow-2xs overflow-hidden grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-[#f1f5f9] dark:divide-[#334155]">
        <a href="{{ route('battery.products') }}" wire:navigate class="p-3.5 px-4 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-[#1e293b] transition-colors">
            <div class="flex items-center gap-3">
                <div class="w-[42px] h-[42px] rounded-full bg-[#e0e7ff] dark:bg-indigo-950/60 text-[#4f46e5] dark:text-indigo-400 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-boxes-stacked text-sm"></i>
                </div>
                <div>
                    <span class="text-[12px] font-semibold text-[#64748b] dark:text-slate-400 block">Total Products</span>
                    <h4 class="text-[18px] font-bold text-[#1e293b] dark:text-white m-0">{{ $e['products'] }}</h4>
                </div>
            </div>
            <i class="fa-solid fa-chevron-right text-xs text-[#cbd5e1] dark:text-slate-600"></i>
        </a>

        <a href="{{ route('battery.customers') }}" wire:navigate class="p-3.5 px-4 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-[#1e293b] transition-colors">
            <div class="flex items-center gap-3">
                <div class="w-[42px] h-[42px] rounded-full bg-[#dcfce7] dark:bg-emerald-950/60 text-[#16a34a] dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-users text-sm"></i>
                </div>
                <div>
                    <span class="text-[12px] font-semibold text-[#64748b] dark:text-slate-400 block">Total Customers</span>
                    <h4 class="text-[18px] font-bold text-[#1e293b] dark:text-white m-0">{{ $e['customers'] }}</h4>
                </div>
            </div>
            <i class="fa-solid fa-chevron-right text-xs text-[#cbd5e1] dark:text-slate-600"></i>
        </a>

        <a href="{{ route('battery.suppliers') }}" wire:navigate class="p-3.5 px-4 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-[#1e293b] transition-colors">
            <div class="flex items-center gap-3">
                <div class="w-[42px] h-[42px] rounded-full bg-[#fef3c7] dark:bg-amber-950/60 text-[#d97706] dark:text-amber-400 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-truck-fast text-sm"></i>
                </div>
                <div>
                    <span class="text-[12px] font-semibold text-[#64748b] dark:text-slate-400 block">Total Suppliers</span>
                    <h4 class="text-[18px] font-bold text-[#1e293b] dark:text-white m-0">{{ $e['suppliers'] }}</h4>
                </div>
            </div>
            <i class="fa-solid fa-chevron-right text-xs text-[#cbd5e1] dark:text-slate-600"></i>
        </a>
    </div>

    {{-- Row 4: Charts Row --}}
    <div
        wire:key="battery-charts-{{ md5(json_encode($chartPayload)) }}"
        x-data="{
            payload: @js($chartPayload),
            areaChart: null,
            donutChart: null,
            renderCharts() {
                if (!window.ApexCharts) return;
                const dark = document.documentElement.classList.contains('dark');
                const fg = dark ? '#cbd5e1' : '#475569';
                const grid = dark ? '#1e293b' : '#e2e8f0';
                if (this.areaChart) this.areaChart.destroy();
                const series = [{ name: 'Battery Sales (৳)', data: this.payload.sales }];
                if (this.payload.includeProfit) series.push({ name: 'Net Profit (৳)', data: this.payload.profit });
                this.areaChart = new window.ApexCharts(this.$refs.areaEl, {
                    chart: { type: 'area', height: 270, toolbar: { show: false }, fontFamily: 'Valley Sans, Baloo Da 2, sans-serif', background: 'transparent' },
                    colors: ['#059669', '#0ea5e9'],
                    dataLabels: { enabled: false },
                    stroke: { curve: 'smooth', width: 2.5 },
                    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.05 } },
                    series,
                    xaxis: { categories: this.payload.labels, labels: { style: { colors: fg, fontSize: '11px' } } },
                    yaxis: { labels: { style: { colors: fg, fontSize: '11px' }, formatter: (v) => '৳' + Math.round(v).toLocaleString() } },
                    grid: { borderColor: grid, strokeDashArray: 4 },
                    tooltip: { theme: dark ? 'dark' : 'light' },
                    legend: { labels: { colors: fg } }
                });
                this.areaChart.render();
                if (this.donutChart) this.donutChart.destroy();
                const dVals = [this.payload.cash, this.payload.expense, this.payload.supplierPay, this.payload.due];
                const hasData = dVals.some(v => v > 0);
                this.donutChart = new window.ApexCharts(this.$refs.donutEl, {
                    chart: { type: 'donut', height: 270, fontFamily: 'Valley Sans, Baloo Da 2, sans-serif', background: 'transparent' },
                    labels: ['Cash Collection', 'Expenses', 'Supplier Paid', 'Customer Due'],
                    colors: ['#16a34a', '#e11d48', '#0284c7', '#f59e0b'],
                    series: hasData ? dVals : [1],
                    dataLabels: { enabled: hasData },
                    legend: { position: 'bottom', labels: { colors: fg } },
                    tooltip: { theme: dark ? 'dark' : 'light' }
                });
                this.donutChart.render();
            },
            init() {
                this.$nextTick(() => this.renderCharts());
                window.addEventListener('marss-theme-changed', () => this.renderCharts());
            }
        }"
        class="grid grid-cols-1 lg:grid-cols-12 gap-3"
    >
        <div class="lg:col-span-8 bg-white dark:bg-[#0f172a] border border-[#f1f5f9] dark:border-[#1e293b] rounded-[14px] p-4 shadow-2xs">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <h2 class="text-[15px] font-bold text-[#1e293b] dark:text-white m-0">{{ $metrics['filter']['chartTitle'] }}</h2>
                    <span class="text-xs text-[#64748b] dark:text-slate-400">Performance chart for the selected date range</span>
                </div>
                <span class="px-3 py-1 rounded-full bg-[#f8fafc] dark:bg-slate-800 border border-[#e2e8f0] dark:border-slate-700 text-[#475569] dark:text-slate-300 text-xs font-semibold">
                    {{ $currentFilterLabel }}
                </span>
            </div>
            <div x-ref="areaEl" class="w-full min-h-[270px]"></div>
        </div>
        <div class="lg:col-span-4 bg-white dark:bg-[#0f172a] border border-[#f1f5f9] dark:border-[#1e293b] rounded-[14px] p-4 shadow-2xs flex flex-col">
            <div class="mb-2">
                <h2 class="text-[15px] font-bold text-[#1e293b] dark:text-white m-0">Financial Distribution</h2>
                <span class="text-xs text-[#64748b] dark:text-slate-400">Proportion of key metrics</span>
            </div>
            <div x-ref="donutEl" class="w-full min-h-[270px] flex-1 flex items-center justify-center"></div>
        </div>
    </div>

    {{-- Row 5: This Month's Financial Summary --}}
    <div class="bg-white dark:bg-[#0f172a] border border-[#f1f5f9] dark:border-[#1e293b] rounded-[14px] p-4 shadow-2xs">
        <div class="flex items-center gap-2 mb-3">
            <div class="w-[34px] h-[34px] rounded-[9px] bg-[#ede9fe] dark:bg-purple-950/60 text-[#7c3aed] dark:text-purple-400 flex items-center justify-center">
                <i class="fa-solid fa-calendar-check text-sm"></i>
            </div>
            <div>
                <h6 class="text-[14.5px] font-bold text-[#1e293b] dark:text-white m-0">This Month's Financial Summary</h6>
                <span class="text-[11.5px] text-[#64748b] dark:text-slate-400">Running current month performance ({{ now()->format('F Y') }})</span>
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3">
            <div class="p-3 rounded-[12px] bg-[#f0fdf4] dark:bg-emerald-950/30 border border-[#bbf7d0] dark:border-emerald-900">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-[11px] font-bold uppercase text-[#15803d] dark:text-emerald-300">Monthly Sales</span>
                    <i class="fa-solid fa-cart-shopping text-[#16a34a]"></i>
                </div>
                <h4 class="text-[19px] font-extrabold text-[#14532d] dark:text-emerald-200 m-0 tabular-nums">{{ Money::formatBd($m['sales_amount']) }}</h4>
                <span class="text-[11px] text-[#16a34a] font-semibold">This month's revenue</span>
            </div>
            <div class="p-3 rounded-[12px] bg-[#f0f9ff] dark:bg-sky-950/30 border border-[#bae6fd] dark:border-sky-900">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-[11px] font-bold uppercase text-[#0369a1] dark:text-sky-300">Cash Collected</span>
                    <i class="fa-solid fa-hand-holding-dollar text-[#0284c7]"></i>
                </div>
                <h4 class="text-[19px] font-extrabold text-[#0c4a6e] dark:text-sky-200 m-0 tabular-nums">{{ Money::formatBd($m['cash_collection']) }}</h4>
                <span class="text-[11px] text-[#0284c7] font-semibold">Received in hand this month</span>
            </div>
            <div class="p-3 rounded-[12px] bg-[#fffbeb] dark:bg-amber-950/30 border border-[#fde68a] dark:border-amber-900">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-[11px] font-bold uppercase text-[#b45309] dark:text-amber-300">Monthly Purchases</span>
                    <i class="fa-solid fa-truck-ramp-box text-[#d97706]"></i>
                </div>
                <h4 class="text-[19px] font-extrabold text-[#78350f] dark:text-amber-200 m-0 tabular-nums">{{ Money::formatBd($m['purchase_amount']) }}</h4>
                <span class="text-[11px] text-[#d97706] font-semibold">Stock purchased this month</span>
            </div>
            <div class="p-3 rounded-[12px] bg-[#fff1f2] dark:bg-rose-950/30 border border-[#fecaca] dark:border-rose-900">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-[11px] font-bold uppercase text-[#be123c] dark:text-rose-300">Monthly Expenses</span>
                    <i class="fa-solid fa-receipt text-[#e11d48]"></i>
                </div>
                <h4 class="text-[19px] font-extrabold text-[#881337] dark:text-rose-200 m-0 tabular-nums">{{ Money::formatBd($m['expense']) }}</h4>
                <span class="text-[11px] text-[#e11d48] font-semibold">Operating costs this month</span>
            </div>
        </div>
    </div>

    {{-- Row 6: 4 Top-Bordered (3.5px solid) Receivable, Payable & Stock Valuation Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3">
        <a href="{{ route('battery.customer.due') }}" wire:navigate class="bg-white dark:bg-[#0f172a] border border-[#f1f5f9] dark:border-[#1e293b] rounded-[14px] p-3.5 shadow-2xs hover:shadow-md transition-all block" style="border-top: 3.5px solid #ef4444;">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold uppercase text-[#64748b] dark:text-slate-400 m-0 mb-1">Total Customer Outstanding (Receivable)</p>
                    <h4 class="text-[20px] font-extrabold text-[#dc2626] dark:text-rose-400 m-0 tabular-nums">{{ Money::formatBd($f['customer_due']) }}</h4>
                    <span class="text-[11px] text-[#94a3b8]">All-time customer due balance &rarr;</span>
                </div>
                <div class="w-[40px] h-[40px] rounded-[10px] bg-[#fee2e2] dark:bg-rose-950/60 text-[#dc2626] dark:text-rose-400 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                </div>
            </div>
        </a>

        <a href="{{ route('battery.supplier.due') }}" wire:navigate class="bg-white dark:bg-[#0f172a] border border-[#f1f5f9] dark:border-[#1e293b] rounded-[14px] p-3.5 shadow-2xs hover:shadow-md transition-all block" style="border-top: 3.5px solid #f97316;">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold uppercase text-[#64748b] dark:text-slate-400 m-0 mb-1">Total Supplier Payable (Due)</p>
                    <h4 class="text-[20px] font-extrabold text-[#ea580c] dark:text-orange-400 m-0 tabular-nums">{{ Money::formatBd($f['supplier_payable']) }}</h4>
                    <span class="text-[11px] text-[#94a3b8]">All-time supplier payable balance &rarr;</span>
                </div>
                <div class="w-[40px] h-[40px] rounded-[10px] bg-[#ffedd5] dark:bg-orange-950/60 text-[#ea580c] dark:text-orange-400 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-boxes-packing"></i>
                </div>
            </div>
        </a>

        <a href="{{ route('battery.stock-overview') }}" wire:navigate class="bg-white dark:bg-[#0f172a] border border-[#f1f5f9] dark:border-[#1e293b] rounded-[14px] p-3.5 shadow-2xs hover:shadow-md transition-all block" style="border-top: 3.5px solid #0d9488;">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold uppercase text-[#64748b] dark:text-slate-400 m-0 mb-1">Total Stock Cost Value (Purchase)</p>
                    <h4 class="text-[20px] font-extrabold text-[#0f766e] dark:text-teal-400 m-0 tabular-nums">{{ Money::formatBd($f['cost_stock_value']) }}</h4>
                    <span class="text-[11px] text-[#94a3b8]">Current inventory at cost price &rarr;</span>
                </div>
                <div class="w-[40px] h-[40px] rounded-[10px] bg-[#ccfbf1] dark:bg-teal-950/60 text-[#0d9488] dark:text-teal-400 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-warehouse"></i>
                </div>
            </div>
        </a>

        <a href="{{ route('battery.stock-overview') }}" wire:navigate class="bg-white dark:bg-[#0f172a] border border-[#f1f5f9] dark:border-[#1e293b] rounded-[14px] p-3.5 shadow-2xs hover:shadow-md transition-all block" style="border-top: 3.5px solid #059669;">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold uppercase text-[#64748b] dark:text-slate-400 m-0 mb-1">Total Stock Selling Value (Est. Sale)</p>
                    <h4 class="text-[20px] font-extrabold text-[#059669] dark:text-emerald-400 m-0 tabular-nums">{{ Money::formatBd($f['sell_stock_value']) }}</h4>
                    <span class="text-[11px] text-[#94a3b8]">Current inventory at sell price &rarr;</span>
                </div>
                <div class="w-[40px] h-[40px] rounded-[10px] bg-[#d1fae5] dark:bg-emerald-950/60 text-[#059669] dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-coins"></i>
                </div>
            </div>
        </a>
    </div>

    {{-- Row 7: Quick Actions & Shortcuts --}}
    <div class="bg-white dark:bg-[#0f172a] border border-[#f1f5f9] dark:border-[#1e293b] rounded-[14px] p-3.5 shadow-2xs">
        <div class="flex items-center justify-between mb-2.5">
            <span class="text-[12px] font-bold uppercase tracking-wider text-[#64748b] dark:text-slate-400">
                <i class="fa-solid fa-bolt text-amber-500 mr-1"></i> Quick Actions &amp; Shortcuts
            </span>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2">
            <a href="{{ route('battery.pos') }}" wire:navigate class="p-2.5 rounded-[10px] bg-[#f0fdf4] dark:bg-emerald-950/40 hover:bg-emerald-100/70 border border-[#bbf7d0] dark:border-emerald-900 flex flex-col items-center justify-center gap-1.5 text-center transition-all hover:-translate-y-0.5">
                <div class="w-[36px] h-[36px] rounded-full bg-[#16a34a] text-white flex items-center justify-center"><i class="fa-solid fa-cash-register text-sm"></i></div>
                <span class="text-[12px] font-bold text-[#14532d] dark:text-emerald-300">POS Sale</span>
            </a>
            <a href="{{ route('battery.purchases') }}" wire:navigate class="p-2.5 rounded-[10px] bg-[#eff6ff] dark:bg-blue-950/40 hover:bg-blue-100/70 border border-[#bfdbfe] dark:border-blue-900 flex flex-col items-center justify-center gap-1.5 text-center transition-all hover:-translate-y-0.5">
                <div class="w-[36px] h-[36px] rounded-full bg-[#2563eb] text-white flex items-center justify-center"><i class="fa-solid fa-cart-plus text-sm"></i></div>
                <span class="text-[12px] font-bold text-[#1e3a8a] dark:text-blue-300">New Purchase</span>
            </a>
            <a href="{{ route('battery.invoices') }}" wire:navigate class="p-2.5 rounded-[10px] bg-[#f5f3ff] dark:bg-purple-950/40 hover:bg-purple-100/70 border border-[#ddd6fe] dark:border-purple-900 flex flex-col items-center justify-center gap-1.5 text-center transition-all hover:-translate-y-0.5">
                <div class="w-[36px] h-[36px] rounded-full bg-[#7c3aed] text-white flex items-center justify-center"><i class="fa-solid fa-file-invoice text-sm"></i></div>
                <span class="text-[12px] font-bold text-[#4c1d95] dark:text-purple-300">Invoices</span>
            </a>
            <a href="{{ route('battery.customer.due') }}" wire:navigate class="p-2.5 rounded-[10px] bg-[#fffbeb] dark:bg-amber-950/40 hover:bg-amber-100/70 border border-[#fde68a] dark:border-amber-900 flex flex-col items-center justify-center gap-1.5 text-center transition-all hover:-translate-y-0.5">
                <div class="w-[36px] h-[36px] rounded-full bg-[#d97706] text-white flex items-center justify-center"><i class="fa-solid fa-hand-holding-dollar text-sm"></i></div>
                <span class="text-[12px] font-bold text-[#78350f] dark:text-amber-300">Customer Due</span>
            </a>
            <a href="{{ route('battery.expenses') }}" wire:navigate class="p-2.5 rounded-[10px] bg-[#fff1f2] dark:bg-rose-950/40 hover:bg-rose-100/70 border border-[#fecaca] dark:border-rose-900 flex flex-col items-center justify-center gap-1.5 text-center transition-all hover:-translate-y-0.5">
                <div class="w-[36px] h-[36px] rounded-full bg-[#e11d48] text-white flex items-center justify-center"><i class="fa-solid fa-wallet text-sm"></i></div>
                <span class="text-[12px] font-bold text-[#881337] dark:text-rose-300">Add Expense</span>
            </a>
            <a href="{{ route('battery.reports.daily-ledger') }}" wire:navigate class="p-2.5 rounded-[10px] bg-[#f0fdfa] dark:bg-teal-950/40 hover:bg-teal-100/70 border border-[#99f6e4] dark:border-teal-900 flex flex-col items-center justify-center gap-1.5 text-center transition-all hover:-translate-y-0.5">
                <div class="w-[36px] h-[36px] rounded-full bg-[#0d9488] text-white flex items-center justify-center"><i class="fa-solid fa-book-bookmark text-sm"></i></div>
                <span class="text-[12px] font-bold text-[#134e4a] dark:text-teal-300">Daily Ledger</span>
            </a>
        </div>
    </div>

    {{-- Row 8: Low Stock Alert & Recent Sales Invoices (50% / 50% Exact Reference Layout) --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- Left (50%): Low Stock Alert --}}
        <div class="bg-white dark:bg-[#0f172a] border border-[#f1f5f9] dark:border-[#1e293b] rounded-[14px] p-4 shadow-2xs flex flex-col">
            <div class="flex items-center justify-between gap-2 mb-3">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation text-[#ef4444] text-sm"></i>
                    <h6 class="text-[15px] font-bold text-[#1e293b] dark:text-white m-0">Low Stock Alert</h6>
                </div>
                <a
                    href="{{ route('battery.reports.stock-out') }}"
                    wire:navigate
                    class="px-3.5 py-1.5 rounded-full bg-white dark:bg-rose-950/40 hover:bg-[#fef2f2] dark:hover:bg-rose-950/70 text-[#dc2626] dark:text-rose-300 border border-[#f87171] dark:border-rose-700 text-[12px] font-semibold transition-colors whitespace-nowrap"
                >
                    View All Stock
                </a>
            </div>
            <div class="overflow-x-auto max-h-[340px]">
                <table class="w-full text-[13px] text-left border-collapse">
                    <thead class="sticky top-0 z-10 bg-[#f8fafc] dark:bg-slate-800 text-[#475569] dark:text-slate-300 text-[12px] font-semibold border-b border-slate-100 dark:border-slate-700">
                        <tr>
                            <th class="py-2.5 px-3">Product Name</th>
                            <th class="py-2.5 px-3 text-center">Code</th>
                            <th class="py-2.5 px-3 text-center">Stock</th>
                            <th class="py-2.5 px-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($metrics['low_stock_products'] ?? [] as $p)
                            @php
                                $q = (int) round((float) ($p->quantity ?? 0));
                                $pCode = $p->primaryCode();
                            @endphp
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="py-2.5 px-3 font-bold text-[#1e293b] dark:text-white">{{ $p->product_name }}</td>
                                <td class="py-2.5 px-3 text-center">
                                    <span class="inline-block px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-mono text-[11px] font-semibold max-w-[140px] truncate" title="{{ $pCode ?: 'N/A' }}">
                                        {{ $pCode ?: 'N/A' }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-[11px] font-bold text-white {{ $q <= 0 ? 'bg-[#ef4444]' : 'bg-[#f59e0b]' }}">
                                        {{ $q }} pcs
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 text-right whitespace-nowrap">
                                    <a
                                        href="{{ route('battery.purchases') }}"
                                        wire:navigate
                                        class="inline-flex items-center justify-center gap-1.5 px-3 py-1 rounded-full bg-white dark:bg-emerald-950/30 hover:bg-[#15803d] text-[#15803d] dark:text-emerald-300 hover:text-white border border-[#86efac] dark:border-emerald-700 text-[11.5px] font-bold transition-colors"
                                    >
                                        <i class="fa-solid fa-cart-plus text-[11px]"></i>
                                        <span>Reorder</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr data-empty-row="true"><td colspan="4" class="py-8 text-center text-slate-400">All products are sufficiently stocked.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Right (50%): Recent Sales Invoices --}}
        <div class="bg-white dark:bg-[#0f172a] border border-[#f1f5f9] dark:border-[#1e293b] rounded-[14px] p-4 shadow-2xs flex flex-col">
            <div class="flex items-center justify-between gap-2 mb-3">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-[#334155] dark:text-slate-300 text-sm"></i>
                    <h6 class="text-[15px] font-bold text-[#1e293b] dark:text-white m-0">Recent Sales Invoices</h6>
                </div>
                <a
                    href="{{ route('battery.invoices') }}"
                    wire:navigate
                    class="px-3.5 py-1.5 rounded-full bg-white dark:bg-indigo-950/40 hover:bg-[#eef2ff] dark:hover:bg-indigo-950/70 text-[#4338ca] dark:text-indigo-300 border border-[#a5b4fc] dark:border-indigo-700 text-[12px] font-semibold transition-colors whitespace-nowrap"
                >
                    All Invoices
                </a>
            </div>
            <div class="overflow-x-auto max-h-[340px]">
                <table class="w-full text-[13px] text-left border-collapse">
                    <thead class="sticky top-0 z-10 bg-[#f8fafc] dark:bg-slate-800 text-[#475569] dark:text-slate-300 text-[12px] font-semibold border-b border-slate-100 dark:border-slate-700">
                        <tr>
                            <th class="py-2.5 px-3">Invoice No</th>
                            <th class="py-2.5 px-3">Customer</th>
                            <th class="py-2.5 px-3 text-right">Amount</th>
                            <th class="py-2.5 px-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($metrics['recent_sales'] ?? [] as $ord)
                            @php
                                $ordDue = (float) ($ord->due_amount ?? 0);
                                $ordPaid = (float) ($ord->paid_amount ?? 0);
                                $statusText = $ordDue <= 0.009 ? 'Paid' : ($ordPaid > 0.009 ? 'Partial' : 'Due');
                                $statusBadge = $statusText === 'Paid'
                                    ? 'bg-[#22c55e] text-white'
                                    : ($statusText === 'Partial' ? 'bg-[#eab308] text-[#1e293b]' : 'bg-[#ef4444] text-white');
                            @endphp
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="py-2.5 px-3 font-bold text-[#16a34a] dark:text-emerald-400 whitespace-nowrap">
                                    <a href="{{ route('battery.invoice.show', $ord->id) }}" target="_blank" class="hover:underline">{{ $ord->order_no }}</a>
                                </td>
                                <td class="py-2.5 px-3 font-medium text-[#334155] dark:text-slate-200">{{ $ord->customer?->customer_name ?? 'Walk-in' }}</td>
                                <td class="py-2.5 px-3 text-right font-extrabold text-[#1e293b] dark:text-white tabular-nums whitespace-nowrap">{{ Money::formatBd($ord->sub_total) }}</td>
                                <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $statusBadge }}">
                                        {{ $statusText }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr data-empty-row="true"><td colspan="4" class="py-8 text-center text-slate-400">No recent invoices.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
