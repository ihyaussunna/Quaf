# QUAF Fest 09 — Features Specification
**Comprehensive Catalogue of Features and Business Capabilities**

---

## 1. Feature Status Summary
- **Implemented**: Fully operational in current codebase and production database.
- **In Progress**: Active work in progress or partial automation.
- **Planned**: Scheduled for subsequent festival conclave iterations.
- **Deprecated**: Removed or superseded by updated architecture.

---

## 2. Implemented Features

### 2.1 Public Festival Portal (`/`)
- **Brand Identity & Navigation**: Official QUAF Season 09 logo lockup, institution branding, and responsive header navigation.
- **Emergency Broadcast Ticker**: Sticky top alert bar broadcasting live announcements dispatched from the festival control room.
- **Real-time Group Standings (`#groups`)**: Live scoreboard cards displaying rank, official group colors, manager details, and aggregate points tally.
- **Festival Zones Showcase (`#zones`)**: Visual cards for the 4 official academic zones (A Zone, B Zone, C Zone, Mix Zone) with direct result filter links.
- **Live Stage Monitoring (`#stages`)**: Real-time venue status indicators (`LIVE NOW`, `BREAK`, `CLOSED`) displaying current performing program and next scheduled event.
- **Verified Results Archive (`/results`)**: Filterable competition verdicts with gold, silver, and bronze placement cards, student chest numbers, and signed judge marks.
- **Cryptographic Verification**:
  - Certificate verification (`/verify/certificate/{certificateNumber}`): Verifies student credentials, position, grade, and date of issue.
  - Student identity verification (`/verify/student/{qrToken}`): QR scanner verification for competitor badges.
- **Festival News & Media Hub**: Editorial articles with category filters (`/news`), photo gallery (`/gallery`), and video live streams (`/videos`).

### 2.2 Central Administration Console (`/admin`)
- **Executive Operations Dashboard**:
  - 4 Primary stat cards: Students (1,168+), Programs (144+), Teams (5), and Venues (4).
  - Dynamic multi-line performance chart tracking real-time group points progression as results are declared.
  - 1-Click "Sync Official Data" button to automatically seed and sync all 144 official programs and 1,168 students from CSV sources.
  - Live stage status monitor and quick navigation shortcuts.
- **Dedicated Zone Management Dashboard (`/admin/zones`)**: Dedicated zone oversight with statistics, stage vs. off-stage breakdown, program matrix, and student rosters.
- **Academic Groups Module (`/admin/groups`)**: Full management of the 5 official groups (Pacto, Yugo, Conco, Lumo, Unio) with leader assignments, official color hex codes, and standings caches.
- **Student Competitor Master (`/admin/students`)**:
  - Directory search by Chest Number, Name, Class, or Group.
  - Add Student form with automatic sequential chest number generator.
  - **Bulk Student Registration (`/admin/students/bulk`)**: Upload 4-column CSV / Excel rosters with downloadable template.
  - Student-wise enrolled events inspector (`/admin/students-wise`).
- **Program & Competition Management (`/admin/programs`)**:
  - Full catalog of 144 official programs categorized by Zone and discipline.
  - Configuration of eligibility rules, participant limits, duration, and scoring criteria.
  - Filtered stage dropdowns: Only stage programs (`is_stage = true`) appear in Current / Next Program selectors.
  - Program-wise competitor roster inspector (`/admin/programs-wise`).
- **Code-Letter Anonymization Matrix (`/admin/code-letters`)**: One-click random code letter generator (`A`, `B`, `C`...) for checked-in competitors with manual override.
- **Printable Stage Operations Forms (`/admin/forms`)**:
  - Stage Call Lists (`/admin/forms/call-list`) with arrival check-boxes.
  - Judge Evaluation Sheets (`/admin/forms/evaluation`) with criteria breakdown.
  - Competitor Chest Slips (`/admin/idcards/chest-slips`).
- **Marks & Tabulation Suite (`/admin/mark-entry`)**:
  - Tabulated marks viewer (`/admin/mark-entry/view-marks`).
  - Administrative marks entry override (`/admin/mark-entry/handler`).
  - Multi-judge variance checker (`/admin/mark-entry/check`).
- **Results Declaration & Publishing Engine (`/admin/results`)**:
  - Staged publication workflow: `Declare Result` -> `Review Podium` -> `Publish Result`.
  - **Dense Ranking with Ties**: Equal point distribution for tied scores without skipping subsequent ranks.
  - **Workflow Dispatching**: One-click dispatch of declared results to the Announcer Desk (`send-to-announcer`) and Media Studio.
  - Undeclare capability (`DELETE /admin/results/{result}/undeclare`) with transactional points rollback.
- **Achievements & Leaderboards (`/admin/achievements`)**: Overall Team Score, Zone Scores, Stage Scores, and Championship Top Scorers (Kalaprathibha, Kalathilakam, Zone Toppers).
- **Settings Drawer (`/admin/settings`)**: Sliding drawer for point weights, registration cutoff countdowns, and broadcast messages.

