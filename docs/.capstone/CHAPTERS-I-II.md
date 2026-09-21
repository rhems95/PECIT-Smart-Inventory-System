# CHAPTER I
# THE PROBLEM AND ITS BACKGROUND

## 1.1 Background of the Study

Philippine Electronic and Communication Institute of Technology (PECIT) is a higher education institution that maintains school supplies, classroom materials, and student uniforms across several academic units. National development policy treats digital, well-run public and education services as a current priority (National Economic and Development Authority [NEDA], 2023). Day-to-day supply work includes receiving goods, recording stock, issuing items to faculty, and selling uniforms to students—activities that belong to routine inventory control (Heizer et al., 2023; Slack et al., 2022). These tasks involve several offices at once: Supply Personnel keep the stockroom, Accounting reviews quantities and student payments, Admission or an Administrator approves faculty requisitions, Faculty request items for instruction, and Students purchase department uniforms and shared items such as P.E. and NSTP uniforms. Splitting custody, authorization, and recording among offices is a basic internal-control practice (Romney et al., 2021).

When inventory is kept in paper logs, spreadsheets, or informal chat messages, three problems appear. Informal files and personal productivity tools are a weak substitute for an organization-wide information system when many offices must share the same stock figures (Laudon & Laudon, 2024). First, on-hand quantity is easy to overstate. A request may be approved while another office is already holding the same pieces for a different order. Inventory records must distinguish what is physically on hand from what is already committed, or the institution will promise stock it cannot issue (Heizer et al., 2023; Slack et al., 2022). Second, student uniforms are not all shared. A Computer Studies uniform must not be sold to a Criminology student, while P.E., NSTP, and ID lanyards remain available to every student. That exclusivity rule is a PECIT business policy implemented in this study; it is not taken from a published costing method. Third, managers cannot answer a simple audit question: who moved how many units, from which source, and what the running balance was after that movement. Accounting information systems require an audit trail that links each movement to a person, a document, and a quantity (Romney et al., 2021).

The PECIT Smart Inventory System (PSIS) is a web-based institutional inventory and requisition system built for this setting. It is not a commercial enterprise resource planning (ERP) warehouse package and not a full procurement or inventory-costing system (Kieso et al., 2022; Laudon & Laudon, 2024). It is a role-based application that (a) records stock with on-hand, reserved, and available quantities; (b) routes faculty supply requests through Accounting review and Admission or Administrator approval before Supply release; (c) sells uniforms through a Uniform Shop that hides other departments’ exclusive items; (d) verifies student payments in Accounting before Supply releases the goods; and (e) shows a Stock Card of physical movements on the stock ledger. Access should follow the job: each role receives only the permissions it needs (International Organization for Standardization [ISO], 2022).

This chapter states the problem the researchers addressed, the objectives of the system, what is inside and outside the project, who benefits, and the terms used in later chapters.

## 1.2 Statement of the Problem

### General Problem

How can PECIT record, control, and release school supplies and student uniforms in a single web system so that stock is not deducted too early, the correct offices approve each transaction, and students can buy only the uniforms allowed for their department?

### Specific Problems

1. How can the institution show on-hand, reserved, and available stock so that an approved request does not take pieces that are already promised to another transaction (Heizer et al., 2023; Slack et al., 2022)?

2. How can faculty requisitions move from submission to Accounting review, Admission or Administrator approval, and Supply release without deducting stock at the moment of submission, consistent with segregation of duties (Romney et al., 2021)?

3. How can students purchase uniforms online (or through the campus shop module) without seeing or buying another department’s exclusive uniform, while still buying shared items such as P.E., NSTP, and ID lanyards?

4. How can Accounting verify student payments and only then reserve stock, and how can Supply release verified purchases and approved faculty requests with a recorded ledger entry (Romney et al., 2021)?

5. How can Supply Personnel record stock-in, stock-out, adjustment, damage, bad order, and return to supplier, including optional supplier and delivery cost, without turning the system into a first-in-first-out (FIFO) costing engine or a full purchase-order module (Kieso et al., 2022; Republic of the Philippines, 2024)?

6. How can staff and students receive in-app and optional email notices for request status, payment, release, and low stock, using only data that exist in the database (Laudon & Laudon, 2024)?

## 1.3 Objectives of the Study

### General Objective

