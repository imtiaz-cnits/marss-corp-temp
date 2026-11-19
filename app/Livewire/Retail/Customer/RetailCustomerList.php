<?php

namespace App\Livewire\Retail\Customer;

use App\Models\Retail\RetailCustomer;
use App\Models\Shared\District;
use App\Models\Shared\Location;
use App\Models\Shared\Thana;
use App\Models\Shared\Upazila;
use App\Services\Shared\ActivityLogger;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Retail Customers')]
class RetailCustomerList extends Component
{
    use WithPagination;

    public string $search = '';

    public int $perPage = 15;

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $customer_name = '';

    public string $mobile = '';

    public string $email = '';

    public string $nid = '';

    public string $address_details = '';

    public string $previous_due_amount = '0';

    public ?int $district_id = null;

    public ?int $upazila_id = null;

    public ?int $thana_id = null;

    public ?int $location_id = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->reset(['editingId', 'customer_name', 'mobile', 'email', 'nid', 'address_details', 'district_id', 'upazila_id', 'thana_id', 'location_id']);
        $this->previous_due_amount = '0';
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $c = RetailCustomer::findOrFail($id);
        $this->editingId = $c->id;
        $this->customer_name = (string) $c->customer_name;
        $this->mobile = (string) ($c->mobile ?? '');
        $this->email = (string) ($c->email ?? '');
        $this->nid = (string) ($c->nid ?? '');
        $this->address_details = (string) ($c->address_details ?? '');
        $this->previous_due_amount = Money::round($c->previous_due_amount ?? 0);
        $this->district_id = $c->district_id;
        $this->upazila_id = $c->upazila_id;
        $this->thana_id = $c->thana_id;
        $this->location_id = $c->location_id;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'customer_name' => ['required', 'string', 'max:150'],
            'mobile' => ['nullable', 'string', 'max:30'],
            'previous_due_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $data = [
            'customer_name' => trim($this->customer_name),
            'mobile' => trim($this->mobile),
            'email' => trim($this->email),
            'nid' => trim($this->nid),
            'address_details' => trim($this->address_details),
            'previous_due_amount' => Money::round($this->previous_due_amount ?: 0),
            'district_id' => $this->district_id,
            'upazila_id' => $this->upazila_id,
            'thana_id' => $this->thana_id,
            'location_id' => $this->location_id,
            'user_id' => Auth::id(),
        ];

        if (! $this->editingId) {
            $max = (int) (RetailCustomer::max('id') ?? 0);
            $data['customer_id'] = '#CusID'.str_pad((string) ($max + 1), 5, '0', STR_PAD_LEFT);
            $data['date'] = Carbon::today()->toDateString();
        }

        $customer = RetailCustomer::updateOrCreate(['id' => $this->editingId], $data);
        ActivityLogger::log($this->editingId ? 'Update' : 'Create', 'Retail / Customer', "Saved Retail customer '{$customer->customer_name}'.");
        $this->showModal = false;
        $this->dispatch('toast', type: 'success', message: "Customer '{$customer->customer_name}' saved!");
    }

    public function delete(int $id): void
    {
        $c = RetailCustomer::findOrFail($id);
        if ($c->orders()->exists()) {
            $this->dispatch('toast', type: 'error', message: "Cannot delete '{$c->customer_name}' with existing orders.");

            return;
        }
        $c->delete();
        $this->dispatch('toast', type: 'success', message: 'Customer deleted.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function render()
    {
        $query = RetailCustomer::withSum('orders', 'due_amount')->orderByDesc('id');
        if (trim($this->search) !== '') {
            $t = '%'.trim($this->search).'%';
            $query->where(fn ($q) => $q->where('customer_name', 'like', $t)->orWhere('mobile', 'like', $t)->orWhere('customer_id', 'like', $t)->orWhere('address_details', 'like', $t));
        }

        return view('livewire.retail.customer.retail-customer-list', [
            'customers' => $query->paginate($this->perPage),
            'districts' => $this->showModal ? District::orderBy('district_name')->get() : collect(),
            'upazilas' => $this->showModal && $this->district_id ? Upazila::where('district_id', $this->district_id)->orderBy('upazila_name')->get() : collect(),
            'thanas' => $this->showModal && $this->district_id ? Thana::where('district_id', $this->district_id)->orderBy('Thana_name')->get() : collect(),
            'locations' => $this->showModal ? Location::orderBy('name')->get() : collect(),
        ]);
    }
}
