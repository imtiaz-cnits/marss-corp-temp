<?php

namespace App\Livewire\Battery\Report;

use App\Services\Battery\BatteryReportService;
use Carbon\Carbon;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Battery Personal Transaction Report')]
class BatteryPersonalTransactionReport extends Component
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

        // Strictly isolated Battery Personal expenses only (fixes legacy Retail Invest leak FIN-10!)
        return view('livewire.battery.report.battery-personal-transaction-report', [
            'data' => $reports->personalTransactionReport($start, $end),
        ]);
    }
}
