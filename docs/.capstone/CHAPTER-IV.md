# CHAPTER IV
# SYSTEM IMPLEMENTATION, RESULTS, AND DISCUSSION

This chapter presents the PECIT Smart Inventory System (PSIS) as implemented. It describes the running features and modules, the MySQL schema, the user interface, how the system was installed on the campus stack, and the tests that were actually executed. Where a college instrument (signed user-acceptance forms or ISO/IEC 25010 questionnaires) has not yet been administered, the chapter states that fact and does not invent ratings (ISO, 2023).

## 4.1 System Overview

PSIS is a Laravel 12 web application for PECIT. It records school supplies and student uniforms in one database (`pecit_sis`), routes faculty requisitions and student purchases through the correct offices, and shows a Stock Card of physical movements. It is not an enterprise resource planning (ERP) warehouse package and not a procurement or inventory-costing engine (Kieso et al., 2022; Laudon & Laudon, 2024).

The implemented architecture matches Chapter III: Blade and Tailwind on `layouts.psis`; thin controllers; stock and workflow logic in services; Spatie roles on authenticated routes; MySQL/MariaDB through XAMPP. Users open `/login`. After a verified, active session, each role sees a dashboard and a sidebar from `PsisMenu`.

The six roles in the running system are Administrator, Admission, Accounting, Supply Personnel, Faculty, and Student. Staff authenticate with email and password. Students authenticate with last name and Student ID (`employee_id`). Students have no `/inventory` routes; they buy only through the Uniform Shop.

Stock follows the rule documented in Chapters I–III. Submit does not deduct. Admission or Administrator approval of a faculty request, or Accounting verification of a student payment, **reserves** quantity. Supply **release** reduces on-hand quantity and clears the matching reserved quantity. Cancel before release restores reserved stock, including per-size rows for clothing uniforms. Available quantity is on-hand minus reserved and is not stored as its own column (Heizer et al., 2023; Slack et al., 2022).

## 4.2 System Features

The implemented features are the following.

1. **Dual login and session control.** Staff and Student tabs on one login page. `verified`, `active`, and `session.timeout` middleware on authenticated routes. Public self-registration is disabled.

2. **Role-based menus and policies.** Each Spatie role receives only its modules. InventoryPolicy allows Faculty to view items but not to mutate stock or open the Stock Card. Students are excluded from inventory. Admission may view inventory, open the Stock Card, and approve or reject faculty requests, but has no users, stock operations, or master-data menus (ISO, 2022).

3. **Inventory master.** Item code, name, category, unit of measurement, selling price, on-hand, reserved, minimum stock, location, status, Uniform Shop flag, and optional exclusive department. Clothing shop items store on-hand and reserved per size (XS–3XL) on `inventory_size_stocks`. Parent `inventory.quantity` and `reserved_quantity` are sums of those rows.

4. **Faculty supply requests.** Create, track, and cancel (until release). Path: `pending` → Accounting review → `admin_review` → approve (reserve) or reject → Supply release (deduct). An order-status tracker is shown on the request screens.

5. **Uniform Shop and student purchases.** Listing uses `student_shop` plus department exclusivity (null department = shared; set department = that college only). Cart and checkout repeat the same checks. Clothing lines require a size. Checkout writes `purchase_requests` and `payments` with no stock change. Optional receipt upload (JPG, PNG, WEBP, PDF, maximum 5 MB). Accounting verify reserves. Supply release deducts. Students may cancel until release.

6. **Stock operations.** Stock-in (source, optional supplier, optional purchase-order or delivery-receipt number as text, optional unit cost, size when required), stock-out, adjustment, damage, bad order, and return to supplier. Insufficient available quantity is rejected. A reason is required for deductions.

7. **Stock Card.** Built from `transactions` for physical types only. Reserve and restore rows are logged for control and hidden on the card. Filters include date range, type, and size.

8. **Suppliers and units of measurement.** Administrator and Supply Personnel maintain `suppliers` and `units_of_measurement`. Supplier is recorded on the movement, not as a single `supplier_id` on the item.

