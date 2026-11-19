<?php

namespace App\Livewire\Retail\Pos;

use App\Models\Retail\RetailBrand;
use App\Models\Retail\RetailCategory;
use App\Models\Retail\RetailCustomer;
use App\Models\Retail\RetailOrder;
use App\Models\Retail\RetailProduct;
use App\Models\Retail\RetailUnit;
use App\Services\Retail\RetailSalesService;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::pos')]
#[Title('Point of Sale - MARSS Corporation')]
class RetailPosTerminal extends Component
{
    public string $search = '';

    public string $barcodeInput = '';

    public string $brandFilter = '';

    public string $categoryFilter = '';

    public ?int $customer_id = null;

    public string $invoice_date = '';

    public string $discount_type = 'fixed';

    public string $discount_value = '0';

    public string $delivery_charge = '0';

    public string $exchange_amount = '0';

    public string $paid_amount = '0';

    public string $payment_method = 'Cash';

    public string $transaction_id = '';

    public string $order_note = '';

    public int $productLimit = 40;

    /** @var array<int, array{product_id: int, product_name: string, door_side: ?string, stock: int, cost_price: string, selling_price: string, quantity: int}> */
    public array $cart = [];

    // Quick-add Customer modal
    public bool $showCustomerModal = false;

    public string $newCustName = '';

    public string $newCustMobile = '';

    public string $newCustEmail = '';

    public string $newCustNid = '';

    public string $newCustPrevDue = '0';

    public string $newCustAddress = '';

    // Quick-add Product modal
    public bool $showProductModal = false;

    public string $newProdName = '';

    public string $newProdCode = '';

    public string $newProdQty = '1';

    public string $newProdCost = '0';

    public string $newProdSell = '0';

    public string $newProdDoorSide = '';

    public ?int $newProdBrandId = null;

    public ?int $newProdCategoryId = null;

    public ?int $newProdUnitId = null;

    public function mount(): void
    {
        $this->invoice_date = Carbon::today()->toDateString();
    }

    public function selectBrand(string $brandId = ''): void
    {
        $this->brandFilter = $brandId;
        $this->productLimit = 40;
    }

    public function loadMoreProducts(): void
    {
        $this->productLimit += 25;
    }

    public function selectCustomer(?int $id): void
    {
        $this->customer_id = $id ?: null;
    }

    public function generateQuickBarcode(): void
    {
        $this->newProdCode = 'MARSS-'.mt_rand(100000, 999999);
    }

    public function scanBarcode(?string $code = null): void
    {
        $lookup = trim((string) ($code ?? ($this->barcodeInput !== '' ? $this->barcodeInput : $this->search)));
        if ($lookup === '') {
            return;
        }

        $product = RetailProduct::query()
            ->where('product_code', $lookup)
            ->orWhere('product_code', 'like', '%"'.$lookup.'"%')
            ->orWhere('product_name', $lookup)
            ->first();

        if (! $product) {
            $matches = RetailProduct::query()
                ->where('product_name', 'like', '%'.$lookup.'%')
                ->orWhere('product_code', 'like', '%'.$lookup.'%')
                ->limit(2)
                ->get();

            if ($matches->count() === 1) {
                $product = $matches->first();
            }
        }

        if (! $product) {
            $this->dispatch('toast', type: 'error', message: "Product with code '{$lookup}' was not found.");

            return;
        }

        $this->addToCart($product->id);
        $this->barcodeInput = '';
        $this->search = '';
    }

    public function addToCart(int $productId): void
    {
        $product = RetailProduct::find($productId);
        if (! $product) {
            return;
        }

        $stock = (int) round((float) ($product->quantity ?? 0));
        if ($stock <= 0) {
            $this->dispatch('toast', type: 'error', message: "'{$product->product_name}' is out of stock (Qty: {$stock} Pcs).");

            return;
        }

        foreach ($this->cart as $idx => $item) {
            if ($item['product_id'] === $product->id) {
                if ($stock < $item['quantity'] + 1) {
                    $this->dispatch('toast', type: 'error', message: "Only {$stock} Pcs available in stock.");

                    return;
                }
                $this->cart[$idx]['quantity']++;
                $this->dispatch('pos-beep');

                return;
            }
        }

        $defaultPrice = Money::cmp($product->sell_price ?? 0, 0) > 0
            ? Money::round($product->sell_price)
            : Money::round($product->cost_price ?? 0);

        $this->cart[] = [
            'product_id' => $product->id,
            'product_name' => (string) $product->product_name,
            'door_side' => $product->door_side,
            'stock' => $stock,
            'cost_price' => Money::round($product->cost_price ?? 0),
            'selling_price' => $defaultPrice,
            'quantity' => 1,
        ];
        $this->dispatch('pos-beep');
    }

