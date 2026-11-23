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

    public string $quickFilter = 'this_month';

    public function mount(): void
    {
        $this->startDate = Carbon::now()->startOfMonth()->toDateString();
        $this->endDate = Carbon::today()->toDateString();
        $this->quickFilter = 'this_month';
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
        $this->resetPage('expPage');
        $this->resetPage('invPage');
    }

    public function updatedPerPage(): void
    {
        $this->resetPage('expPage');
        $this->resetPage('invPage');
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
