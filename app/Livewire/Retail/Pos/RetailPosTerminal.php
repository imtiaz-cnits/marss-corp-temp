<?php

namespace App\Livewire\Retail\Pos;

use App\Models\Retail\RetailBrand;
use App\Models\Retail\RetailCategory;
use App\Models\Retail\RetailCustomer;
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
#[Title('Retail POS Terminal')]
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

    public string $paid_amount = '0';

    public string $payment_method = 'Cash';

    public string $transaction_id = '';

    public string $order_note = '';

    /** @var array<int, array{product_id: int, product_name: string, door_side: ?string, stock: int, cost_price: string, selling_price: string, quantity: int}> */
    public array $cart = [];

    // Quick-add Customer modal
    public bool $showCustomerModal = false;

    public string $newCustName = '';

    public string $newCustMobile = '';

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

    public function mount(): void
    {
        $this->invoice_date = Carbon::today()->toDateString();
        $firstCustomer = RetailCustomer::orderBy('id')->first();
        $this->customer_id = $firstCustomer?->id;
    }

    public function scanBarcode(?string $code = null): void
    {
        $lookup = trim((string) ($code ?? $this->barcodeInput));
        if ($lookup === '') {
            return;
        }

        $product = RetailProduct::query()
            ->where('product_code', $lookup)
            ->orWhere('product_code', 'like', '%"'.$lookup.'"%')
            ->orWhere('product_name', $lookup)
            ->first();

        if (! $product) {
            $this->dispatch('toast', type: 'error', message: "Barcode '{$lookup}' not found in Retail catalog.");

            return;
        }

        $this->addToCart($product->id);
        $this->barcodeInput = '';
    }

    public function addToCart(int $productId): void
    {
        $product = RetailProduct::find($productId);
        if (! $product) {
            return;
        }

        $stock = (int) round((float) ($product->quantity ?? 0));
        if ($stock <= 0) {
            $this->dispatch('toast', type: 'error', message: "'{$product->product_name}' is out of stock (Qty: {$stock}).");

            return;
        }

        foreach ($this->cart as $idx => $item) {
            if ($item['product_id'] === $product->id) {
                if ($stock < $item['quantity'] + 1) {
                    $this->dispatch('toast', type: 'error', message: "Only {$stock} unit(s) available in stock.");

                    return;
                }
                $this->cart[$idx]['quantity']++;
                $this->syncPaidIfFull();

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
        $this->syncPaidIfFull();
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
            $this->removeItem($index);

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
        $this->discount_value = '0';
        $this->delivery_charge = '0';
        $this->paid_amount = '0';
        $this->order_note = '';
    }

    public function payExact(): void
    {
        $this->paid_amount = $this->netPayable();
    }

    private function syncPaidIfFull(): void
    {
        if (Money::cmp($this->paid_amount, 0) === 0 && count($this->cart) === 1) {
            $this->paid_amount = $this->netPayable();
        }
    }

    public function subTotal(): string
    {
        $sum = '0.00';
        foreach ($this->cart as $line) {
            $sum = Money::add($sum, Money::mul($line['selling_price'] ?? 0, max(0, (int) ($line['quantity'] ?? 0))));
        }

        return $sum;
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
            Money::add(Money::sub($this->subTotal(), $this->discountAmount()), Money::maxZero($this->delivery_charge))
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
            'newCustMobile' => ['nullable', 'string', 'max:30'],
            'newCustAddress' => ['nullable', 'string', 'max:255'],
        ]);

        $maxId = (int) (RetailCustomer::max('id') ?? 0);
        $customer = RetailCustomer::create([
            'customer_id' => '#CusID'.str_pad((string) ($maxId + 1), 5, '0', STR_PAD_LEFT),
            'customer_name' => trim($this->newCustName),
            'mobile' => trim($this->newCustMobile),
            'address_details' => trim($this->newCustAddress),
            'previous_due_amount' => '0.00',
            'date' => Carbon::today()->toDateString(),
            'user_id' => Auth::id(),
        ]);

        $this->customer_id = $customer->id;
        $this->reset(['newCustName', 'newCustMobile', 'newCustAddress', 'showCustomerModal']);
        $this->dispatch('toast', type: 'success', message: "Customer '{$customer->customer_name}' added!");
    }

    public function createQuickProduct(): void
    {
        $this->validate([
            'newProdName' => ['required', 'string', 'max:200'],
            'newProdQty' => ['required', 'integer', 'min:1'],
            'newProdCost' => ['required', 'numeric', 'min:0'],
            'newProdSell' => ['required', 'numeric', 'min:0'],
        ]);

        $unitId = RetailUnit::query()->value('id');
        $brandId = $this->newProdBrandId ?: RetailBrand::query()->value('id');
        $catId = $this->newProdCategoryId ?: RetailCategory::query()->value('id');
        $product = RetailProduct::create([
            'product_name' => trim($this->newProdName),
            'product_code' => [trim($this->newProdCode) ?: ('RET-'.time())],
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

        $this->addToCart($product->id);
        $this->reset(['newProdName', 'newProdCode', 'newProdQty', 'newProdCost', 'newProdSell', 'newProdDoorSide', 'newProdBrandId', 'newProdCategoryId', 'showProductModal']);
        $this->dispatch('toast', type: 'success', message: "Product '{$product->product_name}' added & put in cart!");
    }

    public function closeModals(): void
    {
        $this->showCustomerModal = false;
        $this->showProductModal = false;
    }

    public function completeSale(RetailSalesService $salesService): void
    {
        if (empty($this->cart)) {
            $this->dispatch('toast', type: 'error', message: 'Please add at least one product to the cart.');

            return;
        }
        if (! $this->customer_id) {
            $this->dispatch('toast', type: 'error', message: 'Please select a customer.');

            return;
        }

        try {
            $order = $salesService->createOrder([
                'customer_id' => $this->customer_id,
                'invoice_date' => $this->invoice_date ?: Carbon::today()->toDateString(),
                'delivery_charge' => $this->delivery_charge,
                'discount_type' => $this->discount_type,
                'discount_rate' => $this->discount_type === 'percent' ? $this->discount_value : null,
                'discount_amount' => $this->discount_type === 'fixed' ? $this->discount_value : $this->discountAmount(),
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
            $this->dispatch('toast', type: 'success', message: "Invoice {$order->order_no} created!");
            $this->redirect(route('retail.invoice.show', $order->id));
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function render()
    {
        $products = RetailProduct::with(['brand', 'category', 'unit'])
            ->when($this->search !== '', function ($q) {
                $term = '%'.trim($this->search).'%';
                $q->where(fn ($sub) => $sub->where('product_name', 'like', $term)->orWhere('product_code', 'like', $term));
            })
            ->when($this->brandFilter !== '', fn ($q) => $q->where('brand_id', (int) $this->brandFilter))
            ->when($this->categoryFilter !== '', fn ($q) => $q->where('category_id', (int) $this->categoryFilter))
            ->orderByDesc('id')
            ->limit(60)
            ->get();

        return view('livewire.retail.pos.retail-pos-terminal', [
            'products' => $products,
            'brands' => RetailBrand::orderBy('name')->get(),
            'categories' => RetailCategory::orderBy('category_name')->get(),
            'customers' => RetailCustomer::orderByDesc('id')->get(['id', 'customer_id', 'customer_name', 'mobile']),
            'subTotal' => $this->subTotal(),
            'discountAmt' => $this->discountAmount(),
            'netPayable' => $this->netPayable(),
            'dueAmt' => $this->dueAmount(),
        ]);
    }
}
