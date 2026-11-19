@php use App\Support\Money; @endphp

<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Customer Invoice Report</h1>
            <p class="text-xs sm:text-sm text-slate-500">Customer-wise Retail invoice statement with date filtering.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <select wire:model.live="customerId" class="h-[38px] px-3 w-56 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold">
                <option value="">All Customers</option>
                @foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->customer_name }}</option>@endforeach
            </select>
            <div class="w-32"><x-ui.date-picker wire:model.live="startDate" placeholder="From" /></div>
            <div class="w-32"><x-ui.date-picker wire:model.live="endDate" placeholder="To" /></div>
            <x-ui.export-buttons table-id="custInvRepTable" filename="customer-invoice-report" title="MARSS Customer Invoice Report" />
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table id="custInvRepTable" class="w-full text-xs sm:text-sm text-left">
                <thead class="bg-[#15803d] text-white text-xs uppercase whitespace-nowrap">
                    <tr><th class="px-3 py-3">Date</th><th class="px-3 py-3">Invoice #</th><th class="px-3 py-3">Customer</th><th class="px-3 py-3 text-right">SubTotal</th><th class="px-3 py-3 text-right">Paid</th><th class="px-3 py-3 text-right">Due</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($orders as $o)
                        <tr>
                            <td class="px-3 py-2 text-slate-500">{{ \Carbon\Carbon::parse($o->invoice_date ?? $o->created_at)->format('d-m-Y') }}</td>
                            <td class="px-3 py-2 font-bold"><a href="{{ route('retail.invoice.show', $o->id) }}" target="_blank" class="text-emerald-700 hover:underline">{{ $o->order_no }}</a></td>
                            <td class="px-3 py-2">{{ $o->customer?->customer_name ?? '—' }}</td>
                            <td class="px-3 py-2 text-right tabular-nums">{{ Money::formatBd($o->sub_total) }}</td>
                            <td class="px-3 py-2 text-right text-emerald-700 font-bold tabular-nums">{{ Money::formatBd($o->paid_amount) }}</td>
                            <td class="px-3 py-2 text-right text-rose-600 font-bold tabular-nums">{{ Money::formatBd($o->due_amount) }}</td>
                        </tr>
                    @empty
                        <tr data-empty-row="true"><td colspan="6" class="px-4 py-8 text-center text-slate-500">No invoices matching criteria.</td></tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-100 dark:bg-slate-800 font-extrabold">
                    <tr>
                        <th colspan="3" class="px-3 py-3 text-right">Totals:</th>
                        <th class="px-3 py-3 text-right tabular-nums">{{ Money::formatBd($totalSales) }}</th>
                        <th class="px-3 py-3 text-right text-emerald-700 tabular-nums">{{ Money::formatBd($totalPaid) }}</th>
                        <th class="px-3 py-3 text-right text-rose-600 tabular-nums">{{ Money::formatBd($totalDue) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
