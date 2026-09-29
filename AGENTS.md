# AGENTS.md — PECIT Smart Inventory System (PSIS)

Guidance for AI coding agents working in this repository.

---

## What this project is

**PSIS** is a Laravel 12 institutional inventory & requisition system for **PECIT** (Philippine Electronic and Communication Institute of Technology).

- **Path:** `C:\xampp\htdocs\pecit-sis` (XAMPP)
- **Database:** MySQL `pecit_sis`
- **UI:** Blade + Tailwind + Alpine.js (not React/Inertia)
- **Auth:** Laravel Breeze + Spatie Permission (roles); dual login (staff vs student)
- **Primary layout:** `resources/views/layouts/psis.blade.php`

Human-facing docs: `README.md`.

---

## Roles (exact Spatie names)

| Role | Purpose |
|------|---------|
| `Administrator` | Users, master data, audit, reports; can also approve requests |
| `Admission` | School owner: dashboard, **view inventory**, **approve/reject** faculty requests — no users, stock ops, or master data |
| `Accounting` | Review faculty requests, verify student payments |
| `Supply Personnel` | Stock ops, release orders, dashboard **work queue** (ready-to-release + inspect), **student accounts**, users, categories, departments, announcements, audit logs |
| `Faculty` | Submit/cancel supply requests |
| `Student` | Uniform shop (department), cart, checkout, receipts, purchases — **no inventory module** |

Do **not** rename roles without updating seeders, menus, middleware, and policies.

## Departments (exact codes)

Seeded in `MasterDataSeeder`. Do **not** rename codes without updating seeders, shop exclusivity, CSV import, and demo students.

| Code | Name |
|------|------|
| `CCS` | College of Computer Studies |
| `CC` | College of Criminology |
| `CTHM` | College of Tourism and Hospitality Management |
| `CTE` | College of Teacher Education |
| `CBA` | College of Business Administration |
| `SHS` | Senior High School |
| `ADMIN` | Administration |
| `SUPPLY` | Supply Office |

Removed academic codes: `CIT`, `COE`, `COB` (and similar leftovers). Live data remapped **CIT→CCS**, **COE→CC**, **COB→CBA**.

---

## Core business rules (do not break)

### Inventory deduction

1. Submit request/purchase → **no stock deduct**
2. Admin approve / Accounting verify payment → **reserve** (`reserved_quantity`)
3. Supply **release** → deduct **on-hand** `quantity` and clear reserved
4. Cancel before release → restore reserved

Services own this logic:

- `app/Services/InventoryService.php` — stockIn, reserve, release, restore, adjust, damage, returnToSupplier
- `app/Services/SupplyRequestService.php` — faculty workflow
- `app/Services/PurchaseRequestService.php` — student purchase workflow

Prefer extending these services over putting stock math in controllers.

### Available quantity

```text
available = quantity - reserved_quantity
```

Quantities are **decimal (4 places)** so 0.5 ream and 3.74 L store as written. Uniform Shop cart stays whole pieces.

Inventory UI should show **On Hand**, **Reserved**, and **Available**.

### Faculty department budget (do not break)

Each department’s faculty requisitions are capped by `departments.faculty_budget_limit` (default **₱10,000**) **per semester**. There are **two semesters** per academic year: **1st** June 1–November 30, **2nd** December 1–May 31. Count statuses: pending, accounting review, admin review, approved, reserved, released. Cancelled and rejected do **not** count. Enforce in `FacultyBudgetService` / `SupplyRequestService` (create and accounting review). Faculty must have a `department_id`.

### Student shop / department exclusivity (do not break)

Students **cannot** access `/inventory` (menu, routes, policy). They buy only via **Uniform Shop** (`shop.*`).

**Rules:**

1. Item must have `student_shop = true`
2. `department_id` **null** → shared for all students (P.E., NSTP, ID lanyard only in practice)
3. `department_id` **set** → exclusive; **only** students whose `users.department_id` matches may see/buy it
4. A student must **not** see or buy another department’s exclusive uniform

Example: Computer Studies (`CCS`) exclusive uniform is buyable only by CCS students. Criminology (`CC`) students see CC exclusive + shared, never the CCS exclusive. Same rule for `CTHM`, `CTE`, `CBA`, and `SHS`.

