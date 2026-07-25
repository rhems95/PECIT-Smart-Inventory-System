# AGENTS.md — PECIT Smart Inventory System (PSIS)

Guidance for AI coding agents working in this repository.

---

## What this project is

**PSIS** is a Laravel 12 institutional inventory & requisition system for **PECIT** (Philippine Electronic and Communication Institute of Technology).

- **Path:** `C:\xampp\htdocs\pecit-sis` (XAMPP)
- **Database:** MySQL `pecit_sis`
- **UI:** Blade + Tailwind + Alpine.js (not React/Inertia)
- **Auth:** Laravel Breeze + Spatie Permission (roles)
- **Primary layout:** `resources/views/layouts/psis.blade.php`

Human-facing docs: `README.md`.

---

## Roles (exact Spatie names)

| Role | Purpose |
|------|---------|
| `Administrator` | Approvals, users, master data, audit, reports |
| `Accounting` | Review faculty requests, verify student payments |
| `Supply Personnel` | Stock ops, release faculty + student orders |
| `Faculty` | Submit/cancel supply requests |
| `Student` | Shop, cart, checkout, receipts, purchases |

Do **not** rename roles without updating seeders, menus, middleware, and policies.

---

## Core business rules (do not break)

### Inventory deduction

1. Submit request/purchase → **no stock deduct**
2. Admin approve / Accounting verify payment → **reserve** (`reserved_quantity`)
3. Supply **release** → deduct **on-hand** `quantity` and clear reserved
4. Cancel before release → restore reserved

Services own this logic:

- `app/Services/InventoryService.php` — stockIn, reserve, release, restore, adjust
- `app/Services/SupplyRequestService.php` — faculty workflow
- `app/Services/PurchaseRequestService.php` — student purchase workflow

Prefer extending these services over putting stock math in controllers.

### Available quantity

```text
available = quantity - reserved_quantity
```

Inventory UI should show **On Hand**, **Reserved**, and **Available**.

---

## Architecture map

```text
routes/web.php          # Main app routes (role middleware)
routes/auth.php         # Breeze auth
routes/api.php          # Mostly unused; session JSON APIs live under web /api

app/Http/Controllers/   # Thin controllers
app/Services/           # Business logic
app/Policies/           # Inventory, SupplyRequest, PurchaseRequest
app/Support/PsisMenu.php# Sidebar items + isActive() matching
app/Mail/               # Email for notifications
config/psis.php         # PSIS_MAIL_NOTIFICATIONS

resources/views/
  layouts/psis.blade.php
  partials/ai-chat-widget.blade.php
  emails/
public/images/          # pecit-logo.png, chatbot.png
```

### Important models / tables

| Model | Table | Notes |
|-------|-------|-------|
| `SupplyRequest` | `requests` | Faculty requisitions |
| `RequestItem` | `request_items` | |
| `PurchaseRequest` | `purchase_requests` | Student purchases |
| `Inventory` | `inventory` | |
| `PsisNotification` | `psis_notifications` | Custom in-app notifications (not Laravel notifications table) |
| `Payment` | `payments` | Includes `receipt_path` |

---

## Workflows (quick reference)

### Faculty supply request

`pending` → Accounting review → `admin_review` → Admin approve (reserve) → `approved` → Supply release → `released`

Routes under `requests.*` (Faculty only). Accounting: `accounting.requests*`. Admin: `admin.requests*`. Supply: `supply.releases*`.

### Student purchase

Shop cart (session) → checkout → `payment_submitted` → optional receipt upload → Accounting verify (reserve) → `payment_verified` → Supply release → `released`

Student: `shop.*`, `purchases.*`. Accounting: `accounting.payments*`. Supply: `supply.purchases*`.

---

## Notifications & email

`app/Services/NotificationService.php`:

1. Creates `PsisNotification` (in-app)
2. Sends `App\Mail\PsisNotificationMail` if `config('psis.mail_notifications')` is true

Email failures must **not** abort business transactions (catch + log).

Env flags: `MAIL_*`, `PSIS_MAIL_NOTIFICATIONS`.

---

## AI module

