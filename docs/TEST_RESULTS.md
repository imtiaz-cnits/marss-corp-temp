# MARSS Corporation — Complete Automated Test & Regression Evidence (Finding F-04)

**Test Runner:** PHPUnit 12.5.44 (`php artisan test --testdox`)  
**Framework Stack:** Laravel 13.43.0 + Livewire 4.4.8 + Tailwind CSS 4 + Vite 8.3.8  
**Database Engine:** MySQL 8.4.3 (`marsscorp-temp-db`, wrapped in `DatabaseTransactions` — **0 historical records mutated**)  
**Final Suite Result:** **`30 / 30 Tests Passed (278 Assertions, 5.05s, 0 Failures, 0 Errors)`**

---

## 1. Suite Summary by Test Class

| # | Suite Type | Test Class | File Path | Tests | Assertions | Status |
|---:|---|---|---|---:|---:|---|
| 1 | Unit | `Tests\Unit\ExampleTest` | `tests/Unit/ExampleTest.php` | `1` | `1` | **VERIFIED (`PASS`)** |
| 2 | Unit | `Tests\Unit\MoneyAndPermissionsTest` | `tests/Unit/MoneyAndPermissionsTest.php` | `3` | `23` | **VERIFIED (`PASS`)** |
| 3 | Feature | `Tests\Feature\Auth\AuthenticationAndRbacTest` | `tests/Feature/Auth/AuthenticationAndRbacTest.php` | `6` | `32` | **VERIFIED (`PASS`)** |
| 4 | Feature | `Tests\Feature\ExampleTest` | `tests/Feature/ExampleTest.php` | `1` | `1` | **VERIFIED (`PASS`)** |
| 5 | Feature | `Tests\Feature\Financial\BatteryFinancialTest` | `tests/Feature/Financial/BatteryFinancialTest.php` | `6` | `32` | **VERIFIED (`PASS`)** |
| 6 | Feature | `Tests\Feature\Financial\RetailFinancialTest` | `tests/Feature/Financial/RetailFinancialTest.php` | `7` | `45` | **VERIFIED (`PASS`)** |
| 7 | Feature | `Tests\Feature\Isolation\CrossModuleIsolationTest` | `tests/Feature/Isolation/CrossModuleIsolationTest.php` | `1` | `30` | **VERIFIED (`PASS`)** |
| 8 | Feature | `Tests\Feature\Livewire\LivewireEndToEndWorkflowsTest` | `tests/Feature/Livewire/LivewireEndToEndWorkflowsTest.php` | `4` | `21` | **VERIFIED (`PASS`)** |
| 9 | Feature | `Tests\Feature\Smoke\AllModulesRenderTest` | `tests/Feature/Smoke/AllModulesRenderTest.php` | `1` | `93` | **VERIFIED (`PASS`)** |
| **Total** | **Unit + Feature** | **9 Test Classes** | `tests/` | **`30`** | **`278`** | **VERIFIED (`100% PASS`)** |

---

## 2. Method-by-Method Test Execution Matrix (All 30 Tests / 278 Assertions)

