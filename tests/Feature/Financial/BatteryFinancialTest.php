<?php

namespace Tests\Feature\Financial;

use App\Models\Battery\BatteryBrand;
use App\Models\Battery\BatteryCategory;
use App\Models\Battery\BatteryCustomer;
use App\Models\Battery\BatteryExpense;
use App\Models\Battery\BatteryExpenseType;
use App\Models\Battery\BatteryOpeningBalance;
use App\Models\Battery\BatteryProduct;
use App\Models\Battery\BatterySupplier;
use App\Models\Battery\BatteryUnit;
use App\Services\Battery\BatteryDueCollectionService;
use App\Services\Battery\BatteryPurchaseService;
use App\Services\Battery\BatteryReportService;
use App\Services\Battery\BatteryReturnService;
use App\Services\Battery\BatterySalesService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use InvalidArgumentException;
use Tests\TestCase;

class BatteryFinancialTest extends TestCase
{
    use DatabaseTransactions;

    private function seedBatteryFixture(): array
    {
        $brand = BatteryBrand::create(['name' => 'Hamko Test', 'user_id' => 1]);
        $category = BatteryCategory::create(['name' => 'IPS Battery Test', 'user_id' => 1]);
        $unit = BatteryUnit::create(['unit_name' => 'Pcs', 'user_id' => 1]);

        $product = BatteryProduct::create([
            'product_name' => 'Test 12V 200Ah Battery',
            'quantity' => '10',
            'cost_price' => '12000.00',
            'sell_price' => '15000.00',
            'status' => 'Available',
            'product_code' => ['BAT-200'],
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'user_id' => 1,
        ]);

        $supplier = BatterySupplier::create([
            'supplier_id' => 'BSUP-TEST-1',
            'name' => 'Battery Test Supplier',
            'company' => 'Hamko Corp',
            'mobile' => '01800000001',
            'address' => 'Dhaka',
            'purchase_payable_amount' => '2000.00',
            'user_id' => 1,
        ]);

        $customer = BatteryCustomer::create([
            'customer_id' => 'BCUS-TEST-1',
            'customer_name' => 'Battery Test Customer',
            'mobile' => '01800000002',
            'previous_due_amount' => '1000.00',
            'user_id' => 1,
        ]);

        return compact('product', 'supplier', 'customer');
    }

    public function test_battery_pos_purchase_return_and_split_work_without_legacy_crashes(): void
    {
        $fx = $this->seedBatteryFixture();
        $purchases = app(BatteryPurchaseService::class);
        $sales = app(BatterySalesService::class);
        $returns = app(BatteryReturnService::class);

        // Purchase 5 units @ 14000 -> total 15 units, WAC = (10*12000 + 5*14000)/15 = 12666.67
        $purchase = $purchases->create([
            'supplier_id' => $fx['supplier']->id,
            'date' => '2026-11-19',
            'paid_amount' => '30000.00',
            'items' => [
                ['product_id' => $fx['product']->id, 'quantity' => 5, 'cost_price' => '14000.00'],
            ],
        ], 1);

        $fx['product']->refresh();
        $this->assertSame('15', $fx['product']->quantity);
        $this->assertSame('12666.67', $fx['product']->cost_price);
        $this->assertSame('40000.00', $purchase->due_amount);

        // Battery POS order (legacy threw fatal Schema error!)
        $order = $sales->createOrder([
            'customer_id' => $fx['customer']->id,
            'invoice_date' => '2026-11-19',
            'paid_amount' => '20000.00',
            'items' => [
                ['product_id' => $fx['product']->id, 'quantity' => 2, 'selling_price' => '15000.00'],
            ],
        ], 1);

        $this->assertStringStartsWith('#BAT-INV', $order->order_no);
        $this->assertSame('13', $fx['product']->fresh()->quantity);
        $this->assertSame('10000.00', (string) $order->due_amount);

        // Return 1 unit -> stock 14, order due drops 10000 -> 0 (capped at remaining due)
        $returns->createSalesReturn($order->id, [
            ['order_detail_id' => $order->details->first()->id, 'quantity' => 1],
        ], '2026-11-19', 1);

        $order->refresh();
        $this->assertSame('14', $fx['product']->fresh()->quantity);
        $this->assertSame('0.00', (string) $order->due_amount);
        $this->assertSame('10000.00', (string) $order->return_adjustment_amount);

        // Purchase return of 1 unit (14000) -> purchase due drops 40000 -> 26000; supplier opening payable stays 2000
        $returns->createPurchaseReturn($purchase->id, [
            ['purchase_order_detail_id' => $purchase->orderDetails->first()->id, 'quantity' => 1],
        ], '2026-11-19', 1);

        $purchase->refresh();
        $this->assertSame('13', $fx['product']->fresh()->quantity);
        $this->assertSame('26000.00', (string) $purchase->due_amount);
        $this->assertSame('2000.00', (string) $fx['supplier']->fresh()->purchase_payable_amount);
    }

