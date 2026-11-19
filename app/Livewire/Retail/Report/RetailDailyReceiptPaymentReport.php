<?php

namespace App\Livewire\Retail\Report;

use App\Services\Retail\RetailReportService;
use Carbon\Carbon;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Retail Daily Receipt & Payment')]
class RetailDailyReceiptPaymentReport extends Component
{
    public string $startDate = '';

    public string $endDate = '';

    public function mount(): void
    {
        $this->startDate = Carbon::today()->toDateString();
        $this->endDate = Carbon::today()->toDateString();
    }

    public function render(RetailReportService $reports)
    {
        $start = $this->startDate ?: Carbon::today()->toDateString();
        $end = $this->endDate ?: $start;

        return view('livewire.retail.report.retail-daily-receipt-payment-report', [
            'stmt' => $reports->dailyReceiptPayment($start, $end),
        ]);
    }
}
