# MARSS Corporation — Database & Model Mapping (58 Tables / 48 Eloquent Models)

**Target Database:** `marsscorp-temp-db` (MySQL 8.4.3, `utf8mb4_unicode_ci`)  
**Total Database Tables:** **`58` tables** (`55` original tables from `marsscorp-temp-db.sql` + `3` incremental tables `user_activity_logs`, `investor_infos`, `invests` created by `2` guarded batch-9 migration files)  
**Total Isolated Eloquent Models:** **`48` models** (`6` Shared + `22` Retail + `20` Battery = `48` domain models) + **`10` framework/infrastructure tables** not mapped to domain Eloquent models (`58` tables total).

---

## 1. Shared Models (`App\Models\Shared` — 6 Models / 6 Tables)

| # | Model | Table | Classification | Key Columns | Relationships | Status |
|---:|---|---|---|---|---|---|
| 1 | `User` | `users` | Original (SQL Dump) | `id, img_url, name, email, mobile, password, otp, otp_expires_at, status, role, permissions` | `activityLogs()` | **VERIFIED** |
| 2 | `UserActivityLog` | `user_activity_logs` | Incremental (`2026_11_19_000001`, Batch 9) | `id, user_id, user_name, user_role, user_email, action, module, description, details, ip_address, user_agent` | `user()` | **VERIFIED** |
| 3 | `District` | `districts` | Original (SQL Dump) | `id, district_name, status, user_id` | `upazilas()`, `thanas()`, `user()` | **VERIFIED** |
| 4 | `Upazila` | `upazilas` | Original (SQL Dump) | `id, upazila_name, status, district_id, user_id` | `district()`, `thanas()`, `user()` | **VERIFIED** |
| 5 | `Thana` | `thanas` | Original (SQL Dump) | `id, Thana_name, status, district_id, upazila_id, user_id` | `district()`, `upazila()`, `user()` | **VERIFIED** |
| 6 | `Location` | `locations` | Original (SQL Dump) | `id, name, status, user_id` | `user()` *(fixed legacy broken FK `'location'` -> `'location_id'`)* | **FIXED & VERIFIED** |

---

## 2. Retail Models (`App\Models\Retail` — 22 Models / 22 Retail Tables ONLY)

