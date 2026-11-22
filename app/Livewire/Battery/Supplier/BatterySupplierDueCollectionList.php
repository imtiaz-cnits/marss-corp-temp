<?php

namespace App\Livewire\Battery\Supplier;

use App\Models\Battery\BatterySupplierDueCollection;
use App\Support\Money;
use Carbon\Carbon;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Battery Supplier Due Collection History')]
class BatterySupplierDueCollectionList extends Component
{
    use WithPagination;

    public string $search = '';

    public int $perPage = 15;

    public string $dateFilter = 'all';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function setDateFilter(string $filter): void
    {
        $this->dateFilter = in_array($filter, ['all', 'today', '7', '30', '365'], true) ? $filter : 'all';
        $this->resetPage();
    }

    public function render()
    {
        $query = BatterySupplierDueCollection::with('supplier')->orderByDesc('id');

        if (trim($this->search) !== '') {
            $t = '%'.trim($this->search).'%';
            $query->where(function ($q) use ($t) {
                $q->whereHas('supplier', fn ($sq) => $sq
                    ->where('name', 'like', $t)
                    ->orWhere('supplier_id', 'like', $t)
                    ->orWhere('mobile', 'like', $t))
                    ->orWhere('payment_date', 'like', $t)
                    ->orWhere('payment_method', 'like', $t);
            });
        }

        if ($this->dateFilter !== 'all') {
            $start = match ($this->dateFilter) {
                'today' => Carbon::today(),
                '7' => Carbon::today()->subDays(7),
                '30' => Carbon::today()->subDays(30),
                '365' => Carbon::today()->subDays(365),
                default => null,
            };
            if ($start) {
                $query->where(function ($q) use ($start) {
                    $q->whereDate('payment_date', '>=', $start->toDateString())
                        ->orWhere(function ($sub) use ($start) {
                            $sub->whereNull('payment_date')
                                ->where('created_at', '>=', $start->startOfDay());
                        });
                });
            }
        }

        $allMatching = (clone $query)->get(['paid_amount', 'due_amount']);
        $subTotalPaid = Money::sum($allMatching->pluck('paid_amount'));
        $subTotalDue = Money::sum($allMatching->pluck('due_amount'));
        $subTotalPayable = Money::add($subTotalPaid, $subTotalDue);

        return view('livewire.battery.supplier.battery-supplier-due-collection-list', [
            'rows' => $query->paginate($this->perPage),
            'subTotalPayable' => $subTotalPayable,
            'subTotalPaid' => $subTotalPaid,
            'subTotalDue' => $subTotalDue,
        ]);
    }
}
