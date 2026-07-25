# PSIS Entity-Relationship Diagram

**System:** PECIT Smart Inventory System (PSIS)  
**Database:** `pecit_sis` (MySQL)  
**Source:** Laravel migrations under `database/migrations/`

This document is for project documentation. Render the Mermaid diagrams in GitHub, VS Code/Cursor Markdown preview, or [mermaid.live](https://mermaid.live).

---

## 1. High-level ER diagram (core domain)

```mermaid
erDiagram
    DEPARTMENTS ||--o{ USERS : "has"
    DEPARTMENTS ||--o{ REQUESTS : "for"

    USERS ||--o{ REQUESTS : "submits"
    USERS ||--o{ PURCHASE_REQUESTS : "buys"
    USERS ||--o{ PAYMENTS : "pays"
    USERS ||--o{ PSIS_NOTIFICATIONS : "receives"
    USERS ||--o{ ANNOUNCEMENTS : "creates"
    USERS ||--o{ AUDIT_LOGS : "performs"
    USERS ||--o{ TRANSACTIONS : "performs"
    USERS ||--o{ STOCK_LOGS : "performs"

    CATEGORIES ||--o{ INVENTORY : "classifies"
    SUPPLIERS ||--o{ INVENTORY : "supplies"

    REQUESTS ||--|{ REQUEST_ITEMS : "contains"
    INVENTORY ||--o{ REQUEST_ITEMS : "requested as"

    PURCHASE_REQUESTS ||--|{ PURCHASE_REQUEST_ITEMS : "contains"
    INVENTORY ||--o{ PURCHASE_REQUEST_ITEMS : "purchased as"
    PURCHASE_REQUESTS ||--o{ PAYMENTS : "paid via"

    INVENTORY ||--o{ TRANSACTIONS : "stock movement"
    INVENTORY ||--o{ STOCK_LOGS : "delivery log"

    ROLES ||--o{ MODEL_HAS_ROLES : "assigned"
    USERS ||--o{ MODEL_HAS_ROLES : "has role"
    PERMISSIONS ||--o{ ROLE_HAS_PERMISSIONS : "granted"
    ROLES ||--o{ ROLE_HAS_PERMISSIONS : "includes"

    DEPARTMENTS {
        bigint id PK
        string name
        string code UK
        text description
        boolean is_active
    }

    USERS {
        bigint id PK
        string employee_id UK
        bigint department_id FK
        string name
        string email UK
        string phone
        boolean is_active
        timestamp email_verified_at
        timestamp last_activity_at
        string password
    }

    CATEGORIES {
        bigint id PK
        string name
        string slug UK
        text description
        boolean is_active
    }

    SUPPLIERS {
        bigint id PK
        string name
        string contact_person
        string email
        string phone
        text address
        boolean is_active
    }

    INVENTORY {
        bigint id PK
        string item_code UK
        string item_name
        text description
        bigint category_id FK
        string unit
        decimal unit_price
        int quantity
        int reserved_quantity
        int minimum_stock
        bigint supplier_id FK
        string location
        enum status
        string barcode
    }

    REQUESTS {
        bigint id PK
        string request_number UK
        bigint user_id FK
        bigint department_id FK
        enum type
        enum status
        text purpose
        decimal total_amount
        bigint reviewed_by FK
        bigint approved_by FK
        bigint released_by FK
    }

    REQUEST_ITEMS {
        bigint id PK
        bigint request_id FK
        bigint inventory_id FK
        int quantity_requested
        int quantity_approved
        int quantity_released
        decimal unit_price
        decimal subtotal
    }

    PURCHASE_REQUESTS {
        bigint id PK
        string purchase_number UK
        bigint user_id FK
        enum status
        decimal total_amount
        bigint verified_by FK
        bigint released_by FK
    }

    PURCHASE_REQUEST_ITEMS {
        bigint id PK
        bigint purchase_request_id FK
        bigint inventory_id FK
        int quantity
        decimal unit_price
        decimal subtotal
    }

    PAYMENTS {
        bigint id PK
        string reference_number UK
        bigint purchase_request_id FK
        bigint user_id FK
        decimal amount
        enum status
        string payment_method
        string receipt_path
        bigint verified_by FK
    }

    TRANSACTIONS {
        bigint id PK
        string transaction_number UK
        bigint inventory_id FK
        enum type
        int quantity
        int quantity_before
        int quantity_after
        string reference_type
        bigint reference_id
        bigint performed_by FK
    }

    STOCK_LOGS {
        bigint id PK
        bigint inventory_id FK
        enum action
        int quantity
        int balance_after
        string delivery_recipient
        bigint performed_by FK
    }

    PSIS_NOTIFICATIONS {
        bigint id PK
        bigint user_id FK
        string type
        string title
        text message
        string link
        boolean is_read
    }

    AUDIT_LOGS {
        bigint id PK
        bigint user_id FK
        string action
        string model_type
        bigint model_id
        json old_values
        json new_values
        string ip_address
    }

    ANNOUNCEMENTS {
        bigint id PK
        string title
        text content
        enum priority
        boolean is_active
        timestamp published_at
        timestamp expires_at
        bigint created_by FK
    }

    ROLES {
        bigint id PK
        string name
        string guard_name
    }

    PERMISSIONS {
        bigint id PK
        string name
        string guard_name
    }

    MODEL_HAS_ROLES {
        bigint role_id FK
        string model_type
        bigint model_id
    }

    ROLE_HAS_PERMISSIONS {
        bigint permission_id FK
        bigint role_id FK
    }
```

---

## 2. Faculty request workflow (logical)

```mermaid
erDiagram
    USERS ||--o{ REQUESTS : submits
    DEPARTMENTS ||--o{ REQUESTS : tagged
    REQUESTS ||--|{ REQUEST_ITEMS : lines
    REQUEST_ITEMS }o--|| INVENTORY : item
    USERS ||--o{ REQUESTS : "reviewed_by / approved_by / released_by"
    INVENTORY ||--o{ TRANSACTIONS : "reserve then release"
```

**Status path:**  
`pending` → `accounting_review` / `admin_review` → `approved` (stock reserved) → `released` (stock deducted)

---

## 3. Student purchase workflow (logical)

```mermaid
erDiagram
    USERS ||--o{ PURCHASE_REQUESTS : buys
    PURCHASE_REQUESTS ||--|{ PURCHASE_REQUEST_ITEMS : lines
    PURCHASE_REQUEST_ITEMS }o--|| INVENTORY : item
    PURCHASE_REQUESTS ||--o{ PAYMENTS : payment
    PAYMENTS }o--|| USERS : student
    USERS ||--o{ PURCHASE_REQUESTS : "verified_by / released_by"
    INVENTORY ||--o{ TRANSACTIONS : "reserve then release"
```

**Status path:**  
`pending` → `payment_submitted` → `payment_verified` (stock reserved) → `released` (stock deducted)

---

## 4. Table summary

| Table | Purpose |
|-------|---------|
| `users` | Accounts (faculty, student, staff, admin) |
| `departments` | Organizational units |
| `categories` | Inventory categories |
| `suppliers` | Item suppliers |
| `inventory` | Stock items (on hand + reserved) |
| `requests` | Faculty (and restock) supply requests |
| `request_items` | Lines on a faculty request |
| `purchase_requests` | Student purchases |
| `purchase_request_items` | Lines on a student purchase |
| `payments` | Student payment records / receipts |
| `transactions` | Stock movement ledger |
| `stock_logs` | Delivery / stock action log |
| `psis_notifications` | In-app notifications |
| `audit_logs` | Audit trail (polymorphic target) |
| `announcements` | Campus announcements |
| `roles` / `permissions` / pivots | Spatie RBAC |

### Framework tables (not shown above)

`sessions`, `password_reset_tokens`, `cache`, `jobs`, `failed_jobs` — Laravel infrastructure.

### Polymorphic references

- `transactions.reference_type` + `reference_id` → e.g. `SupplyRequest` or `PurchaseRequest`
- `audit_logs.model_type` + `model_id` → auditable model

---

## 5. Cardinality notes

| Relationship | Cardinality |
|--------------|-------------|
| Department → Users | 1:N |
| Category → Inventory | 1:N |
| Supplier → Inventory | 1:N (nullable) |
| User → Requests | 1:N |
| Request → Request Items | 1:N |
| Inventory → Request Items | 1:N |
| User → Purchase Requests | 1:N |
| Purchase Request → Items | 1:N |
| Purchase Request → Payments | 1:N |
| Inventory → Transactions | 1:N |
| User → Roles (via Spatie) | N:M |

---

## 6. Export tips

- **GitHub:** paste Mermaid into a `.md` file (this file) — GitHub renders it automatically  
- **PNG/SVG:** open [mermaid.live](https://mermaid.live), paste section 1, export  
- **Word/PDF docs:** export SVG/PNG from mermaid.live and insert into your report  

---

*Generated for PSIS documentation. Keep this file updated when migrations change.*
