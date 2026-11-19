<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Battery\Expense\BatteryExpenseTypeList;
use App\Livewire\Battery\Pos\BatteryPosTerminal;
use App\Livewire\Retail\Catalog\RetailProductList;
use App\Livewire\Retail\Pos\RetailPosTerminal;
use App\Models\Battery\BatteryBrand;
use App\Models\Battery\BatteryCategory;
use App\Models\Battery\BatteryCustomer;
use App\Models\Battery\BatteryOrder;
use App\Models\Battery\BatteryProduct;
use App\Models\Retail\RetailBrand;
use App\Models\Retail\RetailCategory;
use App\Models\Retail\RetailCustomer;
use App\Models\Retail\RetailOrder;
use App\Models\Retail\RetailProduct;
use App\Models\Shared\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class LivewireEndToEndWorkflowsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_retail_pos_creates_order_and_redirects_to_retail_invoice_print(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->firstOrFail();
        $customer = RetailCustomer::firstOrFail();
        $brandId = RetailBrand::query()->value('id');
        $catId = RetailCategory::query()->value('id');
        $product = RetailProduct::create([
            'product_name' => 'LW Test Door Lock',
            'product_code' => ['LW-RET-9991'],
            'quantity' => '15',
            'cost_price' => '500.00',
            'sell_price' => '750.00',
            'door_side' => 'Right Handed',
            'brand_id' => $brandId,
            'category_id' => $catId,
            'status' => 'active',
            'user_id' => $admin->id,
        ]);

        $beforeBatteryOrders = BatteryOrder::count();

        $component = Livewire::actingAs($admin)
            ->test(RetailPosTerminal::class)
            ->set('customer_id', $customer->id)
            ->call('addToCart', $product->id)
            ->call('incrementQty', 0) // qty = 2 -> 1500
            ->set('paid_amount', '1000')
            ->call('completeSale');

        $latestOrder = RetailOrder::orderByDesc('id')->firstOrFail();
        $component->assertRedirect(route('retail.invoice.show', $latestOrder->id));

        $this->assertEquals('1500.00', number_format((float) $latestOrder->sub_total, 2, '.', ''));
        $this->assertEquals('500.00', number_format((float) $latestOrder->due_amount, 2, '.', ''));
        $this->assertEquals(13, (int) $product->fresh()->quantity);
        $this->assertEquals($beforeBatteryOrders, BatteryOrder::count());
    }

    public function test_battery_pos_creates_isolated_order_and_redirects_to_battery_invoice_print(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->firstOrFail();
        $customer = BatteryCustomer::create([
            'customer_id' => '#BCus99999',
            'customer_name' => 'LW Battery Buyer',
            'mobile' => '01700000001',
            'previous_due_amount' => '0.00',
            'user_id' => $admin->id,
        ]);
        $batBrand = BatteryBrand::first() ?? BatteryBrand::create(['name' => 'Test Bat Brand', 'user_id' => $admin->id]);
        $batCat = BatteryCategory::first() ?? BatteryCategory::create(['name' => 'Test Bat Cat', 'user_id' => $admin->id]);
        $product = BatteryProduct::create([
            'product_name' => 'LW 12V 100Ah Gel Battery',
            'product_code' => ['BAT-LW-01'],
            'quantity' => '10',
            'cost_price' => '8000.00',
            'sell_price' => '9500.00',
            'brand_id' => $batBrand->id,
            'category_id' => $batCat->id,
            'status' => 'active',
            'user_id' => $admin->id,
        ]);

        $beforeRetailOrders = RetailOrder::count();

        $component = Livewire::actingAs($admin)
            ->test(BatteryPosTerminal::class)
            ->set('customer_id', $customer->id)
            ->call('addToCart', $product->id)
            ->set('paid_amount', '9500')
            ->call('completeSale');

        $latestBatOrder = BatteryOrder::orderByDesc('id')->firstOrFail();
        // Verifies legacy bug fixed: redirects strictly to Battery invoice print, NOT Retail!
        $component->assertRedirect(route('battery.invoice.show', $latestBatOrder->id));

        $this->assertEquals(9, (int) $product->fresh()->quantity);
        $this->assertEquals($beforeRetailOrders, RetailOrder::count());
        $this->actingAs($admin)->get(route('battery.invoice.show', $latestBatOrder->id))->assertOk()->assertSee('LW Battery Buyer');
    }

    public function test_battery_expense_type_saves_without_nonexistent_status_column(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->firstOrFail();

        Livewire::actingAs($admin)
            ->test(BatteryExpenseTypeList::class)
            ->call('openCreate')
            ->set('type_name', 'Battery Transport Test')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('battery_expense_types', ['type_name' => 'Battery Transport Test']);
    }

    public function test_retail_product_list_prevents_duplicate_barcode(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->firstOrFail();
        $brandId = RetailBrand::query()->value('id');
        $catId = RetailCategory::query()->value('id');
        RetailProduct::create([
            'product_name' => 'Existing SKU',
            'product_code' => ['DUP-CODE-777'],
            'quantity' => '5',
            'cost_price' => '100',
            'sell_price' => '150',
            'brand_id' => $brandId,
            'category_id' => $catId,
            'status' => 'active',
            'user_id' => $admin->id,
        ]);

        Livewire::actingAs($admin)
            ->test(RetailProductList::class)
            ->call('openCreate')
            ->set('product_name', 'Duplicate SKU')
            ->set('product_code', 'DUP-CODE-777')
            ->set('quantity', '5')
            ->set('cost_price', '100')
            ->set('sell_price', '150')
            ->call('save')
            ->assertHasErrors(['product_code']);
    }
}
