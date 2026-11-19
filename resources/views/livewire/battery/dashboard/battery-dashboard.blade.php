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
@endphp

<div class="space-y-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 p-4 shadow-xs flex flex-col xl:flex-row xl:items-center xl:justify-between gap-3">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 text-[11px] font-extrabold uppercase"><i class="fa-solid fa-car-battery mr-1"></i> Battery Division</span>
                <h1 class="text-lg sm:text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">Battery Executive Overview</h1>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">100% isolated Battery division accounting, inventory &amp; cash flow.</p>
        </div>

        <div class="flex flex-wrap items-center gap-1.5">
            @foreach(['today' => 'Today', 'last_week' => '7 Days', 'this_month' => 'This Month', 'last_month' => '30 Days', 'all_time' => 'All Time'] as $key => $label)
                <button type="button" wire:click="setFilter('{{ $key }}')" class="h-9 px-3 rounded-xl text-xs font-bold cursor-pointer {{ $filterType === $key ? 'bg-emerald-700 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300' }}">{{ $label }}</button>
            @endforeach
            <div class="flex items-center gap-1.5">
                <div class="w-32"><x-ui.date-picker wire:model="startDate" placeholder="Start" /></div>
                <div class="w-32"><x-ui.date-picker wire:model="endDate" placeholder="End" /></div>
                <button type="button" wire:click="applyDateRange" class="h-[38px] px-3.5 rounded-xl bg-emerald-700 text-white text-xs font-bold cursor-pointer">Filter</button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3">
        <div class="rounded-2xl bg-gradient-to-br from-emerald-700 to-emerald-600 text-white p-4 shadow-md">
            <div class="flex items-center justify-between"><span class="text-xs font-bold uppercase text-emerald-100">Battery Sales</span><i class="fa-solid fa-car-battery opacity-80"></i></div>
            <p class="text-2xl font-extrabold mt-2 tabular-nums">{{ Money::formatBd($b['sales_amount']) }}</p>
            <p class="text-xs text-emerald-100 mt-1">{{ $b['sales_count'] }} invoice(s) • Disc: {{ Money::formatBd($b['discount_amount']) }}</p>
        </div>
        <div class="rounded-2xl bg-gradient-to-br from-sky-700 to-sky-600 text-white p-4 shadow-md">
            <div class="flex items-center justify-between"><span class="text-xs font-bold uppercase text-sky-100">Cash Collection</span><i class="fa-solid fa-sack-dollar opacity-80"></i></div>
            <p class="text-2xl font-extrabold mt-2 tabular-nums">{{ Money::formatBd($b['cash_collection']) }}</p>
            <p class="text-xs text-sky-100 mt-1">Battery Invoice &amp; Due Payments</p>
        </div>
        <div class="rounded-2xl bg-gradient-to-br from-rose-700 to-rose-600 text-white p-4 shadow-md">
            <div class="flex items-center justify-between"><span class="text-xs font-bold uppercase text-rose-100">Battery Expense</span><i class="fa-solid fa-wallet opacity-80"></i></div>
            <p class="text-2xl font-extrabold mt-2 tabular-nums">{{ Money::formatBd($b['expense']) }}</p>
            <p class="text-xs text-rose-100 mt-1">Supplier Paid: {{ Money::formatBd($b['supplier_payment']) }}</p>
        </div>
        @if($canViewProfit)
            <div class="rounded-2xl bg-gradient-to-br from-teal-700 to-emerald-600 text-white p-4 shadow-md">
                <div class="flex items-center justify-between"><span class="text-xs font-bold uppercase text-teal-100">Net Profit (Admin)</span><i class="fa-solid fa-chart-line opacity-80"></i></div>
                <p class="text-2xl font-extrabold mt-2 tabular-nums">{{ Money::formatBd($b['net_profit']) }}</p>
                <p class="text-xs text-teal-100 mt-1">Gross Margin: {{ Money::formatBd($b['gross_profit']) }}</p>
            </div>
        @else
            <div class="rounded-2xl bg-gradient-to-br from-amber-600 to-amber-500 text-white p-4 shadow-md">
                <div class="flex items-center justify-between"><span class="text-xs font-bold uppercase text-amber-100">Period Customer Due</span><i class="fa-solid fa-file-invoice-dollar opacity-80"></i></div>
                <p class="text-2xl font-extrabold mt-2 tabular-nums">{{ Money::formatBd($b['customer_due']) }}</p>
                <p class="text-xs text-amber-100 mt-1">Unpaid Battery Invoices</p>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 p-3.5"><p class="text-[11px] font-bold uppercase text-slate-500">Battery Purchases</p><p class="text-lg font-extrabold mt-1 tabular-nums">{{ Money::formatBd($b['purchase_amount']) }}</p></div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 p-3.5"><p class="text-[11px] font-bold uppercase text-slate-500">Supplier Paid</p><p class="text-lg font-extrabold text-sky-700 mt-1 tabular-nums">{{ Money::formatBd($b['supplier_payment']) }}</p></div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 p-3.5"><p class="text-[11px] font-bold uppercase text-slate-500">Sales Return</p><p class="text-lg font-extrabold text-amber-600 mt-1 tabular-nums">{{ Money::formatBd($b['return_amount']) }}</p></div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 p-3.5"><p class="text-[11px] font-bold uppercase text-slate-500">Customer Due</p><p class="text-lg font-extrabold text-rose-600 mt-1 tabular-nums">{{ Money::formatBd($f['customer_due']) }}</p></div>
        <div class="col-span-2 lg:col-span-1 bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 p-3.5"><p class="text-[11px] font-bold uppercase text-slate-500">Supplier Payable</p><p class="text-lg font-extrabold text-purple-700 mt-1 tabular-nums">{{ Money::formatBd($f['supplier_payable']) }}</p></div>
    </div>

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
                    chart: { type: 'area', height: 260, toolbar: { show: false }, fontFamily: 'Valley Sans, Baloo Da 2, sans-serif', background: 'transparent' },
                    colors: ['#15803d', '#f59e0b'],
                    dataLabels: { enabled: false },
                    stroke: { curve: 'smooth', width: 2.5 },
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
                    chart: { type: 'donut', height: 260, fontFamily: 'Valley Sans, Baloo Da 2, sans-serif', background: 'transparent' },
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
        class="grid grid-cols-1 lg:grid-cols-3 gap-4"
    >
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 p-4">
            <h2 class="text-sm font-extrabold mb-2">{{ $metrics['filter']['chartTitle'] }}</h2>
            <div x-ref="areaEl" class="w-full min-h-[260px]"></div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 p-4">
            <h2 class="text-sm font-extrabold mb-2">Battery Cash Breakdown</h2>
            <div x-ref="donutEl" class="w-full min-h-[260px]"></div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 p-4 space-y-3">
            <h3 class="text-xs font-extrabold uppercase text-emerald-700">Battery Inventory Snapshot</h3>
            <div class="grid grid-cols-2 gap-2">
                <div class="p-3 rounded-xl bg-emerald-50/70 dark:bg-emerald-950/30"><p class="text-[11px] font-bold text-slate-500">Cost Value</p><p class="text-sm font-extrabold text-emerald-700 tabular-nums">{{ Money::formatBd($f['cost_stock_value']) }}</p></div>
                <div class="p-3 rounded-xl bg-sky-50/70 dark:bg-sky-950/30"><p class="text-[11px] font-bold text-slate-500">Sell Value</p><p class="text-sm font-extrabold text-sky-700 tabular-nums">{{ Money::formatBd($f['sell_stock_value']) }}</p></div>
            </div>
            <div class="grid grid-cols-3 gap-2 text-center">
                <a href="{{ route('battery.products') }}" wire:navigate class="p-2 rounded-xl bg-slate-50 dark:bg-slate-800"><p class="text-lg font-extrabold">{{ $e['products'] }}</p><p class="text-[11px] text-slate-500">Products</p></a>
                <a href="{{ route('battery.customers') }}" wire:navigate class="p-2 rounded-xl bg-slate-50 dark:bg-slate-800"><p class="text-lg font-extrabold">{{ $e['customers'] }}</p><p class="text-[11px] text-slate-500">Customers</p></a>
                <a href="{{ route('battery.suppliers') }}" wire:navigate class="p-2 rounded-xl bg-slate-50 dark:bg-slate-800"><p class="text-lg font-extrabold">{{ $e['suppliers'] }}</p><p class="text-[11px] text-slate-500">Suppliers</p></a>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 p-4 space-y-2 text-xs sm:text-sm">
            <h3 class="text-xs font-extrabold uppercase text-emerald-700">This Month ({{ now()->format('F Y') }})</h3>
            <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800"><span class="text-slate-500">Sales</span><span class="font-bold">{{ Money::formatBd($m['sales_amount']) }}</span></div>
            <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800"><span class="text-slate-500">Cash Collected</span><span class="font-bold text-emerald-700">{{ Money::formatBd($m['cash_collection']) }}</span></div>
            <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800"><span class="text-slate-500">Purchases</span><span class="font-bold">{{ Money::formatBd($m['purchase_amount']) }}</span></div>
            <div class="flex justify-between py-1"><span class="text-slate-500">Expenses</span><span class="font-bold text-rose-600">{{ Money::formatBd($m['expense']) }}</span></div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 p-4">
            <h3 class="text-xs font-extrabold uppercase text-emerald-700 mb-3">Battery Quick Actions</h3>
            <div class="grid grid-cols-2 gap-2">
                <a href="{{ route('battery.pos') }}" wire:navigate class="p-2.5 rounded-xl bg-emerald-700 text-white text-xs font-bold flex items-center gap-1.5"><i class="fa-solid fa-cash-register"></i> Battery POS</a>
                <a href="{{ route('battery.purchases') }}" wire:navigate class="p-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-xs font-bold flex items-center gap-1.5"><i class="fa-solid fa-cart-shopping text-emerald-600"></i> Purchase</a>
                <a href="{{ route('battery.invoices') }}" wire:navigate class="p-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-xs font-bold flex items-center gap-1.5"><i class="fa-solid fa-file-invoice text-sky-600"></i> Invoices</a>
                <a href="{{ route('battery.customer.due') }}" wire:navigate class="p-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-xs font-bold flex items-center gap-1.5"><i class="fa-solid fa-hand-holding-dollar text-amber-600"></i> Due List</a>
                <a href="{{ route('battery.expenses') }}" wire:navigate class="p-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-xs font-bold flex items-center gap-1.5"><i class="fa-solid fa-wallet text-rose-600"></i> Expenses</a>
                <a href="{{ route('battery.reports.daily-ledger') }}" wire:navigate class="p-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-xs font-bold flex items-center gap-1.5"><i class="fa-solid fa-book-bookmark text-teal-600"></i> Daily Ledger</a>
            </div>
        </div>
    </div>
</div>
