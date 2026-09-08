# Changelog

All notable changes to the **PECIT Smart Inventory System (PSIS)** are documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).
Dates are in **Asia/Manila**.

---

## [Unreleased] — 2026-09-08

### Added

- Supply / Admin dashboard **Recent Student Purchases** (order date, student name, department, purchase #, status).
- Accounting dashboard and **Verify Payments** show **Recent verified payments** (verified date, student, department, purchase #, amount, status). Waiting payments stay in the top queue; after verify they appear in this list.
- Students can **Cancel Purchase** until Supply releases (before or after Accounting verifies).
- Audit Logs now record logins, inventory/stock, users, master data, and profile changes, with who / action / record / details.

### Changed

- Student login: **Last name** first (visible); **Student ID** below, hidden while typing, with a Show Student ID checkbox. Staff/Student tabs work with HTML + CSS (no Alpine), so the form still works when JS assets fail to load.
- App URLs follow the incoming request (`SetRootUrlFromRequest`, trusted proxies, relative Vite paths) so login and CSS/JS work when the site is opened from another PC or a port-forward / tunnel, not only `APP_URL` localhost.
- Inventory **Add Item**: uniform size shows when Uniform Shop is checked even if Alpine does not start. Faculty **New Request** always has a first item line (Add line works without Alpine). Shop **Add to Cart** stays visible without Alpine.

### Removed

- Unused inventory **`barcode`** column and the item-detail **QR** graphic. Run `php artisan migrate` if the column is still on an existing database.

### Fixed

- **Add Item** opened `/inventory/create` as a 404 because `create` was treated as an inventory id. Create/edit routes are registered first; show only accepts numeric ids.
- Saving a Uniform Shop clothing item without a size no longer 500s; staff get a validation error until they pick a size.
- Faculty can cancel through **admin review** and **approved** (before release). Cancelling an approved request **restores reserved** stock. The Cancel button matches the policy (no 403 on admin review).
- Cancelling a student purchase after payment verify **restores reserved** stock, including per-size stock.
- Accounting payment detail no longer shows **Verify** after the payment is already verified.

---

## [1.2.0] — 2026-08-28

### Added

- **Order status tracker** for student purchases and faculty supply requests.
  - Students: Order placed → Pay at Accounting → Payment verified → Claim at Supply.
  - Faculty: Submitted → Accounting review → Admin approval → Claim at Supply.
  - Full stepper on detail pages; compact tracker on My Purchases, My Requests, and dashboards.
  - Cancelled / rejected orders show as stopped (with rejection reason when present).
  - Same tracker on Accounting and Supply review/release screens.
- Local **colored sidebar icons** (`x-icon`) with per-item colors. Header menu, bell, and dark-mode controls use the same local SVGs (no icon CDN).
- **Admission** role for the school owner (`admission@pecit.edu.ph` / `password`).
  - Dashboard, inventory **view**, and faculty request **approve / reject**.
  - Sidebar is limited: no users, categories, departments, announcements, stock ops, students, reports, or audit logs.
- **Uniform sizes** in Uniform Shop (XS–3XL). Students must choose a size before adding clothing uniforms to the cart. ID lanyard stays size-free.
- **Per-size stock** (`inventory_size_stocks`). Staff assign a size when adding or stocking uniforms. Students see availability for the selected size only, not the item total.
- Size is stored on purchase lines and shown on cart, purchase details, payment slip, and supply release.
- Clickable **dashboard** KPI cards (inventory, pending/approved queues, released items list, reports/chart).
- **Released Items** table on the staff dashboard.
- Students can see **Student ID** and **Department** on Profile (read-only).
- Supply Personnel sidebar access to **Users**, **Categories**, **Departments**, **Announcements**, and **Audit Logs** (Students was already available).
- Inventory list **status** filter (available / low stock / out of stock).

### Changed

- Figtree font and Chart.js are bundled with Vite instead of Bunny Fonts / jsDelivr CDNs.
- Faculty request flow after Accounting review notifies both **Admission** and **Administrator**.
- Receipt upload accepts JPG, PNG, WEBP, and PDF (max 5MB) with clearer error messages.

### Removed

- **Suppliers** module (admin CRUD, inventory supplier field, and related seed data). There is no suppliers feature in PSIS.
- Unused Laravel leftovers: default welcome page, Breeze `app` layout/navigation, unused register scaffolding, unused profile partials, and `setup-psis.php` (one-time generator — do not re-run).
- Unused session JSON endpoints (`/api/inventory`, `/api/requests`) and `routes/api.php`. The app is web-session only.

---

## [1.1.0] — 2026-08-06

### Added

- **Student Uniform Shop** with department exclusivity.
  - Exclusive uniforms (`department_id` set) are visible only to students of that department.
  - Shared items (`department_id` null): P.E., NSTP, ID lanyard — all students.
- Dual login: **Staff** (email + password) and **Student** (Student ID + last name).
- Student cart, checkout, OTC payment slip (PDF), receipt upload, and purchase history.
- Supply / Admin **student account** management and **CSV import**.
- Demo students: `STU-001` / Santos (CIT) and `STU-COE-001` / Mendoza (COE).

### Changed

- Students cannot open the main Inventory module (menu, routes, policy, API).
- CI login tests updated for staff vs student authentication.

### Removed

- Supplier foreign key on inventory (`2026_08_06_000001_drop_suppliers_from_inventory`).

---

## [1.0.1] — 2026-08-18

### Added

- ER diagram for student shop exclusivity, embedded in the README (`docs/ER-DIAGRAM.md`).

---

## [1.0.0] — 2026-07-25

First PSIS release on Laravel 12 (Blade, Tailwind, Alpine.js, Spatie roles).

### Added

- Role-based access: Administrator, Accounting, Supply Personnel, Faculty, Student.
- Inventory with on-hand, reserved, and available quantities.
- Faculty supply requests: submit → Accounting review → Admin approve (reserve) → Supply release (deduct).
- Student purchases (early flow) with Accounting payment verification and Supply release.
- Stock in / inventory adjustment, transactions, and stock logs.
- In-app notifications plus optional email (`PSIS_MAIL_NOTIFICATIONS`).
- Rule-based AI assistant and restock tips (not an external LLM).
- Daily low-stock alert command (`php artisan psis:low-stock-alert`).
- Reports (PDF / Excel), audit logs, announcements, categories, and departments.
- Session timeout, dark mode, PECIT branding (`#0B3C91` / `#F4B400`).

---

## Demo accounts (current)

Staff password: `password`

| Role | Login |
|------|--------|
| Administrator | `admin@pecit.edu.ph` |
| Admission | `admission@pecit.edu.ph` |
| Accounting | `accounting@pecit.edu.ph` |
| Supply Personnel | `supply@pecit.edu.ph` |
| Faculty | `faculty@pecit.edu.ph` |
| Student | Student tab: `STU-001` / `Santos` or `STU-COE-001` / `Mendoza` |
