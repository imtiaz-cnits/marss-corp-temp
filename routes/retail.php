<?php

use App\Http\Controllers\Retail\RetailPrintController;
use App\Livewire\Retail\Catalog\RetailBarcodePrint;
use App\Livewire\Retail\Catalog\RetailBrandList;
use App\Livewire\Retail\Catalog\RetailCategoryList;
use App\Livewire\Retail\Catalog\RetailProductList;
use App\Livewire\Retail\Catalog\RetailStockOverview;
use App\Livewire\Retail\Catalog\RetailSubCategoryList;
use App\Livewire\Retail\Customer\RetailCustomerDueCollectionList;
use App\Livewire\Retail\Customer\RetailCustomerDueList;
use App\Livewire\Retail\Customer\RetailCustomerList;
use App\Livewire\Retail\Customer\RetailCustomerProfile;
use App\Livewire\Retail\Dashboard\RetailDashboard;
use App\Livewire\Retail\Expense\RetailExpenseList;
use App\Livewire\Retail\Expense\RetailExpenseTypeList;
use App\Livewire\Retail\Expense\RetailStaffProfile;
use App\Livewire\Retail\Finance\RetailOpeningBalanceList;
use App\Livewire\Retail\Investment\RetailInvestList;
use App\Livewire\Retail\Investment\RetailInvestorInfoList;
use App\Livewire\Retail\Invoice\RetailInvoiceList;
use App\Livewire\Retail\Pos\RetailPosTerminal;
use App\Livewire\Retail\Purchase\RetailPurchaseList;
use App\Livewire\Retail\Report\RetailBestSellingReport;
use App\Livewire\Retail\Report\RetailCustomerInvoiceReport;
use App\Livewire\Retail\Report\RetailDailyLedgerReport;
use App\Livewire\Retail\Report\RetailDailyReceiptPaymentReport;
use App\Livewire\Retail\Report\RetailDueInvoiceReport;
use App\Livewire\Retail\Report\RetailIncomeExpenseReport;
use App\Livewire\Retail\Report\RetailPersonalTransactionReport;
use App\Livewire\Retail\Report\RetailSalesReport;
use App\Livewire\Retail\Report\RetailStockOutReport;
use App\Livewire\Retail\Returns\RetailReturnManagement;
use App\Livewire\Retail\Supplier\RetailSupplierDueCollectionList;
use App\Livewire\Retail\Supplier\RetailSupplierDueList;
use App\Livewire\Retail\Supplier\RetailSupplierList;
use App\Livewire\Retail\Supplier\RetailSupplierProfile;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/admin-dashboard', RetailDashboard::class)
        ->middleware('perm:dashboard')
        ->name('retail.dashboard');

    // POS & Invoices
    Route::get('/admin-dashboard-pos', RetailPosTerminal::class)
        ->middleware('perm:pos_terminal')
        ->name('retail.pos');

    Route::middleware('perm:invoice_list')->group(function () {
        Route::get('/admin-dashboard-invoice', RetailInvoiceList::class)->name('retail.invoices');
        Route::get('/invoice/{id}', [RetailPrintController::class, 'invoice'])->name('retail.invoice.show');
        Route::get('/due-invoice-print/{id}', [RetailPrintController::class, 'invoice'])->name('retail.invoice.due-print');
        Route::get('/invoice-print', [RetailPrintController::class, 'latestReceipt'])->name('retail.invoice.receipt');
    });

    // Product Catalog
    Route::middleware('perm:product')->group(function () {
        Route::get('/admin-dashboard-product', RetailProductList::class)->name('retail.products');
        Route::get('/admin-dashboard-brand', RetailBrandList::class)->name('retail.brands');
        Route::get('/admin-dashboard-category', RetailCategoryList::class)->name('retail.categories');
        Route::get('/admin-dashboard-sub-category', RetailSubCategoryList::class)->name('retail.sub-categories');
        Route::get('/admin-dashboard-stock-overview', RetailStockOverview::class)->name('retail.stock-overview');
        Route::get('/admin-dashboard-barcode-genarate', RetailBarcodePrint::class)->name('retail.barcode');
        Route::get('/admin-dashboard-product-barcode', RetailBarcodePrint::class)->name('retail.product-barcode');
    });

    // Suppliers & Purchases (preserving legacy URL casing /admin-dashboard-Purchase)
    Route::middleware('perm:purchase')->group(function () {
        Route::get('/admin-dashboard-supplier', RetailSupplierList::class)->name('retail.suppliers');
        Route::get('/supplier/profile/{id}', RetailSupplierProfile::class)->name('retail.supplier.profile');
        Route::get('/supplier-due-page', RetailSupplierDueList::class)->name('retail.supplier.due');
        Route::get('/supplier-due-collection-page', RetailSupplierDueCollectionList::class)->name('retail.supplier.due-collection');
        Route::get('/admin-dashboard-Purchase', RetailPurchaseList::class)->name('retail.purchases');
        Route::get('/purchase-invoice/{id}', [RetailPrintController::class, 'purchase'])->name('retail.purchase.show');
    });

    // Customers
    Route::middleware('perm:customer')->group(function () {
        Route::get('/admin-dashboard-customer', RetailCustomerList::class)->name('retail.customers');
        Route::get('/customer/profile/{id}', RetailCustomerProfile::class)->name('retail.customer.profile');
        Route::get('/admin-dashboard-customer-due-list', RetailCustomerDueList::class)->name('retail.customer.due');
        Route::get('/customer-due-collection-page', RetailCustomerDueCollectionList::class)->name('retail.customer.due-collection');
    });

    // Expenses & Opening Balance (preserving legacy URL spelling expence)
    Route::middleware('perm:expense')->group(function () {
        Route::get('/admin-dashboard-expence-list', RetailExpenseList::class)->name('retail.expenses');
        Route::get('/admin-dashboard-expence-type', RetailExpenseTypeList::class)->name('retail.expense-types');
        Route::get('/admin-dashboard-staff-profile', RetailStaffProfile::class)->name('retail.staff-profile');
        Route::get('/admin-dashboard-opening-balance', RetailOpeningBalanceList::class)->name('retail.opening-balance');
    });

    // Returns
    Route::middleware('perm:return')->group(function () {
        Route::get('/admin-dashboard-return-list', RetailReturnManagement::class)->name('retail.returns');
        Route::get('/return/{id}', [RetailPrintController::class, 'salesReturn'])->name('retail.return.show');
        Route::get('/purchase-return/{id}', [RetailPrintController::class, 'purchaseReturn'])->name('retail.purchase-return.show');
    });

    // Reports & Investments
    Route::middleware('perm:report')->group(function () {
        Route::get('/admin-dashboard-daily-ledger-report', RetailDailyLedgerReport::class)->name('retail.reports.daily-ledger');
        Route::get('/admin-dashboard-sales-report', RetailSalesReport::class)->name('retail.reports.sales');
        Route::get('/admin-dashboard-income-expense-report', RetailIncomeExpenseReport::class)->name('retail.reports.income-expense');
        Route::get('/admin-dashboard-daily-receipt-payment-report', RetailDailyReceiptPaymentReport::class)->name('retail.reports.daily-receipt-payment');
        Route::get('/admin-dashboard-personal-transaction-report', RetailPersonalTransactionReport::class)->name('retail.reports.personal-transaction');
        Route::get('/admin-dashboard-stock-out', RetailStockOutReport::class)->name('retail.reports.stock-out');
        Route::get('/admin-dashboard-due-invoice', RetailDueInvoiceReport::class)->name('retail.reports.due-invoice');
        Route::get('/admin-dashboard-customer-invoice-report', RetailCustomerInvoiceReport::class)->name('retail.reports.customer-invoice');
        Route::get('/admin-best-selling-report', RetailBestSellingReport::class)->name('retail.reports.best-selling');
        Route::get('/admin-dashboard-investor-info', RetailInvestorInfoList::class)->name('retail.investors');
        Route::get('/admin-dashboard-invest-list', RetailInvestList::class)->name('retail.invests');
    });
});
