<?php

namespace App\Livewire\Retail\Supplier;

use App\Models\Retail\RetailSupplier;
use App\Services\Shared\ActivityLogger;
use App\Services\Shared\SafeImageUploadService;
use App\Support\Money;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Title('Retail Suppliers')]
class RetailSupplierList extends Component
{
    use WithFileUploads, WithPagination;

    public string $search = '';

    public int $perPage = 15;

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $company = '';

    public string $mobile = '';

    public string $email = '';

    public string $address = '';

    public string $purchase_payable_amount = '0';

    public string $status = 'active';

    public $image;

    public ?string $existingImage = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->reset(['editingId', 'name', 'company', 'mobile', 'email', 'address', 'image', 'existingImage']);
        $this->purchase_payable_amount = '0';
        $this->status = 'active';
        $this->resetValidation();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $s = RetailSupplier::findOrFail($id);
        $this->editingId = $s->id;
        $this->name = (string) $s->name;
        $this->company = (string) ($s->company ?? '');
        $this->mobile = (string) ($s->mobile ?? '');
        $this->email = (string) ($s->email ?? '');
        $this->address = (string) ($s->address ?? '');
        $this->purchase_payable_amount = Money::round($s->purchase_payable_amount ?? 0);
        $this->status = (string) ($s->status ?: 'active');
        $this->existingImage = $s->img_url;
        $this->image = null;
        $this->showModal = true;
    }

    public function save(SafeImageUploadService $uploads): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:150'],
            'mobile' => ['nullable', 'string', 'max:30'],
            'purchase_payable_amount' => ['nullable', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        $path = $this->existingImage;
        if ($this->image) {
            $path = $uploads->store($this->image, 'suppliers', $this->existingImage);
        }

        $data = [
            'name' => trim($this->name),
            'company' => trim($this->company),
            'mobile' => trim($this->mobile),
            'email' => trim($this->email),
            'address' => trim($this->address),
            'purchase_payable_amount' => Money::round($this->purchase_payable_amount ?: 0),
            'status' => $this->status,
            'img_url' => $path,
            'user_id' => Auth::id(),
        ];

        if (! $this->editingId) {
            $max = (int) (RetailSupplier::max('id') ?? 0);
            $data['supplier_id'] = '#SupID'.str_pad((string) ($max + 1), 5, '0', STR_PAD_LEFT);
        }

        $supplier = RetailSupplier::updateOrCreate(['id' => $this->editingId], $data);
        ActivityLogger::log($this->editingId ? 'Update' : 'Create', 'Retail / Supplier', "Saved Retail supplier '{$supplier->name}'.");
        $this->showModal = false;
        $this->dispatch('toast', type: 'success', message: "Supplier '{$supplier->name}' saved!");
    }

    public function delete(int $id): void
    {
        $s = RetailSupplier::findOrFail($id);
        if ($s->purchases()->exists()) {
            $this->dispatch('toast', type: 'error', message: "Cannot delete '{$s->name}' with existing purchases.");

            return;
        }
        $s->delete();
        $this->dispatch('toast', type: 'success', message: 'Supplier deleted.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function render()
    {
        $query = RetailSupplier::withSum('purchases', 'due_amount')->orderByDesc('id');
        if (trim($this->search) !== '') {
            $term = '%'.trim($this->search).'%';
            $query->where(fn ($q) => $q->where('name', 'like', $term)->orWhere('company', 'like', $term)->orWhere('mobile', 'like', $term)->orWhere('supplier_id', 'like', $term));
        }

        return view('livewire.retail.supplier.retail-supplier-list', [
            'suppliers' => $query->paginate($this->perPage),
        ]);
    }
}
