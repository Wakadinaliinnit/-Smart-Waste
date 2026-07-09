# Project Q&A

Use this file to ask questions about this project.

## How to use
1. Add your question under "Questions".
2. I will add answers under "Answers" with the same ID.

## Questions
- [Q1] (write your question here)
- [Q2] How does the developed solution look and work, how well was it implemented, what challenges/deviations occurred, and how were technical and stakeholder requirements fulfilled?

## Answers
- [A1] (I will answer here)

- [A2] Implementation Evaluation and Technical Justification

### 1) How the developed solution looks and works
The delivered system is a role-based web application for municipal waste operations with four user groups: resident, collector, admin, and officer. It centralizes core processes that were previously manual: collection requests, waste issue reporting, task assignment, schedule publishing, and operational monitoring.

Operationally, the flow is:
1. A resident registers and logs in.
2. The resident submits either a collection request or waste issue report.
3. Admin assigns requests/reports to collectors and creates zone schedules.
4. Collectors update assigned tasks to in-progress/completed or resolved.
5. Officer views aggregate performance and zone-level statistics for decision support.

This meets the core problem of fragmented communication and delayed response by introducing one shared transaction and status-tracking platform.

### 2) How well the project has been done
Overall quality is moderate to good for a baseline implementation:
1. Strengths:
	- Clear role separation with role-based access checks.
	- Core CRUD and status workflows are implemented end-to-end.
	- MySQL schema captures key entities and relationships.
	- Prepared statements are used in many critical write paths.
2. Gaps/risks:
	- A few implementation mismatches exist between schema and helper code (details below).
	- Some state-changing admin actions use GET requests, which is less secure.
	- Officer UI currently depends on a deleted HTML include, which blocks that dashboard.

### 3) Challenges, adjustments, and deviations from proposal
Observed implementation challenges and how they were handled or should be handled:
1. Multi-role complexity:
	- Challenge: different rights and dashboards per role.
	- Current handling: centralized session/role guards in auth helpers.
2. Workflow synchronization:
	- Challenge: keeping request/report status transitions consistent across resident, admin, and collector views.
	- Current handling: explicit status enums in DB and controlled update actions.
3. Notification/audit consistency:
	- Challenge: reliable logging and notification records across actions.
	- Deviation: helper inserts reference columns not present in current schema (ip_address, user_agent, type).

Not met exactly as proposed (and impact):
1. Officer dashboard rendering is not met right now:
	- officer dashboard page includes a separate HTML partial that is currently missing.
	- Impact: officer role cannot view intended reporting UI until include target is restored/replaced.
2. Extended media/reporting features are partial:
	- schema has photo_path but upload pipeline is not implemented.
	- Impact: issue reports work textually but with reduced evidential richness.

### 4) Technical Stack and Environment
Backend language and framework:
1. Language: PHP (procedural, server-rendered approach).
2. Backend structure: plain PHP module architecture (not Laravel/Symfony), using shared include modules for DB/auth/utilities.
3. Why this fits the problem:
	- Low deployment friction on municipal lab stacks (XAMPP).
	- Fast form-based workflow delivery.
	- Native session support and password hashing utilities for secure role-auth.

Front-end choice and UX significance:
1. Technology: server-rendered HTML with shared CSS stylesheet.
2. UX contribution:
	- Consistent navbar and card/table pattern lowers navigation confusion.
	- Status badges communicate task states clearly.
	- Responsive CSS media queries support mobile and desktop usage.

Database management:
1. DBMS: MySQL.
2. Design quality:
	- Strong entity model for users, requests, reports, schedules, notifications, and logs.
	- FK constraints support relational integrity and traceability.
	- Enumerated statuses enforce valid workflow states.

IDE and version control:
1. IDE: Visual Studio Code.
2. Version control: Git (recommended and expected for this workflow).
3. Traceability value:
	- Commits preserve implementation steps.
	- Branching/review process supports accountable change history.

Controlled environment and design expectations:
1. Modularity: includes modules isolate DB connection/auth/utilities from feature pages.
2. Separation of concerns: role modules are isolated by folder and access guard.
3. Scalability (current level): suitable for small/medium concurrent municipal usage; horizontal scale would require app and DB hardening.