9. **Student accounts.** Supply or Administrator create and edit Student-role users and import CSV (`student_id,last_name,name,email,department_code,phone`). Department codes on import are CCS, CC, CTHM, CTE, CBA, and SHS.

10. **Notifications and mail.** In-app `psis_notifications`. Optional email through `NotificationService` when `PSIS_MAIL_NOTIFICATIONS` is true. Mail failure does not roll back stock.

11. **Reports.** PDF and Excel exports for low stock, out of stock, valuation, daily and monthly inventory, faculty requests, student purchases, audit trail, and transactions.

12. **Audit logs.** Who, action, record, and details for logins, inventory/stock, users, master data, and profile changes.

13. **AI assistant.** Question list plus a type box. Listed questions and keyword matches are answered by `AiInsightService` from live tables. Free-typed questions may be worded by optional local Ollama (`OllamaChatService` at `http://127.0.0.1:11434`) using an injected fact snapshot. Restock tips at `/ai/restock` for Supply and Administrator. Daily command `psis:low-stock-alert` at 08:00. There is no cloud large language model.

14. **Support UI.** Announcements, profile, dark mode, local colored sidebar icons, and clickable dashboard cards (queues, released items, recent student purchases for Supply/Admin, recent verified payments for Accounting).

Features that were **not** implemented, matching the delimitations: FIFO or lot costing, moving-average cost, a purchase-order document module, barcode scanning, a native mobile application, and a JSON API (Kieso et al., 2022; Republic of the Philippines, 2024).

## 4.3 System Modules

Table 4.1 maps the running modules to routes and primary services.

**Table 4.1**  
*Implemented modules of PSIS*

| Module | Who uses it | Main routes / views | Service or policy |
|--------|-------------|---------------------|-------------------|
| Authentication | All | `/login`, Breeze auth | `LoginRequest`; Student vs staff |
| Dashboard | All | `/dashboard` | Role-specific cards and lists |
| Inventory | Admin, Admission, Accounting, Supply, Faculty (view) | `inventory.*` | `InventoryController`; `InventoryPolicy` |
| Stock Card | Admin, Admission, Accounting, Supply | `inventory.stock-card` | `StockCardService` / `transactions` |
| Stock operations | Supply, Administrator | `supply.stock.*` | `InventoryService` |
| Faculty requests | Faculty | `requests.*` | `SupplyRequestService` |
| Accounting review | Accounting, Administrator | `accounting.requests*` | Review quantities/prices |
| Approve requests | Administrator, Admission | `admin.requests*` | Approve = reserve; reject |
| Release (faculty) | Supply, Administrator | `supply.releases*` | Release = deduct |
| Uniform Shop | Student | `shop.*` | `ShopController`; `Inventory::scopeForStudentShop` |
| Student purchases | Student | `purchases.*` | `PurchaseRequestService` |
| Verify payments | Accounting, Administrator | `accounting.payments*` | Verify = reserve |
| Release (purchases) | Supply, Administrator | `supply.purchases*` | Release = deduct |
| Students | Supply, Administrator | `supply.students.*` | `StudentAccountService` |
| Users | Administrator, Supply | `admin.users.*` | All roles (Admin form); Supply also uses student module |
| Categories, departments, suppliers, units, announcements | Administrator, Supply | `admin.categories.*`, `departments.*`, `suppliers.*`, `units.*`, `announcements.*` | Master data |
| Reports | Administrator, Accounting, Supply | `reports.*` | DomPDF; Laravel Excel |
| Audit logs | Administrator, Supply | `admin.audit-logs` | `AuditLogService` |
| AI chat / restock | All roles (restock: Supply, Admin) | `/ai/chat`, `POST /ai/ask`, `/ai/restock` | `AiInsightService`; optional `OllamaChatService` |
| Notifications | All | `/notifications` | `NotificationService` |

Admission’s implemented sidebar is dashboard, inventory (view + Stock Card), approve requests, AI assistant, and notifications/profile as provided by the layout—not users, stock, students, reports, or audit logs.

## 4.4 Database Implementation

