<?php

namespace App\Livewire\Battery\Purchase;

use App\Models\Battery\BatteryPurchasePaymentDetail;
use App\Support\DateFilter;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Battery Purchase Payments')]
class BatteryPurchasePaymentList extends Component
{
    use WithPagination;

    public string $startDate = '';

    public string $endDate = '';

    public function render()
    {
        // Strictly guards against legacy orphan row #1 via whereHas('purchase')
        $query = BatteryPurchasePaymentDetail::with('purchase.supplier')
            ->whereHas('purchase')
            ->orderByDesc('id');

        if ($this->startDate || $this->endDate) {
            DateFilter::applyDateFilter($query, 'purchase_due_collection_date', $this->startDate ?: null, $this->endDate ?: null);
        }

        return view('livewire.battery.purchase.battery-purchase-payment-list', [
            'payments' => $query->paginate(20),
        ]);
    }
}
