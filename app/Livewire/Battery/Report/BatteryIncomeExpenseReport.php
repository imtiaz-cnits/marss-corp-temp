<?php

namespace App\Livewire\Battery\Report;

use App\Livewire\Concerns\PaginatesCollections;
use App\Services\Battery\BatteryReportService;
use Carbon\Carbon;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Battery Income & Expense Report')]
class BatteryIncomeExpenseReport extends Component
{
    use PaginatesCollections, WithPagination;

    public string $startDate = '';

    public string $endDate = '';

    public int $perPage = 15;

    public function mount(): void
    {
        $this->startDate = Carbon::now()->startOfMonth()->toDateString();
        $this->endDate = Carbon::today()->toDateString();
    }

    public function updatedStartDate(): void
    {
        $this->resetPage();
    }

    public function updatedEndDate(): void
    {
        $this->resetPage();
    }

    public function render(BatteryReportService $reports)
    {
        $start = $this->startDate ?: Carbon::today()->toDateString();
        $end = $this->endDate ?: $start;
        $summary = $reports->incomeExpenseSummary($start, $end);
        $rows = $this->paginateItems($summary['rows'], $this->perPage);

        return view('livewire.battery.report.battery-income-expense-report', [
            'summary' => $summary,
            'rows' => $rows,
            'canViewProfit' => auth()->user()?->canViewProfit() ?? false,
        ]);
    }
}
