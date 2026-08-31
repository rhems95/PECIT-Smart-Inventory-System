# Changelog

All notable changes to the **PECIT Smart Inventory System (PSIS)** are documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).
Dates are in **Asia/Manila**.

---

## [Unreleased] — 2026-08-28

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

### Fixed

- GitHub Actions **Execute tests** (PHP 8.2+): pages no longer 500 without `public/build` (`withoutVite()`), Spatie roles are seeded in tests, and PHPUnit/CI force SQLite in-memory.
- Student **receipt upload** / view: `public/storage` was an empty folder instead of a link to `storage/app/public`. Recreated as a junction so receipts save and open correctly.
- Uniform **on-hand by size** can be saved from Edit Inventory (Available stays on-hand minus reserved and is not a typed field).

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
