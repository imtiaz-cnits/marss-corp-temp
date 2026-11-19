# MARSS Corporation — Reference Project Forensic Audit

**Audit Date:** 2026-11-19  
**Reference Directory (Read-Only):** `f:\Running-work\marss-corp-temp\marsscorp-retailshop-mgm`  
**Target Directory:** `f:\Running-work\marss-corp-temp`  
**Reference Stack:** PHP 8.2+ / Laravel 12 (`^12.0`) + Sanctum `^4.0` + Bootstrap 5 + jQuery 3.6 + Axios + Select2 + Flatpickr + Vanilla Datepicker + ApexCharts + SweetAlert2 + Toastify + Html5Qrcode + Tailwind CSS v3.4  
**Target Stack:** PHP 8.3.30 / Laravel 13.43.0 + Livewire 4.4.8 (with bundled Alpine.js & `wire:navigate`) + Tailwind CSS 4 + Vite 8 + MySQL 8.4.3 (`marsscorp-temp-db`)

---

## 1. Executive Architecture Summary

The reference MARSS Corporation Management System operates two parallel retail business lines inside one Laravel application:
1. **Retail Shop Module** (`/admin-dashboard*`, `/api/*`) — Hardware, sanitary, pipes, doors (with Left/Right/Both handedness variants), appliances, suppliers, purchases, POS, invoices, returns, expenses, staff salaries, opening balances, and reports.
2. **Battery Module** (`/battery/*`, `/api/battery/*`) — Dedicated battery inventory, suppliers, purchases, POS, invoices, returns, expenses, opening balances, and reports, isolated on 20 dedicated `battery_*` tables.

### Legacy Architecture Deficiencies Found
- **SPA-via-jQuery Anti-Pattern:** Every Blade view renders an empty HTML skeleton with no server-side auth middleware, then executes client-side `localStorage.getItem('token')` redirects and fires multiple Axios calls to `/api/*` using Sanctum bearer tokens (never establishing a Laravel `web` session).
- **Zero Backend RBAC Enforcement:** `AdminOnly` middleware is dead code (applied to zero routes). All 33 permission keys are checked client-side via injected CSS in `dashboard-sidenav.blade.php`; only 11 parent keys are actually enforced even client-side.
- **Critical Security Exposure:** Public unauthenticated admin self-registration (`POST /user-registration` accepting `role="admin"`, `status="approved"`), unauthenticated `/get-all-users`, `/create-user-admin`, `/update-user-role-status`, `/delete-user-admin`, unauthenticated reports/profiles/receipts, unvalidated file uploads into `public/uploads/`, and broken public `/verify-otp`.
- **Battery Crashes & Cross-Module Leak:** Missing `Schema` import in `BatteryOrderController` (guaranteed HTTP 500 on every Battery POS order), post-commit undefined variables in `BatteryPurchaseController::PurchasesDelete` and `PurchaseSplitInvoice`, non-existent `status` column update in `BatteryExpenseTypeController`, and `BatteryReportController::PersonalInvestorTransactionReport` directly querying Retail `App\Models\Invest`.
- **Major Financial Inconsistencies:** 2x double-counting of collected customer/supplier dues on Dashboards & Daily Ledgers, unreduced `orders.due_amount` after sales returns, 2x double-deduction of supplier payables on purchase returns, missing `* quantity` in Sales & Best-Selling reports, missing `purchase_payment_details` in Daily Ledger, and floating-point precision drift.

---

## 2. Backend File & Dependency Audit

| Layer | Retail / Shared Count | Battery Count | Notes |
|---|---:|---:|---|
| Controllers | 28 (+ base `Controller`) | 20 | `PurchaseOrderDetailsController` is completely empty |
| Models | 29 | 20 | `BuyerInfo` unused/no table; `Invest`/`InvestorInfo` have no tables in dump |
| Services | 3 | 0 (shares 2) | `ActivityLogger`, `DashboardDateFilterService`, `ShahinAlomStockRepairService` |
| Console Commands | 2 (+ `routes/console.php`) | 0 | One-off historical migration/repair commands |
| Middleware | 1 (`AdminOnly`) | 0 | Dead/unused |
| Route Files | `web.php`, `api.php`, `console.php` | `battery.php`, `battery_api.php` | 3 broken routes in `web.php` pointing to missing controller methods; 3 `Route::view` pointing to missing views |
| Migrations | 42 | 22 | 62 executed in SQL dump; 2 newer (`stock_repair_executions`, `user_activity_logs`) not yet in dump |

---

## 3. Target Re-Engineering Principles

1. **Pure Laravel Session Auth + Server-Side RBAC:** Authenticate via Laravel's native `web` guard (`Auth::login`), enforce `users.status === 'approved'`, protect every route with `auth` middleware + Laravel Gates/Policies covering all roles (`admin`, `super_admin`, `manager`, `staff`, `cashier`, `accountant`) and all granular permission keys. Preserve legacy admin email aliases (`admin@anisstore.com`, `admin@marsscorporation.com` -> `admin@gmail.com`).
2. **Strict Retail / Battery Isolation:** Separate `App\Models\{Retail,Battery,Shared}`, `App\Services\{Retail,Battery,Shared}`, `App\Livewire\{Retail,Battery,Shared}`, and `routes/{web,retail,battery}.php`. Zero cross-module model queries.
3. **Decimal-Safe Atomic Financial Services:** All monetary arithmetic uses `bcmath` via `App\Support\Money`, wrapped in `DB::transaction()` with deterministic ascending-ID `lockForUpdate()`. Historical records remain 100% preserved.
4. **Pure TALL Stack (Zero Bootstrap / Zero jQuery):** Livewire 4 full-page components with `wire:navigate`, `@persist` sidebar/topbar shell, component-level skeleton placeholders, Alpine.js interactions, Tailwind CSS 4 utility design preserving MARSS Corporation's exact emerald-teal visual identity (`Valley Sans` + `Baloo Da 2`).
