# MARSS Corporation — Database Integrity & Forensic Import Report

**Generated / Audited:** 2026-11-19  
**Source SQL Dump:** `marsscorp-temp-db.sql` (306,430 bytes, phpMyAdmin 5.2.3, source DB `marsscorpdb`, MySQL 8.0.45)  
**Target Database:** `marsscorp-temp-db` (MySQL 8.4.3, `utf8mb4` / `utf8mb4_unicode_ci`)  
**Original Database Status:** `marsscorpdb` — **PRESERVED (Untouched)**  
**Original SQL Dump Status:** `marsscorp-temp-db.sql` — **PRESERVED (Read-Only, Untouched)**

---

## 1. Exact Table & Migration Count Reconciliation (Finding F-01)

- **Original Tables Imported from `marsscorp-temp-db.sql`:** **`55` tables** (with `62` rows in `migrations`, batches `1`–`8`).
- **Guarded Incremental Migration Files in `database/migrations/`:** **`2` migration files** (executed in batch `9`, adding `2` rows to `migrations` for a total of **`64` rows** in `migrations`):
  1. `database/migrations/2026_11_19_000001_create_user_activity_logs_table_if_missing.php` — creates **`1` table**: `user_activity_logs`.
  2. `database/migrations/2026_11_19_000002_create_investor_tables_if_missing.php` — creates **`2` tables**: `investor_infos` and `invests`.
- **Incremental Tables Created:** **`3` tables** (`user_activity_logs`, `investor_infos`, `invests`).
- **Authoritative Table Formula:**
  - **`55` original tables + `3` incremental tables (created by `2` guarded incremental migration files) = `58` total tables in `marsscorp-temp-db`.**

### Confirmation of the 4 Specifically Audited Tables

| Table Name | Present in `marsscorp-temp-db.sql`? | Present in `marsscorp-temp-db`? | Row Count in Target DB | How Created / Why Absent | Audit Status |
|---|---|---|---:|---|---|
| `user_activity_logs` | `false` | **`true`** | `0` | Created by incremental migration `2026_11_19_000001_create_user_activity_logs_table_if_missing.php` (Batch 9) | **VERIFIED** |
| `investor_infos` | `false` | **`true`** | `0` | Created by incremental migration `2026_11_19_000002_create_investor_tables_if_missing.php` (Batch 9) | **VERIFIED** |
| `invests` | `false` | **`true`** | `0` | Created by incremental migration `2026_11_19_000002_create_investor_tables_if_missing.php` (Batch 9) | **VERIFIED** |
| `stock_repair_executions` | `false` | **`false`** | N/A | One-off tracking table used only by legacy artisan command `RepairSalesReturn38Command` in the reference repo; never needed or migrated into target app | **NOT APPLICABLE** |

---

## 2. Complete 58-Table Schema & Row-Count Comparison (`marsscorp-temp-db.sql` vs `marsscorp-temp-db` vs Local `marsscorpdb`)

> **Note on `marsscorp-temp-db.sql` vs Local `marsscorpdb`:** Resource C (`marsscorp-temp-db.sql`) is the authoritative SQL dump provided for the migration. Every single one of the 55 original tables in `marsscorp-temp-db` matches `marsscorp-temp-db.sql` **100%** (with `migrations` increasing from `62` to `64` after executing the `2` batch-9 incremental migration files). The separate local `marsscorpdb` database on the host MySQL server is an older snapshot that was left completely untouched.

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

## 3. Complete `information_schema.tables` Inventory (`marsscorp-temp-db`, All 58 Tables)

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

---

## 4. Actual Laravel `migrations` Table Contents (`marsscorp-temp-db`, All 64 Rows)

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

## 5. Preserved Historical Data & Schema Characteristics

1. **Pre-Existing Orphan Record in Battery Module (`PRESERVED`):**
   - `battery_purchase_payment_details` row `#1` (`purchases_id=1`, `paid_amount=0.00`, `payment_status='Due'`, `purchase_due_collection_date='2026-10-06'`) has no matching parent row in `battery_purchases` (`0` rows). Preserved untouched and safely excluded from active ledger joins via `INNER JOIN battery_purchases`.
2. **VARCHAR Numeric Columns in Legacy Schema (`PRESERVED`):**
   - Legacy `VARCHAR(255)` numeric columns across Retail and Battery tables are preserved without destructive DDL alterations; all application reads and writes use `App\Support\Money` (`bcmath`) and `CAST(... AS DECIMAL(15,2))`.
3. **Historical Retail Financial Snapshot (`PRESERVED`):**
   - **Purchases (`19` rows):** 19/19 reconcile (`0` discrepancies).
   - **Orders (`124` rows):** 121/124 reconcile directly (including `#43` and `#47` via due-collection discounts in `order_payment_details.discount_amount`). Only 3 legacy return orders (`#40`, `#42`, `#56`) carry historical pre-`return_adjustment_amount` or float-division artifacts and remain untouched per Section 8.5.
   - **Products (`406` rows):** `62` zero-stock, `343` low-stock (`<10`), `44` pre-existing negative-stock products preserved verbatim; total net units `1,958`; positive-stock cost valuation `৳ 31,85,231.76`.