Helpers (keep logic here):

- `Inventory::scopeForStudentShop(User $user)`
- `Inventory::isAvailableInStudentShop(?User $user)`
- `Inventory::isDepartmentExclusive()`

Enforce the same checks in `ShopController` (list/cart/add) and `PurchaseRequestService::checkout`.

When creating shop items in admin/supply inventory form:

- Shared → student shop on, department empty
- Exclusive → student shop on, department selected

There is a **Suppliers** module (Admin / Supply). Supplier is recorded on the **stock movement**, not as a single `supplier_id` on the inventory item. One item can come from many suppliers or from donations / external sources.

---

## Architecture map

```text
routes/web.php          # Main app routes (role middleware)
routes/auth.php         # Breeze auth (login, password reset; no public register)

app/Http/Controllers/   # Thin controllers (incl. AiAssistantController)
app/Services/           # Business logic (AiInsightService, OllamaChatService)
app/Policies/           # Inventory, SupplyRequest, PurchaseRequest
app/Support/PsisMenu.php# Sidebar items + isActive() matching
app/Support/Qty.php      # Decimal quantity rounding (4 dp)
app/Mail/               # Email for notifications
config/psis.php         # PSIS_MAIL_*, PSIS_OLLAMA_*

resources/views/
  layouts/psis.blade.php        # Fixed sidebar + sticky header
  auth/login.blade.php          # Staff / Student tabs
  supply/students/              # Supply student list, form, CSV import
  dashboard/index.blade.php     # Role KPI cards; Supply/Admin work queue
  partials/ai-chat-widget.blade.php
  partials/supply-work-queue.blade.php
  partials/forecast-demand.blade.php
  reports/index.blade.php       # Month/semester/year ranking; separate semester demand trend
  emails/
resources/js/app.js     # Alpine, Chart.js, psisAiChat (clears chat on login page)
public/images/          # pecit-logo.png, chatbot.png
```

### Important models / tables

| Model | Table | Notes |
|-------|-------|-------|
| `User` | `users` | `employee_id` = Student ID for students; `last_name` for student login; `email` for notifications; `department_id` for shop exclusivity |
| `Department` | `departments` | Canonical codes: CCS, CC, CTHM, CTE, CBA, SHS, ADMIN, SUPPLY |
| `SupplyRequest` | `requests` | Faculty requisitions |
| `RequestItem` | `request_items` | |
| `PurchaseRequest` | `purchase_requests` | Student purchases |
| `Inventory` | `inventory` | `student_shop`; `department_id` null = shared shop item, set = department-exclusive; `unit_of_measurement_id` |
| `Transaction` | `transactions` | Stock ledger / Stock Card (physical movements). Reserve/restore are logged but hidden on the card. |
| `Supplier` | `suppliers` | Linked to stock-in / return transactions, not to the item master |
| `PsisNotification` | `psis_notifications` | Custom in-app notifications (not Laravel notifications table) |
| `Payment` | `payments` | Includes `receipt_path` |

---

## Workflows (quick reference)

### Auth

- **Staff:** email + password (`login_as=staff`) via `LoginRequest`
- **Student:** `employee_id` (Student ID) + `last_name` (`login_as=student`); case-insensitive last name match; students cannot use the staff email login
- Keep `email` on student accounts for `NotificationService` / mail

### Faculty supply request

`pending` → Accounting review → `admin_review` → Admission/Admin approve (reserve) → `approved` → Supply release → `released`

Routes under `requests.*` (Faculty only). Accounting: `accounting.requests*`. Admission/Admin: `admin.requests*`. Supply: `supply.releases*` (dashboard work queue lists `approved`/`reserved`). Department budget: `FacultyBudgetService`.

Stock-in purchase history (correct vs wrong item): `supply.purchase-history`.

### Student purchase

Uniform Shop cart (session) → checkout → `payment_submitted` → optional receipt upload → Accounting verify (reserve) → `payment_verified` → Supply release → `released`

Shop listing is filtered by department exclusivity + shared items. Cart/checkout reject cross-department exclusives.

