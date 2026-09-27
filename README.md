# QUAF Fest — Season 09
**Official Festival Management & Administration System**  
*Organized by Ihyaussunna Students Union (ISU), Markazu Saquafathi Sunniyya*

---

## 1. Project Overview
QUAF Fest Season 09 is a comprehensive, production-grade festival management web application designed to handle end-to-end operations of a large-scale academic and cultural conclave. The platform automates student registrations, stage scheduling, jury evaluations, real-time mark tabulation, auditable group points tracking, rules publication, media poster generation, and public result declarations across **144 official programs** and **1,168 participating students**.

### The 5 Official Competition Groups
1. **Pacto Hikmic** (`#2E3192`) — Royal Blue (Leader: BASIL ADANY)
2. **Yugo Rushdic** (`#AD1E56`) — Berry Magenta (Leader: JABIR SAQAFI)
3. **Conco Majdic** (`#F8E709`) — Radiant Golden Yellow (Leader: SINAN SAQAFI VELLIMUTTAM)
4. **Lumo Fikric** (`#56286B`) — Imperial Violet (Leader: WARIS ADANY)
5. **Unio Hilmic** (`#7F1518`) — Crimson Maroon (Leader: ANAS ADANY)

### The 4 Official Festival Zones
- **A Zone** (റാബിഅ, തഖസ്സുസ്): Senior academic division — Class 4 (NF4, UH4, S4, ID4, UT4, L4, TQS)
- **B Zone** (സാലിസ്): Intermediate academic division — Class 3 (NF3, ID3, UH3, UT3, S3, L3)
- **C Zone** (ഊല, സാനി): Junior academic division — Class 1 & 2 (U1, U2, L2, S1, S2)
- **Mix Zone** (ജനറൽ): General cross-zone division open to all academic levels

### Core Competition Rules
- **Scale**: 144 official competition programs and 1,168 verified student participants.
- **Individual Event Limit**: Maximum **5 individual programmes** per candidate (own Zone + Mix Zone combined). Attempting a 6th is strictly blocked by the system.
- **Group Programmes**: Multi-participant entries that **do not count** toward any participant's individual quota.
- **Auto-Verification**: Group leader registrations are automatically approved upon submission, allowing leaders to edit enrolled students directly.
- **Dense Ranking with Ties**: Supports tied ranks (e.g. joint 1st/2nd/3rd) with automated equal point distribution.
- **Auditable Points Ledger**: Every point awarded is logged in the `points_transactions` table with timestamps and source types (`POSITION`, `GRADE`).
- **Handbook Points Standard**: Position Points (1st: 5 pts, 2nd: 3 pts, 3rd: 1 pt) and Grade Points (A+: 6 pts, A: 5 pts, B+: 4 pts, B: 3 pts, C: 1 pt).

---

## 2. Tech Stack & Dependencies
- **Backend Framework**: Laravel 11 / 12 (PHP 8.3+)
- **Database**: SQLite (default local), MySQL / MariaDB compatible
- **Frontend Engine**: Blade Templates + Alpine.js 3.x
- **CSS Engine**: Tailwind CSS v4 with custom brand tokens and dark/light themes
- **Typography Engine**:
  - **Sora**: UI labels, navigation, buttons, body copy, and section headers
  - **Rockwell**: Display typography and brand titles
  - **JetBrains Mono**: Numbers, chest numbers, scores, codes, ranks, and statistics
  - **Anek Malayalam**: Dedicated typography strictly for Malayalam script (rules, criteria, news, names)
- **Asset Bundler**: Vite 6+
- **Code Formatter**: Laravel Pint (`vendor/bin/pint --format agent`)
- **Testing Suite**: PHPUnit 11+ (`vendor/bin/phpunit`)

---

