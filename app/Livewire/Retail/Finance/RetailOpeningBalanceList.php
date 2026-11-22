<?php

namespace App\Livewire\Retail\Finance;

use App\Models\Retail\RetailOpeningBalance;
use App\Services\Shared\ActivityLogger;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Retail Opening Balance')]
class RetailOpeningBalanceList extends Component
{
    use WithPagination;

    public bool $showModal = false;

    public bool $showDeleteModal = false;

    public ?int $deletingId = null;

    public string $deletingLabel = '';

    public ?int $editingId = null;

    public string $date = '';

    public string $amount = '0';

    public string $note = '';

    public function openCreate(): void
    {
        $this->reset(['editingId', 'note']);
        $this->date = Carbon::today()->toDateString();
        $this->amount = '0';
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $ob = RetailOpeningBalance::findOrFail($id);
        $this->editingId = $ob->id;
        $this->date = Carbon::parse($ob->date)->toDateString();
        $this->amount = Money::round($ob->amount);
        $this->note = (string) ($ob->note ?? '');
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'date' => ['required', 'date'],
            'amount' => ['required', 'numeric'],
        ]);

        $ob = RetailOpeningBalance::updateOrCreate(['id' => $this->editingId], [
            'date' => $this->date,
            'amount' => Money::round($this->amount),
            'note' => trim($this->note),
            'user_id' => Auth::id(),
        ]);

        ActivityLogger::log($this->editingId ? 'Update' : 'Create', 'Retail / Opening Balance', "Set Retail opening balance for {$ob->date}: ৳{$ob->amount}.");
        $this->showModal = false;
        $this->dispatch('toast', type: 'success', message: 'Opening balance saved!');
    }

    public function confirmDelete(int $id): void
    {
        $ob = RetailOpeningBalance::findOrFail($id);
        $this->deletingId = $ob->id;
        $this->deletingLabel = "opening balance for " . Carbon::parse($ob->date)->format('d-m-Y') . " (৳" . Money::round($ob->amount) . ")";
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
        RetailOpeningBalance::findOrFail($id)->delete();
        $this->dispatch('toast', type: 'success', message: 'Opening balance deleted.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function render()
    {
        return view('livewire.retail.finance.retail-opening-balance-list', [
            'rows' => RetailOpeningBalance::orderByDesc('date')->orderByDesc('id')->paginate(20),
        ]);
    }
}