    public function test_battery_fifo_and_ledger_reconcile_without_double_counting(): void
    {
        $fx = $this->seedBatteryFixture();
        $sales = app(BatterySalesService::class);
        $dueService = app(BatteryDueCollectionService::class);
        $reports = app(BatteryReportService::class);

        $testDate = '2027-02-10';
        BatteryOpeningBalance::create(['date' => $testDate, 'amount' => '5000.00', 'note' => 'Bat Open', 'user_id' => 1]);

        $sales->createOrder([
            'customer_id' => $fx['customer']->id,
            'invoice_date' => $testDate,
            'paid_amount' => '5000.00',
            'items' => [
                ['product_id' => $fx['product']->id, 'quantity' => 1, 'selling_price' => '15000.00'],
            ],
        ], 1);

        // Collect 3000 cash: 1000 clears previous due + 2000 pays order due
        $dueService->collectCustomerDue($fx['customer']->id, [
            'paid_amount' => '3000.00',
            'date' => $testDate,
        ], 1);

        // Pay battery supplier 1500 previous payable
        $dueService->paySupplierDue($fx['supplier']->id, [
            'paid_amount' => '1500.00',
            'date' => $testDate,
            'collection_type' => 'previous',
        ], 1);

        $expType = BatteryExpenseType::first() ?? BatteryExpenseType::create(['type_name' => 'Battery Shop Rent', 'user_id' => 1]);
        BatteryExpense::create([
            'expense_type_id' => $expType->id,
            'expense_amount' => '500.00',
            'expense_details' => 'Power bill',
            'date' => $testDate,
            'user_id' => 1,
        ]);

        // Opening 5000 + Inflow (5000 + 1000 + 2000 = 8000) - Outflow (1500 + 500 = 2000) = 11000.00
        $ledger = $reports->dailyLedger($testDate, $testDate);
        $rp = $reports->dailyReceiptPayment($testDate, $testDate);

        $this->assertSame('8000.00', $ledger['total_inflow']);
        $this->assertSame('2000.00', $ledger['total_outflow']);
        $this->assertSame('11000.00', $ledger['closing_balance']);
        $this->assertSame('11000.00', $rp['closing_balance']);
    }

    public function test_battery_pos_blocks_negative_stock(): void
    {
        $fx = $this->seedBatteryFixture();
        $sales = app(BatterySalesService::class);

        $this->expectException(InvalidArgumentException::class);
        $sales->createOrder([
            'customer_id' => $fx['customer']->id,
            'invoice_date' => '2026-11-19',
            'items' => [
                ['product_id' => $fx['product']->id, 'quantity' => 50, 'selling_price' => '15000.00'],
            ],
        ], 1);
    }

