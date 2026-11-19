@php use App\Support\Money; @endphp

<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Staff Salary Disbursement Profile</h1>
            <p class="text-xs sm:text-sm text-slate-500">Filter and audit Retail salary payments recorded per staff member.</p>
        </div>
        <div class="px-4 py-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-sm font-extrabold text-emerald-700 dark:text-emerald-300">
            Total Disbursed: {{ Money::formatBd($totalSalary) }}
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 overflow-hidden">
        <div class="p-3.5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between gap-2">
            <select wire:model.live="staffId" class="h-9 px-3 w-64 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-semibold">
                <option value="">All Staff Members</option>
                @foreach($staff as $s)<option value="{{ $s->id }}">{{ $s->name }} ({{ $s->role }})</option>@endforeach
            </select>
            <x-ui.export-buttons table-id="staffSalaryTable" filename="staff-salary-profile" title="MARSS Staff Salary History" />
        </div>
        <div class="overflow-x-auto">
            <table id="staffSalaryTable" class="w-full text-xs sm:text-sm text-left">
                <thead class="bg-[#15803d] text-white text-xs uppercase whitespace-nowrap">
                    <tr><th class="px-3 py-3">Date</th><th class="px-3 py-3">Staff Member</th><th class="px-3 py-3">Category</th><th class="px-3 py-3">Details</th><th class="px-3 py-3 text-right">Amount</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($expenses as $e)
                        <tr>
                            <td class="px-3 py-2.5 text-slate-500">{{ $e->date }}</td>
                            <td class="px-3 py-2.5 font-bold">{{ $e->staff?->name ?? '—' }}</td>
                            <td class="px-3 py-2.5">{{ $e->expenseType?->type_name ?? 'Salary' }}</td>
                            <td class="px-3 py-2.5 text-slate-600 dark:text-slate-300">{{ $e->expense_details ?: '—' }}</td>
                            <td class="px-3 py-2.5 text-right font-extrabold text-emerald-700 tabular-nums">{{ Money::formatBd($e->expense_amount) }}</td>
                        </tr>
                    @empty
                        <tr data-empty-row="true"><td colspan="5" class="px-4 py-8 text-center text-slate-500">No staff salary records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
