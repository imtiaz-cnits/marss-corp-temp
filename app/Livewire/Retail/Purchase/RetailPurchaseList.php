<?php

namespace App\Livewire\Retail\Purchase;

use App\Models\Retail\RetailBrand;
use App\Models\Retail\RetailCategory;
use App\Models\Retail\RetailProduct;
use App\Models\Retail\RetailPurchase;
use App\Models\Retail\RetailPurchaseReturn;
use App\Models\Retail\RetailSubCategory;
use App\Models\Retail\RetailSupplier;
use App\Models\Retail\RetailUnit;
use App\Services\Retail\RetailDueCollectionService;
use App\Services\Retail\RetailPurchaseService;
use App\Services\Retail\RetailReturnService;
use App\Services\Shared\SafeImageUploadService;
use App\Support\DateFilter;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Title('Purchase List')]
class RetailPurchaseList extends Component
{
    use WithFileUploads, WithPagination;

    public string $search = '';

    public string $startDate = '';

    public string $endDate = '';

    public string $dateFilter = 'all';

    public int $perPage = 15;

    // Create / Edit Modal
    public bool $showFormModal = false;

    public ?int $editingId = null;

    public ?int $supplier_id = null;

    public string $supplierSearch = '';

    public string $supplierPayableBalance = '0.00';

    public string $date = '';

    public string $referance_no = '';

    public string $discount_type = 'fixed';

    public string $discount_value = '0';

    public string $delivery_charge = '0';

    public string $paid_amount = '0';

    public string $payment_method = '';

    public string $payment_number = '';

    public string $transaction_id = '';

    public string $bank_name = '';

    public string $bank_account = '';

    public string $productSearch = '';

    public string $addProductId = '';

    public $attachment;

    public bool $hasMultiplePayments = false;

    /** @var array<int, array{product_id: int, product_name: string, barcodes: list<string>, quantity: int, cost_price: string}> */
    public array $items = [];

    // Inline Quick Create Supplier Sub-Modal
    public bool $showQuickSupplierModal = false;

    public string $newSupName = '';

    public string $newSupCompany = '';

    public string $newSupMobile = '';

    public string $newSupAddress = '';

    public string $newSupEmail = '';

    public string $newSupStatus = 'active';

    public string $newSupPayable = '0';

    public $newSupImage;

    // Inline Quick Create Product Sub-Modal
    public bool $showQuickProductModal = false;

    public string $newProdName = '';

    public string $newProdCode = '';

    public ?int $newProdBrandId = null;

    public ?int $newProdCategoryId = null;

    public ?int $newProdSubCategoryId = null;

    public ?int $newProdUnitId = null;

    public string $newProdQty = '0';

    public string $newProdCost = '0';

    public string $newProdSell = '0';

    public $newProdImage;

    // Nested Quick Create Brand / Category / Sub-Category (Inside Quick Product Modal)
    public bool $showQuickBrandModal = false;

    public string $quickBrandName = '';

    public string $quickBrandStatus = 'active';

    public $quickBrandLogo;

    public bool $showQuickCategoryModal = false;

    public string $quickCategoryName = '';

    public string $quickCategoryStatus = 'active';

    public $quickCategoryImage;

    public bool $showQuickSubCategoryModal = false;

    public ?int $quickSubCategoryParentId = null;

    public string $quickSubCategoryName = '';

    public string $quickSubCategoryStatus = 'active';

    // Direct Payment Modal (kept for compatibility)
    public bool $showPayModal = false;

    public ?int $payPurchaseId = null;

    public string $payPurchaseCode = '';

    public string $payCurrentDue = '0.00';

    public string $payAmount = '0';

    public string $payDiscount = '0';

    public string $payMethod = 'Cash';

    public string $payDate = '';

    // Purchase Return Modal
    public bool $showReturnModal = false;

    public ?int $returnPurchaseId = null;

    public string $returnPurchaseCode = '';

    public string $returnSupplierName = '';

    public string $returnDate = '';

    /** @var array<int, array{detail_id: int, product_name: string, bought_qty: int, returned_qty: int, available: int, cost_price: string, return_qty: int}> */
    public array $returnItems = [];

    // Split Invoice Modal
    public bool $showSplitModal = false;

    public ?int $splitPurchaseId = null;

    public string $splitPurchaseCode = '';

    public string $splitSupplierName = '';

    public string $splitSourceSubtotal = '0.00';

    public string $splitSourcePaid = '0.00';

    public string $splitSourceDue = '0.00';

    public string $splitSourceDelivery = '0.00';

    public string $splitSourceReturnAdj = '0.00';

    public string $splitSourceDiscountType = 'fixed';

    public string $splitSourceDiscountValue = '0';

    public string $splitSourceDiscountAmount = '0.00';

    public bool $splitCustomSourceDiscount = false;

    public string $splitNewRefNo = '';

    public string $splitNewDiscountType = 'fixed';

    public string $splitNewDiscount = '0';

    /** @var array<int, array{id: int, product_name: string, quantity: int, cost_price: string, subtotal: string, return_quantity: int}> */
    public array $splitDetails = [];

    /** @var list<int> */
    public array $splitSelectedIds = [];