To design, develop, and implement a web-based inventory and requisition system for PECIT that reserves stock on approval or payment verification, deducts on-hand quantity only on Supply release, enforces department-exclusive uniform sales, and provides a physical Stock Card for audit.

### Specific Objectives

1. To maintain inventory with on-hand quantity, reserved quantity, and available quantity (available = on-hand minus reserved), including per-size stock for clothing uniforms.

2. To implement a faculty supply-request workflow: pending → Accounting review → admin review → Admission or Administrator approve (reserve) → Supply release (deduct).

3. To implement a student Uniform Shop filtered by department exclusivity and shared items, with cart, checkout, payment slip, optional receipt upload, Accounting verification (reserve), and Supply release (deduct).

4. To provide role-based access for Administrator, Admission, Accounting, Supply Personnel, Faculty, and Student, including dual login (staff email and password; student last name and Student ID).

5. To record physical stock movements on a ledger (Stock Card) with supplier, source, optional unit cost, and running balance, while keeping selling price separate from delivery cost.

6. To notify concerned users through in-app notifications and optional email, and to support reports (PDF/Excel), audit logs, student account management, and rule-based restock insights.

## 1.4 Scope and Delimitation

### Scope

The study covers PSIS as deployed for PECIT on a local or campus network (XAMPP / Laravel web application). The system includes:

1. **Users and roles.** Administrator, Admission, Accounting, Supply Personnel, Faculty, and Student, using Spatie Laravel Permission.

2. **Inventory.** Item master, categories, units of measurement, on-hand / reserved / available, Uniform Shop flags, department exclusivity, and per-size stock for clothing.

3. **Faculty requisitions.** Create, cancel (before release), Accounting review, Admission or Administrator approve or reject, Supply release.

4. **Student purchases.** Uniform Shop, cart, checkout, over-the-counter payment slip, receipt upload, Accounting verify, Supply release, cancel before release with restore of reserved stock.

5. **Stock operations.** Stock in (with source, optional supplier, optional purchase-order or delivery-receipt number as text, optional unit cost), stock out, adjustment, damage, bad order, return to supplier.

6. **Stock Card.** Physical movements only. Reserve and restore are logged for control but are not shown as stock-in or stock-out on the card.

7. **Master data.** Departments (CCS, CC, CTHM, CTE, CBA, SHS, Administration, Supply Office), categories, suppliers (on the movement, not as a single supplier on the item), announcements, audit logs, reports.

8. **Support functions.** In-app notifications, optional email, session timeout, dark mode, and an AI assistant grounded in live data (question list plus type box; optional local Ollama; not a cloud large language model).

Academic departments in scope are College of Computer Studies (CCS), College of Criminology (CC), College of Tourism and Hospitality Management (CTHM), College of Teacher Education (CTE), College of Business Administration (CBA), and Senior High School (SHS), plus Administration and Supply Office for staff.

### Delimitation

The following are outside the implemented system and are not claimed as results of this study:

1. **FIFO and lot costing.** Financial accounting teaches FIFO as a method of assigning cost to units sold and to ending inventory (Kieso et al., 2022). Stock in PSIS is one quantity per item (or per size). The Stock Card does not consume leftover batches (Heizer et al., 2023).

2. **Moving-average inventory cost.** The weighted-average method likewise belongs to inventory costing, not to the selling price charged in a campus shop (Kieso et al., 2022). Selling price is not overwritten by delivery cost. Unit cost on stock-in is optional history, not a live weighted average.

3. **Purchase-order module.** Philippine public procurement is organized around electronic purchasing documents, including purchase orders and inspection of deliveries (Republic of the Philippines, 2024). ERP systems treat the purchase order as a core purchasing document (Laudon & Laudon, 2024). In PSIS, a source labeled “purchase order” plus a typed reference number is documentation on the movement only. There is no purchase-order document, open quantity, or three-way match.

4. **Public self-registration and mobile native applications.** Staff and Supply create student accounts. The application is a web session system, not a store application or a JSON API for third parties.

5. **External generative AI.** Restock tips and chat answers come from database rules and analytics, not from a cloud language model.

6. **Barcode scanning hardware and unused barcode fields.** Item identity in the current build uses item code; the unused barcode column was removed.

7. **Student access to the main inventory module.** Students buy only through the Uniform Shop.

These delimitations keep the project within institutional requisition and shop operations rather than ERP procurement and costing (Laudon & Laudon, 2024).

