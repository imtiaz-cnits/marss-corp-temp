<?php

namespace App\Livewire\Battery\Catalog;

use App\Models\Battery\BatteryCategory;
use App\Services\Shared\SafeImageUploadService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Title('Battery Category List')]
class BatteryCategoryList extends Component
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

    public function setStatusFilter(string $status): void
    {
        $this->statusFilter = in_array($status, ['', 'active', 'inactive'], true) ? $status : '';
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->reset(['editingId', 'name', 'image', 'existingImage']);
        $this->resetValidation();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $c = BatteryCategory::findOrFail($id);
        $this->editingId = $c->id;
        $this->name = (string) $c->name;
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
            'name' => ['required', 'string', 'max:150'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        $path = $this->existingImage;
        if ($this->image) {
            $path = $uploads->store($this->image, 'battery-categories', $this->existingImage);
        }

        BatteryCategory::updateOrCreate(['id' => $this->editingId], [
            'name' => trim($this->name),
            'img_url' => $path,
            'user_id' => Auth::id(),
        ]);

        $this->showModal = false;
        $this->dispatch('toast', type: 'success', message: 'Battery category saved!');
    }

    public bool $showDeleteModal = false;

    public ?int $deletingId = null;

    public string $deletingName = '';

    public function confirmDelete(int $id): void
    {
        $c = BatteryCategory::findOrFail($id);
        $this->deletingId = $c->id;
        $this->deletingName = (string) $c->name;
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
        $c = BatteryCategory::findOrFail($id);
        $uploads->delete($c->img_url);
        $c->delete();
        $this->dispatch('toast', type: 'success', message: 'Battery category deleted.');
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
        $query = BatteryCategory::query()
            ->when(trim($this->search) !== '', fn ($q) => $q->where('name', 'like', '%'.trim($this->search).'%'))
            ->when($this->statusFilter === 'inactive', fn ($q) => $q->whereRaw('1 = 0'))
            ->orderByDesc('id');

        return view('livewire.battery.catalog.battery-category-list', [
            'categories' => $query->paginate($this->perPage),
        ]);
    }
}