    // Custom Delete Confirmation Modal
    public bool $showDeleteModal = false;

    public ?int $deleteTargetId = null;

    public string $deleteTargetLabel = '';

    // Image Quick View Modal
    public bool $showImageModal = false;

    public string $imageModalUrl = '';

    public string $imageModalTitle = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function updatingStartDate(): void
    {
        $this->dateFilter = 'custom';
        $this->resetPage();
    }

    public function updatingEndDate(): void
    {
        $this->dateFilter = 'custom';
        $this->resetPage();
    }

    public function applyDateFilter(string $filter): void
    {
        $this->dateFilter = $filter;
        $today = Carbon::today();

        if ($filter === 'today') {
            $this->startDate = $today->toDateString();
            $this->endDate = $today->toDateString();
        } elseif ($filter === '7') {
            $this->startDate = $today->copy()->subDays(7)->toDateString();
            $this->endDate = $today->toDateString();
        } elseif ($filter === '30') {
            $this->startDate = $today->copy()->subDays(30)->toDateString();
            $this->endDate = $today->toDateString();
        } elseif ($filter === '365') {
            $this->startDate = $today->copy()->subYear()->toDateString();
            $this->endDate = $today->toDateString();
        } else {
            $this->dateFilter = 'all';
            $this->startDate = '';
            $this->endDate = '';
        }

        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetFormState();
        $this->showFormModal = true;
    }

    public function resetFormState(): void
    {
        $this->reset([
            'editingId', 'supplier_id', 'supplierSearch', 'referance_no',
            'addProductId', 'productSearch', 'attachment', 'items', 'hasMultiplePayments',
            'payment_number', 'transaction_id', 'bank_name', 'bank_account',
        ]);
        $this->supplierPayableBalance = '0.00';
        $this->date = Carbon::today()->toDateString();
        $this->discount_type = 'fixed';
        $this->discount_value = '0';
        $this->delivery_charge = '0';
        $this->paid_amount = '0';
        $this->payment_method = '';
        $this->resetValidation();
    }

    public function restoreHeldDraft(array $draft): void
    {
        $this->resetFormState();
        $this->supplier_id = ! empty($draft['supplier_id']) ? (int) $draft['supplier_id'] : null;
        $this->supplierSearch = (string) ($draft['supplierSearch'] ?? '');
        $this->supplierPayableBalance = (string) ($draft['supplierPayableBalance'] ?? '0.00');
        $this->referance_no = (string) ($draft['referance_no'] ?? '');
        $this->date = ! empty($draft['date']) ? (string) $draft['date'] : Carbon::today()->toDateString();
        $this->discount_type = ($draft['discount_type'] ?? 'fixed') === 'percent' ? 'percent' : 'fixed';
        $this->discount_value = (string) ($draft['discount_value'] ?? '0');
        $this->delivery_charge = (string) ($draft['delivery_charge'] ?? '0');
        $this->paid_amount = (string) ($draft['paid_amount'] ?? '0');
        $this->payment_method = (string) ($draft['payment_method'] ?? '');
        $this->payment_number = (string) ($draft['payment_number'] ?? '');
        $this->transaction_id = (string) ($draft['transaction_id'] ?? '');
        $this->bank_name = (string) ($draft['bank_name'] ?? '');
        $this->bank_account = (string) ($draft['bank_account'] ?? '');
        if (! empty($draft['items']) && is_array($draft['items'])) {
            $this->items = array_values($draft['items']);
        }
        $this->dispatch('toast', type: 'success', message: 'Held purchase draft restored!');
    }

    public function openEdit(int $purchaseId, RetailDueCollectionService $dueService): void
    {
        $p = RetailPurchase::with(['supplier', 'orderDetails.product', 'paymentDetails'])->findOrFail($purchaseId);
        $this->resetValidation();
        $this->editingId = $p->id;
        $this->supplier_id = $p->supplier_id;
        $this->supplierSearch = $p->supplier ? ($p->supplier->name . ($p->supplier->mobile ? ' (' . $p->supplier->mobile . ')' : '')) : '';
        $bal = $p->supplier_id ? $dueService->supplierBalance((int) $p->supplier_id) : ['total_due' => '0.00'];
        $this->supplierPayableBalance = Money::round($bal['total_due'] ?? 0);
        $this->date = Carbon::parse($p->date ?? now())->toDateString();
        $this->referance_no = (string) ($p->referance_no ?? '');
        $this->discount_type = $p->discount_type === 'percent' ? 'percent' : 'fixed';
        $this->discount_value = $this->discount_type === 'percent'
            ? (string) ($p->discount_rate ?? 0)
            : Money::round($p->discount_amount ?? 0);
        $this->delivery_charge = Money::round($p->delivery_charge ?? 0);
        $this->paid_amount = Money::round($p->paid_amount ?? 0);
        $firstPay = $p->paymentDetails->first();
        $this->payment_method = (string) ($firstPay?->payment_method ?? '');
        $this->transaction_id = (string) ($firstPay?->transaction_id ?? '');
        $this->payment_number = '';
        $this->bank_name = '';
        $this->bank_account = '';
        $this->hasMultiplePayments = $p->paymentDetails->count() > 1;
        $this->attachment = null;
        $this->productSearch = '';
        $this->items = $p->orderDetails->map(fn ($d) => [
            'product_id' => (int) $d->product_id,
            'product_name' => (string) ($d->product?->product_name ?? "Product #{$d->product_id}"),
            'barcodes' => $d->product ? $d->product->decodedCodes() : [],
            'quantity' => (int) round((float) $d->quantity),
            'cost_price' => Money::round($d->cost_price ?? 0),
        ])->values()->all();
        $this->showFormModal = true;
    }

