# QUAF Fest 09 — Architecture Decision Records (ADRs)
**Settled Technical Decisions, Trade-offs, and Architectural Rationale**

---

## ADR-001: Selection of SQLite with WAL Mode for High-Speed Festival Operations

### Status
Accepted

### Context
During annual conclaves, server hardware may be constrained (local servers, single VPS, or offline backup laptops). Setting up and maintaining complex MySQL/PostgreSQL replication clusters introduces unnecessary operational failure points.

### Decision
Use SQLite 3 with Write-Ahead Logging (`PRAGMA journal_mode=WAL;`) and a 5000ms busy timeout (`PRAGMA busy_timeout=5000;`).

### Consequences
- **Positive**: Zero external database server dependencies. Entire festival state can be copied in a single `.sqlite` file for offline backup or instant migration. Sub-millisecond reads.
- **Negative**: Long-running write locks can occur if transactions are unmanaged. Mitigated via atomic, short-lived transactions and denormalized point caching.

---

## ADR-002: Consolidation of Category and Zone Concepts into 4 Official Zones

### Status
Accepted

### Context
Previous festival software maintained separate and confusing "Categories" (e.g. Senior, Junior) and "Zones", resulting in ambiguous student assignments and duplicated dropdowns.

### Decision
Eliminate separate standalone categories across the festival data layer and consolidate them strictly into the **4 official academic zones**:
1. **A Zone**: Rabia & Thakhassus (TQS, S4)
2. **B Zone**: Salisa (S3)
3. **C Zone**: Oola & Sani (S1, S2)
4. **Mix Zone**: Open to all academic divisions

### Consequences
- **Positive**: Clear academic mapping, zero duplicate data entry, simplified reporting and print gazettes.
- **Negative**: Legacy historical data must be migrated to one of the 4 designated zones.

---

## ADR-003: Denormalized Points and Rank Caching on Groups and Students

### Status
Accepted

### Context
During live result publishing, hundreds of concurrent visitors hit the public homepage (`/`) and results portal (`/results`). Running on-the-fly SQL `SUM()` and `COUNT()` aggregations over thousands of student entries and scorecards caused CPU spikes.

### Decision
Store denormalized `points_cache` on `students`, and `points_cache` plus `rank_cache` on `groups`. Invalidate and recalculate these fields strictly when an official result is published via `AdminResultController@publish`.

### Consequences
- **Positive**: Public leaderboard queries execute in 0 milliseconds (`SELECT * FROM groups ORDER BY rank_cache ASC`).
- **Negative**: Points cache must be kept strictly synchronized via transactional service hooks whenever results or manual marks are altered.

---

## ADR-004: Server-Rendered Blade + Alpine.js over Heavy Client-Side SPA

### Status
Accepted

### Context
Field coordinators and stage volunteers access the app across diverse mobile devices, spotty festival Wi-Fi networks, and low-end smartphones.

### Decision
Adopt a server-rendered Laravel Blade approach augmented with lightweight Alpine.js for interactive controls (drawers, tabs, filters, modals) and Tailwind CSS v4.

### Consequences
- **Positive**: Instant first-contentful paint, minimal JavaScript payload (under 55KB gzip), flawless browser back-button navigation, high SEO accessibility, and straightforward server-side authentication.
- **Negative**: Page navigations cause light document transitions rather than seamless client-side virtual DOM swaps.

---

## ADR-005: Code-Letter Contestant Anonymization for Jury Scoring

### Status
Accepted

### Context
In competitive arts conclaves, jury members might unconsciously exhibit bias toward specific academic houses or prominent competitors if names or chest numbers are visible.

### Decision
Enforce strict anonymization. Green room operations generate random alphanumeric code letters (`A`, `B`, `C`...). The jury interface displays exclusively the code letter and program title, completely withholding contestant identity and house affiliation until the verdict is declared.

### Consequences
- **Positive**: Uncompromising integrity, complete fairness, and institutional trust in the festival results.
- **Negative**: Requires Green Room coordinators to accurately sequence and verify competitors before they walk onto the stage.