| # | Model | Table | Classification | Important Schema Notes | Relationships | Status |
|---:|---|---|---|---|---|---|
| 1 | `RetailBrand` | `brands` | Original (SQL Dump) | `name, logo, status, user_id` | `products()`, `user()` | **VERIFIED** |
| 2 | `RetailCategory` | `categories` | Original (SQL Dump) | `category_name, img_url, status, user_id` | `products()`, `subCategories()`, `user()` | **VERIFIED** |
| 3 | `RetailSubCategory` | `sub_categories` | Original (SQL Dump) | `sub_category_name, status, category_id, user_id` | `category()`, `products()`, `user()` | **VERIFIED** |
| 4 | `RetailUnit` | `units` | Original (SQL Dump) | `unit_name, status, user_id` | `products()`, `user()` | **VERIFIED** |
| 5 | `RetailProduct` | `products` | Original (SQL Dump) | `quantity, cost_price, sell_price` are `varchar`; `product_code` is `json` (normalized via mutator); has `door_side` | `brand()`, `category()`, `subCategory()`, `unit()`, `orderDetails()`, `purchaseOrderDetails()`, `productReturns()`, `purchaseReturns()`, `user()` | **FIXED & VERIFIED** |
| 6 | `RetailSupplier` | `suppliers` | Original (SQL Dump) | `purchase_payable_amount` is `varchar`; has `email, status` | `purchases()`, `dueCollections()`, `purchaseReturns()`, `user()` | **FIXED & VERIFIED** |
| 7 | `RetailPurchase` | `purchases` | Original (SQL Dump) | `paid_amount, due_amount, purchase_payable_amount, grand_subtotal` are `varchar`; `delivery_charge, discount_amount, return_adjustment_amount` are `decimal` | `supplier()`, `orderDetails()`, `paymentDetails()`, `purchaseReturns()`, `user()` | **VERIFIED** |
| 8 | `RetailPurchaseOrderDetail` | `purchase_order_details` | Original (SQL Dump) | `quantity, cost_price, subtotal` are `varchar`; `return_quantity` is `int` | `purchase()`, `product()`, `user()` | **VERIFIED** |
| 9 | `RetailPurchasePaymentDetail` | `purchase_payment_details` | Original (SQL Dump) | FK column is `purchases_id`; `paid_amount, discount_amount` are `decimal(10,2)` | `purchase()`, `orderDetail()`, `user()` | **VERIFIED** |
| 10 | `RetailPurchaseReturn` | `purchase_returns` | Original (SQL Dump) | Includes `return_number, purchase_order_detail_id, unit_price, discount_amount` in `$fillable` | `purchase()`, `purchaseOrderDetail()`, `supplier()`, `product()`, `user()` | **FIXED & VERIFIED** |
| 11 | `RetailSupplierDueCollection` | `supplier_due_collections` | Original (SQL Dump) | `due_collection_date`, `purchase_payable_amount`, `payment_status` | `supplier()`, `user()` | **VERIFIED** |
| 12 | `RetailCustomer` | `customers` | Original (SQL Dump) | `previous_due_amount` is `varchar`; includes `district_id, upazila_id, thana_id, location_id` | `orders()`, `paymentDetails()`, `productReturns()`, `district()`, `upazila()`, `thana()`, `location()`, `user()` | **VERIFIED** |
| 13 | `RetailCustomerPaymentDetail` | `customer_payment_details` | Original (SQL Dump) | `paid_amount, discount_amount, due_amount, previous_due_amount` are `varchar` | `customer()`, `user()` | **VERIFIED** |
| 14 | `RetailOrder` | `orders` | Original (SQL Dump) | `sub_total, paid_amount, due_amount, previous_due_amount` are `decimal(10,2)` | `customer()`, `details()`, `payments()` (`hasMany`), `payment()` (`hasOne` latest), `productReturns()`, `user()` | **FIXED & VERIFIED** |
| 15 | `RetailOrderDetail` | `order_details` | Original (SQL Dump) | `quantity, price, selling_price` are `varchar`; `return_quantity` is `int` | `order()`, `product()`, `returns()`, `user()` | **VERIFIED** |
| 16 | `RetailOrderPaymentDetail` | `order_payment_details` | Original (SQL Dump) | `paid_amount, discount_amount` are `decimal(10,2)`; `due_collection_date` is `varchar` | `order()`, `user()` | **VERIFIED** |
| 17 | `RetailProductReturn` | `product_returns` | Original (SQL Dump) | `amount, discount_amount, due_amount, date` are `varchar`; `unit_price` is `decimal(12,2)` | `order()`, `orderDetail()`, `customer()`, `product()`, `user()` | **VERIFIED** |
| 18 | `RetailExpenseType` | `expense_types` | Original (SQL Dump) | `type_name, status, user_id` | `expenses()`, `user()` | **VERIFIED** |
| 19 | `RetailExpense` | `expenses` | Original (SQL Dump) | `expense_amount` is `decimal(10,2)`; `date` is `varchar`; `staff_id` nullable FK to `users` | `expenseType()`, `staff()`, `user()` | **VERIFIED** |
| 20 | `RetailOpeningBalance` | `opening_balances` | Original (SQL Dump) | `amount` is `decimal(15,2)`; `date` is `date`; unique `(date, user_id)` | `user()` | **VERIFIED** |
| 21 | `RetailInvestorInfo` | `investor_infos` | Incremental (`2026_11_19_000002`, Batch 9) | `investor_id, name, mobile, address, email, status, user_id` | `invests()`, `user()` | **VERIFIED** |
| 22 | `RetailInvest` | `invests` | Incremental (`2026_11_19_000002`, Batch 9) | `invest_amount, invest_details, date, investor_info_id, user_id` | `investor()`, `user()` | **VERIFIED** |

---

## 3. Battery Models (`App\Models\Battery` — 20 Models / 20 `battery_*` Tables ONLY)

