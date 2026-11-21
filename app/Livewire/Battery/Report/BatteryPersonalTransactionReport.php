<?php

namespace App\Livewire\Battery\Report;

use App\Livewire\Concerns\PaginatesCollections;
use App\Services\Battery\BatteryReportService;
use Carbon\Carbon;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Battery Personal Transaction Report')]
class BatteryPersonalTransactionReport extends Component
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
        $data = $reports->personalTransactionReport($start, $end);
        $expenses = $this->paginateItems($data['expenses'], $this->perPage);

        // Strictly isolated Battery Personal expenses only (fixes legacy Retail Invest leak FIN-10!)
        return view('livewire.battery.report.battery-personal-transaction-report', [
            'data' => $data,
            'expenses' => $expenses,
        ]);
    }
}
