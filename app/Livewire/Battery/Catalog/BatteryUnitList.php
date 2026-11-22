<?php

namespace App\Livewire\Battery\Catalog;

use App\Models\Battery\BatteryUnit;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Battery Units')]
class BatteryUnitList extends Component
{
    use WithPagination;

    public bool $showModal = false;

    public bool $showDeleteModal = false;

    public ?int $deletingId = null;

    public string $deletingName = '';

    public ?int $editingId = null;

    public string $unit_name = '';

    public function openCreate(): void
    {
        $this->reset(['editingId', 'unit_name']);
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $u = BatteryUnit::findOrFail($id);
        $this->editingId = $u->id;
        $this->unit_name = (string) $u->unit_name;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate(['unit_name' => ['required', 'string', 'max:100']]);
        BatteryUnit::updateOrCreate(['id' => $this->editingId], [
            'unit_name' => trim($this->unit_name),
            'user_id' => Auth::id(),
        ]);
        $this->showModal = false;
        $this->dispatch('toast', type: 'success', message: 'Battery unit saved!');
    }

    public function confirmDelete(int $id): void
    {
        $u = BatteryUnit::findOrFail($id);
        $this->deletingId = $u->id;
        $this->deletingName = (string) $u->unit_name;
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
        BatteryUnit::findOrFail($id)->delete();
        $this->dispatch('toast', type: 'success', message: 'Unit deleted.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->showDeleteModal = false;
        $this->deletingId = null;
        $this->deletingName = '';
    }

    public function render()
    {
        return view('livewire.battery.catalog.battery-unit-list', [
            'units' => BatteryUnit::orderByDesc('id')->paginate(15),
        ]);
    }
}