    public function incrementQty(int $index): void
    {
        if (! isset($this->cart[$index])) {
            return;
        }
        if ($this->cart[$index]['quantity'] + 1 > $this->cart[$index]['stock']) {
            $this->dispatch('toast', type: 'error', message: 'Cannot exceed available stock.');

            return;
        }
        $this->cart[$index]['quantity']++;
    }

    public function decrementQty(int $index): void
    {
        if (! isset($this->cart[$index])) {
            return;
        }
        if ($this->cart[$index]['quantity'] <= 1) {
            $this->dispatch('toast', type: 'error', message: 'Quantity cannot be less than 1. Click trash icon to remove.');

            return;
        }
        $this->cart[$index]['quantity']--;
    }

    public function removeItem(int $index): void
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
    }

    public function clearCart(): void
    {
        $this->cart = [];
        $this->discount_type = 'fixed';
        $this->discount_value = '0';
        $this->delivery_charge = '0';
        $this->exchange_amount = '0';
        $this->paid_amount = '0';
        $this->order_note = '';
        $this->transaction_id = '';
    }

    public function restoreHeldOrder(array $data): void
    {
        $this->customer_id = ! empty($data['customer_id']) ? (int) $data['customer_id'] : null;
        $this->invoice_date = ! empty($data['invoice_date']) ? (string) $data['invoice_date'] : Carbon::today()->toDateString();
        $this->discount_type = ($data['discount_type'] ?? 'fixed') === 'percent' ? 'percent' : 'fixed';
        $this->discount_value = (string) ($data['discount_value'] ?? '0');
        $this->delivery_charge = (string) ($data['delivery_charge'] ?? '0');
        $this->exchange_amount = (string) ($data['exchange_amount'] ?? '0');
        $this->paid_amount = (string) ($data['paid_amount'] ?? '0');
        $this->order_note = (string) ($data['order_note'] ?? '');
        $this->cart = is_array($data['cart'] ?? null) ? array_values($data['cart']) : [];
        $this->dispatch('toast', type: 'success', message: 'Held invoice restored to POS!');
    }

    public function payExact(): void
    {
        $this->paid_amount = $this->netPayable();
    }

    public function itemsTotal(): string
    {
        $sum = '0.00';
        foreach ($this->cart as $line) {
            $sum = Money::add($sum, Money::mul($line['selling_price'] ?? 0, max(0, (int) ($line['quantity'] ?? 0))));
        }

        return $sum;
    }

    public function subTotal(): string
    {
        return Money::add($this->itemsTotal(), Money::maxZero($this->delivery_charge));
    }

    public function discountAmount(): string
    {
        $sub = $this->subTotal();
        if ($this->discount_type === 'percent') {
            return Money::min(Money::percent($sub, $this->discount_value), $sub);
        }

        return Money::min(Money::maxZero($this->discount_value), $sub);
    }

    public function netPayable(): string
    {
        return Money::maxZero(
            Money::sub(
                Money::sub($this->subTotal(), $this->discountAmount()),
                Money::maxZero($this->exchange_amount)
            )
        );
    }

    public function dueAmount(): string
    {
        return Money::maxZero(Money::sub($this->netPayable(), Money::maxZero($this->paid_amount)));
    }

    public function createQuickCustomer(): void
    {
        $this->validate([
            'newCustName' => ['required', 'string', 'max:150'],
            'newCustMobile' => ['required', 'string', 'max:30'],
            'newCustEmail' => ['nullable', 'email', 'max:150'],
            'newCustNid' => ['nullable', 'string', 'max:60'],
            'newCustPrevDue' => ['nullable', 'numeric', 'min:0'],
            'newCustAddress' => ['nullable', 'string', 'max:255'],
        ]);

        $maxId = (int) (RetailCustomer::max('id') ?? 0);
        $customer = RetailCustomer::create([
            'customer_id' => '#CusID'.str_pad((string) ($maxId + 1), 5, '0', STR_PAD_LEFT),
            'customer_name' => trim($this->newCustName),
            'mobile' => trim($this->newCustMobile),
            'email' => trim($this->newCustEmail) ?: null,
            'nid' => trim($this->newCustNid) ?: null,
            'address_details' => trim($this->newCustAddress),
            'previous_due_amount' => Money::round($this->newCustPrevDue ?: 0),
            'date' => Carbon::today()->toDateString(),
            'user_id' => Auth::id(),
        ]);

        $this->customer_id = $customer->id;
        $this->reset(['newCustName', 'newCustMobile', 'newCustEmail', 'newCustNid', 'newCustPrevDue', 'newCustAddress', 'showCustomerModal']);
        $this->dispatch('toast', type: 'success', message: "Customer '{$customer->customer_name}' created & selected!");
    }

    public function createQuickProduct(): void
    {
        $this->validate([
            'newProdName' => ['required', 'string', 'max:200'],
            'newProdQty' => ['required', 'numeric', 'min:0'],
            'newProdCost' => ['required', 'numeric', 'min:0'],
            'newProdSell' => ['required', 'numeric', 'min:0'],
        ]);

        $unitId = $this->newProdUnitId ?: RetailUnit::query()->value('id');
        $brandId = $this->newProdBrandId ?: RetailBrand::query()->value('id');
        $catId = $this->newProdCategoryId ?: RetailCategory::query()->value('id');
        $code = trim($this->newProdCode) ?: ('MARSS-'.mt_rand(100000, 999999));

        $product = RetailProduct::create([
            'product_name' => trim($this->newProdName),
            'product_code' => [$code],
            'quantity' => (string) (int) $this->newProdQty,
            'cost_price' => Money::round($this->newProdCost),
            'sell_price' => Money::round($this->newProdSell),
            'door_side' => $this->newProdDoorSide ?: null,
            'brand_id' => $brandId,
            'category_id' => $catId,
            'unit_id' => $unitId,
            'status' => 'active',
            'user_id' => Auth::id(),
        ]);

        if ((int) $this->newProdQty > 0) {
            $this->addToCart($product->id);
        }
        $this->reset(['newProdName', 'newProdCode', 'newProdQty', 'newProdCost', 'newProdSell', 'newProdDoorSide', 'newProdBrandId', 'newProdCategoryId', 'newProdUnitId', 'showProductModal']);
        $this->dispatch('toast', type: 'success', message: "Product '{$product->product_name}' created!");
    }

    public function closeModals(): void
    {
        $this->showCustomerModal = false;
        $this->showProductModal = false;
    }

    public function completeSale(RetailSalesService $salesService): void
    {
        if (empty($this->cart)) {
            $this->dispatch('toast', type: 'error', message: 'Add at least one product to cart to place order.');

            return;
        }
        if (! $this->customer_id) {
            $this->dispatch('toast', type: 'error', message: '⚠️ অনুগ্রহ করে কাস্টমার সিলেক্ট অথবা তৈরি করুন!');

            return;
        }

        try {
            $totalDiscount = Money::add(
                $this->discount_type === 'fixed' ? Money::maxZero($this->discount_value) : $this->discountAmount(),
                Money::maxZero($this->exchange_amount)
            );

            $order = $salesService->createOrder([
                'customer_id' => $this->customer_id,
                'invoice_date' => $this->invoice_date ?: Carbon::today()->toDateString(),
                'delivery_charge' => $this->delivery_charge,
                'discount_type' => $this->discount_type,
                'discount_rate' => $this->discount_type === 'percent' ? $this->discount_value : null,
                'discount_amount' => $totalDiscount,
                'paid_amount' => $this->paid_amount,
                'payment_method' => $this->payment_method,
                'transaction_id' => $this->transaction_id ?: null,
                'order_note' => $this->order_note ?: null,
                'items' => array_map(fn (array $line) => [
                    'product_id' => $line['product_id'],
                    'quantity' => (int) $line['quantity'],
                    'selling_price' => $line['selling_price'],
                ], $this->cart),
            ], (int) Auth::id());

            $this->clearCart();
            $this->dispatch('toast', type: 'success', message: "Order {$order->order_no} Completed Successfully!");
            $this->redirect(route('retail.invoice.show', $order->id));
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function render()
    {
        $baseQuery = RetailProduct::with(['brand', 'category', 'unit'])
            ->when($this->search !== '', function ($q) {
                $term = '%'.trim($this->search).'%';
                $q->where(fn ($sub) => $sub->where('product_name', 'like', $term)->orWhere('product_code', 'like', $term));
            })
            ->when($this->brandFilter !== '', fn ($q) => $q->where('brand_id', (int) $this->brandFilter))
            ->when($this->categoryFilter !== '', fn ($q) => $q->where('category_id', (int) $this->categoryFilter))
            ->orderByDesc('id');

        $totalMatchingProducts = (clone $baseQuery)->count();
        $products = $baseQuery->limit($this->productLimit)->get();

        $customers = RetailCustomer::query()
            ->withSum('orders as orders_due_sum', 'due_amount')
            ->orderByDesc('id')
            ->get(['id', 'customer_id', 'customer_name', 'mobile', 'address_details', 'previous_due_amount']);

        $selectedCustomer = $this->customer_id
            ? $customers->firstWhere('id', $this->customer_id)
            : null;

        return view('livewire.retail.pos.retail-pos-terminal', [
            'products' => $products,
            'totalMatchingProducts' => $totalMatchingProducts,
            'brands' => RetailBrand::orderBy('id')->get(),
            'categories' => RetailCategory::orderBy('category_name')->get(),
            'units' => RetailUnit::orderBy('id')->get(),
            'customers' => $customers,
            'selectedCustomer' => $selectedCustomer,
            'itemsTotal' => $this->itemsTotal(),
            'subTotal' => $this->subTotal(),
            'discountAmt' => $this->discountAmount(),
            'netPayable' => $this->netPayable(),
            'dueAmt' => $this->dueAmount(),
        ]);
    }
}