The live database is MySQL/MariaDB schema `pecit_sis`, created by Laravel migrations. A structure-and-data dump is kept in `db/pecit_sis.sql`. Table 4.2 lists the domain tables. Laravel infrastructure tables (`sessions`, `cache`, `jobs`, `failed_jobs`, `password_reset_tokens`) exist but are not business entities.

**Table 4.2**  
*Domain tables implemented in pecit_sis*

| Table | Role in the running system |
|-------|----------------------------|
| `users` | All accounts. `employee_id` is Student ID; `last_name` is used for student login; `department_id` drives shop exclusivity |
| `departments` | Codes CCS, CC, CTHM, CTE, CBA, SHS, ADMIN, SUPPLY |
| `categories` | Item classification (includes Uniforms) |
| `units_of_measurement` | UoM master; `inventory.unit_of_measurement_id` |
| `suppliers` | Vendor master; linked from `transactions.supplier_id` |
| `inventory` | Item master: on-hand, reserved, shop flag, exclusive department, selling `unit_price` |
| `inventory_size_stocks` | Per-size on-hand and reserved (unique item + size) |
| `inventory_price_adjustments` | History when selling price is changed |
| `requests` / `request_items` | Faculty (and restock) requisitions; no size on faculty lines |
| `purchase_requests` / `purchase_request_items` | Student orders; `size` on clothing lines |
| `payments` | Amount, method, `receipt_path`, verified_by |
| `transactions` | Stock ledger / Stock Card source |
| `stock_logs` | Additional delivery / stock-in action log |
| `psis_notifications` | In-app notices |
| `audit_logs` | Polymorphic audit trail |
| `announcements` | Campus notices on the dashboard |
| `roles` / `permissions` / pivots | Spatie RBAC |

`inventory` has **no** `supplier_id` and **no** barcode column in the current schema. Available quantity is computed in the model.

`transactions` as implemented includes `type` (varchar), `quantity`, `quantity_before`, `quantity_after`, `quantity_in`, `quantity_out`, `balance_after`, `source_type`, `unit_cost`, `total_cost`, `supplier_id`, `reference_number`, `delivery_receipt_number`, `size`, `transaction_date`, and polymorphic `reference_type` / `reference_id`. Physical types include opening balance, stock-in, stock-out, release, adjustment, damage, bad order, return to supplier, and purchase delivery. `reserve` and `restore` are stored but omitted from the Stock Card view.

Seeders used to initialize a demonstration database are `RoleAndPermissionSeeder`, `MasterDataSeeder`, `DemoUsersSeeder`, and `DemoInventorySeeder`. Academic department codes that had been CIT, COE, and COB were remapped to CCS, CC, and CBA in a later migration.

## 4.5 User Interface Presentation

All authenticated pages use `resources/views/layouts/psis.blade.php`. Visual constants are PECIT blue `#0B3C91` and gold `#F4B400`, shared button and input classes, and local SVG icons (no icon CDN). Tables use the same horizontal padding on header and body cells. Dark mode is a layout preference.

Figure captions below are the screens the printed manuscript should show. Capture them from the running application (XAMPP or `php artisan serve`) using the demo accounts in the README, and insert the PNG files under each caption. Do not substitute mock-ups that show FIFO, a purchase-order document, or a student inventory menu.

**Figure 4.1** Login — Staff and Student tabs (last name visible; Student ID masked with Show).

**Figure 4.2** Administrator dashboard — KPI cards, announcements, queues.

**Figure 4.3** Admission dashboard — inventory view and approve-request entry; no Users or Stock Operations.

**Figure 4.4** Inventory list — On Hand, Reserved, Available; status filter; Stock Card link for allowed roles.

**Figure 4.5** Stock Card — physical in/out, running balance, date/type/size filters.

**Figure 4.6** Stock operations — stock-in sources, supplier, PO/DR text, unit cost, size; deduct forms with reason.

**Figure 4.7** Faculty New Request and My Requests with status tracker.

**Figure 4.8** Accounting review and Verify Payments (recent verified payments list).