Student: `shop.*`, `purchases.*`. Accounting: `accounting.payments*`. Supply: `supply.purchases*`.

### Student account management (Supply / Admin)

Routes: `supply.students.*`

- List / create / edit students only (`Student` role)
- CSV import: `student_id,last_name,name,email,department_code,phone` (`department_code` is CCS, CC, CTHM, CTE, CBA, or SHS)
- Logic in `app/Services/StudentAccountService.php` (create, update, import, template)
- Controllers: `SupplyStudentController`
- Auto-generates a random password (students do not use password login)
- Sets `email_verified_at` so `verified` middleware allows access

Admin still manages all roles via `admin.users.*`.

---

## Notifications & email

`app/Services/NotificationService.php`:

1. Creates `PsisNotification` (in-app)
2. Sends `App\Mail\PsisNotificationMail` if `config('psis.mail_notifications')` is true

Email failures must **not** abort business transactions (catch + log).

Env flags: `MAIL_*`, `PSIS_MAIL_NOTIFICATIONS`.

---

## AI module

Hybrid, **local only** — no OpenAI / cloud API.

1. `app/Services/AiInsightService.php` — live stock, requests, budget snapshot, keyword answers, restock math
2. `app/Services/OllamaChatService.php` — optional wording via Ollama on `http://127.0.0.1:11434` (localhost / `::1` only)
3. Chat: `AiAssistantController` + `POST /ai/ask` (type box + collapsible role question list)
4. Floating widget: `resources/views/partials/ai-chat-widget.blade.php` (PSIS layout)
5. Restock page: `/ai/restock` (Supply / Admin)
6. Daily alert: `php artisan psis:low-stock-alert` (scheduled in `bootstrap/app.php` at 08:00)

Env: `PSIS_OLLAMA_ENABLED`, `PSIS_OLLAMA_URL`, `PSIS_OLLAMA_MODEL`, `PSIS_OLLAMA_TIMEOUT` in `config/psis.php`.

Listed questions stay rule-based (exact numbers). Free text uses keyword match first, then Ollama with an injected fact snapshot (live departments/categories/units of measurement/users/suppliers and **only items clearly named in the question**, not a sample catalog). Inventory totals (SKU count, on hand, reserved, available, and **by category**) are in that snapshot and in the **How many items?** / available-by-type answers. **List all items** returns each item with its available quantity (first 30, then type a name; students: shop items only), **one bullet per line**. Monthly **most requested** ranking (faculty + student; cancelled/rejected excluded) is on Reports (stacked bar; filter by **month, semester, or year**) and in chat (**Most requested this month**). Demand trend has its **own semester filter**: **item names on the left**, months of that semester on the bottom, and a predicted finish from last year the same months (or current pace). Remaining months of that semester also get an **item forecast** (likely trend items + restock vs available). Forecast / Restock Tips also split **faculty most requested** and **student most purchased**; the AI remembers those tops for the month and suggests restock when available stock cannot cover demand (**What needs restock this month**). The Reports / Restock **Monthly summary** is organized (counts + lists) and exportable as PDF/Excel (`reports.summary.pdf`, `reports.summary.excel`, `?month=Y-m`). The assistant also answers **one item or every row with the same name** (name/code, listed one per line), **REQ-/PUR-** status (own records for Faculty/Student), **What should I do next?**, **where to view a page** (role-gated sidebar URL, clickable in the widget and `/ai` chat), **Compare this month to last month**, **What will trend this semester?** (remaining months vs last year + restock, listed one per line), and faculty **budget fit** for a quantity. Follow-ups such as “that item” use the last few chat messages (`history` on `POST /ai/ask`). Typed questions are understood in **English, Filipino (Tagalog), and Cebuano (Bisaya)**; rule-based replies and Ollama should answer in the same language. The greeting bubble is a **short shared intro** (type or pick a question) — **do not list those language names** in the widget or `/ai` page. If Ollama is off or down, the question list still works. Never invent stock numbers.

Widget UX: **Choose a question** is collapsed by default. The FAB can be dragged a short way up/left (not into the middle); click opens the panel **pinned to the lower-right corner**. The thread is stored in `sessionStorage` (`psis-ai-chat:{userId}` via `window.psisAiChat` in `resources/js/app.js`) and **cleared on logout** and on the login page. Do not persist chat after logout on a shared PC.

