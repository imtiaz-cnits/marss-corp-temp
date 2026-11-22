<?php

namespace App\Livewire\Battery\Expense;

use App\Models\Battery\BatteryExpense;
use App\Models\Battery\BatteryExpenseType;
use App\Models\Shared\User;
use App\Services\Shared\ActivityLogger;
use App\Support\DateFilter;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Expense & Salary Management')]
class BatteryExpenseList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $typeFilter = '';

    public string $startDate = '';

    public string $endDate = '';

    public int $perPage = 15;

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $date = '';

    // Multiple selection in Create Expense Modal (Image 2, 3, 4)
    /** @var array<int, int> */
    public array $selectedTypes = [];

    /** @var array<int, string> */
    public array $typeAmounts = [];

    /** @var array<int, string> */
    public array $typeNotes = [];

    /** @var array<int, int|null> */
    public array $typeStaff = [];

    // Details Sub-Modal (Image 3)
    public bool $showDetailsModal = false;

    public ?int $activeTypeId = null;

    public string $activeTypeName = '';

    public string $activeAmount = '';

    public string $activeNote = '';

    public ?int $activeStaffId = null;

    // Fallback / Single Edit fields
    public ?int $expense_type_id = null;

    public ?int $staff_id = null;

    public string $expense_amount = '';

    public string $expense_details = '';

    // Quick Type Sub-Modal
    public bool $showQuickTypeModal = false;

    public string $quickTypeName = '';

    public string $quickTypeStatus = 'active';

    // Quick Staff Sub-Modal
    public bool $showQuickStaffModal = false;

    public string $quickStaffName = '';

    public string $quickStaffEmail = '';

    public string $quickStaffPhone = '';

    public string $quickStaffRole = 'Staff';

    // Custom Delete Confirmation Modal
    public bool $showDeleteModal = false;

    public ?int $deletingId = null;

    public string $deletingLabel = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStartDate(): void
    {
        $this->resetPage();
    }

    public function updatingEndDate(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function updatingTypeFilter(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->reset([
            'editingId',
            'expense_type_id',
            'staff_id',
            'expense_amount',
            'expense_details',
            'selectedTypes',
            'typeAmounts',
            'typeNotes',
            'typeStaff',
            'showDetailsModal',
            'activeTypeId',
            'activeTypeName',
            'activeAmount',
            'activeNote',
            'activeStaffId',
        ]);
        $this->date = Carbon::today()->toDateString();
        $this->showModal = true;
    }

    public function openTypeDetailsModal(int $typeId): void
    {
        $type = BatteryExpenseType::find($typeId);
        if (! $type) {
            return;
        }

        $this->activeTypeId = $type->id;
        $this->activeTypeName = $type->type_name;
        $this->activeAmount = isset($this->typeAmounts[$typeId]) && (float) $this->typeAmounts[$typeId] > 0
            ? (string) $this->typeAmounts[$typeId]
            : '0.00';
        $this->activeNote = (string) ($this->typeNotes[$typeId] ?? '');
        $this->activeStaffId = $this->typeStaff[$typeId] ?? null;
        $this->showDetailsModal = true;
    }

    public function saveTypeDetails(): void
    {
        if (! is_numeric($this->activeAmount) || (float) $this->activeAmount <= 0) {
            $this->dispatch('toast', type: 'error', message: 'Please enter a valid amount greater than zero.');

            return;
        }

        if ($this->activeTypeId) {
            if (! in_array($this->activeTypeId, $this->selectedTypes, true)) {
                $this->selectedTypes[] = $this->activeTypeId;
            }
            $this->typeAmounts[$this->activeTypeId] = Money::round($this->activeAmount);
            $this->typeNotes[$this->activeTypeId] = trim($this->activeNote);
            $this->typeStaff[$this->activeTypeId] = $this->activeStaffId;
        }

        $this->showDetailsModal = false;
        $this->reset(['activeTypeId', 'activeTypeName', 'activeAmount', 'activeNote', 'activeStaffId']);
    }

    public function removeSelectedType(int $typeId): void
    {
        $this->selectedTypes = array_values(array_filter($this->selectedTypes, fn ($id) => $id !== $typeId));
        unset($this->typeAmounts[$typeId], $this->typeNotes[$typeId], $this->typeStaff[$typeId]);
    }

    public function closeDetailsModal(): void
    {
        $this->showDetailsModal = false;
        $this->reset(['activeTypeId', 'activeTypeName', 'activeAmount', 'activeNote', 'activeStaffId']);
    }

    public function openEdit(int $id): void
    {
        $e = BatteryExpense::findOrFail($id);
        $this->editingId = $e->id;
        $this->expense_type_id = $e->expense_type_id;
        $this->staff_id = $e->staff_id;
        $this->expense_amount = Money::round($e->expense_amount);
        $this->expense_details = (string) ($e->expense_details ?? '');
        $this->date = DateFilter::parseDateString($e->date) ?? Carbon::today()->toDateString();

        $this->selectedTypes = [$e->expense_type_id];
        $this->typeAmounts = [$e->expense_type_id => Money::round($e->expense_amount)];
        $this->typeNotes = [$e->expense_type_id => (string) ($e->expense_details ?? '')];
        $this->typeStaff = [$e->expense_type_id => $e->staff_id];

        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'date' => ['required', 'date'],
        ]);

        if ($this->editingId) {
            $typeId = ! empty($this->selectedTypes) ? (int) $this->selectedTypes[0] : (int) $this->expense_type_id;
            $amt = isset($this->typeAmounts[$typeId]) && trim((string) $this->typeAmounts[$typeId]) !== ''
                ? $this->typeAmounts[$typeId]
                : $this->expense_amount;

            if (! $typeId || ! is_numeric($amt) || (float) $amt <= 0) {
                $this->addError('expense_amount', 'Valid expense type and amount are required.');

                return;
            }

            $note = $this->typeNotes[$typeId] ?? $this->expense_details;
            $staffId = $this->typeStaff[$typeId] ?? $this->staff_id;

            $exp = BatteryExpense::findOrFail($this->editingId);
            $exp->update([
                'expense_type_id' => $typeId,
                'staff_id' => $staffId,
                'expense_amount' => Money::round($amt),
                'expense_details' => trim((string) $note),
                'date' => $this->date,
                'user_id' => Auth::id(),
            ]);

            ActivityLogger::log('Update', 'Battery / Expense', "Updated Battery expense ৳{$exp->expense_amount}.");
            $this->showModal = false;
            $this->dispatch('toast', type: 'success', message: 'Battery expense updated successfully!');

            return;
        }

        // Create mode: save each selected item
        $entriesToCreate = [];
        if (! empty($this->selectedTypes)) {
            foreach ($this->selectedTypes as $typeId) {
                $amt = $this->typeAmounts[$typeId] ?? '';
                if (is_numeric($amt) && (float) $amt > 0) {
                    $entriesToCreate[] = [
                        'expense_type_id' => $typeId,
                        'staff_id' => $this->typeStaff[$typeId] ?? null,
                        'expense_amount' => Money::round($amt),
                        'expense_details' => trim((string) ($this->typeNotes[$typeId] ?? '')),
                        'date' => $this->date,
                        'user_id' => Auth::id(),
                    ];
                }
            }
        } elseif ($this->expense_type_id && is_numeric($this->expense_amount) && (float) $this->expense_amount > 0) {
            $entriesToCreate[] = [
                'expense_type_id' => $this->expense_type_id,
                'staff_id' => $this->staff_id,
                'expense_amount' => Money::round($this->expense_amount),
                'expense_details' => trim((string) $this->expense_details),
                'date' => $this->date,
                'user_id' => Auth::id(),
            ];
        }

        if (empty($entriesToCreate)) {
            $this->addError('expense_amount', 'Please select at least one expense type and enter an amount.');

            return;
        }

        foreach ($entriesToCreate as $entry) {
            $created = BatteryExpense::create($entry);
            ActivityLogger::log('Create', 'Battery / Expense', "Recorded Battery expense ৳{$created->expense_amount}.");
        }

        $this->showModal = false;
        $count = count($entriesToCreate);
        $this->dispatch('toast', type: 'success', message: $count > 1 ? "{$count} Expenses recorded successfully!" : 'Expense recorded successfully!');
    }

    public function openQuickType(): void
    {
        $this->reset(['quickTypeName']);
        $this->quickTypeStatus = 'active';
        $this->showQuickTypeModal = true;
    }

    public function saveQuickType(): void
    {
        $this->validate([
            'quickTypeName' => ['required', 'string', 'max:150'],
        ]);

        $payload = [
            'type_name' => trim($this->quickTypeName),
            'user_id' => Auth::id(),
        ];
        if (Schema::hasColumn('battery_expense_types', 'status')) {
            $payload['status'] = $this->quickTypeStatus;
        }

        $newType = BatteryExpenseType::create($payload);

        $this->showQuickTypeModal = false;
        $this->openTypeDetailsModal($newType->id);
        $this->dispatch('toast', type: 'success', message: 'New expense type created! Now enter the amount.');
    }

    public function openQuickStaff(): void
    {
        $this->reset(['quickStaffName', 'quickStaffEmail', 'quickStaffPhone']);
        $this->quickStaffRole = 'Staff';
        $this->showQuickStaffModal = true;
    }

    public function saveQuickStaff(): void
    {
        $this->validate([
            'quickStaffName' => ['required', 'string', 'max:150'],
        ]);

        $email = trim($this->quickStaffEmail) ?: 'staff_'.Str::random(6).'@marss.local';

        User::create([
            'name' => trim($this->quickStaffName),
            'email' => $email,
            'mobile' => trim($this->quickStaffPhone),
            'role' => $this->quickStaffRole,
            'password' => Hash::make(Str::random(16)),
            'status' => 'active',
        ]);

        $this->showQuickStaffModal = false;
        $this->dispatch('toast', type: 'success', message: 'New staff added successfully!');
    }

    public function confirmDelete(int $id): void
    {
        $e = BatteryExpense::with('expenseType')->findOrFail($id);
        $this->deletingId = $e->id;
        $this->deletingLabel = "expense entry for ".Money::formatBd($e->expense_amount)." (".($e->expenseType?->type_name ?? 'Expense').")";
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
        $e = BatteryExpense::findOrFail($id);
        ActivityLogger::log('Delete', 'Battery / Expense', "Deleted Battery expense ৳{$e->expense_amount}.");
        $e->delete();
        $this->dispatch('toast', type: 'success', message: 'Deleted.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function render()
    {
        $query = BatteryExpense::with(['expenseType', 'staff'])->orderByDesc('id');

        if (trim($this->search) !== '') {
            $t = '%'.trim($this->search).'%';
            $query->where(function ($q) use ($t) {
                $q->where('expense_details', 'like', $t)
                    ->orWhereHas('expenseType', fn ($eq) => $eq->where('type_name', 'like', $t))
                    ->orWhereHas('staff', fn ($sq) => $sq->where('name', 'like', $t));
            });
        }

        if ($this->typeFilter !== '') {
            $query->where('expense_type_id', (int) $this->typeFilter);
        }

        if ($this->startDate || $this->endDate) {
            DateFilter::applyDateFilter($query, 'date', $this->startDate ?: null, $this->endDate ?: null);
        }

        // Metrics matching 1:1 reference
        $totalExpenses = Money::round(BatteryExpense::sum('expense_amount'));
        $todayExpense = Money::round(BatteryExpense::whereDate('date', Carbon::today())->sum('expense_amount'));
        $monthExpense = Money::round(BatteryExpense::whereMonth('date', Carbon::now()->month)->whereYear('date', Carbon::now()->year)->sum('expense_amount'));
        $staffTotalSalary = Money::round(BatteryExpense::whereNotNull('staff_id')->sum('expense_amount'));

        return view('livewire.battery.expense.battery-expense-list', [
            'expenses' => $query->paginate($this->perPage),
            'types' => BatteryExpenseType::orderBy('type_name')->get(),
            'staffMembers' => User::visible()->orderBy('name')->get(['id', 'name', 'role']),
            'totalExpenses' => $totalExpenses,
            'todayExpense' => $todayExpense,
            'monthExpense' => $monthExpense,
            'staffTotalSalary' => $staffTotalSalary,
        ]);
    }
}
