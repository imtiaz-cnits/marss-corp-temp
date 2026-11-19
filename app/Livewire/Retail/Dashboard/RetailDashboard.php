<?php

namespace App\Livewire\Retail\Dashboard;

use App\Services\Retail\RetailReportService;
use Carbon\Carbon;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Retail Dashboard')]
class RetailDashboard extends Component
{
    public string $filterType = 'today';

    public string $startDate = '';

    public string $endDate = '';

    public function mount(): void
    {
        $today = Carbon::today()->toDateString();
        $this->startDate = $today;
        $this->endDate = $today;
    }

    public function setFilter(string $type): void
    {
        $this->filterType = $type;
        $this->dispatch('retail-dashboard-updated');
    }

    public function applyDateRange(): void
    {
        $this->filterType = 'date_wise';
        $this->dispatch('retail-dashboard-updated');
    }

    public function render(RetailReportService $reports)
    {
        $canViewProfit = auth()->user()?->canViewProfit() ?? false;
        $metrics = $reports->dashboardMetrics(
            $this->filterType,
            $this->startDate ?: null,
            $this->endDate ?: null,
            $canViewProfit,
        );

        return view('livewire.retail.dashboard.retail-dashboard', [
            'metrics' => $metrics,
            'canViewProfit' => $canViewProfit,
        ]);
    }
}
