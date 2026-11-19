# MARSS Corporation — Complete Route Inventory & Legacy Route Mapping (Finding F-03)

All original user-facing URLs are preserved verbatim (including legacy URL spellings such as `/admin-dashboard-Purchase`, `/admin-dashboard-expence-list`, `/admin-dashboard-barcode-genarate`, `/admin-dashboard-dsistrict`, `/naxus-pos-logout`) so existing bookmarks, links, and muscle memory continue working with zero breakage.

---

## Route Count Reconciliation Summary (Finding F-03)

- **Why the previous edition of `ROUTE_MAPPING.md` had 88 table rows for 89 application routes:**
  - In the previous version of Section 1 (row 4), two distinct registered Laravel routes — `POST /logout` (`name: logout`) and `GET|HEAD /naxus-pos-logout` (`name: logout.legacy`) — were combined into a single Markdown table row (`11 + 41 + 36 = 88` table rows).
  - Below, every single registered route has its own individual row (`12 + 41 + 36 = 89` application route rows), matching `php artisan route:list --except-vendor` (`89` routes) and `php artisan route:list` (`89` application routes + `12` framework/vendor routes = `101` total routes).

| Route Group | Source File | Exact Route Count | Status |
|---|---|---:|---|
| **1. Shared & Auth Routes** | `routes/web.php` | **`12`** | **VERIFIED** |
| **2. Retail Module Routes** | `routes/retail.php` | **`41`** | **VERIFIED** |
| **3. Battery Module Routes** | `routes/battery.php` | **`36`** | **VERIFIED** |
| **Subtotal — Application Routes (`--except-vendor`)** | `routes/{web,retail,battery}.php` | **`89`** | **VERIFIED** |
| **4. Framework & Livewire Vendor Routes** | Laravel 13 / Livewire 4 | **`12`** | **VERIFIED** |
| **Grand Total — All Registered Routes (`route:list`)** | Application + Framework | **`101`** | **VERIFIED** |

---

## 1. Shared & Auth Routes (`routes/web.php` — 12 Routes)

| # | HTTP Method | Legacy URL | Target URI | Target Route Name | Handler | Middleware | Status |
|---:|---|---|---|---|---|---|---|
| 1 | `GET|HEAD` | `/` | `/` | `home` | `Closure` | `web` | **VERIFIED** |
| 2 | `GET|HEAD` | `/admin-dashboard-dsistrict` | `/admin-dashboard-dsistrict` | `settings.districts` | `App\Livewire\Shared\Location\LocationManagerPage` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:customer` | **VERIFIED** |
| 3 | `GET|HEAD` | `/admin-dashboard-location` | `/admin-dashboard-location` | `settings.locations` | `App\Livewire\Shared\Location\LocationManagerPage` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:customer` | **VERIFIED** |
| 4 | `GET|HEAD` | `/admin-dashboard-thana` | `/admin-dashboard-thana` | `settings.thanas` | `App\Livewire\Shared\Location\LocationManagerPage` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:customer` | **VERIFIED** |
| 5 | `GET|HEAD` | `/admin-dashboard-upazila` | `/admin-dashboard-upazila` | `settings.upazilas` | `App\Livewire\Shared\Location\LocationManagerPage` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:customer` | **VERIFIED** |
| 6 | `GET|HEAD` | `/admin-dashboard-user-activity-log` | `/admin-dashboard-user-activity-log` | `settings.activity-log` | `App\Livewire\Shared\Settings\UserActivityLogPage` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\EnsureAdmin` | **VERIFIED** |
| 7 | `GET|HEAD` | `/admin-dashboard-user-profile` | `/admin-dashboard-user-profile` | `settings.profile` | `App\Livewire\Shared\Settings\UserProfilePage` | `web`, `Illuminate\Auth\Middleware\Authenticate` | **VERIFIED** |
| 8 | `GET|HEAD` | `/admin-dashboard-user-role` | `/admin-dashboard-user-role` | `settings.users` | `App\Livewire\Shared\Settings\UserRoleManagementPage` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:user` | **VERIFIED** |
| 9 | `GET|HEAD` | `/admin-login-page` | `/admin-login-page` | `login` | `App\Livewire\Shared\Auth\LoginPage` | `web`, `Illuminate\Auth\Middleware\RedirectIfAuthenticated` | **VERIFIED** |
| 10 | `POST` | `/logout` | `/logout` | `logout` | `App\Http\Controllers\AuthController@logout` | `web`, `Illuminate\Auth\Middleware\Authenticate` | **VERIFIED** |
| 11 | `GET|HEAD` | `/naxus-pos-logout` | `/naxus-pos-logout` | `logout.legacy` | `App\Http\Controllers\AuthController@logout` | `web`, `Illuminate\Auth\Middleware\Authenticate` | **VERIFIED** |
| 12 | `GET|HEAD|POST|PUT|PATCH|DELETE|OPTIONS|QUERY` | `/nexus-login-page` | `/nexus-login-page` | *(unnamed redirect)* | `Illuminate\Routing\RedirectController` | `web`, `Illuminate\Auth\Middleware\RedirectIfAuthenticated` | **VERIFIED** |