| # | Test Class | Test Method | Input / Setup | Expected Result | Actual Result | Assertions | Status |
|---:|---|---|---|---|---|---:|---|
| 1 | `Tests\Unit\ExampleTest` | `test_that_true_is_true` | Boolean `true` sanity check | `assertTrue(true)` succeeds | `true === true` | `1` | **PASS** |
| 2 | `Tests\Unit\MoneyAndPermissionsTest` | `test_bcmath_decimal_arithmetic_avoids_float_drift` | `Money::add('0.1', '0.2')`, `Money::sub('20.00', '0.01')`, `Money::mul('78.00', 20)`, `Money::percent('250.00', '5')`, `Money::div('100', '3')`, `Money::maxZero('-50.25')` | Exact 2-decimal `bcmath` strings (`0.30`, `19.99`, `1560.00`, `12.50`, `33.33`, `0.00`) | All 6 `bcmath` arithmetic assertions matched | `6` | **PASS** |
| 3 | `Tests\Unit\MoneyAndPermissionsTest` | `test_bd_currency_formatting_and_taka_words` | `Money::formatBd('24800')`, `Money::formatBd('124800.5')`, `Money::formatBd('3185231.76')`, `Money::toWords('124800')`, `Money::toWords('100.50')` | Bangladeshi formatted currency (`৳ 24,800.00`, `৳ 1,24,800.50`, `৳ 31,85,231.76`) and Taka/Poisha words | All 5 formatting assertions matched | `5` | **PASS** |
| 4 | `Tests\Unit\MoneyAndPermissionsTest` | `test_role_permissions_and_admin_profit_isolation` | `User` instances with roles `admin`, `manager`, `cashier`, `accountant` tested against `PermissionRegistry::isAdmin`, `canViewProfit`, and `allows` | Admin has profit & `user` access; Manager has `product` but no `profit`/`user`; Cashier has `pos_terminal`/`invoice_list` only; Accountant has `report`/`expense` only | All 12 RBAC permission matrix checks matched | `12` | **PASS** |
| 5 | `Tests\Feature\Auth\AuthenticationAndRbacTest` | `test_guest_is_redirected_to_login_page` | Unauthenticated `GET /admin-dashboard`, `GET /battery/dashboard`, and `GET /admin-login-page` | Protected routes redirect (`302`) to `/admin-login-page`; login page renders (`200`) with `MARSS` | Redirects and login view verified | `4` | **PASS** |
| 6 | `Tests\Feature\Auth\AuthenticationAndRbacTest` | `test_pending_user_cannot_authenticate` | Pending user (`status='pending', role='staff'`) attempting login via `LoginPage::authenticate` | Rejects login with pending approval error message and keeps session unauthenticated (`assertGuest`) | Error message set and guest state preserved | `2` | **PASS** |
| 7 | `Tests\Feature\Auth\AuthenticationAndRbacTest` | `test_cashier_login_redirects_to_pos_and_enforces_server_side_rbac` | Approved Cashier user (`role='cashier'`) authenticating via `LoginPage` and requesting allowed (`retail.pos`, `retail.invoices`, `settings.profile`) vs restricted (`settings.users`, `settings.activity-log`, `retail.purchases`, `battery.purchases`, `retail.reports.sales`, `battery.reports.sales`) routes | Login redirects to `retail.pos`; 3 allowed routes return HTTP `200`; 6 restricted routes return HTTP `403 Forbidden` | All 10 redirect, `200`, and `403` assertions passed | `10` | **PASS** |
| 8 | `Tests\Feature\Auth\AuthenticationAndRbacTest` | `test_manager_cannot_access_admin_activity_log_or_profit_metrics` | Approved Manager user (`role='manager'`) checking `canViewProfit()` and requesting `retail.dashboard`, `retail.purchases`, `battery.dashboard`, `settings.activity-log`, `settings.users` | `canViewProfit() === false`, `Net Profit (Admin)` hidden on dashboard, `200` on allowed pages, `403` on Admin Activity Log & User Role Management | All 7 Manager RBAC assertions passed | `7` | **PASS** |
| 9 | `Tests\Feature\Auth\AuthenticationAndRbacTest` | `test_admin_can_access_activity_log_and_profit_metrics` | Existing approved Admin (`admin@gmail.com`) checking `canViewProfit()` and requesting `retail.dashboard`, `settings.activity-log`, `settings.users` | `canViewProfit() === true`, sees `Net Profit (Admin)` on dashboard, and receives `200` on Activity Log & User Role Management | All 6 Admin assertions passed | `6` | **PASS** |
| 10 | `Tests\Feature\Auth\AuthenticationAndRbacTest` | `test_logout_invalidates_session` | Authenticated Admin sending `POST /logout` | Redirects to `login` (`/admin-login-page`) and invalidates session (`assertGuest`) | Redirect and guest state verified | `3` | **PASS** |
| 11 | `Tests\Feature\ExampleTest` | `test_root_redirects_unauthenticated_visitor_to_login` | Unauthenticated `GET /` root URL | HTTP `302` redirect to `/admin-login-page` | Returned HTTP `302` | `1` | **PASS** |
| 12 | `Tests\Feature\Financial\BatteryFinancialTest` | `test_battery_pos_purchase_return_and_split_work_without_legacy_crashes` | Battery Purchase (`5 × 14000`, paid `30000`, due `40000`), Battery POS Sale (`2 × 15000`, paid `20000`, due `10000` — fixing `FIN-08`/`FIN-09`), Sales Return (`1` unit -> due drops `10000 -> 0.00`, `return_adjustment_amount = 10000.00` — `FIN-03`), Purchase Return (`1` unit `14000` -> purchase due drops `40000 -> 26000.00`, supplier payable stays `2000.00` — `FIN-04`) | Exact WAC `12666.67`, `#BAT-INV*` order creation, `due_amount=0.00` after sales return, and zero 2× supplier deduction after purchase return | All 12 Battery financial lifecycle assertions passed | `12` | **PASS** |
| 13 | `Tests\Feature\Financial\BatteryFinancialTest` | `test_battery_fifo_and_ledger_reconcile_without_double_counting` | Battery Opening Balance `5000.00`, POS Sale (`15000`, paid `5000`), FIFO Customer Due Collection (`3000` cash -> `1000` prev due + `2000` order due), Supplier Payment (`1500`), Battery Expense (`500`) | Daily Ledger `total_inflow = 8000.00`, `total_outflow = 2000.00`, `closing_balance = 11000.00 === Daily Receipt & Payment closing_balance` (zero double-counting `FIN-01`/`FIN-05`/`FIN-07`) | All 4 Battery ledger reconciliation assertions passed | `4` | **PASS** |
| 14 | `Tests\Feature\Financial\BatteryFinancialTest` | `test_battery_pos_blocks_negative_stock` | Battery product with `quantity=10`; POS attempt for `quantity=50` | Throws `InvalidArgumentException` blocking negative stock | Exception thrown as expected | `1` | **PASS** |
| 15 | `Tests\Feature\Financial\BatteryFinancialTest` | `test_fin13_battery_full_invoice_edit_preserves_delivery_return_adj_return_qty_created_at_and_multi_payment_history` | **`FIN-13` Battery Regression:** Create Battery POS order (`4 × 15000 = 60000`, delivery `500`, discount `1000`, paid `10000`, due `49500`), add 2nd direct payment (`9000` cash + `500` discount), return `1` unit (`return_adjustment_amount = 14750.00`, `due_amount = 25250.00`), then run `updateFullInvoice` increasing qty `4 -> 5` with **omitted `id` (`detail_id`)** and omitted `delivery_charge` | Preserves `delivery_charge=500.00`, `return_adjustment_amount=14750.00`, `return_quantity=1` (via `product_id` fallback), original `created_at`, all `2` payment history rows (`paid=19000.00`), and exact `due_amount=40250.00` | All 9 `FIN-13` Battery assertions matched to the poisha | `9` | **PASS** |
| 16 | `Tests\Feature\Financial\BatteryFinancialTest` | `test_fin14_battery_customer_and_supplier_profiles_include_all_direct_payments_without_fifo_summary_duplicates` | **`FIN-14` Battery Regression:** Customer (`1000` opening due + order `30000` with 1st payment `5000`, 2nd payment `4000`, 3rd payment `6000`, plus FIFO collection `6000`); Supplier (`2000` opening payable + purchase `24000` with 1st payment `4000`, 2nd payment `3000`, 3rd payment `2000`, plus FIFO payment `7000`) | Customer `total_paid = 21000.00` (all 3 direct payments + FIFO, zero duplicate summary), `total_due = 10000.00`; Supplier `total_paid = 16000.00`, `total_due = 10000.00` | All 4 `FIN-14` Battery balance assertions matched | `4` | **PASS** |
| 17 | `Tests\Feature\Financial\BatteryFinancialTest` | `test_fin15_battery_purchase_return_validates_current_stock_and_rolls_back_multi_item_tx_on_failure` | **`FIN-15` Battery Regression:** Multi-item Battery POS sale with descending product IDs `[productB (qty=1, req=3), productA (qty=10, req=2)]`; followed by Purchase (`3` units -> stock `4`), POS sale consuming `3` units (`1` left), and Purchase Return attempting to return `2` units | Failed multi-item POS sale rolls back both products (`10` and `1`); Purchase Return of `2` units when current stock is `1` throws `InvalidArgumentException` | Atomic rollback and purchase return stock validation verified | `4` | **PASS** |
| 18 | `Tests\Feature\Financial\RetailFinancialTest` | `test_purchase_weighted_average_cost_and_split_invoice` | Existing `10` units @ `100.00` + Purchase `10` units @ `200.00`, delivery `50.00`, `10%` discount (`200.00`), initial paid `1000.00`, followed by 2nd direct payment `350.00` + `50.00` discount | Updates stock to `20`, WAC to `150.00`, `grand_subtotal=2000.00`, `discount_amount=200.00`, `due_amount=850.00 -> 450.00`, `paid_amount=1350.00` | All 7 WAC & multi-payment purchase assertions passed | `7` | **PASS** |
| 19 | `Tests\Feature\Financial\RetailFinancialTest` | `test_pos_sale_blocks_insufficient_stock_and_calculates_exact_totals` | Retail product with `quantity=10`; POS attempt for `quantity=11` | Throws `InvalidArgumentException` under `lockForUpdate()` | Exception thrown as expected | `1` | **PASS** |
| 20 | `Tests\Feature\Financial\RetailFinancialTest` | `test_sales_return_synchronizes_due_and_purchase_return_never_double_deducts_supplier` | Retail POS sale (`4 × 150 = 600`, discount `60`, paid `200`, due `340`) + Sales Return of `2` units (`270` credit); Retail Purchase (`5 × 100 = 500`, paid `100`, due `400`, supplier payable `500`) + Purchase Return of `2` units (`200`) | Sales return restores stock `6 -> 8`, sets `return_adjustment_amount=270.00`, drops `orders.due_amount` `340.00 -> 70.00` (`FIN-03`); Purchase return drops `purchases.due_amount` `400.00 -> 200.00` while `supplier.purchase_payable_amount` stays `500.00` (`FIN-04`) | All 8 `FIN-03` & `FIN-04` assertions passed | `8` | **PASS** |
| 21 | `Tests\Feature\Financial\RetailFinancialTest` | `test_fifo_collection_has_zero_double_counting_and_ledger_balances_with_receipt_payment` | Opening Balance `1000.00`, POS Sale (`2 × 150 = 300`, paid `100`, due `200`), FIFO Customer Due Collection (`450` cash -> `300` prev due + `150` invoice due), Expense (`75.00`) | FIFO `applied_previous=300.00, applied_invoices=150.00, remaining_total_due=50.00`; Daily Ledger `total_inflow=550.00` (not `700.00`), `total_outflow=75.00`, `closing_balance=1475.00 === Receipt & Payment closing_balance`; Sales Report multiplies by qty (`300.00` / `200.00` — `FIN-06`) | All 9 `FIN-01`, `FIN-05`, `FIN-06`, `FIN-07` assertions passed | `9` | **PASS** |
| 22 | `Tests\Feature\Financial\RetailFinancialTest` | `test_fin13_retail_full_invoice_edit_preserves_delivery_return_adj_return_qty_created_at_and_multi_payment_history` | **`FIN-13` Retail Regression:** Create Retail POS order (`5 × 150 = 750`, delivery `80`, discount `30`, initial paid `200`, due `600`), add 2nd direct payment (`150` cash + `10` discount), return `1` unit (`return_adjustment_amount = 144.00`, `due_amount = 296.00`), then run `updateFullInvoice` increasing qty `5 -> 6` with **omitted `id` (`detail_id`)** and omitted `delivery_charge` | Preserves `delivery_charge=80.00`, `return_adjustment_amount=144.00`, `return_quantity=1` (via `product_id` fallback), original `created_at`, all `2` payment rows (`paid=350.00`), and exact `due_amount=446.00` | All 9 `FIN-13` Retail assertions matched to the poisha | `9` | **PASS** |
| 23 | `Tests\Feature\Financial\RetailFinancialTest` | `test_fin14_retail_customer_and_supplier_profiles_include_all_direct_payments_without_fifo_summary_duplicates` | **`FIN-14` Retail Regression:** Customer (`300` opening due + order `600` with 1st payment `100`, 2nd payment `120`, 3rd payment `80`, plus FIFO collection `450`); Supplier (`500` opening payable + purchase `500` with 1st payment `100`, 2nd payment `90`, 3rd payment `60`, plus FIFO payment `600`) | Customer `total_paid = 750.00` (all 3 direct payments + FIFO, zero duplicate summary), `total_due = 150.00`; Supplier `total_paid = 850.00`, `total_due = 150.00` | All 4 `FIN-14` Retail balance assertions matched | `4` | **PASS** |
| 24 | `Tests\Feature\Financial\RetailFinancialTest` | `test_fin15_retail_purchase_return_validates_current_stock_and_rolls_back_multi_item_tx_on_failure` | **`FIN-15` Retail Regression:** Multi-item Retail POS sale with descending product IDs `[productB (qty=2, req=5), productA (qty=10, req=3)]`; followed by Purchase (`4` units -> stock `6`), POS sale consuming `5` units (`1` left), and Purchase Return attempting to return `3` units | Failed multi-item POS sale rolls back both products (`10` and `2`); Purchase Return of `3` units when current stock is `1` throws `InvalidArgumentException` | Atomic rollback and purchase return stock validation verified | `4` | **PASS** |
| 25 | `Tests\Feature\Isolation\CrossModuleIsolationTest` | `test_all_twelve_cross_module_isolation_guarantees` | Execute full 12-point Retail & Battery lifecycle (Purchase, POS Sale, Sales Return, Customer Due Collection, Personal Expense, Retail Investor/Invest, Daily Ledger) and verify zero cross-module contamination | **0** Battery rows or report figures change during Retail mutations; **0** Retail rows or report figures change during Battery mutations; Battery Personal Transaction Report has **0** Retail Investment leak (`FIN-10`) | All 30 cross-module isolation assertions passed | `30` | **PASS** |
| 26 | `Tests\Feature\Livewire\LivewireEndToEndWorkflowsTest` | `test_retail_pos_creates_order_and_redirects_to_retail_invoice_print` | Livewire `RetailPosTerminal`: `addToCart`, `incrementQty`, `completeSale` (`paid_amount=1000`) | Creates Retail order (`sub_total=1500.00, due_amount=500.00`), decrements stock `15 -> 13`, leaves `BatteryOrder::count()` unchanged, redirects to `retail.invoice.show` | All 6 Livewire Retail POS assertions passed | `6` | **PASS** |
| 27 | `Tests\Feature\Livewire\LivewireEndToEndWorkflowsTest` | `test_battery_pos_creates_isolated_order_and_redirects_to_battery_invoice_print` | Livewire `BatteryPosTerminal`: `addToCart`, `completeSale` (`paid_amount=9500`) + `GET /battery/invoice/{id}` | Creates Battery order, decrements Battery stock `10 -> 9`, leaves `RetailOrder::count()` unchanged, redirects strictly to `battery.invoice.show` (fixing legacy redirect bug), renders print view `200` | All 7 Livewire Battery POS & print assertions passed | `7` | **PASS** |
| 28 | `Tests\Feature\Livewire\LivewireEndToEndWorkflowsTest` | `test_battery_expense_type_saves_without_nonexistent_status_column` | Livewire `BatteryExpenseTypeList`: `openCreate`, `set('type_name', 'Battery Transport Test')`, `call('save')` | Saves cleanly without querying non-existent `battery_expense_types.status` column | Saved and verified in `battery_expense_types` | `3` | **PASS** |
| 29 | `Tests\Feature\Livewire\LivewireEndToEndWorkflowsTest` | `test_retail_product_list_prevents_duplicate_barcode` | Livewire `RetailProductList`: attempt to create product with existing barcode `DUP-CODE-777` | Fails validation on `product_code` (`assertHasErrors(['product_code'])`) | Duplicate barcode blocked | `2` | **PASS** |
| 30 | `Tests\Feature\Smoke\AllModulesRenderTest` | `test_all_shared_retail_and_battery_routes_render_http_200_for_admin` | Authenticated Admin visiting all 67 static Shared (`7`), Retail (`32`), and Battery (`28`) GET page routes + 7 dynamic Retail profile/print routes against live historical data + 8 dynamic Retail Purchase Return & Battery Customer/Supplier Profile, Invoice, Due Invoice, Purchase Invoice, Sales Return, and Purchase Return print routes via synthetic rolled-back fixtures | Every single route returns HTTP `200 OK` (`93` assertions across all `82` GET page/print routes, `0` 500/404 errors) | All 93 HTTP `200 OK` & content assertions passed | `93` | **PASS** |