## 1.5 Significance of the Study

**Supply Personnel.** The system centralizes receiving, counting, damage, returns, and release. The Stock Card supports answers to auditors about physical in and out, which is the purpose of a running record of receipts and issues (Romney et al., 2021; Slack et al., 2022).

**Accounting.** Faculty lines can be reviewed before approval. Student payments can be verified with an optional receipt before stock is reserved. Authorization before recording a commitment is an accounting-information-system control (Romney et al., 2021).

**Admission and Administrator.** Faculty requests can be approved or rejected after Accounting review. The Administrator retains users, master data, reports, and audit logs. Admission is limited to dashboard, inventory view, and approval, which matches an owner-level role without stock-room operations. Least privilege—giving each role only the access it needs—is an access-control requirement (ISO, 2022).

**Faculty.** Instructors can submit and track supply requests without needing direct stockroom access, and they can cancel before release. Information systems are used to coordinate work across departments without sharing the same physical files (Laudon & Laudon, 2024).

**Students.** Each student sees only exclusive uniforms of their department plus shared P.E., NSTP, and lanyard items, and can track purchase status after payment.

**PECIT as an institution.** One database replaces scattered spreadsheets for the same items used in both classroom issuance and uniform sales (Laudon & Laudon, 2024; NEDA, 2023).

**Future researchers.** The documented reserve-and-release model, department shop rule, and explicit exclusion of FIFO, moving average, and purchase-order modules provide a baseline for later costing or procurement studies (Kieso et al., 2022).

## 1.6 Definition of Terms

The following terms are used in this manuscript in the sense defined here. Operational names (PECIT, PSIS, Admission, Uniform Shop) are defined by this project. Inventory words follow common usage in operations and accounting.

**Available quantity.** On-hand quantity minus reserved quantity. It is not stored as its own column. The idea is related to quantity that can still be promised to a new order (Slack et al., 2022).

**Admission.** The Spatie role for the school owner: dashboard, view inventory, and approve or reject faculty requests. This role does not manage users, stock operations, or master data.

**Department exclusivity.** A Uniform Shop item with a department assigned may be seen and bought only by a student whose account has the same department. A null department means the item is shared.

**On-hand quantity.** Physical pieces in stock (`quantity`), including pieces that may already be reserved (Heizer et al., 2023).

**PECIT.** Philippine Electronic and Communication Institute of Technology.

**PSIS.** PECIT Smart Inventory System, the web application described in this study.

**Purchase request.** A student Uniform Shop order (`purchase_requests`). It is not a vendor purchase order as used in government or ERP purchasing (Laudon & Laudon, 2024; Republic of the Philippines, 2024).

**Reserved quantity.** Pieces promised to an approved faculty request or a payment-verified student purchase, not yet physically released (Slack et al., 2022).

**Stock Card.** The on-screen ledger of physical inventory transactions (in, out, release, adjustment, damage, return) with running balance. Reservations are omitted from this view. A stock card is a running record of receipts and issues (Romney et al., 2021; Slack et al., 2022).

**Stock in.** A Supply operation that increases on-hand quantity. Optional fields include source, supplier, reference number, delivery receipt number, and unit cost.

**Supply request.** A faculty requisition (`requests` table) for items from inventory.

**Unit cost.** Delivery or acquisition cost recorded on a stock movement. It is separate from selling price (Kieso et al., 2022).

**Unit price.** Selling or charging price on the item master, used in the shop and on faculty request lines (Kieso et al., 2022).

**Uniform Shop.** The only student purchase interface. Students cannot open the inventory module.

**Unit of measurement (UoM).** Master list of units (piece, box, set, and others) linked to an item (Heizer et al., 2023).

<!-- pagebreak -->

# CHAPTER II
# REVIEW OF RELATED LITERATURE AND STUDIES

This chapter presents literature and studies dated 2021–2026 that inform the design of the PECIT Smart Inventory System (PSIS). Related literature covers inventory control, management and accounting information systems, access control, software quality, and Philippine digital-governance and procurement policy. Related studies cover web-based inventory systems in universities abroad and in the Philippines. The chapter ends with a synthesis and the research gap.

## 2.1 Related Literature

### Foreign Literature

