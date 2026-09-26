# QUAF Fest 09 — Features Specification
**Comprehensive Catalogue of Features and Business Capabilities**

---

## 1. Public Festival Portal (`/`)

### 1.1 Brand Identity & Hero Section
- **Dynamic Title Lockup**: Displays the official QUAF Season 09 logo with side-by-side festival metadata, institution tag, and quick-navigation CTA buttons.
- **Urgent Announcement Ticker**: Top sticky alert bar showing live emergency broadcasts from the festival control room.

### 1.2 Academic Houses Standings (`#groups`)
- Real-time leaderboard cards for all competing academic houses.
- Displays House Code, Rank badge, House Name, Captain/Leader details, and live points tally.
- Clean presentation without avatar boxes or placeholder graphics.

### 1.3 Festival Zones Showcase (`#zones`)
- Visual display of the 4 official academic zones:
  - **A Zone** (റാബിഅ, തഖസ്സുസ് — TQS, S4)
  - **B Zone** (സാലിസ — S3)
  - **C Zone** (ഊല, സാനി — S1, S2)
  - **Mix Zone** (Open Category)
- Quick links to filter official results by zone.

### 1.4 Active Stages & Live Venue Tracker (`#stages`)
- Real-time indicator for each stage venue with color-coded status pills:
  - `LIVE NOW` (Emerald green pulse)
  - `BREAK` (Amber intermission)
  - `CLOSED` (Slate grey)
- Displays currently performing program and the next scheduled event.

### 1.5 Verified Verdicts & Podium Placements (`#results`)
- Instant card rendering of published competition verdicts.
- Gold (1st), Silver (2nd), and Bronze (3rd) medal placements with contestant names, chest numbers, and house codes.
- Direct links to view complete signed score sheets.

### 1.6 Festival Journal & Media Dispatches (`#news`, `#gallery`, `#videos`)
- Editorial articles and announcements with category filtering.
- High-resolution photo gallery with zoom modals.
- Video stream integration for live stage broadcasts.

### 1.7 Cryptographic Certificate Verification (`/verify-certificate/{code}`)
- Public verification portal where any scanned QR code confirms student authenticity, program details, placement, and date of issue.

---

## 2. Central Administration Panel (`/admin`)

### 2.1 Operational Command Dashboard
- 4 Primary stat cards: Total Students, Competitions, Teams, and Active Venues.
- Multi-line performance chart tracking house points progression over time.
- Real-time feed of recent announcements and audit logs.

### 2.2 Academic Houses (Teams) Module
- Full CRUD for competing houses.
- Configuration of house name, unique code, official brand color hex, and manager contact.
- Automated points cache monitoring.

### 2.3 Dedicated Zone Management Dashboard (`/admin/zones`)
- Dedicated administrative overview of the 4 official zones.
- Real-time statistics: Total Programs, Stage vs. Off-stage breakdown, Enrolled Students, Total Points.
- Interactive tabbed interface:
  - **Programs Tab**: Filterable list of competitions with participant limits and entry tallies.
  - **Students Tab**: Filterable roster of competitors with chest numbers, class levels, and house affiliations.

### 2.4 Student Management Suite
- Comprehensive student directory with search by Chest Number, Name, Class, or House.
- Add Student form with automatic validation of required fields and unique chest numbers.
- Student-wise program inspector displaying all events a competitor is registered for.
- Data export in multiple formats.

### 2.5 Program & Competition Engine
- Configuration of individual and group programs with eligibility constraints.
- Stage vs. Off-stage classification.
- Participant cap settings (e.g. 2 participants per team for individual events).
- Program-wise student roster inspector.

### 2.6 Code-Letter Anonymization Engine
- Automated one-click assignment of random code letters (`A`, `B`, `C`...) for checked-in competitors.
- Manual code letter override for stage emergencies.
- Complete separation between jury sheets and competitor identities.

### 2.7 Printable Stage Operations (Forms)
- **Call Lists (`/admin/forms/call-list`)**: Official stage-call sheet with student verification check-boxes, chest numbers, and arrival timestamps.
- **Evaluation Forms (`/admin/forms/evaluation`)**: Printable jury judging sheets with criteria score tables.
- **Chest Slips (`/admin/idcards/chest-slips`)**: Printable competitor badges and stage slips.

### 2.8 Mark Entry & Verification Suite
- **View Marks (`/admin/mark-entry/view-marks`)**: Tabulated matrix of judge marks per competition.
- **Marks Handler (`/admin/mark-entry/handler`)**: Administrative override and bulk mark input.
- **Mark Check (`/admin/mark-entry/check`)**: Discrepancy detector flagging variance between multiple jury sheets.

### 2.9 Official Results Management
- Multi-stage publication lifecycle:
  - Declare results based on judge marks.
  - Review preliminary podium winners.
  - Official sign-off and publication triggering point cache recalculation.

### 2.10 Achievements & Championship Leaderboards
- **Team Score**: Overall house championship ranking with point breakdown.
- **Zone Score**: Filterable ranking of students within each of the 4 zones.
- **Stage Score**: Separate leaderboard for Stage vs. Off-stage excellence.
- **All Student Score**: Global individual student leaderboard.
- **Kalaprathibha & Kalathilakam Tracker**: Automatic identification of top male and female performers.

### 2.11 Stage & Venue Management
- Add, edit, and configure physical stage venues, seat capacities, and current live status.

### 2.12 Jury Management
- Directory of empanelled judges, contact info, and program assignments.

### 2.13 PDF & Export Customizer (`/admin/exports`)
- Dynamic print report builder with toggles for exact columns, house filters, and zone filters.

### 2.14 Settings Drawer (Sliding Control Panel)
- **Mark Settings**: Adjust point weights per program.
- **Limit Settings**: Configure group event team limits.
- **Broadcast Messages**: Send instantaneous notices to teams and students.
- **Deadline Settings**: Configure countdown timers for registration cutoffs.
- **Score Display Settings**: Toggle public score visibility (Off, Limited count, or All).

---

## 3. House Leader Portal (`/leader`)
- Dedicated portal restricted to house captains.
- Register competitors under official chest number formats.
- Enroll house representatives into eligible individual and group programs while enforcing limits.
- View real-time house points tally and program schedule.

---

## 4. Digital Jury Portal (`/judge`)
- Touch-friendly interface optimized for mobile and tablet evaluation.
- Displays assigned programs, contestant code letters, and evaluation criteria sliders/inputs.
- One-click final submission locking to prevent unauthorized score tampering.

---

## 5. Green Room Coordinator Portal (`/greenroom`)
- Real-time contestant check-in and stage preparation.
- Code letter generation and verification.
- Stage-call communication.