### 2.3 Program Committee / Program Samithi Portal (`/program-committee`)
- **Rules & Guidelines Editor**: Manage official instructions, duration, and scoring criteria across all 144 programs.
- **Malayalam Rules Typography**: Dedicated `Anek Malayalam` font rendering for Malayalam guidelines.
- **Printable Niyamavali Rules Book (`/program-committee/niyamavali/print-book`)**: Generates a unified, formatted printable rules book with cover page, index, and individual program guidelines.
- **Single Program Rules Print (`/program-committee/programs/{program}/rules/print`)**: One-click individual rule sheet printing.

### 2.4 Group Leader Portal (`/leader`)
- **Quota Tracking & Slot Counter**: Displays slot capacity per program (e.g. `2 / 2 Slots Filled` or `1 / 2 Slots Filled - Partial`).
- **Continuous Multi-Student Registration**: Group leaders can rapidly enroll multiple students without leaving the registration modal.
- **Auto-Verification**: Registrations are instantly approved upon submission by group leaders without waiting for admin sign-off.
- **Inline Entry Editing**: Group leaders can directly edit or replace registered competitors (`/leader/registrations/{entry}/edit`) or remove entries before deadlines.
- **Roster & Program Inspectors**: Student-wise (`/leader/students-wise`) and Program-wise (`/leader/programs-wise`) views.

### 2.5 Jury Evaluation Suite (`/judge`)
- **PIN-Based Quick Login**: Fast 4-digit PIN authentication for judges on mobile/tablet devices.
- **Digital Marksheets**: Touch-friendly criteria evaluation cards with automated score sum and percentage calculation.
- **Grade B+ Option**: Full support for Grade A+ (6 pts), Grade A (5 pts), Grade B+ (4 pts), Grade B (3 pts), and Grade C (1 pt).
- **Locked Submissions**: Submissions are cryptographically locked to prevent tampering once finalized.

### 2.6 Green Room Operations (`/greenroom`)
- **Contestant Check-In**: Stage arrival and attendance verification.
- **Automated Code Letters**: Generates random code letters per program to preserve anonymity on stage.
- **Call-Next Queue**: Advances contestants from called to on-stage status.

### 2.7 Auditorium Projector View (`/stages/{stage}/projector`)
- **Big Screen Live Display**: High-contrast, dark-mode auditorium display showing the stage name, currently performing contestant/code letter, and upcoming contestants.

### 2.8 Announcer Desk Console (`/announcer`)
- **Live Broadcast Queue**: Queue of declared results dispatched from the admin desk ready for live stage announcement.
- **Stage Call Sheets**: Real-time access to stage call lists for live auditorium microphone callouts.

### 2.9 Media Desk & Result Poster Graphics Studio (`/media`)
- **Editorial Hub**: News authoring (`/media/news`), photo gallery (`/media/gallery`), and video archives (`/media/videos`).
- **Result Poster Studio (`/media/results/{result}/studio`)**: Built-in canvas graphics generator that renders branded social media posters with winner names, chest numbers, scores, and group colors.
- **Instant Poster Publishing**: Exports PNG graphics and publishes them directly to the public result page.

### 2.10 Student Competitor Portal (`/student`)
- **Personalized Competitor Dashboard**: Shows student chest number, group, enrolled programs, and schedule timings.
- **Quota Meter**: Visual indicator showing `X / 5 Used (Remaining: Y)`.
- **Digital ID Card (`/student/id-card`)**: Printable competitor identity card with QR verification token.
- **Certificates Viewer (`/student/certificates`)**: Downloadable digital certificates for winners and participants.

---

## 3. In Progress Features
- **Auditorium Live Status WebSockets**: Replacing client polling on `/stages/{stage}/projector` with native WebSockets / Server-Sent Events (SSE).
- **Automated Stage Call SMS / WhatsApp Alerts**: Direct notifications to group leaders 15 minutes before their student's scheduled stage appearance.

---

## 4. Planned Features
- **Server-Side Batch PDF Certificate Export**: Bulk certificate generation using Headless Chrome or DomPDF.
- **Bar-code Scanner Integration for Stage Slips**: Physical barcode scanner support at the green room check-in desk.

---

## 5. Deprecated Features
- **Manual Admin Registration Verification**: Replaced by automatic verification on group leader submissions to eliminate enrollment bottlenecks.
- **Standalone Category Models**: Replaced by the 4 official Zones (`A Zone`, `B Zone`, `C Zone`, `Mix Zone`).
- **"House" Terminology**: Fully replaced by "Group" across all models, views, and documentation.
- **JetBrains Mono Removal**: Re-instated as the dedicated tabular font for all numbers, chest numbers, codes, and scores.
