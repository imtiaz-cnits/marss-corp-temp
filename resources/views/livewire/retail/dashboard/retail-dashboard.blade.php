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
    {{-- Page Header & Date Filter Bar --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 p-4 shadow-xs flex flex-col xl:flex-row xl:items-center xl:justify-between gap-3">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 text-[11px] font-extrabold uppercase">Retail Shop</span>
                <h1 class="text-lg sm:text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">Executive Business Overview</h1>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Real-time isolated Retail financials, stock valuation &amp; daily trend analytics.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-1.5">
            @foreach(['today' => 'Today', 'last_week' => '7 Days', 'this_month' => 'This Month', 'last_month' => '30 Days', 'all_time' => 'All Time'] as $key => $label)
                <button
                    type="button"
                    wire:click="setFilter('{{ $key }}')"
                    class="h-9 px-3 rounded-xl text-xs font-bold transition-all cursor-pointer {{ $filterType === $key ? 'bg-emerald-700 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-emerald-50 dark:hover:bg-slate-700' }}"
                >
                    {{ $label }}
                </button>
            @endforeach

            <div class="flex items-center gap-1.5 ml-0 sm:ml-1">
                <div class="w-32"><x-ui.date-picker wire:model="startDate" placeholder="Start" /></div>
                <div class="w-32"><x-ui.date-picker wire:model="endDate" placeholder="End" /></div>
                <button
                    type="button"
                    wire:click="applyDateRange"
                    class="h-[38px] px-3.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold cursor-pointer"
                >
                    <i class="fa-solid fa-filter mr-1"></i> Filter
                </button>
            </div>
        </div>
    </div>

    {{-- Row 1: Primary 4 KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3">
        <div class="rounded-2xl bg-gradient-to-br from-emerald-700 to-emerald-600 text-white p-4 shadow-md">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-100">Total Sales</span>
                <span class="w-9 h-9 rounded-xl bg-white/15 flex items-center justify-center"><i class="fa-solid fa-bag-shopping"></i></span>
            </div>
            <p class="text-2xl font-extrabold mt-2 tabular-nums">{{ Money::formatBd($b['sales_amount']) }}</p>
            <p class="text-xs text-emerald-100 mt-1">{{ $b['sales_count'] }} invoice(s) • Disc: {{ Money::formatBd($b['discount_amount']) }}</p>
        </div>

        <div class="rounded-2xl bg-gradient-to-br from-sky-700 to-sky-600 text-white p-4 shadow-md">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-sky-100">Cash Collection</span>
                <span class="w-9 h-9 rounded-xl bg-white/15 flex items-center justify-center"><i class="fa-solid fa-sack-dollar"></i></span>
            </div>
            <p class="text-2xl font-extrabold mt-2 tabular-nums">{{ Money::formatBd($b['cash_collection']) }}</p>
            <p class="text-xs text-sky-100 mt-1">Invoice + Previous Due Payments</p>
        </div>

        <div class="rounded-2xl bg-gradient-to-br from-rose-700 to-rose-600 text-white p-4 shadow-md">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-rose-100">Operating Expense</span>
                <span class="w-9 h-9 rounded-xl bg-white/15 flex items-center justify-center"><i class="fa-solid fa-wallet"></i></span>
            </div>
            <p class="text-2xl font-extrabold mt-2 tabular-nums">{{ Money::formatBd($b['expense']) }}</p>
            <p class="text-xs text-rose-100 mt-1">Supplier Paid: {{ Money::formatBd($b['supplier_payment']) }}</p>
        </div>

        @if($canViewProfit)
            <div class="rounded-2xl bg-gradient-to-br from-teal-700 to-emerald-600 text-white p-4 shadow-md">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-teal-100">Net Profit (Admin)</span>
                    <span class="w-9 h-9 rounded-xl bg-white/15 flex items-center justify-center"><i class="fa-solid fa-chart-line"></i></span>
                </div>
                <p class="text-2xl font-extrabold mt-2 tabular-nums">{{ Money::formatBd($b['net_profit']) }}</p>
                <p class="text-xs text-teal-100 mt-1">Gross Margin: {{ Money::formatBd($b['gross_profit']) }}</p>
            </div>
        @else
            <div class="rounded-2xl bg-gradient-to-br from-amber-600 to-amber-500 text-white p-4 shadow-md">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-100">Period Customer Due</span>
                    <span class="w-9 h-9 rounded-xl bg-white/15 flex items-center justify-center"><i class="fa-solid fa-file-invoice-dollar"></i></span>
                </div>
                <p class="text-2xl font-extrabold mt-2 tabular-nums">{{ Money::formatBd($b['customer_due']) }}</p>
                <p class="text-xs text-amber-100 mt-1">Unpaid Invoices in Period</p>
            </div>
        @endif
    </div>

    {{-- Row 2: Secondary KPIs & Entity Totals --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 p-3.5 shadow-xs">
            <p class="text-[11px] font-bold uppercase text-slate-500">Purchase Amount</p>
            <p class="text-lg font-extrabold text-slate-900 dark:text-white mt-1 tabular-nums">{{ Money::formatBd($b['purchase_amount']) }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 p-3.5 shadow-xs">
            <p class="text-[11px] font-bold uppercase text-slate-500">Supplier Payment</p>
            <p class="text-lg font-extrabold text-sky-700 dark:text-sky-400 mt-1 tabular-nums">{{ Money::formatBd($b['supplier_payment']) }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 p-3.5 shadow-xs">
            <p class="text-[11px] font-bold uppercase text-slate-500">Sales Return</p>
            <p class="text-lg font-extrabold text-amber-600 dark:text-amber-400 mt-1 tabular-nums">{{ Money::formatBd($b['return_amount']) }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 p-3.5 shadow-xs">
            <p class="text-[11px] font-bold uppercase text-slate-500">Total Customer Due</p>
            <p class="text-lg font-extrabold text-rose-600 dark:text-rose-400 mt-1 tabular-nums">{{ Money::formatBd($f['customer_due']) }}</p>
        </div>
        <div class="col-span-2 lg:col-span-1 bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 p-3.5 shadow-xs">
            <p class="text-[11px] font-bold uppercase text-slate-500">Supplier Payable</p>
            <p class="text-lg font-extrabold text-purple-700 dark:text-purple-400 mt-1 tabular-nums">{{ Money::formatBd($f['supplier_payable']) }}</p>
        </div>
    </div>

    {{-- Row 3: Charts (Sales & Profit Trend Area + Cash Flow Donut) --}}
    <div
        wire:key="retail-charts-{{ md5(json_encode($chartPayload)) }}"
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
                const series = [{ name: 'Sales (৳)', data: this.payload.sales }];
                if (this.payload.includeProfit) {
                    series.push({ name: 'Net Profit (৳)', data: this.payload.profit });
                }
                this.areaChart = new window.ApexCharts(this.$refs.areaEl, {
                    chart: { type: 'area', height: 270, toolbar: { show: false }, fontFamily: 'Valley Sans, Baloo Da 2, sans-serif', background: 'transparent' },
                    colors: ['#15803d', '#0ea5e9'],
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
        class="grid grid-cols-1 lg:grid-cols-3 gap-4"
    >
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 p-4 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-sm font-extrabold text-slate-800 dark:text-white">{{ $metrics['filter']['chartTitle'] }}</h2>
                <span class="text-xs text-slate-400">Grouped SQL Series</span>
            </div>
            <div x-ref="areaEl" class="w-full min-h-[270px]"></div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 p-4 shadow-xs">
            <h2 class="text-sm font-extrabold text-slate-800 dark:text-white mb-2">Financial Distribution</h2>
            <div x-ref="donutEl" class="w-full min-h-[270px]"></div>
        </div>
    </div>

    {{-- Row 4: Stock Valuation, Monthly Summary & Quick Shortcuts --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- Stock Valuation & Entities --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 p-4 shadow-xs space-y-3">
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Stock &amp; Entity Snapshot</h3>
            <div class="grid grid-cols-2 gap-2.5">
                <div class="p-3 rounded-xl bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900">
                    <p class="text-[11px] font-bold text-slate-500">Stock Value (Cost)</p>
                    <p class="text-sm font-extrabold text-emerald-700 dark:text-emerald-300 mt-0.5 tabular-nums">{{ Money::formatBd($f['cost_stock_value']) }}</p>
                </div>
                <div class="p-3 rounded-xl bg-sky-50/70 dark:bg-sky-950/30 border border-sky-200 dark:border-sky-900">
                    <p class="text-[11px] font-bold text-slate-500">Stock Value (Sell)</p>
                    <p class="text-sm font-extrabold text-sky-700 dark:text-sky-300 mt-0.5 tabular-nums">{{ Money::formatBd($f['sell_stock_value']) }}</p>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-2 text-center pt-1">
                <a href="{{ route('retail.products') }}" wire:navigate class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 hover:bg-emerald-50 dark:hover:bg-slate-700 transition-colors">
                    <p class="text-lg font-extrabold text-slate-900 dark:text-white">{{ $e['products'] }}</p>
                    <p class="text-[11px] font-semibold text-slate-500">Products</p>
                </a>
                <a href="{{ route('retail.customers') }}" wire:navigate class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 hover:bg-emerald-50 dark:hover:bg-slate-700 transition-colors">
                    <p class="text-lg font-extrabold text-slate-900 dark:text-white">{{ $e['customers'] }}</p>
                    <p class="text-[11px] font-semibold text-slate-500">Customers</p>
                </a>
                <a href="{{ route('retail.suppliers') }}" wire:navigate class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 hover:bg-emerald-50 dark:hover:bg-slate-700 transition-colors">
                    <p class="text-lg font-extrabold text-slate-900 dark:text-white">{{ $e['suppliers'] }}</p>
                    <p class="text-[11px] font-semibold text-slate-500">Suppliers</p>
                </a>
            </div>
        </div>

        {{-- This Month Summary --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 p-4 shadow-xs space-y-2.5">
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">This Month Summary ({{ now()->format('F Y') }})</h3>
            <div class="space-y-2 text-xs sm:text-sm">
                <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800"><span class="text-slate-500">Monthly Sales</span><span class="font-bold tabular-nums">{{ Money::formatBd($m['sales_amount']) }}</span></div>
                <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800"><span class="text-slate-500">Monthly Cash Collected</span><span class="font-bold text-emerald-700 dark:text-emerald-400 tabular-nums">{{ Money::formatBd($m['cash_collection']) }}</span></div>
                <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800"><span class="text-slate-500">Monthly Purchases</span><span class="font-bold tabular-nums">{{ Money::formatBd($m['purchase_amount']) }}</span></div>
                <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800"><span class="text-slate-500">Monthly Expenses</span><span class="font-bold text-rose-600 tabular-nums">{{ Money::formatBd($m['expense']) }}</span></div>
                @if($canViewProfit)
                    <div class="flex justify-between py-1"><span class="font-bold text-slate-700 dark:text-slate-200">Monthly Net Profit</span><span class="font-extrabold text-emerald-700 dark:text-emerald-400 tabular-nums">{{ Money::formatBd($m['net_profit']) }}</span></div>
                @endif
            </div>
        </div>

        {{-- 6 Quick Shortcuts --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 p-4 shadow-xs">
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-emerald-700 dark:text-emerald-400 mb-3">Quick Shortcuts</h3>
            <div class="grid grid-cols-2 gap-2">
                <a href="{{ route('retail.pos') }}" wire:navigate class="flex items-center gap-2 p-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold transition-colors"><i class="fa-solid fa-cash-register"></i> Open POS</a>
                <a href="{{ route('retail.purchases') }}" wire:navigate class="flex items-center gap-2 p-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-emerald-50 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold transition-colors"><i class="fa-solid fa-cart-shopping text-emerald-600"></i> Purchase</a>
                <a href="{{ route('retail.invoices') }}" wire:navigate class="flex items-center gap-2 p-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-emerald-50 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold transition-colors"><i class="fa-solid fa-file-invoice text-sky-600"></i> Invoices</a>
                <a href="{{ route('retail.customer.due') }}" wire:navigate class="flex items-center gap-2 p-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-emerald-50 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold transition-colors"><i class="fa-solid fa-hand-holding-dollar text-amber-600"></i> Customer Due</a>
                <a href="{{ route('retail.expenses') }}" wire:navigate class="flex items-center gap-2 p-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-emerald-50 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold transition-colors"><i class="fa-solid fa-wallet text-rose-600"></i> Expenses</a>
                <a href="{{ route('retail.reports.daily-ledger') }}" wire:navigate class="flex items-center gap-2 p-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-emerald-50 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold transition-colors"><i class="fa-solid fa-book-bookmark text-teal-600"></i> Daily Ledger</a>
            </div>
        </div>
    </div>

    {{-- Row 5: Low Stock Alert & Recent Sales Tables --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 shadow-xs overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <h3 class="text-sm font-extrabold text-slate-800 dark:text-white"><i class="fa-solid fa-triangle-exclamation text-red-500 mr-1.5"></i> Low Stock Alerts ({{ $f['low_stock_count'] }})</h3>
                <a href="{{ route('retail.reports.stock-out') }}" wire:navigate class="text-xs font-bold text-emerald-700 dark:text-emerald-400 hover:underline">View All &rarr;</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-[#15803d] text-white uppercase whitespace-nowrap">
                        <tr>
                            <th class="px-3 py-2.5">Product</th>
                            <th class="px-3 py-2.5">Brand</th>
                            <th class="px-3 py-2.5 text-right">Stock</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse($metrics['low_stock_products'] as $p)
                            @php $q = (int) round((float) ($p->quantity ?? 0)); @endphp
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                <td class="px-3 py-2 font-semibold text-slate-800 dark:text-white">{{ $p->product_name }}</td>
                                <td class="px-3 py-2 text-slate-500">{{ $p->brand?->name ?? '—' }}</td>
                                <td class="px-3 py-2 text-right">
                                    <span class="px-2 py-0.5 rounded-full font-bold {{ $q <= 0 ? 'bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-red-300' : 'bg-amber-100 text-amber-700' }}">{{ $q }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-3 py-6 text-center text-slate-500">No low-stock products.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 shadow-xs overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <h3 class="text-sm font-extrabold text-slate-800 dark:text-white"><i class="fa-solid fa-receipt text-emerald-600 mr-1.5"></i> Recent Retail Invoices</h3>
                <a href="{{ route('retail.invoices') }}" wire:navigate class="text-xs font-bold text-emerald-700 dark:text-emerald-400 hover:underline">All Invoices &rarr;</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-[#15803d] text-white uppercase whitespace-nowrap">
                        <tr>
                            <th class="px-3 py-2.5">Invoice</th>
                            <th class="px-3 py-2.5">Customer</th>
                            <th class="px-3 py-2.5 text-right">Total</th>
                            <th class="px-3 py-2.5 text-right">Due</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse($metrics['recent_sales'] as $o)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                <td class="px-3 py-2 font-bold">
                                    <a href="{{ route('retail.invoice.show', $o->id) }}" target="_blank" class="text-emerald-700 dark:text-emerald-400 hover:underline">{{ $o->order_no }}</a>
                                </td>
                                <td class="px-3 py-2 text-slate-700 dark:text-slate-300 truncate max-w-[140px]">{{ $o->customer?->customer_name ?? 'Walk-in' }}</td>
                                <td class="px-3 py-2 text-right font-semibold tabular-nums">{{ Money::formatBd($o->sub_total) }}</td>
                                <td class="px-3 py-2 text-right font-bold tabular-nums {{ (float) $o->due_amount > 0 ? 'text-rose-600' : 'text-emerald-600' }}">{{ Money::formatBd($o->due_amount) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-3 py-6 text-center text-slate-500">No invoices found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
