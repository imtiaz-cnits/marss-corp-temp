# MARSS Corporation — Complete Module Inventory

**Status Legend:** `NOT STARTED` | `AUDITING` | `IN PROGRESS` | `BLOCKED` | `IMPLEMENTED` | `TESTING` | `VERIFIED`

---

## 1. Core / Shared System Modules

| # | Module | Reference Entry Route(s) | Target Livewire / Route | Permissions | Status |
|---|---|---|---|---|---|
| C1 | Authentication (Login / Logout / Alias / Status check) | `/`, `/admin-login-page`, `/nexus-login-page`, `/naxus-pos-logout` | `Shared\Auth\LoginPage` | Public / Auth | VERIFIED |
| C2 | User Profile Management | `/admin-dashboard-user-profile` | `Shared\Settings\UserProfilePage` | Authenticated | VERIFIED |
| C3 | User List, Roles & Granular Permissions | `/admin-dashboard-user-role` | `Shared\Settings\UserRoleManagementPage` | `user` / Admin | VERIFIED |
| C4 | User Activity Logs | `/admin-dashboard-user-activity-log` | `Shared\Settings\UserActivityLogPage` | Admin / Super Admin | VERIFIED |
| C5 | Persistent Navigation Shell (Topbar, Retail/Battery Switcher, Drilldown Sidebar, Notifications Bell, Dark/Light Theme, Live Clock) | `layouts/dashboard-sidenav.blade.php` + `battery/layouts/sidebar-panels.blade.php` | `layouts/app.blade.php` + `Shared\Layout\*` | Authenticated | VERIFIED |
| C6 | Geographic Masters (Districts, Upazilas, Thanas, Locations) | `/admin-dashboard-dsistrict`, `/admin-dashboard-upazila`, `/admin-dashboard-thana`, `/admin-dashboard-location` | `Shared\Location\*` | `customer` | VERIFIED |

---

## 2. Retail Shop Modules (Isolated on Retail Tables)

| # | Module | Reference URL(s) | Permission | Status |
|---|---|---|---|---|
| R1 | Retail Overview Dashboard (KPIs, Trends, Donut, Low Stock, Recent Sales, Date Filter) | `/admin-dashboard` | `dashboard` (profit: admin-only) | VERIFIED |
| R2 | Retail POS Terminal (100vh, Cart, Door Handedness, Barcode Scanner, Quick Add Product/Customer, Multi-Payment) | `/admin-dashboard-pos` | `pos_terminal` | VERIFIED |
| R3 | Retail Invoice List, Due Payment Modal & Full Invoice Edit | `/admin-dashboard-invoice` | `invoice_list` | VERIFIED |
| R4 | Retail Sales Invoice & Due Invoice Print | `/invoice/{id}`, `/due-invoice-print/{id}`, `/invoice-print` | `invoice_list` | VERIFIED |
| R5 | Retail Product Management (incl. Door Handedness Left/Right/Both, Grouping, Units) | `/admin-dashboard-product` | `product` | VERIFIED |
| R6 | Retail Brand Management | `/admin-dashboard-brand` | `product` | VERIFIED |
| R7 | Retail Category & Sub-Category Management | `/admin-dashboard-category`, `/admin-dashboard-sub-category` | `product` | VERIFIED |
| R8 | Retail Stock Overview & Stock Adjustment | `/admin-dashboard-stock-overview` | `product` | VERIFIED |
| R9 | Retail Barcode Generator & Label Print | `/admin-dashboard-barcode-genarate`, `/admin-dashboard-product-barcode` | `product` | VERIFIED |
| R10 | Retail Supplier List & Supplier Profile Ledger | `/admin-dashboard-supplier`, `/supplier/profile/{id}` | `purchase` | VERIFIED |
| R11 | Retail Supplier Due List & FIFO Due Collection | `/supplier-due-page`, `/supplier-due-collection-page` | `purchase` | VERIFIED |
| R12 | Retail Purchase List, Create, Edit, Delete, Direct Payment, Invoice Split & Print | `/admin-dashboard-Purchase`, `/purchase-invoice/{id}` | `purchase` | VERIFIED |
| R13 | Retail Customer List & Customer Profile Ledger | `/admin-dashboard-customer`, `/customer/profile/{id}` | `customer` | VERIFIED |
| R14 | Retail Customer Due List & FIFO Due Collection | `/admin-dashboard-customer-due-list`, `/customer-due-collection-page` | `customer` | VERIFIED |
| R15 | Retail Expense List (Batch/Single), Expense Types & Staff Salary Profile | `/admin-dashboard-expence-list`, `/admin-dashboard-expence-type`, `/admin-dashboard-staff-profile` | `expense` | VERIFIED |
| R16 | Retail Return Management (Sales Return & Purchase Return, Batch Create & Print Details) | `/admin-dashboard-return-list`, `/return/{id}`, `/purchase-return/{id}` | `return` | VERIFIED |
| R17 | Retail Opening Balance Management | `/admin-dashboard-opening-balance` | `expense` | VERIFIED |
| R18 | Retail Reports: Daily Income & Expense Ledger | `/admin-dashboard-daily-ledger-report` | `report` | VERIFIED |
| R19 | Retail Reports: Sales Report | `/admin-dashboard-sales-report` | `report` | VERIFIED |
| R20 | Retail Reports: Income & Expense Summary Report | `/admin-dashboard-income-expense-report` | `report` | VERIFIED |
| R21 | Retail Reports: Daily Receipt & Payment Report | `/admin-dashboard-daily-receipt-payment-report` | `report` | VERIFIED |
| R22 | Retail Reports: Personal / Investor Transaction Report | `/admin-dashboard-personal-transaction-report` | `report` | VERIFIED |
| R23 | Retail Reports: Low Stock / Stock-Out Report | `/admin-dashboard-stock-out` | `report` | VERIFIED |
| R24 | Retail Reports: Due Invoice, Customer Invoice & Best-Selling Reports | `/admin-dashboard-due-invoice`, `/admin-dashboard-customer-invoice-report`, `/admin-best-selling-report` | `report` | VERIFIED |
| R25 | Retail Investments & Investor Info (conditional schema support) | `/admin-dashboard-investor-info`, `/admin-dashboard-invest-list` | `report` | VERIFIED |

