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

    public bool $showDeleteModal = false;

    public bool $showImageModal = false;

    public ?int $editingId = null;

    public ?int $deletingId = null;

    public string $deletingName = '';

    public string $viewingImageUrl = '';

    public string $viewingImageTitle = '';

    public string $name = '';

    public string $company = '';

    public string $mobile = '';

    public string $email = '';

    public string $address = '';

    public string $purchase_payable_amount = '';

    public string $status = 'Active';

    public $image;

    public ?string $existingImage = null;

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
        $this->reset(['editingId', 'name', 'company', 'mobile', 'email', 'address', 'purchase_payable_amount', 'image', 'existingImage']);
        $this->status = 'Active';
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
        $rawStatus = strtolower(trim((string) ($s->status ?: 'Active')));
        $this->status = $rawStatus === 'inactive' ? 'InActive' : 'Active';
        $this->existingImage = $s->img_url;
        $this->image = null;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function save(SafeImageUploadService $uploads): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:150'],
            'mobile' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'purchase_payable_amount' => ['nullable', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        $path = $this->existingImage;
        if ($this->image) {
            $path = $uploads->store($this->image, 'supplier-img', $this->existingImage);
        }

        $data = [
            'name' => trim($this->name),
            'company' => trim($this->company),
            'mobile' => trim($this->mobile),
            'email' => trim($this->email),
            'address' => trim($this->address),
            'purchase_payable_amount' => Money::round($this->purchase_payable_amount !== '' ? $this->purchase_payable_amount : 0),
            'status' => $this->status ?: 'Active',
            'img_url' => $path,
            'user_id' => Auth::id(),
        ];

        if (! $this->editingId) {
            $last = RetailSupplier::latest('id')->first();
            $nextNum = 10001;
            if ($last && preg_match('/(\d+)$/', (string) $last->supplier_id, $m)) {
                $nextNum = max(10001, ((int) $m[1]) + 1);
            }
            $data['supplier_id'] = 'SUP-'.$nextNum;
        }

        $supplier = RetailSupplier::updateOrCreate(['id' => $this->editingId], $data);
        ActivityLogger::log($this->editingId ? 'Update' : 'Create', 'Retail / Supplier', "Saved Retail supplier '{$supplier->name}'.");
        $this->showModal = false;
        $this->dispatch('toast', type: 'success', message: $this->editingId ? 'Supplier updated successfully!' : 'Supplier Created Successfully!');
    }

    public function confirmDelete(int $id): void
    {
        $s = RetailSupplier::findOrFail($id);
        $this->deletingId = $s->id;
        $this->deletingName = (string) $s->name;
        $this->showDeleteModal = true;
    }

    public function confirmDeleteAction(): void
    {
        if ($this->deletingId) {
            $this->delete($this->deletingId);
        }
        $this->closeModal();
    }

    public function delete(int $id): void
    {
        $s = RetailSupplier::findOrFail($id);
        if ($s->purchases()->exists()) {
            $this->dispatch('toast', type: 'error', message: "Cannot delete '{$s->name}' with existing purchases.");

            return;
        }
        $s->delete();
        ActivityLogger::log('Delete', 'Retail / Supplier', "Deleted Retail supplier '{$s->name}'.");
        $this->dispatch('toast', type: 'success', message: 'Supplier deleted successfully.');
    }

    public function openImageModal(string $url, string $title = ''): void
    {
        $this->viewingImageUrl = $url;
        $this->viewingImageTitle = $title;
        $this->showImageModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->showDeleteModal = false;
        $this->showImageModal = false;
        $this->deletingId = null;
        $this->deletingName = '';
        $this->viewingImageUrl = '';
        $this->viewingImageTitle = '';
    }

    public function render()
    {
        $query = RetailSupplier::withSum('purchases', 'due_amount')->orderByDesc('id');
        if (trim($this->search) !== '') {
            $term = '%'.trim($this->search).'%';
            $query->where(fn ($q) => $q
                ->where('name', 'like', $term)
                ->orWhere('company', 'like', $term)
                ->orWhere('mobile', 'like', $term)
                ->orWhere('supplier_id', 'like', $term)
                ->orWhere('status', 'like', $term));
        }

        $totalPayable = Money::sum((clone $query)->pluck('purchase_payable_amount'));

        return view('livewire.retail.supplier.retail-supplier-list', [
            'suppliers' => $query->paginate($this->perPage),
            'totalPayable' => $totalPayable,
        ]);
    }
}
