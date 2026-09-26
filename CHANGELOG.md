# QUAF Fest 09 — Changelog
**Version History, Releases, and Major Architectural Milestones**

---

## [v9.0.4] — 2026-09-24

### Added
- **5 Official Groups with Brand Colors**: Standardized competition units to exactly 5 Groups: Pacto Hikmic (`#2e3192`), Yugo Rushdic (`#ad1e56`), Conco Majdic (`#f8e709`), Lumo Fikric (`#56286b`), and Unio Hilmic (`#7f1518`).
- **10-Check Eligibility Engine (`App\Services\EligibilityService`)**: Comprehensive validation engine with transactional concurrency locks (`lockForUpdate`), validating student status, group assignment, zone matching, individual limits, Mix Zone access, group quotas, and duplicate entries.
- **Individual 5-Programme Cap**: Students are strictly capped at maximum 5 individual programmes across their own Zone and Mix Zone combined. Attempting a 6th programme is blocked by the backend.
- **Group Programme Multi-Participant Support**: Multi-student group registrations supported via `program_entry_participants` pivot, excluded from the student's individual 5-programme quota, with single-award group scoring.
- **Auditable Points Transaction Ledger (`points_transactions`)**: Fully auditable ledger tracking every point awarded with timestamp, Group, Programme, Result, Student, Source Type (`POSITION`, `GRADE`), and calculation description.
- **Handbook Scoring Standards**: Position Points (1st: 5 pts, 2nd: 3 pts, 3rd: 1 pt) and Grade Points (A+: 6 pts, A: 5 pts, B: 3 pts, C: 1 pt).
- **Points Ledger Table in Admin Panel (`/admin/points`)**: Complete transaction history with filter dropdowns for Group and Source Type.
- **Student Quota Meter (`/student`)**: Visual indicator displaying `X / 5 Used (Remaining: Y)` with active registration listings.
- **12 Comprehensive Feature Tests (`tests/Feature/QuafSeason09RulesAndEligibilityTest.php`)**: Full coverage of all competition rules and edge cases (12/12 passing). Total suite: 33 tests, 168 assertions.
- **Documentation Suite**: Added `PROGRAMME_RULES.md`, `ZONE_AND_GROUP_RULES.md`, `POINTS_RULES.md`, `REGISTRATION_RULES.md`, `ADMIN_GUIDE.md`, `JUDGE_GUIDE.md`, `GROUP_LEADER_GUIDE.md`, and `HANDBOOK_MAPPING.md`.

### Changed
- **Strict Terminology Standard**: Removed "House" and "Team" in favor of "Group". Replaced legacy category usages with explicit Zones (`A Zone`, `B Zone`, `C Zone`, `Mix Zone`) and Programme Categories (disciplines).
- **Admin & Leader Views**: Updated tables, forms, scorecards, and drawer modals to reflect Group brand colors and terminology.

---

## [v9.0.3] — 2026-09-24

### Added
- **Dedicated Admin Zone Section (`/admin/zones`)**: Full-featured administrative dashboard for managing the 4 official festival zones with live event metrics, stage vs. off-stage breakdowns, and interactive tabs for programs and student rosters.
- **Public Homepage Festival Zones (`#zones`)**: Added showcase section displaying all 4 academic zones with eligible class levels and direct result filter links.
- **Admin Sidebar Route Integration**: Fixed 3rd sidebar menu link to route directly to `admin.zones.index` with high-contrast active state indicators.
- **Feature Tests**: Added comprehensive test suite `tests/Feature/AdminZoneTest.php` bringing the test count to 21 passing feature tests (118 assertions).

### Changed
- **Homepage Hero Redesign**: Replaced text title with official `quaf-title-logo.png` and transitioned description and metadata to a responsive side-by-side flex layout.
- **House Standings Optimization**: Removed avatar/profile picture boxes from academic house cards across the public portal.
- **Footer Redesign**: Removed deprecated Season 09 text tags, enlarged the footer title logo, and aligned festival metadata to the side of the logo.

### Fixed
- **Mobile Overflow & Text Clipping**: Eliminated horizontal scrolling bugs on 360px–400px mobile devices, resolving left-edge cutoffs on admin headers and tables.
- **Coding Style**: Fully formatted with Laravel Pint (`vendor/bin/pint --format agent`).

---

## [v9.0.2] — 2026-09-23

### Added
- **Official 4-Zone Consolidation**:
  1. `A Zone`: Rabia & Thakhassus (TQS, S4)
  2. `B Zone`: Salisa (S3)
  3. `C Zone`: Oola & Sani (S1, S2)
  4. `Mix Zone`: Open Category
- **Competition Dataset**: Ingested and synchronized 25 official festival programs from conclave guidelines (`Q9 - 101` to `Q9 - 125`).
- **Student Roster Dataset**: Enrolled initial Group A competitors with standardized chest numbering (`QF1001` to `QF1030`).

### Changed
- Removed deprecated standalone "Category" models in favor of unified Zone assignments across programs, registrations, and reports.

---

## [v9.0.1] — 2026-09-21

### Fixed
- **Database Unique Constraint**: Resolved integrity constraint violation on `program_entries(program_id, chest_number)` during rapid concurrent registration saves.

### Added
- **Anonymized Code Letters**: Added code letter tracking on `program_entries` and 4-digit access code for jury members.
- **Stage Call Sheets**: Printable call-list templates with attendance verification check-ins.

---

## [v9.0.0] — 2026-09-20
- **Initial Core Release**: Laravel 11/12 foundation, core SQLite database schema, multi-guard session authentication, and public portal foundation.