Inventory control remains a core operations problem: the organization must know what is on hand, what is already promised, and when to replenish (Heizer et al., 2023; Slack et al., 2022). Records that mix physical stock with committed stock lead to overselling and delayed issues. Slack et al. (2022) describe inventory as a buffer that must be visible across the operation, not only in a storeroom log. Heizer et al. (2023) likewise treat independent-demand items—such as office supplies and finished goods—as items whose available quantity must be planned, not guessed from a spreadsheet.

Management information systems exist to coordinate work among units that do not share the same paper file (Laudon & Laudon, 2024). When each office keeps its own workbook, the institution has several versions of the truth. Laudon and Laudon (2024) also distinguish enterprise systems that integrate processes from departmental tools that cannot enforce a single workflow. That distinction matters for PECIT: faculty requisition, student uniform sale, accounting verification, and supply release are one stock, not four separate books.

Accounting information systems add the control view. Romney et al. (2021) require segregation of duties—custody of goods, authorization of issues, and recording of transactions should not rest on one person—and an audit trail that ties each movement to a user, a document, and a quantity. Authorization should occur before the organization records a commitment. These points support a reserve-then-release model: approval or payment verification commits stock; physical release later reduces on-hand quantity.

Inventory *costing* is a different problem from inventory *quantity*. Kieso et al. (2022) present FIFO and weighted-average as methods of assigning cost to units sold and to ending inventory for financial reporting. Those methods need identifiable cost layers or a continuously updated average cost. An institutional shop that charges a selling price can record delivery cost on a receipt without adopting FIFO or moving-average as the engine of stock deduction (Kieso et al., 2022; Heizer et al., 2023).

Access control literature for information systems now sits in current security standards. ISO/IEC 27001 (2022) requires that access rights follow the job and the need to know. Least privilege—each role sees only the functions it requires—is therefore a literature-based requirement, not a local preference.

Software product quality is specified in ISO/IEC 25010 (2023), which defines characteristics such as functional suitability, reliability, usability, security, and maintainability for ICT products. Later chapters of this manuscript may use that model for evaluation; this chapter treats it as the current international language for saying whether an inventory system is fit for use.

### Local Literature

Philippine development policy treats digital public service as a present agenda, not a future slogan. The Philippine Development Plan 2023–2028 calls for digitalization of government and social services so that records are timely and usable across offices (NEDA, 2023). A campus inventory system that replaces scattered workbooks is consistent with that policy, even though PECIT is a private higher education institution rather than a national agency.

Procurement law in the Philippines was updated by Republic Act No. 12009, the New Government Procurement Act (Republic of the Philippines, 2024). That statute organizes purchasing around electronic documents, including purchase orders and inspection of deliveries. It is literature about *buying from vendors*. It is not a requirement that a campus Uniform Shop or a faculty supply request be implemented as a government purchase order. The researchers use RA 12009 to keep those two ideas apart: a typed PO number on a stock-in movement is receiving documentation; a full purchase-order module is procurement.

Together, NEDA (2023) and RA 12009 (2024) support two local claims. First, digital records and role-based processes are expected. Second, procurement documents and internal stock issues are not the same class of transaction. PSIS is designed around the second class, with an optional reference to the first.

## 2.2 Related Studies

### Foreign Studies

Oyekan and Ikuomola (2022) developed a web-based inventory control system for a Nigerian university store. Data came from interviews and store records. The application used PHP, Bootstrap, and MySQL. The system kept on-hand balance, produced low-stock alerts, and supported reorder analysis. The study shows that university stores still suffer from late information and excess or spoiled stock when they rely on manual methods, and that a browser-based store system is a practical response. It does not model faculty approval chains or a student shop with department exclusivity.

Singh et al. (2022) described an inventory management system for an engineering institute in India that had mixed paper rosters, Google Sheets, and verbal borrowing among departments. The system used a PHP–MySQL stack. The study confirms that undocumented issues and duplicate entries appear when there is no single database. The scope is laboratory and deadstock items, not uniforms sold to students and not a reserved-versus-available quantity for pending approvals.

Tjahjanto et al. (2022) built a PHP–MySQL information system for state-owned inventories in a faculty of computer science in Indonesia. Documentation had been paper-based, which made location and availability reports difficult. The system managed users, devices, locations, logs, and reports and was assessed for visual communication, software engineering, and usability. Like the Indian and Nigerian studies, it digitizes property records. It does not implement a two-path workflow (faculty requisition versus student purchase) on the same stock ledger.