    public function test_fin13_battery_full_invoice_edit_preserves_delivery_return_adj_return_qty_created_at_and_multi_payment_history(): void
    {
        $fx = $this->seedBatteryFixture();
        $sales = app(BatterySalesService::class);
        $returns = app(BatteryReturnService::class);

        // 1. Create Battery order: 4 * 15000 = 60000, delivery = 500, discount = 1000 -> net = 59500, paid = 10000, due = 49500
        $order = $sales->createOrder([
            'customer_id' => $fx['customer']->id,
            'invoice_date' => '2026-11-15',
            'delivery_charge' => '500.00',
            'discount_type' => 'fixed',
            'discount_amount' => '1000.00',
            'paid_amount' => '10000.00',
            'items' => [
                ['product_id' => $fx['product']->id, 'quantity' => 4, 'selling_price' => '15000.00'],
            ],
        ], 1);

        $originalCreatedAt = $order->created_at?->toIso8601String();

        // 2. Record 2nd direct payment: paid 9000, discount 500 -> total paid = 19000, collection disc = 500, due = 40000
        $sales->recordDirectPayment($order->id, [
            'paid_amount' => '9000.00',
            'discount_amount' => '500.00',
            'date' => '2026-11-16',
        ], 1);

        // 3. Return 1 unit (selling_price = 15000, proportional disc = 15000 * (1000/60000) = 250 -> net credit = 14750)
        $returns->createSalesReturn($order->id, [
            ['order_detail_id' => $order->details->first()->id, 'quantity' => 1],
        ], '2026-11-17', 1);

        $order->refresh();
        $this->assertSame('14750.00', (string) $order->return_adjustment_amount);
        $this->assertSame('25250.00', (string) $order->due_amount);

        // 4. Full edit: increase qty 4 -> 5 without passing detail 'id'; omit delivery_charge to verify preservation
        // New subTotal = 75000; discount = 1000; delivery = 500; return_adj = 14750; collection disc = 500
        // Net payable = 75000 - 1000 + 500 - 14750 - 500 = 59250.00; Paid = 19000.00; Expected Due = 40250.00
        $updated = $sales->updateFullInvoice($order->id, [
            'customer_id' => $fx['customer']->id,
            'invoice_date' => '2026-11-18',
            'discount_type' => 'fixed',
            'discount_amount' => '1000.00',
            'items' => [
                ['product_id' => $fx['product']->id, 'quantity' => 5, 'selling_price' => '15000.00'],
            ],
        ], 1);

        $this->assertSame('500.00', (string) $updated->delivery_charge);
        $this->assertSame('14750.00', (string) $updated->return_adjustment_amount);
        $this->assertSame(1, (int) $updated->details->first()->return_quantity);
        $this->assertSame($originalCreatedAt, $updated->created_at?->toIso8601String());
        $this->assertCount(2, $updated->payments);
        $this->assertSame('19000.00', (string) $updated->paid_amount);
        $this->assertSame('40250.00', (string) $updated->due_amount);
    }

    public function test_fin14_battery_customer_and_supplier_profiles_include_all_direct_payments_without_fifo_summary_duplicates(): void
    {
        $fx = $this->seedBatteryFixture();
        $sales = app(BatterySalesService::class);
        $purchases = app(BatteryPurchaseService::class);
        $dueService = app(BatteryDueCollectionService::class);

        // Customer has 1000.00 opening due. Create order: 2 * 15000 = 30000, 1st payment = 5000 -> due = 25000
        $order = $sales->createOrder([
            'customer_id' => $fx['customer']->id,
            'invoice_date' => '2026-11-18',
            'paid_amount' => '5000.00',
            'items' => [['product_id' => $fx['product']->id, 'quantity' => 2, 'selling_price' => '15000.00']],
        ], 1);

        // 2nd & 3rd direct payments (4000 + 6000 = 10000) -> order due = 15000
        $sales->recordDirectPayment($order->id, ['paid_amount' => '4000.00', 'date' => '2026-11-18'], 1);
        $sales->recordDirectPayment($order->id, ['paid_amount' => '6000.00', 'date' => '2026-11-19'], 1);

        // FIFO due collection of 6000 (1000 clears opening due + 5000 on order)
        $dueService->collectCustomerDue($fx['customer']->id, ['paid_amount' => '6000.00', 'date' => '2026-11-19'], 1);

        // Total paid = 5000 (1st) + 4000 (2nd) + 6000 (3rd) + 1000 (FIFO prev) + 5000 (FIFO inv, counted ONCE) = 21000.00
        $custBal = $dueService->customerBalance($fx['customer']->id);
        $this->assertSame('21000.00', $custBal['total_paid']);
        $this->assertSame('10000.00', $custBal['total_due']);

        // Supplier has 2000.00 opening payable. Create purchase: 2 * 12000 = 24000, 1st payment = 4000 -> due = 20000
        $purchase = $purchases->create([
            'supplier_id' => $fx['supplier']->id,
            'date' => '2026-11-18',
            'paid_amount' => '4000.00',
            'items' => [['product_id' => $fx['product']->id, 'quantity' => 2, 'cost_price' => '12000.00']],
        ], 1);

        // 2nd & 3rd direct payments on purchase (3000 + 2000 = 5000) -> purchase due = 15000
        $purchases->recordPayment($purchase->id, ['paid_amount' => '3000.00', 'date' => '2026-11-18'], 1);
        $purchases->recordPayment($purchase->id, ['paid_amount' => '2000.00', 'date' => '2026-11-19'], 1);

        // FIFO supplier payment of 7000 (2000 clears opening payable + 5000 on purchase)
        $dueService->paySupplierDue($fx['supplier']->id, ['paid_amount' => '7000.00', 'date' => '2026-11-19'], 1);

        // Total paid = 4000 (1st) + 3000 (2nd) + 2000 (3rd) + 2000 (FIFO prev) + 5000 (FIFO pur, counted ONCE) = 16000.00
        $suppBal = $dueService->supplierBalance($fx['supplier']->id);
        $this->assertSame('16000.00', $suppBal['total_paid']);
        $this->assertSame('10000.00', $suppBal['total_due']);
    }