### 5) Hardware environment and concurrency
Recommended deployment profile for this implementation:
1. CPU: 4 logical cores minimum.
2. RAM: 8 GB minimum (16 GB preferred).
3. Storage: SSD for MySQL data and logs.
4. Stack: Apache + PHP + MySQL on local server or VPS.

Why this is adequate:
1. Workload is mostly short transactional queries and form submissions.
2. With indexed tables and moderate user load, this profile supports concurrent role traffic without visible degradation.
3. For higher peak loads, add DB indexes on high-frequency filters (status, zone, assigned_collector_id, created_at/reported_at/requested_at).

### 6) Backup and recovery strategy
Automated backup strategy (recommended standard for this project):
1. Nightly full MySQL dump.
2. Hourly incremental/binlog backups where supported.
3. Weekly off-site encrypted backup copy.
4. Monthly restore test in staging.

Protection benefits:
1. Prevents permanent data loss from accidental deletion or server failure.
2. Supports rollback after corruption/security incidents.
3. Ensures continuity of decision-support records and service history.

### 7) Stakeholder interaction and functional fulfilment
Access control and rights:
1. Implemented through session login checks and role enforcement.
2. Users are redirected to role-specific dashboards after authentication.

Non-functional requirement realization:
1. Security: password hashing and many prepared statements are implemented.
2. Usability: consistent UI components, clear status labels, and role-focused pages.
3. Maintainability: reusable include files and module directories.

Reporting and decision support:
1. Officer dashboard computes completion and resolution rates.
2. Admin dashboard exposes aggregate operational counts and recent activity.

Logic consistency and traceability assurance:
1. Core process trace example:
	- Use case: Resident submits collection request.
	- Implementation: resident form inserts into collection_requests.
	- Admin assigns collector and status changes to assigned.
	- Collector updates status to in_progress/completed.
	- Evidence chain: DB records + dashboard tables + activity logs.
2. No new process assurance method:
	- Map each implemented page/action against use-case list.
	- Verify each action has corresponding table updates and UI state display.
	- Run role-based test cases for each path.

Error messages:
1. User-facing validation and flash messages are implemented for common failures (invalid login, missing fields, assignment failures).
2. Some system-level failures still return generic DB errors and should be standardized for production-safe reporting.

### 8) Explicit appendix evidence mapping (required in report)
Use clearly labeled appendices after references and include them in your list of figures.

Recommended appendix mapping:
1. Appendix A: Authentication and role-based login screenshots.
2. Appendix B: Resident request/report submission and status tracking screenshots.
3. Appendix C: Admin assignment, scheduling, and user management screenshots.
4. Appendix D: Collector task update workflow screenshots.
5. Appendix E: Officer analytics and zone statistics screenshots.
6. Appendix F: Database schema export and key table snapshots.
7. Appendix G: Functional test case matrix (requirement-to-test traceability).
8. Appendix H: Source code snippets proving access control and prepared statements.
9. Appendix I: Error/validation message screenshots.

In the implementation chapter, cite these explicitly, for example: See Appendix C, Figure C.2 for request assignment; See Appendix G, Test Case TC-REQ-01 for functional verification.

### 9) Evidence links to implementation files
1. Role routing and landing flow: index.php
2. Authentication and session guard logic: includes/auth.php
3. Database connection and charset setup: includes/db.php
4. Resident request/report modules: resident/request.php, resident/report.php
5. Admin assignment/scheduling modules: admin/requests.php, admin/reports.php, admin/schedules.php
6. Collector execution workflow: collector/dashboard.php
7. Officer reporting logic: officer/dashboard.php
8. Database schema and status models: sql/schema.sql

### 10) Concise overall judgment
The project substantially satisfies the core functional scope and demonstrates clear value for the problem statement. It is best described as a working baseline with strong role-based process coverage, but requiring final hardening and consistency fixes (especially schema-helper alignment and officer dashboard rendering) before production-level acceptance.