| # | Model | Table | Classification | Key Differences vs Retail | Relationships | Status |
|---:|---|---|---|---|---|---|
| 1 | `BatteryBrand` | `battery_brands` | Original (SQL Dump) | `img_url` (not `logo`), no `status` column | `products()`, `user()` | **VERIFIED** |
| 2 | `BatteryCategory` | `battery_categories` | Original (SQL Dump) | `name` (not `category_name`), no `status` column | `products()`, `subCategories()`, `user()` | **VERIFIED** |
| 3 | `BatterySubCategory` | `battery_sub_categories` | Original (SQL Dump) | `name` (not `sub_category_name`), no `status` column | `category()`, `products()`, `user()` | **VERIFIED** |
| 4 | `BatteryUnit` | `battery_units` | Original (SQL Dump) | `unit_name`, no `status` column | `products()`, `user()` | **VERIFIED** |
| 5 | `BatteryProduct` | `battery_products` | Original (SQL Dump) | No `door_side`; `product_code` is JSON cast (never double-encoded) | `brand()`, `category()`, `subCategory()`, `unit()`, `orderDetails()`, `purchaseOrderDetails()`, `productReturns()`, `purchaseReturns()`, `user()` | **FIXED & VERIFIED** |
| 6 | `BatterySupplier` | `battery_suppliers` | Original (SQL Dump) | No `email`, no `status`; `purchase_payable_amount` is `decimal(15,2)` | `purchases()`, `dueCollections()`, `purchaseReturns()`, `user()` | **VERIFIED** |
| 7 | `BatteryPurchase` | `battery_purchases` | Original (SQL Dump) | Monetary columns are `decimal(15,2)` | `supplier()`, `orderDetails()`, `paymentDetails()`, `purchaseReturns()`, `user()` | **VERIFIED** |
| 8 | `BatteryPurchaseOrderDetail` | `battery_purchase_order_details` | Original (SQL Dump) | Includes `return_quantity` in `$fillable` (fixing legacy omission) | `purchase()`, `product()`, `user()` | **FIXED & VERIFIED** |
| 9 | `BatteryPurchasePaymentDetail` | `battery_purchase_payment_details` | Original (SQL Dump) | FK column `purchases_id`, `paid_amount` is `decimal(15,2)` | `purchase()`, `user()` | **VERIFIED** |
| 10 | `BatteryPurchaseReturn` | `battery_purchase_returns` | Original (SQL Dump) | Includes `return_number, purchase_order_detail_id, unit_price` in `$fillable` | `purchase()`, `purchaseOrderDetail()`, `supplier()`, `product()`, `user()` | **FIXED & VERIFIED** |
| 11 | `BatterySupplierDueCollection` | `battery_supplier_due_collections` | Original (SQL Dump) | Uses `payment_date` (not `due_collection_date`), no `purchase_payable_amount` or `payment_status` | `supplier()`, `user()` | **VERIFIED** |
| 12 | `BatteryCustomer` | `battery_customers` | Original (SQL Dump) | `previous_due_amount` is `decimal(15,2)` | `orders()`, `paymentDetails()`, `productReturns()`, `district()`, `upazila()`, `thana()`, `location()`, `user()` | **VERIFIED** |
| 13 | `BatteryCustomerPaymentDetail` | `battery_customer_payment_details` | Original (SQL Dump) | `paid_amount, discount_amount, due_amount, previous_due_amount` are `varchar` | `customer()`, `user()` | **VERIFIED** |
| 14 | `BatteryOrder` | `battery_orders` | Original (SQL Dump) | Monetary columns are `decimal(15,2)` | `customer()`, `details()`, `payments()`, `payment()`, `productReturns()`, `user()` | **VERIFIED** |
| 15 | `BatteryOrderDetail` | `battery_order_details` | Original (SQL Dump) | `quantity, price, selling_price` are `varchar`; `return_quantity` is `int` | `order()`, `product()`, `returns()`, `user()` | **VERIFIED** |
| 16 | `BatteryOrderPaymentDetail` | `battery_order_payment_details` | Original (SQL Dump) | `paid_amount` is `decimal(15,2)` | `order()`, `user()` | **VERIFIED** |
| 17 | `BatteryProductReturn` | `battery_product_returns` | Original (SQL Dump) | `amount, discount_amount, due_amount, date` are `varchar` | `order()`, `orderDetail()`, `customer()`, `product()`, `user()` | **VERIFIED** |
| 18 | `BatteryExpenseType` | `battery_expense_types` | Original (SQL Dump) | **NO `status` column** (`id, type_name, user_id`) | `expenses()`, `user()` | **FIXED & VERIFIED** |
| 19 | `BatteryExpense` | `battery_expenses` | Original (SQL Dump) | `expense_amount` is `decimal(10,2)`, `staff_id` nullable | `expenseType()`, `staff()`, `user()` | **VERIFIED** |
| 20 | `BatteryOpeningBalance` | `battery_opening_balances` | Original (SQL Dump) | `amount` is `decimal(15,2)`, unique `(date, user_id)` | `user()` | **VERIFIED** |

---

## 4. Infrastructure, Framework & Utility Tables (10 Tables — Completing the 58-Table Inventory)

| # | Table | Classification | Purpose | Rows in `marsscorp-temp-db` | Status |
|---:|---|---|---|---:|---|
| 1 | `battery_migration_mappings` | Original (SQL Dump) | Legacy one-time migration mapping audit table | `0` | **PRESERVED** |
| 2 | `cache` | Original (SQL Dump) | Laravel database cache store | `0` | **VERIFIED** |
| 3 | `cache_locks` | Original (SQL Dump) | Laravel atomic cache lock table | `0` | **VERIFIED** |
| 4 | `failed_jobs` | Original (SQL Dump) | Laravel queue failed job log | `0` | **VERIFIED** |
| 5 | `jobs` | Original (SQL Dump) | Laravel queue pending jobs | `0` | **VERIFIED** |
| 6 | `job_batches` | Original (SQL Dump) | Laravel queue batch metadata | `0` | **VERIFIED** |
| 7 | `migrations` | Original (SQL Dump + Batch 9) | Laravel schema migration history (`62` original + `2` incremental = `64`) | `64` | **VERIFIED** |
| 8 | `password_reset_tokens` | Original (SQL Dump) | Laravel password reset token table | `0` | **VERIFIED** |
| 9 | `personal_access_tokens` | Original (SQL Dump) | Historical Sanctum tokens preserved from dump | `8` | **PRESERVED** |
| 10 | `sessions` | Original (SQL Dump) | Laravel session table | `7` | **VERIFIED** |

> **Explicit Confirmation regarding `stock_repair_executions`:** `stock_repair_executions` is **NOT present** in `marsscorp-temp-db.sql` and **NOT present** in `marsscorp-temp-db` (**`NOT APPLICABLE`** — it belonged only to an unexecuted one-off legacy repair command `RepairSalesReturn38Command` in the reference codebase).
