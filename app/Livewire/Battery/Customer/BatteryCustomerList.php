<?php

namespace App\Livewire\Battery\Customer;

use App\Models\Battery\BatteryCustomer;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Battery Customers')]
class BatteryCustomerList extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $customer_name = '';

    public string $mobile = '';

    public string $email = '';

    public string $address_details = '';

    public string $previous_due_amount = '0';

    public function openCreate(): void
    {
        $this->reset(['editingId', 'customer_name', 'mobile', 'email', 'address_details']);
        $this->previous_due_amount = '0';
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $c = BatteryCustomer::findOrFail($id);
        $this->editingId = $c->id;
        $this->customer_name = (string) $c->customer_name;
        $this->mobile = (string) ($c->mobile ?? '');
        $this->email = (string) ($c->email ?? '');
        $this->address_details = (string) ($c->address_details ?? '');
        $this->previous_due_amount = Money::round($c->previous_due_amount ?? 0);
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate(['customer_name' => ['required', 'string', 'max:150']]);
        $data = [
            'customer_name' => trim($this->customer_name),
            'mobile' => trim($this->mobile),
            'email' => trim($this->email),
            'address_details' => trim($this->address_details),
            'previous_due_amount' => Money::round($this->previous_due_amount ?: 0),
            'user_id' => Auth::id(),
        ];
        if (! $this->editingId) {
            $max = (int) (BatteryCustomer::max('id') ?? 0);
            $data['customer_id'] = '#BCus'.str_pad((string) ($max + 1), 5, '0', STR_PAD_LEFT);
            $data['date'] = Carbon::today()->toDateString();
        }
        BatteryCustomer::updateOrCreate(['id' => $this->editingId], $data);
        $this->showModal = false;
        $this->dispatch('toast', type: 'success', message: 'Battery customer saved!');
    }

    public function delete(int $id): void
    {
        $c = BatteryCustomer::findOrFail($id);
        if ($c->orders()->exists()) {
            $this->dispatch('toast', type: 'error', message: 'Cannot delete customer with Battery orders.');

            return;
        }
        $c->delete();
        $this->dispatch('toast', type: 'success', message: 'Deleted.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function render()
    {
        return view('livewire.battery.customer.battery-customer-list', [
            'customers' => BatteryCustomer::withSum('orders', 'due_amount')
                ->when($this->search !== '', fn ($q) => $q->where('customer_name', 'like', '%'.trim($this->search).'%')->orWhere('mobile', 'like', '%'.trim($this->search).'%'))
                ->orderByDesc('id')->paginate(15),
        ]);
    }
}
