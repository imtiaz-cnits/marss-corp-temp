# MARSS Corporation — Financial Reconciliation Report

**Generated / Audited:** 2026-11-19  
**Database Verified:** `marsscorp-temp-db` (MySQL 8.4.3)  
**Services Executed:**
- `App\Services\Retail\FinancialReconciliationService`
- `App\Services\Battery\FinancialReconciliationService`

---

## 1. Retail Module Reconciliation Results

| Check | Result | Details | Status |
|---|---|---|---|
| Purchases (`purchases` vs `purchase_order_details` & `purchase_payment_details`) | **19 / 19 Reconciled (100%)** | `0` discrepancies across all 19 historical purchases | **VERIFIED** |
| Orders (`orders` vs `order_details`, `order_payment_details`, `product_returns`) | **121 / 124 Reconciled** | Only 3 pre-existing historical legacy-return orders (`#40`, `#42`, `#56`) carry legacy return adjustments (preserved untouched per Section 8.5) | **PRESERVED** |
| Stock Valuation (`products`) | **406 Products / 1,958 Total Units** | Positive-stock Cost Valuation: **৳ 31,85,231.76**; 44 pre-existing negative-stock products preserved untouched | **VERIFIED** |
| Customer Receivables (`customers` + `orders`) | **104 Customers** | Total Receivable Due: **৳ 4,40,718.75** | **VERIFIED** |
| Supplier Payables (`suppliers` + `purchases`) | **14 Suppliers** | Total Payable Due: **৳ 4,02,577.40** | **VERIFIED** |
| Daily Ledger vs Daily Receipt & Payment Closing Balance | **BALANCED (`true`)** | Daily Ledger Closing: **৳ 2,42,800.35** == Receipt & Payment Closing: **৳ 2,42,800.35** (`0.00` variance) | **FIXED & VERIFIED** |

### Preserved Historical Order Artifacts (Read-Only, Never Silently Rewritten)

| Order ID | Invoice No | Historical Legacy Artifact | Audit Status |
|---:|---|---|---|
| `#40` | `#InvID00038` | Historical legacy return `#RET00001` (`৳ 8,302.00` returned with unrounded PHP float division `due_amount = 501.95031847134`). `orders.paid_amount` (`39,063.95`) differs from `SUM(order_payment_details.paid_amount)` (`40,000.00`). | **PRESERVED** |
| `#42` | `#InvID00040` | Legacy return `#1` (`৳ 5,700.00`) deducted directly from `orders.paid_amount` (`51,300.00 -> 45,600.00`) before `return_adjustment_amount` column existed. | **PRESERVED** |
| `#56` | `#InvID00054` | Legacy return `#10` (`৳ 260.00`) deducted directly from `orders.paid_amount` (`1,740.00 -> 1,480.00`). | **PRESERVED** |

---

## 2. Battery Module Reconciliation Results (100% Isolated)

| Check | Result | Details | Status |
|---|---|---|---|
| Battery Purchases (`battery_purchases`) | **0 / 0 Reconciled (100%)** | `0` discrepancies | **VERIFIED** |
| Battery Orders (`battery_orders`) | **0 / 0 Reconciled (100%)** | `0` discrepancies | **VERIFIED** |
| Battery Stock (`battery_products`) | **0 Products / 0 Units** | Cost Valuation: **৳ 0.00** | **VERIFIED** |
| Battery Receivables (`battery_customers` + `battery_orders`) | **0 Customers** | Total Receivable: **৳ 0.00** | **VERIFIED** |
| Battery Payables (`battery_suppliers` + `battery_purchases`) | **0 Suppliers** | Total Payable: **৳ 0.00** | **VERIFIED** |
| Battery Daily Ledger vs Receipt & Payment Closing Balance | **BALANCED (`true`)** | Daily Ledger Closing: **৳ 0.00** == Receipt & Payment Closing: **৳ 0.00** | **FIXED & VERIFIED** |
| Pre-Existing Orphan Guard (`battery_purchase_payment_details`) | **1 Orphan Row Handled** | Orphan row `#1` (`purchases_id=1`, `paid_amount=0.00`) preserved untouched and cleanly excluded via `INNER JOIN battery_purchases`. | **PRESERVED** |

---

## 3. Verified Calculation Fixes Covered by Automated Regression Tests (`FIN-01` through `FIN-15`)

1. **Zero 2x Double-Counting (`FIN-01`, `FIN-02` — `FIXED & VERIFIED`):** FIFO due collections against Retail/Battery invoices & purchases are counted **exactly once** in Dashboard Cash Collection, Supplier Payment, Daily Ledger, and Daily Receipt & Payment.
2. **Sales Return Due Synchronization & Exact Proportional Discount (`FIN-03`, `FIN-12` — `FIXED & VERIFIED`):** New sales returns update `orders.return_adjustment_amount` AND reduce `orders.due_amount` using single-division `Money::div(Money::mul($lineAmount, $orderDiscount, 6), $subTotal, 2)` with zero 1-poisha intermediate rounding drift.
3. **Purchase Return Non-Double-Deduction (`FIN-04` — `FIXED & VERIFIED`):** New purchase returns reduce `purchases.due_amount` & increase `purchases.return_adjustment_amount` WITHOUT double-deducting `suppliers.purchase_payable_amount`.
4. **Daily Ledger Completeness (`FIN-05`, `FIN-07` — `FIXED & VERIFIED`):** Daily Ledger includes Opening Balance roll-forward + Purchase Cash Outflows + Previous-Due Collections and matches Daily Receipt & Payment Closing Balance to `৳ 0.00` variance.
5. **Sales & Best-Selling Quantity Multiplication (`FIN-06` — `FIXED & VERIFIED`):** Unit `price` and `selling_price` are multiplied by net quantity `(quantity - return_quantity)`.
6. **Full Invoice Edit Preservation (`FIN-13` — `FIXED & VERIFIED`):** `RetailSalesService::updateFullInvoice` and `BatterySalesService::updateFullInvoice` preserve `delivery_charge`, `return_adjustment_amount`, `return_quantity` (even when `detail_id` is omitted), `created_at`, and all existing payment history rows while computing exact `due_amount`.
7. **Complete Direct Payment Ledger in Customer & Supplier Profiles (`FIN-14` — `FIXED & VERIFIED`):** `RetailDueCollectionService`, `BatteryDueCollectionService`, `RetailCustomerProfile`, `RetailSupplierProfile`, `BatteryCustomerProfile`, and `BatterySupplierProfile` include 1st, 2nd, and 3rd+ direct payments plus FIFO allocations while filtering out duplicate FIFO summary rows (`previous_due_amount = 0` / `purchase_payable_amount = 0`).
8. **Transactional Locking, Atomic Rollback & Purchase Return Stock Validation (`FIN-15` — `FIXED & VERIFIED`):** All Retail & Battery write services lock products in deterministic ascending ID order inside `DB::transaction()`, block insufficient stock on POS sales and purchase returns, and roll back multi-item transactions atomically on failure.
9. **12 Mandatory Cross-Module Isolation Guarantees (`FIN-10` — `FIXED & VERIFIED`):** Verified via `Tests\Feature\Isolation\CrossModuleIsolationTest`.
