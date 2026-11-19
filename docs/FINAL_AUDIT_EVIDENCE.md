# MARSS Corporation — Final Independent Audit Evidence Dossier (`F-01`..`F-04`)

All status entries in this dossier strictly use the five allowed audit classification values:
- `VERIFIED`
- `FIXED & VERIFIED`
- `PRESERVED`
- `OPEN`
- `NOT APPLICABLE`

---

## 1. Audit Date and Environment

| Parameter | Audited Value | Status |
|---|---|---|
| **Audit Date** | `2026-11-19` | **VERIFIED** |
| **Operating System** | Windows 10/11 (`10.0.19045`, x64) | **VERIFIED** |
| **PHP Runtime** | `PHP 8.3.30` (`cli`, `bcmath` enabled) | **VERIFIED** |
| **Application Framework** | `Laravel Framework 13.43.0` + `Livewire v4.4.8` + `Tailwind CSS v4` + `Vite v8.3.8` | **VERIFIED** |
| **Database Server** | `MySQL 8.4.3` (`127.0.0.1:3306`, `utf8mb4` / `utf8mb4_unicode_ci`) | **VERIFIED** |
| **Target Database** | `marsscorp-temp-db` (`58` tables) | **VERIFIED** |
| **Authoritative SQL Dump** | `f:\Running-work\marss-corp-temp\marsscorp-temp-db.sql` (`306,430` bytes, read-only) | **PRESERVED** |
| **Reference Project Directory** | `f:\Running-work\marss-corp-temp\marsscorp-retailshop-mgm\` (read-only) | **PRESERVED** |
| **Pre-Edit File Backups** | `C:\Users\CNIT PC 01\.gemini\antigravity\brain\6d180ff3-faa1-4495-80f6-dae15ae1c63e\scratch\backups\20261119_090422\` | **VERIFIED** |

### Executive Finding-by-Finding Audit Matrix

| Finding ID | Title | Audit Verdict | Status Classification |
|---|---|---|---|
| **Finding F-01** | Database and Migration Reconciliation (`55 + 3 = 58` tables, `62 + 2 = 64` migrations) | **PASS** | **FIXED & VERIFIED** |
| **Finding F-02** | `FIN-13`, `FIN-14`, `FIN-15` Code Hardening & Dedicated Regression Verification | **PASS** | **FIXED & VERIFIED** |
| **Finding F-03** | Complete Laravel Route Inventory (`12 Shared + 41 Retail + 36 Battery = 89 App Routes` + `12 Vendor = 101 Total`) | **PASS** | **FIXED & VERIFIED** |
| **Finding F-04** | Final Regression Evidence (`30 / 30 Tests Passed, 278 Assertions, 0 Failures`) | **PASS** | **VERIFIED** |

---

## 2. Database Inventory Evidence (Finding F-01)

### 2.1 Reconciliation of `55 + 3 = 58` vs `55 + 2 = 58` Wording Discrepancy

- **Original Tables Imported from `marsscorp-temp-db.sql`:** **`55` tables** (recording `62` migration entries across batches `1`–`8`).
- **Incremental Migration Files in `database/migrations/`:** **`2` migration files** (executed in batch `9`, adding `2` rows to `migrations` for a total of `64` rows in `migrations`).
- **Incremental Tables Created by those `2` Migration Files:** **`3` tables**:
  1. `2026_11_19_000001_create_user_activity_logs_table_if_missing.php` -> created **`1` table**: `user_activity_logs`.
  2. `2026_11_19_000002_create_investor_tables_if_missing.php` -> created **`2` tables**: `investor_infos` and `invests`.
- **Why the Earlier Wording Discrepancy Occurred:** `MIGRATION_PROGRESS.md` previously wrote `"55 original tables + 2 guarded incremental migrations (58 tables total)"` (referring to the **2 migration files**), while `FINAL_ACCEPTANCE_REPORT.md` wrote `"55 original tables + 3 safe incremental tables = 58 tables"` (referring to the **3 tables** created by those 2 files), and `DATABASE_INTEGRITY_REPORT.md` showed the initial Phase-2 pre-migration import snapshot (`55` original tables).
- **Synchronized Statement Across All Documentation:**
  - **`55` original tables + `3` incremental tables (`user_activity_logs`, `investor_infos`, `invests` created by `2` guarded incremental migration files) = `58` total tables in `marsscorp-temp-db`.**

### 2.2 Exact `SHOW TABLES` Output from `marsscorp-temp-db` (58 Tables)

| # | `Tables_in_marsscorp-temp-db` |
|---:|---|
| 1 | `battery_brands` |
| 2 | `battery_categories` |
| 3 | `battery_customer_payment_details` |
| 4 | `battery_customers` |
| 5 | `battery_expense_types` |
| 6 | `battery_expenses` |
| 7 | `battery_migration_mappings` |
| 8 | `battery_opening_balances` |
| 9 | `battery_order_details` |
| 10 | `battery_order_payment_details` |
| 11 | `battery_orders` |
| 12 | `battery_product_returns` |
| 13 | `battery_products` |
| 14 | `battery_purchase_order_details` |
| 15 | `battery_purchase_payment_details` |
| 16 | `battery_purchase_returns` |
| 17 | `battery_purchases` |
| 18 | `battery_sub_categories` |
| 19 | `battery_supplier_due_collections` |
| 20 | `battery_suppliers` |
| 21 | `battery_units` |
| 22 | `brands` |
| 23 | `cache` |
| 24 | `cache_locks` |
| 25 | `categories` |
| 26 | `customer_payment_details` |
| 27 | `customers` |
| 28 | `districts` |
| 29 | `expense_types` |
| 30 | `expenses` |
| 31 | `failed_jobs` |
| 32 | `investor_infos` |
| 33 | `invests` |
| 34 | `job_batches` |
| 35 | `jobs` |
| 36 | `locations` |
| 37 | `migrations` |
| 38 | `opening_balances` |
| 39 | `order_details` |
| 40 | `order_payment_details` |
| 41 | `orders` |
| 42 | `password_reset_tokens` |
| 43 | `personal_access_tokens` |
| 44 | `product_returns` |
| 45 | `products` |
| 46 | `purchase_order_details` |
| 47 | `purchase_payment_details` |
| 48 | `purchase_returns` |
| 49 | `purchases` |
| 50 | `sessions` |
| 51 | `sub_categories` |
| 52 | `supplier_due_collections` |
| 53 | `suppliers` |
| 54 | `thanas` |
| 55 | `units` |
| 56 | `upazilas` |
| 57 | `user_activity_logs` |
| 58 | `users` |

### 2.3 Confirmation of `user_activity_logs`, `investor_infos`, `invests`, and `stock_repair_executions`

| Table Name | In `marsscorp-temp-db.sql`? | In `marsscorp-temp-db`? | Target Row Count | Evidence / Explanation | Status |
|---|---|---|---:|---|---|
| `user_activity_logs` | `false` | **`true`** | `0` | Created by `database/migrations/2026_11_19_000001_create_user_activity_logs_table_if_missing.php` (`Batch 9`, `migrations.id = 63`) | **VERIFIED** |
| `investor_infos` | `false` | **`true`** | `0` | Created by `database/migrations/2026_11_19_000002_create_investor_tables_if_missing.php` (`Batch 9`, `migrations.id = 64`) | **VERIFIED** |
| `invests` | `false` | **`true`** | `0` | Created by `database/migrations/2026_11_19_000002_create_investor_tables_if_missing.php` (`Batch 9`, `migrations.id = 64`) | **VERIFIED** |
| `stock_repair_executions` | `false` | **`false`** | `N/A` | Existed only in reference repo `2026_11_18_000001_create_stock_repair_executions_table.php` for legacy one-off command `RepairSalesReturn38Command`. Historical data is never silently rewritten in target app. | **NOT APPLICABLE** |

### 2.4 Complete `information_schema.tables` Inventory (`marsscorp-temp-db`, All 58 Tables)

| # | `TABLE_NAME` | `ENGINE` | `TABLE_ROWS` (InnoDB Estimate) | Exact `SELECT COUNT(*)` | `DATA_LENGTH` | `INDEX_LENGTH` | `TABLE_COLLATION` | `CREATE_TIME` |
|---:|---|---|---:|---:|---:|---:|---|---|
| 1 | `battery_brands` | `InnoDB` | 0 | **0** | 16384 | 16384 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:48` |
| 2 | `battery_categories` | `InnoDB` | 0 | **0** | 16384 | 16384 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:48` |
| 3 | `battery_customer_payment_details` | `InnoDB` | 0 | **0** | 16384 | 32768 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:48` |
| 4 | `battery_customers` | `InnoDB` | 0 | **0** | 16384 | 65536 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:48` |
| 5 | `battery_expense_types` | `InnoDB` | 1 | **1** | 16384 | 16384 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:48` |
| 6 | `battery_expenses` | `InnoDB` | 0 | **0** | 16384 | 32768 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:48` |
| 7 | `battery_migration_mappings` | `InnoDB` | 0 | **0** | 16384 | 49152 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:44` |
| 8 | `battery_opening_balances` | `InnoDB` | 0 | **0** | 16384 | 32768 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:48` |
| 9 | `battery_order_details` | `InnoDB` | 0 | **0** | 16384 | 49152 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:48` |
| 10 | `battery_order_payment_details` | `InnoDB` | 0 | **0** | 16384 | 32768 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:48` |
| 11 | `battery_orders` | `InnoDB` | 0 | **0** | 16384 | 32768 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:48` |
| 12 | `battery_product_returns` | `InnoDB` | 0 | **0** | 16384 | 98304 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:48` |
| 13 | `battery_products` | `InnoDB` | 0 | **0** | 16384 | 81920 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:48` |
| 14 | `battery_purchase_order_details` | `InnoDB` | 0 | **0** | 16384 | 49152 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:48` |
| 15 | `battery_purchase_payment_details` | `InnoDB` | 1 | **1** | 16384 | 32768 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:48` |
| 16 | `battery_purchase_returns` | `InnoDB` | 0 | **0** | 16384 | 98304 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:48` |
| 17 | `battery_purchases` | `InnoDB` | 0 | **0** | 16384 | 32768 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:48` |
| 18 | `battery_sub_categories` | `InnoDB` | 0 | **0** | 16384 | 32768 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:48` |
| 19 | `battery_supplier_due_collections` | `InnoDB` | 0 | **0** | 16384 | 32768 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:48` |
| 20 | `battery_suppliers` | `InnoDB` | 0 | **0** | 16384 | 16384 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:48` |
| 21 | `battery_units` | `InnoDB` | 0 | **0** | 16384 | 16384 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:48` |
| 22 | `brands` | `InnoDB` | 21 | **21** | 16384 | 16384 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:48` |
| 23 | `cache` | `InnoDB` | 0 | **0** | 16384 | 0 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:34` |
| 24 | `cache_locks` | `InnoDB` | 0 | **0** | 16384 | 0 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:34` |
| 25 | `categories` | `InnoDB` | 29 | **29** | 16384 | 16384 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:48` |
| 26 | `customer_payment_details` | `InnoDB` | 31 | **31** | 16384 | 32768 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:48` |
| 27 | `customers` | `InnoDB` | 104 | **104** | 16384 | 81920 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:48` |
| 28 | `districts` | `InnoDB` | 0 | **0** | 16384 | 16384 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:48` |
| 29 | `expense_types` | `InnoDB` | 23 | **23** | 16384 | 16384 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:48` |
| 30 | `expenses` | `InnoDB` | 159 | **159** | 16384 | 49152 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:48` |
| 31 | `failed_jobs` | `InnoDB` | 0 | **0** | 16384 | 16384 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:46` |
| 32 | `investor_infos` | `InnoDB` | 0 | **0** | 16384 | 16384 | `utf8mb4_unicode_ci` | `2026-11-19 13:45:52` |
| 33 | `invests` | `InnoDB` | 0 | **0** | 16384 | 32768 | `utf8mb4_unicode_ci` | `2026-11-19 13:45:52` |
| 34 | `job_batches` | `InnoDB` | 0 | **0** | 16384 | 0 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:35` |
| 35 | `jobs` | `InnoDB` | 0 | **0** | 16384 | 16384 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:46` |
| 36 | `locations` | `InnoDB` | 0 | **0** | 16384 | 16384 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:48` |
| 37 | `migrations` | `InnoDB` | 62 | **64** | 16384 | 0 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:46` |
| 38 | `opening_balances` | `InnoDB` | 0 | **0** | 16384 | 32768 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:48` |
| 39 | `order_details` | `InnoDB` | 466 | **466** | 65536 | 49152 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:48` |
| 40 | `order_payment_details` | `InnoDB` | 150 | **150** | 16384 | 32768 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:48` |
| 41 | `orders` | `InnoDB` | 124 | **124** | 16384 | 32768 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:48` |
| 42 | `password_reset_tokens` | `InnoDB` | 0 | **0** | 16384 | 0 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:35` |
| 43 | `personal_access_tokens` | `InnoDB` | 8 | **8** | 16384 | 32768 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:46` |
| 44 | `product_returns` | `InnoDB` | 9 | **9** | 16384 | 81920 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:49` |
| 45 | `products` | `InnoDB` | 406 | **406** | 81920 | 81920 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:48` |
| 46 | `purchase_order_details` | `InnoDB` | 134 | **134** | 16384 | 49152 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:49` |
| 47 | `purchase_payment_details` | `InnoDB` | 20 | **20** | 16384 | 49152 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:49` |
| 48 | `purchase_returns` | `InnoDB` | 0 | **0** | 16384 | 98304 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:49` |
| 49 | `purchases` | `InnoDB` | 19 | **19** | 16384 | 32768 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:49` |
| 50 | `sessions` | `InnoDB` | 7 | **7** | 16384 | 32768 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:37` |
| 51 | `sub_categories` | `InnoDB` | 0 | **0** | 16384 | 32768 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:49` |
| 52 | `supplier_due_collections` | `InnoDB` | 3 | **3** | 16384 | 32768 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:49` |
| 53 | `suppliers` | `InnoDB` | 14 | **14** | 16384 | 16384 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:49` |
| 54 | `thanas` | `InnoDB` | 0 | **0** | 16384 | 49152 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:49` |
| 55 | `units` | `InnoDB` | 1 | **1** | 16384 | 16384 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:49` |
| 56 | `upazilas` | `InnoDB` | 0 | **0** | 16384 | 32768 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:49` |
| 57 | `user_activity_logs` | `InnoDB` | 0 | **0** | 16384 | 49152 | `utf8mb4_unicode_ci` | `2026-11-19 13:45:52` |
| 58 | `users` | `InnoDB` | 6 | **6** | 16384 | 16384 | `utf8mb4_unicode_ci` | `2026-11-19 13:35:48` |

### 2.5 Original vs Incremental Classification & Row-Count Comparison (All 58 Tables)

| # | Table | Classification | Module | Columns | SQL Dump Rows (`marsscorp-temp-db.sql`) | Target DB Exact `COUNT(*)` (`marsscorp-temp-db`) | Untouched Local DB (`marsscorpdb`) | AUTO_INCREMENT | FKs | Engine / Collation | Status |
|---:|---|---|---|---:|---:|---:|---:|---:|---:|---|---|
| 1 | `battery_brands` | Original (SQL Dump) | Battery | 6 | 0 | 0 | 0 | 3 | 1 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 2 | `battery_categories` | Original (SQL Dump) | Battery | 6 | 0 | 0 | 0 | 2 | 1 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 3 | `battery_customer_payment_details` | Original (SQL Dump) | Battery | 13 | 0 | 0 | 0 | 6 | 2 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 4 | `battery_customers` | Original (SQL Dump) | Battery | 17 | 0 | 0 | 0 | 5 | 4 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 5 | `battery_expense_types` | Original (SQL Dump) | Battery | 5 | 1 | 1 | 1 | 4 | 1 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 6 | `battery_expenses` | Original (SQL Dump) | Battery | 9 | 0 | 0 | 0 | 3 | 2 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 7 | `battery_migration_mappings` | Original (SQL Dump) | Battery | 10 | 0 | 0 | 0 | 38 | 0 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 8 | `battery_opening_balances` | Original (SQL Dump) | Battery | 7 | 0 | 0 | 0 | - | 1 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 9 | `battery_order_details` | Original (SQL Dump) | Battery | 10 | 0 | 0 | 0 | 4 | 3 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 10 | `battery_order_payment_details` | Original (SQL Dump) | Battery | 11 | 0 | 0 | 0 | 3 | 2 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 11 | `battery_orders` | Original (SQL Dump) | Battery | 17 | 0 | 0 | 0 | 4 | 2 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 12 | `battery_product_returns` | Original (SQL Dump) | Battery | 15 | 0 | 0 | 0 | - | 4 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 13 | `battery_products` | Original (SQL Dump) | Battery | 15 | 0 | 0 | 0 | 6 | 5 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 14 | `battery_purchase_order_details` | Original (SQL Dump) | Battery | 10 | 0 | 0 | 0 | 2 | 3 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 15 | `battery_purchase_payment_details` | Original (SQL Dump) | Battery | 11 | 1 | 1 | 1 | 2 | 2 | `InnoDB` / `utf8mb4_unicode_ci` | **PRESERVED (1 orphan row)** |
| 16 | `battery_purchase_returns` | Original (SQL Dump) | Battery | 15 | 0 | 0 | 0 | - | 4 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 17 | `battery_purchases` | Original (SQL Dump) | Battery | 18 | 0 | 0 | 0 | 2 | 2 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 18 | `battery_sub_categories` | Original (SQL Dump) | Battery | 6 | 0 | 0 | 0 | - | 2 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 19 | `battery_supplier_due_collections` | Original (SQL Dump) | Battery | 11 | 0 | 0 | 0 | 4 | 2 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 20 | `battery_suppliers` | Original (SQL Dump) | Battery | 11 | 0 | 0 | 0 | 3 | 1 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 21 | `battery_units` | Original (SQL Dump) | Battery | 5 | 0 | 0 | 0 | 2 | 1 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 22 | `brands` | Original (SQL Dump) | Retail | 7 | 21 | 21 | 22 | 25 | 1 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 23 | `cache` | Original (SQL Dump) | Framework | 3 | 0 | 0 | 0 | - | 0 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 24 | `cache_locks` | Original (SQL Dump) | Framework | 3 | 0 | 0 | 0 | - | 0 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 25 | `categories` | Original (SQL Dump) | Retail | 7 | 29 | 29 | 29 | 31 | 1 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 26 | `customer_payment_details` | Original (SQL Dump) | Retail | 13 | 31 | 31 | 28 | 49 | 2 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 27 | `customers` | Original (SQL Dump) | Retail | 17 | 104 | 104 | 101 | 113 | 5 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 28 | `districts` | Original (SQL Dump) | Shared | 6 | 0 | 0 | 0 | - | 1 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 29 | `expense_types` | Original (SQL Dump) | Retail | 6 | 23 | 23 | 23 | 24 | 1 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 30 | `expenses` | Original (SQL Dump) | Retail | 9 | 159 | 159 | 159 | 162 | 3 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 31 | `failed_jobs` | Original (SQL Dump) | Framework | 7 | 0 | 0 | 0 | - | 0 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 32 | `investor_infos` | Incremental (Batch 9) | Retail (Investment) | 9 | N/A (Not in dump) | 0 | N/A | - | 1 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 33 | `invests` | Incremental (Batch 9) | Retail (Investment) | 8 | N/A (Not in dump) | 0 | N/A | - | 1 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 34 | `job_batches` | Original (SQL Dump) | Framework | 10 | 0 | 0 | 0 | - | 0 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 35 | `jobs` | Original (SQL Dump) | Framework | 7 | 0 | 0 | 0 | - | 0 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 36 | `locations` | Original (SQL Dump) | Shared | 6 | 0 | 0 | 0 | - | 1 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 37 | `migrations` | Original (SQL Dump) | Framework | 3 | 62 | 64 | 64 | 63 | 0 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED (62 dump + 2 batch-9)** |
| 38 | `opening_balances` | Original (SQL Dump) | Retail | 7 | 0 | 0 | 0 | - | 1 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 39 | `order_details` | Original (SQL Dump) | Retail | 10 | 466 | 466 | 327 | 508 | 3 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 40 | `order_payment_details` | Original (SQL Dump) | Retail | 11 | 150 | 150 | 123 | 206 | 2 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 41 | `orders` | Original (SQL Dump) | Retail | 17 | 124 | 124 | 107 | 136 | 2 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 42 | `password_reset_tokens` | Original (SQL Dump) | Framework | 3 | 0 | 0 | 0 | - | 0 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 43 | `personal_access_tokens` | Original (SQL Dump) | Framework | 10 | 8 | 8 | 2 | 160 | 0 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 44 | `product_returns` | Original (SQL Dump) | Retail | 15 | 9 | 9 | 9 | 11 | 3 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 45 | `products` | Original (SQL Dump) | Retail | 16 | 406 | 406 | 371 | 818 | 5 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 46 | `purchase_order_details` | Original (SQL Dump) | Retail | 10 | 134 | 134 | 134 | 184 | 3 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 47 | `purchase_payment_details` | Original (SQL Dump) | Retail | 12 | 20 | 20 | 19 | 24 | 3 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 48 | `purchase_returns` | Original (SQL Dump) | Retail | 15 | 0 | 0 | 0 | - | 4 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 49 | `purchases` | Original (SQL Dump) | Retail | 18 | 19 | 19 | 19 | 36 | 2 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 50 | `sessions` | Original (SQL Dump) | Framework | 6 | 1 | 7 | 2 | - | 0 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 51 | `sub_categories` | Original (SQL Dump) | Retail | 7 | 0 | 0 | 0 | - | 2 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 52 | `supplier_due_collections` | Original (SQL Dump) | Retail | 13 | 3 | 3 | 1 | 7 | 2 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 53 | `suppliers` | Original (SQL Dump) | Retail | 13 | 14 | 14 | 13 | 16 | 1 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 54 | `thanas` | Original (SQL Dump) | Shared | 8 | 0 | 0 | 0 | - | 3 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 55 | `units` | Original (SQL Dump) | Retail | 6 | 1 | 1 | 1 | 2 | 1 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 56 | `upazilas` | Original (SQL Dump) | Shared | 7 | 0 | 0 | 0 | - | 2 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 57 | `user_activity_logs` | Incremental (Batch 9) | Shared/Auth | 13 | N/A (Not in dump) | 0 | N/A | - | 0 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |
| 58 | `users` | Original (SQL Dump) | Shared | 13 | 6 | 6 | 6 | 7 | 0 | `InnoDB` / `utf8mb4_unicode_ci` | **VERIFIED** |

---

## 3. Migration Execution Evidence (Finding F-01)

### 3.1 Incremental Migration Filenames in `database/migrations/` and Execution Status

```text
+------+-------------------------------------------------------------------+-------+
| Ran? | Migration                                                         | Batch |
+------+-------------------------------------------------------------------+-------+
| Yes  | 2026_11_19_000001_create_user_activity_logs_table_if_missing      | 9     |
| Yes  | 2026_11_19_000002_create_investor_tables_if_missing               | 9     |
+------+-------------------------------------------------------------------+-------+
```

- File 1: `database/migrations/2026_11_19_000001_create_user_activity_logs_table_if_missing.php` — Guarded with `if (! Schema::hasTable('user_activity_logs'))`, creates `user_activity_logs` (`Ran`, Batch `9`, `migrations.id = 63`).
- File 2: `database/migrations/2026_11_19_000002_create_investor_tables_if_missing.php` — Guarded with `if (! Schema::hasTable('investor_infos'))` and `if (! Schema::hasTable('invests'))`, creates `investor_infos` and `invests` (`Ran`, Batch `9`, `migrations.id = 64`).

### 3.2 Complete `migrations` Table Contents (`marsscorp-temp-db`, All 64 Rows)

| `id` | `migration` | `batch` | Source | Status |
|---:|---|---:|---|---|
| 1 | `0001_01_01_000000_create_users_table` | 1 | Original SQL Dump | **VERIFIED** |
| 2 | `0001_01_01_000001_create_cache_table` | 1 | Original SQL Dump | **VERIFIED** |
| 3 | `0001_01_01_000002_create_jobs_table` | 1 | Original SQL Dump | **VERIFIED** |
| 4 | `2024_09_19_090149_create_personal_access_tokens_table` | 1 | Original SQL Dump | **VERIFIED** |
| 5 | `2024_09_21_084506_create_brands_table` | 1 | Original SQL Dump | **VERIFIED** |
| 6 | `2024_09_21_130437_create_categories_table` | 1 | Original SQL Dump | **VERIFIED** |
| 7 | `2025_01_08_071602_create_sub_categories_table` | 1 | Original SQL Dump | **VERIFIED** |
| 8 | `2025_01_08_072428_create_units_table` | 1 | Original SQL Dump | **VERIFIED** |
| 9 | `2025_01_08_072707_create_products_table` | 1 | Original SQL Dump | **VERIFIED** |
| 10 | `2025_01_11_053642_create_suppliers_table` | 1 | Original SQL Dump | **VERIFIED** |
| 11 | `2025_01_12_061519_create_purchases_table` | 1 | Original SQL Dump | **VERIFIED** |
| 12 | `2025_01_13_100914_create_purchase_order_details_table` | 1 | Original SQL Dump | **VERIFIED** |
| 13 | `2025_01_13_103344_create_purchase_payment_details_table` | 1 | Original SQL Dump | **VERIFIED** |
| 14 | `2025_01_14_111246_create_districts_table` | 1 | Original SQL Dump | **VERIFIED** |
| 15 | `2025_01_15_071041_create_upazilas_table` | 1 | Original SQL Dump | **VERIFIED** |
| 16 | `2025_01_15_094111_create_thanas_table` | 1 | Original SQL Dump | **VERIFIED** |
| 17 | `2025_01_20_081311_create_locations_table` | 1 | Original SQL Dump | **VERIFIED** |
| 18 | `2025_01_20_094304_create_customers_table` | 1 | Original SQL Dump | **VERIFIED** |
| 19 | `2025_01_20_094711_create_customer_payment_details_table` | 1 | Original SQL Dump | **VERIFIED** |
| 20 | `2025_01_20_101901_add_location_id_to_customers_table` | 1 | Original SQL Dump | **VERIFIED** |
| 21 | `2025_01_22_101032_create_orders_table` | 1 | Original SQL Dump | **VERIFIED** |
| 22 | `2025_01_22_101435_create_order_details_table` | 1 | Original SQL Dump | **VERIFIED** |
| 23 | `2025_01_22_101530_create_order_payment_details_table` | 1 | Original SQL Dump | **VERIFIED** |
| 24 | `2025_02_04_065739_create_expense_types_table` | 1 | Original SQL Dump | **VERIFIED** |
| 25 | `2025_02_04_070426_create_expenses_table` | 1 | Original SQL Dump | **VERIFIED** |
| 26 | `2025_02_10_090857_create_product_returns_table` | 1 | Original SQL Dump | **VERIFIED** |
| 27 | `2025_02_26_094016_create_supplier_due_collections_table` | 1 | Original SQL Dump | **VERIFIED** |
| 28 | `2025_08_20_021940_create_purchase_returns_table` | 1 | Original SQL Dump | **VERIFIED** |
| 29 | `2025_11_25_174839_create_opening_balances_table` | 1 | Original SQL Dump | **VERIFIED** |
| 30 | `2026_08_03_165159_add_permissions_to_users_table` | 1 | Original SQL Dump | **VERIFIED** |
| 31 | `2026_08_06_000001_add_return_adjustment_amount_to_orders_table` | 1 | Original SQL Dump | **VERIFIED** |
| 32 | `2026_08_06_000002_add_quantity_and_product_id_to_product_returns_table` | 1 | Original SQL Dump | **VERIFIED** |
| 33 | `2026_08_07_000002_add_return_adjustment_amount_to_purchases_table` | 1 | Original SQL Dump | **VERIFIED** |
| 34 | `2026_08_25_000001_add_staff_id_to_expenses_table` | 2 | Original SQL Dump | **VERIFIED** |
| 35 | `2026_09_01_000001_add_door_side_to_products_table` | 2 | Original SQL Dump | **VERIFIED** |
| 36 | `2026_09_16_000001_add_delivery_charge_to_orders_table` | 3 | Original SQL Dump | **VERIFIED** |
| 37 | `2026_10_03_000001_add_delivery_charge_to_purchases_table` | 4 | Original SQL Dump | **VERIFIED** |
| 38 | `2026_10_05_170654_add_discount_amount_to_purchases_table` | 5 | Original SQL Dump | **VERIFIED** |
| 39 | `2026_10_08_000001_create_battery_brands_table` | 6 | Original SQL Dump | **VERIFIED** |
| 40 | `2026_10_08_000002_create_battery_categories_table` | 6 | Original SQL Dump | **VERIFIED** |
| 41 | `2026_10_08_000003_create_battery_sub_categories_table` | 6 | Original SQL Dump | **VERIFIED** |
| 42 | `2026_10_08_000004_create_battery_units_table` | 6 | Original SQL Dump | **VERIFIED** |
| 43 | `2026_10_08_000005_create_battery_products_table` | 6 | Original SQL Dump | **VERIFIED** |
| 44 | `2026_10_08_000006_create_battery_suppliers_table` | 6 | Original SQL Dump | **VERIFIED** |
| 45 | `2026_10_08_000007_create_battery_purchases_table` | 6 | Original SQL Dump | **VERIFIED** |
| 46 | `2026_10_08_000008_create_battery_purchase_order_details_table` | 6 | Original SQL Dump | **VERIFIED** |
| 47 | `2026_10_08_000009_create_battery_purchase_payment_details_table` | 6 | Original SQL Dump | **VERIFIED** |
| 48 | `2026_10_08_000010_create_battery_supplier_due_collections_table` | 6 | Original SQL Dump | **VERIFIED** |
| 49 | `2026_10_08_000011_create_battery_purchase_returns_table` | 6 | Original SQL Dump | **VERIFIED** |
| 50 | `2026_10_08_000012_create_battery_customers_table` | 6 | Original SQL Dump | **VERIFIED** |
| 51 | `2026_10_08_000013_create_battery_orders_table` | 6 | Original SQL Dump | **VERIFIED** |
| 52 | `2026_10_08_000014_create_battery_order_details_table` | 6 | Original SQL Dump | **VERIFIED** |
| 53 | `2026_10_08_000015_create_battery_order_payment_details_table` | 6 | Original SQL Dump | **VERIFIED** |
| 54 | `2026_10_08_000016_create_battery_customer_payment_details_table` | 6 | Original SQL Dump | **VERIFIED** |
| 55 | `2026_10_08_000017_create_battery_product_returns_table` | 6 | Original SQL Dump | **VERIFIED** |
| 56 | `2026_10_08_000018_create_battery_expense_types_table` | 6 | Original SQL Dump | **VERIFIED** |
| 57 | `2026_10_08_000019_create_battery_expenses_table` | 6 | Original SQL Dump | **VERIFIED** |
| 58 | `2026_10_08_000020_create_battery_opening_balances_table` | 6 | Original SQL Dump | **VERIFIED** |
| 59 | `2026_10_18_000001_add_return_quantity_to_order_details_tables` | 7 | Original SQL Dump | **VERIFIED** |
| 60 | `2026_10_18_000002_add_return_number_and_order_detail_id_to_sales_returns` | 7 | Original SQL Dump | **VERIFIED** |
| 61 | `2026_10_18_000003_add_discount_fields_to_purchases_and_orders` | 7 | Original SQL Dump | **VERIFIED** |
| 62 | `2026_11_02_000001_create_battery_migration_mappings_table` | 8 | Original SQL Dump | **VERIFIED** |
| 63 | `2026_11_19_000001_create_user_activity_logs_table_if_missing` | 9 | Incremental Target Migration | **VERIFIED** |
| 64 | `2026_11_19_000002_create_investor_tables_if_missing` | 9 | Incremental Target Migration | **VERIFIED** |

---

## 4. Route Inventory Evidence (Finding F-03)

### 4.1 Resolution of 88 Documented Rows vs 89 Actual Application Routes

- **Command Executed:** `php artisan route:list --except-vendor --json` and `php artisan route:list --json`
- **Actual Application Route Count (`--except-vendor`):** **`89` routes**
  - **Shared & Auth (`routes/web.php`):** **`12` routes**
  - **Retail Module (`routes/retail.php`):** **`41` routes**
  - **Battery Module (`routes/battery.php`):** **`36` routes**
  - **Sum:** `12 + 41 + 36 = 89` application routes.
- **Framework / Livewire Vendor Routes:** **`12` routes** (`GET|HEAD /up`, `GET|HEAD /storage/{path}`, and `10` Livewire endpoints).
- **Total Registered Routes (`php artisan route:list`):** **`101` routes** (`89 + 12 = 101`).
- **Root Cause of 88 vs 89 Discrepancy:** In the earlier version of `docs/ROUTE_MAPPING.md` Section 1, row 4 combined `POST /logout` (`name: logout`) and `GET|HEAD /naxus-pos-logout` (`name: logout.legacy`) on a single Markdown table line (`11 + 41 + 36 = 88` rows). Splitting those two distinct routes into rows 4 and 5 produces the exact `12 + 41 + 36 = 89` rows with zero route additions, deletions, or renames.

### 4.2 Complete Application Route Inventory — Shared & Auth (`routes/web.php` — 12 Routes)

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

### 4.3 Complete Application Route Inventory — Retail Module (`routes/retail.php` — 41 Routes)

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

### 4.4 Complete Application Route Inventory — Battery Module (`routes/battery.php` — 36 Routes)

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

### 4.5 Complete Framework & Livewire Vendor Routes (12 Routes — Total 101 Routes)

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

## 5. `FIN-13`, `FIN-14`, `FIN-15` Code & Regression Evidence (Finding F-02)

### 5.1 `FIN-13` — Full Invoice Edit Preservation (`FIXED & VERIFIED`)

- **Audited Files:**
  - `app/Services/Retail/RetailSalesService.php` (`updateFullInvoice`, lines `260`–`400`)
  - `app/Services/Battery/BatterySalesService.php` (`updateFullInvoice`, lines `257`–`397`)
  - `app/Services/Retail/RetailReturnService.php` (`createSalesReturn`, line `53`)
  - `app/Services/Battery/BatteryReturnService.php` (`createSalesReturn`, line `53`)
- **Verified Invariants & Code Hardening Applied:**
  1. **`delivery_charge` Preserved:** `$deliveryCharge = Money::round($payload['delivery_charge'] ?? $order->delivery_charge ?? 0);`
  2. **`return_adjustment_amount` Preserved:** `$returnAdj = Money::round($order->return_adjustment_amount ?? 0);`
  3. **`return_quantity` Preserved (Hardened):** Looked up by `$existingDetails->get($detailId)` AND via fallback `$existingByProduct->get($productId)?->shift()` when caller omits `id` (`detail_id`), validating `newQty >= returnQty`.
  4. **`created_at` Preserved:** Only domain columns (`sub_total`, `delivery_charge`, `paid_amount`, `discount_amount`, `discount_type`, `discount_rate`, `due_amount`, `previous_due_amount`, `order_note`, `customer_id`, `invoice_date`) are updated; `created_at` is never overwritten.
  5. **Existing Payment History Preserved:** `order_payment_details` / `battery_order_payment_details` rows are never deleted on full edit; if `paid_amount` increases, only an incremental `Invoice Edit Adjustment` payment row is appended.
  6. **Outstanding Due Calculation & Proportional Return Discount Precision:**
     - `netPayable = max(0, sub_total + delivery_charge - discount_amount - return_adjustment_amount)`
     - `due_amount = max(0, netPayable - paid_amount)`
     - Hardened proportional invoice discount on sales return in `RetailReturnService` and `BatteryReturnService` to single-division `Money::div(Money::mul($lineAmount, $orderDiscount, 6), $subTotal, 2)` to eliminate 1-poisha intermediate ratio rounding drift (`14749.99` -> `14750.00`).
- **Dedicated Regression Tests:**
  - `Tests\Feature\Financial\RetailFinancialTest::test_fin13_retail_full_invoice_edit_preserves_delivery_return_adj_return_qty_created_at_and_multi_payment_history` (**PASS**, `9` assertions)
  - `Tests\Feature\Financial\BatteryFinancialTest::test_fin13_battery_full_invoice_edit_preserves_delivery_return_adj_return_qty_created_at_and_multi_payment_history` (**PASS**, `9` assertions)

### 5.2 `FIN-14` — Customer & Supplier Profiles Include Every Direct Payment Without Duplicate FIFO Summary (`FIXED & VERIFIED`)

- **Audited Files:**
  - `app/Services/Retail/RetailDueCollectionService.php` (`customerBalance`, `supplierBalance`)
  - `app/Services/Battery/BatteryDueCollectionService.php` (`customerBalance`, `supplierBalance`)
  - `app/Livewire/Retail/Customer/RetailCustomerProfile.php`
  - `app/Livewire/Retail/Supplier/RetailSupplierProfile.php`
  - `app/Livewire/Battery/Customer/BatteryCustomerProfile.php`
  - `app/Livewire/Battery/Supplier/BatterySupplierProfile.php`
- **Verified Invariants & Code Hardening Applied:**
  1. **Every Direct Payment Included (1st, 2nd, 3rd+):** Profile balances sum **all** rows in `order_payment_details` / `battery_order_payment_details` and `purchase_payment_details` / `battery_purchase_payment_details` (never filtering to `MIN(id)` as the legacy app did).
  2. **Zero Duplicate FIFO Summary Counting:**
     - Retail & Battery Customer balances filter `customer_payment_details` / `battery_customer_payment_details` strictly to `CAST(COALESCE(previous_due_amount, 0) AS DECIMAL(15,2)) > 0`, excluding FIFO invoice-collection summary rows (`previous_due_amount = 0`).
     - Retail Supplier balance & `RetailSupplierProfile` filter `supplier_due_collections` strictly to `CAST(COALESCE(purchase_payable_amount, 0) AS DECIMAL(15,2)) > 0`, excluding FIFO purchase-payment summary rows (`purchase_payable_amount = 0`).
     - Battery Supplier FIFO collection (`BatteryDueCollectionService::collectSupplierDue`) writes `battery_supplier_due_collections` ONLY in Step 1 (when paying previous opening payable) and writes `battery_purchase_payment_details` in Step 2 (when paying purchase invoices), never writing a duplicate summary row.
- **Dedicated Regression Tests:**
  - `Tests\Feature\Financial\RetailFinancialTest::test_fin14_retail_customer_and_supplier_profiles_include_all_direct_payments_without_fifo_summary_duplicates` (**PASS**, `4` assertions)
  - `Tests\Feature\Financial\BatteryFinancialTest::test_fin14_battery_customer_and_supplier_profiles_include_all_direct_payments_without_fifo_summary_duplicates` (**PASS**, `4` assertions)

### 5.3 `FIN-15` — Transactional Row Locking, Stock Validation, Atomic Rollback & Purchase Return Stock Validation (`FIXED & VERIFIED`)

- **Audited Files:**
  - `app/Services/Retail/RetailStockService.php` & `app/Services/Battery/BatteryStockService.php` (`lockProducts`, `adjustStock`, `deduct`, `incrementWithWac`)
  - `app/Services/Retail/RetailSalesService.php` & `app/Services/Battery/BatterySalesService.php` (`createOrder`, `updateFullInvoice`, `recordDirectPayment`, `deleteOrder`)
  - `app/Services/Retail/RetailPurchaseService.php` & `app/Services/Battery/BatteryPurchaseService.php` (`create`, `update`, `recordPayment`, `split`, `delete`)
  - `app/Services/Retail/RetailReturnService.php` & `app/Services/Battery/BatteryReturnService.php` (`createSalesReturn`, `createPurchaseReturn`)
  - `app/Services/Retail/RetailDueCollectionService.php` & `app/Services/Battery/BatteryDueCollectionService.php` (`collectCustomerDue`, `paySupplierDue`)
- **Verified Invariants:**
  1. **`DB::transaction()` Wrapping:** Every write operation across all 10 Retail and Battery mutation services runs inside `DB::transaction()`.
  2. **Deterministic Ascending-ID Row Locking:** `lockProducts()` sorts product IDs ascending (`collect($productIds)->map(fn ($id) => (int) $id)->unique()->sort()->values()->all()`) and executes `->whereIn('id', $sortedIds)->orderBy('id')->lockForUpdate()->get()->keyBy('id')` to prevent deadlocks and race conditions.
  3. **Strict Stock Availability Validation & Atomic Rollback:** `stockService->deduct($product, $qty, true)` throws `InvalidArgumentException` when `$currentStock < $qty`, rolling back all prior line mutations in the transaction.
  4. **Purchase Return Stock Validation:** Both `RetailReturnService::createPurchaseReturn()` and `BatteryReturnService::createPurchaseReturn()` lock the product row with `lockForUpdate()` and call `$this->stockService->deduct($product, $returnQty, true)` — throwing `InvalidArgumentException` if current stock is less than the purchase return quantity instead of silently skipping stock deduction.
- **Dedicated Regression Tests:**
  - `Tests\Feature\Financial\RetailFinancialTest::test_fin15_retail_purchase_return_validates_current_stock_and_rolls_back_multi_item_tx_on_failure` (**PASS**, `4` assertions)
  - `Tests\Feature\Financial\BatteryFinancialTest::test_fin15_battery_purchase_return_validates_current_stock_and_rolls_back_multi_item_tx_on_failure` (**PASS**, `4` assertions)

---

## 6. Complete Test Execution Output (Finding F-04)

See `docs/TEST_RESULTS.md` Section 2 for the complete 30-test method-by-method table (`30 tests, 278 assertions, 5.14s, 0 failures`).

```text
$ php artisan test --testdox

