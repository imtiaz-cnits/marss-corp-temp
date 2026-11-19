<?php

namespace App\Livewire\Battery\Dashboard;

use App\Services\Battery\BatteryReportService;
use Carbon\Carbon;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Battery Dashboard')]
class BatteryDashboard extends Component
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
    }

    public function updatedStartDate(): void
    {
        $this->filterType = 'date_wise';
    }

    public function updatedEndDate(): void
    {
        $this->filterType = 'date_wise';
    }

    public function applyDateRange(): void
    {
        $this->filterType = 'date_wise';
    }

    public function clearDateRange(): void
    {
        $today = Carbon::today()->toDateString();
        $this->startDate = $today;
        $this->endDate = $today;
        $this->filterType = 'today';
    }

    public function render(BatteryReportService $reports)
    {
        $canViewProfit = auth()->user()?->canViewProfit() ?? false;
        $metrics = $reports->dashboardMetrics(
            $this->filterType,
            $this->startDate ?: null,
            $this->endDate ?: null,
            $canViewProfit,
        );

        return view('livewire.battery.dashboard.battery-dashboard', [
            'metrics' => $metrics,
            'canViewProfit' => $canViewProfit,
        ]);
    }
}
