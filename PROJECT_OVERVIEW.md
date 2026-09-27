# QUAF Fest 09 — Project Overview
**Functional Specification and Domain Architecture**

---

## 1. Executive Summary
QUAF (Season 09) is an enterprise-scale arts and academic festival platform purpose-built for the annual grand conclave conducted by **Ihyaussunna Students Union (ISU)** at **Markazu Saquafathi Sunniyya**. The platform manages **1,168 verified student competitors** across **144 official programs** (both on-stage and off-stage), running concurrently across multiple festival venues.

The primary objectives of QUAF 9.0 are:
- 100% computational accuracy, transparent score calculation, and auditable point transactions.
- Zero-latency result declaration and instantaneous group standings updates.
- Automated code-letter anonymization guaranteeing impartial jury evaluation.
- Dedicated operational portals tailored for field staff, jury members, group leaders, stage announcers, press media, and students.
- High accessibility on mobile and desktop devices with standardized multilingual typography (Sora, Rockwell, JetBrains Mono, and Anek Malayalam).

---

## 2. Institutional Context & Stakeholders

### Organizer
- **Organization**: Ihyaussunna Students Union (ISU)
- **Institution**: Markazu Saquafathi Sunniyya, Karanthur, Kozhikode, Kerala

### Primary Stakeholders
1. **Central Festival Directorate (Super Admins / Admins)**: Monitors festival metrics, issues emergency broadcasts, configures global scoring weights, manages mark entry discrepancies, and performs final result verification and publication.
2. **Program Committee (പ്രോഗ്രാം സമിതി)**: Authors and publishes official program rules, guidelines, criteria weights, and compiles the printable Niyamavali rulebook.
3. **Group Leaders**: Enrolls students, allocates chest numbers, registers individual and group event entries, monitors quota slots (e.g. 2/2 slots filled), edits enrolled students inline, and tracks group standings.
4. **Jury Members (Judges)**: Evaluates live stage and non-stage performances using criteria-based digital marksheets with locked submissions and Grade B+ support.
5. **Green Room Team**: Manages participant call lists, assigns random code letters, verifies chest slips, and sequences contestants before stage entry.
6. **Announcer Desk**: Receives declared results in real-time, announces stage verdicts, and manages stage announcement queues.
7. **Media & Press Desk**: Publishes festival news articles, maintains photo galleries and live streams, and generates branded social media result posters via the Studio editor.
8. **Student Competitors**: Tracks personal program schedules, chest numbers, scores, quota usage (X/5 Used), and accesses digital ID cards and verified certificates.
9. **Public Audience & Alumni**: Follows live scores, group standings, photo dispatches, stage updates, and verified verdicts.

---

## 3. Academic Structure: The 4 Official Zones
In QUAF 09, categories and zones have been unified into **4 official academic divisions**:

| Zone Name | Malayalam Title | Eligible Academic Classes | Scope |
|---|---|---|---|
| **A Zone** | റാബിഅ, തഖസ്സുസ് | Class 4: NF4, UH4, S4, ID4, UT4, L4, TQS (Thakhassus) | Senior academic division |
| **B Zone** | സാലിസ് | Class 3: NF3, ID3, UH3, UT3, S3, L3 | Intermediate academic division |
| **C Zone** | ഊല, സാനി | Class 1 & 2: U1, U2, L2, S1, S2 | Junior academic division |
| **Mix Zone** | ജനറൽ | Open to all academic divisions | General cross-zone & group events |

Every competition and participating student belongs strictly to one of these zones, ensuring equitable competition.

---

## 4. Official Competition Groups
Student competitors are partitioned into exactly 5 official Groups, which compete for the overall QUAF Championship Trophy:
1. **PACTO HIKMIC** (`PACTO`) — Color: Royal Blue (`#2E3192`) | Leader: BASIL ADANY
2. **YUGO RUSHDIC** (`YUGO`) — Color: Berry Magenta (`#AD1E56`) | Leader: JABIR SAQAFI
3. **CONCO MAJDIC** (`CONCO`) — Color: Radiant Golden Yellow (`#F8E709`) | Leader: SINAN SAQAFI VELLIMUTTAM
4. **LUMO FIKRIC** (`LUMO`) — Color: Imperial Violet (`#56286B`) | Leader: WARIS ADANY
5. **UNIO HILMIC** (`UNIO`) — Color: Crimson Maroon (`#7F1518`) | Leader: ANAS ADANY

---

## 5. Core Functional Modules

