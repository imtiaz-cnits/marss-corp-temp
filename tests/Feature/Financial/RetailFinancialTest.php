<?php

namespace Tests\Feature\Financial;

use App\Models\Retail\RetailBrand;
use App\Models\Retail\RetailCategory;
use App\Models\Retail\RetailCustomer;
use App\Models\Retail\RetailExpense;
use App\Models\Retail\RetailExpenseType;
use App\Models\Retail\RetailOpeningBalance;
use App\Models\Retail\RetailProduct;
use App\Models\Retail\RetailSupplier;
use App\Models\Retail\RetailUnit;
use App\Services\Retail\RetailDueCollectionService;
use App\Services\Retail\RetailPurchaseService;
use App\Services\Retail\RetailReportService;
use App\Services\Retail\RetailReturnService;
use App\Services\Retail\RetailSalesService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use InvalidArgumentException;
use Tests\TestCase;

class RetailFinancialTest extends TestCase
{
    use DatabaseTransactions;

    private function seedRetailFixture(): array
    {
        $brand = RetailBrand::create(['name' => 'Test Retail Brand', 'status' => 'Active', 'user_id' => 1]);
        $category = RetailCategory::create(['category_name' => 'Test Retail Cat', 'status' => 'Active', 'user_id' => 1]);
        $unit = RetailUnit::first() ?? RetailUnit::create(['unit_name' => 'Pcs', 'status' => 'active', 'user_id' => 1]);

        $product = RetailProduct::create([
            'product_name' => 'Test Retail Pump',
            'quantity' => '10',
            'cost_price' => '100.00',
            'sell_price' => '150.00',
            'status' => 'Available',
            'product_code' => json_encode(['TR-001']),
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'user_id' => 1,
        ]);

        $supplier = RetailSupplier::create([
            'supplier_id' => 'SUP-TEST-R1',
            'name' => 'Retail Test Supplier',
            'company' => 'Test Co',
            'mobile' => '01700000001',
            'address' => 'Pabna',
            'purchase_payable_amount' => '500.00',
            'status' => 'Active',
            'user_id' => 1,
        ]);

        $customer = RetailCustomer::create([
            'customer_id' => 'CUS-TEST-R1',
            'customer_name' => 'Retail Test Customer',
            'mobile' => '01700000002',
            'previous_due_amount' => '300.00',
            'user_id' => 1,
        ]);

        return compact('product', 'supplier', 'customer');
    }

    public function test_purchase_weighted_average_cost_and_split_invoice(): void
    {
        $fx = $this->seedRetailFixture();
        $service = app(RetailPurchaseService::class);

        // Existing: 10 units @ 100.00 -> Buy 10 units @ 200.00 => 20 units @ WAC 150.00
        $purchase = $service->create([
            'supplier_id' => $fx['supplier']->id,
            'date' => '2026-11-18',
            'delivery_charge' => '50.00',
            'discount_type' => 'percent',
            'discount_rate' => '10', // 10% of 2000 = 200
            'paid_amount' => '1000.00',
            'payment_method' => 'Cash',
            'items' => [
                ['product_id' => $fx['product']->id, 'quantity' => 10, 'cost_price' => '200.00'],
            ],
        ], 1);

        $fx['product']->refresh();
        $this->assertSame('20', $fx['product']->quantity);
        $this->assertSame('150.00', $fx['product']->cost_price);
        $this->assertSame('2000.00', $purchase->grand_subtotal);
        $this->assertSame('200.00', $purchase->discount_amount);
        // Net = 2000 - 200 + 50 = 1850; Paid = 1000; Due = 850
        $this->assertSame('850.00', $purchase->due_amount);

        // Direct payment accounts for remaining due accurately
        $service->recordPayment($purchase->id, [
            'paid_amount' => '350.00',
            'discount_amount' => '50.00',
            'date' => '2026-11-19',
        ], 1);

        $purchase->refresh();
        $this->assertSame('1350.00', $purchase->paid_amount);
        $this->assertSame('450.00', $purchase->due_amount);
    }

    public function test_pos_sale_blocks_insufficient_stock_and_calculates_exact_totals(): void
    {
        $fx = $this->seedRetailFixture();
        $sales = app(RetailSalesService::class);

        $this->expectException(InvalidArgumentException::class);
        $sales->createOrder([
            'customer_id' => $fx['customer']->id,
            'invoice_date' => '2026-11-19',
            'items' => [
                ['product_id' => $fx['product']->id, 'quantity' => 11, 'selling_price' => '150.00'],
            ],
        ], 1);
    }

