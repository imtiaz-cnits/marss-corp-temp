<?php

namespace App\Livewire\Retail\Report;

use App\Services\Retail\RetailReportService;
use Carbon\Carbon;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Retail Personal Transaction Report')]
class RetailPersonalTransactionReport extends Component
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

        return view('livewire.retail.report.retail-personal-transaction-report', [
            'data' => $reports->personalTransactionReport($start, $end),
        ]);
    }
}
