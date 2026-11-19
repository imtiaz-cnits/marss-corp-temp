<?php

namespace App\Livewire\Retail\Investment;

use App\Models\Retail\RetailInvestorInfo;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Retail Investor Directory')]
class RetailInvestorInfoList extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $mobile = '';

    public string $email = '';

    public string $address = '';

    public string $status = 'active';

    public function openCreate(): void
    {
        $this->reset(['editingId', 'name', 'mobile', 'email', 'address']);
        $this->status = 'active';
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $inv = RetailInvestorInfo::findOrFail($id);
        $this->editingId = $inv->id;
        $this->name = (string) $inv->name;
        $this->mobile = (string) ($inv->mobile ?? '');
        $this->email = (string) ($inv->email ?? '');
        $this->address = (string) ($inv->address ?? '');
        $this->status = (string) ($inv->status ?: 'active');
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate(['name' => ['required', 'string', 'max:150']]);

        $data = [
            'name' => trim($this->name),
            'mobile' => trim($this->mobile),
            'email' => trim($this->email),
            'address' => trim($this->address),
            'status' => $this->status,
            'user_id' => Auth::id(),
        ];
        if (! $this->editingId) {
            $max = (int) (RetailInvestorInfo::max('id') ?? 0);
            $data['investor_id'] = '#InvInfo'.str_pad((string) ($max + 1), 4, '0', STR_PAD_LEFT);
        }

        RetailInvestorInfo::updateOrCreate(['id' => $this->editingId], $data);
        $this->showModal = false;
        $this->dispatch('toast', type: 'success', message: 'Investor saved!');
    }

    public function delete(int $id): void
    {
        RetailInvestorInfo::findOrFail($id)->delete();
        $this->dispatch('toast', type: 'success', message: 'Investor deleted.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function render()
    {
        return view('livewire.retail.investment.retail-investor-info-list', [
            'investors' => RetailInvestorInfo::withSum('invests', 'invest_amount')
                ->when($this->search !== '', fn ($q) => $q->where('name', 'like', '%'.trim($this->search).'%'))
                ->orderByDesc('id')
                ->paginate(15),
        ]);
    }
}