    public function test_sales_return_synchronizes_due_and_purchase_return_never_double_deducts_supplier(): void
    {
        $fx = $this->seedRetailFixture();
        $sales = app(RetailSalesService::class);
        $purchases = app(RetailPurchaseService::class);
        $returns = app(RetailReturnService::class);

        // 1. Create sale: 4 * 150 = 600, discount = 60 (10%), paid = 200, due = 340
        $order = $sales->createOrder([
            'customer_id' => $fx['customer']->id,
            'invoice_date' => '2026-11-19',
            'discount_type' => 'fixed',
            'discount_amount' => '60.00',
            'paid_amount' => '200.00',
            'items' => [
                ['product_id' => $fx['product']->id, 'quantity' => 4, 'selling_price' => '150.00'],
            ],
        ], 1);

        $this->assertSame('6', $fx['product']->fresh()->quantity);
        $this->assertSame('340.00', (string) $order->due_amount);

        // Return 2 units -> line=300, proportional disc=30, net credit=270 -> due drops 340 -> 70
        $returns->createSalesReturn($order->id, [
            ['order_detail_id' => $order->details->first()->id, 'quantity' => 2],
        ], '2026-11-19', 1);

        $order->refresh();
        $this->assertSame('8', $fx['product']->fresh()->quantity);
        $this->assertSame('270.00', (string) $order->return_adjustment_amount);
        $this->assertSame('70.00', (string) $order->due_amount);

        // 2. Create purchase: 5 * 100 = 500, paid = 100, due = 400; supplier previous payable = 500
        $purchase = $purchases->create([
            'supplier_id' => $fx['supplier']->id,
            'date' => '2026-11-19',
            'paid_amount' => '100.00',
            'items' => [
                ['product_id' => $fx['product']->id, 'quantity' => 5, 'cost_price' => '100.00'],
            ],
        ], 1);

        // Return 2 units (200) -> purchase due drops 400 -> 200; supplier.purchase_payable_amount MUST STAY 500!
        $returns->createPurchaseReturn($purchase->id, [
            ['purchase_order_detail_id' => $purchase->orderDetails->first()->id, 'quantity' => 2],
        ], '2026-11-19', 1);

        $purchase->refresh();
        $fx['supplier']->refresh();
        $this->assertSame('200.00', $purchase->due_amount);
        $this->assertSame('200.00', (string) $purchase->return_adjustment_amount);
        $this->assertSame('500.00', $fx['supplier']->purchase_payable_amount);
    }

    public function test_fifo_collection_has_zero_double_counting_and_ledger_balances_with_receipt_payment(): void
    {
        $fx = $this->seedRetailFixture();
        $sales = app(RetailSalesService::class);
        $dueService = app(RetailDueCollectionService::class);
        $reports = app(RetailReportService::class);

        $testDate = '2027-01-15';
        RetailOpeningBalance::create(['date' => $testDate, 'amount' => '1000.00', 'note' => 'Test Open', 'user_id' => 1]);

        // Sale: 2 * 150 = 300, paid 100, due 200
        $order = $sales->createOrder([
            'customer_id' => $fx['customer']->id,
            'invoice_date' => $testDate,
            'paid_amount' => '100.00',
            'items' => [
                ['product_id' => $fx['product']->id, 'quantity' => 2, 'selling_price' => '150.00'],
            ],
        ], 1);

        // FIFO collect 450 cash: 300 clears previous due, 150 applies to invoice #order
        $result = $dueService->collectCustomerDue($fx['customer']->id, [
            'paid_amount' => '450.00',
            'discount_amount' => '0.00',
            'date' => $testDate,
            'collection_type' => 'all',
        ], 1);

        $this->assertSame('300.00', $result['applied_previous']);
        $this->assertSame('150.00', $result['applied_invoices']);
        $this->assertSame('50.00', $result['remaining_total_due']);

        // Add operating expense of 75
        $expType = RetailExpenseType::first() ?? RetailExpenseType::create(['type_name' => 'General', 'status' => 'Active', 'user_id' => 1]);
        RetailExpense::create([
            'expense_type_id' => $expType->id,
            'expense_amount' => '75.00',
            'expense_details' => 'Tea',
            'date' => $testDate,
            'user_id' => 1,
        ]);

        // Expected Cash on testDate: Opening 1000 + POS Cash 100 + PrevDue 300 + InvoiceDue 150 - Expense 75 = 1475.00
        // (Must NOT be 1625.00 from double-counting the 150 invoice due collection!)
        $ledger = $reports->dailyLedger($testDate, $testDate);
        $rp = $reports->dailyReceiptPayment($testDate, $testDate);
        $salesRep = $reports->salesReport($testDate, $testDate);

        $this->assertSame('550.00', $ledger['total_inflow']);
        $this->assertSame('75.00', $ledger['total_outflow']);
        $this->assertSame('1475.00', $ledger['closing_balance']);
        $this->assertSame('1475.00', $rp['closing_balance']);
        // Sales report multiplies unit selling_price (150) * qty (2) = 300.00, cost (100) * qty (2) = 200.00
        $this->assertSame('300.00', $salesRep['totals']['selling_price']);
        $this->assertSame('200.00', $salesRep['totals']['total_cost']);
    }