    public function selectSupplier(int $supplierId, RetailDueCollectionService $dueService): void
    {
        $sup = RetailSupplier::find($supplierId);
        if (! $sup) {
            return;
        }
        $this->supplier_id = $sup->id;
        $this->supplierSearch = $sup->name . ($sup->mobile ? " ({$sup->mobile})" : '');
        $bal = $dueService->supplierBalance($sup->id);
        $this->supplierPayableBalance = Money::round($bal['total_due'] ?? 0);
    }

    public function addProductById(int $productId): void
    {
        $prod = RetailProduct::find($productId);
        if (! $prod) {
            return;
        }
        $this->appendOrIncrementProduct($prod);
        $this->productSearch = '';
        $this->addProductId = '';
    }

    public function addItem(): void
    {
        if (! $this->addProductId) {
            return;
        }
        $this->addProductById((int) $this->addProductId);
    }

    public function scanOrSearchProduct(?string $code = null): void
    {
        $term = trim($code ?? $this->productSearch);
        if ($term === '') {
            return;
        }

        // Exact barcode match first
        $candidates = RetailProduct::where('product_code', 'like', '%' . $term . '%')
            ->orWhere('product_name', 'like', '%' . $term . '%')
            ->limit(25)
            ->get();

        $exact = $candidates->first(function (RetailProduct $p) use ($term) {
            foreach ($p->decodedCodes() as $c) {
                if (strcasecmp($c, $term) === 0) {
                    return true;
                }
            }

            return strcasecmp((string) $p->product_name, $term) === 0;
        });

        $chosen = $exact ?? ($candidates->count() === 1 ? $candidates->first() : null);

        if ($chosen) {
            $this->appendOrIncrementProduct($chosen);
            $this->productSearch = '';
            $this->dispatch('toast', type: 'success', message: "Added: {$chosen->product_name}");
        } else {
            $this->dispatch('toast', type: 'error', message: "No exact product found for '{$term}'.");
        }
    }

    private function appendOrIncrementProduct(RetailProduct $prod): void
    {
        foreach ($this->items as $idx => $item) {
            if ((int) $item['product_id'] === $prod->id) {
                $this->items[$idx]['quantity'] = ((int) $this->items[$idx]['quantity']) + 1;

                return;
            }
        }

        $this->items[] = [
            'product_id' => $prod->id,
            'product_name' => (string) $prod->product_name,
            'barcodes' => $prod->decodedCodes(),
            'quantity' => 1,
            'cost_price' => Money::round($prod->cost_price ?? 0),
        ];
    }

    public function incrementItemQty(int $idx): void
    {
        if (! isset($this->items[$idx])) {
            return;
        }
        $this->items[$idx]['quantity'] = max(1, ((int) $this->items[$idx]['quantity']) + 1);
    }

    public function decrementItemQty(int $idx): void
    {
        if (! isset($this->items[$idx])) {
            return;
        }
        $this->items[$idx]['quantity'] = max(1, ((int) $this->items[$idx]['quantity']) - 1);
    }

    public function removeItem(int $idx): void
    {
        unset($this->items[$idx]);
        $this->items = array_values($this->items);
    }

    // Inline Quick Create Supplier
    public function openQuickSupplier(): void
    {
        $this->reset(['newSupName', 'newSupCompany', 'newSupMobile', 'newSupAddress', 'newSupEmail', 'newSupImage']);
        $this->newSupStatus = 'active';
        $this->newSupPayable = '';
        $this->resetValidation();
        $this->showQuickSupplierModal = true;
    }

    public function saveQuickSupplier(RetailDueCollectionService $dueService, SafeImageUploadService $uploads): void
    {
        $this->validate([
            'newSupName' => ['required', 'string', 'max:255'],
            'newSupMobile' => ['required', 'string', 'max:30'],
            'newSupEmail' => ['nullable', 'email', 'max:255'],
            'newSupPayable' => ['nullable', 'numeric', 'min:0'],
            'newSupImage' => ['nullable', 'image', 'max:1024'],
        ]);

        $imgPath = $this->newSupImage ? $uploads->store($this->newSupImage, 'suppliers') : null;
        $lastId = (int) (RetailSupplier::max('id') ?? 0);
        $supCode = 'SUP-' . str_pad((string) ($lastId + 10001), 5, '0', STR_PAD_LEFT);

        $sup = RetailSupplier::create([
            'supplier_id' => $supCode,
            'name' => trim($this->newSupName),
            'company' => trim($this->newSupCompany) ?: null,
            'mobile' => trim($this->newSupMobile) ?: null,
            'address' => trim($this->newSupAddress) ?: null,
            'email' => trim($this->newSupEmail) ?: null,
            'img_url' => $imgPath,
            'purchase_payable_amount' => Money::maxZero($this->newSupPayable ?: 0),
            'status' => strtolower($this->newSupStatus) === 'inactive' ? 'inactive' : 'active',
            'user_id' => (int) Auth::id(),
        ]);

        $this->showQuickSupplierModal = false;
        $this->selectSupplier($sup->id, $dueService);
        $this->dispatch('toast', type: 'success', message: "Supplier '{$sup->name}' created & selected!");
    }