**Figure 4.9** Uniform Shop for a CCS student (CCS exclusive + shared) versus a CC student (CC exclusive + shared).

**Figure 4.10** Cart, checkout, payment slip, and purchase detail with tracker.

**Figure 4.11** Supply release screens for faculty requests and student purchases.

**Figure 4.12** Student CSV import; suppliers; units of measurement.

**Figure 4.13** Reports index and AI assistant (question list plus type box).

**Figure 4.14** Audit logs (who / action / record / details).

The layout includes a floating AI widget on authenticated pages. Faculty **My Requests** and **New Request** remain mutually exclusive in the sidebar (`PsisMenu::isActive()`).

## 4.6 System Implementation

Implementation used the XAMPP stack on Windows (PHP 8.2+, Apache, MySQL) and the project path `C:\xampp\htdocs\pecit-sis`. The application layer is Laravel 12. Front-end assets are built with Vite (`npm run build`) so Figtree and Chart.js are bundled.

Typical install sequence used on the demonstration host:

1. `composer install`
2. Copy `.env`; set `DB_DATABASE=pecit_sis`; `php artisan key:generate`
3. Create the database; `php artisan migrate --seed`
4. `php artisan storage:link` (receipt files)
5. `npm install` and `npm run build`
6. `php artisan serve` or Apache document root to `public/`

Brand files are `public/images/pecit-logo.png` and `public/images/chatbot.png`. Optional mail uses `MAIL_*` and `PSIS_MAIL_NOTIFICATIONS`. Optional scheduler: `php artisan schedule:run` so `psis:low-stock-alert` can run at 08:00.

Code organization matches the design in Chapter III. `InventoryService` owns stock-in, stock-out, damage, bad order, return to supplier, reserve, release, restore, and adjust. `SupplyRequestService` and `PurchaseRequestService` call those methods at approve/verify and release. Controllers do not duplicate the quantity formulas. Git is used in the project folder; `.env` secrets are not committed.

Versioned construction (from the project changelog) placed core workflows first, then uniform sizes and the Admission role, then suppliers, units of measurement, and the Stock Card foundation. The reserve-then-release rule did not change between those drops.

## 4.7 Testing Results

Tests were run on 11 September 2026 with `php artisan test` (PHPUnit 11, in-memory SQLite as configured in `phpunit.xml`). Result: **66 tests passed, 252 assertions, duration 6.96 seconds.** No test in that run failed.

A role-by-role quality checklist (login, inventory, stock, faculty requests, student shop, payments, menus) is documented for manual walkthrough against `AGENTS.md`. This chapter reports the automated suite as completed evidence. Signed paper UAT forms, if the college requires them, should be attached as appendices when collected.

### Functional Testing

Table 4.3 groups the PHPUnit feature tests that exercise PSIS domain behavior. Breeze leftover tests (password reset, email verification, profile) also passed in the same run; they confirm the auth scaffolding still boots.

**Table 4.3**  
*Functional test groups (all passed in the 11 September 2026 run)*

| Area | What was asserted |
|------|-------------------|
| Login | Login screen renders; staff email/password; student ID + last name; students cannot use staff email login; student tab kept after validation error; logout; root redirects to login; registration disabled |
| Inventory | Supply/Admin can create items; shop clothing requires a size (no 500 error without size); per-size on-hand on edit; cannot set on-hand below reserved; unused items may be deleted by Supply/Admin; Faculty cannot delete; item on a faculty request cannot be deleted; create route is not captured as an inventory id |
| Stock Card / ledger | Stock-in writes a physical row; reserve does not change on-hand and is hidden on the card; release writes `release` not a generic stock-out; restore is not shown as stock-in; Faculty cannot open the card; Supply can; stock-out requires a reason; damage deducts available; return to supplier requires supplier and deducts only the returned quantity; students cannot manage suppliers |
| Faculty / student cancel | Faculty can cancel during `admin_review`; cancelling an approved request restores reserved; cancelling a verified purchase restores reserved size stock; released requests cannot be cancelled |
| Shop / tracker | Purchase and request show pages include the status tracker; released steps complete; rejected requests show as stopped |
| Dashboards / audit | Supply dashboard lists recent student purchases with department; Faculty dashboard does not; Accounting dashboard lists recent verified payments; audit page lists who changed what |
| AI | Chat lists questions and has no type box; listed questions are accepted; free-typed questions are rejected |
| Layout | Authenticated layout uses local colored sidebar icons |