---

## 3. Verbatim PHPUnit Execution Output

```text
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

## 4. Post-Test Historical Database Preservation Verification

All feature tests run inside `DatabaseTransactions` rollbacks. Post-suite verification confirmed 100% of historical `marsscorp-temp-db` row counts remain identical to `marsscorp-temp-db.sql`:

- `users`: **`6`** (`VERIFIED`)
- `products`: **`406`** (`VERIFIED`)
- `orders`: **`124`** (`VERIFIED`)
- `order_details`: **`466`** (`VERIFIED`)
- `order_payment_details`: **`150`** (`VERIFIED`)
- `purchases`: **`19`** (`VERIFIED`)
- `purchase_order_details`: **`134`** (`VERIFIED`)
- `purchase_payment_details`: **`20`** (`VERIFIED`)
- `customers`: **`104`** (`VERIFIED`)
- `customer_payment_details`: **`31`** (`VERIFIED`)
- `suppliers`: **`14`** (`VERIFIED`)
- `supplier_due_collections`: **`3`** (`VERIFIED`)
- `expenses`: **`159`** (`VERIFIED`)
- `product_returns`: **`9`** (`VERIFIED`)
- `battery_expense_types`: **`1`** (`VERIFIED`)
- `battery_purchase_payment_details`: **`1`** (`PRESERVED` orphan row)
- `migrations`: **`64`** (`62` original + `2` batch-9 incremental migrations)
