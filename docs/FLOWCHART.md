# PSIS System Flowcharts

**System:** PECIT Smart Inventory System (PSIS)  
**Purpose:** Documentation of end-to-end process flows by role and module  

Render Mermaid diagrams in GitHub, Cursor/VS Code Markdown preview, or [mermaid.live](https://mermaid.live) (export PNG/SVG for reports).

Related: [`ER-DIAGRAM.md`](ER-DIAGRAM.md)

---

## 1. System overview (login → role dashboards)

```mermaid
flowchart TD
    A([User opens PSIS]) --> B[Login / Register]
    B --> C{Authenticated?}
    C -->|No| B
    C -->|Yes| D{Email verified?}
    D -->|No| E[Verify email]
    E --> D
    D -->|Yes| F{Account active?}
    F -->|No| G([Access blocked])
    F -->|Yes| H{Session timed out?}
    H -->|Yes| B
    H -->|No| I[Dashboard]

    I --> J{Role}
    J -->|Administrator| K[Users / Approvals / Master data / Reports / AI]
    J -->|Admission| K2[Dashboard / Inventory view / Approve requests]
    J -->|Accounting| L[Review faculty requests / Verify payments / Reports]
    J -->|Supply Personnel| M[Stock / Release / Students / Users / Master data / Audit]
    J -->|Faculty| N[Submit and track supply requests]
    J -->|Student| O[Shop / Cart / Size / Pay / Track purchases]

    K --> P[Notifications + AI assistant]
    K2 --> P
    L --> P
    M --> P
    N --> P
    O --> P
```

---

## 2. Faculty supply request workflow

```mermaid
flowchart TD
    A([Faculty]) --> B[Select items + quantities]
    B --> C[Submit supply request]
    C --> D[Status: pending]
    D --> E[Notify Accounting]
    E --> F{Faculty cancels?}
    F -->|Yes| G([Status: cancelled])
    F -->|No| H[Accounting reviews quantities / prices]
    H --> I{Accounting decision}
    I -->|Reject path via Admin| J[Status: admin_review then reject]
    I -->|Forward| K[Status: admin_review]
    K --> L[Notify Admission + Administrator]
    L --> M{Admission or Admin decision}
    M -->|Reject| N[Status: rejected]
    N --> O[Notify Faculty]
    M -->|Approve| P[Reserve stock per line item]
    P --> Q[Status: approved]
    Q --> R[Notify Faculty + Supply Personnel]
    R --> S[Supply Personnel releases items]
    S --> T[Deduct reserved stock / log transaction]
    T --> U([Status: released])
    U --> V[Notify Faculty]

    style P fill:#fff3cd
    style T fill:#d1e7dd
```

**Stock rule:** Submit does **not** deduct stock. **Approve** reserves. **Release** deducts.

| Status | Actor | Stock effect |
|--------|--------|--------------|
| `pending` | Faculty | None |
| `accounting_review` / `admin_review` | Accounting → Admission / Admin | None |
| `approved` | Admission or Administrator | Reserve |
| `released` | Supply Personnel | Deduct (from reserved) |
| `rejected` / `cancelled` | Admission / Admin / Faculty | None |

---

## 3. Student purchase workflow

```mermaid
flowchart TD
    A([Student]) --> B[Browse shop]
    B --> C[Choose size for uniforms]
    C --> D[Add items to cart]
    D --> E{Cart valid + size stock available?}
    E -->|No| C
    E -->|Yes| F[Checkout]
    F --> G[Create purchase + payment record]
    G --> H[Status: payment_submitted]
    H --> I[Notify Accounting]
    I --> J[Student uploads receipt optional]
    J --> K[Accounting verifies payment]
    K --> L{Payment OK?}
    L -->|No| M[Remains / follow-up]
    L -->|Yes| N[Reserve stock for size]
    N --> O[Status: payment_verified]
    O --> P[Notify Student + Supply Personnel]
    P --> Q[Supply Personnel releases purchase]
    Q --> R[Deduct reserved size stock / log transaction]
    R --> S([Status: released])
    S --> T[Notify Student]

    style N fill:#fff3cd
    style R fill:#d1e7dd
```

| Status | Actor | Stock effect |
|--------|--------|--------------|
| `pending` → `payment_submitted` | Student checkout | None |
| `payment_verified` | Accounting | Reserve |
| `released` | Supply Personnel | Deduct |
| `cancelled` | Allowed actors | Release reservation if any |

---

## 4. Inventory stock lifecycle

```mermaid
flowchart LR
    A[Supply Personnel: Stock In by size] --> B[(On Hand quantity)]
    C[Supply Personnel: Adjust by size] --> B
    B --> D{Available = On Hand − Reserved per size or item}
    D --> E[Admission/Admin approve / Accounting verify payment]
    E --> F[(Reserved quantity ↑)]
    F --> G[Supply release]
    G --> H[(On Hand ↓ and Reserved ↓)]
    H --> I[Transaction + Stock log]
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
    CHECK -->|No| ERR([Runtime error: insufficient stock])
```

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

    H[Scheduled: psis:low-stock-alert] --> D
    I[User asks AI widget] --> J[AiInsightService]
    J --> K[Role-aware answer / restock forecast]
```

---

## 6. Administrator, Admission, and Supply master data

```mermaid
flowchart TD
    A([Administrator]) --> B[Manage users + roles]
    A --> C[Departments / Categories]
    A --> D[Announcements]
    A --> E[Approve / reject faculty requests]
    A --> F[View audit logs]
    A --> G[Reports PDF / Excel]
    A --> H[AI restock insights]

    AD([Admission]) --> E
    AD --> IV[View inventory + dashboard]

    SP([Supply Personnel]) --> B
    SP --> C
    SP --> D
    SP --> F
    SP --> ST[Students add / CSV]
    SP --> SK[Stock in-adjust by size]

    B --> I[(users + Spatie roles)]
    C --> J[(master tables → inventory)]
    D --> K[Shown on dashboards]
    E --> L[See Faculty workflow]
    F --> M[(audit_logs)]
    G --> N[Operational decisions]
    SK --> SZ[(inventory_size_stocks)]
```

---

## 7. Swimlane summary (who does what)

```mermaid
flowchart TB
    subgraph Faculty
        F1[Submit request] --> F2[Cancel if still open]
        F3[Receive release notification]
    end

    subgraph Student
        S1[Shop + choose size + cart] --> S2[Checkout + receipt]
        S3[Receive items after release]
    end

    subgraph Accounting
        A1[Review faculty request] --> A2[Forward to Admission / Admin]
        A3[Verify student payment] --> A4[Trigger reserve]
    end

    subgraph Admission
        ADM1[Approve / reject request] --> ADM2[Trigger reserve on approve]
        ADM3[Dashboard + view inventory]
    end

    subgraph Administrator
        AD1[Approve / reject request] --> AD2[Trigger reserve on approve]
        AD3[Users / master data / reports]
    end

    subgraph Supply Personnel
        SP1[Stock in / adjust by size] --> SP2[Release approved requests]
        SP2 --> SP3[Release verified purchases]
        SP4[Users / categories / departments / students / audit]
    end

    F1 --> A1
    A2 --> ADM1
    A2 --> AD1
    ADM2 --> SP2
    AD2 --> SP2
    SP2 --> F3
    S2 --> A3
    A4 --> SP3
    SP3 --> S3
```

---

## 8. Export tips

- Paste any diagram into [mermaid.live](https://mermaid.live) → **Export PNG/SVG** for Word/PDF chapters  
- GitHub renders Mermaid in Markdown automatically after push  
- For thesis/docs, use **§2 Faculty** and **§3 Student** as the two primary process chapters; use **§1** as the system context diagram  

---

*Keep this file aligned with `SupplyRequestService`, `PurchaseRequestService`, and `InventoryService` when workflows change.*