    // Inline Quick Create Product
    public function openQuickProduct(): void
    {
        $this->reset(['newProdName', 'newProdCode', 'newProdBrandId', 'newProdCategoryId', 'newProdSubCategoryId', 'newProdUnitId', 'newProdImage']);
        $this->newProdQty = '0';
        $this->newProdCost = '0';
        $this->newProdSell = '0';
        $this->resetValidation();
        $this->showQuickProductModal = true;
    }

    public function resetQuickProductForm(): void
    {
        $this->reset(['newProdName', 'newProdCode', 'newProdBrandId', 'newProdCategoryId', 'newProdSubCategoryId', 'newProdUnitId', 'newProdImage']);
        $this->newProdQty = '0';
        $this->newProdCost = '0';
        $this->newProdSell = '0';
        $this->resetValidation();
    }

    public function saveQuickProduct(SafeImageUploadService $uploads): void
    {
        $this->validate([
            'newProdName' => ['required', 'string', 'max:255'],
            'newProdCategoryId' => ['nullable', 'integer'],
            'newProdQty' => ['nullable', 'numeric', 'min:0'],
            'newProdCost' => ['nullable', 'numeric', 'min:0'],
            'newProdSell' => ['nullable', 'numeric', 'min:0'],
            'newProdImage' => ['nullable', 'image', 'max:1024'],
        ]);

        $imgPath = $this->newProdImage ? $uploads->store($this->newProdImage, 'products') : null;
        $code = trim($this->newProdCode) ?: ('MARSS-' . random_int(100000, 999999));

        $prod = RetailProduct::create([
            'product_name' => trim($this->newProdName),
            'product_code' => [$code],
            'img_url' => $imgPath,
            'brand_id' => $this->newProdBrandId ?: null,
            'category_id' => $this->newProdCategoryId ?: null,
            'sub_category_id' => $this->newProdSubCategoryId ?: null,
            'unit_id' => $this->newProdUnitId ?: null,
            'quantity' => max(0, (int) round((float) ($this->newProdQty ?: 0))),
            'cost_price' => Money::maxZero($this->newProdCost ?: 0),
            'sell_price' => Money::maxZero($this->newProdSell ?: 0),
            'status' => 1,
            'user_id' => (int) Auth::id(),
        ]);

        $this->showQuickProductModal = false;
        $this->appendOrIncrementProduct($prod);
        $this->dispatch('toast', type: 'success', message: "Product '{$prod->product_name}' created & added to cart!");
    }

    public function updatedNewProdCategoryId(): void
    {
        $this->newProdSubCategoryId = null;
    }

    public function openQuickBrand(): void
    {
        $this->reset(['quickBrandName', 'quickBrandLogo']);
        $this->quickBrandStatus = 'active';
        $this->resetValidation();
        $this->showQuickBrandModal = true;
    }

    public function saveQuickBrand(SafeImageUploadService $uploads): void
    {
        $this->validate([
            'quickBrandName' => ['required', 'string', 'max:150'],
            'quickBrandLogo' => ['nullable', 'image', 'max:2048'],
        ]);
        $logoPath = $this->quickBrandLogo ? $uploads->store($this->quickBrandLogo, 'brands') : null;
        $brand = RetailBrand::create([
            'name' => trim($this->quickBrandName),
            'logo' => $logoPath,
            'status' => $this->quickBrandStatus ?: 'active',
            'user_id' => (int) Auth::id(),
        ]);
        $this->newProdBrandId = $brand->id;
        $this->reset(['quickBrandName', 'quickBrandLogo']);
        $this->quickBrandStatus = 'active';
        $this->showQuickBrandModal = false;
        $this->dispatch('toast', type: 'success', message: "Brand '{$brand->name}' created!");
    }

    public function openQuickCategory(): void
    {
        $this->reset(['quickCategoryName', 'quickCategoryImage']);
        $this->quickCategoryStatus = 'active';
        $this->resetValidation();
        $this->showQuickCategoryModal = true;
    }

    public function saveQuickCategory(SafeImageUploadService $uploads): void
    {
        $this->validate([
            'quickCategoryName' => ['required', 'string', 'max:150'],
            'quickCategoryImage' => ['nullable', 'image', 'max:2048'],
        ]);
        $imgPath = $this->quickCategoryImage ? $uploads->store($this->quickCategoryImage, 'categories') : null;
        $cat = RetailCategory::create([
            'category_name' => trim($this->quickCategoryName),
            'img_url' => $imgPath,
            'status' => $this->quickCategoryStatus ?: 'active',
            'user_id' => (int) Auth::id(),
        ]);
        $this->newProdCategoryId = $cat->id;
        $this->newProdSubCategoryId = null;
        $this->reset(['quickCategoryName', 'quickCategoryImage']);
        $this->quickCategoryStatus = 'active';
        $this->showQuickCategoryModal = false;
        $this->dispatch('toast', type: 'success', message: "Category '{$cat->category_name}' created!");
    }

