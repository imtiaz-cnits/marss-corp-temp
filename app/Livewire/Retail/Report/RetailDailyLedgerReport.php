<?php

namespace App\Livewire\Retail\Report;

use App\Services\Retail\RetailReportService;
use Carbon\Carbon;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Retail Daily Income & Expense Ledger')]
class RetailDailyLedgerReport extends Component
{
    public string $startDate = '';

    public string $endDate = '';

    public function mount(): void
    {
        $this->startDate = Carbon::now()->startOfMonth()->toDateString();
        $this->endDate = Carbon::today()->toDateString();
    }

    public function render(RetailReportService $reports)
    {
        $start = $this->startDate ?: Carbon::today()->toDateString();
        $end = $this->endDate ?: $start;

        return view('livewire.retail.report.retail-daily-ledger-report', [
            'ledger' => $reports->dailyLedger($start, $end),
        ]);
    }
}