    public function test_fin13_retail_full_invoice_edit_preserves_delivery_return_adj_return_qty_created_at_and_multi_payment_history(): void
    {
        $fx = $this->seedRetailFixture();
        $sales = app(RetailSalesService::class);
        $returns = app(RetailReturnService::class);

        // 1. Create order: 5 * 150 = 750, delivery = 80, discount = 30 -> net = 800, initial paid = 200, due = 600
        $order = $sales->createOrder([
            'customer_id' => $fx['customer']->id,
            'invoice_date' => '2026-11-15',
            'delivery_charge' => '80.00',
            'discount_type' => 'fixed',
            'discount_amount' => '30.00',
            'paid_amount' => '200.00',
            'items' => [
                ['product_id' => $fx['product']->id, 'quantity' => 5, 'selling_price' => '150.00'],
            ],
        ], 1);

        $originalCreatedAt = $order->created_at?->toIso8601String();

        // 2. Record a 2nd direct payment of 150 + discount 10 -> total paid = 350, collection disc = 10, due = 440
        $sales->recordDirectPayment($order->id, [
            'paid_amount' => '150.00',
            'discount_amount' => '10.00',
            'date' => '2026-11-16',
        ], 1);

        // 3. Process sales return of 1 unit (selling_price 150, proportional invoice disc = 150 * (30/750) = 6 -> net credit = 144)
        $returns->createSalesReturn($order->id, [
            ['order_detail_id' => $order->details->first()->id, 'quantity' => 1],
        ], '2026-11-17', 1);

        $order->refresh();
        $this->assertSame('144.00', (string) $order->return_adjustment_amount);
        $this->assertSame('296.00', (string) $order->due_amount);

        // 4. Full edit: change quantity from 5 -> 6 (even without passing detail 'id'!), omit delivery_charge to test preservation
        // New subTotal = 6 * 150 = 900; discount = 30; delivery preserved = 80; return_adj preserved = 144; collection disc = 10
        // Net payable = 900 - 30 + 80 - 144 - 10 = 796.00; Paid = 350.00; Expected Due = 446.00
        $updated = $sales->updateFullInvoice($order->id, [
            'customer_id' => $fx['customer']->id,
            'invoice_date' => '2026-11-18',
            'discount_type' => 'fixed',
            'discount_amount' => '30.00',
            'items' => [
                ['product_id' => $fx['product']->id, 'quantity' => 6, 'selling_price' => '150.00'],
            ],
        ], 1);

        $this->assertSame('80.00', (string) $updated->delivery_charge);
        $this->assertSame('144.00', (string) $updated->return_adjustment_amount);
        $this->assertSame(1, (int) $updated->details->first()->return_quantity);
        $this->assertSame($originalCreatedAt, $updated->created_at?->toIso8601String());
        $this->assertCount(2, $updated->payments);
        $this->assertSame('350.00', (string) $updated->paid_amount);
        $this->assertSame('446.00', (string) $updated->due_amount);
    }