    public function openQuickSubCategory(): void
    {
        $this->reset(['quickSubCategoryName']);
        $this->quickSubCategoryParentId = $this->newProdCategoryId;
        $this->quickSubCategoryStatus = 'active';
        $this->resetValidation();
        $this->showQuickSubCategoryModal = true;
    }

    public function saveQuickSubCategory(): void
    {
        $this->validate([
            'quickSubCategoryParentId' => ['required', 'integer'],
            'quickSubCategoryName' => ['required', 'string', 'max:150'],
        ], [
            'quickSubCategoryParentId.required' => 'Please select a Category.',
        ]);
        $sub = RetailSubCategory::create([
            'category_id' => $this->quickSubCategoryParentId,
            'sub_category_name' => trim($this->quickSubCategoryName),
            'status' => $this->quickSubCategoryStatus ?: 'active',
            'user_id' => (int) Auth::id(),
        ]);
        $this->newProdCategoryId = $this->quickSubCategoryParentId;
        $this->newProdSubCategoryId = $sub->id;
        $this->reset(['quickSubCategoryName']);
        $this->showQuickSubCategoryModal = false;
        $this->dispatch('toast', type: 'success', message: "Sub-Category '{$sub->sub_category_name}' created!");
    }

    public function savePurchase(RetailPurchaseService $service, SafeImageUploadService $uploads): void
    {
        $this->validate([
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'date' => ['required', 'date'],
            'attachment' => ['nullable', 'file', 'max:5120'],
        ]);

        try {
            $attachPath = null;
            if ($this->attachment) {
                $attachPath = $uploads->store($this->attachment, 'purchases', null, true);
            }

            $trxParts = array_filter([
                trim($this->transaction_id),
                trim($this->payment_number) ? 'No: ' . trim($this->payment_number) : '',
                trim($this->bank_name) ? 'Bank: ' . trim($this->bank_name) : '',
                trim($this->bank_account) ? 'A/C: ' . trim($this->bank_account) : '',
            ]);

            $payload = [
                'supplier_id' => $this->supplier_id,
                'date' => $this->date,
                'referance_no' => $this->referance_no ?: null,
                'delivery_charge' => $this->delivery_charge ?: '0',
                'discount_type' => $this->discount_type,
                'discount_rate' => $this->discount_type === 'percent' ? ($this->discount_value ?: '0') : null,
                'discount_amount' => $this->discount_type === 'fixed' ? ($this->discount_value ?: '0') : null,
                'payment_method' => $this->payment_method ?: 'Cash',
                'transaction_id' => ! empty($trxParts) ? implode(' | ', $trxParts) : null,
                'items' => $this->items,
            ];
            if ($attachPath) {
                $payload['attach_document'] = $attachPath;
            }
            if (! $this->editingId || ! $this->hasMultiplePayments) {
                $payload['paid_amount'] = $this->paid_amount ?: '0';
            }

            if ($this->editingId) {
                $p = $service->update($this->editingId, $payload, (int) Auth::id());
                $msg = "Purchase {$p->purchase_id} updated!";
            } else {
                $p = $service->create($payload, (int) Auth::id());
                $msg = "Purchase {$p->purchase_id} created!";
                $this->dispatch('retail-purchase-created');
            }

            $this->showFormModal = false;
            $this->dispatch('toast', type: 'success', message: $msg);
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    // Purchase Return Modal
    public function openReturn(int $purchaseId): void
    {
        $p = RetailPurchase::with(['supplier', 'orderDetails.product'])->findOrFail($purchaseId);
        $this->returnPurchaseId = $p->id;
        $this->returnPurchaseCode = (string) $p->purchase_id;
        $this->returnSupplierName = (string) ($p->supplier?->name ?? 'N/A');
        $this->returnDate = Carbon::today()->toDateString();
        $this->returnItems = $p->orderDetails->map(function ($d) {
            $bought = (int) round((float) $d->quantity);
            $returned = (int) ($d->return_quantity ?? 0);

            return [
                'detail_id' => (int) $d->id,
                'product_name' => (string) ($d->product?->product_name ?? "Product #{$d->product_id}"),
                'bought_qty' => $bought,
                'returned_qty' => $returned,
                'available' => max(0, $bought - $returned),
                'cost_price' => Money::round($d->cost_price ?? 0),
                'return_qty' => 0,
            ];
        })->values()->all();
        $this->showReturnModal = true;
    }

    public function submitReturn(RetailReturnService $returnService): void
    {
        $payload = [];
        foreach ($this->returnItems as $row) {
            $q = (int) ($row['return_qty'] ?? 0);
            if ($q > 0) {
                $payload[] = [
                    'purchase_order_detail_id' => (int) $row['detail_id'],
                    'quantity' => $q,
                ];
            }
        }

        if (empty($payload)) {
            $this->dispatch('toast', type: 'error', message: 'Enter return quantity for at least one product.');

            return;
        }

        try {
            $returnService->createPurchaseReturn(
                (int) $this->returnPurchaseId,
                $payload,
                $this->returnDate ?: Carbon::today()->toDateString(),
                (int) Auth::id(),
            );
            $this->showReturnModal = false;
            $this->dispatch('toast', type: 'success', message: "Purchase return processed for {$this->returnPurchaseCode}!");
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function openPay(int $purchaseId): void
    {
        $p = RetailPurchase::findOrFail($purchaseId);
        $this->payPurchaseId = $p->id;
        $this->payPurchaseCode = (string) $p->purchase_id;
        $this->payCurrentDue = Money::round($p->due_amount ?? 0);
        $this->payAmount = $this->payCurrentDue;
        $this->payDiscount = '0';
        $this->payMethod = 'Cash';
        $this->payDate = Carbon::today()->toDateString();
        $this->showPayModal = true;
    }

    public function submitPay(RetailPurchaseService $service): void
    {
        try {
            $service->recordPayment((int) $this->payPurchaseId, [
                'paid_amount' => $this->payAmount,
                'discount_amount' => $this->payDiscount,
                'payment_method' => $this->payMethod,
                'date' => $this->payDate ?: Carbon::today()->toDateString(),
            ], (int) Auth::id());
            $this->showPayModal = false;
            $this->dispatch('toast', type: 'success', message: "Payment recorded for {$this->payPurchaseCode}!");
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function openSplit(int $purchaseId): void
    {
        $p = RetailPurchase::with(['supplier', 'orderDetails.product', 'paymentDetails'])->findOrFail($purchaseId);
        $this->splitPurchaseId = $p->id;
        $this->splitPurchaseCode = (string) $p->purchase_id;
        $this->splitSupplierName = (string) ($p->supplier?->name ?? 'N/A');
        $this->splitSourceSubtotal = Money::round($p->grand_subtotal ?? 0);
        $this->splitSourcePaid = Money::round($p->paid_amount ?? 0);
        $this->splitSourceDue = Money::round($p->due_amount ?? 0);
        $this->splitSourceDelivery = Money::round($p->delivery_charge ?? 0);
        $this->splitSourceReturnAdj = Money::round($p->return_adjustment_amount ?? 0);
        $this->splitSourceDiscountType = $p->discount_type === 'percent' ? 'percent' : 'fixed';
        $this->splitSourceDiscountValue = $this->splitSourceDiscountType === 'percent'
            ? (string) ($p->discount_rate ?? 0)
            : Money::round($p->discount_amount ?? 0);
        $this->splitSourceDiscountAmount = Money::round($p->discount_amount ?? 0);
        $this->splitCustomSourceDiscount = false;
        $this->splitNewRefNo = $p->referance_no ? ($p->referance_no . '-SPLIT') : ('SPLIT-' . now()->format('YmdHis'));
        $this->splitNewDiscountType = 'fixed';
        $this->splitNewDiscount = '0';
        $this->splitSelectedIds = [];
        $this->splitDetails = $p->orderDetails->map(fn ($d) => [
            'id' => (int) $d->id,
            'product_name' => (string) ($d->product?->product_name ?? "#{$d->product_id}"),
            'quantity' => (int) round((float) $d->quantity),
            'cost_price' => Money::round($d->cost_price ?? 0),
            'subtotal' => Money::round($d->subtotal ?? Money::mul($d->cost_price ?? 0, $d->quantity ?? 0)),
            'return_quantity' => (int) ($d->return_quantity ?? 0),
        ])->values()->all();
        $this->showSplitModal = true;
    }

    public function toggleSplitSelectAll(bool $checked): void
    {
        if (! $checked) {
            $this->splitSelectedIds = [];

            return;
        }

        $this->splitSelectedIds = collect($this->splitDetails)
            ->filter(fn ($d) => (int) ($d['return_quantity'] ?? 0) === 0)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    public function submitSplit(RetailPurchaseService $service): void
    {
        try {
            $options = [
                'referance_no' => trim($this->splitNewRefNo) ?: null,
                'new_discount_type' => $this->splitNewDiscountType,
                'new_discount_rate' => $this->splitNewDiscountType === 'percent' ? $this->splitNewDiscount : null,
                'new_discount_amount' => $this->splitNewDiscountType === 'fixed' ? $this->splitNewDiscount : null,
            ];

            if ($this->splitCustomSourceDiscount) {
                $options['original_discount_type'] = $this->splitSourceDiscountType;
                if ($this->splitSourceDiscountType === 'percent') {
                    $options['original_discount_rate'] = $this->splitSourceDiscountValue;
                } else {
                    $options['original_discount_amount'] = $this->splitSourceDiscountValue;
                }
            }

            $newP = $service->splitInvoice(
                (int) $this->splitPurchaseId,
                array_map('intval', $this->splitSelectedIds),
                $options,
                (int) Auth::id(),
            );
            $this->showSplitModal = false;
            $this->dispatch('toast', type: 'success', message: "Split successful! New purchase {$newP->purchase_id} created.");
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function confirmDelete(int $purchaseId): void
    {
        $p = RetailPurchase::findOrFail($purchaseId);
        $this->deleteTargetId = $p->id;
        $this->deleteTargetLabel = (string) ($p->purchase_id ?: "Purchase #{$p->id}");
        $this->showDeleteModal = true;
    }

    public function executeDelete(RetailPurchaseService $service): void
    {
        if (! $this->deleteTargetId) {
            return;
        }
        $id = $this->deleteTargetId;
        $this->showDeleteModal = false;
        $this->deleteTargetId = null;
        $this->deletePurchase($id, $service);
    }

    public function deletePurchase(int $purchaseId, RetailPurchaseService $service): void
    {
        try {
            $service->delete($purchaseId);
            $this->dispatch('toast', type: 'success', message: 'Purchase deleted and stock reversed cleanly.');
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function openImageModal(string $url, string $title = 'Document Preview'): void
    {
        $this->imageModalUrl = $url;
        $this->imageModalTitle = $title;
        $this->showImageModal = true;
    }

    public function closeModals(): void
    {
        $this->showFormModal = false;
        $this->showPayModal = false;
        $this->showSplitModal = false;
        $this->showReturnModal = false;
        $this->showDeleteModal = false;
        $this->showImageModal = false;
        $this->showQuickSupplierModal = false;
        $this->showQuickProductModal = false;
        $this->showQuickBrandModal = false;
        $this->showQuickCategoryModal = false;
        $this->showQuickSubCategoryModal = false;
    }

    public function render()
    {
        $query = RetailPurchase::with(['supplier', 'orderDetails.product', 'paymentDetails', 'purchaseReturns'])
            ->orderByDesc('id');

        if (trim($this->search) !== '') {
            $t = '%' . trim($this->search) . '%';
            $query->where(fn ($q) => $q
                ->where('purchase_id', 'like', $t)
                ->orWhere('referance_no', 'like', $t)
                ->orWhereHas('supplier', fn ($sq) => $sq->where('name', 'like', $t)->orWhere('supplier_id', 'like', $t))
                ->orWhereHas('orderDetails.product', fn ($pq) => $pq->where('product_code', 'like', $t)->orWhere('product_name', 'like', $t))
            );
        }

        if ($this->startDate || $this->endDate) {
            DateFilter::applyDateFilter($query, 'date', $this->startDate ?: null, $this->endDate ?: null);
        }

        // Totals across filtered dataset
        $allIds = (clone $query)->pluck('id');
        $totalsRow = (clone $query)->reorder()->selectRaw('
            COALESCE(SUM(CAST(COALESCE(grand_subtotal, 0) AS DECIMAL(15,2)) + CAST(COALESCE(delivery_charge, 0) AS DECIMAL(15,2))), 0) as sum_grand,
            COALESCE(SUM(CAST(COALESCE(paid_amount, 0) AS DECIMAL(15,2))), 0) as sum_paid,
            COALESCE(SUM(CAST(COALESCE(due_amount, 0) AS DECIMAL(15,2))), 0) as sum_due
        ')->first();

        $totalReturnSum = $allIds->isNotEmpty()
            ? Money::sum(RetailPurchaseReturn::whereIn('purchase_id', $allIds)->pluck('amount'))
            : '0.00';

        // Form calculations
        $formQty = array_sum(array_map(fn ($i) => max(0, (int) ($i['quantity'] ?? 0)), $this->items));
        $formSub = Money::sum(array_map(fn ($i) => Money::mul($i['cost_price'] ?? 0, max(0, (int) ($i['quantity'] ?? 0))), $this->items));
        $formDelivery = Money::maxZero($this->delivery_charge ?: 0);
        $formDiscAmount = $this->discount_type === 'percent'
            ? Money::min(Money::percent($formSub, Money::maxZero($this->discount_value ?: 0)), $formSub)
            : Money::min(Money::maxZero($this->discount_value ?: 0), $formSub);
        $formNetPayable = Money::maxZero(Money::add(Money::sub($formSub, $formDiscAmount), $formDelivery));
        $formPaid = Money::maxZero($this->paid_amount ?: 0);
        $formDue = Money::maxZero(Money::sub($formNetPayable, $formPaid));
        $formStatus = Money::cmp($formDue, 0) <= 0 ? 'Fully Paid' : (Money::cmp($formPaid, 0) > 0 ? 'Partial Paid' : 'Unpaid');

        // Modal search results
        $filteredSuppliers = collect();
        $filteredProducts = collect();
        if ($this->showFormModal) {
            $supQuery = RetailSupplier::orderBy('name');
            $st = trim($this->supplierSearch);
            if ($st !== '' && ! $this->supplier_id) {
                $supQuery->where(fn ($q) => $q->where('name', 'like', "%{$st}%")->orWhere('mobile', 'like', "%{$st}%")->orWhere('supplier_id', 'like', "%{$st}%"));
            }
            $filteredSuppliers = $supQuery->limit(50)->get(['id', 'supplier_id', 'name', 'mobile', 'company']);

            $pt = trim($this->productSearch);
            if ($pt !== '') {
                $filteredProducts = RetailProduct::where(fn ($q) => $q->where('product_name', 'like', "%{$pt}%")->orWhere('product_code', 'like', "%{$pt}%"))
                    ->orderBy('product_name')
                    ->limit(25)
                    ->get(['id', 'product_name', 'product_code', 'cost_price', 'quantity']);
            }
        }

        // Split preview calculation
        $splitPreview = null;
        if ($this->showSplitModal) {
            $selIds = array_map('intval', $this->splitSelectedIds);
            $newSub = '0.00';
            $remSub = '0.00';
            foreach ($this->splitDetails as $d) {
                if (in_array((int) $d['id'], $selIds, true)) {
                    $newSub = Money::add($newSub, $d['subtotal']);
                } else {
                    $remSub = Money::add($remSub, $d['subtotal']);
                }
            }
            $newDisc = $this->splitNewDiscountType === 'percent'
                ? Money::min(Money::percent($newSub, Money::maxZero($this->splitNewDiscount ?: 0)), $newSub)
                : Money::min(Money::maxZero($this->splitNewDiscount ?: 0), $newSub);
            $newNet = Money::maxZero(Money::sub($newSub, $newDisc));

            if ($this->splitCustomSourceDiscount) {
                $remDisc = $this->splitSourceDiscountType === 'percent'
                    ? Money::min(Money::percent($remSub, Money::maxZero($this->splitSourceDiscountValue ?: 0)), $remSub)
                    : Money::min(Money::maxZero($this->splitSourceDiscountValue ?: 0), $remSub);
            } elseif ($this->splitSourceDiscountType === 'percent' && Money::cmp($this->splitSourceDiscountValue, 0) > 0) {
                $remDisc = Money::min(Money::percent($remSub, Money::maxZero($this->splitSourceDiscountValue)), $remSub);
            } else {
                $ratio = Money::cmp($this->splitSourceSubtotal, 0) > 0 ? Money::div($remSub, $this->splitSourceSubtotal, 6) : '0';
                $remDisc = Money::min(Money::mul($this->splitSourceDiscountAmount, $ratio), $remSub);
            }

            $remNet = Money::maxZero(Money::sub(Money::add(Money::sub($remSub, $remDisc), $this->splitSourceDelivery), $this->splitSourceReturnAdj));
            $histPaid = Money::maxZero($this->splitSourcePaid);
            $combined = Money::add($remNet, $newNet);
            if (Money::cmp($combined, 0) <= 0) {
                $newPaid = '0.00';
                $remPaid = '0.00';
            } elseif (Money::cmp($histPaid, $combined) >= 0) {
                $newPaid = $newNet;
                $remPaid = Money::add($remNet, Money::sub($histPaid, $combined));
            } else {
                $share = Money::div($newNet, $combined, 6);
                $newPaid = Money::min(Money::mul($histPaid, $share), $newNet);
                $remPaid = Money::maxZero(Money::sub($histPaid, $newPaid));
            }

            $splitPreview = [
                'selected_count' => count($selIds),
                'remaining_count' => count($this->splitDetails) - count($selIds),
                'new_sub' => $newSub,
                'new_disc' => $newDisc,
                'new_net' => $newNet,
                'new_paid' => $newPaid,
                'new_due' => Money::maxZero(Money::sub($newNet, $newPaid)),
                'rem_sub' => $remSub,
                'rem_disc' => $remDisc,
                'rem_net' => $remNet,
                'rem_paid' => $remPaid,
                'rem_due' => Money::maxZero(Money::sub($remNet, $remPaid)),
                'hist_paid' => $histPaid,
                'alloc_paid' => Money::add($newPaid, $remPaid),
            ];
        }

        return view('livewire.retail.purchase.retail-purchase-list', [
            'purchases' => $query->paginate($this->perPage),
            'totalGrandSum' => Money::round($totalsRow->sum_grand ?? 0),
            'totalPaidSum' => Money::round($totalsRow->sum_paid ?? 0),
            'totalDueSum' => Money::round($totalsRow->sum_due ?? 0),
            'totalReturnSum' => $totalReturnSum,
            'filteredSuppliers' => $filteredSuppliers,
            'filteredProducts' => $filteredProducts,
            'brands' => $this->showQuickProductModal ? RetailBrand::orderBy('name')->get(['id', 'name']) : collect(),
            'categories' => $this->showQuickProductModal ? RetailCategory::orderBy('category_name')->get(['id', 'category_name']) : collect(),
            'subCategories' => $this->showQuickProductModal
                ? RetailSubCategory::when($this->newProdCategoryId, fn ($q) => $q->where('category_id', $this->newProdCategoryId))->orderBy('sub_category_name')->get(['id', 'sub_category_name'])
                : collect(),
            'units' => $this->showQuickProductModal ? RetailUnit::orderBy('unit_name')->get(['id', 'unit_name']) : collect(),
            'formQty' => $formQty,
            'formSubTotal' => $formSub,
            'formDiscAmount' => $formDiscAmount,
            'formNetPayable' => $formNetPayable,
            'formDue' => $formDue,
            'formStatus' => $formStatus,
            'splitPreview' => $splitPreview,
        ]);
    }
}
