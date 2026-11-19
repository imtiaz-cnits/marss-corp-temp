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

    public function mount(): void
    {
        $this->startDate = Carbon::today()->toDateString();
        $this->endDate = Carbon::today()->toDateString();
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
