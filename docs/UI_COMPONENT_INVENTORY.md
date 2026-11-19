# MARSS Corporation — UI Component & Visual Design System Inventory

**Audit Date:** 2026-11-19  
**Target Implementation:** Pure **Tailwind CSS 4** + **Alpine.js** (bundled via Livewire 4) + **Livewire 4 (`wire:navigate` + `@persist`)** — ZERO Bootstrap, ZERO jQuery, ZERO Select2, ZERO SweetAlert2, ZERO Toastify, ZERO custom CSS bloat.

---

## 1. Visual Design System (Exact Parity Tokens)

### Brand & Surface Palette
- **Primary Brand Emerald:** `emerald-700` (`#15803d`), `emerald-600` (`#16a34a`), Hover `emerald-800` (`#166534`) / `emerald-900` (`#14532d`), Tint `emerald-50` (`#f0fdf4`)
- **Sidebar Mesh Gradient (240px expanded / 70px collapsed):** `linear-gradient(165deg, #064e3b 0%, #047857 35%, #0d9488 70%, #0f766e 100%)`
- **Sidebar Brand Box & Logout Footer:** `rgba(6, 78, 59, 0.95)` with `rgba(255, 255, 255, 0.12)` divider
- **Active Sidebar Menu Link:** `linear-gradient(135deg, #10b981 0%, #059669 100%)` with `3px` `#34d399` left accent
- **Topbar (58px compact height):** Light `#ffffff` / Dark `#0b0f19` with subtle slate border
- **Page Background:** Light `#f8fafc` / Dark `#0b0f19`
- **Cards & Modals:** Light `#ffffff` (`border-[1.5px] border-slate-300`) / Dark `#0f172a` (`border-slate-700`)
- **Inputs & Controls:** Light `#ffffff` (`border-[1.5px] border-slate-300`, hover `#16a34a`, focus `#15803d` + `ring-emerald-700/20`) / Dark `#1e293b` (`border-slate-700`)
- **Shop Context Switcher:** Retail Shop (`emerald` pill + `fa-shop`) | Battery (`amber` pill + `fa-car-battery`)
- **Login Page:** Left 46% hero (`login-bg.jpg` + deep green gradient + glassmorphic `MARSS CORPORATION` card), Right 54% light/dark form panel with theme toggle.

### Typography
- **Global Font Stack:** `'Valley Sans', 'Baloo Da 2', sans-serif` (Google Fonts: `Valley Sans` 300..800 for English/Numbers + `Baloo Da 2` 400..800 for Bangla). `Poppins` strictly prohibited.
- **Icons:** `@fortawesome/fontawesome-free` (FA 6 Solid & Regular bundled locally via Vite — eliminating external FA Kit CDN dependency) + clean inline SVGs.

### Dark / Light Mode
- Single authoritative dark-mode mechanism (`html.dark` + `[light-mode="dark"]` attribute persisted in `localStorage.lightMode`, applied synchronously before paint to prevent FOUC — resolving the 4 conflicting legacy `toggle_light_mode()` definitions).

---

## 2. Layout & Navigation Architecture

1. **Persistent Shell (`@persist('sidebar')` & `@persist('topbar')`):**
   - Left Sidebar (`240px` expanded, `70px` collapsed icon rail with floating hover flyouts, mobile slide-out drawer `< 1024px`).
   - 2-panel sliding drilldown submenus (Main Panel ↔ Submenu Panel with smooth CSS transform) for both Retail (12 top-level items) and Battery (12 top-level items).
   - Fixed 58px Topbar: Sidebar toggle (`38×38px`), Dynamic Back Button, Retail ↔ Battery Shop Switcher, Live Date & Time pill (`d M Y • h:i:s A`), Light/Dark toggle, Fullscreen toggle, Low-Stock & Activity Notification Bell (with Admin 2-tab switcher), User Profile Dropdown (avatar, name, role badge, Profile, Logout).
   - Sticky Footer (`mt-auto`): `© {{ date('Y') }} MARSS CORPORATION | Software By: CodeNext IT`.
2. **POS 100vh Fullscreen Layout (`RetailPosTerminal` & `BatteryPosTerminal`):**
   - Dedicated viewport-locked `h-screen overflow-hidden` layout (desktop 2-column split, mobile tabbed with bottom cart dock), sticky cart header, pinned payment/checkout footer, `F4` submit shortcut, Door Handedness selector (Retail), barcode camera scanner (`html5-qrcode`) & keyboard barcode entry.

---

## 3. Standardized UI Components (`resources/views/components/`)

| Component | Purpose & Parity Spec |
|---|---|
| `x-ui.page-card` | Rounded-2xl card container (`border-[1.5px] border-slate-300 dark:border-slate-700`), icon badge + `h1` title + top export buttons (`38×38px`) |
| `x-ui.filter-bar` | Responsive search box (`h-[38px]`), Flatpickr `d/m/Y` (`monthSelectorType: "static"`) date range, `Show: 15/50/100/200/500`, searchable dropdowns |
| `x-ui.data-table` | Desktop table (`hidden md:block`, solid `#15803d` nowrap `thead`, `p-[10px]` cells, `tfoot` summary, BD currency formatting `৳ 24,800.00`) + Mobile card list (`block md:hidden`, 2-col financial grid) + single-line pagination & Showing pill |
| `x-ui.modal` | Pure Alpine/Livewire modal (`sm: 24rem`, `md: 32rem`, `lg: 48rem`, `xl: 64rem`, `920px` full-edit), blurred backdrop, ESC close, body scroll lock |
| `x-ui.date-picker` | Alpine-wrapped Flatpickr (`d/m/Y` display, `Y-m-d` Livewire value, static month selector, dark mode styled) |
| `x-ui.searchable-select` | Pure Alpine searchable dropdown replacing Select2 (zero jQuery) |
| `x-ui.export-toolbar` | Copy (TSV clipboard), CSV (UTF-8 download), Excel (`.xls`), PDF (print-ready export), Print (`window.print()`) |
| `x-ui.toast` | Alpine-driven toast notifications (Emerald success / Crimson error, bottom-right, auto-dismiss) |
| `x-ui.skeleton.*` | Component-level Livewire `placeholder()` skeletons (Dashboard, Table, Cards, Form, POS, Invoice, Report) |

---

## 4. Print & Barcode Templates
- **Sales & Due Invoice Print (Retail & Battery):** Standalone `8in × 10.5in` MARSS bill layout (`@page { size: portrait; margin: 0mm; }`), MARSS logo + red/green header, dotted metadata, 5-col table, Totals + Returned Amount + Net Bill + Previous/Total Due, Taka in Words (Crore/Lakh/Thousand), Dual Signatures, CodeNext IT footer, 50/50 Red/Green bottom stripe.
- **Purchase Invoice Print (Retail & Battery):** 3-column header (Supplier \| Purchase Info + Logo \| MARSS Info), 5-col table, Taka in Words, Dual Signatures, clean `@media print` rules.
- **Return Vouchers (Sales & Purchase, Retail & Battery):** Clean Tailwind printable vouchers (fixing legacy hardcoded `৳0.00` & leftover mobile-shop text).
- **Barcode Label Generator (Retail & Battery):** Dual-mode (Product Barcode Label with MRP + Sequential Prefix Range) powered by bundled `jsbarcode` (`CODE128`, A4 3-column grid — fixing legacy broken static PNG barcode).