---

## 2. Retail Module Routes (`routes/retail.php` — 41 Routes)

| # | HTTP Method | Legacy URL | Target URI | Target Route Name | Handler | Middleware | Status |
|---:|---|---|---|---|---|---|---|
| 1 | `GET|HEAD` | `/admin-best-selling-report` | `/admin-best-selling-report` | `retail.reports.best-selling` | `App\Livewire\Retail\Report\RetailBestSellingReport` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:report` | **VERIFIED** |
| 2 | `GET|HEAD` | `/admin-dashboard` | `/admin-dashboard` | `retail.dashboard` | `App\Livewire\Retail\Dashboard\RetailDashboard` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:dashboard` | **VERIFIED** |
| 3 | `GET|HEAD` | `/admin-dashboard-Purchase` | `/admin-dashboard-Purchase` | `retail.purchases` | `App\Livewire\Retail\Purchase\RetailPurchaseList` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:purchase` | **VERIFIED** |
| 4 | `GET|HEAD` | `/admin-dashboard-barcode-genarate` | `/admin-dashboard-barcode-genarate` | `retail.barcode` | `App\Livewire\Retail\Catalog\RetailBarcodePrint` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:product` | **VERIFIED** |
| 5 | `GET|HEAD` | `/admin-dashboard-brand` | `/admin-dashboard-brand` | `retail.brands` | `App\Livewire\Retail\Catalog\RetailBrandList` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:product` | **VERIFIED** |
| 6 | `GET|HEAD` | `/admin-dashboard-category` | `/admin-dashboard-category` | `retail.categories` | `App\Livewire\Retail\Catalog\RetailCategoryList` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:product` | **VERIFIED** |
| 7 | `GET|HEAD` | `/admin-dashboard-customer` | `/admin-dashboard-customer` | `retail.customers` | `App\Livewire\Retail\Customer\RetailCustomerList` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:customer` | **VERIFIED** |
| 8 | `GET|HEAD` | `/admin-dashboard-customer-due-list` | `/admin-dashboard-customer-due-list` | `retail.customer.due` | `App\Livewire\Retail\Customer\RetailCustomerDueList` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:customer` | **VERIFIED** |
| 9 | `GET|HEAD` | `/admin-dashboard-customer-invoice-report` | `/admin-dashboard-customer-invoice-report` | `retail.reports.customer-invoice` | `App\Livewire\Retail\Report\RetailCustomerInvoiceReport` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:report` | **VERIFIED** |
| 10 | `GET|HEAD` | `/admin-dashboard-daily-ledger-report` | `/admin-dashboard-daily-ledger-report` | `retail.reports.daily-ledger` | `App\Livewire\Retail\Report\RetailDailyLedgerReport` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:report` | **VERIFIED** |
| 11 | `GET|HEAD` | `/admin-dashboard-daily-receipt-payment-report` | `/admin-dashboard-daily-receipt-payment-report` | `retail.reports.daily-receipt-payment` | `App\Livewire\Retail\Report\RetailDailyReceiptPaymentReport` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:report` | **VERIFIED** |
| 12 | `GET|HEAD` | `/admin-dashboard-due-invoice` | `/admin-dashboard-due-invoice` | `retail.reports.due-invoice` | `App\Livewire\Retail\Report\RetailDueInvoiceReport` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:report` | **VERIFIED** |
| 13 | `GET|HEAD` | `/admin-dashboard-expence-list` | `/admin-dashboard-expence-list` | `retail.expenses` | `App\Livewire\Retail\Expense\RetailExpenseList` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:expense` | **VERIFIED** |
| 14 | `GET|HEAD` | `/admin-dashboard-expence-type` | `/admin-dashboard-expence-type` | `retail.expense-types` | `App\Livewire\Retail\Expense\RetailExpenseTypeList` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:expense` | **VERIFIED** |
| 15 | `GET|HEAD` | `/admin-dashboard-income-expense-report` | `/admin-dashboard-income-expense-report` | `retail.reports.income-expense` | `App\Livewire\Retail\Report\RetailIncomeExpenseReport` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:report` | **VERIFIED** |
| 16 | `GET|HEAD` | `/admin-dashboard-invest-list` | `/admin-dashboard-invest-list` | `retail.invests` | `App\Livewire\Retail\Investment\RetailInvestList` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:report` | **VERIFIED** |
| 17 | `GET|HEAD` | `/admin-dashboard-investor-info` | `/admin-dashboard-investor-info` | `retail.investors` | `App\Livewire\Retail\Investment\RetailInvestorInfoList` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:report` | **VERIFIED** |
| 18 | `GET|HEAD` | `/admin-dashboard-invoice` | `/admin-dashboard-invoice` | `retail.invoices` | `App\Livewire\Retail\Invoice\RetailInvoiceList` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:invoice_list` | **VERIFIED** |
| 19 | `GET|HEAD` | `/admin-dashboard-opening-balance` | `/admin-dashboard-opening-balance` | `retail.opening-balance` | `App\Livewire\Retail\Finance\RetailOpeningBalanceList` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:expense` | **VERIFIED** |
| 20 | `GET|HEAD` | `/admin-dashboard-personal-transaction-report` | `/admin-dashboard-personal-transaction-report` | `retail.reports.personal-transaction` | `App\Livewire\Retail\Report\RetailPersonalTransactionReport` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:report` | **VERIFIED** |
| 21 | `GET|HEAD` | `/admin-dashboard-pos` | `/admin-dashboard-pos` | `retail.pos` | `App\Livewire\Retail\Pos\RetailPosTerminal` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:pos_terminal` | **VERIFIED** |
| 22 | `GET|HEAD` | `/admin-dashboard-product` | `/admin-dashboard-product` | `retail.products` | `App\Livewire\Retail\Catalog\RetailProductList` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:product` | **VERIFIED** |
| 23 | `GET|HEAD` | `/admin-dashboard-product-barcode` | `/admin-dashboard-product-barcode` | `retail.product-barcode` | `App\Livewire\Retail\Catalog\RetailBarcodePrint` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:product` | **VERIFIED** |
| 24 | `GET|HEAD` | `/admin-dashboard-return-list` | `/admin-dashboard-return-list` | `retail.returns` | `App\Livewire\Retail\Returns\RetailReturnManagement` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:return` | **VERIFIED** |
| 25 | `GET|HEAD` | `/admin-dashboard-sales-report` | `/admin-dashboard-sales-report` | `retail.reports.sales` | `App\Livewire\Retail\Report\RetailSalesReport` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:report` | **VERIFIED** |
| 26 | `GET|HEAD` | `/admin-dashboard-staff-profile` | `/admin-dashboard-staff-profile` | `retail.staff-profile` | `App\Livewire\Retail\Expense\RetailStaffProfile` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:expense` | **VERIFIED** |
| 27 | `GET|HEAD` | `/admin-dashboard-stock-out` | `/admin-dashboard-stock-out` | `retail.reports.stock-out` | `App\Livewire\Retail\Report\RetailStockOutReport` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:report` | **VERIFIED** |
| 28 | `GET|HEAD` | `/admin-dashboard-stock-overview` | `/admin-dashboard-stock-overview` | `retail.stock-overview` | `App\Livewire\Retail\Catalog\RetailStockOverview` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:product` | **VERIFIED** |
| 29 | `GET|HEAD` | `/admin-dashboard-sub-category` | `/admin-dashboard-sub-category` | `retail.sub-categories` | `App\Livewire\Retail\Catalog\RetailSubCategoryList` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:product` | **VERIFIED** |
| 30 | `GET|HEAD` | `/admin-dashboard-supplier` | `/admin-dashboard-supplier` | `retail.suppliers` | `App\Livewire\Retail\Supplier\RetailSupplierList` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:purchase` | **VERIFIED** |
| 31 | `GET|HEAD` | `/customer-due-collection-page` | `/customer-due-collection-page` | `retail.customer.due-collection` | `App\Livewire\Retail\Customer\RetailCustomerDueCollectionList` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:customer` | **VERIFIED** |
| 32 | `GET|HEAD` | `/customer/profile/{id}` | `/customer/profile/{id}` | `retail.customer.profile` | `App\Livewire\Retail\Customer\RetailCustomerProfile` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:customer` | **VERIFIED** |
| 33 | `GET|HEAD` | `/due-invoice-print/{id}` | `/due-invoice-print/{id}` | `retail.invoice.due-print` | `App\Http\Controllers\Retail\RetailPrintController@invoice` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:invoice_list` | **VERIFIED** |
| 34 | `GET|HEAD` | `/invoice-print` | `/invoice-print` | `retail.invoice.receipt` | `App\Http\Controllers\Retail\RetailPrintController@latestReceipt` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:invoice_list` | **VERIFIED** |
| 35 | `GET|HEAD` | `/invoice/{id}` | `/invoice/{id}` | `retail.invoice.show` | `App\Http\Controllers\Retail\RetailPrintController@invoice` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:invoice_list` | **VERIFIED** |
| 36 | `GET|HEAD` | `/purchase-invoice/{id}` | `/purchase-invoice/{id}` | `retail.purchase.show` | `App\Http\Controllers\Retail\RetailPrintController@purchase` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:purchase` | **VERIFIED** |
| 37 | `GET|HEAD` | `/purchase-return/{id}` | `/purchase-return/{id}` | `retail.purchase-return.show` | `App\Http\Controllers\Retail\RetailPrintController@purchaseReturn` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:return` | **VERIFIED** |
| 38 | `GET|HEAD` | `/return/{id}` | `/return/{id}` | `retail.return.show` | `App\Http\Controllers\Retail\RetailPrintController@salesReturn` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:return` | **VERIFIED** |
| 39 | `GET|HEAD` | `/supplier-due-collection-page` | `/supplier-due-collection-page` | `retail.supplier.due-collection` | `App\Livewire\Retail\Supplier\RetailSupplierDueCollectionList` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:purchase` | **VERIFIED** |
| 40 | `GET|HEAD` | `/supplier-due-page` | `/supplier-due-page` | `retail.supplier.due` | `App\Livewire\Retail\Supplier\RetailSupplierDueList` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:purchase` | **VERIFIED** |
| 41 | `GET|HEAD` | `/supplier/profile/{id}` | `/supplier/profile/{id}` | `retail.supplier.profile` | `App\Livewire\Retail\Supplier\RetailSupplierProfile` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:purchase` | **VERIFIED** |

---

## 3. Battery Module Routes (`routes/battery.php` — 36 Routes)

| # | HTTP Method | Legacy URL | Target URI | Target Route Name | Handler | Middleware | Status |
|---:|---|---|---|---|---|---|---|
| 1 | `GET|HEAD` | `/battery/barcode-generate` | `/battery/barcode-generate` | `battery.barcode.generate` | `App\Livewire\Battery\Catalog\BatteryBarcodePrint` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:product` | **VERIFIED** |
| 2 | `GET|HEAD` | `/battery/brands` | `/battery/brands` | `battery.brands` | `App\Livewire\Battery\Catalog\BatteryBrandList` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:product` | **VERIFIED** |
| 3 | `GET|HEAD` | `/battery/categories` | `/battery/categories` | `battery.categories` | `App\Livewire\Battery\Catalog\BatteryCategoryList` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:product` | **VERIFIED** |
| 4 | `GET|HEAD` | `/battery/customer-due` | `/battery/customer-due` | `battery.customer.due` | `App\Livewire\Battery\Customer\BatteryCustomerDueList` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:customer` | **VERIFIED** |
| 5 | `GET|HEAD` | `/battery/customer-due-collection` | `/battery/customer-due-collection` | `battery.customer.due.collection` | `App\Livewire\Battery\Customer\BatteryCustomerDueCollectionList` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:customer` | **VERIFIED** |
| 6 | `GET|HEAD` | `/battery/customer/profile/{id}` | `/battery/customer/profile/{id}` | `battery.customer.profile` | `App\Livewire\Battery\Customer\BatteryCustomerProfile` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:customer` | **VERIFIED** |
| 7 | `GET|HEAD` | `/battery/customers` | `/battery/customers` | `battery.customers` | `App\Livewire\Battery\Customer\BatteryCustomerList` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:customer` | **VERIFIED** |
| 8 | `GET|HEAD` | `/battery/dashboard` | `/battery/dashboard` | `battery.dashboard` | `App\Livewire\Battery\Dashboard\BatteryDashboard` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:dashboard` | **VERIFIED** |
| 9 | `GET|HEAD` | `/battery/due-invoice-print/{id}` | `/battery/due-invoice-print/{id}` | `battery.invoice.due.print` | `App\Http\Controllers\Battery\BatteryPrintController@invoice` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:invoice_list` | **VERIFIED** |
| 10 | `GET|HEAD` | `/battery/expense-types` | `/battery/expense-types` | `battery.expense.types` | `App\Livewire\Battery\Expense\BatteryExpenseTypeList` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:expense` | **VERIFIED** |
| 11 | `GET|HEAD` | `/battery/expenses` | `/battery/expenses` | `battery.expenses` | `App\Livewire\Battery\Expense\BatteryExpenseList` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:expense` | **VERIFIED** |
| 12 | `GET|HEAD` | `/battery/invoice/{id}` | `/battery/invoice/{id}` | `battery.invoice.show` | `App\Http\Controllers\Battery\BatteryPrintController@invoice` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:invoice_list` | **VERIFIED** |
| 13 | `GET|HEAD` | `/battery/invoices` | `/battery/invoices` | `battery.invoices` | `App\Livewire\Battery\Invoice\BatteryInvoiceList` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:invoice_list` | **VERIFIED** |
| 14 | `GET|HEAD` | `/battery/opening-balance` | `/battery/opening-balance` | `battery.opening.balance` | `App\Livewire\Battery\Finance\BatteryOpeningBalanceList` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:expense` | **VERIFIED** |
| 15 | `GET|HEAD` | `/battery/pos` | `/battery/pos` | `battery.pos` | `App\Livewire\Battery\Pos\BatteryPosTerminal` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:pos_terminal` | **VERIFIED** |
| 16 | `GET|HEAD` | `/battery/products` | `/battery/products` | `battery.products` | `App\Livewire\Battery\Catalog\BatteryProductList` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:product` | **VERIFIED** |
| 17 | `GET|HEAD` | `/battery/purchase-invoice/{id}` | `/battery/purchase-invoice/{id}` | `battery.purchase.invoice.show` | `App\Http\Controllers\Battery\BatteryPrintController@purchase` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:purchase` | **VERIFIED** |
| 18 | `GET|HEAD` | `/battery/purchase-payments` | `/battery/purchase-payments` | `battery.purchase.payments` | `App\Livewire\Battery\Purchase\BatteryPurchasePaymentList` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:purchase` | **VERIFIED** |
| 19 | `GET|HEAD` | `/battery/purchase-return/{id}` | `/battery/purchase-return/{id}` | `battery.purchase-return.show` | `App\Http\Controllers\Battery\BatteryPrintController@purchaseReturn` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:return` | **VERIFIED** |
| 20 | `GET|HEAD` | `/battery/purchase-returns` | `/battery/purchase-returns` | `battery.purchase.returns` | `App\Livewire\Battery\Returns\BatteryReturnManagement` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:return` | **VERIFIED** |
| 21 | `GET|HEAD` | `/battery/purchases` | `/battery/purchases` | `battery.purchases` | `App\Livewire\Battery\Purchase\BatteryPurchaseList` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:purchase` | **VERIFIED** |
| 22 | `GET|HEAD` | `/battery/reports/daily-ledger` | `/battery/reports/daily-ledger` | `battery.reports.daily-ledger` | `App\Livewire\Battery\Report\BatteryDailyLedgerReport` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:report` | **VERIFIED** |
| 23 | `GET|HEAD` | `/battery/reports/daily-receipt-payment` | `/battery/reports/daily-receipt-payment` | `battery.reports.daily-receipt-payment` | `App\Livewire\Battery\Report\BatteryDailyReceiptPaymentReport` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:report` | **VERIFIED** |
| 24 | `GET|HEAD` | `/battery/reports/income-expense` | `/battery/reports/income-expense` | `battery.reports.income-expense` | `App\Livewire\Battery\Report\BatteryIncomeExpenseReport` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:report` | **VERIFIED** |
| 25 | `GET|HEAD` | `/battery/reports/personal-transaction` | `/battery/reports/personal-transaction` | `battery.reports.personal-transaction` | `App\Livewire\Battery\Report\BatteryPersonalTransactionReport` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:report` | **VERIFIED** |
| 26 | `GET|HEAD` | `/battery/reports/sales` | `/battery/reports/sales` | `battery.reports.sales` | `App\Livewire\Battery\Report\BatterySalesReport` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:report` | **VERIFIED** |
| 27 | `GET|HEAD` | `/battery/reports/stock-out` | `/battery/reports/stock-out` | `battery.reports.stock-out` | `App\Livewire\Battery\Report\BatteryStockOutReport` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:report` | **VERIFIED** |
| 28 | `GET|HEAD` | `/battery/return/{id}` | `/battery/return/{id}` | `battery.return.show` | `App\Http\Controllers\Battery\BatteryPrintController@salesReturn` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:return` | **VERIFIED** |
| 29 | `GET|HEAD` | `/battery/sales-returns` | `/battery/sales-returns` | `battery.sales.returns` | `App\Livewire\Battery\Returns\BatteryReturnManagement` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:return` | **VERIFIED** |
| 30 | `GET|HEAD` | `/battery/stock-overview` | `/battery/stock-overview` | `battery.stock-overview` | `App\Livewire\Battery\Catalog\BatteryStockOverview` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:product` | **VERIFIED** |
| 31 | `GET|HEAD` | `/battery/sub-categories` | `/battery/sub-categories` | `battery.sub-categories` | `App\Livewire\Battery\Catalog\BatterySubCategoryList` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:product` | **VERIFIED** |
| 32 | `GET|HEAD` | `/battery/supplier-due` | `/battery/supplier-due` | `battery.supplier.due` | `App\Livewire\Battery\Supplier\BatterySupplierDueList` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:purchase` | **VERIFIED** |
| 33 | `GET|HEAD` | `/battery/supplier-due-collection` | `/battery/supplier-due-collection` | `battery.supplier.due.collection` | `App\Livewire\Battery\Supplier\BatterySupplierDueCollectionList` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:purchase` | **VERIFIED** |
| 34 | `GET|HEAD` | `/battery/supplier/profile/{id}` | `/battery/supplier/profile/{id}` | `battery.supplier.profile` | `App\Livewire\Battery\Supplier\BatterySupplierProfile` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:purchase` | **VERIFIED** |
| 35 | `GET|HEAD` | `/battery/suppliers` | `/battery/suppliers` | `battery.suppliers` | `App\Livewire\Battery\Supplier\BatterySupplierList` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:purchase` | **VERIFIED** |
| 36 | `GET|HEAD` | `/battery/units` | `/battery/units` | `battery.units` | `App\Livewire\Battery\Catalog\BatteryUnitList` | `web`, `Illuminate\Auth\Middleware\Authenticate`, `App\Http\Middleware\CheckPermission:product` | **VERIFIED** |

