<?php

namespace App\Livewire\Retail\Investment;

use App\Models\Retail\RetailInvest;
use App\Models\Retail\RetailInvestorInfo;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Retail Investments')]
class RetailInvestList extends Component
{
    use WithPagination;

    public bool $showModal = false;

    public ?int $editingId = null;

    public ?int $investor_info_id = null;

    public string $invest_amount = '';

    public string $invest_details = '';

    public string $date = '';

    public function openCreate(): void
    {
        $this->reset(['editingId', 'investor_info_id', 'invest_amount', 'invest_details']);
        $this->date = Carbon::today()->toDateString();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $i = RetailInvest::findOrFail($id);
        $this->editingId = $i->id;
        $this->investor_info_id = $i->investor_info_id;
        $this->invest_amount = Money::round($i->invest_amount);
        $this->invest_details = (string) ($i->invest_details ?? '');
        $this->date = Carbon::parse($i->date ?? now())->toDateString();
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'investor_info_id' => ['required', 'integer', 'exists:investor_infos,id'],
            'invest_amount' => ['required', 'numeric', 'min:0.01'],
            'date' => ['required', 'date'],
        ]);

        RetailInvest::updateOrCreate(['id' => $this->editingId], [
            'investor_info_id' => $this->investor_info_id,
            'invest_amount' => Money::round($this->invest_amount),
            'invest_details' => trim($this->invest_details),
            'date' => $this->date,
            'user_id' => Auth::id(),
        ]);

        $this->showModal = false;
        $this->dispatch('toast', type: 'success', message: 'Investment saved!');
    }

    public function delete(int $id): void
    {
        RetailInvest::findOrFail($id)->delete();
        $this->dispatch('toast', type: 'success', message: 'Investment deleted.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function render()
    {
        return view('livewire.retail.investment.retail-invest-list', [
            'invests' => RetailInvest::with('investor')->orderByDesc('id')->paginate(20),
            'investors' => RetailInvestorInfo::orderBy('name')->get(),
            'totalInvested' => Money::round(RetailInvest::sum('invest_amount')),
        ]);
    }
}