These results support the specific objectives on three-quantity stock, reserve-then-release, size stock, and a physical Stock Card. They do not, by themselves, prove every click-path in the Uniform Shop listing for all six academic departments; that remains a manual check on the seeded shop (CCS vs CC demo students, plus CTHM, CTE, CBA, SHS exclusives when those items are seeded).

### Non-Functional Testing

| Concern | Evidence in this build | Limit of the evidence |
|---------|------------------------|------------------------|
| Security / access | Role middleware on routes; InventoryPolicy; registration disabled; AI rejects unknown questions; hashed password cast; session timeout middleware | Not a paid penetration test (ISO, 2022; Romney et al., 2021) |
| Session | `SESSION_LIFETIME` default 120 minutes; activity stamp on login | Idle logout was not timed with a stopwatch in the PHPUnit run |
| Mail | Tests force `MAIL_MAILER=array` and `PSIS_MAIL_NOTIFICATIONS=false` | Live SMTP delivery was not measured in this run |
| Assets / usability | Vite-bundled fonts and Chart.js; Alpine-optional login tabs and selected forms (changelog) | No formal SUS questionnaire in this draft |
| Performance | Suite finished in 6.96 s on the development host | Not a multi-user load test |
| Compatibility | Developed and tested on Windows 10 with Chromium-based browsing for implementation | No separate iOS/Android native client (out of scope) |

### User Acceptance Testing

Formal UAT with Likert-scale forms signed by PECIT offices has **not** been filed in the project repository as of this chapter. Until those instruments are collected, the researchers do not report a mean “acceptability” score.

What can be stated now is the **acceptance setup** that the demonstration database already provides: staff `admin@`, `admission@`, `accounting@`, `supply@`, and `faculty@pecit.edu.ph` (password `password`); students `STU-001` / Santos (CCS) and `STU-CC-001` / Mendoza (CC). A walkthrough using those accounts is the operational UAT path described in Chapter III. When the college panel requires ISO/IEC 25010 questionnaires after that walkthrough, the blank forms may follow Tungcul and Kummer (2021) as a local pattern; filled tables belong in a revision of this section, not as invented numbers.

### ISO/IEC 25010 Evaluation Results (if applicable)

ISO/IEC 25010 (2023) names product quality characteristics (functional suitability, performance efficiency, compatibility, usability, reliability, security, maintainability, flexibility). Local inventory studies used that model with end-user and IT-expert raters (Tungcul & Kummer, 2021).

**This study has not yet produced ISO/IEC 25010 characteristic means or an overall compliance rating.** No table of 4.00 / 4.50 / “very great extent” scores is presented. Reporting such figures without administered questionnaires would be false. After UAT, the researchers may add one table per characteristic with *n*, mean, and interpretation. Until then, quality claims rest on (a) the passing PHPUnit suite, (b) role middleware and policies in code, and (c) the delimitations that keep FIFO, moving average, and a purchase-order module out of the product.

## 4.8 Discussion of Findings

The implementation answers the general problem in Chapter I with a single web system, not with four office workbooks (Laudon & Laudon, 2024). Findings are discussed against the specific problems and against the gap in Chapter II.

**On-hand, reserved, and available.** Tests show that reserve leaves on-hand unchanged and that release reduces on-hand. The inventory list and Stock Card header display all three figures. That matches operations guidance to separate physical stock from committed stock (Heizer et al., 2023; Slack et al., 2022). Related systems in Chapter II typically published an on-hand list and alerts, not this three-quantity model.

