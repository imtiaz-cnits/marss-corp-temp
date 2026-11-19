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

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $type_name = '';

    public function openCreate(): void
    {
        $this->reset(['editingId', 'type_name']);
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $t = BatteryExpenseType::findOrFail($id);
        $this->editingId = $t->id;
        $this->type_name = (string) $t->type_name;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate(['type_name' => ['required', 'string', 'max:150']]);
        // Fixes FIN-09: battery_expense_types has NO status column!
        BatteryExpenseType::updateOrCreate(['id' => $this->editingId], [
            'type_name' => trim($this->type_name),
            'user_id' => Auth::id(),
        ]);
        $this->showModal = false;
        $this->dispatch('toast', type: 'success', message: 'Battery expense type saved!');
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
            'types' => BatteryExpenseType::orderByDesc('id')->paginate(15),
        ]);
    }
}
