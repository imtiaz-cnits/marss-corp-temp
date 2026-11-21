<?php

namespace App\Livewire\Retail\Report;

use App\Livewire\Concerns\PaginatesCollections;
use App\Services\Retail\RetailReportService;
use Carbon\Carbon;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Retail Best-Selling Products')]
class RetailBestSellingReport extends Component
{
    use PaginatesCollections, WithPagination;

    public string $startDate = '';

    public string $endDate = '';

    public int $perPage = 15;

    public function mount(): void
    {
        $this->startDate = Carbon::now()->startOfYear()->toDateString();
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

        return view('livewire.retail.report.retail-best-selling-report', [
            'rows' => $this->paginateItems($reports->bestSellingProducts($start, $end), $this->perPage),
            'canViewProfit' => auth()->user()?->canViewProfit() ?? false,
        ]);
    }
}