Supply dashboard (Supply Personnel): KPI cards are **Ready to release**, **Inspect today**, **Reserved**, **Today’s movements** — not **Pending Requests** (that stays Accounting / Admin / Faculty). Admin keeps pending/approved cards **and** the same work-queue block. Queue data: faculty `approved`/`reserved`, student `payment_verified`, purchase-history rows with `inspection_status` pending/null, low-stock items with Stock In links (`supply.stock.index?item=`).

---

## UI conventions

- Use `layouts.psis` for authenticated pages (not Breeze `app` layout) for new screens
- Reuse classes: `psis-card`, `psis-btn-primary`, `psis-btn-outline`, `psis-input`, `psis-label`
- Brand colors: `#0B3C91` (blue), `#F4B400` (gold)
- Tables: give **every** `<th>` and `<td>` the same horizontal padding (`px-4 py-3`); avoid “header-only padding” bugs
- Sidebar active state: use `PsisMenu::isActive()` — Faculty **My Requests** vs **New Request** must stay mutually exclusive
- Desktop sidebar stays visible while scrolling: keep `#psis-sidebar` **fixed** (`inset-y-0`) and offset content with `.psis-main-col` / `lg:ml-64`. Do not use `lg:static` on the aside (that scrolls the menu off-screen).
- Assets: `public/images/pecit-logo.png`, `public/images/chatbot.png`
- After CSS/JS changes under `resources/`, run `npm run build` (or `npm run dev`)

---

## Auth & session pitfalls

- Base controller uses `AuthorizesRequests` (`app/Http/Controllers/Controller.php`) — required for `$this->authorize()`
- `SessionTimeout` middleware uses **session** `last_activity_at` (not only DB) so old DB stamps do not kick users out immediately after login
- Login must reset activity: see `AuthenticatedSessionController@store`
- New users (admin or supply-created students) should get `email_verified_at` set so `verified` middleware allows login
- User model casts `password` as `hashed` — pass plain password on create/update (do not double `Hash::make`)
- Student create: password optional / auto-generated; **require** `employee_id`, `last_name`, `department_id`, `email`
- Student login: last name (visible) then Student ID (masked, with Show); keep staff and student validation paths in `LoginRequest` in sync with the Blade form

---

## Blade pitfalls already seen

- Short `@php(...)` cannot contain multiple statements / `;` — use `@php ... @endphp` blocks
- Do not `@include` a full `@extends` view inside another layout page (breaks nested sections)
- `.env` values with spaces must be quoted: `MAIL_PASSWORD="xxxx xxxx"`

---

## Demo credentials

**Staff** — password: `password`

- admin@pecit.edu.ph
- admission@pecit.edu.ph
- accounting@pecit.edu.ph
- supply@pecit.edu.ph
- faculty@pecit.edu.ph

**Student** — login tab (**Student ID + last name**):

| Student ID | Last name | Department | Uniform Shop sees |
|------------|-----------|------------|-------------------|
| `STU-001` | `Santos` | CCS | CCS Exclusive + P.E. / NSTP / lanyard |
| `STU-CC-001` | `Mendoza` | CC (Criminology) | CC Exclusive + P.E. / NSTP / lanyard |

Seeded exclusive uniforms (via `DemoInventorySeeder`): `UNI-CCS`, `UNI-CC`, `UNI-CTHM`, `UNI-CTE`, `UNI-CBA`, `UNI-SHS` plus shared `UNI-PE`, `UNI-NSTP`, `UNI-LANYARD`.

Seeders: `RoleAndPermissionSeeder`, `MasterDataSeeder` (includes Uniforms category), `DemoUsersSeeder`, `DemoInventorySeeder`.

---

## What is intentionally incomplete

- Laravel Sanctum / JSON API (the app is web-session only)
- Broad domain PHPUnit coverage (mostly Breeze auth tests)
- Cloud LLM APIs (OpenAI, etc.). Optional **local** Ollama is in `OllamaChatService`
- Public self-registration (Admin / Supply create student accounts)