PHPUnit 12.5.44 by Sebastian Bergmann and contributors.
Runtime: PHP 8.3.30
Configuration: F:\Running-work\marss-corp-temp\phpunit.xml

All Modules Render (Tests\Feature\Smoke\AllModulesRender)
 ✔ All shared retail and battery routes render http 200 for admin

Authentication And Rbac (Tests\Feature\Auth\AuthenticationAndRbac)
 ✔ Guest is redirected to login page
 ✔ Pending user cannot authenticate
 ✔ Cashier login redirects to pos and enforces server side rbac
 ✔ Manager cannot access admin activity log or profit metrics
 ✔ Admin can access activity log and profit metrics
 ✔ Logout invalidates session

Battery Financial (Tests\Feature\Financial\BatteryFinancial)
 ✔ Battery pos purchase return and split work without legacy crashes
 ✔ Battery fifo and ledger reconcile without double counting
 ✔ Battery pos blocks negative stock
 ✔ Fin13 battery full invoice edit preserves delivery return adj return qty created at and multi payment history
 ✔ Fin14 battery customer and supplier profiles include all direct payments without fifo summary duplicates
 ✔ Fin15 battery purchase return validates current stock and rolls back multi item tx on failure

Cross Module Isolation (Tests\Feature\Isolation\CrossModuleIsolation)
 ✔ All twelve cross module isolation guarantees