---

## 4. Framework & Livewire Vendor Built-in Routes (12 Routes — Completing 101 Total Routes)

| # | HTTP Method | Target URI | Route Name | Handler | Middleware | Status |
|---:|---|---|---|---|---|---|
| 1 | `GET|HEAD` | `/livewire-eff09e76/css/{component}.css` | — | `Closure` | — | **VERIFIED** |
| 2 | `GET|HEAD` | `/livewire-eff09e76/css/{component}.global.css` | — | `Closure` | — | **VERIFIED** |
| 3 | `GET|HEAD` | `/livewire-eff09e76/js/{component}.js` | — | `Closure` | — | **VERIFIED** |
| 4 | `GET|HEAD` | `/livewire-eff09e76/livewire.csp.min.js.map` | — | `Livewire\Mechanisms\FrontendAssets\FrontendAssets@cspMaps` | — | **VERIFIED** |
| 5 | `GET|HEAD` | `/livewire-eff09e76/livewire.js` | — | `Livewire\Mechanisms\FrontendAssets\FrontendAssets@returnJavaScriptAsFile` | — | **VERIFIED** |
| 6 | `GET|HEAD` | `/livewire-eff09e76/livewire.min.js.map` | — | `Livewire\Mechanisms\FrontendAssets\FrontendAssets@maps` | — | **VERIFIED** |
| 7 | `GET|HEAD` | `/livewire-eff09e76/preview-file/{filename}` | `livewire.preview-file` | `Livewire\Features\SupportFileUploads\FilePreviewController@handle` | `web` | **VERIFIED** |
| 8 | `POST` | `/livewire-eff09e76/update` | `default-livewire.update` | `Livewire\Mechanisms\HandleRequests\HandleRequests@handleUpdate` | `web`, `Livewire\Mechanisms\HandleRequests\RequireLivewireHeaders` | **VERIFIED** |
| 9 | `POST` | `/livewire-eff09e76/upload-file` | `livewire.upload-file` | `Livewire\Features\SupportFileUploads\FileUploadController@handle` | `web`, `Illuminate\Routing\Middleware\ThrottleRequests:60,1` | **VERIFIED** |
| 10 | `GET|HEAD` | `/storage/{path}` | `storage.local` | `Closure` | — | **VERIFIED** |
| 11 | `PUT` | `/storage/{path}` | `storage.local.upload` | `Closure` | — | **VERIFIED** |
| 12 | `GET|HEAD` | `/up` | — | `Illuminate\Foundation\Http\HealthCheck` | — | **VERIFIED** |