    public function test_fin15_battery_purchase_return_validates_current_stock_and_rolls_back_multi_item_tx_on_failure(): void
    {
        $fx = $this->seedBatteryFixture();
        $purchases = app(BatteryPurchaseService::class);
        $sales = app(BatterySalesService::class);
        $returns = app(BatteryReturnService::class);

        $productB = BatteryProduct::create([
            'product_name' => 'Test 12V 100Ah Battery B',
            'quantity' => '1',
            'cost_price' => '8000.00',
            'sell_price' => '10000.00',
            'status' => 'Available',
            'product_code' => ['BAT-100B'],
            'brand_id' => $fx['product']->brand_id,
            'category_id' => $fx['product']->category_id,
            'unit_id' => $fx['product']->unit_id,
            'user_id' => 1,
        ]);

        // Multi-item POS sale with descending product IDs [productB, productA] where productB fails stock
        try {
            $sales->createOrder([
                'customer_id' => $fx['customer']->id,
                'invoice_date' => '2026-11-19',
                'items' => [
                    ['product_id' => $productB->id, 'quantity' => 3, 'selling_price' => '10000.00'],
                    ['product_id' => $fx['product']->id, 'quantity' => 2, 'selling_price' => '15000.00'],
                ],
            ], 1);
            $this->fail('Expected InvalidArgumentException for insufficient battery stock.');
        } catch (InvalidArgumentException) {
            $this->assertSame('10', $fx['product']->fresh()->quantity);
            $this->assertSame('1', $productB->fresh()->quantity);
        }

        // Purchase 3 units of productB (stock becomes 4), sell 3 units (stock becomes 1), then try to purchase-return 2 units
        $purchase = $purchases->create([
            'supplier_id' => $fx['supplier']->id,
            'date' => '2026-11-19',
            'paid_amount' => '0.00',
            'items' => [['product_id' => $productB->id, 'quantity' => 3, 'cost_price' => '8000.00']],
        ], 1);

        $sales->createOrder([
            'customer_id' => $fx['customer']->id,
            'invoice_date' => '2026-11-19',
            'paid_amount' => '30000.00',
            'items' => [['product_id' => $productB->id, 'quantity' => 3, 'selling_price' => '10000.00']],
        ], 1);
        $this->assertSame('1', $productB->fresh()->quantity);

        $this->expectException(InvalidArgumentException::class);
        $returns->createPurchaseReturn($purchase->id, [
            ['purchase_order_detail_id' => $purchase->orderDetails->first()->id, 'quantity' => 2],
        ], '2026-11-19', 1);
    }
}
