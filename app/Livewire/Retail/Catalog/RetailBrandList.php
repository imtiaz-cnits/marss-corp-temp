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

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $status = 'active';

    public $logo;

    public ?string $existingLogo = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->reset(['editingId', 'name', 'logo', 'existingLogo']);
        $this->status = 'active';
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $b = RetailBrand::findOrFail($id);
        $this->editingId = $b->id;
        $this->name = (string) $b->name;
        $this->status = (string) ($b->status ?: 'active');
        $this->existingLogo = $b->logo;
        $this->logo = null;
        $this->showModal = true;
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
    }

    public function render()
    {
        return view('livewire.retail.catalog.retail-brand-list', [
            'brands' => RetailBrand::query()
                ->when($this->search !== '', fn ($q) => $q->where('name', 'like', '%'.trim($this->search).'%'))
                ->orderByDesc('id')
                ->paginate(15),
        ]);
    }
}