These foreign studies agree on web databases, user accounts, and reports. None of them combine (a) reserve on approval or payment verify, (b) deduct only on supply release, (c) a department-exclusive student shop, and (d) a physical stock card that hides reservation rows.

### Local Studies

Tungcul and Kummer (2021) developed a supplies and equipment inventory, monitoring, and tracking system for Cagayan State University. The work covered purchase order, acceptance, issuance, and tracking, and used data-mining reports for heads of office. Evaluation used ISO/IEC 25010 and was rated compliant to a very great extent. The study is close to a supply-office system, including purchasing. The researchers of PSIS do not implement a purchase-order document or data-mining allocation reports; they keep vendor PO numbers as optional text on stock-in.

Bernabe et al. (2023) developed an equipment information system for Lyceum of the Philippines University–Cavite, with web access, user accounts, and RFID tagging of equipment. Thirty end users and ten IT experts rated the system highly acceptable under ISO 25010. The focus is equipment registration and property control, including student-owned devices, not consumable school supplies and not department-locked uniform sales.

Cepeda and Saludes (2025) developed an online ICT equipment inventory and borrowing system with decision support for St. Paul University Philippines. Manual logbooks caused weak tracking and no real-time view. The system added dashboards, borrowing workflows, notifications, and analytics. Quality was assessed with ISO/IEC 25010 and acceptance with the Technology Acceptance Model. Recommendations included QR codes and maintenance tracking. The workflow is borrow-and-return of ICT assets, not reserve-and-release of shop and classroom stock.

Gajetela (2025) developed an automated inventory system for income-generating projects at Jose Rizal Memorial State University, using C# and MySQL and evaluating quality with ISO/IEC 9126. The context is merchandise and IGP operations, which is nearer to a campus shop than a laboratory store, but the study does not describe department-exclusive student uniforms or a faculty accounting-to-admission approval path.

Across these Philippine studies, the pattern is the same: replace logbooks, add roles, add reports, and evaluate with an ISO quality model. Gaps remain in the exact PECIT mix of faculty requisition, student uniform exclusivity, payment verification, and a stock card that is a quantity ledger rather than a costing or procurement engine.

## 2.3 Synthesis of the Literature and Studies

The literature and the studies point in one direction and then stop short of the present project.

Operations and MIS writers agree that on-hand stock, committed stock, and a single shared record matter (Heizer et al., 2023; Laudon & Laudon, 2024; Slack et al., 2022). Accounting writers agree that authorization, custody, and recording should be split and that each movement needs an audit trail (Romney et al., 2021). Costing writers treat FIFO and weighted average as financial methods, not as the only way to count pieces (Kieso et al., 2022). Security and quality standards require least privilege and an explicit quality model (ISO, 2022, 2023). Philippine policy supports digital records and distinguishes procurement documents from other transactions (NEDA, 2023; Republic of the Philippines, 2024).

Empirical studies in Nigeria, India, Indonesia, and the Philippines show that PHP or similar web stacks, MySQL, user roles, alerts, and ISO evaluation are the common solution to manual university inventory (Bernabe et al., 2023; Cepeda & Saludes, 2025; Oyekan & Ikuomola, 2022; Singh et al., 2022; Tjahjanto et al., 2022; Tungcul & Kummer, 2021). Those systems usually track equipment, borrowed ICT items, or supply-office purchases. They rarely publish a three-quantity model (on-hand, reserved, available), a student shop that hides another college’s uniform, and a stock card of physical in and out with delivery cost recorded only as history.

PSIS is therefore an application of the shared literature—visibility, control, roles, digital records—to a setting the cited systems did not fully cover: one campus inventory that serves both faculty issuance and department-exclusive uniform sales, with stock deducted only when Supply releases the goods.

## 2.4 Research Gap

From the review, the following gap remains.

1. **Quantity control without costing layers.** Studies digitize on-hand lists and alerts (Oyekan & Ikuomola, 2022; Singh et al., 2022). They do not, in the sources reviewed, implement reserved quantity on approval or payment verification and available quantity as on-hand minus reserved, while refusing FIFO and moving-average engines (Heizer et al., 2023; Kieso et al., 2022).