- **Not an external LLM** — rule-based / analytics in `app/Services/AiInsightService.php`
- Chat: `AiAssistantController` + `POST /ai/ask`
- Floating widget: `resources/views/partials/ai-chat-widget.blade.php` (included in PSIS layout)
- Restock page: `/ai/restock` (Supply / Admin)
- Daily alert: `php artisan psis:low-stock-alert` (scheduled in `bootstrap/app.php` at 08:00)

Role-aware suggestions: `AiInsightService::chatSuggestions()`.
Keep AI answers grounded in DB data; do not invent stock numbers.

---

## UI conventions

- Use `layouts.psis` for authenticated pages (not Breeze `app` layout) for new screens
- Reuse classes: `psis-card`, `psis-btn-primary`, `psis-btn-outline`, `psis-input`, `psis-label`
- Brand colors: `#0B3C91` (blue), `#F4B400` (gold)
- Tables: give **every** `<th>` and `<td>` the same horizontal padding (`px-4 py-3`); avoid “header-only padding” bugs
- Sidebar active state: use `PsisMenu::isActive()` — Faculty **My Requests** vs **New Request** must stay mutually exclusive
- Assets: `public/images/pecit-logo.png`, `public/images/chatbot.png`
- After CSS/JS changes under `resources/`, run `npm run build` (or `npm run dev`)

---

## Auth & session pitfalls

- Base controller uses `AuthorizesRequests` (`app/Http/Controllers/Controller.php`) — required for `$this->authorize()`
- `SessionTimeout` middleware uses **session** `last_activity_at` (not only DB) so old DB stamps do not kick users out immediately after login
- Login must reset activity: see `AuthenticatedSessionController@store`
- New admin-created users should get `email_verified_at` set so `verified` middleware allows login
- User model casts `password` as `hashed` — pass plain password on create/update (do not double `Hash::make`)

---

## Blade pitfalls already seen

- Short `@php(...)` cannot contain multiple statements / `;` — use `@php ... @endphp` blocks
- Do not `@include` a full `@extends` view inside another layout page (breaks nested sections)
- `.env` values with spaces must be quoted: `MAIL_PASSWORD="xxxx xxxx"`

---

## Demo credentials

All passwords: `password`

- admin@pecit.edu.ph
- accounting@pecit.edu.ph
- supply@pecit.edu.ph
- faculty@pecit.edu.ph
- student@pecit.edu.ph

Seeders: `RoleAndPermissionSeeder`, `MasterDataSeeder`, `DemoUsersSeeder`, `DemoInventorySeeder`.

---

## What is intentionally incomplete

- Laravel Sanctum token API (session `/api/*` under web auth exists only)
- Broad domain PHPUnit coverage (mostly Breeze auth tests)
- External LLM integration
- Public self-registration (admin creates users)

Do not add Sanctum/LLM unless the user asks.

---

## Agent working rules for this repo

1. **Match existing patterns** — Blade/Tailwind/Alpine, service layer, Spatie role names.
2. **Minimal diffs** — change only what the task needs; avoid drive-by refactors.
3. **Do not commit** unless the user explicitly asks.
4. **Do not put secrets** in git; never echo real mail passwords into README/chat logs.
5. Prefer fixing inventory/request logic in **Services**, not duplicating in controllers/views.
6. When adding notifications, use `NotificationService` so email stays in sync.
7. When adding sidebar links, update `PsisMenu` and ensure `isActive()` behaves correctly for sibling routes.
8. Run `npm run build` after changing `resources/css` or `resources/js` if the user needs to see UI changes under `php artisan serve`.

---

## Common commands

```bash
php artisan serve
php artisan migrate --seed
php artisan storage:link
php artisan config:clear
php artisan view:clear
php artisan psis:low-stock-alert
npm run build
```

Windows scheduler (optional): run `php artisan schedule:run` every minute for daily low-stock emails.

---

## Key files checklist

| Task | Start here |
|------|------------|
| Routes / roles | `routes/web.php` |
| Sidebar | `app/Support/PsisMenu.php` |
| Stock math | `app/Services/InventoryService.php` |
| Faculty flow | `app/Services/SupplyRequestService.php` |
| Student flow | `app/Services/PurchaseRequestService.php` |
| AI | `app/Services/AiInsightService.php` |
| Email + bell | `app/Services/NotificationService.php` |
| Layout / FAB | `resources/views/layouts/psis.blade.php`, `partials/ai-chat-widget.blade.php` |
| App config bootstrap | `bootstrap/app.php` |
