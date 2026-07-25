# PECIT Smart Inventory System (PSIS)

Centralized inventory and requisition management system for the **Philippine Electronic and Communication Institute of Technology (PECIT)**.

PSIS manages school supplies and inventory (office, classroom, laboratory, computer, cleaning, pantry, maintenance, furniture, and more) with role-based workflows for requests, purchasing, approvals, stock release, reporting, notifications, and AI-assisted insights.

---

## Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | Laravel 12, PHP 8.2+, MySQL |
| Frontend | Blade, Tailwind CSS, Alpine.js, Chart.js |
| Auth / RBAC | Laravel Breeze, Spatie Laravel Permission |
| Exports | DomPDF, Laravel Excel (Maatwebsite) |
| Other | Simple QR Code |

**Branding:** PECIT Blue `#0B3C91` · Gold `#F4B400` · Dark mode supported

---

## Requirements

- PHP 8.2+
- Composer
- Node.js & npm
- MySQL (database name: `pecit_sis`)
- XAMPP (or equivalent Apache/MySQL stack)

---

## Installation

```bash
cd pecit-sis
composer install
cp .env.example .env
php artisan key:generate
```

Configure `.env` database settings:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pecit_sis
DB_USERNAME=root
DB_PASSWORD=
```

Create the MySQL database `pecit_sis`, then:

```bash
php artisan migrate --seed
php artisan storage:link
npm install
npm run build
php artisan serve
```

Open: [http://127.0.0.1:8000](http://127.0.0.1:8000)

Place brand assets in:

```text
public/images/pecit-logo.png
public/images/chatbot.png
```

---

## Demo Accounts

Password for all demo users: `password`

| Role | Email |
|------|-------|
| Administrator | admin@pecit.edu.ph |
| Accounting | accounting@pecit.edu.ph |
| Supply Personnel | supply@pecit.edu.ph |
| Faculty | faculty@pecit.edu.ph |
| Student | student@pecit.edu.ph |

---

## User Roles & Workflows

### 1. Faculty
- View / search inventory
- Submit supply requests
- Track status, cancel pending requests
- Receive in-app + email notifications

**Flow:** Faculty Request → Accounting Review → Admin Approval → Supply Release → Inventory Deducted

### 2. Student
- Browse shop, cart, checkout
- Download payment slip (PDF)
- Upload payment receipt
- View purchase history & status

**Flow:** Purchase → OTC Payment → Receipt Upload → Accounting Verification → Supply Release → Inventory Deducted

### 3. Accounting
- Review faculty requests (approve qty / set prices)
- Verify student payments (view receipt)
- Access reports

### 4. Supply Personnel
- Inventory CRUD (with Admin)
- Stock in / inventory adjustment
- Release faculty requests & student purchases
- Restock recommendations (AI)
- Low-stock monitoring

### 5. Administrator
- Approve / reject faculty requests
- User management (create/edit, roles, departments)
- Categories, suppliers, departments, announcements
- Audit logs & full reports access

---

## Inventory Logic

Stock is **not** deducted when a request is submitted.

Correct process:

1. Request / purchase submitted  
2. Approved / payment verified → **Reserved**  
3. Supply releases items → **On-hand deducted** + reserved cleared  
4. Transaction & stock logs recorded  

If cancelled before release, reserved quantity is restored.

Inventory list shows **On Hand**, **Reserved**, and **Available**.

---

## Current Features

### Authentication & Security
- Login / logout, forgot & reset password, change password (profile)
- Email verification support
- Session timeout (idle logout)
- Active-user enforcement
- CSRF protection, validation, password hashing
- Role middleware + authorization policies (inventory, supply requests, purchases)

### Dashboard
- Stock KPIs (total items, available, low stock, out of stock)
- Pending / approved / released request counts
- Monthly transaction chart (Chart.js)
- AI restock insights + announcements feed
- Role-relevant recent activity

### Inventory
- CRUD for Admin / Supply
- Search & filters (category, status)
- Fields: code, name, description, category, unit, price, qty, min stock, supplier, location, status
- QR code on item detail
- Barcode-ready field

### Master Data (Admin)
- Categories, suppliers, departments (add / edit / delete where safe)
- Announcements (priority; shown on dashboard)
- User management with roles & departments

### Notifications
- In-app notification center (bell icon)
- Email on the same events when enabled (see Email section)
- Events: new request, reviewed, approved, rejected, released, payment submitted/verified, low-stock alerts

### Reports (PDF / Excel / on-screen)
- Daily Inventory (PDF)
- Monthly Inventory (PDF)
- Low Stock (PDF)
- Out of Stock (PDF)
- Inventory Valuation (PDF)
- Faculty Request Report (web + PDF)
- Student Purchase Report (web + PDF)
- Audit Trail (PDF)
- Transactions (Excel)

### AI Module (rule-based decision support)
- Role-aware chat assistant (Faculty / Student / Accounting / Supply / Admin)
- Floating chatbot widget (Messenger-style, uses `chatbot.png`)
- Frequent question chips by role
- Inventory forecast & reorder suggestions (90-day usage)
- Restock Tips page for Supply / Admin
- Monthly AI summary on dashboard & reports
- Daily alert command: `php artisan psis:low-stock-alert` (scheduled 08:00)

> Note: AI is keyword / analytics based (not an external LLM). Answers are grounded in database data.

### UI / UX
- Responsive PSIS layout with modern sidebar
- Dark mode toggle
- Toast-style success / error messages
- Aligned data tables, search filters
- PECIT logo on sidebar & login
- Floating AI button (bottom-right)

### Session API (browser session auth)
Under authenticated `/api`:

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/inventory` | List inventory |
| GET | `/api/inventory/{id}` | Item detail |
| GET | `/api/requests` | User supply requests |
| POST | `/api/requests` | Submit supply request |

