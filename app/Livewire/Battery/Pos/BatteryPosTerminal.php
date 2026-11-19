<?php

namespace App\Livewire\Battery\Pos;

use App\Models\Battery\BatteryBrand;
use App\Models\Battery\BatteryCategory;
use App\Models\Battery\BatteryCustomer;
use App\Models\Battery\BatteryProduct;
use App\Models\Battery\BatteryUnit;
use App\Services\Battery\BatterySalesService;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::pos')]
#[Title('Battery POS Terminal')]
class BatteryPosTerminal extends Component
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

    /** @var array<int, array{product_id: int, product_name: string, stock: int, cost_price: string, selling_price: string, quantity: int}> */
    public array $cart = [];

    public bool $showCustomerModal = false;

    public string $newCustName = '';

    public string $newCustMobile = '';

    public string $newCustAddress = '';

    public bool $showProductModal = false;

    public string $newProdName = '';

    public string $newProdCode = '';

    public string $newProdQty = '1';

    public string $newProdCost = '0';

    public string $newProdSell = '0';

    public function mount(): void
    {
        $this->invoice_date = Carbon::today()->toDateString();
        $this->customer_id = BatteryCustomer::orderBy('id')->value('id');
    }

    public function scanBarcode(?string $code = null): void
    {
        $lookup = trim((string) ($code ?? $this->barcodeInput));
        if ($lookup === '') {
            return;
        }

        $product = BatteryProduct::query()
            ->where('product_code', 'like', '%'.$lookup.'%')
            ->orWhere('product_name', $lookup)
            ->first();

        if (! $product) {
            $this->dispatch('toast', type: 'error', message: "Barcode '{$lookup}' not found in Battery catalog.");

            return;
        }

        $this->addToCart($product->id);
        $this->barcodeInput = '';
    }

    public function addToCart(int $productId): void
    {
        $product = BatteryProduct::find($productId);
        if (! $product) {
            return;
        }

        $stock = (int) round((float) ($product->quantity ?? 0));
        if ($stock <= 0) {
            $this->dispatch('toast', type: 'error', message: "'{$product->product_name}' is out of stock.");

            return;
        }

        foreach ($this->cart as $idx => $item) {
            if ($item['product_id'] === $product->id) {
                if ($stock < $item['quantity'] + 1) {
                    $this->dispatch('toast', type: 'error', message: "Only {$stock} unit(s) available.");

                    return;
                }
                $this->cart[$idx]['quantity']++;

                return;
            }
        }

        $defaultPrice = Money::cmp($product->sell_price ?? 0, 0) > 0
            ? Money::round($product->sell_price)
            : Money::round($product->cost_price ?? 0);

        $this->cart[] = [
            'product_id' => $product->id,
            'product_name' => (string) $product->product_name,
            'stock' => $stock,
            'cost_price' => Money::round($product->cost_price ?? 0),
            'selling_price' => $defaultPrice,
            'quantity' => 1,
        ];
    }

    public function incrementQty(int $idx): void
    {
        if (isset($this->cart[$idx]) && $this->cart[$idx]['quantity'] < $this->cart[$idx]['stock']) {
            $this->cart[$idx]['quantity']++;
        }
    }

    public function decrementQty(int $idx): void
    {
        if (! isset($this->cart[$idx])) {
            return;
        }
        if ($this->cart[$idx]['quantity'] <= 1) {
            $this->removeItem($idx);

            return;
        }
        $this->cart[$idx]['quantity']--;
    }

    public function removeItem(int $idx): void
    {
        unset($this->cart[$idx]);
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

        return $this->discount_type === 'percent'
            ? Money::min(Money::percent($sub, $this->discount_value), $sub)
            : Money::min(Money::maxZero($this->discount_value), $sub);
    }

    public function netPayable(): string
    {
        return Money::maxZero(Money::add(Money::sub($this->subTotal(), $this->discountAmount()), Money::maxZero($this->delivery_charge)));
    }

    public function dueAmount(): string
    {
        return Money::maxZero(Money::sub($this->netPayable(), Money::maxZero($this->paid_amount)));
    }

    public function createQuickCustomer(): void
    {
        $this->validate(['newCustName' => ['required', 'string', 'max:150']]);
        $maxId = (int) (BatteryCustomer::max('id') ?? 0);
        $c = BatteryCustomer::create([
            'customer_id' => '#BCus'.str_pad((string) ($maxId + 1), 5, '0', STR_PAD_LEFT),
            'customer_name' => trim($this->newCustName),
            'mobile' => trim($this->newCustMobile),
            'address_details' => trim($this->newCustAddress),
            'previous_due_amount' => '0.00',
            'date' => Carbon::today()->toDateString(),
            'user_id' => Auth::id(),
        ]);
        $this->customer_id = $c->id;
        $this->reset(['newCustName', 'newCustMobile', 'newCustAddress', 'showCustomerModal']);
        $this->dispatch('toast', type: 'success', message: "Battery customer '{$c->customer_name}' added!");
    }

    public function createQuickProduct(): void
    {
        $this->validate([
            'newProdName' => ['required', 'string', 'max:200'],
            'newProdQty' => ['required', 'integer', 'min:1'],
            'newProdCost' => ['required', 'numeric', 'min:0'],
            'newProdSell' => ['required', 'numeric', 'min:0'],
        ]);
        $code = trim($this->newProdCode) ?: ('BAT-'.time());
        $brandId = BatteryBrand::query()->value('id') ?? BatteryBrand::create(['name' => 'General', 'user_id' => Auth::id()])->id;
        $categoryId = BatteryCategory::query()->value('id') ?? BatteryCategory::create(['name' => 'General', 'user_id' => Auth::id()])->id;
        $p = BatteryProduct::create([
            'product_name' => trim($this->newProdName),
            'product_code' => [$code],
            'quantity' => (string) (int) $this->newProdQty,
            'cost_price' => Money::round($this->newProdCost),
            'sell_price' => Money::round($this->newProdSell),
            'brand_id' => $brandId,
            'category_id' => $categoryId,
            'unit_id' => BatteryUnit::query()->value('id'),
            'status' => 'active',
            'user_id' => Auth::id(),
        ]);
        $this->addToCart($p->id);
        $this->reset(['newProdName', 'newProdCode', 'newProdQty', 'newProdCost', 'newProdSell', 'showProductModal']);
        $this->dispatch('toast', type: 'success', message: "Battery product '{$p->product_name}' added!");
    }

    public function closeModals(): void
    {
        $this->showCustomerModal = false;
        $this->showProductModal = false;
    }

    public function completeSale(BatterySalesService $salesService): void
    {
        if (empty($this->cart)) {
            $this->dispatch('toast', type: 'error', message: 'Cart is empty.');

            return;
        }
        if (! $this->customer_id) {
            $this->dispatch('toast', type: 'error', message: 'Select a Battery customer.');

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
                'items' => array_map(fn ($l) => [
                    'product_id' => $l['product_id'],
                    'quantity' => (int) $l['quantity'],
                    'selling_price' => $l['selling_price'],
                ], $this->cart),
            ], (int) Auth::id());

            $this->clearCart();
            $this->dispatch('toast', type: 'success', message: "Battery Invoice {$order->order_no} created!");
            // Strictly redirects to Battery Invoice Print (fixing legacy Retail redirect bug!)
            $this->redirect(route('battery.invoice.show', $order->id));
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function render()
    {
        $products = BatteryProduct::with(['brand', 'category', 'unit'])
            ->when($this->search !== '', function ($q) {
                $t = '%'.trim($this->search).'%';
                $q->where(fn ($sub) => $sub->where('product_name', 'like', $t)->orWhere('product_code', 'like', $t));
            })
            ->when($this->brandFilter !== '', fn ($q) => $q->where('brand_id', (int) $this->brandFilter))
            ->when($this->categoryFilter !== '', fn ($q) => $q->where('category_id', (int) $this->categoryFilter))
            ->orderByDesc('id')
            ->limit(60)
            ->get();

        return view('livewire.battery.pos.battery-pos-terminal', [
            'products' => $products,
            'brands' => BatteryBrand::orderBy('name')->get(),
            'categories' => BatteryCategory::orderBy('name')->get(),
            'customers' => BatteryCustomer::orderByDesc('id')->get(['id', 'customer_id', 'customer_name', 'mobile']),
            'subTotal' => $this->subTotal(),
            'discountAmt' => $this->discountAmount(),
            'netPayable' => $this->netPayable(),
            'dueAmt' => $this->dueAmount(),
        ]);
    }
}
