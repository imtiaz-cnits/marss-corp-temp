@php
    use App\Support\Money;
    $defaultCustomerImg = asset('backend/assets/img/customer-placeholder.svg');
@endphp

<div class="space-y-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-300 dark:border-slate-800 shadow-sm overflow-hidden transition-colors">
        <div class="p-3 sm:p-5 md:p-6">
            {{-- 1. Top Section: Page Title & + Create Customer Button --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400 flex items-center justify-center border border-emerald-100 dark:border-slate-800 shadow-xs shrink-0">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-800 dark:text-white tracking-tight leading-none m-0">Customer List</h1>
                </div>

                <div class="flex items-center flex-wrap gap-2">
                    <button
                        type="button"
                        wire:click="openCreate"
                        class="inline-flex items-center gap-1.5 px-4 h-[38px] bg-[#15803d] hover:bg-[#166534] active:scale-[0.98] text-white text-sm font-semibold rounded-xl shadow-sm transition-all border-0 cursor-pointer"
                    >
                        <i class="fa-solid fa-plus text-xs"></i>
                        <span>Create Customer</span>
                    </button>
                </div>
            </div>

            {{-- 2. Controls & Filter Row: Search Bar + Show Entry --}}
            <div class="flex items-center gap-2.5 mb-4">
                <div class="flex-1 md:flex-none md:w-[300px] lg:w-[340px] h-[38px] flex items-center px-3 bg-white dark:bg-slate-800/90 rounded-xl unified-ui-border shadow-2xs focus-within:border-emerald-600 focus-within:ring-2 focus-within:ring-emerald-600/20 transition-all">
                    <i class="fa-solid fa-magnifying-glass text-slate-400 text-xs mr-2.5 shrink-0"></i>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search Customer..."
                        class="w-full h-full bg-transparent border-0 outline-none text-xs sm:text-sm text-slate-800 dark:text-slate-100 placeholder:text-slate-400 focus:ring-0"
                    >
                </div>

                <div class="h-[38px] flex items-center gap-1.5 bg-white dark:bg-slate-800/90 px-3 rounded-xl unified-ui-border text-xs font-semibold text-slate-600 dark:text-slate-300 shadow-2xs hover:border-emerald-500 transition-all shrink-0">
                    <span class="text-slate-400 dark:text-slate-400 text-[11px] uppercase tracking-wider font-bold">SHOW:</span>
                    <select
                        wire:model.live="perPage"
                        class="no-custom-select bg-transparent border-0 text-xs sm:text-sm font-bold text-emerald-700 dark:text-emerald-400 focus:outline-none cursor-pointer pr-1"
                    >
                        <option value="15" class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">15</option>
                        <option value="50" class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">50</option>
                        <option value="100" class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">100</option>
                        <option value="200" class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">200</option>
                        <option value="500" class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">500</option>
                    </select>
                </div>
            </div>

            {{-- 3. Desktop Table (8 Reference Columns — Screenshot 4) --}}
            <div class="hidden md:block w-full overflow-x-auto rounded-2xl unified-ui-border shadow-2xs bg-white dark:bg-slate-900">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#15803d] text-white text-xs font-semibold uppercase tracking-wider">
                            <th class="p-[10px] text-center w-[46px] rounded-tl-2xl whitespace-nowrap">SL</th>
                            <th class="p-[10px] text-start w-[135px] whitespace-nowrap">Customer ID</th>
                            <th class="p-[10px] text-center w-[68px] whitespace-nowrap">Image</th>
                            <th class="p-[10px] text-start whitespace-nowrap">Customer Name</th>
                            <th class="p-[10px] text-start w-[150px] whitespace-nowrap">Mobile</th>
                            <th class="p-[10px] text-end w-[150px] whitespace-nowrap">Total Due Amount</th>
                            <th class="p-[10px] text-center w-[95px] whitespace-nowrap">Status</th>
                            <th class="p-[10px] text-center w-[88px] rounded-tr-2xl whitespace-nowrap">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm text-slate-700 dark:text-slate-200">
                        @forelse($customers as $idx => $c)
                            @php
                                $totalDue = Money::add($c->previous_due_amount ?? 0, $c->orders_sum_due_amount ?? 0);
                                $hasDue = Money::cmp($totalDue, 0) > 0;
                                $custCode = $c->customer_id ?: ('BCUST-' . str_pad((string) $c->id, 4, '0', STR_PAD_LEFT));
                                $cleanImg = $c->img_url ? ltrim((string) $c->img_url, '/') : '';
                                $imgUrl = ($cleanImg !== '' && file_exists(public_path($cleanImg))) ? asset($cleanImg) : $defaultCustomerImg;
                            @endphp
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="p-[10px] text-center font-semibold text-slate-400 dark:text-slate-500">
                                    {{ ($customers->firstItem() ?? 1) + $idx }}
                                </td>
                                <td class="p-[10px] text-start whitespace-nowrap">
                                    <a href="{{ route('battery.customer.profile', $c->id) }}" wire:navigate class="inline-flex items-center gap-1.5 font-mono font-bold text-xs text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 hover:underline">
                                        <i class="fa-regular fa-user text-[11px]"></i>
                                        <span>{{ $custCode }}</span>
                                    </a>
                                </td>
                                <td class="p-[10px] text-center">
                                    <button
                                        type="button"
                                        wire:click="openImageModal('{{ $imgUrl }}', '{{ addslashes($c->customer_name) }}')"
                                        class="w-8 h-8 rounded-full border border-emerald-200 dark:border-slate-700 overflow-hidden mx-auto shadow-2xs cursor-pointer hover:scale-110 transition-transform bg-emerald-50 dark:bg-slate-800 p-0 block"
                                        title="Click to view photo"
                                    >
                                        <img src="{{ $imgUrl }}" alt="{{ $c->customer_name }}" class="w-full h-full object-cover" onerror="this.src='{{ $defaultCustomerImg }}'">
                                    </button>
                                </td>
                                <td class="p-[10px] text-start">
                                    <a href="{{ route('battery.customer.profile', $c->id) }}" wire:navigate class="font-bold text-slate-800 dark:text-slate-100 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors text-sm leading-snug">
                                        {{ $c->customer_name }}
                                    </a>
                                </td>
                                <td class="p-[10px] text-start font-medium text-slate-600 dark:text-slate-300 whitespace-nowrap font-mono text-xs sm:text-sm">
                                    {{ $c->mobile ?: '-' }}
                                </td>
                                <td class="p-[10px] text-end font-bold whitespace-nowrap font-mono tabular-nums {{ $hasDue ? 'text-rose-600 dark:text-rose-400' : 'text-slate-800 dark:text-slate-100' }}">
                                    {{ Money::formatBd($totalDue) }}
                                </td>
                                <td class="p-[10px] text-center whitespace-nowrap">
                                    <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-500 text-white shadow-2xs">
                                        Active
                                    </span>
                                </td>
                                <td class="p-[10px] text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button
                                            type="button"
                                            wire:click="openEdit({{ $c->id }})"
                                            class="w-[30px] h-[30px] rounded-lg bg-emerald-50 hover:bg-emerald-600 text-emerald-600 hover:text-white border border-emerald-200/80 hover:border-emerald-600 dark:bg-emerald-950/40 dark:border-slate-700 dark:text-emerald-400 dark:hover:bg-emerald-600 dark:hover:text-white flex items-center justify-center transition-all shadow-2xs cursor-pointer"
                                            title="Edit Customer"
                                        >
                                            <i class="fa-regular fa-pen-to-square text-xs"></i>
                                        </button>
                                        <button
                                            type="button"
                                            wire:click="confirmDelete({{ $c->id }})"
                                            class="w-[30px] h-[30px] rounded-lg bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white border border-rose-200/80 hover:border-rose-600 dark:bg-rose-950/40 dark:border-slate-700 dark:text-rose-400 dark:hover:bg-rose-600 dark:hover:text-white flex items-center justify-center transition-all shadow-2xs cursor-pointer"
                                            title="Delete Customer"
                                        >
                                            <i class="fa-regular fa-trash-can text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-8 text-center text-rose-500 font-semibold">
                                    <i class="fa-regular fa-circle-xmark text-xl block mb-1.5 opacity-80"></i>
                                    No customers found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-slate-50/90 dark:bg-slate-800/70 text-slate-800 dark:text-slate-100 font-bold border-t-2 border-emerald-600/30 dark:border-emerald-600/20 text-xs sm:text-sm">
                        <tr>
                            <td colspan="5" class="p-[10px] text-end text-slate-600 dark:text-slate-300">Total Due:</td>
                            <td class="p-[10px] text-end text-rose-600 dark:text-rose-400 whitespace-nowrap font-mono tabular-nums">{{ Money::formatBd($totalDueSum) }}</td>
                            <td class="p-[10px]"></td>
                            <td class="p-[10px]"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- 4. Mobile Card List View --}}
            <div class="block md:hidden space-y-3 mb-3">
                @forelse($customers as $idx => $c)
                    @php
                        $totalDue = Money::add($c->previous_due_amount ?? 0, $c->orders_sum_due_amount ?? 0);
                        $hasDue = Money::cmp($totalDue, 0) > 0;
                        $custCode = $c->customer_id ?: ('BCUST-' . str_pad((string) $c->id, 4, '0', STR_PAD_LEFT));
                        $cleanImg = $c->img_url ? ltrim((string) $c->img_url, '/') : '';
                        $imgUrl = ($cleanImg !== '' && file_exists(public_path($cleanImg))) ? asset($cleanImg) : $defaultCustomerImg;
                    @endphp
                    <div class="unified-ui-border rounded-2xl p-3.5 bg-white dark:bg-slate-800/90 shadow-xs">
                        <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-100 dark:border-slate-700">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-[10px] font-bold">#{{ ($customers->firstItem() ?? 1) + $idx }}</span>
                                <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-slate-700 text-[10px] font-bold font-mono">
                                    <i class="fa-solid fa-user mr-1"></i>{{ $custCode }}
                                </span>
                            </div>
                            <span class="px-2 py-0.5 rounded-full bg-emerald-500 text-white text-[10px] font-bold">Active</span>
                        </div>

                        <div class="flex items-center gap-2.5 mb-2.5">
                            <img
                                src="{{ $imgUrl }}"
                                alt="{{ $c->customer_name }}"
                                wire:click="openImageModal('{{ $imgUrl }}', '{{ addslashes($c->customer_name) }}')"
                                class="w-9 h-9 rounded-full border border-emerald-200 dark:border-slate-700 object-cover shrink-0 cursor-pointer"
                            >
                            <div class="min-w-0">
                                <h6 class="font-bold text-slate-800 dark:text-slate-100 truncate text-sm leading-snug m-0">{{ $c->customer_name }}</h6>
                                <div class="flex items-center gap-1 text-slate-500 dark:text-slate-400 text-xs mt-0.5">
                                    <i class="fa-solid fa-phone text-[10px]"></i>
                                    <span>{{ $c->mobile ?: 'No Mobile' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 bg-slate-50 dark:bg-slate-900/50 p-2.5 rounded-xl my-2 border border-slate-100 dark:border-slate-700">
                            <div class="border-r border-slate-200 dark:border-slate-700 pr-2">
                                <span class="text-slate-400 block text-[9px] font-bold uppercase tracking-wider">Total Due</span>
                                <span class="font-bold font-mono text-sm {{ $hasDue ? 'text-rose-600 dark:text-rose-400' : 'text-slate-800 dark:text-slate-100' }}">{{ Money::formatBd($totalDue) }}</span>
                            </div>
                            <div class="pl-1">
                                <span class="text-slate-400 block text-[9px] font-bold uppercase tracking-wider">Profile</span>
                                <a href="{{ route('battery.customer.profile', $c->id) }}" wire:navigate class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:underline">View Details &rarr;</a>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-2.5 mt-1 border-t border-slate-100 dark:border-slate-700">
                            <a href="{{ route('battery.customer.profile', $c->id) }}" wire:navigate class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-200 hover:text-emerald-600">
                                <i class="fa-regular fa-user"></i>
                                <span>Profile</span>
                            </a>
                            <div class="flex items-center gap-2">
                                <button type="button" wire:click="openEdit({{ $c->id }})" class="w-8 h-8 rounded-xl bg-emerald-50 hover:bg-emerald-600 text-emerald-600 hover:text-white border border-emerald-200 flex items-center justify-center cursor-pointer" title="Edit">
                                    <i class="fa-regular fa-pen-to-square text-xs"></i>
                                </button>
                                <button type="button" wire:click="confirmDelete({{ $c->id }})" class="w-8 h-8 rounded-xl bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white border border-rose-200 flex items-center justify-center cursor-pointer" title="Delete">
                                    <i class="fa-regular fa-trash-can text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-rose-500 font-semibold bg-white dark:bg-slate-800 rounded-2xl unified-ui-border">No customers found.</div>
                @endforelse
            </div>

            {{-- 5. Display Info & Pagination Footer --}}
            <div class="flex flex-col sm:flex-row items-center justify-between pt-4 mt-4 border-t border-slate-200 dark:border-slate-800 gap-3">
                <div class="flex items-center gap-1.5 text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
                    <span>Showing</span>
                    <span class="font-bold text-slate-800 dark:text-slate-100 px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700">
                        {{ $customers->firstItem() ?? 0 }} – {{ $customers->lastItem() ?? 0 }}
                    </span>
                    <span>of</span>
                    <span class="font-bold text-emerald-700 dark:text-emerald-400 px-1.5 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-slate-700">
                        {{ $customers->total() }}
                    </span>
                    <span>customers</span>
                </div>
                <div class="max-w-full overflow-x-auto">
                    {{ $customers->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL 1: Add New / Edit Customer Modal (1:1 Reference Screenshot 5) --}}
    @if($showModal)
        <div data-marss-modal="true" class="fixed inset-0 z-50 !m-0 overflow-hidden bg-slate-950/60 backdrop-blur-[2px] flex items-center justify-center p-3 sm:p-4" wire:click.self="closeModal">
            <div class="w-full max-w-[720px] max-h-[90vh] bg-white dark:bg-slate-900 rounded-2xl shadow-2xl overflow-hidden flex flex-col border border-slate-200 dark:border-slate-800">
                <div class="bg-gradient-to-r from-[#15803d] to-[#16a34a] px-5 py-3 flex items-center justify-between shrink-0 sticky top-0 z-20">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-white/20 text-white flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-user-plus text-sm"></i>
                        </div>
                        <h3 class="text-base font-bold text-white m-0">{{ $editingId ? 'Edit Customer' : 'Add New Customer' }}</h3>
                    </div>
                    <button type="button" wire:click="closeModal" class="w-7 h-7 rounded-full bg-red-500 hover:bg-red-600 text-white flex items-center justify-center border-0 shadow-xs cursor-pointer transition-transform hover:scale-105" title="Close">
                        <i class="fa-solid fa-xmark text-xs"></i>
                    </button>
                </div>

                <form wire:submit.prevent="save" novalidate class="flex flex-col flex-1 overflow-hidden min-h-0 m-0">
                    <div class="p-5 overflow-y-auto flex-1 min-h-0 space-y-3.5 text-left">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block text-[13px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Customer Name <span class="text-rose-500">*</span></label>
                                <input type="text" wire:model="customer_name" placeholder="Enter Customer Name" class="w-full h-[42px] px-3.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-[13.5px] text-slate-800 dark:text-slate-100 outline-none focus:border-[#16a34a]">
                                @error('customer_name') <span class="text-xs text-rose-500 font-semibold mt-0.5 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-[13px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Customer Number <span class="text-rose-500">*</span></label>
                                <input type="text" wire:model="mobile" placeholder="Enter Customer Number" class="w-full h-[42px] px-3.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-[13.5px] text-slate-800 dark:text-slate-100 outline-none focus:border-[#16a34a]">
                                @error('mobile') <span class="text-xs text-rose-500 font-semibold mt-0.5 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-[13px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Customer Email</label>
                                <input type="text" wire:model="email" placeholder="Enter Customer Email" class="w-full h-[42px] px-3.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-[13.5px] text-slate-800 dark:text-slate-100 outline-none focus:border-[#16a34a]">
                                @error('email') <span class="text-xs text-rose-500 font-semibold mt-0.5 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-[13px] font-semibold text-slate-700 dark:text-slate-300 mb-1">NID Number</label>
                                <input type="text" wire:model="nid" placeholder="Enter NID Number" class="w-full h-[42px] px-3.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-[13.5px] text-slate-800 dark:text-slate-100 outline-none focus:border-[#16a34a]">
                            </div>
                            <div>
                                <label class="block text-[13px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Previous Due Amount</label>
                                <input type="number" step="any" min="0" wire:model="previous_due_amount" placeholder="0.00" class="w-full h-[42px] px-3.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-[13.5px] text-slate-800 dark:text-slate-100 outline-none focus:border-[#16a34a]">
                                @error('previous_due_amount') <span class="text-xs text-rose-500 font-semibold mt-0.5 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-[13px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Address Details</label>
                                <textarea wire:model="address_details" rows="1" placeholder="Enter Address Details" class="w-full h-[42px] px-3.5 py-2.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-[13.5px] text-slate-800 dark:text-slate-100 outline-none focus:border-[#16a34a] resize-none"></textarea>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[13px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Customer Photo</label>
                            <div class="flex items-center gap-3.5 p-3 rounded-xl border-[1.5px] border-dashed border-slate-300 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/40">
                                <div class="w-[72px] h-[60px] rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 flex items-center justify-center overflow-hidden shrink-0">
                                    @php $cleanExisting = $existingImage ? ltrim((string) $existingImage, '/') : ''; @endphp
                                    @if($photo)
                                        <img src="{{ $photo->temporaryUrl() }}" alt="Preview" class="w-full h-full object-cover">
                                    @elseif($cleanExisting !== '' && file_exists(public_path($cleanExisting)))
                                        <img src="{{ asset($cleanExisting) }}" alt="Current Photo" class="w-full h-full object-cover">
                                    @else
                                        <i class="fa-regular fa-image text-2xl text-slate-400"></i>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <label for="batteryCustPhoto" class="inline-flex items-center gap-2 px-3.5 h-[38px] rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-[13px] font-semibold text-slate-700 dark:text-slate-200 hover:border-[#16a34a] hover:text-[#15803d] shadow-2xs cursor-pointer transition-colors">
                                        <i class="fa-solid fa-arrow-up-from-bracket text-emerald-600 text-xs"></i>
                                        <span>Upload Photo</span>
                                    </label>
                                    <input type="file" id="batteryCustPhoto" wire:model="photo" accept="image/*" class="hidden">
                                    <div wire:loading wire:target="photo" class="text-[11px] font-semibold text-emerald-600 mt-1">
                                        <i class="fa-solid fa-spinner fa-spin mr-1"></i>Uploading photo...
                                    </div>
                                    <p class="text-[11.5px] text-slate-500 dark:text-slate-400 mt-1.5 mb-0">PNG, JPEG or GIF (up to 5 MB)</p>
                                    @error('photo') <span class="text-xs text-rose-500 font-semibold mt-0.5 block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="px-5 py-3 bg-slate-50 dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2 shrink-0 sticky bottom-0 z-20">
                        <button type="button" wire:click="closeModal" class="h-[38px] px-5 rounded-lg bg-[#dc2626] hover:bg-red-700 text-white text-[13.5px] font-semibold border-0 shadow-xs cursor-pointer">
                            Cancel
                        </button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="photo,save" class="h-[38px] px-5 rounded-lg bg-gradient-to-r from-[#15803d] to-[#16a34a] hover:from-[#166534] hover:to-[#15803d] text-white text-[13.5px] font-bold border-0 shadow-xs cursor-pointer disabled:opacity-60">
                            <span wire:loading.remove wire:target="save">Submit</span>
                            <span wire:loading wire:target="save"><i class="fa-solid fa-spinner fa-spin mr-1"></i>Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- MODAL 2: Custom Delete Confirmation Modal --}}
    @if($showDeleteModal)
        <div data-marss-modal="true" class="fixed inset-0 z-[60] !m-0 overflow-hidden bg-slate-950/60 backdrop-blur-[2px] flex items-center justify-center p-4" wire:click.self="closeModal">
            <div class="w-full max-w-md bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 p-6 text-center">
                <div class="w-14 h-14 rounded-full bg-rose-100 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-trash-can text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-1.5">Delete Customer?</h3>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mb-5">
                    Are you sure you want to delete <strong class="text-slate-800 dark:text-white">{{ $deleteTargetName }}</strong>? This action cannot be undone.
                </p>
                <div class="flex items-center justify-center gap-3">
                    <button type="button" wire:click="closeModal" class="px-5 h-10 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-bold hover:bg-slate-50 cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" wire:click="executeDelete" class="px-5 h-10 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold border-0 cursor-pointer shadow-sm">
                        Yes, Delete
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL 3: Full-Screen Image Quick View Modal --}}
    @if($showImageModal)
        <div data-marss-modal="true" wire:click="closeModal" class="fixed inset-0 z-[99999] !m-0 overflow-hidden bg-black/85 backdrop-blur-md flex items-center justify-center p-5">
            <div class="relative flex flex-col items-center justify-center max-h-[82vh] max-w-[90vw]" @click.stop>
                <button type="button" wire:click="closeModal" class="absolute -top-3.5 -right-3.5 z-50 w-9 h-9 rounded-full bg-rose-600 hover:bg-rose-700 text-white flex items-center justify-center shadow-2xl border-2 border-white cursor-pointer" title="Close">
                    <i class="fa-solid fa-xmark text-sm font-bold"></i>
                </button>
                <div class="rounded-2xl overflow-hidden shadow-2xl bg-slate-900 border border-white/20 p-1">
                    <img src="{{ $imageModalUrl }}" alt="{{ $imageModalTitle }}" class="block max-h-[78vh] max-w-[86vw] object-contain rounded-xl bg-white">
                </div>
            </div>
        </div>
    @endif
</div>