Example (Tests\Feature\Example)
 ✔ Root redirects unauthenticated visitor to login

Example (Tests\Unit\Example)
 ✔ That true is true

Livewire End To End Workflows (Tests\Feature\Livewire\LivewireEndToEndWorkflows)
 ✔ Retail pos creates order and redirects to retail invoice print
 ✔ Battery pos creates isolated order and redirects to battery invoice print
 ✔ Battery expense type saves without nonexistent status column
 ✔ Retail product list prevents duplicate barcode

Money And Permissions (Tests\Unit\MoneyAndPermissions)
 ✔ Bcmath decimal arithmetic avoids float drift
 ✔ Bd currency formatting and taka words
 ✔ Role permissions and admin profit isolation

Retail Financial (Tests\Feature\Financial\RetailFinancial)
 ✔ Purchase weighted average cost and split invoice
 ✔ Pos sale blocks insufficient stock and calculates exact totals
 ✔ Sales return synchronizes due and purchase return never double deducts supplier
 ✔ Fifo collection has zero double counting and ledger balances with receipt payment
 ✔ Fin13 retail full invoice edit preserves delivery return adj return qty created at and multi payment history
 ✔ Fin14 retail customer and supplier profiles include all direct payments without fifo summary duplicates
 ✔ Fin15 retail purchase return validates current stock and rolls back multi item tx on failure