2. **Two client paths on one stock.** Local systems address equipment borrowing, property RFID, IGP merchandise, or supply purchasing (Bernabe et al., 2023; Cepeda & Saludes, 2025; Gajetela, 2025; Tungcul & Kummer, 2021). They do not combine a faculty path (accounting review then admission or administrator approval) with a student Uniform Shop that is exclusive by department and shared only for specified items.

3. **Procurement versus receiving notes.** Tungcul and Kummer (2021) include purchase-order processing. RA 12009 (2024) governs government purchasing documents. There is still a need for a campus system that may store a PO or delivery-receipt *number* on stock-in without becoming a purchase-order module.

4. **Stock Card as physical ledger.** Literature requires an audit trail (Romney et al., 2021). Related systems emphasize logs, dashboards, and ISO scores. A stock card that shows physical in, out, and running balance, and that hides reservation rows, is not the center of the studies reviewed.

The present study addresses that gap by developing PSIS for PECIT: role-based web inventory with reserve-and-release, department-exclusive student sales, optional delivery cost on the movement, and a physical Stock Card, without claiming FIFO, moving-average costing, or a purchase-order module.

<!-- pagebreak -->

# REFERENCES

Bernabe, J., Colos, R., De Leon, N. J., Garcia, C. K., & Romero, C. V. (2023). LPU EIS: Equipment information system for Lyceum of the Philippines University – Cavite. *International Journal of Computer Science and Information Technology Research, 11*(3), 7–30. https://doi.org/10.5281/zenodo.8139237

Cepeda, J. A. U., & Saludes, A. J. C. (2025). Online ICT equipment inventory and borrowing system with decision support. *European Journal of Innovative Studies and Sustainability, 1*(5). https://doi.org/10.59324/ejiss.2025.1(5).07

Gajetela, N. K. J. S. (2025). Automated inventory management system for income-generating projects. *International Journal of Advanced Multidisciplinary Studies*, 270–284. https://www.ijams-bbp.net/wp-content/uploads/2025/05/2-IJAMS-FEBRUARY-2025-270-284.pdf

Heizer, J., Render, B., & Munson, C. (2023). *Operations management: Sustainability and supply chain management* (14th ed.). Pearson.

International Organization for Standardization. (2022). *ISO/IEC 27001:2022: Information security, cybersecurity and privacy protection—Information security management systems—Requirements*. ISO.

International Organization for Standardization. (2023). *ISO/IEC 25010:2023: Systems and software engineering—Systems and software quality requirements and evaluation (SQuaRE)—Product quality model*. ISO.

Kieso, D. E., Weygandt, J. J., & Warfield, T. D. (2022). *Intermediate accounting* (18th ed.). Wiley.

Laudon, K. C., & Laudon, J. P. (2024). *Management information systems: Managing the digital firm* (18th ed.). Pearson.

National Economic and Development Authority. (2023). *Philippine development plan 2023–2028*. https://pdp.neda.gov.ph/

Oyekan, E. A., & Ikuomola, A. J. (2022). An adaptive web-based inventory control system for universities. *International Journal of Innovative Research and Development, 11*(12). https://doi.org/10.24940/ijird/2022/v11/i12/DEC22009

Republic of the Philippines. (2024). *Republic Act No. 12009: New Government Procurement Act*. Official Gazette. https://www.officialgazette.gov.ph/

Romney, M. B., Steinbart, P. J., Summers, S. L., & Wood, D. A. (2021). *Accounting information systems* (15th ed.). Pearson.

Singh, S., Kunder, K., Jain, V., & Sagvekar, V. (2022). Inventory management system for education institutions. In *2022 International Conference on Advances in Science and Technology (ICAST)*. IEEE. https://doi.org/10.1109/icast55766.2022.10039520

Slack, N., Brandon-Jones, A., & Burgess, N. (2022). *Operations management* (10th ed.). Pearson.

Tjahjanto, Arista, A., & Ermatita. (2022). Application of the waterfall method in information system for state-owned inventories management development. *Sinkron: Jurnal dan Penelitian Teknik Informatika, 7*(4), 2182–2191. https://doi.org/10.33395/sinkron.v7i4.11678

Tungcul, M. B., & Kummer, M. G. C. (2021). Supplies and equipment inventory, monitoring and tracking management system using data mining techniques. *International Journal of Recent Technology and Engineering, 10*(2). https://doi.org/10.35940/ijrte.B6174.0710221