## 3. Core Portals & Features
1. **Public Festival Portal (`/`)**: Real-time scoreboards, verified verdicts, stage schedules, festival news, photo gallery, live video streams, and student/certificate verification.
2. **Central Administration Panel (`/admin`)**: Complete festival command center for Groups, Zones, Students, Programs, Stages, Mark Entry, Results Declaration, Exports, and Audit Logs.
3. **Dedicated Zone Dashboard (`/admin/zones`)**: Dedicated tracking for all 4 official festival zones with live event metrics and student rosters.
4. **Group Leader Portal (`/leader`)**: Group leaders can register students, track quota slots (e.g., 2/2 Filled), edit enrolled students inline, and monitor live standings.
5. **Jury Evaluation Portal (`/judge`)**: Criteria-based scoring with touch-friendly controls, Grade B+ option, instant tabulation, and score locking.
6. **Green Room Operations (`/greenroom`)**: Secret code-letter generation, stage call sheets, chest slips, and contestant sequencing.
7. **Auditorium Projector View (`/stages/{stage}/projector`)**: High-contrast live stage display showing currently performing and upcoming contestants for projectors and big screens.
8. **Program Committee / Niyamavali (`/program-committee`)**: Guidelines management and automated print engine for individual rules and the complete Niyamavali booklet.
9. **Media & Press Desk (`/media`)**: News publishing, photo gallery, video archive, and Result Poster Graphics Studio for instant social media graphics.
10. **Announcer Desk (`/announcer`)**: Stage announcement queue, call sheets, and real-time result dispatching.
11. **Student Competitor Portal (`/student`)**: Competitor dashboard with quota meter (X/5 Used), enrolled programs, schedule, digital ID card, and verified certificates.
12. **Cryptographic Verification (`/verify/certificate/{certificateNumber}` & `/verify/student/{qrToken}`)**: Public QR-code verification for authentic winner certificates and student credentials.

---

## 4. User Roles & Access
| Role | Portal URL | Primary Responsibilities |
|---|---|---|
| **Super Admin / Admin** | `/admin` | Complete festival governance, mark handler, results declaration, audit logs |
| **Program Committee** | `/program-committee` | Program guidelines, scoring criteria, and Niyamavali rules book publishing |
| **Group Leader** | `/leader` | Student enrollment, program allocation, quota management, house roster |
| **Judge (Jury)** | `/judge` | Digital marksheets, criteria-based evaluation, score submission |
| **Green Room Coordinator** | `/greenroom` | Contestant check-in, code letters, stage call lists, chest slips |
| **Announcer Desk** | `/announcer` | Live stage announcements, results broadcast queue, call sheets |
| **Media / Press Desk** | `/media` | Festival news, photo gallery, video streams, result poster studio |
| **Student** | `/student` | Program schedule, quota status, digital ID card, certificates |
| **Public Visitor** | `/` | Live scoreboards, verified verdicts, stage status, news, gallery |

---

## 5. Quick Start

### Prerequisites
- PHP 8.3 or higher with extensions: `pdo_sqlite`, `sqlite3`, `mbstring`, `openssl`, `curl`, `fileinfo`
- Composer 2.x
- Node.js 18+ and npm

### Step-by-Step Setup
```bash
# 1. Clone repository & navigate
git clone https://github.com/ihyaussunna/Quaf.git "Quaf 9.0"
cd "Quaf 9.0"

# 2. Install PHP Dependencies
composer install

# 3. Environment Configuration
copy .env.example .env
php artisan key:generate

# 4. Database Setup & Seeding
touch database/database.sqlite
php artisan migrate:fresh --seed

# 5. Sync Official Data (144 Programs & 1,168 Students)
php artisan app:sync-official-programs
php artisan app:sync-official-students

# 6. Install & Compile Frontend Assets
npm install
npm run build

# 7. Start Development Server
php artisan serve --port=8000
```
Open [http://127.0.0.1:8000](http://127.0.0.1:8000) in your browser.

---

## 6. Official Credentials (Development)
- **Central Admin**: `admin@quaf.fest` / `password`
- **Program Samithi**: `samithi@quaf.fest` / `Samithi#2026@QuafFest!`
- **Green Room Officer**: `greenroom@quaf.fest` / `password`
- **Judge 1**: `judge1@quaf.fest` / `password`
- **Judge 2**: `judge2@quaf.fest` / `password`
- **Group Leaders**:
  - **Lumo Fikric**: `leader.lumo@quaf.fest` / `Lumo#9482@FikricFest!26`
  - **Pacto Hikmic**: `leader.pacto@quaf.fest` / `Pacto$Hikmic*8319#Q9`
  - **Conco Majdic**: `leader.conco@quaf.fest` / `Majdic&Conco%6724!Apex`
  - **Unio Hilmic**: `leader.unio@quaf.fest` / `Unio_5193-Hilmic@9Fest`
  - **Yugo Rushdic**: `leader.yugo@quaf.fest` / `Yugo!Rushdic?3825#Shield`
- **Student**: `student@quaf.fest` / `password`