    public function test_fin14_retail_customer_and_supplier_profiles_include_all_direct_payments_without_fifo_summary_duplicates(): void
    {
        $fx = $this->seedRetailFixture();
        $sales = app(RetailSalesService::class);
        $purchases = app(RetailPurchaseService::class);
        $dueService = app(RetailDueCollectionService::class);

        // Customer has 300.00 opening due. Create order: 4 * 150 = 600, 1st payment = 100 -> order due = 500
        $order = $sales->createOrder([
            'customer_id' => $fx['customer']->id,
            'invoice_date' => '2026-11-18',
            'paid_amount' => '100.00',
            'items' => [['product_id' => $fx['product']->id, 'quantity' => 4, 'selling_price' => '150.00']],
        ], 1);

        // 2nd & 3rd direct payments on the invoice (120 + 80 = 200) -> order due = 300
        $sales->recordDirectPayment($order->id, ['paid_amount' => '120.00', 'date' => '2026-11-18'], 1);
        $sales->recordDirectPayment($order->id, ['paid_amount' => '80.00', 'date' => '2026-11-19'], 1);

        // FIFO due collection of 450 (clears 300 opening due + 150 on invoice)
        $dueService->collectCustomerDue($fx['customer']->id, ['paid_amount' => '450.00', 'date' => '2026-11-19'], 1);

        // Total paid must be: 100 (1st) + 120 (2nd) + 80 (3rd) + 300 (FIFO prev) + 150 (FIFO inv, counted ONCE) = 750.00
        $custBal = $dueService->customerBalance($fx['customer']->id);
        $this->assertSame('750.00', $custBal['total_paid']);
        $this->assertSame('150.00', $custBal['total_due']);

        // Supplier has 500.00 opening payable. Create purchase: 5 * 100 = 500, 1st payment = 100 -> purchase due = 400
        $purchase = $purchases->create([
            'supplier_id' => $fx['supplier']->id,
            'date' => '2026-11-18',
            'paid_amount' => '100.00',
            'items' => [['product_id' => $fx['product']->id, 'quantity' => 5, 'cost_price' => '100.00']],
        ], 1);

        // 2nd & 3rd direct payments on purchase (90 + 60 = 150) -> purchase due = 250
        $purchases->recordPayment($purchase->id, ['paid_amount' => '90.00', 'date' => '2026-11-18'], 1);
        $purchases->recordPayment($purchase->id, ['paid_amount' => '60.00', 'date' => '2026-11-19'], 1);

        // FIFO supplier payment of 600 (clears 500 opening payable + 100 on purchase)
        $dueService->paySupplierDue($fx['supplier']->id, ['paid_amount' => '600.00', 'date' => '2026-11-19'], 1);

        // Total paid must be: 100 (1st) + 90 (2nd) + 60 (3rd) + 500 (FIFO prev) + 100 (FIFO pur, counted ONCE) = 850.00
        $suppBal = $dueService->supplierBalance($fx['supplier']->id);
        $this->assertSame('850.00', $suppBal['total_paid']);
        $this->assertSame('150.00', $suppBal['total_due']);
    }

    public function test_fin15_retail_purchase_return_validates_current_stock_and_rolls_back_multi_item_tx_on_failure(): void
    {
        $fx = $this->seedRetailFixture();
        $purchases = app(RetailPurchaseService::class);
        $sales = app(RetailSalesService::class);
        $returns = app(RetailReturnService::class);

        $productB = RetailProduct::create([
            'product_name' => 'Test Retail Valve B',
            'quantity' => '2',
            'cost_price' => '50.00',
            'sell_price' => '80.00',
            'status' => 'Available',
            'product_code' => ['TR-002'],
            'brand_id' => $fx['product']->brand_id,
            'category_id' => $fx['product']->category_id,
            'unit_id' => $fx['product']->unit_id,
            'user_id' => 1,
        ]);

        // Multi-item POS sale with descending product IDs [productB, productA] where productB fails stock (requested 5 > available 2)
        try {
            $sales->createOrder([
                'customer_id' => $fx['customer']->id,
                'invoice_date' => '2026-11-19',
                'items' => [
                    ['product_id' => $productB->id, 'quantity' => 5, 'selling_price' => '80.00'],
                    ['product_id' => $fx['product']->id, 'quantity' => 3, 'selling_price' => '150.00'],
                ],
            ], 1);
            $this->fail('Expected InvalidArgumentException for insufficient stock on productB.');
        } catch (InvalidArgumentException) {
            // Verify atomic rollback: productA stock is still 10
            $this->assertSame('10', $fx['product']->fresh()->quantity);
            $this->assertSame('2', $productB->fresh()->quantity);
        }

        // Purchase 4 units of productB (stock becomes 6), then sell 5 units (stock becomes 1), then try to purchase-return 3 units
        $purchase = $purchases->create([
            'supplier_id' => $fx['supplier']->id,
            'date' => '2026-11-19',
            'paid_amount' => '0.00',
            'items' => [['product_id' => $productB->id, 'quantity' => 4, 'cost_price' => '50.00']],
        ], 1);

        $sales->createOrder([
            'customer_id' => $fx['customer']->id,
            'invoice_date' => '2026-11-19',
            'paid_amount' => '400.00',
            'items' => [['product_id' => $productB->id, 'quantity' => 5, 'selling_price' => '80.00']],
        ], 1);
        $this->assertSame('1', $productB->fresh()->quantity);

        $this->expectException(InvalidArgumentException::class);
        $returns->createPurchaseReturn($purchase->id, [
            ['purchase_order_detail_id' => $purchase->orderDetails->first()->id, 'quantity' => 3],
        ], '2026-11-19', 1);
    }
}
