# CHAPTER V
# SUMMARY, CONCLUSIONS, AND RECOMMENDATIONS

This chapter restates the study, states only conclusions that the implemented PECIT Smart Inventory System (PSIS) can support, and lists recommendations. Future costing, procurement, and generative-AI items are labeled as **not implemented**.

## 5.1 Summary

PECIT needed one web record of school supplies and student uniforms. Paper, spreadsheets, and chat could not keep on-hand quantity, committed quantity, and an audit trail in the same place, and they could not hide another college’s exclusive uniform at the point of sale (Heizer et al., 2023; Laudon & Laudon, 2024; Romney et al., 2021; Slack et al., 2022). The general objective was to design, develop, and implement PSIS so that stock is reserved on faculty approval or on Accounting payment verification, deducted only when Supply Personnel release the goods, sold in a department-exclusive Uniform Shop, and shown on a physical Stock Card.

Chapter II reviewed 2021–2026 literature and studies. Operations and accounting writers separate quantity control from costing methods such as FIFO and weighted average (Heizer et al., 2023; Kieso et al., 2022; Slack et al., 2022). Philippine policy supports digital records and treats purchase orders as procurement documents, not as campus shop tickets (National Economic and Development Authority [NEDA], 2023; Republic of the Philippines, 2024). University web stores exist in Nigeria, India, Indonesia, and the Philippines, but the reviewed systems did not combine reserve-then-release, a department-locked student shop, and a Stock Card that hides reservation rows.

Chapter III documented an IPO framework, role requirements, UML/DFD/ERD (from the project mermaid files), the XAMPP and Laravel stack, a waterfall-style write-up with versioned construction, and a test plan. ISO/IEC 25010 was named as the language for later evaluation, not as a score already earned (ISO, 2023).

Chapter IV described the running Laravel 12 application: six Spatie roles, dual login, inventory with on-hand / reserved / available (including per-size clothing stock), faculty and student workflows, stock operations, suppliers on the movement, units of measurement, reports, audit logs, in-app notices, and a question-list AI assistant. On 11 September 2026, `php artisan test` produced 66 passing tests and 252 assertions. Formal signed UAT forms and ISO/IEC 25010 means were not in the repository and were not invented.

PSIS as built is a campus requisition and shop system. It is not FIFO costing, not a moving-average engine, not a purchase-order module, not a public register, and not an external large language model.

## 5.2 Conclusions

The researchers conclude the following, aligned with the specific objectives.

1. PSIS maintains on-hand, reserved, and available quantity, with available computed as on-hand minus reserved. Clothing Uniform Shop items keep those figures per size (XS–3XL). Automated tests showed that reserve does not reduce on-hand and that release does.

2. The faculty path is implemented as pending → Accounting review → admin review → Admission or Administrator approve (reserve) → Supply release (deduct). Submit does not deduct. Cancel before release restores reserved stock, including during admin review and after approval.

3. The Uniform Shop lists only student-shop items that are shared (`department_id` null) or exclusive to the student’s department. Cart and checkout apply the same rule. Accounting verify reserves; Supply release deducts. Students have no inventory module.

4. Access is role-based for Administrator, Admission, Accounting, Supply Personnel, Faculty, and Student. Staff log in with email and password; students log in with last name and Student ID. Registration is disabled. Tests showed that students cannot manage suppliers, Faculty cannot open the Stock Card or delete inventory, and unused items may be deleted only by Supply or Administrator when the item is not on a request.

5. Physical movements are written to `transactions` and shown on the Stock Card with running balance. Optional supplier, source, typed PO or delivery-receipt number, and unit cost may appear on stock-in. Selling price remains on the item master. Reserve and restore rows are logged and hidden on the card.

6. The system creates in-app notifications, may send email when configured, exports PDF and Excel reports, records audit logs, lets Supply and Administrator manage student accounts (including CSV import), and answers a fixed list of AI questions from live data. Free-typed AI questions are rejected.

Taken together, the general objective is met **within the delimitations**: one web system for PECIT requisition and uniform sales with reserve-then-release and a physical ledger, without claiming ERP procurement, inventory costing layers, or generative AI (Kieso et al., 2022; Laudon & Laudon, 2024; Romney et al., 2021).

The researchers also conclude that quality **ratings** under ISO/IEC 25010 cannot be stated yet, because no administered questionnaire results exist (ISO, 2023). Passing PHPUnit tests support functional suitability of the paths that were coded as tests; they do not replace office-signed acceptance or a MySQL walkthrough of every report.

## 5.3 Recommendations

On the basis of the conclusions and of the limits stated in Chapter IV, the researchers recommend the following.

1. PECIT should use PSIS on the campus host with changed demo passwords before any live student load, keep `APP_DEBUG` off on a shared machine, and run `php artisan storage:link` so receipts remain available to Accounting.

2. Supply Personnel should treat the Stock Card as the quantity audit view and should enter a reason on every deduction (stock-out, damage, bad order, return).

3. Accounting should verify payments before expecting reserved shop stock, and Admission or an Administrator should approve faculty requests before expecting reserved classroom stock.

4. The institution should complete signed user-acceptance walkthroughs with the six roles and, if the panel requires it, ISO/IEC 25010 instruments after those walkthroughs—then revise Chapter IV with real *n* and means (ISO, 2023; Tungcul & Kummer, 2021).

5. The team should add automated tests for Uniform Shop listing (CCS versus CC and the other academic codes) on MySQL, not only SQLite, and should time session idle logout on the live host.

### Future Enhancements

These items are **not in the current system**. They are possible later work if PECIT changes scope.

1. **FIFO or lot costing** — cost layers and batch consumption. Not implemented; selling price and optional delivery cost would have to be redesigned (Kieso et al., 2022; Heizer et al., 2023).

2. **Moving-average inventory cost** — a live weighted average that overwrites or replaces unit cost. Not implemented (Kieso et al., 2022).

3. **Purchase-order module** — PO document, open quantity, and three-way match. Not implemented. The typed PO number on stock-in would remain receiving text unless this module is built (Republic of the Philippines, 2024; Laudon & Laudon, 2024).

4. **Barcode or QR scanning hardware** — the unused barcode column was removed; item identity is item code.

5. **Cloud large language model** — OpenAI or similar APIs are not used. Optional local Ollama on this PC may word free-typed chat; stock figures still come from `AiInsightService`.

6. **Public self-registration or a native mobile application / JSON API** — out of the present web-session design.

7. **Scheduled backups and HTTPS on a public host** — operational, not a current application feature.

### Recommendations for Future Researchers

1. Replicate the reserve-then-release tests on a copy of the production MySQL schema and report any difference from SQLite PHPUnit.

2. Measure shop exclusivity with paired student accounts for every academic code (CCS, CC, CTHM, CTE, CBA, SHS), including cart rejection of another department’s exclusive.

3. If costing is the new problem, start from Kieso et al. (2022) and do not assume PSIS already stores lots.

4. If procurement is the new problem, start from RA 12009 (2024) and do not treat student `purchase_requests` as vendor purchase orders.

5. If quality scoring is required, administer ISO/IEC 25010 (2023) with a stated sample; do not copy means from other campuses.

6. Keep Spatie role names and department codes unchanged unless seeders, menus, policies, and the shop filter are updated together.

---

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
