<?php

namespace App\Livewire\Battery\Customer;

use App\Models\Battery\BatteryCustomer;
use App\Services\Shared\ActivityLogger;
use App\Services\Shared\SafeImageUploadService;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Title('Battery Customer List')]
class BatteryCustomerList extends Component
{
    use WithFileUploads, WithPagination;

    public string $search = '';

    public int $perPage = 15;

    // Create / Edit Modal
    public bool $showModal = false;

    public ?int $editingId = null;

    public string $customer_name = '';

    public string $mobile = '';

    public string $email = '';

    public string $nid = '';

    public string $address_details = '';

    public string $previous_due_amount = '0';

    public $photo;

    public ?string $existingImage = null;

    // Custom Delete Confirmation Modal
    public bool $showDeleteModal = false;

    public ?int $deleteTargetId = null;

    public string $deleteTargetName = '';

    // Image Quick View Modal
    public bool $showImageModal = false;

    public string $imageModalUrl = '';

    public string $imageModalTitle = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->reset(['editingId', 'customer_name', 'mobile', 'email', 'nid', 'address_details', 'photo', 'existingImage']);
        $this->previous_due_amount = '0.00';
        $this->resetValidation();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $c = BatteryCustomer::findOrFail($id);
        $this->resetValidation();
        $this->editingId = $c->id;
        $this->customer_name = (string) $c->customer_name;
        $this->mobile = (string) ($c->mobile ?? '');
        $this->email = (string) ($c->email ?? '');
        $this->nid = (string) ($c->nid ?? '');
        $this->address_details = (string) ($c->address_details ?? '');
        $this->previous_due_amount = Money::round($c->previous_due_amount ?? 0);
        $this->photo = null;
        $this->existingImage = $c->img_url ?: null;
        $this->showModal = true;
    }

    public function save(SafeImageUploadService $uploads): void
    {
        $this->validate([
            'customer_name' => ['required', 'string', 'max:150'],
            'mobile' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'string', 'max:150'],
            'nid' => ['nullable', 'string', 'max:50'],
            'previous_due_amount' => ['nullable', 'numeric', 'min:0'],
            'photo' => ['nullable', 'image', 'max:5120'],
        ]);

        try {
            $data = [
                'customer_name' => trim($this->customer_name),
                'mobile' => trim($this->mobile),
                'email' => trim($this->email),
                'nid' => trim($this->nid),
                'address_details' => trim($this->address_details),
                'previous_due_amount' => Money::round($this->previous_due_amount ?: 0),
                'user_id' => Auth::id(),
            ];

            if ($this->photo) {
                $data['img_url'] = $uploads->store($this->photo, 'battery_customers', $this->existingImage);
            }

            if (! $this->editingId) {
                $max = (int) (BatteryCustomer::max('id') ?? 0);
                $data['customer_id'] = 'BCUST-' . str_pad((string) ($max + 1), 4, '0', STR_PAD_LEFT);
                $data['date'] = Carbon::today()->toDateString();
            }

            $customer = BatteryCustomer::updateOrCreate(['id' => $this->editingId], $data);
            ActivityLogger::log($this->editingId ? 'Update' : 'Create', 'Battery / Customer', "Saved Battery customer '{$customer->customer_name}'.");
            $this->photo = null;
            $this->existingImage = null;
            $this->showModal = false;
            $this->dispatch('toast', type: 'success', message: "Battery customer '{$customer->customer_name}' saved!");
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function confirmDelete(int $id): void
    {
        $c = BatteryCustomer::findOrFail($id);
        $this->deleteTargetId = $c->id;
        $this->deleteTargetName = (string) $c->customer_name;
        $this->showDeleteModal = true;
    }

    public function executeDelete(): void
    {
        if (! $this->deleteTargetId) {
            return;
        }
        $id = $this->deleteTargetId;
        $this->showDeleteModal = false;
        $this->deleteTargetId = null;
        $this->delete($id);
    }

    public function delete(int $id): void
    {
        $c = BatteryCustomer::findOrFail($id);
        if ($c->orders()->exists()) {
            $this->dispatch('toast', type: 'error', message: 'Cannot delete customer with Battery orders.');

            return;
        }
        $c->delete();
        $this->dispatch('toast', type: 'success', message: 'Customer deleted.');
    }

    public function openImageModal(string $url, string $title = 'Customer Photo'): void
    {
        $this->imageModalUrl = $url;
        $this->imageModalTitle = $title;
        $this->showImageModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->showDeleteModal = false;
        $this->showImageModal = false;
    }

    public function render()
    {
        $query = BatteryCustomer::withSum('orders', 'due_amount')->orderByDesc('id');
        if (trim($this->search) !== '') {
            $t = '%' . trim($this->search) . '%';
            $query->where(fn ($q) => $q
                ->where('customer_name', 'like', $t)
                ->orWhere('mobile', 'like', $t)
                ->orWhere('customer_id', 'like', $t)
                ->orWhere('address_details', 'like', $t)
            );
        }

        $allMatching = (clone $query)->get(['id', 'previous_due_amount']);
        $totalDueSum = Money::sum($allMatching->map(fn ($c) => Money::add($c->previous_due_amount ?? 0, $c->orders_sum_due_amount ?? 0)));

        return view('livewire.battery.customer.battery-customer-list', [
            'customers' => $query->paginate($this->perPage),
            'totalDueSum' => $totalDueSum,
        ]);
    }
}