Time: 00:05.140, Memory: 62.00 MB

OK (30 tests, 278 assertions)
```

---

## 7. Historical Data Preservation Evidence

All 55 original tables in `marsscorp-temp-db` match the authoritative SQL dump `marsscorp-temp-db.sql` row-for-row before and after running the full 30-test regression suite:

| Historical Table | SQL Dump (`marsscorp-temp-db.sql`) | Target DB (`marsscorp-temp-db`) | Status |
|---|---:|---:|---|
| `users` | `6` | `6` | **PRESERVED** |
| `brands` | `21` | `21` | **PRESERVED** |
| `categories` | `29` | `29` | **PRESERVED** |
| `units` | `1` | `1` | **PRESERVED** |
| `products` | `406` | `406` | **PRESERVED** |
| `suppliers` | `14` | `14` | **PRESERVED** |
| `supplier_due_collections` | `3` | `3` | **PRESERVED** |
| `purchases` | `19` | `19` | **PRESERVED** |
| `purchase_order_details` | `134` | `134` | **PRESERVED** |
| `purchase_payment_details` | `20` | `20` | **PRESERVED** |
| `customers` | `104` | `104` | **PRESERVED** |
| `customer_payment_details` | `31` | `31` | **PRESERVED** |
| `orders` | `124` | `124` | **PRESERVED** |
| `order_details` | `466` | `466` | **PRESERVED** |
| `order_payment_details` | `150` | `150` | **PRESERVED** |
| `product_returns` | `9` | `9` | **PRESERVED** |
| `expense_types` | `23` | `23` | **PRESERVED** |
| `expenses` | `159` | `159` | **PRESERVED** |
| `battery_expense_types` | `1` | `1` | **PRESERVED** |
| `battery_purchase_payment_details` | `1` | `1` | **PRESERVED** |
| `personal_access_tokens` | `8` | `8` | **PRESERVED** |
| `sessions` | `7` | `7` | **PRESERVED** |
| All other 32 original domain/framework tables | `0` | `0` | **PRESERVED** |
| `migrations` | `62` (+ `2` batch-9 incremental) | `64` | **VERIFIED** |

---

## 8. Git and Reference-Project Integrity Evidence

- **Reference Project (`f:\Running-work\marss-corp-temp\marsscorp-retailshop-mgm\`):**
  - Command: `git -C f:\Running-work\marss-corp-temp\marsscorp-retailshop-mgm status --short`
  - Result: **`0` modified, added, or deleted files** (**`PRESERVED`**).
- **Authoritative SQL Dump (`f:\Running-work\marss-corp-temp\marsscorp-temp-db.sql`):**
  - Size: **`306,430` bytes** (unchanged, read-only, **`PRESERVED`**).
- **Target Project Git Repository (`f:\Running-work\marss-corp-temp\.git`):**
  - Command: `git log -n 5 --oneline`
  - Result: `eb399f4 Initial commit` preserved intact (**`PRESERVED`**).

---

## 9. Remaining Limitations or Unresolved Findings

- **Critical / High / Medium / Low `OPEN` Findings:** **`0` (`NONE`)**.
- **Historical Data Anomalies (`PRESERVED` by Mandate):**
  1. Retail Orders `#40` (`#InvID00038`), `#42` (`#InvID00040`), and `#56` (`#InvID00054`) carry historical pre-migration sales-return artifacts (`orders.paid_amount` vs `order_payment_details` and float division `501.95031847134` in `product_returns`). Per Absolute Protection Rule #5 and Section 8.5, these historical rows are **never silently rewritten** (`PRESERVED`).
  2. 44 historical Retail products carry pre-existing negative stock (`-1` to `-50`) from legacy unguarded POS sales. Historical quantities are preserved verbatim (`PRESERVED`), while all new POS sales and purchase returns enforce strict stock sufficiency validation.
  3. 1 pre-existing orphan row in `battery_purchase_payment_details` (`id=1, purchases_id=1, paid_amount=0.00`) is preserved verbatim (`PRESERVED`) and safely excluded from active ledger joins via `INNER JOIN battery_purchases`.
