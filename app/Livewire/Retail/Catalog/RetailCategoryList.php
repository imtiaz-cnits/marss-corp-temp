<?php

namespace App\Livewire\Retail\Catalog;

use App\Models\Retail\RetailCategory;
use App\Services\Shared\SafeImageUploadService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Title('Retail Categories')]
class RetailCategoryList extends Component
{
    use WithFileUploads, WithPagination;

    public string $search = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $category_name = '';

    public string $status = 'active';

    public $image;

    public ?string $existingImage = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->reset(['editingId', 'category_name', 'image', 'existingImage']);
        $this->status = 'active';
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $c = RetailCategory::findOrFail($id);
        $this->editingId = $c->id;
        $this->category_name = (string) $c->category_name;
        $this->status = (string) ($c->status ?: 'active');
        $this->existingImage = $c->img_url;
        $this->image = null;
        $this->showModal = true;
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
    }

    public function render()
    {
        return view('livewire.retail.catalog.retail-category-list', [
            'categories' => RetailCategory::query()
                ->when($this->search !== '', fn ($q) => $q->where('category_name', 'like', '%'.trim($this->search).'%'))
                ->orderByDesc('id')
                ->paginate(15),
        ]);
    }
}
