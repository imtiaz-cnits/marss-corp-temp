<?php

namespace App\Livewire\Battery\Expense;

use App\Models\Battery\BatteryExpenseType;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Battery Expense Types')]
class BatteryExpenseTypeList extends Component
{
    use WithPagination;

    public string $search = '';

    public int $perPage = 15;

    public bool $showModal = false;

    public bool $showDeleteModal = false;

    public ?int $deletingId = null;

    public string $deletingLabel = '';

    public ?int $editingId = null;

    public string $type_name = '';

    public string $status = 'active';

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
        $this->reset(['editingId', 'type_name']);
        $this->status = 'active';
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $t = BatteryExpenseType::findOrFail($id);
        $this->editingId = $t->id;
        $this->type_name = (string) $t->type_name;
        $this->status = 'active';
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate(['type_name' => ['required', 'string', 'max:150']]);
        $payload = [
            'type_name' => trim($this->type_name),
            'user_id' => Auth::id(),
        ];
        if (\Illuminate\Support\Facades\Schema::hasColumn('battery_expense_types', 'status')) {
            $payload['status'] = $this->status;
        }
        BatteryExpenseType::updateOrCreate(['id' => $this->editingId], $payload);
        $this->showModal = false;
        $this->dispatch('toast', type: 'success', message: 'Battery expense type saved!');
    }

    public function confirmDelete(int $id): void
    {
        $t = BatteryExpenseType::findOrFail($id);
        $this->deletingId = $t->id;
        $this->deletingLabel = "expense type '{$t->type_name}'";
        $this->showDeleteModal = true;
    }

    public function executeDelete(): void
    {
        if ($this->deletingId) {
            $this->delete($this->deletingId);
            $this->showDeleteModal = false;
            $this->deletingId = null;
            $this->deletingLabel = '';
        }
    }

    public function delete(int $id): void
    {
        BatteryExpenseType::findOrFail($id)->delete();
        $this->dispatch('toast', type: 'success', message: 'Deleted.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function render()
    {
        return view('livewire.battery.expense.battery-expense-type-list', [
            'types' => BatteryExpenseType::query()
                ->when($this->search !== '', fn ($q) => $q->where('type_name', 'like', '%'.trim($this->search).'%'))
                ->orderByDesc('id')
                ->paginate($this->perPage),
        ]);
    }
}