Do not add Sanctum or a cloud LLM unless the user asks.

---

## Agent working rules for this repo

1. **Match existing patterns** — Blade/Tailwind/Alpine, service layer, Spatie role names.
2. **Minimal diffs** — change only what the task needs; avoid drive-by refactors.
3. **Do not commit** unless the user explicitly asks.
4. **Do not put secrets** in git; never echo real mail passwords into README/chat logs.
5. Prefer fixing inventory/request/student logic in **Services**, not duplicating in controllers/views.
6. When adding notifications, use `NotificationService` so email stays in sync.
7. When adding sidebar links, update `PsisMenu` and ensure `isActive()` behaves correctly for sibling routes.
8. Run `npm run build` after changing `resources/css` or `resources/js` if the user needs to see UI changes under `php artisan serve`.
9. Keep student shop exclusivity on `Inventory` helpers; never bypass in checkout/cart.
10. Student CSV import stays under `supply.students.*`. Supply may also use admin users, categories, departments, announcements, and audit logs.
11. Do not give Students inventory menu/routes; Uniform Shop is their only purchase UI.
---

## Common commands

```bash
php artisan serve
php artisan migrate --seed
php artisan storage:link
php artisan config:clear
php artisan view:clear
php artisan psis:low-stock-alert
php artisan psis:import-supplies-xlsx --force
php artisan psis:purge-student-purchases --force
npm run build
```

`psis:import-supplies-xlsx` replaces office inventory and faculty request history from `docs/.supply data/SUPPLIES DATA updated.xlsx` (falls back to `SUPPLIES DATA.xlsx`). It **keeps** users, students, Uniform Shop items, and student purchases. Do not re-run unless asked (it wipes imported faculty history first). Same item names across 2024/2025/2026 sheets stay **one SKU**; each row is issuance history. QTY cells that Excel stored as dates (typed `1/2`) are read as **0.5** from TOTAL AMOUNT ÷ UNIT PRICE. Pump liters such as 3.74 stay decimals, not rounded to whole units. Date cells follow the visible **month/day** on the sheet, not Excel’s day/month serial.

`psis:purge-student-purchases` deletes student purchases and payment history. It **keeps** student accounts and the Uniform Shop catalog.

Optional local chat: install Ollama, `ollama pull llama3.2:3b`, set `PSIS_OLLAMA_ENABLED=true`.

Windows scheduler (optional): run `php artisan schedule:run` every minute for daily low-stock emails.

---

## Key files checklist

| Task | Start here |
|------|------------|
| Routes / roles | `routes/web.php` |
| Sidebar | `app/Support/PsisMenu.php` |
| Stock math | `app/Services/InventoryService.php` |
| Faculty flow | `app/Services/SupplyRequestService.php`, `app/Services/FacultyBudgetService.php` |
| Stock receive check | `supply.purchase-history`, `transactions.inspection_status` |
| Student purchase flow | `app/Services/PurchaseRequestService.php` |
| Student shop filter | `app/Http/Controllers/ShopController.php`, `Inventory` scopes |
| Departments | `database/seeders/MasterDataSeeder.php`, `DepartmentController` |
| Student accounts / CSV | `app/Services/StudentAccountService.php`, `SupplyStudentController` |
| Login (staff + student) | `app/Http/Requests/Auth/LoginRequest.php`, `resources/views/auth/login.blade.php` |
| AI | `app/Services/AiInsightService.php`, `app/Services/OllamaChatService.php` |
| Chat widget / persist | `partials/ai-chat-widget.blade.php`, `resources/js/app.js` (`psisAiChat`) |
| Email + bell | `app/Services/NotificationService.php` |
| Layout / FAB / sidebar | `resources/views/layouts/psis.blade.php`, `partials/ai-chat-widget.blade.php` |
| Supply dashboard queue | `DashboardController`, `partials/supply-work-queue.blade.php` |
| Reports / demand charts | `ReportController`, `resources/views/reports/index.blade.php` |
| Supplies issuance log | `SuppliesIssuanceService`, `SimplePdf`, `reports.supplies-issuance` |
| App config bootstrap | `bootstrap/app.php` |
