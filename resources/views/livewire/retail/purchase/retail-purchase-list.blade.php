@php
    use App\Support\Money;
    use Carbon\Carbon;

    $filterLabels = [
        'all' => 'Filter',
        'today' => 'Today',
        '7' => 'Last 7 Days',
        '30' => 'Last Month',
        '365' => 'Last Year',
        'custom' => 'Custom Range',
    ];
    $activeFilterLabel = $filterLabels[$dateFilter] ?? 'Filter';
@endphp

<div
    x-data="{
        openMenuId: null,
        menuTop: 0,
        menuLeft: 0,
        menuViewUrl: '',
        hasHeldDraft: !!localStorage.getItem('marss_retail_purchase_draft'),
        cameraOpen: false,
        cameraStatus: 'Starting camera... Hold barcode in front of camera.',
        cameraError: false,
        lastScanned: '-',
        scanner: null,
        cameras: [],
        camIdx: 0,
        openActionMenu(event, id, viewUrl) {
            if (this.openMenuId === id) {
                this.openMenuId = null;
                return;
            }
            const rect = event.currentTarget.getBoundingClientRect();
            const menuW = 165;
            const menuH = 205;
            let left = rect.right - menuW;
            if (left < 10) left = 10;
            let top = rect.bottom + 6;
            if (top + menuH > window.innerHeight - 10) {
                top = Math.max(10, rect.top - menuH - 6);
            }
            this.menuLeft = left;
            this.menuTop = top;
            this.menuViewUrl = viewUrl;
            this.openMenuId = id;
        },
        closeActionMenu() {
            this.openMenuId = null;
        },
        holdDraft() {
            const payload = {
                supplier_id: $wire.supplier_id,
                supplierSearch: $wire.supplierSearch,
                supplierPayableBalance: $wire.supplierPayableBalance,
                referance_no: $wire.referance_no,
                date: $wire.date,
                discount_type: $wire.discount_type,
                discount_value: $wire.discount_value,
                delivery_charge: $wire.delivery_charge,
                paid_amount: $wire.paid_amount,
                payment_method: $wire.payment_method,
                payment_number: $wire.payment_number,
                transaction_id: $wire.transaction_id,
                bank_name: $wire.bank_name,
                bank_account: $wire.bank_account,
                items: $wire.items,
            };
            if (!payload.supplier_id && (!payload.items || payload.items.length === 0)) {
                window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: 'Nothing to hold yet.' } }));
                return;
            }
            localStorage.setItem('marss_retail_purchase_draft', JSON.stringify(payload));
            this.hasHeldDraft = true;
            $wire.closeModals();
            window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Purchase draft held! You can resume anytime.' } }));
        },
        resumeDraft() {
            try {
                const raw = localStorage.getItem('marss_retail_purchase_draft');
                if (!raw) return;
                const parsed = JSON.parse(raw);
                $wire.restoreHeldDraft(parsed);
            } catch (e) {
                localStorage.removeItem('marss_retail_purchase_draft');
                this.hasHeldDraft = false;
            }
        },
        resetAndClearDraft() {
            localStorage.removeItem('marss_retail_purchase_draft');
            this.hasHeldDraft = false;
            $wire.resetFormState();
            window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Form reset & draft cleared.' } }));
        },
        async openCamera() {
            this.cameraOpen = true;
            this.cameraError = false;
            this.cameraStatus = 'Starting camera... Hold barcode in front of camera.';
            this.lastScanned = '-';
            await this.$nextTick();
            if (typeof window.Html5Qrcode === 'undefined') {
                this.cameraError = true;
                this.cameraStatus = 'Camera scanner library not loaded.';
                return;
            }
            try {
                const devices = await window.Html5Qrcode.getCameras();
                if (!devices || devices.length === 0) {
                    this.cameraError = true;
                    this.cameraStatus = 'No camera device found on this computer. Please connect a webcam or use a USB barcode scanner.';
                    return;
                }
                this.cameras = devices;
                this.camIdx = devices.length > 1 ? 1 : 0;
                await this.startCameraStream(devices[this.camIdx].id);
            } catch (err) {
                this.cameraError = true;
                const msg = err?.message || String(err);
                this.cameraStatus = msg.includes('NotFound') || msg.includes('Requested device not found')
                    ? 'No camera detected on this device. Connect a camera or type/scan barcode directly.'
                    : 'Camera unavailable: ' + msg;
            }
        },
        async startCameraStream(cameraId) {
            try {
                if (this.scanner) {
                    try { await this.scanner.stop(); } catch (e) {}
                    try { this.scanner.clear(); } catch (e) {}
                }
                this.scanner = new window.Html5Qrcode('retailPurchaseCameraReader');
                await this.scanner.start(
                    cameraId,
                    { fps: 10, qrbox: { width: 250, height: 150 } },
                    (decodedText) => {
                        this.lastScanned = decodedText;
                        $wire.scanOrSearchProduct(decodedText);
                        this.stopCamera();
                    },
                    () => {}
                );
                this.cameraError = false;
                this.cameraStatus = 'Camera active. Align barcode inside the frame.';
            } catch (err) {
                this.cameraError = true;
                this.cameraStatus = 'Could not start camera: ' + (err?.message || String(err));
            }
        },
        async switchCamera() {
            if (!this.cameras || this.cameras.length < 2) return;
            this.camIdx = (this.camIdx + 1) % this.cameras.length;
            await this.startCameraStream(this.cameras[this.camIdx].id);
        },
        async stopCamera() {
            if (this.scanner) {
                try { await this.scanner.stop(); } catch (e) {}
                try { this.scanner.clear(); } catch (e) {}
                this.scanner = null;
            }
            this.cameraOpen = false;
        }
    }"
    @retail-purchase-created.window="localStorage.removeItem('marss_retail_purchase_draft'); hasHeldDraft = false;"
    @scroll.window="closeActionMenu()"
    @resize.window="closeActionMenu()"
    class="space-y-4"
