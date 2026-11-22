<?php

namespace App\Livewire\Retail\Catalog;

use App\Models\Retail\RetailCategory;
use App\Services\Shared\SafeImageUploadService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Title('Retail Category List')]
class RetailCategoryList extends Component
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

    public string $category_name = '';

    public string $status = 'active';

    public $image;

    public ?string $existingImage = null;

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
        $this->reset(['editingId', 'category_name', 'image', 'existingImage']);
        $this->status = 'active';
        $this->resetValidation();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $c = RetailCategory::findOrFail($id);
        $this->editingId = $c->id;
        $this->category_name = (string) $c->category_name;
        $this->status = strtolower((string) ($c->status ?: 'active'));
        $this->existingImage = $c->img_url;
        $this->image = null;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function openImageModal(string $url, string $title = 'Category Image'): void
    {
        $this->viewingImageUrl = $url;
        $this->viewingImageTitle = $title;
        $this->showImageModal = true;
    }

    public function save(SafeImageUploadService $uploads): void
    {
        $this->validate([
            'category_name' => ['required', 'string', 'max:150'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        $path = $this->existingImage;
        if ($this->image) {
            $path = $uploads->store($this->image, 'categories', $this->existingImage);
        }

        RetailCategory::updateOrCreate(['id' => $this->editingId], [
            'category_name' => trim($this->category_name),
            'img_url' => $path,
            'status' => $this->status,
            'user_id' => Auth::id(),
        ]);

        $this->showModal = false;
        $this->dispatch('toast', type: 'success', message: 'Category saved!');
    }

    public bool $showDeleteModal = false;

    public ?int $deletingId = null;

    public string $deletingName = '';

    public function confirmDelete(int $id): void
    {
        $c = RetailCategory::findOrFail($id);
        $this->deletingId = $c->id;
        $this->deletingName = (string) $c->category_name;
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
        $c = RetailCategory::findOrFail($id);
        $uploads->delete($c->img_url);
        $c->delete();
        $this->dispatch('toast', type: 'success', message: 'Category deleted.');
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
        $query = RetailCategory::query()
            ->when(trim($this->search) !== '', fn ($q) => $q->where('category_name', 'like', '%'.trim($this->search).'%'))
            ->when($this->statusFilter !== '', fn ($q) => $q->whereRaw("LOWER(COALESCE(status, 'active')) = ?", [strtolower($this->statusFilter)]))
            ->orderByDesc('id');

        return view('livewire.retail.catalog.retail-category-list', [
            'categories' => $query->paginate($this->perPage),
        ]);
    }
}
