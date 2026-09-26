# QUAF Fest — Season 09
**Official Festival Management & Administration System**  
*Organized by Ihyaussunna Students Union (ISU), Markazu Saquafathi Sunniyya*

---

## 1. Project Overview
QUAF Fest Season 09 is a comprehensive, production-grade festival management web application designed to handle end-to-end operations of a large-scale academic and cultural conclave. The platform automates student registrations, stage scheduling, judge evaluations, real-time mark tabulation, auditable group points tracking, and public result declarations.

### The 5 Official Competition Groups
1. **Pacto Hikmic** (`#2e3192`) — Royal Blue
2. **Yugo Rushdic** (`#ad1e56`) — Berry Magenta
3. **Conco Majdic** (`#f8e709`) — Golden Yellow
4. **Lumo Fikric** (`#56286b`) — Violet Purple
5. **Unio Hilmic** (`#7f1518`) — Crimson Maroon

### The 4 Official Festival Zones
- **A Zone** (`A_ZONE`): Classes 1 to 4
- **B Zone** (`B_ZONE`): Classes 5 to 7
- **C Zone** (`C_ZONE`): Classes 8 to 10
- **Mix Zone** (`MIX_ZONE`): General / Combined cross-zone events

### Core Competition Rules
- **Individual Event Limit**: Maximum **5 individual programmes** per candidate (own Zone + Mix Zone combined). Attempting a 6th is strictly blocked.
- **Group Programmes**: Multi-participant entries that **do not count** toward any participant's individual limit.
- **Auditable Points Ledger**: Every point awarded (Position 5/3/1, Grade A+:6 / A:5 / B:3 / C:1) is logged in the `points_transactions` ledger table.

---

## 2. Tech Stack & Dependencies
- **Backend Framework**: Laravel 11 / 12 (PHP 8.3+)
- **Database**: SQLite (default local), MySQL / MariaDB compatible
- **Frontend Engine**: Blade Templates + Alpine.js 3.x
- **CSS Engine**: Tailwind CSS v4 with custom brand tokens
- **Asset Bundler**: Vite 6+
- **Code Formatter**: Laravel Pint (`vendor/bin/pint --format agent`)
- **Testing Suite**: PHPUnit 11+ (`vendor/bin/phpunit`)

---

## 3. Core Features
1. **Public Festival Portal**: Live leaderboards, verified verdicts, stage schedules, news dispatches, photo galleries, and certificate QR verification.
2. **Central Administration Panel**: Complete management of Groups, Zones, Students, Competitions, Stages, Call Slips, Marks, Results, and Auditable Points Ledger.
3. **Dedicated Zone Dashboard (`/admin/zones`)**: Dynamic tracking for all 4 official festival zones with live event metrics and student rosters.
4. **Group Leader Portal (`/leader`)**: Group leaders can register students, manage group rosters, and monitor live standings.
5. **Jury Evaluation Portal (`/judge`)**: Multi-judge scoring with criteria weights, instant tabulation, and score locking.
6. **Green Room Operations (`/greenroom`)**: Secret code-letter generation, call lists, chest slips, and stage sequencing.
7. **Certificate Verification**: Cryptographically secure QR-code verification for winner and participation certificates.

---

## 4. User Roles & Access
| Role | Portal URL | Primary Responsibilities |
|---|---|---|
| **Super Admin / Admin** | `/admin` | Full system control, registrations, results, settings |
| **Group Leader** | `/leader` | Student entries, group roster, category allocation |
| **Judge** | `/judge` | Digital marksheets, criteria evaluation, scoring |
| **Green Room Coordinator** | `/greenroom` | Stage coordination, code-letters, call lists |
| **Student** | `/student` | Profile, quota meter (X/5 Used), enrolled programs, points |
| **Public User** | `/` | Live results, group standings, gallery, announcements |

---

## 5. Quick Start (5 Minutes)