**Authorization before deduction.** Faculty submit and student checkout do not call deduct. Approve and payment verify call reserve. Supply release calls deduct. Cancel after reserve restores. Those paths are covered by `CancelRestoresReservedStockTest` and the ledger tests. The split of custody, authorization, and recording is the accounting-information-system control cited in Chapter I (Romney et al., 2021).

**Department shop.** The shop module and checkout service use the same exclusivity helpers. Automated tests in this run emphasize size stock, trackers, and dashboards more than a CCS-versus-CC listing assertion. The finding from code and seeded data is still that exclusivity is implemented; a printed screenshot pair (Figure 4.9) should be the exhibit for the panel.

**Ledger without costing layers.** Stock-in may store unit cost, supplier, and a typed PO or DR number. Tests show return-to-supplier needs a supplier and that reserve rows are hidden on the card. The system therefore provides an audit trail of pieces (Romney et al., 2021) without becoming FIFO or moving-average costing (Kieso et al., 2022) and without a purchase-order document (Republic of the Philippines, 2024). That is the gap named in Chapter II: university PHP–MySQL stores exist (Tjahjanto et al., 2022), but PECIT needed two client paths and a physical card on one stock.

**Roles.** Admission is implemented as an owner-level approver, not as a second Supply clerk. Students cannot manage suppliers and cannot open the Stock Card as Faculty cannot. Least privilege is visible in middleware and in the passing access tests (ISO, 2022).

**AI.** The assistant is grounded in live data (`AiInsightService`). Listed questions stay rule-based. Typed questions may be worded by optional local Ollama on this PC. Tests confirm listed answers, free text, Ollama wording, and fallback if Ollama is down or not localhost. This is not a cloud LLM and must not be described as one in defense.

**Limits of the findings.** PHPUnit uses SQLite in memory, not the XAMPP MySQL copy, so passing tests do not replace a seeded MySQL walkthrough. Coverage of every report PDF and every CSV error row is incomplete. ISO/IEC 25010 scores are absent. Those limits are reasons for the recommendations in Chapter V, not reasons to invent data.

Overall, the researchers find that the implemented PSIS matches the IPO design in Chapter III on the reserve-then-release rule, role separation, Stock Card, and shop exclusivity, and that it stays inside the delimitations on costing and procurement.

---

## References (working list for Chapter IV)

Merge with Chapters I–III into the manuscript **REFERENCES** (APA 7th Edition). All items are 2021–2026.

Heizer, J., Render, B., & Munson, C. (2023). *Operations management: Sustainability and supply chain management* (14th ed.). Pearson.

International Organization for Standardization. (2022). *ISO/IEC 27001:2022: Information security, cybersecurity and privacy protection—Information security management systems—Requirements*. ISO.

International Organization for Standardization. (2023). *ISO/IEC 25010:2023: Systems and software engineering—Systems and software quality requirements and evaluation (SQuaRE)—Product quality model*. ISO.

Kieso, D. E., Weygandt, J. J., & Warfield, T. D. (2022). *Intermediate accounting* (18th ed.). Wiley.

Laudon, K. C., & Laudon, J. P. (2024). *Management information systems: Managing the digital firm* (18th ed.). Pearson.

Republic of the Philippines. (2024). *Republic Act No. 12009: New Government Procurement Act*. Official Gazette. https://www.officialgazette.gov.ph/

Romney, M. B., Steinbart, P. J., Summers, S. L., & Wood, D. A. (2021). *Accounting information systems* (15th ed.). Pearson.

Slack, N., Brandon-Jones, A., & Burgess, N. (2022). *Operations management* (10th ed.). Pearson.

Tjahjanto, Arista, A., & Ermatita. (2022). Application of the waterfall method in information system for state-owned inventories management development. *Sinkron: Jurnal dan Penelitian Teknik Informatika, 7*(4), 2182–2191. https://doi.org/10.33395/sinkron.v7i4.11678

Tungcul, M. B., & Kummer, M. G. C. (2021). Supplies and equipment inventory, monitoring and tracking management system using data mining techniques. *International Journal of Recent Technology and Engineering, 10*(2). https://doi.org/10.35940/ijrte.B6174.0710221