### Module 1: Student Master, Bulk Import & Chest Numbers
- Central repository of all 1,168 verified student competitors across the 5 groups.
- Standardized numeric chest numbers without hash prefixes (e.g. `1001`, `2001`, `3001`, `4001`, `5001`).
- Bulk student enrollment engine supporting 4-column CSV / Excel uploads with auto-assigned sequential chest numbers.
- 360-degree student profile showing registered events, group affiliation, zone, points tally, and cryptographic QR token.

### Module 2: Program Master & Stage Configuration
- Complete directory of 144 official competitions categorized by Zone and discipline.
- Stage vs. Off-stage classification (`is_stage = true/false`).
- Participant cap settings (e.g., maximum 2 individual competitors per group).
- Filtered dropdowns: Only stage programs appear in stage live-status and auditorium selection dropdowns.
- Automated one-click sync command (`php artisan app:sync-official-programs`) and admin dashboard button.

### Module 3: Green Room, Code Letters & Auditorium Projector
- Automated random code-letter generator (`A`, `B`, `C`, `D`...) that replaces student identities before jury evaluation.
- Printable stage call-lists with participant check-in status and physical chest slips.
- Dedicated Auditorium Projector display (`/stages/{stage}/projector`) showing currently performing and upcoming contestants in real time.

### Module 4: Digital Jury Marking Suite
- Multi-criteria assessment interface tailored for mobile and tablet touchscreens.
- Criteria-based scoring with configurable maximum marks and weights.
- Grade options: A+ (6 pts), A (5 pts), B+ (4 pts), B (3 pts), C (1 pt).
- Multi-judge variance checker and score sheet locking upon submission.

### Module 5: Result Review, Declaration & Dense Ranking Engine
- Multi-stage publication lifecycle: `Pending` -> `Declared` -> `Published`.
- Dense ranking engine supporting ties (e.g. joint 1st, 2nd, or 3rd place) with fair point distribution.
- Hand-off workflow: Declared results can be sent directly to the Announcer Desk and Media Desk.
- Automated point allocation:
  - 1st Place: 5 Points (or weighted equivalent)
  - 2nd Place: 3 Points
  - 3rd Place: 1 Point
  - Grade Points calculated concurrently.

### Module 6: Real-time Leaderboards & Achievements
- Live group standings computed via database cache fields with automated invalidation.
- Individual championship titles:
  - **Kalaprathibha**: Highest point-scoring student overall.
  - **Kalathilakam**: Top performing female student.
  - **Zone Champions**: Top scorers in each of the 4 zones.
- Interactive multi-line performance chart dynamically tracking point progression as results are declared.

### Module 7: Program Committee & Niyamavali Publisher
- Guidelines authoring for all 144 programs with Malayalam typography (`Anek Malayalam`).
- Dynamic printing of individual program rules and a complete Niyamavali booklet with cover page and table of contents.

### Module 8: Media Desk & Result Poster Studio
- Comprehensive press hub for publishing news articles, maintaining photo galleries, and embedding video streams.
- Result Poster Graphics Studio (`/media/results/{result}/studio`): Custom canvas graphics editor for generating social media posters with winner details and group colors.

### Module 9: Announcer Console & Live Queue
- Dedicated announcer desk (`/announcer`) displaying stage call sheets and declared results ready for live auditorium broadcast.

### Module 10: Student Portal & Public Verification
- Student login with personalized quota tracker (X/5 Used), digital ID card, and authentic certificates.
- Cryptographically secure QR verification for winner certificates (`/verify/certificate/{certificateNumber}`) and student credentials (`/verify/student/{qrToken}`).

---

## 6. Important Business Rules
1. **10-Check Concurrency-Safe Eligibility (`EligibilityService`)**: Checks student active state, group assignment, zone matching, program open state, 5-program individual cap, Mix Zone rules, group quotas, and duplicate entries.
2. **Auto-Verified Registrations**: Group leader entries are automatically verified upon creation, eliminating bureaucratic delays and allowing leaders to edit entries directly.
3. **Single Award for Group Events**: Group events award points once to the Group; individual members do not receive duplicate individual points.
4. **Immutable Transaction Ledger (`points_transactions`)**: All points awarded or adjusted are logged in the transaction table with full audit details.

---

## 7. Current Implementation Status
- **Core Festival Engine**: 100% Implemented & Production-Ready.
- **144 Programs & 1,168 Students**: Fully seeded and synchronized into the database.
- **Role Portals (Admin, Leader, Judge, Green Room, Announcer, Media, Program Committee, Student, Public)**: Fully functional.
- **Typography & Theme**: Standardized to Sora, Rockwell, JetBrains Mono, and Anek Malayalam across all layouts.
