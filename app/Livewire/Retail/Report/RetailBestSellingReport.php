<?php

namespace App\Livewire\Retail\Report;

use App\Services\Retail\RetailReportService;
use Carbon\Carbon;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Retail Best-Selling Products')]
class RetailBestSellingReport extends Component
{
    public string $startDate = '';

    public string $endDate = '';

    public function mount(): void
    {
        $this->startDate = Carbon::now()->startOfYear()->toDateString();
        $this->endDate = Carbon::today()->toDateString();
    }

    public function render(RetailReportService $reports)
    {
        $start = $this->startDate ?: Carbon::today()->toDateString();
        $end = $this->endDate ?: $start;

        return view('livewire.retail.report.retail-best-selling-report', [
            'rows' => $reports->bestSellingProducts($start, $end),
            'canViewProfit' => auth()->user()?->canViewProfit() ?? false,
        ]);
    }
}