>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-300 dark:border-slate-800 shadow-sm overflow-hidden transition-colors">
        <div class="p-3 sm:p-5 md:p-6">
            {{-- 1. Top Section: Page Title & + Create Purchase Button --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400 flex items-center justify-center border border-emerald-100 dark:border-slate-800 shadow-xs shrink-0">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <path d="M16 10a4 4 0 0 1-8 0"></path>
                        </svg>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-800 dark:text-white tracking-tight leading-none m-0">Purchase List</h1>
                </div>

                <div class="flex items-center flex-wrap gap-2">
                    <button
                        type="button"
                        wire:click="openCreate"
                        class="inline-flex items-center gap-1.5 px-4 h-[38px] rounded-xl bg-[#15803d] hover:bg-[#166534] active:scale-[0.98] text-white font-semibold text-sm shadow-sm transition-all cursor-pointer border-0"
                    >
                        <i class="fa-solid fa-plus text-xs"></i>
                        <span>Create Purchase</span>
                    </button>
                </div>
            </div>

            {{-- 2. Controls & Filter Row (5 Columns Desktop, 2-Col Grid Mobile) --}}
            <div class="grid grid-cols-2 md:flex md:flex-nowrap items-center gap-2 mb-4">
                {{-- Search Bar --}}
                <div class="col-span-2 md:flex-1 md:min-w-[160px] h-[38px] flex items-center px-3.5 bg-white dark:bg-slate-800/90 rounded-xl unified-ui-border shadow-2xs focus-within:border-emerald-600 focus-within:ring-2 focus-within:ring-emerald-600/20 transition-all">
                    <i class="fa-solid fa-magnifying-glass text-slate-400 text-xs mr-2.5 shrink-0"></i>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search Purchase..."
                        class="w-full h-full bg-transparent border-0 outline-none text-xs font-medium text-slate-800 dark:text-slate-100 placeholder:text-slate-400 focus:ring-0"
                    >
                </div>

                {{-- Start Date (Flatpickr) --}}
                <div class="col-span-1 md:w-[138px] md:shrink-0">
                    <x-ui.date-picker
                        wire:model.live="startDate"
                        placeholder="Start Date"
                        alt-format="d/m/Y"
                        icon-position="right"
                        class="h-[38px] pl-3 pr-8 rounded-xl unified-ui-border bg-white dark:bg-slate-800/90 text-xs font-medium text-slate-700 dark:text-slate-200 placeholder:text-slate-400 outline-none cursor-pointer"
                    />
                </div>

                {{-- End Date (Flatpickr) --}}
                <div class="col-span-1 md:w-[138px] md:shrink-0">
                    <x-ui.date-picker
                        wire:model.live="endDate"
                        placeholder="End Date"
                        alt-format="d/m/Y"
                        icon-position="right"
                        class="h-[38px] pl-3 pr-8 rounded-xl unified-ui-border bg-white dark:bg-slate-800/90 text-xs font-medium text-slate-700 dark:text-slate-200 placeholder:text-slate-400 outline-none cursor-pointer"
                    />
                </div>

                {{-- SHOW Entries --}}
                <div class="col-span-1 md:w-auto md:shrink-0 h-[38px] flex items-center justify-between gap-1.5 px-3 bg-white dark:bg-slate-800/90 rounded-xl unified-ui-border shadow-2xs hover:border-emerald-500 transition-all">
                    <span class="text-slate-500 dark:text-slate-400 text-[11px] font-bold uppercase tracking-wider whitespace-nowrap">SHOW:</span>
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

                {{-- Filter Custom Dropdown --}}
                <div x-data="{ open: false }" @click.outside="open = false" class="col-span-1 md:w-[135px] md:shrink-0 custom-filter-dropdown relative">
                    <button
                        type="button"
                        @click="open = !open"
                        class="w-full h-[38px] px-3 rounded-xl unified-ui-border bg-white dark:bg-slate-800/90 shadow-2xs flex items-center justify-between gap-1.5 text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 hover:border-emerald-500 transition-all cursor-pointer"
                    >
                        <span class="flex items-center gap-1.5 truncate">
                            <i class="fa-solid fa-filter text-[11px] text-slate-400"></i>
                            <span class="truncate">{{ $activeFilterLabel }}</span>
                        </span>
                        <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transition-transform" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <div
                        x-show="open"
                        x-transition
                        style="display: none;"
                        class="select-menu right-0 left-auto min-w-[165px]"
                    >
                        @foreach(['all' => 'All time', 'today' => 'Today', '7' => 'Last 7 Days', '30' => 'Last Month', '365' => 'Last Year'] as $fKey => $fLabel)
                            <button
                                type="button"
                                wire:click="applyDateFilter('{{ $fKey }}')"
                                @click="open = false"
                                class="select-option-item w-full text-left border-0 bg-transparent {{ $dateFilter === $fKey ? 'active' : '' }}"
                            >
                                <span>{{ $fLabel }}</span>
                                @if($dateFilter === $fKey)
                                    <i class="fa-solid fa-check text-xs text-emerald-600"></i>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- 3. Desktop Table (14 Reference Columns) --}}
            <div class="hidden lg:block w-full overflow-x-auto rounded-2xl unified-ui-border shadow-2xs bg-white dark:bg-slate-900 mb-4" style="min-height: 240px;">
                <table class="w-full text-left border-collapse min-w-full table-auto">
                    <thead>
                        <tr class="bg-[#15803d] text-white text-[11px] font-semibold uppercase tracking-wider">
                            <th class="py-2.5 px-1.5 text-center w-[34px] rounded-tl-2xl">SL</th>
                            <th class="py-2.5 px-1.5 text-center whitespace-nowrap">DATE</th>
                            <th class="py-2.5 px-1.5 text-center leading-tight whitespace-nowrap">PURCHASE<br>ID</th>
                            <th class="py-2.5 px-1.5 text-start leading-tight whitespace-nowrap">REFERENCE<br>NO</th>
                            <th class="py-2.5 px-1.5 text-start leading-tight whitespace-nowrap">SUPPLIER<br>ID</th>
                            <th class="py-2.5 px-1.5 text-start leading-tight">SUPPLIER<br>NAME</th>
                            <th class="py-2.5 px-1.5 text-end leading-tight whitespace-nowrap">GRAND<br>TOTAL</th>
                            <th class="py-2.5 px-1.5 text-end leading-tight whitespace-nowrap">PAID<br>AMOUNT</th>
                            <th class="py-2.5 px-1.5 text-end leading-tight whitespace-nowrap">DUE<br>AMOUNT</th>
                            <th class="py-2.5 px-1.5 text-end leading-tight whitespace-nowrap">RETURN<br>AMOUNT</th>
                            <th class="py-2.5 px-1.5 text-center leading-tight whitespace-nowrap">PAYMENT<br>METHOD</th>
                            <th class="py-2.5 px-1.5 text-center whitespace-nowrap">STATUS</th>
                            <th class="py-2.5 px-1.5 text-center w-[44px] rounded-tr-2xl">ACTION</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-[11.5px] lg:text-xs text-slate-700 dark:text-slate-200">
                        @forelse($purchases as $idx => $p)
                            @php
                                $grandVal = Money::add($p->grand_subtotal ?? 0, $p->delivery_charge ?? 0);
                                $paidVal = Money::round($p->paid_amount ?? 0);
                                $dueVal = Money::round($p->due_amount ?? 0);
                                $retVal = Money::sum($p->purchaseReturns->pluck('amount'));
                                $statusText = Money::cmp($dueVal, 0) <= 0 ? 'Fully Paid' : (Money::cmp($paidVal, 0) > 0 ? 'Partial Paid' : 'Unpaid');
                                $statusClass = match ($statusText) {
                                    'Fully Paid' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-400 dark:border-emerald-800',
                                    'Partial Paid' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/50 dark:text-amber-400 dark:border-amber-800',
                                    default => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/50 dark:text-rose-400 dark:border-rose-800',
                                };
                                $method = $p->paymentDetails->first()?->payment_method ?: 'N/A';
                                $docUrl = $p->attach_document
                                    ? (str_starts_with($p->attach_document, 'http') || str_starts_with($p->attach_document, '/') ? $p->attach_document : asset($p->attach_document))
                                    : null;
                                $supCode = $p->supplier?->supplier_id ?: ('SUP-' . str_pad((string) ($p->supplier_id ?? 0), 5, '0', STR_PAD_LEFT));
                                $dateFmt = $p->date ? Carbon::parse($p->date)->format('d-m-Y') : 'N/A';
                            @endphp
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="py-2 px-1.5 text-center font-semibold text-slate-500 dark:text-slate-400">
                                    {{ ($purchases->firstItem() ?? 1) + $idx }}
                                </td>
                                <td class="py-2 px-1.5 text-center font-medium text-slate-600 dark:text-slate-300 whitespace-nowrap tabular-nums">{{ $dateFmt }}</td>
                                <td class="py-2 px-1.5 text-center font-bold text-emerald-700 dark:text-emerald-400 whitespace-nowrap font-mono">{{ $p->purchase_id }}</td>
                                <td class="py-2 px-1.5 text-start font-medium text-slate-500 dark:text-slate-400 whitespace-nowrap max-w-[125px] truncate" title="{{ $p->referance_no }}">{{ $p->referance_no ?: '-' }}</td>
                                <td class="py-2 px-1.5 text-start whitespace-nowrap">
                                    @if($p->supplier_id)
                                        <a href="{{ route('retail.supplier.profile', $p->supplier_id) }}" wire:navigate class="inline-flex items-center gap-1 font-semibold text-emerald-700 dark:text-emerald-400 hover:underline">
                                            <i class="fa-solid fa-truck-field text-[10px]"></i>
                                            <span>{{ $supCode }}</span>
                                        </a>
                                    @else
                                        <span class="text-slate-400">N/A</span>
                                    @endif
                                </td>
                                <td class="py-2 px-1.5 text-start font-bold text-slate-800 dark:text-slate-100 max-w-[135px] truncate" title="{{ $p->supplier?->name ?? '' }}">
                                    @if($p->supplier_id)
                                        <a href="{{ route('retail.supplier.profile', $p->supplier_id) }}" wire:navigate class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">
                                            {{ $p->supplier?->name ?? 'N/A' }}
                                        </a>
                                    @else
                                        <span>N/A</span>
                                    @endif
                                </td>
                                <td class="py-2 px-1.5 text-end font-bold text-slate-800 dark:text-slate-100 whitespace-nowrap font-mono tabular-nums">{{ Money::formatBd($grandVal) }}</td>
                                <td class="py-2 px-1.5 text-end font-bold text-emerald-600 dark:text-emerald-400 whitespace-nowrap font-mono tabular-nums">{{ Money::formatBd($paidVal) }}</td>
                                <td class="py-2 px-1.5 text-end font-bold text-rose-600 dark:text-rose-400 whitespace-nowrap font-mono tabular-nums">{{ Money::formatBd($dueVal) }}</td>
                                <td class="py-2 px-1.5 text-end font-bold text-rose-600 dark:text-rose-400 whitespace-nowrap font-mono tabular-nums">{{ Money::formatBd($retVal) }}</td>
                                <td class="py-2 px-1.5 text-center font-medium text-slate-600 dark:text-slate-300 whitespace-nowrap">{{ $method }}</td>
                                <td class="py-2 px-1.5 text-center whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded-md text-[10.5px] font-bold inline-flex items-center border {{ $statusClass }}">
                                        {{ $statusText }}
                                    </span>
                                </td>
                                <td class="py-2 px-1.5 text-center">
                                    <button
                                        type="button"
                                        @click.stop="openActionMenu($event, {{ $p->id }}, '{{ route('retail.purchase.show', $p->id) }}')"
                                        class="w-[26px] h-[26px] rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 inline-flex items-center justify-center shadow-2xs transition-all cursor-pointer"
                                        title="Actions"
                                    >
                                        <i class="fa-solid fa-ellipsis-vertical text-xs"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="13" class="py-10 text-center text-rose-500 font-bold text-sm">
                                    <i class="fa-regular fa-folder-open text-2xl block mb-1.5 opacity-60"></i>
                                    No purchase data found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-50/90 dark:bg-slate-800/80 font-bold text-xs border-t border-slate-200 dark:border-slate-700">
                            <td colspan="6" class="py-2.5 px-2 text-end text-slate-700 dark:text-slate-200">Total:</td>
                            <td class="py-2.5 px-1.5 text-end text-slate-800 dark:text-white whitespace-nowrap font-mono tabular-nums">{{ Money::formatBd($totalGrandSum) }}</td>
                            <td class="py-2.5 px-1.5 text-end text-emerald-600 dark:text-emerald-400 whitespace-nowrap font-mono tabular-nums">{{ Money::formatBd($totalPaidSum) }}</td>
                            <td class="py-2.5 px-1.5 text-end text-rose-600 dark:text-rose-400 whitespace-nowrap font-mono tabular-nums">{{ Money::formatBd($totalDueSum) }}</td>
                            <td class="py-2.5 px-1.5 text-end text-rose-600 dark:text-rose-400 whitespace-nowrap font-mono tabular-nums">{{ Money::formatBd($totalReturnSum) }}</td>
                            <td colspan="3" class="py-2.5 px-1.5"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- 4. Mobile & Tablet Card List View (1-col Mobile, 2-col Tablet) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:hidden gap-3 mb-4">
                @forelse($purchases as $idx => $p)
                    @php
                        $grandVal = Money::add($p->grand_subtotal ?? 0, $p->delivery_charge ?? 0);
                        $paidVal = Money::round($p->paid_amount ?? 0);
                        $dueVal = Money::round($p->due_amount ?? 0);
                        $retVal = Money::sum($p->purchaseReturns->pluck('amount'));
                        $statusText = Money::cmp($dueVal, 0) <= 0 ? 'Fully Paid' : (Money::cmp($paidVal, 0) > 0 ? 'Partial Paid' : 'Unpaid');
                        $statusClass = match ($statusText) {
                            'Fully Paid' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'Partial Paid' => 'bg-amber-50 text-amber-700 border-amber-200',
                            default => 'bg-rose-50 text-rose-700 border-rose-200',
                        };
                        $dateFmt = $p->date ? Carbon::parse($p->date)->format('d-m-Y') : 'N/A';
                    @endphp
                    <div class="p-3.5 rounded-2xl bg-white dark:bg-slate-800/90 unified-ui-border shadow-xs">
                        <div class="flex items-center justify-between pb-2.5 mb-2.5 border-b border-slate-200 dark:border-slate-700">
                            <div class="flex items-center gap-1.5">
                                <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-bold">#{{ ($purchases->firstItem() ?? 1) + $idx }}</span>
                                <span class="px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/40 text-emerald-700 dark:text-emerald-400 text-xs font-bold font-mono">
                                    <i class="fa-solid fa-receipt mr-1"></i>{{ $p->purchase_id }}
                                </span>
                            </div>
                            <span class="px-2 py-0.5 rounded-md text-xs font-bold border {{ $statusClass }}">{{ $statusText }}</span>
                        </div>

                        <div class="mb-2">
                            <div class="flex items-center justify-between gap-2">
                                @if($p->supplier_id)
                                    <a href="{{ route('retail.supplier.profile', $p->supplier_id) }}" wire:navigate class="font-bold text-slate-800 dark:text-white text-sm flex items-center gap-1.5 hover:text-emerald-600 truncate">
                                        <i class="fa-solid fa-truck-field text-emerald-600 dark:text-emerald-400 text-xs"></i>
                                        <span class="truncate">{{ $p->supplier?->name ?? 'N/A' }}</span>
                                    </a>
                                @else
                                    <span class="font-bold text-slate-800 dark:text-white text-sm">N/A</span>
                                @endif
                                <span class="text-slate-500 dark:text-slate-400 text-xs font-medium shrink-0">{{ $dateFmt }}</span>
                            </div>
                            <div class="text-slate-500 dark:text-slate-400 text-xs mt-1">
                                Ref: <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $p->referance_no ?: 'N/A' }}</span>
                            </div>
                        </div>

                        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-700/60 my-2.5">
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <div>
                                    <span class="text-slate-500 dark:text-slate-400">Grand Total:</span>
                                    <div class="font-bold text-slate-800 dark:text-white text-sm font-mono">{{ Money::formatBd($grandVal) }}</div>
                                </div>
                                <div class="text-end">
                                    <span class="text-slate-500 dark:text-slate-400">Paid:</span>
                                    <div class="font-bold text-emerald-600 dark:text-emerald-400 text-sm font-mono">{{ Money::formatBd($paidVal) }}</div>
                                </div>
                                <div>
                                    <span class="text-slate-500 dark:text-slate-400">Due:</span>
                                    <div class="font-bold text-rose-600 dark:text-rose-400 font-mono">{{ Money::formatBd($dueVal) }}</div>
                                </div>
                                <div class="text-end">
                                    <span class="text-slate-500 dark:text-slate-400">Return:</span>
                                    <div class="font-bold text-rose-600 dark:text-rose-400 font-mono">{{ Money::formatBd($retVal) }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-2.5 border-t border-slate-200 dark:border-slate-700">
                            <a href="{{ route('retail.purchase.show', $p->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 text-xs font-semibold rounded-lg border border-emerald-200 dark:border-emerald-800/40">
                                <i class="fa-solid fa-eye text-xs"></i>
                                <span>View Memo</span>
                            </a>
                            <div class="flex items-center gap-1.5">
                                <button type="button" wire:click="openEdit({{ $p->id }})" class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 border border-amber-200 flex items-center justify-center cursor-pointer" title="Edit">
                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                </button>
                                <button type="button" wire:click="openReturn({{ $p->id }})" class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 border border-indigo-200 flex items-center justify-center cursor-pointer" title="Return">
                                    <i class="fa-solid fa-rotate-left text-xs"></i>
                                </button>
                                <button type="button" wire:click="openSplit({{ $p->id }})" class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 border border-purple-200 flex items-center justify-center cursor-pointer" title="Split Invoice">
                                    <i class="fa-solid fa-code-fork text-xs"></i>
                                </button>
                                <button type="button" wire:click="confirmDelete({{ $p->id }})" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 border border-rose-200 flex items-center justify-center cursor-pointer" title="Delete">
                                    <i class="fa-solid fa-trash text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-rose-500 font-bold bg-white dark:bg-slate-800 rounded-2xl unified-ui-border">
                        No purchase data found.
                    </div>
                @endforelse
            </div>

            {{-- 5. Display Info & Smart Pagination Footer --}}
            <div class="flex flex-col sm:flex-row items-center justify-between pt-4 mt-2 border-t border-slate-200 dark:border-slate-800 gap-3">
                <div class="flex items-center gap-1.5 text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
                    <span>Showing</span>
                    <span class="font-bold text-slate-700 dark:text-slate-200 px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                        {{ $purchases->firstItem() ?? 0 }} – {{ $purchases->lastItem() ?? 0 }}
                    </span>
                    <span>of</span>
                    <span class="font-bold text-emerald-700 dark:text-emerald-400 px-1.5 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-slate-800">
                        {{ $purchases->total() }}
                    </span>
                    <span>purchases</span>
                </div>
                <div class="max-w-full overflow-x-auto">
                    {{ $purchases->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Root-Level Fixed 3-Dot Action Dropdown Menu (Never Clipped by Table Overflow) --}}
    <div
        x-show="openMenuId !== null"
        @click.outside="closeActionMenu()"
        x-transition.opacity.duration.120ms
        :style="`top: ${menuTop}px; left: ${menuLeft}px;`"
        style="display: none;"
        class="fixed z-[99999] min-w-[162px] rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-2xl p-1.5 text-xs"
    >
        <ul class="space-y-0.5 m-0 p-0 list-none">
            <li>
                <a
                    :href="menuViewUrl"
                    @click="closeActionMenu()"
                    class="rounded-lg px-2.5 py-2 flex items-center gap-2.5 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700/60 font-medium cursor-pointer transition-colors"
                >
                    <i class="fa-solid fa-eye text-emerald-600 dark:text-emerald-400 w-4 text-center"></i>
                    <span>View Memo</span>
                </a>
            </li>
            <li>
                <button
                    type="button"
                    @click="$wire.openEdit(openMenuId); closeActionMenu()"
                    class="w-full text-left rounded-lg px-2.5 py-2 flex items-center gap-2.5 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700/60 font-medium cursor-pointer border-0 bg-transparent transition-colors"
                >
                    <i class="fa-solid fa-pen-to-square text-amber-500 w-4 text-center"></i>
                    <span>Edit</span>
                </button>
            </li>
            <li>
                <button
                    type="button"
                    @click="$wire.openReturn(openMenuId); closeActionMenu()"
                    class="w-full text-left rounded-lg px-2.5 py-2 flex items-center gap-2.5 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 font-medium cursor-pointer border-0 bg-transparent transition-colors"
                >
                    <i class="fa-solid fa-rotate-left w-4 text-center"></i>
                    <span>Return</span>
                </button>
            </li>
            <li>
                <button
                    type="button"
                    @click="$wire.openSplit(openMenuId); closeActionMenu()"
                    class="w-full text-left rounded-lg px-2.5 py-2 flex items-center gap-2.5 text-purple-600 dark:text-purple-400 hover:bg-purple-50 dark:hover:bg-purple-950/40 font-medium cursor-pointer border-0 bg-transparent transition-colors"
                >
                    <i class="fa-solid fa-code-fork w-4 text-center"></i>
                    <span>Split Invoice</span>
                </button>
            </li>
            <li><hr class="my-1 border-slate-200 dark:border-slate-700"></li>
            <li>
                <button
                    type="button"
                    @click="$wire.confirmDelete(openMenuId); closeActionMenu()"
                    class="w-full text-left rounded-lg px-2.5 py-2 flex items-center gap-2.5 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 font-medium cursor-pointer border-0 bg-transparent transition-colors"
                >
                    <i class="fa-solid fa-trash w-4 text-center"></i>
                    <span>Delete</span>
                </button>
            </li>
        </ul>
    </div>

    {{-- MODAL 1: Purchase Product Modal (Create / Edit — 1:1 Reference Screenshot 4) --}}
    @if($showFormModal)
        <div data-marss-modal="true" class="fixed inset-0 z-50 !m-0 overflow-hidden bg-slate-950/60 backdrop-blur-[2px] flex items-center justify-center p-2 sm:p-4">
            <div class="w-full max-w-[1060px] max-h-[92vh] bg-white dark:bg-slate-900 rounded-2xl shadow-2xl overflow-hidden flex flex-col border border-slate-200 dark:border-slate-800">
                {{-- Sticky Emerald Green Header --}}
                <div class="bg-[#15803d] px-5 py-3.5 flex items-center justify-between shrink-0 sticky top-0 z-20">
                    <h2 class="text-base sm:text-lg font-bold text-white flex items-center gap-2.5 m-0 leading-none">
                        <i class="fa-solid fa-cart-flatbed text-base"></i>
                        <span>{{ $editingId ? 'Update Purchase Product' : 'Purchase Product' }}</span>
                    </h2>
                    <button
                        type="button"
                        wire:click="closeModals"
                        class="w-7 h-7 rounded-full bg-red-600 hover:bg-red-700 text-white flex items-center justify-center border-0 cursor-pointer transition-transform hover:scale-105"
                        title="Close"
                    >
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>

                <form wire:submit="savePurchase" class="flex flex-col flex-1 overflow-hidden m-0">
                    {{-- Scrollable Body --}}
                    <div class="p-4 sm:p-5 overflow-y-auto flex-1 space-y-3.5 bg-white dark:bg-slate-900">
                        {{-- Top 2 Cards: Supplier Info & Invoice/Voucher Details --}}
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3.5">
                            {{-- Left Card: SUPPLIER INFORMATION --}}
                            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-900/60 p-4 flex flex-col justify-between">
                                <div>
                                    <h5 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-1.5 mb-3">
                                        <i class="fa-solid fa-truck-field text-emerald-600 dark:text-emerald-400"></i>
                                        <span>Supplier Information</span>
                                    </h5>
                                    <div class="flex items-center gap-2" x-data="{ supOpen: false }" @click.outside="supOpen = false">
                                        <div class="relative flex-1">
                                            <input
                                                type="text"
                                                wire:model.live.debounce.200ms="supplierSearch"
                                                @focus="supOpen = true"
                                                @input="supOpen = true; $wire.set('supplier_id', null)"
                                                placeholder="Search or Select Supplier *"
                                                autocomplete="off"
                                                class="w-full h-[38px] px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-xs font-medium focus:border-emerald-600 focus:outline-none"
                                            >
                                            <div
                                                x-show="supOpen"
                                                x-transition.opacity
                                                style="display: none;"
                                                class="absolute left-0 right-0 top-[calc(100%+4px)] z-50 max-h-60 overflow-y-auto rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-xl py-1"
                                            >
                                                @forelse($filteredSuppliers as $sup)
                                                    <button
                                                        type="button"
                                                        wire:click="selectSupplier({{ $sup->id }})"
                                                        @click="supOpen = false"
                                                        class="w-full text-left px-3 py-2 text-xs hover:bg-emerald-50 dark:hover:bg-slate-700/70 flex items-center justify-between gap-2 border-0 bg-transparent cursor-pointer {{ $supplier_id === $sup->id ? 'bg-emerald-50 dark:bg-emerald-950/40 font-bold text-emerald-700 dark:text-emerald-400' : 'text-slate-700 dark:text-slate-200' }}"
                                                    >
                                                        <div class="truncate">
                                                            <span class="font-bold">{{ $sup->name }}</span>
                                                            @if($sup->company)<span class="text-slate-400 ml-1">({{ $sup->company }})</span>@endif
                                                        </div>
                                                        <span class="text-[11px] font-mono text-slate-400 shrink-0">{{ $sup->mobile ?: $sup->supplier_id }}</span>
                                                    </button>
                                                @empty
                                                    <div class="px-3 py-2.5 text-xs text-slate-400 text-center">No matching supplier found.</div>
                                                @endforelse
                                            </div>
                                        </div>
                                        <button
                                            type="button"
                                            wire:click="openQuickSupplier"
                                            class="inline-flex items-center gap-1 px-3 h-[38px] bg-[#15803d] hover:bg-[#166534] text-white text-xs font-semibold rounded-xl shadow-xs shrink-0 border-0 cursor-pointer transition-all"
                                        >
                                            <i class="fa-solid fa-plus text-[11px]"></i>
                                            <span>New</span>
                                        </button>
                                    </div>
                                    @error('supplier_id')
                                        <p class="text-xs text-rose-600 font-semibold mt-1.5">Please select a supplier.</p>
                                    @enderror
                                </div>

                                @if(!$editingId)
                                    <div x-show="hasHeldDraft" style="display: none;" class="mt-3 p-2.5 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/40 flex items-center justify-between gap-2">
                                        <span class="text-xs font-semibold text-amber-800 dark:text-amber-300">
                                            <i class="fa-solid fa-clock-rotate-left mr-1"></i> A held purchase draft is saved.
                                        </span>
                                        <button type="button" @click="resumeDraft()" class="px-2.5 py-1 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-[11px] font-bold border-0 cursor-pointer">
                                            Resume Draft
                                        </button>
                                    </div>
                                @endif
                            </div>

                            {{-- Right Card: INVOICE & VOUCHER DETAILS --}}
                            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-900/60 p-4">
                                <h5 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-1.5 mb-3">
                                    <i class="fa-solid fa-file-invoice text-emerald-600 dark:text-emerald-400"></i>
                                    <span>Invoice &amp; Voucher Details</span>
                                </h5>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Ref / Invoice No <span class="text-rose-500">*</span></label>
                                        <input
                                            type="text"
                                            wire:model="referance_no"
                                            placeholder="Reference No *"
                                            class="w-full h-[38px] px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-xs font-medium focus:border-emerald-600 focus:outline-none"
                                        >
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Purchase Date <span class="text-rose-500">*</span></label>
                                        <x-ui.date-picker
                                            wire:model="date"
                                            placeholder="DD-MM-YYYY"
                                            alt-format="d-m-Y"
                                            class="h-[38px] pl-8 pr-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-xs font-medium outline-none cursor-pointer"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Payable Balance</label>
                                        <input
                                            type="text"
                                            readonly
                                            value="{{ $supplier_id ? Money::formatBd($supplierPayableBalance) : '' }}"
                                            placeholder="Payable Amount"
                                            class="w-full h-[38px] px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-100 dark:bg-slate-800/80 text-emerald-700 dark:text-emerald-400 text-xs font-bold outline-none"
                                        >
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Attach Invoice Doc</label>
                                        <input
                                            type="file"
                                            wire:model="attachment"
                                            class="w-full h-[38px] px-2 py-1.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:bg-slate-100 dark:file:bg-slate-700 file:text-slate-700 dark:file:text-slate-200 file:text-[11px] file:font-semibold"
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Middle Card: SCAN BARCODE OR SELECT PRODUCT --}}
                        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-900/60 p-4">
                            <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                                <label class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                    <i class="fa-solid fa-barcode text-emerald-600 dark:text-emerald-400"></i>
                                    <span>Scan Barcode or Select Product <span class="text-rose-500">*</span></span>
                                </label>
                                <span class="px-2 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/40 text-emerald-700 dark:text-emerald-400 text-[11px] font-bold uppercase tracking-wider">
                                    <i class="fa-solid fa-bolt mr-1"></i>Auto-Cart Enabled
                                </span>
                            </div>

                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                                <div class="flex-1 relative" x-data="{ prodOpen: false }" @click.outside="prodOpen = false">
                                    <input
                                        type="text"
                                        wire:model.live.debounce.200ms="productSearch"
                                        @focus="prodOpen = true"
                                        @input="prodOpen = true"
                                        @keydown.enter.prevent="$wire.scanOrSearchProduct(); prodOpen = false"
                                        placeholder="Scan barcode or type product name/code (Auto-adds to list)..."
                                        autocomplete="off"
                                        class="w-full h-[38px] px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-xs font-medium focus:border-emerald-600 focus:outline-none"
                                    >
                                    @if(trim($productSearch) !== '')
                                        <div
                                            x-show="prodOpen"
                                            class="absolute left-0 right-0 top-[calc(100%+4px)] z-50 max-h-64 overflow-y-auto rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-xl divide-y divide-slate-100 dark:divide-slate-700/70"
                                        >
                                            @forelse($filteredProducts as $fp)
                                                @php $fpCode = $fp->primaryCode(); @endphp
                                                <button
                                                    type="button"
                                                    wire:click="addProductById({{ $fp->id }})"
                                                    @click="prodOpen = false"
                                                    class="w-full text-left px-3 py-2 text-xs hover:bg-emerald-50 dark:hover:bg-slate-700/70 flex items-center justify-between gap-2 border-0 bg-transparent cursor-pointer"
                                                >
                                                    <div class="min-w-0">
                                                        <div class="font-bold text-slate-800 dark:text-white truncate">{{ $fp->product_name }}</div>
                                                        <div class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-2">
                                                            @if($fpCode)<span class="font-mono text-emerald-700 dark:text-emerald-400">{{ $fpCode }}</span>@endif
                                                            <span>Stock: {{ (int) round((float) $fp->quantity) }}</span>
                                                        </div>
                                                    </div>
                                                    <span class="font-mono font-bold text-emerald-700 dark:text-emerald-400 shrink-0">{{ Money::formatBd($fp->cost_price ?? 0) }}</span>
                                                </button>
                                            @empty
                                                <div class="px-3 py-3 text-xs text-slate-400 text-center">No matching product found.</div>
                                            @endforelse
                                        </div>
                                    @endif
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    <button
                                        type="button"
                                        wire:click="openQuickProduct"
                                        class="inline-flex items-center justify-center gap-1 flex-1 sm:flex-initial px-3.5 h-[38px] bg-[#15803d] hover:bg-[#166534] text-white text-xs font-semibold rounded-xl shadow-xs border-0 cursor-pointer transition-all"
                                    >
                                        <i class="fa-solid fa-plus text-[11px]"></i>
                                        <span>New</span>
                                    </button>
                                    <button
                                        type="button"
                                        @click="openCamera()"
                                        class="inline-flex items-center justify-center gap-1.5 flex-1 sm:flex-initial px-4 h-[38px] bg-[#15803d] hover:bg-[#166534] text-white text-xs font-semibold rounded-xl shadow-xs border-0 cursor-pointer transition-all"
                                    >
                                        <i class="fa-solid fa-camera text-xs"></i>
                                        <span>Scan Camera</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Items Table (Emerald Header) --}}
                        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 overflow-x-auto bg-white dark:bg-slate-900 shadow-2xs">
                            <table class="w-full text-left border-collapse text-xs min-w-[640px]">
                                <thead class="bg-[#15803d] text-white text-[11px] font-semibold uppercase tracking-wider">
                                    <tr>
                                        <th class="p-2.5 w-[160px]">Barcodes</th>
                                        <th class="p-2.5">Product Name</th>
                                        <th class="p-2.5 text-center w-[135px]">Qty</th>
                                        <th class="p-2.5 text-end w-[125px]">Cost Price (৳)</th>
                                        <th class="p-2.5 text-end w-[115px]">Sub Total</th>
                                        <th class="p-2.5 text-center w-[54px]">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    @forelse($items as $idx => $item)
                                        @php
                                            $lineTotal = Money::mul($item['cost_price'] ?? 0, max(0, (int) ($item['quantity'] ?? 0)));
                                            $barcodes = $item['barcodes'] ?? [];
                                        @endphp
                                        <tr wire:key="retail-pitem-{{ $idx }}-{{ $item['product_id'] }}" class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40">
                                            <td class="p-2.5">
                                                @if(!empty($barcodes))
                                                    <span class="inline-block px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/40 text-emerald-700 dark:text-emerald-400 font-mono text-[11px] font-semibold max-w-[140px] truncate" title="{{ implode(', ', $barcodes) }}">
                                                        {{ $barcodes[0] }}
                                                    </span>
                                                @else
                                                    <span class="text-slate-400 text-[11px]">N/A</span>
                                                @endif
                                            </td>
                                            <td class="p-2.5 font-bold text-slate-800 dark:text-slate-100">{{ $item['product_name'] }}</td>
                                            <td class="p-2.5">
                                                <div class="flex items-center justify-center gap-1">
                                                    <button
                                                        type="button"
                                                        wire:click="decrementItemQty({{ $idx }})"
                                                        class="w-7 h-7 rounded-lg border border-slate-300 dark:border-slate-600 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 flex items-center justify-center cursor-pointer"
                                                    >
                                                        <i class="fa-solid fa-minus text-[10px]"></i>
                                                    </button>
                                                    <input
                                                        type="number"
                                                        min="1"
                                                        wire:model.live="items.{{ $idx }}.quantity"
                                                        class="w-14 h-7 text-center font-bold rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-xs outline-none focus:border-emerald-600"
                                                    >
                                                    <button
                                                        type="button"
                                                        wire:click="incrementItemQty({{ $idx }})"
                                                        class="w-7 h-7 rounded-lg border border-slate-300 dark:border-slate-600 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 flex items-center justify-center cursor-pointer"
                                                    >
                                                        <i class="fa-solid fa-plus text-[10px]"></i>
                                                    </button>
                                                </div>
                                            </td>
                                            <td class="p-2.5 text-end">
                                                <input
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    wire:model.live="items.{{ $idx }}.cost_price"
                                                    class="w-24 h-8 px-2 text-end font-bold font-mono rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-xs outline-none focus:border-emerald-600"
                                                >
                                            </td>
                                            <td class="p-2.5 text-end font-bold font-mono text-slate-800 dark:text-white whitespace-nowrap">
                                                {{ Money::formatBd($lineTotal) }}
                                            </td>
                                            <td class="p-2.5 text-center">
                                                <button
                                                    type="button"
                                                    wire:click="removeItem({{ $idx }})"
                                                    class="w-7 h-7 rounded-lg bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white border border-rose-200 dark:bg-rose-950/40 dark:border-rose-800 inline-flex items-center justify-center transition-colors cursor-pointer"
                                                    title="Remove item"
                                                >
                                                    <i class="fa-solid fa-trash text-[11px]"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="py-6 text-center text-slate-400 text-xs font-medium">
                                                Scan a barcode or search a product above to add items to this purchase order.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Bottom 2 Cards: Payment Method & Totals Summary --}}
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3.5">
                            {{-- Left Card: PAYMENT METHOD & DETAILS --}}
                            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-900/60 p-4">
                                <h5 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-1.5 mb-3">
                                    <i class="fa-solid fa-credit-card text-emerald-600 dark:text-emerald-400"></i>
                                    <span>Payment Method &amp; Details</span>
                                </h5>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Payment Method</label>
                                        <select
                                            wire:model.live="payment_method"
                                            class="w-full h-[38px] px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-xs font-semibold outline-none focus:border-emerald-600"
                                        >
                                            <option value="">Select Method</option>
                                            <option value="Cash">Cash</option>
                                            <option value="Bkash">Bkash</option>
                                            <option value="Nagad">Nagad</option>
                                            <option value="Rocket">Rocket</option>
                                            <option value="Bank">Bank</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Paid Amount (৳)</label>
                                        <input
                                            type="number"
                                            step="any"
                                            min="0"
                                            wire:model.live="paid_amount"
                                            @disabled($editingId && $hasMultiplePayments)
                                            class="w-full h-[38px] px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-xs font-bold outline-none focus:border-emerald-600 disabled:opacity-60"
                                        >
                                    </div>

                                    @if($editingId && $hasMultiplePayments)
                                        <div class="sm:col-span-2 text-[11px] text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/40 rounded-lg px-2.5 py-1.5">
                                            <i class="fa-solid fa-circle-info mr-1"></i> Paid amount is locked because multiple payment installments exist for this purchase.
                                        </div>
                                    @endif

                                    @if(in_array($payment_method, ['Bkash', 'Nagad', 'Rocket'], true))
                                        <div class="sm:col-span-2 p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-100/70 dark:bg-slate-800/60 grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            <div>
                                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Payment Number</label>
                                                <input type="text" wire:model="payment_number" placeholder="e.g. 017xxxxxxxx" class="w-full h-9 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs outline-none focus:border-emerald-600">
                                            </div>
                                            <div>
                                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Transaction ID</label>
                                                <input type="text" wire:model="transaction_id" placeholder="e.g. TRX12345678" class="w-full h-9 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs outline-none focus:border-emerald-600">
                                            </div>
                                        </div>
                                    @elseif($payment_method === 'Bank')
                                        <div class="sm:col-span-2 p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-100/70 dark:bg-slate-800/60 grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            <div>
                                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Bank Name</label>
                                                <input type="text" wire:model="bank_name" placeholder="e.g. Islami Bank / DBBL" class="w-full h-9 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs outline-none focus:border-emerald-600">
                                            </div>
                                            <div>
                                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Bank / Account Number</label>
                                                <input type="text" wire:model="bank_account" placeholder="e.g. 2050xxxxxxxx" class="w-full h-9 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs outline-none focus:border-emerald-600">
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Right Card: Totals Summary --}}
                            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-900/60 p-4 space-y-2 text-xs">
                                <div class="flex justify-between items-center py-1 border-b border-slate-200 dark:border-slate-800">
                                    <span class="text-slate-500 dark:text-slate-400 font-semibold">Total Quantity:</span>
                                    <span class="font-bold text-slate-800 dark:text-white text-sm font-mono">{{ number_format($formQty, 2) }}</span>
                                </div>
                                <div class="flex justify-between items-center py-1 border-b border-slate-200 dark:border-slate-800">
                                    <span class="text-slate-500 dark:text-slate-400 font-semibold">Grand Subtotal:</span>
                                    <span class="font-bold text-slate-800 dark:text-white text-sm font-mono">{{ Money::formatBd($formSubTotal) }}</span>
                                </div>
                                <div class="flex justify-between items-center py-1 border-b border-slate-200 dark:border-slate-800 gap-2">
                                    <span class="text-slate-600 dark:text-slate-300 font-semibold">Delivery &amp; Transport Charge:</span>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-slate-400 font-bold">৳</span>
                                        <input
                                            type="number"
                                            step="any"
                                            min="0"
                                            wire:model.live="delivery_charge"
                                            class="w-28 h-8 px-2 text-end font-bold rounded-lg text-xs border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:border-emerald-600"
                                        >
                                    </div>
                                </div>
                                <div class="flex justify-between items-center py-1 border-b border-slate-200 dark:border-slate-800 gap-2">
                                    <span class="text-slate-600 dark:text-slate-300 font-semibold flex items-center gap-1.5">
                                        <span>Discount:</span>
                                        @if($discount_type === 'percent' && (float) $formDiscAmount > 0)
                                            <span class="text-[11px] font-bold text-rose-500 font-mono">(-{{ Money::formatBd($formDiscAmount) }})</span>
                                        @endif
                                    </span>
                                    <div class="flex items-center gap-1.5">
                                        <select
                                            wire:model.live="discount_type"
                                            class="w-16 h-8 px-2 text-xs font-bold rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:border-emerald-600"
                                        >
                                            <option value="fixed">৳</option>
                                            <option value="percent">%</option>
                                        </select>
                                        <input
                                            type="number"
                                            step="any"
                                            min="0"
                                            wire:model.live="discount_value"
                                            class="w-28 h-8 px-2 text-end font-bold rounded-lg text-xs border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:border-emerald-600"
                                        >
                                    </div>
                                </div>

                                <div class="flex justify-between items-center p-2.5 rounded-xl bg-slate-800 dark:bg-slate-950 text-white my-1.5">
                                    <span class="font-bold text-xs">Net Payable:</span>
                                    <span class="font-bold text-base text-emerald-400 font-mono">{{ Money::formatBd($formNetPayable) }}</span>
                                </div>

                                <div class="flex justify-between items-center py-1 border-b border-slate-200 dark:border-slate-800">
                                    <span class="text-slate-500 dark:text-slate-400 font-semibold">Due Amount:</span>
                                    <span class="font-bold text-rose-600 dark:text-rose-400 text-sm font-mono">{{ Money::formatBd($formDue) }}</span>
                                </div>
                                <div class="flex justify-between items-center py-1">
                                    <span class="text-slate-500 dark:text-slate-400 font-semibold">Payment Status:</span>
                                    <span class="px-2.5 py-1 rounded-md text-xs font-bold {{ $formStatus === 'Fully Paid' ? 'bg-emerald-100 text-emerald-800' : ($formStatus === 'Partial Paid' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                        {{ $formStatus }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Sticky Bottom Modal Footer --}}
                    <div class="px-5 py-3.5 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2.5 shrink-0 sticky bottom-0 z-20">
                        <div class="flex items-center justify-between sm:justify-start gap-2">
                            <button
                                type="button"
                                wire:click="closeModals"
                                class="px-5 h-[38px] rounded-xl text-xs font-bold text-white bg-red-600 hover:bg-red-700 active:scale-[0.98] border-0 cursor-pointer transition-all shadow-xs"
                            >
                                Cancel
                            </button>
                            <div class="flex sm:hidden items-center gap-2">
                                <button
                                    type="button"
                                    @click="resetAndClearDraft()"
                                    class="w-[38px] h-[38px] rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-300 dark:border-slate-700 flex items-center justify-center cursor-pointer"
                                    title="Reset All"
                                >
                                    <i class="fa-solid fa-arrows-rotate text-sm"></i>
                                </button>
                                @if(!$editingId)
                                    <button
                                        type="button"
                                        @click="holdDraft()"
                                        class="px-4 h-[38px] rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold flex items-center gap-1.5 border-0 cursor-pointer"
                                    >
                                        <i class="fa-solid fa-pause text-xs"></i>
                                        <span>Hold</span>
                                    </button>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-2 sm:ml-auto">
                            <button
                                type="button"
                                @click="resetAndClearDraft()"
                                class="hidden sm:inline-flex w-[38px] h-[38px] rounded-xl bg-slate-100 hover:bg-rose-50 dark:bg-slate-800 text-slate-600 hover:text-rose-600 dark:text-slate-300 border border-slate-300 dark:border-slate-700 items-center justify-center cursor-pointer transition-all"
                                title="Reset All & Clear Hold Draft"
                            >
                                <i class="fa-solid fa-arrows-rotate text-sm"></i>
                            </button>
                            @if(!$editingId)
                                <button
                                    type="button"
                                    @click="holdDraft()"
                                    class="hidden sm:inline-flex px-4 h-[38px] rounded-xl bg-amber-500 hover:bg-amber-600 active:scale-[0.98] text-white text-xs font-bold items-center gap-1.5 border-0 cursor-pointer transition-all"
                                    title="Hold / Save Draft"
                                >
                                    <i class="fa-solid fa-pause text-xs"></i>
                                    <span>Hold</span>
                                </button>
                            @endif
                            <button
                                type="submit"
                                wire:loading.attr="disabled"
                                class="flex-1 sm:flex-initial min-w-[215px] px-5 h-[38px] rounded-xl bg-[#15803d] hover:bg-[#166534] active:scale-[0.98] text-white text-xs font-bold inline-flex items-center justify-center gap-2 border-0 cursor-pointer shadow-xs transition-all"
                            >
                                <i class="fa-solid fa-circle-check text-sm"></i>
                                <span>{{ $editingId ? 'Update Purchase Order' : 'Submit Purchase Order' }}</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- SUB-MODAL: Add New Supplier (+ New — 1:1 Reference Screenshot 2) --}}
    @if($showQuickSupplierModal)
        <div data-marss-modal="true" class="fixed inset-0 z-[60] !m-0 overflow-hidden bg-slate-950/70 backdrop-blur-[2px] flex items-center justify-center p-3 sm:p-4" wire:click.self="$set('showQuickSupplierModal', false)">
            <div class="w-full max-w-[660px] max-h-[90vh] bg-white dark:bg-slate-900 rounded-2xl shadow-2xl overflow-hidden flex flex-col border border-slate-200 dark:border-slate-800">
                <div class="bg-[#15803d] px-5 py-3.5 flex items-center justify-between shrink-0 sticky top-0 z-20">
                    <h3 class="text-base sm:text-lg font-bold text-white m-0">Add New Supplier</h3>
                    <button type="button" wire:click="$set('showQuickSupplierModal', false)" class="w-7 h-7 rounded-full bg-red-600 hover:bg-red-700 text-white flex items-center justify-center border-0 cursor-pointer shadow-xs">
                        <i class="fa-solid fa-xmark text-xs"></i>
                    </button>
                </div>

                <form wire:submit="saveQuickSupplier" class="flex flex-col flex-1 overflow-hidden min-h-0 m-0">
                    <div class="p-5 overflow-y-auto flex-1 min-h-0 space-y-3.5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Supplier Name <span class="text-rose-500">*</span></label>
                                <input type="text" wire:model="newSupName" placeholder="Enter Supplier Name" required class="w-full h-[42px] px-3.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-800 dark:text-slate-100 outline-none focus:border-[#15803d]">
                                @error('newSupName') <span class="text-xs text-rose-500 font-semibold mt-0.5 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Supplier Mobile <span class="text-rose-500">*</span></label>
                                <input type="text" wire:model="newSupMobile" placeholder="Enter Supplier Mobile" required class="w-full h-[42px] px-3.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-800 dark:text-slate-100 outline-none focus:border-[#15803d]">
                                @error('newSupMobile') <span class="text-xs text-rose-500 font-semibold mt-0.5 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Supplier Company</label>
                                <input type="text" wire:model="newSupCompany" placeholder="Enter Supplier Company" class="w-full h-[42px] px-3.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-800 dark:text-slate-100 outline-none focus:border-[#15803d]">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Supplier Address</label>
                                <input type="text" wire:model="newSupAddress" placeholder="Enter Supplier Address" class="w-full h-[42px] px-3.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-800 dark:text-slate-100 outline-none focus:border-[#15803d]">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Supplier Email</label>
                                <input type="email" wire:model="newSupEmail" placeholder="Enter Supplier Email" class="w-full h-[42px] px-3.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-800 dark:text-slate-100 outline-none focus:border-[#15803d]">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Purchase Payable Amount</label>
                                <input type="number" step="0.01" min="0" wire:model="newSupPayable" placeholder="Enter Purchase Payable Amount" class="w-full h-[42px] px-3.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-800 dark:text-slate-100 outline-none focus:border-[#15803d]">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Status <span class="text-rose-500">*</span></label>
                                <select wire:model="newSupStatus" class="w-full h-[42px] px-3.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-800 dark:text-slate-100 outline-none focus:border-[#15803d]">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Supplier Image</label>
                            <div class="flex items-start gap-3">
                                <div class="w-[74px] h-[74px] rounded-xl border-[1.5px] border-dashed border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 flex items-center justify-center overflow-hidden shrink-0">
                                    @if($newSupImage)
                                        <img src="{{ $newSupImage->temporaryUrl() }}" alt="Preview" class="w-full h-full object-cover">
                                    @else
                                        <i class="fa-regular fa-image text-2xl text-slate-400"></i>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <label for="quickRetailSupImg" class="w-full h-[46px] rounded-lg bg-slate-100 hover:bg-slate-200/80 dark:bg-slate-800 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-200 text-sm font-semibold flex items-center justify-center cursor-pointer transition-colors">
                                        Upload Photo
                                    </label>
                                    <input type="file" id="quickRetailSupImg" wire:model="newSupImage" accept="image/*" class="hidden">
                                    <p class="text-[11px] text-slate-400 mt-1.5 mb-0">PNG, JPEG or GIF (up to 1 MB)</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="px-5 py-3.5 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 shrink-0 sticky bottom-0 z-20">
                        <button type="submit" class="w-full h-[42px] rounded-xl bg-[#15803d] hover:bg-[#166534] text-white text-sm font-bold border-0 cursor-pointer shadow-sm transition-colors">
                            Submit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- SUB-MODAL: Add New Product (+ New — 1:1 Reference Product Modal) --}}
    @if($showQuickProductModal)
        <div data-marss-modal="true" class="fixed inset-0 z-[60] !m-0 overflow-hidden bg-slate-950/70 backdrop-blur-[2px] flex items-center justify-center p-3 sm:p-4" wire:click.self="$set('showQuickProductModal', false)">
            <div class="w-full max-w-[740px] max-h-[90vh] bg-white dark:bg-slate-900 rounded-2xl shadow-2xl overflow-hidden flex flex-col border border-slate-200 dark:border-slate-800">
                <div class="bg-[#15803d] px-4 py-3 text-white flex items-center justify-between shadow-sm shrink-0 sticky top-0 z-20">
                    <h3 class="text-base sm:text-lg font-bold text-white flex items-center gap-2 m-0">
                        <i class="fa-solid fa-cart-plus"></i> Add New Product
                    </h3>
                    <button type="button" wire:click="$set('showQuickProductModal', false)" class="w-7 h-7 rounded-full bg-red-600 hover:bg-red-700 text-white flex items-center justify-center border-0 cursor-pointer shadow-xs">
                        <i class="fa-solid fa-xmark text-xs"></i>
                    </button>
                </div>

                <form wire:submit="saveQuickProduct" id="quickRetailProductForm" class="flex flex-col flex-1 overflow-hidden min-h-0 m-0">
                    <div class="p-4 sm:p-6 overflow-y-auto flex-1 min-h-0 space-y-3.5">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            {{-- Brand Custom Select + Quick Add --}}
                            <div x-data="{ open: false, q: '' }" @click.outside="open = false" class="relative">
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Brand</label>
                                <div class="flex items-center gap-1.5">
                                    <div class="relative flex-1 min-w-0">
                                        <button type="button" @click="open = !open" class="w-full h-[42px] px-3 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium text-slate-800 dark:text-slate-100 flex items-center justify-between gap-1 cursor-pointer hover:border-[#15803d]">
                                            <span class="truncate">{{ $newProdBrandId ? ($brands->firstWhere('id', $newProdBrandId)?->name ?? 'Select Brand') : 'Select Brand' }}</span>
                                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 shrink-0" :class="open ? 'rotate-180' : ''"></i>
                                        </button>
                                        <div x-show="open" x-transition style="display: none;" class="absolute left-0 right-0 top-[calc(100%+4px)] z-50 bg-white dark:bg-slate-900 border-[1.5px] border-slate-300 dark:border-slate-700 rounded-xl shadow-xl p-1.5">
                                            @if($brands->count() > 8)
                                                <input type="text" x-model="q" placeholder="Search Brand..." class="w-full h-8 px-2 mb-1 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs outline-none">
                                            @endif
                                            <div class="max-h-44 overflow-y-auto">
                                                <div wire:click="$set('newProdBrandId', null)" @click="open = false" class="px-2.5 py-1.5 rounded-lg text-xs cursor-pointer hover:bg-emerald-50 dark:hover:bg-slate-800 {{ !$newProdBrandId ? 'bg-emerald-50 text-emerald-700 font-bold' : '' }}">Select Brand</div>
                                                @foreach($brands as $b)
                                                    <div x-show="!q || '{{ strtolower(addslashes($b->name)) }}'.includes(q.toLowerCase())" wire:click="$set('newProdBrandId', {{ $b->id }})" @click="open = false; q = ''" class="px-2.5 py-1.5 rounded-lg text-xs cursor-pointer hover:bg-emerald-50 dark:hover:bg-slate-800 flex items-center justify-between {{ (int)$newProdBrandId === (int)$b->id ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 font-bold' : '' }}">
                                                        <span class="truncate">{{ $b->name }}</span>
                                                        @if((int)$newProdBrandId === (int)$b->id)<i class="fa-solid fa-check text-[10px] text-emerald-600"></i>@endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" wire:click="openQuickBrand" class="w-[42px] h-[42px] rounded-lg bg-[#16a34a] hover:bg-[#15803d] text-white flex items-center justify-center shrink-0 cursor-pointer border-0" title="Add New Brand">
                                        <i class="fa-solid fa-plus text-base font-bold"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- Category Custom Select + Quick Add --}}
                            <div x-data="{ open: false, q: '' }" @click.outside="open = false" class="relative">
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Category</label>
                                <div class="flex items-center gap-1.5">
                                    <div class="relative flex-1 min-w-0">
                                        <button type="button" @click="open = !open" class="w-full h-[42px] px-3 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium text-slate-800 dark:text-slate-100 flex items-center justify-between gap-1 cursor-pointer hover:border-[#15803d]">
                                            <span class="truncate">{{ $newProdCategoryId ? ($categories->firstWhere('id', $newProdCategoryId)?->category_name ?? 'Select Category') : 'Select Category' }}</span>
                                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 shrink-0" :class="open ? 'rotate-180' : ''"></i>
                                        </button>
                                        <div x-show="open" x-transition style="display: none;" class="absolute left-0 right-0 top-[calc(100%+4px)] z-50 bg-white dark:bg-slate-900 border-[1.5px] border-slate-300 dark:border-slate-700 rounded-xl shadow-xl p-1.5">
                                            @if($categories->count() > 8)
                                                <input type="text" x-model="q" placeholder="Search Category..." class="w-full h-8 px-2 mb-1 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs outline-none">
                                            @endif
                                            <div class="max-h-44 overflow-y-auto">
                                                <div wire:click="$set('newProdCategoryId', null)" @click="open = false" class="px-2.5 py-1.5 rounded-lg text-xs cursor-pointer hover:bg-emerald-50 dark:hover:bg-slate-800 {{ !$newProdCategoryId ? 'bg-emerald-50 text-emerald-700 font-bold' : '' }}">Select Category</div>
                                                @foreach($categories as $c)
                                                    <div x-show="!q || '{{ strtolower(addslashes($c->category_name)) }}'.includes(q.toLowerCase())" wire:click="$set('newProdCategoryId', {{ $c->id }})" @click="open = false; q = ''" class="px-2.5 py-1.5 rounded-lg text-xs cursor-pointer hover:bg-emerald-50 dark:hover:bg-slate-800 flex items-center justify-between {{ (int)$newProdCategoryId === (int)$c->id ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 font-bold' : '' }}">
                                                        <span class="truncate">{{ $c->category_name }}</span>
                                                        @if((int)$newProdCategoryId === (int)$c->id)<i class="fa-solid fa-check text-[10px] text-emerald-600"></i>@endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" wire:click="openQuickCategory" class="w-[42px] h-[42px] rounded-lg bg-[#16a34a] hover:bg-[#15803d] text-white flex items-center justify-center shrink-0 cursor-pointer border-0" title="Add New Category">
                                        <i class="fa-solid fa-plus text-base font-bold"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- Sub-Category Custom Select + Quick Add --}}
                            <div x-data="{ open: false, q: '' }" @click.outside="open = false" class="relative">
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Sub-Category</label>
                                <div class="flex items-center gap-1.5">
                                    <div class="relative flex-1 min-w-0">
                                        <button type="button" @click="open = !open" class="w-full h-[42px] px-3 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium text-slate-800 dark:text-slate-100 flex items-center justify-between gap-1 cursor-pointer hover:border-[#15803d]">
                                            <span class="truncate">{{ $newProdSubCategoryId ? ($subCategories->firstWhere('id', $newProdSubCategoryId)?->sub_category_name ?? 'Select Sub-Category') : 'Select Sub-Category' }}</span>
                                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 shrink-0" :class="open ? 'rotate-180' : ''"></i>
                                        </button>
                                        <div x-show="open" x-transition style="display: none;" class="absolute left-0 right-0 top-[calc(100%+4px)] z-50 bg-white dark:bg-slate-900 border-[1.5px] border-slate-300 dark:border-slate-700 rounded-xl shadow-xl p-1.5">
                                            @if($subCategories->count() > 8)
                                                <input type="text" x-model="q" placeholder="Search Sub-Category..." class="w-full h-8 px-2 mb-1 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs outline-none">
                                            @endif
                                            <div class="max-h-44 overflow-y-auto">
                                                <div wire:click="$set('newProdSubCategoryId', null)" @click="open = false" class="px-2.5 py-1.5 rounded-lg text-xs cursor-pointer hover:bg-emerald-50 dark:hover:bg-slate-800 {{ !$newProdSubCategoryId ? 'bg-emerald-50 text-emerald-700 font-bold' : '' }}">Select Sub-Category</div>
                                                @foreach($subCategories as $sc)
                                                    <div x-show="!q || '{{ strtolower(addslashes($sc->sub_category_name)) }}'.includes(q.toLowerCase())" wire:click="$set('newProdSubCategoryId', {{ $sc->id }})" @click="open = false; q = ''" class="px-2.5 py-1.5 rounded-lg text-xs cursor-pointer hover:bg-emerald-50 dark:hover:bg-slate-800 flex items-center justify-between {{ (int)$newProdSubCategoryId === (int)$sc->id ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 font-bold' : '' }}">
                                                        <span class="truncate">{{ $sc->sub_category_name }}</span>
                                                        @if((int)$newProdSubCategoryId === (int)$sc->id)<i class="fa-solid fa-check text-[10px] text-emerald-600"></i>@endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" wire:click="openQuickSubCategory" class="w-[42px] h-[42px] rounded-lg bg-[#16a34a] hover:bg-[#15803d] text-white flex items-center justify-center shrink-0 cursor-pointer border-0" title="Add New Sub-Category">
                                        <i class="fa-solid fa-plus text-base font-bold"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Product Photo Upload Box --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Product Photo</label>
                            <div class="flex items-center gap-3.5 p-3 rounded-xl border-[1.5px] border-dashed border-slate-300 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/40">
                                <div class="w-[72px] h-[64px] rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-2xs flex items-center justify-center overflow-hidden shrink-0">
                                    @if($newProdImage)
                                        <img src="{{ $newProdImage->temporaryUrl() }}" class="w-full h-full object-cover">
                                    @else
                                        <i class="fa-regular fa-image text-2xl text-slate-400"></i>
                                    @endif
                                </div>
                                <div>
                                    <label for="quickRetailProdPhoto" class="inline-flex items-center gap-2 px-3.5 h-9 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:border-emerald-500 hover:text-emerald-700 shadow-2xs cursor-pointer transition-colors">
                                        <i class="fa-solid fa-cloud-arrow-up text-slate-600 dark:text-slate-300"></i>
                                        <span>Upload Photo</span>
                                    </label>
                                    <input type="file" wire:model="newProdImage" id="quickRetailProdPhoto" accept="image/*" class="hidden">
                                    <p class="text-[11px] text-slate-400 mt-1.5 mb-0">PNG, JPEG or GIF (up to 1 MB)</p>
                                </div>
                            </div>
                        </div>

                        {{-- Product Name + Unit --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Product Name <span class="text-rose-500">*</span></label>
                                <input type="text" wire:model="newProdName" placeholder="Product Name *" required class="w-full h-[42px] px-3 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium text-slate-800 dark:text-slate-100 outline-none focus:border-[#15803d]">
                                @error('newProdName') <span class="text-xs text-rose-500 font-semibold">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Unit</label>
                                <select wire:model="newProdUnitId" class="w-full h-[42px] px-3 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium text-slate-800 dark:text-slate-100 outline-none focus:border-[#15803d]">
                                    <option value="">Select Unit</option>
                                    @foreach($units as $u)<option value="{{ $u->id }}">{{ $u->unit_name }}</option>@endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Quantity, Cost Price, Selling Price --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Product Quantity</label>
                                <input type="number" min="0" wire:model="newProdQty" placeholder="Quantity" class="w-full h-[42px] px-3 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium text-slate-800 dark:text-slate-100 outline-none focus:border-[#15803d]">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Cost Price</label>
                                <input type="number" step="0.01" min="0" wire:model="newProdCost" placeholder="Cost Price" class="w-full h-[42px] px-3 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium text-slate-800 dark:text-slate-100 outline-none focus:border-[#15803d]">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Selling Price</label>
                                <input type="number" step="0.01" min="0" wire:model="newProdSell" placeholder="Selling Price" class="w-full h-[42px] px-3 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium text-slate-800 dark:text-slate-100 outline-none focus:border-[#15803d]">
                            </div>
                        </div>

                        {{-- Barcode / Product Code + Camera Scan --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Barcode / Product Code</label>
                            <div class="flex items-center gap-2">
                                <input type="text" wire:model="newProdCode" placeholder="Enter or scan barcode (Auto-generated if empty)..." class="flex-1 h-[42px] px-3 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium text-slate-800 dark:text-slate-100 outline-none focus:border-[#15803d]">
                                <button type="button" @click="openCamera()" class="h-[42px] px-3.5 rounded-lg bg-[#15803d] hover:bg-[#166534] text-white text-xs sm:text-sm font-bold flex items-center gap-2 shadow-xs shrink-0 border-0 cursor-pointer">
                                    <i class="fa-solid fa-camera"></i>
                                    <span class="hidden sm:inline">Camera Scan</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="px-4 py-2.5 border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 flex items-center justify-end gap-2 shrink-0 sticky bottom-0 z-20">
                        <button type="button" wire:click="resetQuickProductForm" class="h-[40px] px-4 rounded-lg bg-[#dc2626] hover:bg-red-700 text-white text-sm font-semibold cursor-pointer border-0">Reset</button>
                        <button type="submit" class="h-[40px] px-5 rounded-lg bg-[#15803d] hover:bg-[#166534] text-white text-sm font-bold cursor-pointer border-0 shadow-sm">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- NESTED SUB-MODAL: Add New Brand (1:1 Reference) --}}
    @if($showQuickBrandModal)
        <div data-marss-modal="true" class="fixed inset-0 z-[75] !m-0 overflow-hidden bg-slate-950/75 backdrop-blur-xs flex items-center justify-center p-4" wire:click.self="$set('showQuickBrandModal', false)">
            <div class="w-full max-w-[440px] max-h-[90vh] bg-white dark:bg-slate-900 rounded-2xl shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-700 flex flex-col">
                <div class="bg-[#15803d] px-4 py-3 text-white flex items-center justify-between shadow-sm shrink-0 sticky top-0 z-20">
                    <h3 class="text-base sm:text-lg font-bold text-white flex items-center gap-2 m-0">
                        <i class="fa-solid fa-tag"></i> Add New Brand
                    </h3>
                    <button type="button" wire:click="$set('showQuickBrandModal', false)" class="w-7 h-7 rounded-full bg-red-600 hover:bg-red-700 text-white flex items-center justify-center border-0 cursor-pointer shadow-xs">
                        <i class="fa-solid fa-xmark text-xs"></i>
                    </button>
                </div>
                <form wire:submit="saveQuickBrand" class="p-5 space-y-4 overflow-y-auto flex-1">
                    <div class="flex items-center gap-3.5 p-3.5 rounded-xl border-[1.5px] border-dashed border-slate-300 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/40">
                        <div class="w-16 h-16 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-2xs flex items-center justify-center overflow-hidden shrink-0">
                            @if($quickBrandLogo)
                                <img src="{{ $quickBrandLogo->temporaryUrl() }}" class="w-full h-full object-cover">
                            @else
                                <i class="fa-regular fa-image text-2xl text-slate-400"></i>
                            @endif
                        </div>
                        <div>
                            <label for="quickRetailPurchaseBrandImg" class="inline-flex items-center gap-2 px-3.5 h-9 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:border-emerald-500 hover:text-emerald-700 shadow-2xs cursor-pointer">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                                <span>Upload Photo</span>
                            </label>
                            <input type="file" wire:model="quickBrandLogo" id="quickRetailPurchaseBrandImg" accept="image/*" class="hidden">
                            <p class="text-[11px] text-slate-400 mt-1.5 mb-0">PNG, JPG or GIF (up to 1 MB)</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Brand Name <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="quickBrandName" placeholder="Enter brand name" class="w-full h-[42px] px-3.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm outline-none focus:border-[#16a34a]">
                        @error('quickBrandName') <span class="text-xs text-rose-500 font-semibold">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Status <span class="text-rose-500">*</span></label>
                        <select wire:model="quickBrandStatus" class="w-full h-[42px] px-3.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm outline-none focus:border-[#16a34a]">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" wire:click="$set('showQuickBrandModal', false)" class="h-10 px-5 rounded-lg bg-[#ef4444] hover:bg-red-600 text-white text-xs sm:text-sm font-bold border-0 cursor-pointer">Cancel</button>
                        <button type="submit" class="h-10 px-5 rounded-lg bg-[#15803d] hover:bg-[#166534] text-white text-xs sm:text-sm font-bold border-0 cursor-pointer shadow-xs">Save Brand</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- NESTED SUB-MODAL: Add New Category (1:1 Reference) --}}
    @if($showQuickCategoryModal)
        <div data-marss-modal="true" class="fixed inset-0 z-[75] !m-0 overflow-hidden bg-slate-950/75 backdrop-blur-xs flex items-center justify-center p-4" wire:click.self="$set('showQuickCategoryModal', false)">
            <div class="w-full max-w-[440px] max-h-[90vh] bg-white dark:bg-slate-900 rounded-2xl shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-700 flex flex-col">
                <div class="bg-[#15803d] px-4 py-3 text-white flex items-center justify-between shadow-sm shrink-0 sticky top-0 z-20">
                    <h3 class="text-base sm:text-lg font-bold text-white flex items-center gap-2 m-0">
                        <i class="fa-solid fa-layer-group"></i> Add New Category
                    </h3>
                    <button type="button" wire:click="$set('showQuickCategoryModal', false)" class="w-7 h-7 rounded-full bg-red-600 hover:bg-red-700 text-white flex items-center justify-center border-0 cursor-pointer shadow-xs">
                        <i class="fa-solid fa-xmark text-xs"></i>
                    </button>
                </div>
                <form wire:submit="saveQuickCategory" class="p-5 space-y-4 overflow-y-auto flex-1">
                    <div class="flex items-center gap-3.5 p-3.5 rounded-xl border-[1.5px] border-dashed border-slate-300 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/40">
                        <div class="w-16 h-16 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-2xs flex items-center justify-center overflow-hidden shrink-0">
                            @if($quickCategoryImage)
                                <img src="{{ $quickCategoryImage->temporaryUrl() }}" class="w-full h-full object-cover">
                            @else
                                <i class="fa-regular fa-image text-2xl text-slate-400"></i>
                            @endif
                        </div>
                        <div>
                            <label for="quickRetailPurchaseCategoryImg" class="inline-flex items-center gap-2 px-3.5 h-9 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:border-emerald-500 hover:text-emerald-700 shadow-2xs cursor-pointer">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                                <span>Upload Photo</span>
                            </label>
                            <input type="file" wire:model="quickCategoryImage" id="quickRetailPurchaseCategoryImg" accept="image/*" class="hidden">
                            <p class="text-[11px] text-slate-400 mt-1.5 mb-0">PNG, JPG or GIF (up to 1 MB)</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Category Name <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="quickCategoryName" placeholder="Enter category name" class="w-full h-[42px] px-3.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm outline-none focus:border-[#16a34a]">
                        @error('quickCategoryName') <span class="text-xs text-rose-500 font-semibold">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Status <span class="text-rose-500">*</span></label>
                        <select wire:model="quickCategoryStatus" class="w-full h-[42px] px-3.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm outline-none focus:border-[#16a34a]">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" wire:click="$set('showQuickCategoryModal', false)" class="h-10 px-5 rounded-lg bg-[#ef4444] hover:bg-red-600 text-white text-xs sm:text-sm font-bold border-0 cursor-pointer">Cancel</button>
                        <button type="submit" class="h-10 px-5 rounded-lg bg-[#15803d] hover:bg-[#166534] text-white text-xs sm:text-sm font-bold border-0 cursor-pointer shadow-xs">Save Category</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- NESTED SUB-MODAL: Add New Sub-Category (1:1 Reference) --}}
    @if($showQuickSubCategoryModal)
        <div data-marss-modal="true" class="fixed inset-0 z-[75] !m-0 overflow-hidden bg-slate-950/75 backdrop-blur-xs flex items-center justify-center p-4" wire:click.self="$set('showQuickSubCategoryModal', false)">
            <div class="w-full max-w-[440px] max-h-[90vh] bg-white dark:bg-slate-900 rounded-2xl shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-700 flex flex-col">
                <div class="bg-[#15803d] px-4 py-3 text-white flex items-center justify-between shadow-sm shrink-0 sticky top-0 z-20">
                    <h3 class="text-base sm:text-lg font-bold text-white flex items-center gap-2 m-0">
                        <i class="fa-solid fa-sitemap"></i> Add New Sub-Category
                    </h3>
                    <button type="button" wire:click="$set('showQuickSubCategoryModal', false)" class="w-7 h-7 rounded-full bg-red-600 hover:bg-red-700 text-white flex items-center justify-center border-0 cursor-pointer shadow-xs">
                        <i class="fa-solid fa-xmark text-xs"></i>
                    </button>
                </div>
                <form wire:submit="saveQuickSubCategory" class="p-5 space-y-4 overflow-y-auto flex-1">
                    <div x-data="{ open: false, q: '' }" @click.outside="open = false" class="relative">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Category <span class="text-rose-500">*</span></label>
                        <button type="button" @click="open = !open" class="w-full h-[42px] px-3.5 rounded-lg border-[1.5px] border-[#16a34a] bg-white dark:bg-slate-800 text-sm font-medium text-slate-700 dark:text-slate-100 flex items-center justify-between gap-2 cursor-pointer">
                            <span class="truncate">{{ $quickSubCategoryParentId ? ($categories->firstWhere('id', $quickSubCategoryParentId)?->category_name ?? 'Select Category *') : 'Select Category *' }}</span>
                            <i class="fa-solid fa-chevron-down text-xs text-slate-400" :class="open ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="open" x-transition style="display: none;" class="absolute left-0 right-0 top-[calc(100%+4px)] z-50 bg-white dark:bg-slate-900 border-[1.5px] border-slate-300 dark:border-slate-700 rounded-xl shadow-xl p-1.5">
                            @if($categories->count() > 8)
                                <input type="text" x-model="q" placeholder="Search Category..." class="w-full h-8 px-2 mb-1 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs outline-none">
                            @endif
                            <div class="max-h-44 overflow-y-auto">
                                @foreach($categories as $c)
                                    <div x-show="!q || '{{ strtolower(addslashes($c->category_name)) }}'.includes(q.toLowerCase())" wire:click="$set('quickSubCategoryParentId', {{ $c->id }})" @click="open = false; q = ''" class="px-2.5 py-1.5 rounded-lg text-xs cursor-pointer hover:bg-emerald-50 dark:hover:bg-slate-800 flex items-center justify-between {{ (int)$quickSubCategoryParentId === (int)$c->id ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 font-bold' : '' }}">
                                        <span>{{ $c->category_name }}</span>
                                        @if((int)$quickSubCategoryParentId === (int)$c->id)<i class="fa-solid fa-check text-[10px] text-emerald-600"></i>@endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @error('quickSubCategoryParentId') <span class="text-xs text-rose-500 font-semibold">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Sub-Category Name <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="quickSubCategoryName" placeholder="Enter sub-category name" class="w-full h-[42px] px-3.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm outline-none focus:border-[#16a34a]">
                        @error('quickSubCategoryName') <span class="text-xs text-rose-500 font-semibold">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Status <span class="text-rose-500">*</span></label>
                        <select wire:model="quickSubCategoryStatus" class="w-full h-[42px] px-3.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm outline-none focus:border-[#16a34a]">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" wire:click="$set('showQuickSubCategoryModal', false)" class="h-10 px-5 rounded-lg bg-[#ef4444] hover:bg-red-600 text-white text-xs sm:text-sm font-bold border-0 cursor-pointer">Cancel</button>
                        <button type="submit" class="h-10 px-5 rounded-lg bg-[#15803d] hover:bg-[#166534] text-white text-xs sm:text-sm font-bold border-0 cursor-pointer shadow-xs">Save Sub-Category</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- SUB-MODAL: Camera Barcode Scanner --}}
    <div x-show="cameraOpen" data-marss-modal="true" style="display: none;" class="fixed inset-0 z-[80] !m-0 overflow-hidden bg-slate-950/75 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="w-full max-w-md bg-white dark:bg-slate-900 rounded-2xl shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-800">
            <div class="bg-[#15803d] px-4 py-3 flex items-center justify-between text-white shadow-sm shrink-0">
                <h3 class="text-sm font-bold flex items-center gap-2 m-0">
                    <i class="fa-solid fa-barcode text-white"></i> Product Barcode Scanner
                </h3>
                <button type="button" @click="stopCamera()" class="w-7 h-7 rounded-full bg-red-600 hover:bg-red-700 text-white flex items-center justify-center border-0 cursor-pointer shadow-xs">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
            </div>
            <div class="p-4 text-center space-y-3">
                <div :class="cameraError ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-sky-50 text-sky-700 border-sky-200'" class="px-3 py-2 rounded-xl border text-xs font-semibold" x-text="cameraStatus"></div>
                <div id="retailPurchaseCameraReader" class="w-full min-h-[240px] bg-black rounded-xl overflow-hidden"></div>
                <div class="flex items-center justify-between gap-2">
                    <span class="px-2.5 py-1 rounded-lg bg-slate-800 text-white text-xs font-mono" x-text="'Scanned: ' + lastScanned"></span>
                    <button type="button" @click="switchCamera()" class="px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 text-xs font-semibold cursor-pointer">
                        <i class="fa-solid fa-camera-rotate mr-1"></i> Switch Camera
                    </button>
                </div>
            </div>
            <div class="px-4 py-3 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-200 dark:border-slate-700">
                <button type="button" @click="stopCamera()" class="w-full h-9 rounded-xl bg-slate-700 hover:bg-slate-800 text-white text-xs font-bold border-0 cursor-pointer">Close</button>
            </div>
        </div>
    </div>

    {{-- MODAL 2: Purchase Return Modal (Max 90vh with Internal Scroll) --}}
    @if($showReturnModal)
        <div data-marss-modal="true" class="fixed inset-0 z-50 !m-0 overflow-hidden bg-slate-950/60 backdrop-blur-[2px] flex items-center justify-center p-3 sm:p-4">
            <div class="w-full max-w-3xl max-h-[90vh] bg-white dark:bg-slate-900 rounded-2xl shadow-2xl overflow-hidden flex flex-col border border-slate-200 dark:border-slate-800">
                <div class="bg-[#15803d] px-5 py-3.5 flex items-center justify-between shrink-0 sticky top-0 z-20">
                    <h2 class="text-base font-bold text-white flex items-center gap-2 m-0">
                        <i class="fa-solid fa-rotate-left"></i>
                        <span>Purchase Return — {{ $returnPurchaseCode }}</span>
                    </h2>
                    <button type="button" wire:click="closeModals" class="w-7 h-7 rounded-full bg-red-600 hover:bg-red-700 text-white flex items-center justify-center border-0 cursor-pointer">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>
                <form wire:submit="submitReturn" class="flex flex-col flex-1 overflow-hidden min-h-0 m-0">
                    <div class="p-4 sm:p-5 space-y-4 overflow-y-auto flex-1 min-h-0">
                        <div class="flex flex-wrap items-center justify-between gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200 dark:border-slate-700 text-xs">
                            <div>Supplier: <strong class="text-slate-800 dark:text-white">{{ $returnSupplierName }}</strong></div>
                            <div class="w-48">
                                <x-ui.date-picker wire:model="returnDate" placeholder="Return Date" alt-format="d-m-Y" class="h-8 pl-8 pr-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs" />
                            </div>
                        </div>

                        <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
                            <table class="w-full text-xs text-left">
                                <thead class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold sticky top-0">
                                    <tr>
                                        <th class="p-2.5">Product</th>
                                        <th class="p-2.5 text-center">Purchased</th>
                                        <th class="p-2.5 text-center">Returned</th>
                                        <th class="p-2.5 text-center">Returnable</th>
                                        <th class="p-2.5 text-end">Cost Price</th>
                                        <th class="p-2.5 text-center w-28">Return Qty</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    @foreach($returnItems as $idx => $ri)
                                        <tr wire:key="ret-item-{{ $ri['detail_id'] }}">
                                            <td class="p-2.5 font-semibold text-slate-800 dark:text-slate-100">{{ $ri['product_name'] }}</td>
                                            <td class="p-2.5 text-center font-mono">{{ $ri['bought_qty'] }}</td>
                                            <td class="p-2.5 text-center font-mono text-amber-600">{{ $ri['returned_qty'] }}</td>
                                            <td class="p-2.5 text-center font-mono font-bold text-emerald-600">{{ $ri['available'] }}</td>
                                            <td class="p-2.5 text-end font-mono">{{ Money::formatBd($ri['cost_price']) }}</td>
                                            <td class="p-2.5 text-center">
                                                <input
                                                    type="number"
                                                    min="0"
                                                    max="{{ $ri['available'] }}"
                                                    wire:model.live="returnItems.{{ $idx }}.return_qty"
                                                    @disabled($ri['available'] <= 0)
                                                    class="w-20 h-8 text-center font-bold rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs outline-none focus:border-emerald-600 disabled:opacity-40"
                                                >
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="px-5 py-3.5 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between shrink-0 sticky bottom-0 z-20">
                        <button type="button" wire:click="closeModals" class="px-4 h-9 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold border-0 cursor-pointer">Cancel</button>
                        <button type="submit" class="px-5 h-9 rounded-xl bg-[#15803d] hover:bg-[#166534] text-white text-xs font-bold inline-flex items-center gap-1.5 border-0 cursor-pointer">
                            <i class="fa-solid fa-rotate-left"></i> Process Return
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- MODAL 3: Split Purchase Invoice Modal (1:1 Reference) --}}
    @if($showSplitModal)
        @php
            $splitStatus = Money::cmp($splitSourceDue, 0) <= 0 ? 'Fully Paid' : (Money::cmp($splitSourcePaid, 0) > 0 ? 'Partial Paid' : 'Unpaid');
            $splitStatusCls = $splitStatus === 'Fully Paid' ? 'bg-emerald-100 text-emerald-700' : ($splitStatus === 'Partial Paid' ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700');
            $canSubmitSplit = $splitPreview && $splitPreview['selected_count'] > 0 && $splitPreview['remaining_count'] > 0;
        @endphp
        <div data-marss-modal="true" class="fixed inset-0 z-50 !m-0 overflow-hidden bg-slate-950/60 backdrop-blur-[2px] flex items-center justify-center p-3 sm:p-4">
            <div class="w-full max-w-[900px] max-h-[92vh] bg-white dark:bg-slate-900 rounded-2xl shadow-2xl overflow-hidden flex flex-col border border-slate-200 dark:border-slate-800">
                <div class="bg-[#15803d] px-5 py-3.5 flex items-center justify-between shrink-0">
                    <h2 class="text-base font-bold text-white flex items-center gap-2 m-0">
                        <i class="fa-solid fa-code-fork text-sm"></i>
                        <span>Split Purchase Invoice</span>
                    </h2>
                    <button type="button" wire:click="closeModals" class="w-7 h-7 rounded-full bg-red-600 hover:bg-red-700 text-white flex items-center justify-center border-0 cursor-pointer">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>

                <div class="p-4 sm:p-5 overflow-y-auto flex-1 space-y-4">
                    {{-- Source Purchase Summary Bar --}}
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 flex flex-wrap items-center justify-between gap-3 text-xs">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 font-mono text-sm">{{ $splitPurchaseCode }}</span>
                            <span class="font-bold text-slate-700 dark:text-slate-200">{{ $splitSupplierName }}</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-3 font-mono">
                            <div>Subtotal: <strong class="text-slate-800 dark:text-white">{{ Money::formatBd($splitSourceSubtotal) }}</strong></div>
                            <div>Paid: <strong class="text-emerald-600 dark:text-emerald-400">{{ Money::formatBd($splitSourcePaid) }}</strong></div>
                            <div>Due: <strong class="text-rose-600 dark:text-rose-400">{{ Money::formatBd($splitSourceDue) }}</strong></div>
                            <div>Status: <span class="px-2 py-0.5 rounded font-bold text-[11px] {{ $splitStatusCls }}">{{ $splitStatus }}</span></div>
                        </div>
                    </div>

                    {{-- Product Line Selection Table --}}
                    <div>
                        <div class="flex items-center justify-between mb-2 text-xs">
                            <span class="font-bold text-slate-700 dark:text-slate-300">Select Product Lines to Move to New Invoice:</span>
                            <span class="text-slate-500 dark:text-slate-400">{{ $splitPreview['selected_count'] ?? 0 }} of {{ count($splitDetails) }} items selected</span>
                        </div>
                        <div class="border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden max-h-56 overflow-y-auto">
                            <table class="w-full text-xs text-left">
                                <thead class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 sticky top-0 border-b border-slate-200 dark:border-slate-700">
                                    <tr>
                                        <th class="p-2 text-center w-10">
                                            <input type="checkbox" wire:change="toggleSplitSelectAll($event.target.checked)" class="rounded text-emerald-600 cursor-pointer">
                                        </th>
                                        <th class="p-2 text-center w-10">SL</th>
                                        <th class="p-2">Product Name</th>
                                        <th class="p-2 text-center w-16">Qty</th>
                                        <th class="p-2 text-end w-28">Cost Price</th>
                                        <th class="p-2 text-end w-28">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    @foreach($splitDetails as $idx => $sd)
                                        <tr wire:key="split-row-{{ $sd['id'] }}" class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                            <td class="p-2 text-center">
                                                <input
                                                    type="checkbox"
                                                    value="{{ $sd['id'] }}"
                                                    wire:model.live="splitSelectedIds"
                                                    @disabled(($sd['return_quantity'] ?? 0) > 0)
                                                    class="rounded text-emerald-600 cursor-pointer disabled:opacity-40"
                                                >
                                            </td>
                                            <td class="p-2 text-center text-slate-500 font-semibold">{{ $idx + 1 }}</td>
                                            <td class="p-2 font-medium text-slate-800 dark:text-slate-100">
                                                {{ $sd['product_name'] }}
                                                @if(($sd['return_quantity'] ?? 0) > 0)
                                                    <span class="text-[10px] text-rose-500 font-bold ml-1">(Returned: {{ $sd['return_quantity'] }})</span>
                                                @endif
                                            </td>
                                            <td class="p-2 text-center font-mono font-bold">{{ $sd['quantity'] }}</td>
                                            <td class="p-2 text-end font-mono">{{ Money::formatBd($sd['cost_price']) }}</td>
                                            <td class="p-2 text-end font-mono font-bold">{{ Money::formatBd($sd['subtotal']) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Split Settings Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                        <div class="p-3.5 rounded-xl border border-purple-200 dark:border-purple-900/50 bg-purple-50/40 dark:bg-purple-950/20 space-y-2.5">
                            <h4 class="font-bold text-purple-700 dark:text-purple-300 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fa-solid fa-file-invoice"></i> New Invoice Settings (Selected Lines)
                            </h4>
                            <div>
                                <label class="font-semibold text-slate-700 dark:text-slate-300 block mb-1">New Reference No</label>
                                <input type="text" wire:model="splitNewRefNo" class="w-full h-8 px-2.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 font-bold text-xs outline-none">
                            </div>
                            <div>
                                <label class="font-semibold text-slate-700 dark:text-slate-300 block mb-1">New Invoice Discount</label>
                                <div class="flex items-center gap-1.5">
                                    <select wire:model.live="splitNewDiscountType" class="w-16 h-8 px-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 font-bold text-xs">
                                        <option value="fixed">৳</option>
                                        <option value="percent">%</option>
                                    </select>
                                    <input type="number" step="any" min="0" wire:model.live="splitNewDiscount" class="flex-1 h-8 px-2.5 text-end font-bold rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs outline-none">
                                </div>
                            </div>
                        </div>

                        <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/40 space-y-2.5">
                            <h4 class="font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fa-solid fa-receipt"></i> Remaining Items Settings
                            </h4>
                            <label class="flex items-center gap-2 cursor-pointer font-semibold text-slate-700 dark:text-slate-300">
                                <input type="checkbox" wire:model.live="splitCustomSourceDiscount" class="rounded text-emerald-600 cursor-pointer">
                                <span>Customize discount on remaining items?</span>
                            </label>
                            @if($splitCustomSourceDiscount)
                                <div>
                                    <label class="font-semibold text-slate-700 dark:text-slate-300 block mb-1">Remaining Discount</label>
                                    <div class="flex items-center gap-1.5">
                                        <select wire:model.live="splitSourceDiscountType" class="w-16 h-8 px-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 font-bold text-xs">
                                            <option value="fixed">৳</option>
                                            <option value="percent">%</option>
                                        </select>
                                        <input type="number" step="any" min="0" wire:model.live="splitSourceDiscountValue" class="flex-1 h-8 px-2.5 text-end font-bold rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs outline-none">
                                    </div>
                                </div>
                            @endif
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 italic m-0">* Default: Retains verified existing discount configuration on remaining products.</p>
                        </div>
                    </div>

                    {{-- Live Financial Impact Preview --}}
                    @if($splitPreview)
                        <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-2xs text-xs">
                            <h4 class="font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-2.5">Financial Impact &amp; Payment Reconciliation</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 space-y-1">
                                    <div class="font-bold text-slate-800 dark:text-slate-200 pb-1 border-b border-slate-200 dark:border-slate-700 flex justify-between">
                                        <span>Original Invoice (Remaining)</span>
                                        <span class="font-normal text-slate-500">{{ $splitPreview['remaining_count'] }} items</span>
                                    </div>
                                    <div class="flex justify-between"><span>Subtotal:</span><strong class="font-mono">{{ Money::formatBd($splitPreview['rem_sub']) }}</strong></div>
                                    <div class="flex justify-between text-rose-500"><span>Discount:</span><strong class="font-mono">-{{ Money::formatBd($splitPreview['rem_disc']) }}</strong></div>
                                    <div class="flex justify-between font-bold border-t border-slate-200 dark:border-slate-700 pt-1"><span>Net Payable:</span><strong class="font-mono">{{ Money::formatBd($splitPreview['rem_net']) }}</strong></div>
                                    <div class="flex justify-between text-emerald-600 dark:text-emerald-400"><span>Allocated Paid:</span><strong class="font-mono">{{ Money::formatBd($splitPreview['rem_paid']) }}</strong></div>
                                    <div class="flex justify-between text-rose-600 dark:text-rose-400 font-bold"><span>Due Balance:</span><strong class="font-mono">{{ Money::formatBd($splitPreview['rem_due']) }}</strong></div>
                                </div>
                                <div class="p-2.5 rounded-lg bg-purple-50/50 dark:bg-purple-950/20 border border-purple-200 dark:border-purple-900/50 space-y-1">
                                    <div class="font-bold text-purple-800 dark:text-purple-300 pb-1 border-b border-purple-200 dark:border-purple-900/50 flex justify-between">
                                        <span>New Split Invoice</span>
                                        <span class="font-normal text-purple-600 dark:text-purple-400">{{ $splitPreview['selected_count'] }} items</span>
                                    </div>
                                    <div class="flex justify-between"><span>Subtotal:</span><strong class="font-mono">{{ Money::formatBd($splitPreview['new_sub']) }}</strong></div>
                                    <div class="flex justify-between text-rose-500"><span>Discount:</span><strong class="font-mono">-{{ Money::formatBd($splitPreview['new_disc']) }}</strong></div>
                                    <div class="flex justify-between font-bold border-t border-purple-200 dark:border-purple-900/50 pt-1"><span>Net Payable:</span><strong class="font-mono">{{ Money::formatBd($splitPreview['new_net']) }}</strong></div>
                                    <div class="flex justify-between text-emerald-600 dark:text-emerald-400"><span>Allocated Paid:</span><strong class="font-mono">{{ Money::formatBd($splitPreview['new_paid']) }}</strong></div>
                                    <div class="flex justify-between text-rose-600 dark:text-rose-400 font-bold"><span>Due Balance:</span><strong class="font-mono">{{ Money::formatBd($splitPreview['new_due']) }}</strong></div>
                                </div>
                            </div>
                            <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800 flex flex-wrap items-center justify-between gap-2 text-[11.5px]">
                                <div class="flex items-center gap-1.5 text-slate-600 dark:text-slate-300">
                                    <i class="fa-solid fa-shield-halved text-emerald-600"></i>
                                    <span>Historical Paid: <strong class="font-mono">{{ Money::formatBd($splitPreview['hist_paid']) }}</strong> | Allocated Sum: <strong class="font-mono">{{ Money::formatBd($splitPreview['alloc_paid']) }}</strong></span>
                                </div>
                                <div class="font-bold text-emerald-600 flex items-center gap-1">
                                    <i class="fa-solid fa-circle-check"></i>
                                    <span>Payment Reconciled (100% Intact)</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="bg-slate-50 dark:bg-slate-800/80 border-t border-slate-200 dark:border-slate-700 px-5 py-3 flex items-center justify-between gap-3 shrink-0">
                    <button type="button" wire:click="closeModals" class="px-4 h-9 rounded-xl text-xs font-bold text-white bg-red-600 hover:bg-red-700 border-0 cursor-pointer">Cancel</button>
                    <button
                        type="button"
                        wire:click="submitSplit"
                        @disabled(!$canSubmitSplit)
                        class="px-5 h-9 rounded-xl bg-[#15803d] hover:bg-[#166534] text-white text-xs font-bold inline-flex items-center gap-2 border-0 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <i class="fa-solid fa-code-fork"></i>
                        <span>Confirm &amp; Split Invoice</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL 4: Custom Delete Confirmation Modal (No wire:confirm) --}}
    @if($showDeleteModal)
        <div data-marss-modal="true" class="fixed inset-0 z-[60] !m-0 overflow-hidden bg-slate-950/60 backdrop-blur-[2px] flex items-center justify-center p-4">
            <div class="w-full max-w-md bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden p-6 text-center">
                <div class="w-14 h-14 rounded-full bg-rose-100 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-trash-can text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-1.5">Delete Purchase?</h3>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mb-5">
                    Are you sure you want to delete <strong class="text-slate-800 dark:text-white font-mono">{{ $deleteTargetLabel }}</strong>? Stock quantities and payment records will be safely reversed.
                </p>
                <div class="flex items-center justify-center gap-3">
                    <button type="button" wire:click="closeModals" class="px-5 h-10 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-bold hover:bg-slate-50 cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" wire:click="executeDelete" class="px-5 h-10 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold border-0 cursor-pointer shadow-sm">
                        Yes, Delete
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL 5: Full-Screen Image Quick View Modal --}}
    @if($showImageModal)
        <div data-marss-modal="true" wire:click="closeModals" class="fixed inset-0 z-[99999] !m-0 overflow-hidden bg-black/85 backdrop-blur-md flex items-center justify-center p-5">
            <div class="relative flex flex-col items-center justify-center max-h-[82vh] max-w-[90vw]" @click.stop>
                <button
                    type="button"
                    wire:click="closeModals"
                    class="absolute -top-3.5 -right-3.5 z-50 w-9 h-9 rounded-full bg-rose-600 hover:bg-rose-700 text-white flex items-center justify-center shadow-2xl border-2 border-white cursor-pointer"
                    title="Close Preview"
                >
                    <i class="fa-solid fa-xmark text-sm font-bold"></i>
                </button>
                <div class="rounded-2xl overflow-hidden shadow-2xl bg-slate-900 border border-white/20 p-1">
                    <img src="{{ $imageModalUrl }}" alt="{{ $imageModalTitle }}" class="block max-h-[78vh] max-w-[86vw] object-contain rounded-xl">
                </div>
            </div>
        </div>
    @endif
</div>