Token API (Laravel Sanctum) is **not** required for web-only use and is not installed yet.

---

## Email Notifications

When `PSIS_MAIL_NOTIFICATIONS=true`, each in-app notification is also emailed.

### Local testing

```env
MAIL_MAILER=log
PSIS_MAIL_NOTIFICATIONS=true
```

Messages are written to `storage/logs/laravel.log`.

### Real SMTP (example — Gmail App Password)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your@gmail.com
MAIL_PASSWORD="your-16-char-app-password"
MAIL_FROM_ADDRESS="your@gmail.com"
MAIL_FROM_NAME="${APP_NAME}"
PSIS_MAIL_NOTIFICATIONS=true
```

Then:

```bash
php artisan config:clear
```

Put passwords with spaces in quotes. If SMTP fails, workflows continue; the error is logged only.

---

## Scheduled Jobs

| Command | Purpose | Schedule |
|---------|---------|----------|
| `php artisan psis:low-stock-alert` | Notify Supply & Admin of urgent stock risks | Daily 08:00 |

On Windows/XAMPP, run the scheduler every minute via Task Scheduler:

```text
php C:\xampp\htdocs\pecit-sis\artisan schedule:run
```

Or run the alert manually anytime for testing.

---

## Useful Artisan Commands

```bash
php artisan serve
php artisan migrate --seed
php artisan storage:link
php artisan config:clear
php artisan view:clear
php artisan psis:low-stock-alert
npm run build
npm run dev
```

---

## Database / ER Diagram

See the full entity-relationship documentation (Mermaid diagrams + table summary):

- [`docs/ER-DIAGRAM.md`](docs/ER-DIAGRAM.md)

View on GitHub Markdown preview or [mermaid.live](https://mermaid.live) to export PNG/SVG for reports.

## Project Structure

```text
app/
  Console/Commands/     # psis:low-stock-alert
  Http/Controllers/     # Web, Admin, Auth, API
  Mail/                 # Email notification mailable
  Models/               # Eloquent models
  Policies/             # Authorization policies
  Services/             # Inventory, requests, purchases, AI, notifications, audit
  Support/PsisMenu.php  # Role-based sidebar + active states
config/psis.php         # PSIS_MAIL_NOTIFICATIONS and related flags
database/migrations/    # Schema
database/seeders/       # Roles, master data, demo users & inventory
docs/ER-DIAGRAM.md      # ER diagrams for documentation
public/images/          # pecit-logo.png, chatbot.png
resources/views/        # Blade UI (layouts, modules, emails, AI widget)
routes/web.php          # Application routes
routes/auth.php         # Breeze auth routes
```

---

## Completion Status (approx.)

| Area | Status |
|------|--------|
| Core multi-role workflows | Complete |
| Inventory reserve / release | Complete |
| Reports | Complete |
| Notifications + email | Complete |
| AI assistant + floating chat | Complete |
| Session JSON API | Basic |
| Sanctum token API | Not included |
| Domain automated tests | Minimal (Breeze auth tests) |

Suitable for institutional demo and day-to-day PECIT inventory operations.

---

## Pre-GitHub checklist

Before pushing:

1. Confirm `.env` is **not** committed (it is in `.gitignore`)
2. Never commit real Gmail/SMTP passwords — use `.env` locally only
3. Run `npm run build` after clone (Vite assets are gitignored via `public/build`)
4. Run `php artisan migrate --seed` and `php artisan storage:link` on a fresh machine
5. Optional: `php artisan test` (auth/profile suite should pass)

```bash
git status   # ensure .env is not listed as staged
php artisan test
```

## License

MIT — Built for PECIT institutional use.
