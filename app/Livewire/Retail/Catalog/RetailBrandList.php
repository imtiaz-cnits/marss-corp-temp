<?php

namespace App\Livewire\Retail\Catalog;

use App\Models\Retail\RetailBrand;
use App\Services\Shared\SafeImageUploadService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Title('Retail Brand List')]
class RetailBrandList extends Component
{
    use WithFileUploads, WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public int $perPage = 15;

    public bool $showModal = false;

    public bool $showImageModal = false;

    public ?string $viewingImageUrl = null;

    public ?string $viewingImageTitle = null;

    public ?int $editingId = null;

    public string $name = '';

    public string $status = 'active';

    public $logo;

    public ?string $existingLogo = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function setStatusFilter(string $status): void
    {
        $this->statusFilter = in_array($status, ['', 'active', 'inactive'], true) ? $status : '';
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->reset(['editingId', 'name', 'logo', 'existingLogo']);
        $this->status = 'active';
        $this->resetValidation();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $b = RetailBrand::findOrFail($id);
        $this->editingId = $b->id;
        $this->name = (string) $b->name;
        $this->status = strtolower((string) ($b->status ?: 'active'));
        $this->existingLogo = $b->logo;
        $this->logo = null;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function openImageModal(string $url, string $title = 'Brand Logo'): void
    {
        $this->viewingImageUrl = $url;
        $this->viewingImageTitle = $title;
        $this->showImageModal = true;
    }

    public function save(SafeImageUploadService $uploads): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:150'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        $path = $this->existingLogo;
        if ($this->logo) {
            $path = $uploads->store($this->logo, 'brands', $this->existingLogo);
        }

        RetailBrand::updateOrCreate(['id' => $this->editingId], [
            'name' => trim($this->name),
            'logo' => $path,
            'status' => $this->status,
            'user_id' => Auth::id(),
        ]);

        $this->showModal = false;
        $this->dispatch('toast', type: 'success', message: 'Brand saved!');
    }

    public bool $showDeleteModal = false;

    public ?int $deletingId = null;

    public string $deletingName = '';

    public function confirmDelete(int $id): void
    {
        $b = RetailBrand::findOrFail($id);
        $this->deletingId = $b->id;
        $this->deletingName = (string) $b->name;
        $this->showDeleteModal = true;
    }

    public function confirmDeleteAction(SafeImageUploadService $uploads): void
    {
        if ($this->deletingId) {
            $this->delete($this->deletingId, $uploads);
        }
        $this->showDeleteModal = false;
        $this->deletingId = null;
        $this->deletingName = '';
    }

    public function delete(int $id, SafeImageUploadService $uploads): void
    {
        $b = RetailBrand::findOrFail($id);
        $uploads->delete($b->logo);
        $b->delete();
        $this->dispatch('toast', type: 'success', message: 'Brand deleted.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->showImageModal = false;
        $this->showDeleteModal = false;
        $this->viewingImageUrl = null;
        $this->deletingId = null;
    }

    public function render()
    {
        $query = RetailBrand::query()
            ->when(trim($this->search) !== '', fn ($q) => $q->where('name', 'like', '%'.trim($this->search).'%'))
            ->when($this->statusFilter !== '', fn ($q) => $q->whereRaw("LOWER(COALESCE(status, 'active')) = ?", [strtolower($this->statusFilter)]))
            ->orderByDesc('id');

        return view('livewire.retail.catalog.retail-brand-list', [
            'brands' => $query->paginate($this->perPage),
        ]);
    }
}
