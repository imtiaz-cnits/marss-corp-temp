<?php

namespace App\Livewire\Battery\Report;

use App\Services\Battery\BatteryReportService;
use Carbon\Carbon;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Battery Daily Receipt & Payment')]
class BatteryDailyReceiptPaymentReport extends Component
{
    public string $startDate = '';

    public string $endDate = '';

    public string $quickFilter = 'today';

    public function mount(): void
    {
        $this->startDate = Carbon::today()->toDateString();
        $this->endDate = Carbon::today()->toDateString();
        $this->quickFilter = 'today';
    }

    public function updatedQuickFilter(string $value): void
    {
        $now = Carbon::now();
        match ($value) {
            'today' => [$this->startDate, $this->endDate] = [$now->toDateString(), $now->toDateString()],
            'yesterday' => [$this->startDate, $this->endDate] = [$now->copy()->subDay()->toDateString(), $now->copy()->subDay()->toDateString()],
            'this_week' => [$this->startDate, $this->endDate] = [$now->copy()->startOfWeek()->toDateString(), $now->copy()->endOfWeek()->toDateString()],
            'this_month' => [$this->startDate, $this->endDate] = [$now->copy()->startOfMonth()->toDateString(), $now->copy()->endOfMonth()->toDateString()],
            'last_month' => [$this->startDate, $this->endDate] = [$now->copy()->subMonth()->startOfMonth()->toDateString(), $now->copy()->subMonth()->endOfMonth()->toDateString()],
            'this_year' => [$this->startDate, $this->endDate] = [$now->copy()->startOfYear()->toDateString(), $now->copy()->endOfYear()->toDateString()],
            'all_time' => [$this->startDate, $this->endDate] = ['2020-01-01', $now->toDateString()],
            default => null,
        };
    }

    public function render(BatteryReportService $reports)
    {
        $start = $this->startDate ?: Carbon::today()->toDateString();
        $end = $this->endDate ?: $start;

        return view('livewire.battery.report.battery-daily-receipt-payment-report', [
            'stmt' => $reports->dailyReceiptPayment($start, $end),
        ]);
    }
}