---

## 3. Battery Modules (100% Isolated on `battery_*` Tables)

| # | Module | Reference URL(s) | Permission | Status |
|---|---|---|---|---|
| B1 | Battery Overview Dashboard | `/battery/dashboard` | `dashboard` (profit: admin-only) | VERIFIED |
| B2 | Battery POS Terminal | `/battery/pos` | `pos_terminal` | VERIFIED |
| B3 | Battery Invoice List, Due Payment & Full Edit | `/battery/invoices` | `invoice_list` | VERIFIED |
| B4 | Battery Sales & Due Invoice Print | `/battery/invoice/{id}`, `/battery/due-invoice-print/{id}` | `invoice_list` | VERIFIED |
| B5 | Battery Product Management | `/battery/products` | `product` | VERIFIED |
| B6 | Battery Brand Management | `/battery/brands` | `product` | VERIFIED |
| B7 | Battery Category, Sub-Category & Unit Management | `/battery/categories`, `/battery/sub-categories`, `/battery/units` | `product` | VERIFIED |
| B8 | Battery Stock Overview & Stock Adjustment | `/battery/stock-overview` | `product` | VERIFIED |
| B9 | Battery Barcode Generator & Label Print | `/battery/barcode-generate` | `product` | VERIFIED |
| B10 | Battery Supplier List & Supplier Profile Ledger | `/battery/suppliers`, `/battery/supplier/profile/{id}` | `purchase` | VERIFIED |
| B11 | Battery Supplier Due List & FIFO Due Collection | `/battery/supplier-due`, `/battery/supplier-due-collection` | `purchase` | VERIFIED |
| B12 | Battery Purchase List, Create, Edit, Delete, Payments, Split & Print | `/battery/purchases`, `/battery/purchase-payments`, `/battery/purchase-invoice/{id}` | `purchase` | VERIFIED |
| B13 | Battery Customer List & Customer Profile Ledger | `/battery/customers`, `/battery/customer/profile/{id}` | `customer` | VERIFIED |
| B14 | Battery Customer Due List & FIFO Due Collection | `/battery/customer-due`, `/battery/customer-due-collection` | `customer` | VERIFIED |
| B15 | Battery Expense List & Expense Types | `/battery/expenses`, `/battery/expense-types` | `expense` | VERIFIED |
| B16 | Battery Return Management (Sales Returns & Purchase Returns + Details Print) | `/battery/sales-returns`, `/battery/return/{id}`, `/battery/purchase-returns`, `/battery/purchase-return/{id}` | `return` | VERIFIED |
| B17 | Battery Opening Balance Management | `/battery/opening-balance` | `expense` | VERIFIED |
| B18 | Battery Reports: Daily Income & Expense Ledger | `/battery/reports/daily-ledger` | `report` | VERIFIED |
| B19 | Battery Reports: Sales Report | `/battery/reports/sales` | `report` | VERIFIED |
| B20 | Battery Reports: Income & Expense Report | `/battery/reports/income-expense` | `report` | VERIFIED |
| B21 | Battery Reports: Daily Receipt & Payment Report | `/battery/reports/daily-receipt-payment` | `report` | VERIFIED |
| B22 | Battery Reports: Personal Transaction Report (Isolated from Retail `invests`!) | `/battery/reports/personal-transaction` | `report` | VERIFIED |
| B23 | Battery Reports: Low Stock / Stock-Out Report | `/battery/reports/stock-out` | `report` | VERIFIED |
