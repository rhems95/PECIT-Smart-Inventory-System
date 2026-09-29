# Changelog

All notable changes to the **PECIT Smart Inventory System (PSIS)** are documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).
Dates are in **Asia/Manila**.

---

## [Unreleased] — 2026-09-28

### Added

- Supply / Admin dashboard **Recent Student Purchases** (order date, student name, department, purchase #, status).
- Accounting dashboard and **Verify Payments** show **Recent verified payments** (verified date, student, department, purchase #, amount, status). Waiting payments stay in the top queue; after verify they appear in this list.
- Stock Card (`inventory.stock-card`) for Admin, Supply, Accounting, Admission — generated from `transactions`
- **Suppliers** and **Units of measurement** (Admin / Supply). Supplier is stored on each stock movement, not as a single supplier on the item.
- Sample suppliers in `MasterDataSeeder` (`SUP-0001`–`SUP-0004`) for stock-in / return-to-supplier demos.
- AI Assistant uses a **question list** plus a type box. Listed questions stay rule-based; free text can use local Ollama.
- Students can **Cancel Purchase** until Supply releases (before or after Accounting verifies).
- Faculty **department supply budget** of ₱10,000 **per semester** (two semesters per academic year: June–November and December–May). Pending through released count; cancelled/rejected do not. Shown on New Request; Accounting/Admin see remaining room for the current request. Admin/Supply can edit the limit per department.
- Supply **Purchase History** lists **student shop purchases**, **department released items** (faculty requests), and **supplier stock-in**, with a receiving check: correct item vs wrong item.
- Local **Ollama** chatbot (`OllamaChatService`): type box restored; listed questions stay rule-based from live PSIS data; free text can use `http://127.0.0.1:11434` when `PSIS_OLLAMA_ENABLED=true`. Cloud APIs are not used. If Ollama is off, keyword answers and the question list still work.
- AI chat **thread stays** while you navigate the app; it is cleared on **logout** (and on the login page) so the next user does not see it.
- AI answers **how many items** from live inventory (SKU count, on hand, reserved, available). Ollama now receives that fact so it does not say it has no number.
- AI chat understands **English, Filipino, and Cebuano** (and mixed Taglish / Bisaya-English) for common stock and request questions.
- Reports page **Most requested items** bar graph, selectable **by month, semester, or year** (faculty requests + student purchases). The AI can answer the current-month ranking.
- AI can **list all inventory items** with live available counts (students see Uniform Shop items only).
- Forecast / Restock Tips now show **most requested (faculty)** and **most purchased (students)** for the month. The AI remembers those tops and predicts which demanded items need restock.
- Reports **Monthly summary** is organized (counts, faculty, students, restock) and can be exported as **PDF** or **Excel** for the selected month.
- Reports ranking chart is a **stacked horizontal bar** (faculty blue + student gold). **Demand trend** has its own **semester** filter, with item names on the left, that semester’s months along the bottom, and a predicted finish vs last year.
- AI can look up **one item** by name/code, a **REQ- / PUR-** number, **what to do next** for the signed-in role, **this month vs last month**, and **semester restock** from last year same month. Faculty can ask if a quantity still fits the department budget. Short follow-ups like “that item” use the last chat turn.
- Supply / Admin dashboard **work queue**: ready-to-release faculty (`approved` / `reserved`) and students (`payment_verified`), inspect-today deliveries, reserved vs available, actionable low stock with Stock In links, oldest waiting, and today’s physical movements. Supply stat cards no longer treat **Pending Requests** as their queue.
- Reports **Supplies issuance log** (like the office supplies spreadsheet): filter by **day / week / month / semester / year / all**, Faculty vs Students, one department, export **PDF** and **Excel**. Year/semester PDFs write line-by-line (not DomPDF tables) so large imported logs do not freeze the page.
- Artisan `psis:import-supplies-xlsx` loads `docs/.supply data/SUPPLIES DATA updated.xlsx` (year sheets 2024–2026) into inventory + released faculty issuance history (keeps users, students, Uniform Shop items, and student purchases). Blank dates in that spreadsheet follow the last dated item above.
- Artisan `psis:purge-student-purchases` deletes student purchases, payments, and purchase history while keeping student accounts and the Uniform Shop catalog.
- AI answers **where to view a page / how to navigate** with the sidebar name and a clickable URL for that role (Reports, Uniform Shop, New Request, and other menu pages). Pages not in the user’s menu are refused and replaced with allowed links.
- AI item lookup lists **every inventory row with the same name** (and names that start with it), each with item code and available stock. Type a code to see one row in detail.
- AI live snapshot now includes **departments, categories, units of measurement, shop vs office SKU split, user counts by role, and suppliers** (role-gated). Ollama receives **only items clearly named in the question**, not a 20-SKU sample list, so similar wording does not pick a nearby item.

### Changed

- Stock, faculty request, and imported issuance quantities are **decimal (4 places)** so half-reams and pump liters (0.5, 3.74) store as written. Uniform Shop cart stays whole pieces.
- Academic departments are now **CCS** (College of Computer Studies), **CC** (College of Criminology), **CTHM** (College of Tourism and Hospitality Management), **CTE** (College of Teacher Education), and **CBA** (College of Business Administration), plus **SHS**, **Administration** (`ADMIN`), and **Supply Office** (`SUPPLY`). Existing records were remapped **CIT→CCS**, **COE→CC**, **COB→CBA**. Demo student `STU-COE-001` is now `STU-CC-001` (Mendoza / Criminology).
- Student login: **Last name** first (visible); **Student ID** below, hidden while typing, with a Show Student ID checkbox. Staff/Student tabs work with HTML + CSS (no Alpine), so the form still works when JS assets fail to load.
- App URLs follow the incoming request (`SetRootUrlFromRequest`, trusted proxies, relative Vite paths) so login and CSS/JS work when the site is opened from another PC or a port-forward / tunnel, not only `APP_URL` localhost.
- Inventory **Add Item**: uniform size shows when Uniform Shop is checked even if Alpine does not start. Faculty **New Request** always has a first item line (Add line works without Alpine). Shop **Add to Cart** stays visible without Alpine.
- Desktop **sidebar stays on screen** while the main page scrolls (`#psis-sidebar` is fixed; content uses `.psis-main-col`).
- Floating AI chat **Choose a question** list is collapsed until you expand it, so the thread has more room.
- Chatbot icon can be dragged a short way up and left (bottom-right pocket). **Click** opens the panel pinned to the lower-right corner; closing puts the icon back where you left it.
- AI greeting no longer lists **English / Filipino / Cebuano**. The bubble is a short “type or pick a question” intro; the assistant still answers in the language the user typed.
- AI item lists, rankings, restock, and forecasts use a **short intro plus one bullet per line** (including Ollama when it rewrites a list).

### Removed

- Unused inventory **`barcode`** column and the item-detail **QR** graphic. Run `php artisan migrate` if the column is still on an existing database.

### Fixed

- AI assistant answers **how many available / by type** from live inventory (totals plus category counts) instead of hanging. Ollama only gets a short sample of items, and its timeout is capped so other typed questions still get a reply if the local model is slow.
- Supply spreadsheet QTY values that Excel turned into dates (typed **1/2**, stored as 46054) are imported as **0.5** using TOTAL AMOUNT ÷ UNIT PRICE, so they no longer dominate demand trend.
- Supplies issuance log **QTY** now shows fractions (0.5, 3.74) instead of rounding them with `number_format()`.
- Supply spreadsheet dates now follow the **visible month/day** on the cell (same as `1/14/2025` = January 14). Excel serials that used day/month (`1/8/2025` stored as August 1) no longer shift items such as RJ45 onto the wrong month.
- Reports, Restock Tips, and the AI can **forecast items likely to trend** in the remaining months of the selected/current semester (last year those months, or current pace) and **suggest restock** when available stock cannot cover that prediction.

- Viewing an uploaded payment receipt from another PC no longer hits Apache **403 Forbidden** on the `/storage` symlink. Receipts open through a logged-in Laravel route (`purchases.receipt.show`).
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
| Student | Student tab: `STU-001` / `Santos` (CCS) or `STU-CC-001` / `Mendoza` (CC) |
