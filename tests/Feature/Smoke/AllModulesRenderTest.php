<?php

namespace Tests\Feature\Smoke;

use App\Models\Battery\BatteryBrand;
use App\Models\Battery\BatteryCategory;
use App\Models\Battery\BatteryCustomer;
use App\Models\Battery\BatteryProduct;
use App\Models\Battery\BatterySupplier;
use App\Models\Battery\BatteryUnit;
use App\Models\Retail\RetailCustomer;
use App\Models\Retail\RetailOrder;
use App\Models\Retail\RetailProductReturn;
use App\Models\Retail\RetailPurchase;
use App\Models\Retail\RetailPurchaseReturn;
use App\Models\Retail\RetailSupplier;
use App\Models\Shared\User;
use App\Services\Battery\BatteryPurchaseService;
use App\Services\Battery\BatteryReturnService;
use App\Services\Battery\BatterySalesService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AllModulesRenderTest extends TestCase
{
    use DatabaseTransactions;

    public function test_all_shared_retail_and_battery_routes_render_http_200_for_admin(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->firstOrFail();
        $this->actingAs($admin);

        $staticRoutes = [
            // Shared
            'settings.profile',
            'settings.users',
            'settings.activity-log',
            'settings.districts',
            'settings.upazilas',
            'settings.thanas',
            'settings.locations',

            // Retail (all 31 non-parameterized pages)
            'retail.dashboard',
            'retail.pos',
            'retail.invoices',
            'retail.products',
            'retail.brands',
            'retail.categories',
            'retail.sub-categories',
            'retail.stock-overview',
            'retail.barcode',
            'retail.product-barcode',
            'retail.suppliers',
            'retail.supplier.due',
            'retail.supplier.due-collection',
            'retail.purchases',
            'retail.customers',
            'retail.customer.due',
            'retail.customer.due-collection',
            'retail.expenses',
            'retail.expense-types',
            'retail.staff-profile',
            'retail.returns',
            'retail.opening-balance',
            'retail.reports.daily-ledger',
            'retail.reports.sales',
            'retail.reports.income-expense',
            'retail.reports.daily-receipt-payment',
            'retail.reports.personal-transaction',
            'retail.reports.stock-out',
            'retail.reports.due-invoice',
            'retail.reports.customer-invoice',
            'retail.reports.best-selling',
            'retail.investors',
            'retail.invests',

            // Battery (all 28 non-parameterized pages)
            'battery.dashboard',
            'battery.pos',
            'battery.invoices',
            'battery.products',
            'battery.stock-overview',
            'battery.brands',
            'battery.categories',
            'battery.sub-categories',
            'battery.units',
            'battery.barcode.generate',
            'battery.suppliers',
            'battery.purchases',
            'battery.purchase.payments',
            'battery.supplier.due',
            'battery.supplier.due.collection',
            'battery.customers',
            'battery.customer.due',
            'battery.customer.due.collection',
            'battery.expenses',
            'battery.expense.types',
            'battery.sales.returns',
            'battery.purchase.returns',
            'battery.opening.balance',
            'battery.reports.daily-ledger',
            'battery.reports.sales',
            'battery.reports.income-expense',
            'battery.reports.daily-receipt-payment',
            'battery.reports.personal-transaction',
            'battery.reports.stock-out',
        ];

        foreach ($staticRoutes as $routeName) {
            $resp = $this->get(route($routeName));
            $this->assertEquals(
                200,
                $resp->getStatusCode(),
                "Route [{$routeName}] returned HTTP {$resp->getStatusCode()} instead of 200.",
            );
        }

        // Dynamic Retail profile & print routes against real historical records
        if ($order = RetailOrder::orderByDesc('id')->first()) {
            $this->get(route('retail.invoice.show', $order->id))->assertOk()->assertSee('MARSS');
            $this->get(route('retail.invoice.due-print', $order->id))->assertOk();
            $this->get(route('retail.invoice.receipt'))->assertOk();
        }
        if ($purchase = RetailPurchase::orderByDesc('id')->first()) {
            $this->get(route('retail.purchase.show', $purchase->id))->assertOk()->assertSee($purchase->purchase_id);
        }
        if ($cust = RetailCustomer::first()) {
            $this->get(route('retail.customer.profile', $cust->id))->assertOk()->assertSee($cust->customer_name);
        }
        if ($supp = RetailSupplier::first()) {
            $this->get(route('retail.supplier.profile', $supp->id))->assertOk()->assertSee($supp->name);
        }
        if ($ret = RetailProductReturn::first()) {
            $this->get(route('retail.return.show', $ret->id))->assertOk()->assertSee($ret->return_number);
        }

        // Controlled synthetic fixture inside rolled-back transaction for Retail Purchase Return & all Battery dynamic profile/print routes
        if ($purchase = RetailPurchase::with('orderDetails')->first()) {
            $pRet = RetailPurchaseReturn::create([
                'return_number' => '#PRET99999',
                'purchase_id' => $purchase->id,
                'purchase_order_detail_id' => $purchase->orderDetails->first()?->id,
                'supplier_id' => $purchase->supplier_id,
                'product_id' => $purchase->orderDetails->first()?->product_id ?? 1,
                'quantity' => 1,
                'unit_price' => '100.00',
                'amount' => '100.00',
                'discount_amount' => '0.00',
                'due_amount' => '100.00',
                'date' => '2026-11-19',
                'user_id' => $admin->id,
            ]);
            $this->get(route('retail.purchase-return.show', $pRet->id))->assertOk()->assertSee('#PRET99999');
        }

        $bBrand = BatteryBrand::create(['name' => 'Smoke Bat Brand', 'user_id' => $admin->id]);
        $bCat = BatteryCategory::create(['name' => 'Smoke Bat Cat', 'user_id' => $admin->id]);
        $bUnit = BatteryUnit::create(['unit_name' => 'Pcs', 'user_id' => $admin->id]);
        $bProd = BatteryProduct::create([
            'product_name' => 'Smoke 12V Battery', 'product_code' => ['SBAT-1'], 'quantity' => '10',
            'cost_price' => '5000.00', 'sell_price' => '6000.00', 'status' => 'Available',
            'brand_id' => $bBrand->id, 'category_id' => $bCat->id, 'unit_id' => $bUnit->id, 'user_id' => $admin->id,
        ]);
        $bSupp = BatterySupplier::create([
            'supplier_id' => '#BSup999', 'name' => 'Smoke Battery Supplier', 'company' => 'SB Co',
            'mobile' => '01800000099', 'address' => 'Pabna', 'purchase_payable_amount' => '0.00', 'user_id' => $admin->id,
        ]);
        $bCust = BatteryCustomer::create([
            'customer_id' => '#BCus999', 'customer_name' => 'Smoke Battery Customer',
            'mobile' => '01800000098', 'previous_due_amount' => '0.00', 'user_id' => $admin->id,
        ]);

        $bPurchase = app(BatteryPurchaseService::class)->create([
            'supplier_id' => $bSupp->id, 'date' => '2026-11-19', 'paid_amount' => '2000.00',
            'items' => [['product_id' => $bProd->id, 'quantity' => 2, 'cost_price' => '5000.00']],
        ], $admin->id);
        $bOrder = app(BatterySalesService::class)->createOrder([
            'customer_id' => $bCust->id, 'invoice_date' => '2026-11-19', 'paid_amount' => '3000.00',
            'items' => [['product_id' => $bProd->id, 'quantity' => 2, 'selling_price' => '6000.00']],
        ], $admin->id);
        $bSalesRet = app(BatteryReturnService::class)->createSalesReturn($bOrder->id, [
            ['order_detail_id' => $bOrder->details->first()->id, 'quantity' => 1],
        ], '2026-11-19', $admin->id)->first();
        $bPurchRet = app(BatteryReturnService::class)->createPurchaseReturn($bPurchase->id, [
            ['purchase_order_detail_id' => $bPurchase->orderDetails->first()->id, 'quantity' => 1],
        ], '2026-11-19', $admin->id)->first();

        $this->get(route('battery.customer.profile', $bCust->id))->assertOk()->assertSee('Smoke Battery Customer');
        $this->get(route('battery.supplier.profile', $bSupp->id))->assertOk()->assertSee('Smoke Battery Supplier');
        $this->get(route('battery.invoice.show', $bOrder->id))->assertOk()->assertSee($bOrder->order_no);
        $this->get(route('battery.invoice.due.print', $bOrder->id))->assertOk()->assertSee($bOrder->order_no);
        $this->get(route('battery.purchase.invoice.show', $bPurchase->id))->assertOk()->assertSee($bPurchase->purchase_id);
        $this->get(route('battery.return.show', $bSalesRet->id))->assertOk()->assertSee($bSalesRet->return_number);
        $this->get(route('battery.purchase-return.show', $bPurchRet->id))->assertOk()->assertSee($bPurchRet->return_number);
    }
}
