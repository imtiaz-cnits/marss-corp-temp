@php
    $statusLabels = [
        '' => 'All Status',
        'active' => 'Active',
        'inactive' => 'Inactive',
    ];
    $activeStatusLabel = $statusLabels[$statusFilter] ?? 'All Status';
@endphp

<div>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-300 dark:border-slate-800 shadow-xs overflow-hidden p-3 sm:p-5">
        {{-- 1. Top Section: Page Title & + Create Brand Button --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-3">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400 flex items-center justify-center border border-emerald-100 dark:border-slate-800 shadow-2xs shrink-0">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                        <line x1="7" y1="7" x2="7.01" y2="7"></line>
                    </svg>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-800 dark:text-white tracking-tight leading-none m-0">Battery Brand List</h1>
            </div>

            <div class="flex items-center flex-wrap gap-2">
                <button
                    type="button"
                    wire:click="openCreate"
                    class="inline-flex items-center gap-1.5 px-4 h-[38px] bg-[#15803d] hover:bg-[#166534] active:scale-[0.98] text-white text-sm font-semibold rounded-xl shadow-xs transition-all cursor-pointer"
                >
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>Create Brand</span>
                </button>
            </div>
        </div>

        {{-- 2. Controls & Filter Row: Search | SHOW: 15 | All Status --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-2.5 sm:gap-3 mb-4">
            <div class="unified-ui-border flex-1 h-[38px] flex items-center px-3 bg-white dark:bg-slate-800/90 rounded-xl shadow-2xs focus-within:border-[#15803d]">
                <svg class="w-4 h-4 text-slate-400 shrink-0 mr-2 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search Brand..."
                    class="w-full h-full bg-transparent border-0 outline-none text-xs sm:text-[13px] text-slate-800 dark:text-slate-100 placeholder:text-slate-400"
                >
            </div>

            <div class="flex items-center gap-2 sm:gap-2.5 w-full md:w-auto">
                {{-- SHOW Entries Selector --}}
                <div class="unified-ui-border h-[38px] px-3 rounded-xl bg-white dark:bg-slate-800/90 flex items-center justify-between gap-1.5 text-xs font-semibold text-slate-600 dark:text-slate-300 shadow-2xs shrink-0">
                    <span class="text-slate-400 text-[11px] font-bold uppercase tracking-wider whitespace-nowrap">SHOW:</span>
                    <select wire:model.live="perPage" class="no-custom-select bg-transparent border-0 text-xs sm:text-sm font-bold text-[#15803d] dark:text-emerald-400 focus:outline-none cursor-pointer pr-1">
                        <option value="15">15</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="200">200</option>
                        <option value="500">500</option>
                    </select>
                </div>

                {{-- Status Filter Custom Dropdown --}}
                <div x-data="{ open: false }" @click.outside="open = false" class="custom-filter-dropdown flex-1 md:w-[170px] relative">
                    <button
                        type="button"
                        @click="open = !open"
                        class="w-full unified-ui-border h-[38px] px-3 rounded-xl bg-white dark:bg-slate-800/90 text-xs sm:text-[13px] font-semibold text-slate-700 dark:text-slate-200 flex items-center justify-between gap-1.5 shadow-2xs cursor-pointer hover:border-emerald-500 transition-colors"
                    >
                        <span class="flex items-center gap-1.5 truncate">
                            <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                            <span class="truncate">{{ $activeStatusLabel }}</span>
                        </span>
                        <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transition-transform" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <div
                        x-show="open"
                        x-transition
                        style="display: none; right: 0; left: auto; min-width: 170px;"
                        class="select-menu"
                    >
                        @foreach($statusLabels as $val => $lbl)
                            <div
                                wire:click="setStatusFilter('{{ $val }}')"
                                @click="open = false"
                                class="select-option-item {{ $statusFilter === $val ? 'active' : '' }}"
                            >
                                <span>{{ $lbl }}</span>
                                @if($statusFilter === $val)
                                    <i class="fa-solid fa-check text-xs text-emerald-600"></i>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. Desktop Table (5 Columns: SL | Logo | Brand Name | Status | Action) --}}
        <div class="table-responsive unified-ui-border hidden md:block w-full overflow-x-auto rounded-2xl shadow-2xs bg-white dark:bg-slate-900">
            <table id="batteryBrandsTable" data-export-skip-last="true" class="w-full text-xs sm:text-sm text-left border-collapse">
                <thead>
                    <tr class="bg-[#15803d] text-white text-xs font-semibold uppercase tracking-wider whitespace-nowrap">
                        <th class="p-[10px] text-center w-[50px] rounded-tl-2xl">SL</th>
                        <th class="p-[10px] text-center w-[65px]">Logo</th>
                        <th class="p-[10px] text-start">Brand Name</th>
                        <th class="p-[10px] text-center w-[120px]">Status</th>
                        <th class="p-[10px] text-center w-[100px] rounded-tr-2xl">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-200">
                    @forelse($brands as $idx => $b)
                        @php
                            $hasLogo = $b->img_url && file_exists(public_path($b->img_url));
                            $logoUrl = $hasLogo ? asset($b->img_url) : asset('backend/assets/img/brand-defult-img.svg');
                        @endphp
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="p-[10px] text-center font-bold text-slate-400 dark:text-slate-500">{{ ($brands->firstItem() ?? 1) + $idx }}</td>
                            <td class="p-[10px] text-center">
                                <button
                                    type="button"
                                    wire:click="openImageModal('{{ $logoUrl }}', '{{ addslashes($b->name) }}')"
                                    class="inline-flex items-center justify-center w-[38px] h-[38px] rounded-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-2xs overflow-hidden mx-auto cursor-pointer hover:scale-105 transition-transform"
                                    title="Click to preview logo"
                                >
                                    <img src="{{ $logoUrl }}" alt="{{ $b->name }}" class="w-full h-full object-cover">
                                </button>
                            </td>
                            <td class="p-[10px] text-start">
                                <span class="font-bold text-slate-800 dark:text-slate-100 text-sm">{{ $b->name }}</span>
                            </td>
                            <td class="p-[10px] text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border shadow-2xs bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-slate-800">
                                    Active
                                </span>
                            </td>
                            <td class="p-[10px] text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button
                                        type="button"
                                        wire:click="openEdit({{ $b->id }})"
                                        class="w-[30px] h-[30px] rounded-lg bg-emerald-50 hover:bg-emerald-600 text-emerald-600 hover:text-white border border-emerald-200 dark:bg-emerald-950/40 dark:border-slate-800 dark:text-emerald-400 dark:hover:bg-emerald-600 dark:hover:text-white flex items-center justify-center transition-all shadow-2xs cursor-pointer"
                                        title="Edit Brand"
                                    >
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 20h9"></path>
                                            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                        </svg>
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="confirmDelete({{ $b->id }})"
                                        class="w-[30px] h-[30px] rounded-lg bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white border border-rose-200 dark:bg-rose-950/40 dark:border-slate-800 dark:text-rose-400 dark:hover:bg-rose-600 dark:hover:text-white flex items-center justify-center transition-all shadow-2xs cursor-pointer"
                                        title="Delete Brand"
                                    >
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr data-empty-row="true">
                            <td colspan="5" class="px-4 py-8 text-center text-rose-500 font-bold">❌ No Battery brands found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- 4. Mobile Card View --}}
        <div class="block md:hidden space-y-3">
            @forelse($brands as $idx => $b)
                @php
                    $hasLogo = $b->img_url && file_exists(public_path($b->img_url));
                    $logoUrl = $hasLogo ? asset($b->img_url) : asset('backend/assets/img/brand-defult-img.svg');
                @endphp
                <div class="unified-ui-border rounded-2xl p-3.5 bg-white dark:bg-slate-800/80 shadow-2xs">
                    <div class="flex items-center justify-between pb-2 mb-2.5 border-b border-slate-100 dark:border-slate-800">
                        <span class="px-2 py-0.5 rounded-md font-mono text-[11px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700">#{{ ($brands->firstItem() ?? 1) + $idx }}</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300">Active</span>
                    </div>
                    <div class="flex items-center gap-3 mb-2.5">
                        <img src="{{ $logoUrl }}" alt="{{ $b->name }}" wire:click="openImageModal('{{ $logoUrl }}', '{{ addslashes($b->name) }}')" class="w-10 h-10 rounded-full object-cover border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shrink-0 cursor-pointer">
                        <div>
                            <h6 class="font-bold text-slate-800 dark:text-slate-100 text-sm leading-snug">{{ $b->name }}</h6>
                            <span class="text-[11px] font-medium text-slate-400">Battery Brand</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-slate-800">
                        <span class="text-xs font-semibold text-slate-400">Actions:</span>
                        <div class="flex items-center gap-2">
                            <button type="button" wire:click="openEdit({{ $b->id }})" class="w-[32px] h-[32px] rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center cursor-pointer" title="Edit Brand">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                            </button>
                            <button type="button" wire:click="confirmDelete({{ $b->id }})" class="w-[32px] h-[32px] rounded-xl bg-rose-50 text-rose-600 border border-rose-200 flex items-center justify-center cursor-pointer" title="Delete Brand">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-rose-500 font-bold bg-white dark:bg-slate-800 rounded-2xl border border-slate-300 dark:border-slate-800">❌ No Battery brands found.</div>
            @endforelse
        </div>

        {{-- 5. Display Info & Pagination Footer --}}
        <div class="flex flex-col sm:flex-row items-center justify-between pt-4 mt-4 border-t border-slate-200 dark:border-slate-800 gap-3">
            <div class="px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1.5 flex-wrap shadow-2xs border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60">
                <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>Showing</span>
                <span class="px-1.5 py-0.5 rounded font-bold text-slate-800 dark:text-slate-100 bg-white dark:bg-slate-700">{{ $brands->firstItem() ?? 0 }} – {{ $brands->lastItem() ?? 0 }}</span>
                <span>of</span>
                <span class="px-1.5 py-0.5 rounded font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/50">{{ $brands->total() }}</span>
                <span>brands</span>
            </div>
            <div class="w-full sm:w-auto">
                {{ $brands->links() }}
            </div>
        </div>
    </div>

    {{-- Create / Edit Brand Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 bg-slate-900/65 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 overflow-y-auto" wire:click.self="closeModal">
            <div class="w-full max-w-[480px] max-h-[90vh] bg-white dark:bg-slate-900 rounded-2xl shadow-2xl overflow-hidden flex flex-col my-auto border-0">
                <div class="sticky top-0 z-20 px-4 py-3 bg-[#15803d] text-white flex items-center justify-between shadow-sm shrink-0">
                    <h5 class="text-base font-bold text-white flex items-center gap-2 m-0">
                        <i class="fa-solid fa-tag"></i> {{ $editingId ? 'Edit Battery Brand' : 'Create Battery Brand' }}
                    </h5>
                    <button type="button" wire:click="closeModal" class="w-6 h-6 rounded-full bg-red-500 hover:bg-red-600 text-white flex items-center justify-center border-0 shadow-sm cursor-pointer" title="Close">
                        <i class="fa-solid fa-xmark text-[10px]"></i>
                    </button>
                </div>

                <div class="p-5 overflow-y-auto flex-1 space-y-4">
                    <form wire:submit="save" id="batteryBrandForm" class="space-y-4">
                        {{-- Reference Parity: Dashed Upload Photo Box --}}
                        <div class="flex items-center gap-3.5 p-3.5 rounded-xl border-[1.5px] border-dashed border-slate-300 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/40">
                            <div class="w-16 h-16 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-2xs flex items-center justify-center overflow-hidden shrink-0">
                                @if($image)
                                    <img src="{{ $image->temporaryUrl() }}" class="w-full h-full object-contain">
                                @elseif($existingImage && file_exists(public_path($existingImage)))
                                    <img src="{{ asset($existingImage) }}" class="w-full h-full object-contain">
                                @else
                                    <i class="fa-regular fa-image text-2xl text-slate-400"></i>
                                @endif
                            </div>
                            <div>
                                <label for="batteryBrandLogoUpload" class="inline-flex items-center gap-2 px-3.5 h-9 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:border-emerald-500 hover:text-emerald-700 dark:hover:text-emerald-400 shadow-2xs cursor-pointer transition-colors">
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                    <span>Upload Photo</span>
                                </label>
                                <input id="batteryBrandLogoUpload" type="file" wire:model="image" accept="image/*" class="hidden">
                                <p class="text-[11px] text-slate-400 mt-1.5 mb-0">PNG, JPG or GIF (up to 1 MB)</p>
                                @error('image') <span class="text-xs text-rose-600 font-semibold block mt-0.5">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Brand Name <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="name" placeholder="Enter brand name..." class="w-full h-10 px-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm outline-none focus:border-[#15803d]">
                            @error('name') <span class="text-xs text-rose-600 font-semibold">{{ $message }}</span> @enderror
                        </div>
                    </form>
                </div>

                <div class="sticky bottom-0 z-20 px-4 py-2.5 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 flex items-center justify-end gap-2 shrink-0">
                    <button type="button" wire:click="closeModal" class="h-[38px] px-4 rounded-xl bg-[#dc2626] hover:bg-red-700 text-white text-xs sm:text-sm font-semibold cursor-pointer border-0">Cancel</button>
                    <button type="submit" form="batteryBrandForm" class="h-[38px] px-5 rounded-xl bg-[#15803d] hover:bg-[#166534] text-white text-xs sm:text-sm font-bold cursor-pointer border-0 shadow-xs">{{ $editingId ? 'Update' : 'Save' }}</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Reference Parity: Custom Delete Confirmation Modal --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 z-[70] bg-slate-900/65 backdrop-blur-xs flex items-center justify-center p-4" wire:click.self="closeModal">
            <div class="relative w-full max-w-[420px] bg-white dark:bg-slate-900 rounded-[20px] border border-slate-200 dark:border-slate-800 shadow-2xl px-6 pt-7 pb-6 text-center">
                <button type="button" wire:click="closeModal" class="absolute top-3.5 right-3.5 w-8 h-8 rounded-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-400 hover:text-slate-700 dark:hover:text-white flex items-center justify-center cursor-pointer" title="Close">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
                <div class="w-[68px] h-[68px] rounded-full bg-rose-50 dark:bg-rose-950/40 border-2 border-rose-100 dark:border-rose-900/60 text-red-600 dark:text-red-400 flex items-center justify-center mx-auto mb-4 shadow-sm">
                    <i class="fa-solid fa-trash-can text-[26px]"></i>
                </div>
                <h4 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Delete Brand?</h4>
                <p class="text-[13.5px] text-slate-500 dark:text-slate-400 leading-relaxed mb-5 px-2">
                    Are you sure you want to delete <strong class="text-slate-700 dark:text-slate-200">{{ $deletingName }}</strong>? This action cannot be undone.
                </p>
                <div class="flex items-center justify-center gap-3">
                    <button type="button" wire:click="closeModal" class="flex-1 h-[42px] rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-200 border border-slate-200 dark:border-slate-700 text-[13.5px] font-semibold cursor-pointer">Cancel</button>
                    <button type="button" wire:click="confirmDeleteAction" class="flex-1 h-[42px] rounded-xl bg-gradient-to-br from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white text-[13.5px] font-semibold shadow-md inline-flex items-center justify-center gap-2 border-0 cursor-pointer">
                        <i class="fa-solid fa-trash-can text-xs"></i>
                        <span>Yes, Delete</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Image Preview Popup Modal (Reference Parity) --}}
    @if($showImageModal && $viewingImageUrl)
        <div class="fixed inset-0 z-50 bg-slate-900/80 backdrop-blur-xs flex items-center justify-center p-4" wire:click.self="closeModal">
            <div class="relative flex items-center justify-center max-w-[90vw] max-h-[90vh]">
                <button type="button" wire:click="closeModal" title="Close" class="absolute -top-3.5 -right-3.5 w-[34px] h-[34px] rounded-full bg-[#dc2626] hover:bg-red-700 text-white flex items-center justify-center border-2 border-white shadow-xl cursor-pointer z-10">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
                <div class="rounded-2xl overflow-hidden bg-white p-1.5 border-2 border-white shadow-2xl max-w-[90vw] max-h-[90vh]">
                    <img src="{{ $viewingImageUrl }}" alt="{{ $viewingImageTitle }}" class="block max-w-[calc(90vw-24px)] max-h-[calc(90vh-24px)] object-contain rounded-xl">
                </div>
            </div>
        </div>
    @endif
</div>
