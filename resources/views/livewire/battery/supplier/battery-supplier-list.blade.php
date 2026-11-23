@php
    use App\Support\Money;
    $defaultSupplierImg = asset('backend/assets/img/demo-img.jpeg');
@endphp

<div>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-300 dark:border-slate-800 shadow-xs overflow-hidden p-3 sm:p-5">
        {{-- 1. Top Section: Page Title & + Create Supplier Button --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-3">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400 flex items-center justify-center border border-emerald-100 dark:border-slate-800 shadow-2xs shrink-0">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="1" y="3" width="15" height="13"></rect>
                        <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                        <circle cx="5.5" cy="18.5" r="2.5"></circle>
                        <circle cx="18.5" cy="18.5" r="2.5"></circle>
                    </svg>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-800 dark:text-white tracking-tight leading-none m-0">Supplier List</h1>
            </div>

            <div class="flex items-center flex-wrap gap-2">
                <button
                    type="button"
                    wire:click="openCreate"
                    class="inline-flex items-center gap-1.5 px-4 h-[38px] bg-[#15803d] hover:bg-[#166534] active:scale-[0.98] text-white text-sm font-semibold rounded-xl shadow-xs transition-all cursor-pointer border-0"
                >
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>Create Supplier</span>
                </button>
            </div>
        </div>

        {{-- 2. Controls Row: Search | SHOW: 15 --}}
        <div class="flex flex-row items-center justify-between gap-2 sm:gap-3.5 mb-4">
            <div class="unified-ui-border flex-1 min-w-0 h-[38px] flex items-center px-3 py-2 bg-white dark:bg-slate-800/90 rounded-xl shadow-2xs focus-within:border-[#15803d]">
                <svg class="w-4 h-4 text-slate-400 shrink-0 mr-2 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search Supplier..."
                    class="w-full h-full bg-transparent border-0 outline-none text-xs sm:text-sm text-slate-800 dark:text-slate-100 placeholder:text-slate-400"
                >
            </div>

            <div class="unified-ui-border h-[38px] px-3 rounded-xl bg-white dark:bg-slate-800/90 flex items-center gap-1.5 text-xs font-semibold text-slate-600 dark:text-slate-300 shadow-2xs shrink-0">
                <span class="text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider whitespace-nowrap">SHOW:</span>
                <select wire:model.live="perPage" class="no-custom-select bg-transparent border-0 text-xs sm:text-sm font-bold text-[#15803d] dark:text-emerald-400 focus:outline-none cursor-pointer pr-1">
                    <option value="15">15</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="200">200</option>
                    <option value="500">500</option>
                </select>
            </div>
        </div>

        {{-- 3. Desktop Table (8 Columns Reference Parity) --}}
        <div class="table-responsive unified-ui-border hidden lg:block w-full overflow-x-auto rounded-2xl shadow-2xs bg-white dark:bg-slate-900">
            <table id="batterySuppliersTable" data-export-skip-last="true" class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#15803d] text-white text-xs font-semibold uppercase tracking-wider whitespace-nowrap">
                        <th class="p-[10px] text-center w-[50px] rounded-tl-2xl">SL</th>
                        <th class="p-[10px] text-center w-[65px]">Image</th>
                        <th class="p-[10px] text-start">Supplier ID</th>
                        <th class="p-[10px] text-start">Name</th>
                        <th class="p-[10px] text-start">Company</th>
                        <th class="p-[10px] text-end">Payable Amount</th>
                        <th class="p-[10px] text-center w-[100px]">Status</th>
                        <th class="p-[10px] text-center w-[90px] rounded-tr-2xl">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm text-slate-700 dark:text-slate-200">
                    @forelse($suppliers as $idx => $s)
                        @php
                            $payable = (float) ($s->purchase_payable_amount ?? 0);
                            $hasImg = $s->img_url && file_exists(public_path($s->img_url));
                            $imgUrl = $hasImg ? asset($s->img_url) : $defaultSupplierImg;
                        @endphp
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/60 transition-colors">
                            <td class="p-[10px] text-center font-semibold text-slate-500 dark:text-slate-400 text-xs">{{ ($suppliers->firstItem() ?? 1) + $idx }}</td>
                            <td class="p-[10px] text-center w-[65px]">
                                <div class="flex justify-center">
                                    <img
                                        src="{{ $imgUrl }}"
                                        alt="{{ $s->name }}"
                                        wire:click="openImageModal('{{ $imgUrl }}', '{{ addslashes($s->name) }}')"
                                        class="w-[38px] h-[38px] rounded-full object-cover border border-slate-200 dark:border-slate-700 shadow-2xs cursor-pointer hover:scale-105 transition-transform"
                                        title="Click to preview image"
                                    >
                                </div>
                            </td>
                            <td class="p-[10px] text-start whitespace-nowrap">
                                <a href="{{ route('battery.supplier.profile', $s->id) }}" wire:navigate class="inline-flex items-center gap-1 font-semibold text-emerald-600 dark:text-emerald-400 hover:underline text-xs whitespace-nowrap" title="View Supplier Profile">
                                    <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="1" y="3" width="15" height="13"></rect>
                                        <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                                        <circle cx="5.5" cy="18.5" r="2.5"></circle>
                                        <circle cx="18.5" cy="18.5" r="2.5"></circle>
                                    </svg>
                                    <span>{{ $s->supplier_id ?: ('BAT-SUP-'.$s->id) }}</span>
                                </a>
                            </td>
                            <td class="p-[10px] text-start whitespace-nowrap">
                                <a href="{{ route('battery.supplier.profile', $s->id) }}" wire:navigate class="font-bold text-slate-800 dark:text-slate-100 hover:text-emerald-600 transition-colors">{{ $s->name }}</a>
                            </td>
                            <td class="p-[10px] text-start whitespace-nowrap text-slate-600 dark:text-slate-400 font-medium">{{ $s->company ?: '-' }}</td>
                            <td class="p-[10px] text-end font-bold whitespace-nowrap tabular-nums {{ $payable > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-700 dark:text-slate-300' }}">{{ Money::formatBd($payable) }}</td>
                            <td class="p-[10px] text-center w-[100px] whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-800/40">
                                    Active
                                </span>
                            </td>
                            <td class="p-[10px] text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button
                                        type="button"
                                        wire:click="openEdit({{ $s->id }})"
                                        class="w-[30px] h-[30px] rounded-lg bg-emerald-50 hover:bg-emerald-600 text-emerald-600 hover:text-white border border-emerald-200/80 hover:border-emerald-600 dark:bg-emerald-950/40 dark:border-slate-800 dark:text-emerald-400 dark:hover:bg-emerald-600 dark:hover:text-white flex items-center justify-center transition-all shadow-2xs cursor-pointer"
                                        title="Edit Supplier"
                                    >
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 20h9"></path>
                                            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                        </svg>
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="confirmDelete({{ $s->id }})"
                                        class="w-[30px] h-[30px] rounded-lg bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white border border-rose-200/80 hover:border-rose-600 dark:bg-rose-950/40 dark:border-slate-800 dark:text-rose-400 dark:hover:bg-rose-600 dark:hover:text-white flex items-center justify-center transition-all shadow-2xs cursor-pointer"
                                        title="Delete Supplier"
                                    >
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            <line x1="10" y1="11" x2="10" y2="17"></line>
                                            <line x1="14" y1="11" x2="14" y2="17"></line>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr data-empty-row="true">
                            <td colspan="8" class="px-4 py-8 text-center text-rose-500 font-bold">❌ No Battery suppliers found.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50/80 dark:bg-slate-800/80 font-bold text-sm border-t border-slate-200 dark:border-slate-800">
                        <td colspan="5" class="p-[10px] text-end font-bold text-slate-700 dark:text-slate-200">Total Payable:</td>
                        <td class="p-[10px] text-end font-bold text-rose-600 dark:text-rose-400 whitespace-nowrap tabular-nums">{{ Money::formatBd($totalPayable) }}</td>
                        <td colspan="2" class="p-[10px]"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- 4. Mobile & Tablet Card View (1-col Mobile, 2-col Tablet) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:hidden gap-3 mb-3">
            @forelse($suppliers as $idx => $s)
                @php
                    $payable = (float) ($s->purchase_payable_amount ?? 0);
                    $hasImg = $s->img_url && file_exists(public_path($s->img_url));
                    $imgUrl = $hasImg ? asset($s->img_url) : $defaultSupplierImg;
                @endphp
                <div class="unified-ui-border rounded-2xl p-3.5 bg-white dark:bg-slate-800/60 shadow-2xs">
                    <div class="flex items-center justify-between pb-2 mb-2.5 border-b border-slate-100 dark:border-slate-700/60">
                        <div class="flex items-center gap-1.5">
                            <span class="px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-[11px] font-bold">#{{ ($suppliers->firstItem() ?? 1) + $idx }}</span>
                            <a href="{{ route('battery.supplier.profile', $s->id) }}" wire:navigate class="px-2 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/40 text-emerald-700 dark:text-emerald-400 text-xs font-bold inline-flex items-center gap-1">
                                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
                                {{ $s->supplier_id ?: ('BAT-SUP-'.$s->id) }}
                            </a>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400">Active</span>
                    </div>

                    <div class="flex items-center gap-3 mb-2.5">
                        <img src="{{ $imgUrl }}" alt="{{ $s->name }}" wire:click="openImageModal('{{ $imgUrl }}', '{{ addslashes($s->name) }}')" class="w-11 h-11 rounded-full object-cover border border-slate-200 dark:border-slate-700 shrink-0 cursor-pointer">
                        <div class="min-w-0">
                            <a href="{{ route('battery.supplier.profile', $s->id) }}" wire:navigate class="font-bold text-slate-800 dark:text-slate-100 text-sm block truncate hover:text-emerald-600">{{ $s->name }}</a>
                            <span class="text-slate-500 dark:text-slate-400 text-xs inline-flex items-center gap-1 truncate">
                                <i class="fa-solid fa-building text-[10px]"></i>
                                {{ $s->company ?: 'No Company' }}
                            </span>
                        </div>
                    </div>

                    <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700/60 my-2 flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Payable Due:</span>
                        <span class="font-bold text-sm whitespace-nowrap tabular-nums {{ $payable > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-800 dark:text-slate-100' }}">{{ Money::formatBd($payable) }}</span>
                    </div>

                    <div class="flex items-center justify-between pt-2.5 mt-1 border-t border-slate-100 dark:border-slate-700/60">
                        <a href="{{ route('battery.supplier.profile', $s->id) }}" wire:navigate class="inline-flex items-center gap-1 px-3 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 text-xs font-semibold rounded-lg border border-emerald-200 dark:border-emerald-800/40 transition-colors">
                            <i class="fa-regular fa-user text-[11px]"></i>
                            <span>Profile</span>
                        </a>
                        <div class="flex items-center gap-1.5">
                            <button type="button" wire:click="openEdit({{ $s->id }})" class="w-[30px] h-[30px] rounded-lg bg-emerald-50 hover:bg-emerald-600 text-emerald-600 hover:text-white border border-emerald-200 flex items-center justify-center cursor-pointer" title="Edit">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                            </button>
                            <button type="button" wire:click="confirmDelete({{ $s->id }})" class="w-[30px] h-[30px] rounded-lg bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white border border-rose-200 flex items-center justify-center cursor-pointer" title="Delete">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-rose-500 font-bold bg-white dark:bg-slate-800 rounded-2xl border border-slate-300 dark:border-slate-800">❌ No Battery suppliers found.</div>
            @endforelse
        </div>

        {{-- 5. Display Info & Pagination Footer --}}
        <div class="flex flex-col sm:flex-row items-center justify-between pt-4 mt-4 border-t border-slate-200 dark:border-slate-800 gap-3">
            <div class="flex items-center gap-1.5 text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400">
                <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                <span>Showing</span>
                <span class="font-bold text-slate-800 dark:text-slate-100 px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700">{{ $suppliers->firstItem() ?? 0 }} – {{ $suppliers->lastItem() ?? 0 }}</span>
                <span>of</span>
                <span class="font-bold text-emerald-700 dark:text-emerald-400 px-1.5 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800">{{ $suppliers->total() }}</span>
                <span>suppliers</span>
            </div>
            <div class="w-full sm:w-auto">
                {{ $suppliers->links() }}
            </div>
        </div>
    </div>

    {{-- Reference Parity: Add / Update Battery Supplier Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 bg-slate-900/65 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 overflow-y-auto" wire:click.self="closeModal">
            <div class="w-full max-w-[650px] max-h-[90vh] bg-white dark:bg-slate-900 rounded-2xl shadow-2xl overflow-hidden flex flex-col my-auto border border-slate-200 dark:border-slate-800">
                <div class="sticky top-0 z-20 bg-[#15803d] px-5 py-3.5 flex items-center justify-between shrink-0">
                    <h2 class="text-lg font-bold text-white m-0 leading-tight">{{ $editingId ? 'Update Supplier' : 'Add New Supplier' }}</h2>
                    <button type="button" wire:click="closeModal" class="w-7 h-7 rounded-full bg-[#dc2626] hover:bg-red-700 text-white flex items-center justify-center border-0 cursor-pointer shadow-sm" title="Close">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>

                <div class="p-5 sm:px-6 overflow-y-auto flex-1">
                    <form wire:submit="save" id="batterySupplierForm" class="space-y-3.5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block text-[13px] font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Supplier Name <span class="text-rose-500">*</span></label>
                                <input type="text" wire:model="name" placeholder="Enter Supplier Name" required class="w-full h-[42px] px-3.5 rounded-lg border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-[13.5px] text-slate-800 dark:text-slate-100 outline-none focus:border-[#15803d]">
                                @error('name') <span class="text-xs text-rose-600 font-semibold mt-0.5 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-[13px] font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Supplier Mobile <span class="text-rose-500">*</span></label>
                                <input type="text" wire:model="mobile" placeholder="Enter Supplier Mobile" required class="w-full h-[42px] px-3.5 rounded-lg border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-[13.5px] text-slate-800 dark:text-slate-100 outline-none focus:border-[#15803d]">
                                @error('mobile') <span class="text-xs text-rose-600 font-semibold mt-0.5 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-[13px] font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Supplier Company</label>
                                <input type="text" wire:model="company" placeholder="Enter Supplier Company" class="w-full h-[42px] px-3.5 rounded-lg border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-[13.5px] text-slate-800 dark:text-slate-100 outline-none focus:border-[#15803d]">
                            </div>
                            <div>
                                <label class="block text-[13px] font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Supplier Address</label>
                                <input type="text" wire:model="address" placeholder="Enter Supplier Address" class="w-full h-[42px] px-3.5 rounded-lg border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-[13.5px] text-slate-800 dark:text-slate-100 outline-none focus:border-[#15803d]">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-[13px] font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Purchase Payable Amount</label>
                                <input type="number" step="any" min="0" wire:model="purchase_payable_amount" placeholder="Enter Purchase Payable Amount" class="w-full h-[42px] px-3.5 rounded-lg border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-[13.5px] text-slate-800 dark:text-slate-100 outline-none focus:border-[#15803d]">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[13px] font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Supplier Image</label>
                            <div class="flex items-center gap-3.5">
                                <div class="w-[80px] h-[70px] min-w-[80px] rounded-lg bg-slate-100 dark:bg-slate-800 border-[1.5px] border-dashed border-slate-300 dark:border-slate-600 flex items-center justify-center overflow-hidden shrink-0">
                                    @if($image)
                                        <img src="{{ $image->temporaryUrl() }}" class="w-full h-full object-cover rounded-md">
                                    @elseif($existingImage && file_exists(public_path($existingImage)))
                                        <img src="{{ asset($existingImage) }}" class="w-full h-full object-cover rounded-md">
                                    @else
                                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                            <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                            <polyline points="21 15 16 10 5 21"></polyline>
                                        </svg>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <label for="batterySupplierImageUpload" class="w-full h-[46px] rounded-md bg-[#ededed] hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-200 text-[15px] font-medium flex items-center justify-center gap-2 cursor-pointer transition-colors">
                                        <i class="fa-solid fa-cloud-arrow-up text-sm"></i>
                                        <span>Upload Photo</span>
                                    </label>
                                    <input id="batterySupplierImageUpload" type="file" wire:model="image" accept="image/*" class="hidden">
                                    <p class="text-[11px] text-slate-400 mt-1.5 mb-0">PNG, JPEG or GIF (up to 1 MB)</p>
                                    @error('image') <span class="text-xs text-rose-600 font-semibold block mt-0.5">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="sticky bottom-0 z-20 px-6 py-3.5 bg-white dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800 shrink-0">
                    <button type="submit" form="batterySupplierForm" class="w-full h-[42px] bg-[#15803d] hover:bg-[#166534] text-white font-semibold text-[15px] rounded-lg border-0 cursor-pointer flex items-center justify-center transition-colors shadow-xs">
                        Submit
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Reference Parity: Custom Delete Confirmation Modal --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 z-[70] bg-slate-900/65 backdrop-blur-xs flex items-center justify-center p-4" wire:click.self="closeModal">
            <div class="w-full max-w-[440px] bg-white dark:bg-slate-900 rounded-2xl shadow-2xl overflow-hidden flex flex-col border border-slate-200 dark:border-slate-800">
                <div class="sticky top-0 z-20 px-5 py-3.5 bg-[#dc2626] text-white flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-white/20 text-white flex items-center justify-center">
                            <i class="fa-solid fa-trash-can text-sm"></i>
                        </div>
                        <h5 class="text-base font-bold text-white m-0">Delete Supplier</h5>
                    </div>
                    <button type="button" wire:click="closeModal" class="w-[30px] h-[30px] rounded-full bg-[#991b1b] hover:bg-red-900 text-white flex items-center justify-center border-0 cursor-pointer" title="Close">
                        <i class="fa-solid fa-xmark text-xs"></i>
                    </button>
                </div>
                <div class="p-5 text-center">
                    <div class="w-14 h-14 rounded-full bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 flex items-center justify-center mx-auto mb-3.5 border border-rose-100 dark:border-slate-800">
                        <i class="fa-solid fa-triangle-exclamation text-2xl"></i>
                    </div>
                    <h4 class="text-base sm:text-lg font-bold text-slate-800 dark:text-slate-100 mb-1.5">Confirm Deletion</h4>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mb-0">
                        Are you sure you want to delete <strong class="text-slate-700 dark:text-slate-200">{{ $deletingName }}</strong>? This action cannot be undone.
                    </p>
                </div>
                <div class="sticky bottom-0 z-20 px-5 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 flex items-center justify-center gap-2.5">
                    <button type="button" wire:click="closeModal" class="px-4 h-[38px] rounded-xl text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 unified-ui-border text-xs sm:text-sm font-semibold hover:bg-slate-100 dark:hover:bg-slate-700 cursor-pointer">Cancel</button>
                    <button type="button" wire:click="confirmDeleteAction" class="px-5 h-[38px] rounded-xl bg-[#dc2626] hover:bg-rose-700 text-white text-xs sm:text-sm font-semibold border-0 cursor-pointer shadow-xs">Yes, Delete</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Image Preview Quick View Modal --}}
    @if($showImageModal && $viewingImageUrl)
        <div class="fixed inset-0 z-[80] bg-slate-900/85 backdrop-blur-xs flex items-center justify-center p-4" wire:click.self="closeModal">
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
