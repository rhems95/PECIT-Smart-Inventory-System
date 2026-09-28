# PSIS System Flowcharts

**System:** PECIT Smart Inventory System (PSIS)  
**Purpose:** Documentation of end-to-end process flows by role and module  

Render Mermaid diagrams in GitHub, Cursor/VS Code Markdown preview, or [mermaid.live](https://mermaid.live) (export PNG/SVG for reports).

Related: [`ER-DIAGRAM.md`](ER-DIAGRAM.md)

There is **no public self-registration**. Staff use email + password; students use last name + Student ID. AI chat uses live data (`AiInsightService`); listed questions are rule-based and optional **local Ollama** (`OllamaChatService`, localhost only) can word free-typed answers. It is not a cloud LLM. Stock Card shows **physical** `transactions` only (reserve/restore hidden). Supplier and optional unit cost live on the **movement**, not on the item. Typed PO / DR numbers on stock-in are receiving text, not a purchase-order module.

---

## 1. System overview (login → role dashboards)

```mermaid
flowchart TD
    A([User opens PSIS]) --> B{Login tab}
    B -->|Staff| C[Email + password]
    B -->|Student| D[Last name + Student ID]
    C --> E{Authenticated?}
    D --> E
    E -->|No| B
    E -->|Yes| F{Email verified?}
    F -->|No| G[Verify email]
    G --> F
    F -->|Yes| H{Account active?}
    H -->|No| I([Access blocked])
    H -->|Yes| J{Session timed out?}
    J -->|Yes| B
    J -->|No| K[Dashboard]

    K --> L{Role}
    L -->|Administrator| M[Users / Approvals / Master data / Suppliers / Units / Stock / Reports / Audit / AI / work queue]
    L -->|Admission| N[Dashboard / Inventory view / Stock Card / Approve requests]
    L -->|Accounting| O[Review faculty requests / Verify payments / Reports / Inventory + Stock Card]
    L -->|Supply Personnel| P[Work queue / Stock ops / Release / Inspect / Students / Users / Master data / Suppliers / Units / Audit]
    L -->|Faculty| Q[Submit and track supply requests / View inventory]
    L -->|Student| R[Shop / Cart / Size / Pay / Track / Cancel until release]

    M --> S[Notifications + AI question list]
    N --> S
    O --> S
    P --> S
    Q --> S
    R --> S
```

Students never receive an Inventory or Stock Card menu. Faculty can view inventory but cannot open the Stock Card or change stock. **Pending Requests** on the dashboard is Accounting / Admin / Faculty work; Supply’s queue is ready-to-release + inspect (see §4a).

---

## 2. Faculty supply request workflow

```mermaid
flowchart TD
    A([Faculty]) --> B[Select items + quantities]
    B --> C[Submit supply request]
    C --> D[Status: pending]
    D --> E[Notify Accounting]
    E --> F{Faculty cancels?}
    F -->|Yes| G([Status: cancelled — no stock change])
    F -->|No| H[Accounting reviews quantities / prices]
    H --> I{Accounting decision}
    I -->|Forward| K[Status: admin_review]
    K --> L[Notify Admission + Administrator]
    L --> M{Admission or Admin decision}
    M -->|Reject| N[Status: rejected]
    N --> O[Notify Faculty]
    M -->|Approve| P[Reserve stock per line item]
    P --> Q[Status: approved]
    Q --> R[Notify Faculty + Supply Personnel]
    R --> S{Faculty cancels before release?}
    S -->|Yes| T[Restore reserved / status cancelled]
    S -->|No| U[Supply Personnel releases items]
    U --> V[Deduct on-hand and reserved / physical ledger row]
    V --> W([Status: released])
    W --> X[Notify Faculty]

    style P fill:#fff3cd
    style T fill:#f8d7da
    style V fill:#d1e7dd
```

**Stock rule:** Submit does **not** deduct stock. **Approve** reserves. **Release** deducts. **Cancel after approve** restores reserved.

| Status | Actor | Stock effect |
|--------|--------|--------------|
| `pending` | Faculty | None |
| `accounting_review` / `admin_review` | Accounting → Admission / Admin | None |
| `approved` | Admission or Administrator | Reserve (`transactions.type = reserve`, hidden on Stock Card) |
| `released` | Supply Personnel | Deduct on-hand and reserved (`type = release`, shown on card) |
| `cancelled` while pending / admin_review | Faculty | None |
| `cancelled` while approved | Faculty (or Admin) | Restore reserved (`type = restore`, hidden on card) |
| `rejected` | Admission / Admin | None |

---

## 3. Student purchase workflow

```mermaid
flowchart TD
    A([Student]) --> B[Browse shop: own department exclusives + shared]
    B --> C[Choose size for clothing uniforms]
    C --> D[Add items to cart]
    D --> E{Cart valid + size stock + department allowed?}
    E -->|No| C
    E -->|Yes| F[Checkout]
    F --> G[Create purchase + payment record]
    G --> H[Status: payment_submitted]
    H --> I[Notify Accounting]
    I --> J[Student uploads receipt optional]
    J --> K{Student cancels?}
    K -->|Yes before verify| Z([Cancelled — no stock change])
    K -->|No| L[Accounting verifies payment]
    L --> M{Payment OK?}
    M -->|No| N[Remains / follow-up]
    M -->|Yes| O[Reserve stock for size]
    O --> P[Status: payment_verified]
    P --> Q[Notify Student + Supply Personnel]
    Q --> R{Student cancels?}
    R -->|Yes| S[Restore reserved size stock / cancelled]
    R -->|No| T[Supply Personnel releases purchase]
    T --> U[Deduct reserved size stock / physical ledger row]
    U --> V([Status: released])
    V --> W[Notify Student]

    style O fill:#fff3cd
    style S fill:#f8d7da
    style U fill:#d1e7dd
```

| Status | Actor | Stock effect |
|--------|--------|--------------|
| `pending` → `payment_submitted` | Student checkout | None |
| `payment_verified` | Accounting | Reserve (per size when clothing) |
| `released` | Supply Personnel | Deduct |
| `cancelled` before verify | Student | None |
| `cancelled` after verify | Student / Accounting / Admin | Restore reserved (including size) |

**Shop listing:** students only see items with `student_shop = true` that are either **shared** (`department_id` null: P.E., NSTP, lanyard) or **exclusive to their department** (CCS, CC, CTHM, CTE, CBA, or SHS). Other departments’ exclusives are hidden; cart and checkout reject them. Discontinued items are hidden.

```mermaid
flowchart TD
    A[Student opens Uniform Shop] --> B{student_shop = true and not discontinued?}
    B -->|No| H([Hidden])
    B -->|Yes| C{department_id null?}
    C -->|Yes| D[Show shared: P.E. / NSTP / lanyard]
    C -->|No| E{item.department_id = student.department_id?}
    E -->|Yes| F[Show exclusive uniform]
    E -->|No| H
    D --> G[Choose size if clothing]
    F --> G
    G --> I[Add to cart / checkout]
    I --> J{Still same department?}
    J -->|No| K([Reject])
    J -->|Yes| L[Purchase flow continues]
```

---

## 4. Inventory stock lifecycle and Stock Card

```mermaid
flowchart LR
    A[Supply: Stock In + source + optional supplier / PO / DR / unit cost] --> B[(On Hand quantity)]
    C[Supply: Adjust / damage / bad order / return by size] --> B
    B --> D{Available = On Hand − Reserved per size or item}
    D --> E[Admission/Admin approve / Accounting verify payment]
    E --> F[(Reserved quantity ↑)]
    F --> G[Supply release]
    G --> H[(On Hand ↓ and Reserved ↓)]
    H --> I[Physical row on Stock Card]
    B --> J{Below minimum?}
    J -->|Yes| K[Low-stock alert / AI restock tips]
    J -->|No| D
```

```mermaid
flowchart TD
    subgraph Available calc
        OH[On Hand] --> AV[Available]
        RQ[Reserved] --> AV
        AV --> CHECK{Enough for action?}
    end
    CHECK -->|Reserve| R1[reserved_quantity += qty]
    CHECK -->|Release| R2[quantity -= qty<br/>reserved_quantity -= qty]
    CHECK -->|Stock In| R3[quantity += qty]
    CHECK -->|Damage / bad order / return / stock-out| R4[quantity -= qty from available]
    CHECK -->|Restore| R5[reserved_quantity -= qty]
    CHECK -->|No| ERR([Runtime error: insufficient stock])
```

**Stock-in sources** (`StockSourceType` on the form): Manual / external, Purchase order (supplier + PO number **required**), Emergency purchase (reference **required**), Donation, Opening balance, Other. Purchase-order source is **not** a PO module.

**Stock Card** (`StockCardService`): reads `transactions` with physical types only; filters by date, type, supplier, and reference. Admin, Admission, Accounting, and Supply may open it. Faculty cannot.

---

## 4a. Supply / Admin dashboard work queue

Not a table — a **view** of live rows Supply must act on. Faculty, Accounting, Admission, and Student dashboards do **not** show this block. Administrator keeps **Pending Requests** / **Approved** KPI cards **and** this queue.

```mermaid
flowchart TD
    A([Supply or Admin opens Dashboard]) --> B[KPI: Ready to release / Inspect today / Reserved / Today's movements]
    B --> C{Faculty requests status approved or reserved?}
    C -->|Yes oldest first| D[Ready to release — Faculty]
    D --> E[supply.releases.show]
    E --> F[Release: deduct on-hand, clear reserved]
    B --> G{Student purchases status payment_verified?}
    G -->|Yes oldest first| H[Ready to release — Students]
    H --> I[supply.purchases.show]
    I --> J[Release: deduct on-hand, clear reserved]
    B --> K{Stock-in or purchase_delivery inspection pending or null?}
    K -->|Yes| L[Inspect deliveries]
    L --> M[supply.purchase-history]
    M --> N[Mark correct or wrong item]
    B --> O{Item at or below minimum?}
    O -->|Yes| P[Actionable low stock]
    P --> Q[supply.stock.index with item preselected]
    Q --> R[Stock In]
```

| Queue | Source | Status / filter | Next screen |
|-------|--------|-----------------|-------------|
| Faculty ready | `requests` | `approved` or `reserved` | Release Items |
| Student ready | `purchase_requests` | `payment_verified` | Student Purchases |
| Inspect today | `transactions` (stock-in / purchase delivery) | `inspection_status` pending or null | Purchase History |
| Low stock | `inventory` | available ≤ minimum | Stock Operations (`?item=`) |

Pending faculty requests (`pending` / `accounting_review` / `admin_review`) stay with Accounting then Admission/Admin. They are **not** Supply’s dashboard queue.

---

## 5. Cross-cutting support flows

```mermaid
flowchart TD
    A[Any state-changing action] --> B[AuditLogService]
    B --> C[(audit_logs)]

    A --> D[NotificationService]
    D --> E[(psis_notifications)]
    D --> F{Mail enabled?}
    F -->|Yes| G[Email to user]
    F -->|No| E

    H[Scheduled: psis:low-stock-alert at 08:00] --> D
    I[User asks AI: list or typed] --> J[AiInsightService live facts]
    J --> K{Listed question or keyword match?}
    K -->|Yes| L[Rule-based answer from DB]
    K -->|No| M{Ollama on localhost?}
    M -->|Yes| N[OllamaChatService wording]
    M -->|No / down| O[Question-list help]
    J --> DEM[Monthly demand memory: faculty requested + student purchased]
    DEM --> REP[Reports stacked bar + 12-month trend]
    DEM --> RST[Restock tips / predicted restock]
```

Audit logs record logins, inventory/stock, users, master data, and profile changes.

---

## 6. Administrator, Admission, and Supply master data

```mermaid
flowchart TD
    A([Administrator]) --> B[Manage users + roles]
    A --> C[Departments / Categories]
    A --> SUP[Suppliers]
    A --> UOM[Units of measurement]
    A --> D[Announcements]
    A --> E[Approve / reject faculty requests]
    A --> F[View audit logs]
    A --> G[Reports PDF / Excel / issuance log / most-requested]
    A --> H[AI restock insights]
    A --> SC[Stock Card / stock operations]
    A --> WQ[Dashboard work queue]

    AD([Admission]) --> E
    AD --> IV[View inventory + Stock Card + dashboard]

    SP([Supply Personnel]) --> B
    SP --> C
    SP --> SUP
    SP --> UOM
    SP --> D
    SP --> F
    SP --> ST[Students add / CSV]
    SP --> SK[Stock in / out / damage / return by size]
    SP --> SC
    SP --> WQ
    SP --> PH[Purchase History inspect]

    B --> I[(users + Spatie roles)]
    C --> J[(master tables → inventory)]
    SUP --> TXN[(transactions.supplier_id)]
    UOM --> INV[(inventory.unit_of_measurement_id)]
    D --> K[Shown on dashboards]
    E --> L[See Faculty workflow]
    F --> M[(audit_logs)]
    G --> N[Operational decisions]
    SK --> SZ[(inventory_size_stocks + transactions)]
```

**Canonical departments:** CCS, CC, CTHM, CTE, CBA, SHS, ADMIN, SUPPLY. Academic codes drive Uniform Shop exclusivity; ADMIN and SUPPLY are staff home departments. CSV import `department_code` uses CCS, CC, CTHM, CTE, CBA, SHS.

---

## 7. Swimlane summary (who does what)

```mermaid
flowchart TB
    subgraph Faculty
        F1[Submit request] --> F2[Cancel if still open]
        F3[Receive release notification]
        F4[View inventory — no Stock Card]
    end

    subgraph Student
        S1[Shop: own dept + shared + size] --> S2[Checkout + receipt]
        S2 --> S2b[Cancel until release]
        S3[Receive items after release]
    end

    subgraph Accounting
        A1[Review faculty request] --> A2[Forward to Admission / Admin]
        A3[Verify student payment] --> A4[Trigger reserve]
        A5[Inventory + Stock Card + reports]
    end

    subgraph Admission
        ADM1[Approve / reject request] --> ADM2[Trigger reserve on approve]
        ADM3[Dashboard + inventory + Stock Card]
    end

    subgraph Administrator
        AD1[Approve / reject request] --> AD2[Trigger reserve on approve]
        AD3[Users / master data / suppliers / units / reports]
    end

    subgraph Supply Personnel
        SP0[Dashboard work queue]
        SP1[Stock in with source / supplier] --> SP2[Release approved or reserved requests]
        SP2 --> SP3[Release verified purchases]
        SP2b[Inspect pending deliveries]
        SP4[Users / categories / departments / suppliers / units / students / audit]
        SP5[Damage / bad order / return to supplier]
    end

    F1 --> A1
    A2 --> ADM1
    A2 --> AD1
    ADM2 --> SP0
    AD2 --> SP0
    SP0 --> SP2
    SP0 --> SP3
    SP0 --> SP2b
    SP2 --> F3
    S2 --> A3
    A4 --> SP3
    SP3 --> S3
```

---

## 8. Export tips

- Paste any diagram into [mermaid.live](https://mermaid.live) → **Export PNG/SVG** for Word/PDF chapters  
- GitHub renders Mermaid in Markdown automatically after push  
- For thesis/docs, use **§2 Faculty** and **§3 Student** as the two primary process chapters; use **§1** as the system context diagram; use **§4** for Stock Card / stock-in sources; use **§4a** for the Supply dashboard work queue  

---

*Keep this file aligned with `SupplyRequestService`, `PurchaseRequestService`, `InventoryService`, `StockCardService`, `AiInsightService`, `OllamaChatService`, `DashboardController` (Supply/Admin work queue), and the canonical departments (CCS, CC, CTHM, CTE, CBA, SHS, ADMIN, SUPPLY) when workflows change. Last aligned 28 September 2026.*
