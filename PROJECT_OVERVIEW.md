# QUAF Fest 09 — Project Overview
**Functional Specification and Domain Architecture**

---

## 1. Executive Summary
QUAF (Season 09) is an enterprise-scale arts and academic festival platform purpose-built for the annual grand conclave conducted by **Ihyaussunna Students Union (ISU)** at **Markazu Saquafathi Sunniyya**. The platform manages over a thousand student competitors across dozens of on-stage and off-stage programs, running concurrently across multiple festival venues.

The primary objective of QUAF 9.0 is to ensure:
- 100% computational accuracy and transparent score calculation.
- Zero latency in result publishing and house standings updates.
- Automated code-letter anonymization to guarantee impartial jury evaluation.
- High usability on mobile devices for field volunteers, stage coordinators, and jury members.

---

## 2. Institutional Context & Stakeholders

### Organizer
- **Organization**: Ihyaussunna Students Union (ISU)
- **Institution**: Markazu Saquafathi Sunniyya, Karanthur, Kozhikode, Kerala

### Primary Stakeholders
1. **Central Festival Directorate (Super Admins)**: Monitors festival metrics, issues emergency broadcasts, configures global scoring weights, and performs final result verification.
2. **House Captains & Leaders**: Enrolls students, allocates chest numbers, registers individual and group event entries, and monitors house points.
3. **Jury Members (Judges)**: Evaluates live stage and non-stage performances using criteria-based digital marksheets with locked submissions.
4. **Green Room Team**: Manages participant call lists, assigns random code letters, verifies chest slips, and sequences contestants before stage entry.
5. **Student Competitors**: Tracks personal program schedules, chest numbers, scores, and downloads authentic digital certificates.
6. **Public Audience & Alumni**: Follows live scores, house standings, photo dispatches, stage updates, and verified verdicts.

---

## 3. Academic Structure: The 4 Official Zones
In QUAF 09, categories and zones have been unified into **4 official academic divisions**:

| Zone Name | Malayalam Title | Eligible Academic Classes | Scope |
|---|---|---|---|
| **A Zone** | റാബിഅ, തഖസ്സുസ് | TQS (Thakhassus), S4 (Rabia) | Senior academic division |
| **B Zone** | സാലിസ | S3 (Salisa) | Intermediate division |
| **C Zone** | ഊല, സാനി | S1 (Oola), S2 (Sani) | Junior division |
| **Mix Zone** | എല്ലാ സോണുകൾക്കും | Open to all academic levels | General & Open group events |

Every competition and participating student belongs strictly to one of these zones, ensuring equitable competition.

---

## 4. Official Competition Groups
Student competitors are partitioned into exactly 5 official Groups, which compete for the overall QUAF Championship Trophy:
1. **PACTO HIKMIC** (`PACTO`) — Color: Royal Blue (`#2e3192`)
2. **YUGO RUSHDIC** (`YUGO`) — Color: Berry Magenta (`#ad1e56`)
3. **CONCO MAJDIC** (`CONCO`) — Color: Radiant Golden Yellow (`#f8e709`)
4. **LUMO FIKRIC** (`LUMO`) — Color: Imperial Violet (`#56286b`)
5. **UNIO HILMIC** (`UNIO`) — Color: Crimson Maroon (`#7f1518`)

---

## 5. Core Functional Modules

### Module 1: Student Master & Chest Number System
- Central repository of all student competitors.
- Unique format chest numbers (e.g., `QF1001`, `QF1002`).
- 360-degree student profile showing registered events, house affiliation, zone, points tally, and cryptographic QR token.

### Module 2: Competition Master & Stage Configuration
- Complete directory of stage and off-stage competitions (e.g., Qira'ath, Sulook, Malayalam Speech, QUAF x Talk, etc.).
- Event limit constraints (individual participant caps and group team limits).
- Points weight configuration per program (Default: 5 for individual, 10 for group events).

### Module 3: Green Room & Anonymization Engine
- Automated code-letter generator (A, B, C, D...) that replaces student identities before jury evaluation.
- Printable stage call-lists with participant check-in status.
- Stage entry verification with chest slips.

### Module 4: Digital Jury Marking Suite
- Multi-criteria assessment interface tailored for mobile and tablet devices.
- Criteria-based scoring (e.g., Pronunciation, Content, Presentation, Time adherence).
- Automatic calculation of averages and rankings across multiple judges.

### Module 5: Result Review & Declaration Engine
- Staged result progression: `Draft` -> `Submitted` -> `Under Review` -> `Declared` -> `Published`.
- 1st, 2nd, and 3rd place podium placement.
- Automatic point allocation:
  - 1st Place: 5 Points (or weighted equivalent)
  - 2nd Place: 3 Points
  - 3rd Place: 1 Point
  - Grade Points (A Grade, B Grade, C Grade) calculated concurrently.

### Module 6: Real-time Leaderboards & Achievements
- Live house standings computed via database cache fields with automated invalidation.
- Individual championship titles:
  - **Kalaprathibha**: Highest point-scoring male student overall.
  - **Kalathilakam**: Highest point-scoring female student overall.
  - **Zone Champions**: Top scorer in each of the 4 zones.

### Module 7: Print & PDF Reporting Suite
- High-definition print customizer for Programs, Students, Call Lists, Marks Sheets, and Official Gazette.
- Filterable by House, Zone, Stage Venue, or Completion status.

### Module 8: Public Portal & Media Hub
- Mobile-first responsive public website with live announcement ticker.
- Real-time active stage trackers showing currently performing and next-up items.
- Festival journal news dispatches and high-resolution photo galleries.
- Cryptographically signed certificate verification system.
