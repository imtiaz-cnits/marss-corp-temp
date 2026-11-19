<?php

namespace App\Livewire\Battery\Supplier;

use App\Models\Battery\BatterySupplier;
use App\Support\Money;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Battery Suppliers')]
class BatterySupplierList extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $company = '';

    public string $mobile = '';

    public string $address = '';

    public string $purchase_payable_amount = '0';

    public function openCreate(): void
    {
        $this->reset(['editingId', 'name', 'company', 'mobile', 'address']);
        $this->purchase_payable_amount = '0';
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $s = BatterySupplier::findOrFail($id);
        $this->editingId = $s->id;
        $this->name = (string) $s->name;
        $this->company = (string) ($s->company ?? '');
        $this->mobile = (string) ($s->mobile ?? '');
        $this->address = (string) ($s->address ?? '');
        $this->purchase_payable_amount = Money::round($s->purchase_payable_amount ?? 0);
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate(['name' => ['required', 'string', 'max:150']]);
        $data = [
            'name' => trim($this->name),
            'company' => trim($this->company),
            'mobile' => trim($this->mobile),
            'address' => trim($this->address),
            'purchase_payable_amount' => Money::round($this->purchase_payable_amount ?: 0),
            'user_id' => Auth::id(),
        ];
        if (! $this->editingId) {
            $max = (int) (BatterySupplier::max('id') ?? 0);
            $data['supplier_id'] = '#BSup'.str_pad((string) ($max + 1), 5, '0', STR_PAD_LEFT);
        }
        BatterySupplier::updateOrCreate(['id' => $this->editingId], $data);
        $this->showModal = false;
        $this->dispatch('toast', type: 'success', message: 'Battery supplier saved!');
    }

    public function delete(int $id): void
    {
        $s = BatterySupplier::findOrFail($id);
        if ($s->purchases()->exists()) {
            $this->dispatch('toast', type: 'error', message: 'Cannot delete supplier with purchases.');

            return;
        }
        $s->delete();
        $this->dispatch('toast', type: 'success', message: 'Deleted.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function render()
    {
        return view('livewire.battery.supplier.battery-supplier-list', [
            'suppliers' => BatterySupplier::withSum('purchases', 'due_amount')
                ->when($this->search !== '', fn ($q) => $q->where('name', 'like', '%'.trim($this->search).'%')->orWhere('mobile', 'like', '%'.trim($this->search).'%'))
                ->orderByDesc('id')->paginate(15),
        ]);
    }
}
