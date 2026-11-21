<?php

namespace App\Livewire\Retail\Report;

use App\Livewire\Concerns\PaginatesCollections;
use App\Services\Retail\RetailReportService;
use Carbon\Carbon;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Retail Daily Income & Expense Ledger')]
class RetailDailyLedgerReport extends Component
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
        $ledger = $reports->dailyLedger($start, $end);
        $entries = $this->paginateItems($ledger['entries'], $this->perPage);

        return view('livewire.retail.report.retail-daily-ledger-report', [
            'ledger' => $ledger,
            'entries' => $entries,
        ]);
    }
}
