<?php

namespace App\Livewire\Shared\Location;

use App\Models\Shared\District;
use App\Models\Shared\Location;
use App\Models\Shared\Thana;
use App\Models\Shared\Upazila;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Geographic Masters')]
class LocationManagerPage extends Component
{
    use WithPagination;

    public string $tab = 'districts';

    public string $search = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $status = 'active';

    public ?int $district_id = null;

    public ?int $upazila_id = null;

    public function mount(): void
    {
        $route = request()->route()?->getName();
        $this->tab = match ($route) {
            'settings.upazilas' => 'upazilas',
            'settings.thanas' => 'thanas',
            'settings.locations' => 'locations',
            default => 'districts',
        };
    }

    public function switchTab(string $tab): void
    {
        $this->tab = in_array($tab, ['districts', 'upazilas', 'thanas', 'locations'], true) ? $tab : 'districts';
        $this->search = '';
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->reset(['editingId', 'name', 'district_id', 'upazila_id']);
        $this->status = 'active';
        $this->resetValidation();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $this->editingId = $id;
        $this->resetValidation();

        if ($this->tab === 'districts') {
            $m = District::findOrFail($id);
            $this->name = (string) $m->district_name;
            $this->status = (string) ($m->status ?: 'active');
        } elseif ($this->tab === 'upazilas') {
            $m = Upazila::findOrFail($id);
            $this->name = (string) $m->upazila_name;
            $this->district_id = $m->district_id;
            $this->status = (string) ($m->status ?: 'active');
        } elseif ($this->tab === 'thanas') {
            $m = Thana::findOrFail($id);
            $this->name = (string) $m->Thana_name;
            $this->district_id = $m->district_id;
            $this->upazila_id = $m->upazila_id;
            $this->status = (string) ($m->status ?: 'active');
        } else {
            $m = Location::findOrFail($id);
            $this->name = (string) $m->name;
            $this->status = (string) ($m->status ?: 'active');
        }

        $this->showModal = true;
    }

    public function save(): void
    {
        $rules = [
            'name' => ['required', 'string', 'max:150'],
            'status' => ['required', 'string', 'max:30'],
        ];
        if (in_array($this->tab, ['upazilas', 'thanas'], true)) {
            $rules['district_id'] = ['required', 'integer', 'exists:districts,id'];
        }
        if ($this->tab === 'thanas') {
            $rules['upazila_id'] = ['nullable', 'integer', 'exists:upazilas,id'];
        }
        $this->validate($rules);

        $uid = (int) Auth::id();

        if ($this->tab === 'districts') {
            District::updateOrCreate(['id' => $this->editingId], [
                'district_name' => trim($this->name),
                'status' => $this->status,
                'user_id' => $uid,
            ]);
        } elseif ($this->tab === 'upazilas') {
            Upazila::updateOrCreate(['id' => $this->editingId], [
                'upazila_name' => trim($this->name),
                'district_id' => $this->district_id,
                'status' => $this->status,
                'user_id' => $uid,
            ]);
        } elseif ($this->tab === 'thanas') {
            Thana::updateOrCreate(['id' => $this->editingId], [
                'Thana_name' => trim($this->name),
                'district_id' => $this->district_id,
                'upazila_id' => $this->upazila_id,
                'status' => $this->status,
                'user_id' => $uid,
            ]);
        } else {
            Location::updateOrCreate(['id' => $this->editingId], [
                'name' => trim($this->name),
                'status' => $this->status,
                'user_id' => $uid,
            ]);
        }

        $this->showModal = false;
        $this->dispatch('toast', type: 'success', message: 'Record saved successfully!');
    }

    public function delete(int $id): void
    {
        match ($this->tab) {
            'districts' => District::findOrFail($id)->delete(),
            'upazilas' => Upazila::findOrFail($id)->delete(),
            'thanas' => Thana::findOrFail($id)->delete(),
            default => Location::findOrFail($id)->delete(),
        };

        $this->dispatch('toast', type: 'success', message: 'Record deleted.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function render()
    {
        $term = '%'.trim($this->search).'%';

        $items = match ($this->tab) {
            'upazilas' => Upazila::with('district')
                ->when($this->search, fn ($q) => $q->where('upazila_name', 'like', $term))
                ->orderByDesc('id')->paginate(15),
            'thanas' => Thana::with(['district', 'upazila'])
                ->when($this->search, fn ($q) => $q->where('Thana_name', 'like', $term))
                ->orderByDesc('id')->paginate(15),
            'locations' => Location::query()
                ->when($this->search, fn ($q) => $q->where('name', 'like', $term))
                ->orderByDesc('id')->paginate(15),
            default => District::query()
                ->when($this->search, fn ($q) => $q->where('district_name', 'like', $term))
                ->orderByDesc('id')->paginate(15),
        };

        return view('livewire.shared.location.location-manager-page', [
            'items' => $items,
            'districts' => District::orderBy('district_name')->get(),
            'upazilas' => $this->district_id
                ? Upazila::where('district_id', $this->district_id)->orderBy('upazila_name')->get()
                : Upazila::orderBy('upazila_name')->get(),
        ]);
    }
}
