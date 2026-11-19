<?php

use App\Http\Controllers\Battery\BatteryPrintController;
use App\Livewire\Battery\Catalog\BatteryBarcodePrint;
use App\Livewire\Battery\Catalog\BatteryBrandList;
use App\Livewire\Battery\Catalog\BatteryCategoryList;
use App\Livewire\Battery\Catalog\BatteryProductList;
use App\Livewire\Battery\Catalog\BatteryStockOverview;
use App\Livewire\Battery\Catalog\BatterySubCategoryList;
use App\Livewire\Battery\Catalog\BatteryUnitList;
use App\Livewire\Battery\Customer\BatteryCustomerDueCollectionList;
use App\Livewire\Battery\Customer\BatteryCustomerDueList;
use App\Livewire\Battery\Customer\BatteryCustomerList;
use App\Livewire\Battery\Customer\BatteryCustomerProfile;
use App\Livewire\Battery\Dashboard\BatteryDashboard;
use App\Livewire\Battery\Expense\BatteryExpenseList;
use App\Livewire\Battery\Expense\BatteryExpenseTypeList;
use App\Livewire\Battery\Finance\BatteryOpeningBalanceList;
use App\Livewire\Battery\Invoice\BatteryInvoiceList;
use App\Livewire\Battery\Pos\BatteryPosTerminal;
use App\Livewire\Battery\Purchase\BatteryPurchaseList;
use App\Livewire\Battery\Purchase\BatteryPurchasePaymentList;
use App\Livewire\Battery\Report\BatteryDailyLedgerReport;
use App\Livewire\Battery\Report\BatteryDailyReceiptPaymentReport;
use App\Livewire\Battery\Report\BatteryIncomeExpenseReport;
use App\Livewire\Battery\Report\BatteryPersonalTransactionReport;
use App\Livewire\Battery\Report\BatterySalesReport;
use App\Livewire\Battery\Report\BatteryStockOutReport;
use App\Livewire\Battery\Returns\BatteryReturnManagement;
use App\Livewire\Battery\Supplier\BatterySupplierDueCollectionList;
use App\Livewire\Battery\Supplier\BatterySupplierDueList;
use App\Livewire\Battery\Supplier\BatterySupplierList;
use App\Livewire\Battery\Supplier\BatterySupplierProfile;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('battery')->name('battery.')->group(function () {
    Route::get('/dashboard', BatteryDashboard::class)
        ->middleware('perm:dashboard')
        ->name('dashboard');

    Route::get('/pos', BatteryPosTerminal::class)
        ->middleware('perm:pos_terminal')
        ->name('pos');

    Route::middleware('perm:invoice_list')->group(function () {
        Route::get('/invoices', BatteryInvoiceList::class)->name('invoices');
        Route::get('/invoice/{id}', [BatteryPrintController::class, 'invoice'])->name('invoice.show');
        Route::get('/due-invoice-print/{id}', [BatteryPrintController::class, 'invoice'])->name('invoice.due.print');
    });

    Route::middleware('perm:product')->group(function () {
        Route::get('/products', BatteryProductList::class)->name('products');
        Route::get('/stock-overview', BatteryStockOverview::class)->name('stock-overview');
        Route::get('/brands', BatteryBrandList::class)->name('brands');
        Route::get('/categories', BatteryCategoryList::class)->name('categories');
        Route::get('/sub-categories', BatterySubCategoryList::class)->name('sub-categories');
        Route::get('/units', BatteryUnitList::class)->name('units');
        Route::get('/barcode-generate', BatteryBarcodePrint::class)->name('barcode.generate');
    });

    Route::middleware('perm:purchase')->group(function () {
        Route::get('/suppliers', BatterySupplierList::class)->name('suppliers');
        Route::get('/supplier/profile/{id}', BatterySupplierProfile::class)->name('supplier.profile');
        Route::get('/purchases', BatteryPurchaseList::class)->name('purchases');
        Route::get('/purchase-invoice/{id}', [BatteryPrintController::class, 'purchase'])->name('purchase.invoice.show');
        Route::get('/purchase-payments', BatteryPurchasePaymentList::class)->name('purchase.payments');
        Route::get('/supplier-due', BatterySupplierDueList::class)->name('supplier.due');
        Route::get('/supplier-due-collection', BatterySupplierDueCollectionList::class)->name('supplier.due.collection');
    });

    Route::middleware('perm:customer')->group(function () {
        Route::get('/customers', BatteryCustomerList::class)->name('customers');
        Route::get('/customer/profile/{id}', BatteryCustomerProfile::class)->name('customer.profile');
        Route::get('/customer-due', BatteryCustomerDueList::class)->name('customer.due');
        Route::get('/customer-due-collection', BatteryCustomerDueCollectionList::class)->name('customer.due.collection');
    });

    Route::middleware('perm:expense')->group(function () {
        Route::get('/expenses', BatteryExpenseList::class)->name('expenses');
        Route::get('/expense-types', BatteryExpenseTypeList::class)->name('expense.types');
        Route::get('/opening-balance', BatteryOpeningBalanceList::class)->name('opening.balance');
    });

    Route::middleware('perm:return')->group(function () {
        Route::get('/sales-returns', BatteryReturnManagement::class)->name('sales.returns');
        Route::get('/purchase-returns', BatteryReturnManagement::class)->name('purchase.returns');
        Route::get('/return/{id}', [BatteryPrintController::class, 'salesReturn'])->name('return.show');
        Route::get('/purchase-return/{id}', [BatteryPrintController::class, 'purchaseReturn'])->name('purchase-return.show');
    });

    Route::middleware('perm:report')->group(function () {
        Route::get('/reports/daily-ledger', BatteryDailyLedgerReport::class)->name('reports.daily-ledger');
        Route::get('/reports/sales', BatterySalesReport::class)->name('reports.sales');
        Route::get('/reports/income-expense', BatteryIncomeExpenseReport::class)->name('reports.income-expense');
        Route::get('/reports/daily-receipt-payment', BatteryDailyReceiptPaymentReport::class)->name('reports.daily-receipt-payment');
        Route::get('/reports/personal-transaction', BatteryPersonalTransactionReport::class)->name('reports.personal-transaction');
        Route::get('/reports/stock-out', BatteryStockOutReport::class)->name('reports.stock-out');
    });
});