### Prerequisites
- PHP 8.3 or higher with `sqlite3`, `pdo_sqlite`, `mbstring`, `openssl`, `curl` extensions
- Composer 2.x
- Node.js 18+ and npm

### Step-by-Step Setup
```bash
# 1. Clone repository & install PHP dependencies
composer install

# 2. Environment Configuration
copy .env.example .env
php artisan key:generate

# 3. Database Migration & Seeding
touch database/database.sqlite
php artisan migrate --seed

# 4. Frontend Compilation
npm install
npm run build

# 5. Start Development Server
php artisan serve --port=8000
```
Open [http://127.0.0.1:8000](http://127.0.0.1:8000) in your browser.

---

## 6. Official Credentials (Development)
- **Admin**: `admin@quaf.fest` / `password`
- **Green Room**: `greenroom@quaf.fest` / `password`
- **Judge 1**: `judge1@quaf.fest` / `password`
- **Group Leader (Group A)**: `leader.groupa@quaf.fest` / `password`
- **Student**: `student@quaf.fest` / `password`

---

## 7. Documentation Directory Map
For detailed festival rules and engineering documentation, refer to the following files:

### Festival Rules & Operational Guides
- [PROGRAMME_RULES.md](PROGRAMME_RULES.md) — Individual vs Group programmes, caps, and discipline categorisation
- [ZONE_AND_GROUP_RULES.md](ZONE_AND_GROUP_RULES.md) — 5 official Groups (colors/codes), 4 Zones, and terminology rules
- [POINTS_RULES.md](POINTS_RULES.md) — Position (5/3/1), Grade (A+:6 / A:5 / B:3 / C:1), and Auditable Ledger
- [REGISTRATION_RULES.md](REGISTRATION_RULES.md) — 10-Check Eligibility Engine, Max 5 limit, and concurrency locking
- [ADMIN_GUIDE.md](ADMIN_GUIDE.md) — Step-by-step administrator management manual
- [JUDGE_GUIDE.md](JUDGE_GUIDE.md) — Score sheet evaluation and marking guidelines
- [GROUP_LEADER_GUIDE.md](GROUP_LEADER_GUIDE.md) — Group leader registration and delegate tracking instructions
- [HANDBOOK_MAPPING.md](HANDBOOK_MAPPING.md) — Mapping of QUAF Season 09 Handbook rules to code implementation

### Technical Architecture & Architecture Specifications
- [PROJECT_OVERVIEW.md](PROJECT_OVERVIEW.md) — Comprehensive functional specifications and domain architecture
- [SYSTEM_ARCHITECTURE.md](SYSTEM_ARCHITECTURE.md) — Technical diagrams, design patterns, and calculation engines
- [FEATURES.md](FEATURES.md) — Detailed feature inventory and business rules
- [USER_ROLES.md](USER_ROLES.md) — Access control matrix and authorization policies
- [DATABASE.md](DATABASE.md) — Schema breakdown, ER relationships, and table definitions
- [API_DOCUMENTATION.md](API_DOCUMENTATION.md) — Complete route catalogue and request/response specifications
- [FOLDER_STRUCTURE.md](FOLDER_STRUCTURE.md) — Directory index and file responsibilities
- [WORKFLOW.md](WORKFLOW.md) — Step-by-step lifecycle from registration to result publication
- [SETUP.md](SETUP.md) — Exhaustive installation, troubleshooting, and local deployment guide
- [ENVIRONMENT.md](ENVIRONMENT.md) — Environment variables specification
- [DESIGN_SYSTEM.md](DESIGN_SYSTEM.md) — Typography, palette tokens, and UI standards
- [SECURITY.md](SECURITY.md) — Security policies, validation rules, and audit logs
- [CHANGELOG.md](CHANGELOG.md) — Version release logs and architectural upgrades
- [TODO.md](TODO.md) — Roadmap and pending feature enhancements
- `docs/` — Deep-dive sequence flows, deployment guides, and architectural decision records (ADRs)
