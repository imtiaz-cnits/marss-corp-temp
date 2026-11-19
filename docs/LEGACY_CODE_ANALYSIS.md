# MARSS Corporation — Legacy Code Analysis

**Audit Date:** 2026-11-19  
**Source Inspected (Read-Only):** `f:\Running-work\marss-corp-temp\marsscorp-retailshop-mgm`

---

## 1. Unused / Dead Backend Code (Eliminated in New Architecture)

- **Empty Controller:** `app/Http/Controllers/PurchaseOrderDetailsController.php` (10-line empty class).
- **Unused Model (No Table/Migration):** `app/Models/BuyerInfo.php` (`buyer_infos` table does not exist).
- **Stray Archive Inside Source Tree:** `app/Models.zip` committed directly inside `app/`.
- **Dead Middleware:** `app/Http/Middleware/AdminOnly.php` (aliased as `is_admin` in `bootstrap/app.php`, applied to 0 routes, incompatible with token-only auth).
- **Unused Controller Methods:** `UserController::{SendOTPCode, ResetPassword, UserProfile}`, `OrderController::generateCustomerID`, `StockOverviewController::RetailStockOverviewPage`, `ExpenseController::AllExpensesDataShow`, `CategoryController::{AJXCategoryList, GetCategoriesList}`.
- **Unused Mail Stubs:** `app/Mail/MyDemoEmail.php` (references missing `view.name`), duplicate `resources/views/emails/OTPMail.blade.php`.
- **Commented-Out Code Bloat:** ~1,140 lines of commented-out legacy implementations inside `CustomerDueCollectionController.php` (L80–1219), plus large commented OTP blocks in `UserController.php`.
- **One-Off Historical Scripts:** `ShahinAlomStockRepairService`, `MigrateRetailToBatteryCommand`, `RepairSalesReturn38Command`, `public/clear-cache.php` (dangerous unauthenticated cache-clear script in webroot).

---

## 2. Broken Model Relationships & Mass-Assignment Bugs (Corrected in New Models)

| Legacy File | Defect | Fix in New Models |
|---|---|---|
| `app/Models/Order.php` | `payment()` is `hasOne` instead of `hasMany` (`payments()`). | `RetailOrder` & `BatteryOrder` define `payments()` (`hasMany`) + `latestPayment()` (`hasOne()->latestOfMany()`). |
| `app/Models/Supplier.php` | `products()` references non-existent `suppliers_id`; `purchasePaymentDetails()` has wrong `hasManyThrough` keys. | Removed broken `products()` relation; fixed `purchasePaymentDetails()` via `purchases.supplier_id` -> `purchase_payment_details.purchases_id`. |
| `app/Models/Location.php` | `customers()` uses FK `'location'` instead of `'location_id'`. | Fixed FK to `'location_id'`. |
| `app/Models/PurchaseReturn.php` & `BatteryPurchaseReturn.php` | `$fillable` omits `return_number`, `purchase_order_detail_id`, `unit_price`, `discount_amount` (silently dropped on `create()`). | Added all columns from schema to `$fillable`. |
| `app/Models/Battery/BatteryPurchaseOrderDetail.php` | `$fillable` omits `return_quantity`. | Added `return_quantity` to `$fillable`. |
| `app/Models/Battery/BatteryProduct.php` | `$casts = ['product_code' => 'array']` combined with controller `json_encode()` causes double-JSON encoding. | Model casts `product_code` as `array` cleanly; services ALWAYS pass native PHP arrays (never `json_encode`). |
| `app/Models/Customer.php` | `$fillable` omits `upazila_id`, `thana_id`. | Included all geographic FK columns cleanly. |

---

## 3. Duplicated & Conflicting Frontend Assets (Eliminated in TALL Stack)

- **Dual-Loaded Scripts:** `config.js`, `modals.js`/`all-modals.js`, `table-utils.js`/`table-funtion.js`, `theme.js`/`full-screen-toggle.js` loaded simultaneously via Vite AND `<script src>` causing double-bound handlers and conflicting toast positions (`top/right` vs `bottom/left`).
- **4 Conflicting `toggle_light_mode()` Implementations:** Consolidated into 1 clean Alpine/Tailwind dark-mode store.
- **Unused Heavy Libraries Removed:** Bootstrap 5.1.3 & 5.3.0, jQuery 3.6 & 3.7, Select2, VanillaJS Datepicker 1.2.0 (0 usages), bootstrap-datepicker (2012), SweetAlert2, Toastify, Simplebar, Popper.js, Swiper, MaterialDesignIcons woff2 (303 KB, unused), external CodePen CSS injection (`full-screen-toggle.js`).
- **Unused / Broken / Empty Views Removed:** `pages/back-end-page/pos-page.blade.php` (80 KB orphan), `demo.blade.php`, `practices.blade.php`, `supplier-due-payable-list.blade.php`, 0-byte `return-delete.blade.php` & `return-update.blade.php`, broken references to missing `unit-page`, `warehouse-page`, `modal-page`.

---

## 4. Performance & Query Issues (Optimized in New Architecture)

- **N+1 Queries:** Dashboard trend charts execute 4 queries × up to 61 days (~244 queries per dashboard load!). Replaced with single grouped SQL queries (`GROUP BY date`).
- **Unpaginated Full Table Scans:** Legacy list endpoints (`ProductList`, `CustomerList`, `InvoiceOrderPaymentDetails`, `PurchasesList`) load ALL rows into JSON and paginate in browser JS; `InvoiceOrderDuePaymentDetails` loads ALL orders then filters `due_amount > 0` in PHP. Replaced with efficient Livewire server-side pagination (`WithPagination`) + eager loading (`with(...)`) + SQL filtering.
- **Broken Double API Prefix:** `/api/api/all-products-data-show` in `routes/api.php:80`.
- **Non-Idempotent GET Requests:** `GET /api/expense-type-list` and `GET /api/battery/expense-type-list` executed DB `INSERT` queries to auto-seed `'Sallery'`/`'Salary'`. Replaced with clean service/seeder check that never mutates on read unless explicitly ensured.
