<?php

namespace Tests\Feature\Isolation;

use App\Models\Battery\BatteryBrand;
use App\Models\Battery\BatteryCategory;
use App\Models\Battery\BatteryCustomer;
use App\Models\Battery\BatteryExpense;
use App\Models\Battery\BatteryExpenseType;
use App\Models\Battery\BatteryOrder;
use App\Models\Battery\BatteryProduct;
use App\Models\Battery\BatterySupplier;
use App\Models\Battery\BatteryUnit;
use App\Models\Retail\RetailBrand;
use App\Models\Retail\RetailCategory;
use App\Models\Retail\RetailCustomer;
use App\Models\Retail\RetailExpense;
use App\Models\Retail\RetailExpenseType;
use App\Models\Retail\RetailInvest;
use App\Models\Retail\RetailInvestorInfo;
use App\Models\Retail\RetailOrder;
use App\Models\Retail\RetailProduct;
use App\Models\Retail\RetailSupplier;
use App\Models\Retail\RetailUnit;
use App\Services\Battery\BatteryDueCollectionService;
use App\Services\Battery\BatteryPurchaseService;
use App\Services\Battery\BatteryReportService;
use App\Services\Battery\BatteryReturnService;
use App\Services\Battery\BatterySalesService;
use App\Services\Retail\RetailDueCollectionService;
use App\Services\Retail\RetailPurchaseService;
use App\Services\Retail\RetailReportService;
use App\Services\Retail\RetailReturnService;
use App\Services\Retail\RetailSalesService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Mandatory 12-Point Cross-Module Isolation Test Suite (Section 7.7 & Section 15.5).
 * Proves Retail transactions NEVER contaminate Battery and Battery transactions NEVER contaminate Retail.
 */
class CrossModuleIsolationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_all_twelve_cross_module_isolation_guarantees(): void
    {
        $date = '2027-03-20';

        // Seed independent Retail entities
        $rBrand = RetailBrand::create(['name' => 'Iso R Brand', 'status' => 'Active', 'user_id' => 1]);
        $rCat = RetailCategory::create(['category_name' => 'Iso R Cat', 'status' => 'Active', 'user_id' => 1]);
        $rUnit = RetailUnit::first() ?? RetailUnit::create(['unit_name' => 'Pcs', 'status' => 'active', 'user_id' => 1]);
        $rProduct = RetailProduct::create([
            'product_name' => 'Iso Retail Item', 'quantity' => '20', 'cost_price' => '100.00', 'sell_price' => '150.00',
            'status' => 'Available', 'product_code' => json_encode(['ISO-R1']), 'brand_id' => $rBrand->id,
            'category_id' => $rCat->id, 'unit_id' => $rUnit->id, 'user_id' => 1,
        ]);
        $rSupplier = RetailSupplier::create([
            'supplier_id' => 'ISO-RSUP', 'name' => 'Iso R Sup', 'company' => 'Co', 'mobile' => '01711111111',
            'address' => 'Pabna', 'purchase_payable_amount' => '1000.00', 'status' => 'Active', 'user_id' => 1,
        ]);
        $rCustomer = RetailCustomer::create([
            'customer_id' => 'ISO-RCUS', 'customer_name' => 'Iso R Cus', 'mobile' => '01711111112',
            'previous_due_amount' => '500.00', 'user_id' => 1,
        ]);

        // Seed independent Battery entities
        $bBrand = BatteryBrand::create(['name' => 'Iso B Brand', 'user_id' => 1]);
        $bCat = BatteryCategory::create(['name' => 'Iso B Cat', 'user_id' => 1]);
        $bUnit = BatteryUnit::create(['unit_name' => 'Pcs', 'user_id' => 1]);
        $bProduct = BatteryProduct::create([
            'product_name' => 'Iso Battery Item', 'quantity' => '30', 'cost_price' => '5000.00', 'sell_price' => '6500.00',
            'status' => 'Available', 'product_code' => ['ISO-B1'], 'brand_id' => $bBrand->id,
            'category_id' => $bCat->id, 'unit_id' => $bUnit->id, 'user_id' => 1,
        ]);
        $bSupplier = BatterySupplier::create([
            'supplier_id' => 'ISO-BSUP', 'name' => 'Iso B Sup', 'company' => 'BCo', 'mobile' => '01811111111',
            'address' => 'Pabna', 'purchase_payable_amount' => '4000.00', 'user_id' => 1,
        ]);
        $bCustomer = BatteryCustomer::create([
            'customer_id' => 'ISO-BCUS', 'customer_name' => 'Iso B Cus', 'mobile' => '01811111112',
            'previous_due_amount' => '2500.00', 'user_id' => 1,
        ]);

        // 1. Retail purchase ONLY updates Retail stock (Battery stock remains 30)
        app(RetailPurchaseService::class)->create([
            'supplier_id' => $rSupplier->id, 'date' => $date, 'paid_amount' => '200.00',
            'items' => [['product_id' => $rProduct->id, 'quantity' => 5, 'cost_price' => '100.00']],
        ], 1);
        $this->assertSame('25', $rProduct->fresh()->quantity);
        $this->assertSame('30', $bProduct->fresh()->quantity);

        // 2. Battery purchase ONLY updates Battery stock (Retail stock remains 25)
        app(BatteryPurchaseService::class)->create([
            'supplier_id' => $bSupplier->id, 'date' => $date, 'paid_amount' => '10000.00',
            'items' => [['product_id' => $bProduct->id, 'quantity' => 4, 'cost_price' => '5000.00']],
        ], 1);
        $this->assertSame('34', $bProduct->fresh()->quantity);
        $this->assertSame('25', $rProduct->fresh()->quantity);

        // 3. Retail sale ONLY updates Retail stock & orders
        $bOrdersBefore = BatteryOrder::count();
        $rOrder = app(RetailSalesService::class)->createOrder([
            'customer_id' => $rCustomer->id, 'invoice_date' => $date, 'paid_amount' => '300.00',
            'items' => [['product_id' => $rProduct->id, 'quantity' => 3, 'selling_price' => '150.00']],
        ], 1);
        $this->assertSame('22', $rProduct->fresh()->quantity);
        $this->assertSame('34', $bProduct->fresh()->quantity);
        $this->assertSame($bOrdersBefore, BatteryOrder::count());

        // 4. Battery sale ONLY updates Battery stock & orders
        $rOrdersBefore = RetailOrder::count();
        $bOrder = app(BatterySalesService::class)->createOrder([
            'customer_id' => $bCustomer->id, 'invoice_date' => $date, 'paid_amount' => '6500.00',
            'items' => [['product_id' => $bProduct->id, 'quantity' => 2, 'selling_price' => '6500.00']],
        ], 1);
        $this->assertSame('32', $bProduct->fresh()->quantity);
        $this->assertSame('22', $rProduct->fresh()->quantity);
        $this->assertSame($rOrdersBefore, RetailOrder::count());

        // 5. Retail return does NOT modify Battery
        app(RetailReturnService::class)->createSalesReturn($rOrder->id, [
            ['order_detail_id' => $rOrder->details->first()->id, 'quantity' => 1],
        ], $date, 1);
        $this->assertSame('23', $rProduct->fresh()->quantity);
        $this->assertSame('32', $bProduct->fresh()->quantity);
        $this->assertSame('0.00', (string) $bOrder->fresh()->return_adjustment_amount);

        // 6. Battery return does NOT modify Retail
        app(BatteryReturnService::class)->createSalesReturn($bOrder->id, [
            ['order_detail_id' => $bOrder->details->first()->id, 'quantity' => 1],
        ], $date, 1);
        $this->assertSame('33', $bProduct->fresh()->quantity);
        $this->assertSame('23', $rProduct->fresh()->quantity);
        $this->assertSame('150.00', (string) $rOrder->fresh()->return_adjustment_amount);

        // 7. Retail payment does NOT modify Battery balances
        app(RetailDueCollectionService::class)->collectCustomerDue($rCustomer->id, ['paid_amount' => '500.00', 'date' => $date], 1);
        $this->assertSame('0.00', (string) $rCustomer->fresh()->previous_due_amount);
        $this->assertSame('2500.00', (string) $bCustomer->fresh()->previous_due_amount);

        // 8. Battery payment does NOT modify Retail balances
        app(BatteryDueCollectionService::class)->collectCustomerDue($bCustomer->id, ['paid_amount' => '1000.00', 'date' => $date], 1);
        $this->assertSame('1500.00', (string) $bCustomer->fresh()->previous_due_amount);
        $this->assertSame('0.00', (string) $rCustomer->fresh()->previous_due_amount);

        // 9 & 10. Retail expenses & Retail invests NEVER appear in Battery reports; Battery expenses NEVER appear in Retail reports
        $rExpType = RetailExpenseType::create(['type_name' => 'Personal Retail', 'status' => 'Active', 'user_id' => 1]);
        RetailExpense::create(['expense_type_id' => $rExpType->id, 'expense_amount' => '111.00', 'expense_details' => 'R', 'date' => $date, 'user_id' => 1]);

        $investor = RetailInvestorInfo::create(['investor_id' => 'INV-ISO-1', 'name' => 'Investor A', 'mobile' => '01900000001', 'user_id' => 1]);
        RetailInvest::create(['investor_info_id' => $investor->id, 'invest_amount' => '9999.00', 'invest_details' => 'Retail Capital', 'date' => $date, 'user_id' => 1]);

        $bExpType = BatteryExpenseType::create(['type_name' => 'Personal Battery', 'user_id' => 1]);
        BatteryExpense::create(['expense_type_id' => $bExpType->id, 'expense_amount' => '222.00', 'expense_details' => 'B', 'date' => $date, 'user_id' => 1]);

        $rReport = app(RetailReportService::class);
        $bReport = app(BatteryReportService::class);

        $rPersonal = $rReport->personalTransactionReport($date, $date);
        $bPersonal = $bReport->personalTransactionReport($date, $date);

        $this->assertSame('111.00', $rPersonal['total_expense']);
        $this->assertSame('9999.00', $rPersonal['total_invest']);
        $this->assertSame('222.00', $bPersonal['total_expense']);
        $this->assertArrayNotHasKey('total_invest', $bPersonal); // FIN-10 leak completely eliminated!

        // 11 & 12. Retail daily ledger & Battery daily ledger remain 100% independent
        $rLedger = $rReport->dailyLedger($date, $date);
        $bLedger = $bReport->dailyLedger($date, $date);

        // Retail: inflow = 300 (POS) + 500 (PrevDue) = 800; outflow = 200 (Purchase) + 111 (Expense) = 311
        $this->assertSame('800.00', $rLedger['total_inflow']);
        $this->assertSame('311.00', $rLedger['total_outflow']);

        // Battery: inflow = 6500 (POS) + 1000 (PrevDue) = 7500; outflow = 10000 (Purchase) + 222 (Expense) = 10222
        $this->assertSame('7500.00', $bLedger['total_inflow']);
        $this->assertSame('10222.00', $bLedger['total_outflow']);
    }
}
