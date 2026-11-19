<?php

namespace App\Livewire\Battery\Report;

use App\Services\Battery\BatteryReportService;
use Carbon\Carbon;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Battery Income & Expense Report')]
class BatteryIncomeExpenseReport extends Component
{
    public string $startDate = '';

    public string $endDate = '';

    public function mount(): void
    {
        $this->startDate = Carbon::now()->startOfMonth()->toDateString();
        $this->endDate = Carbon::today()->toDateString();
    }

    public function render(BatteryReportService $reports)
    {
        $start = $this->startDate ?: Carbon::today()->toDateString();
        $end = $this->endDate ?: $start;

        return view('livewire.battery.report.battery-income-expense-report', [
            'summary' => $reports->incomeExpenseSummary($start, $end),
            'canViewProfit' => auth()->user()?->canViewProfit() ?? false,
        ]);
    }
}
