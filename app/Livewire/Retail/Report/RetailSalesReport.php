<?php

namespace App\Livewire\Retail\Report;

use App\Livewire\Concerns\PaginatesCollections;
use App\Services\Retail\RetailReportService;
use Carbon\Carbon;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Retail Sales Report')]
class RetailSalesReport extends Component
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

    public function render(RetailReportService $reports)
    {
        $start = $this->startDate ?: Carbon::today()->toDateString();
        $end = $this->endDate ?: $start;
        $report = $reports->salesReport($start, $end);
        $rows = $this->paginateItems($report['rows'], $this->perPage);

        return view('livewire.retail.report.retail-sales-report', [
            'report' => $report,
            'rows' => $rows,
            'canViewProfit' => auth()->user()?->canViewProfit() ?? false,
        ]);
    }
}