---

## 5. Unmatched Legacy Routes Mapping (Legacy AJAX Endpoints & Broken Legacy Routes)

In the original jQuery/Axios application (`marsscorp-retailshop-mgm/routes/web.php` & `routes/battery.php`), each page required separate `GET`/`POST` JSON endpoints (`get-all-*`, `create-*`, `*-by-id`, `update-*`, `delete-*`). In the re-engineered Livewire 4 TALL Stack architecture, all 89 page/print URLs are preserved 1:1 while legacy JSON CRUD endpoints are handled directly inside authenticated, permission-gated Livewire component actions via `/livewire-32888bf2/update`:

| Legacy Route Category | Legacy URIs (from Reference `routes/web.php` & `routes/battery.php`) | Legacy Issue / Purpose | Target TALL Stack Disposition | Status |
|---|---|---|---|---|
| **Broken Legacy View Routes (3 routes)** | `GET /admin-dashboard-unit`, `GET /admin-dashboard-warehouse`, `GET /customer-page` | Referenced non-existent Blade views (`pages.backend.unit-page`, `pages.backend.warehouse-page`, `pages.backend.customer-page`) that threw HTTP 500 in the legacy app and were not linked in any menu. (Note: Battery Units `/battery/units` IS implemented and active.) | Intentionally omitted dead legacy routes; Retail unit selection is managed inline within `RetailProductList`. | **NOT APPLICABLE** |
| **Broken Legacy Controller Routes (3 routes)** | `POST /purchase-invoice/purchase-details-by-id`, `POST /return/return-details-by-id`, `POST /purchase-return/purchase-return-details-by-id` | Referenced non-existent controller methods in legacy `PurchaseController` / `ProductReturnController` / `PurchaseReturnController`. | Replaced by direct Livewire state & `RetailPrintController` (`GET /purchase-invoice/{id}`, `GET /return/{id}`, `GET /purchase-return/{id}`). | **FIXED & VERIFIED** |
| **Unauthenticated Legacy Auth & Admin Endpoints (12 routes)** | `GET /user-registration-page`, `POST /user-registration`, `POST /admin-login`, `GET /send-otp-page`, `POST /send-otp`, `GET /verify-otp-page`, `POST /verify-otp`, `GET /reset-password-page`, `POST /reset-password`, `GET /get-all-users`, `POST /create-user-admin`, `POST /update-user-role-status`, `POST /delete-user-admin` | Allowed unauthenticated public self-registration as `admin` and unauthenticated user listing/mutation. | Replaced by server-side authenticated Livewire actions in `LoginPage`, `UserProfilePage`, and `UserRoleManagementPage` (`perm:user`). | **FIXED & VERIFIED** |
| **Legacy Retail jQuery/Axios JSON CRUD Endpoints (102 routes)** | `/get-all-brand*`, `/brand-*`, `/get-all-category*`, `/category-*`, `/get-all-sub-category*`, `/sub-category-*`, `/get-all-unit*`, `/unit-*`, `/get-all-product*`, `/create-product`, `/update-product`, `/delete-product`, `/adjust-product-stock`, `/get-all-supplier*`, `/create-supplier`, `/update-supplier`, `/delete-supplier`, `/get-all-purchases*`, `/create-purchases`, `/update-purchase*`, `/split-purchase-invoice`, `/delete-purchases`, `/get-all-customer*`, `/create-customer`, `/update-customer`, `/delete-customer`, `/create-order`, `/get-all-order*`, `/update-invoice*`, `/delete-invoice`, `/customer-due-*`, `/supplier-due-*`, `/get-all-expence*`, `/create-expence*`, `/return-*`, `/purchase-return-*`, `/opening-balance/*`, `/get-all-investor*`, `/get-all-invest*`, `/admin-dashboard-*-report-list` | Separate jQuery/Axios JSON endpoints called after page load (many lacking authentication or RBAC middleware). | Encapsulated inside authenticated, permission-gated Livewire 4 component methods and domain services (`App\Livewire\Retail\*` & `App\Services\Retail\*`). | **FIXED & VERIFIED** |
| **Legacy Battery jQuery/Axios JSON CRUD Endpoints (84 routes)** | `/battery/get-all-*`, `/battery/create-*`, `/battery/*-by-id`, `/battery/update-*`, `/battery/delete-*`, `/battery/adjust-product-stock`, `/battery/split-purchase-invoice`, `/battery/customer-due-*`, `/battery/supplier-due-*`, `/battery/opening-balance/*`, `/battery/reports/*-list` | Separate jQuery/Axios JSON endpoints under `/battery/*` (including crashing `BatteryOrderController@OrderCreate` and `BatteryPurchaseController@PurchasesCreate`). | Encapsulated inside authenticated, permission-gated Livewire 4 component methods and domain services (`App\Livewire\Battery\*` & `App\Services\Battery\*`). | **FIXED & VERIFIED** |
