# QUAF Fest 09 — Developer Setup & Installation Guide
**Local Environment Provisioning, Dependencies, and Troubleshooting**

---

## 1. System Requirements
Before running the application, verify that your development machine meets the following prerequisites:
- **PHP**: 8.3 or higher with extensions: `pdo_sqlite`, `sqlite3`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `curl`, `fileinfo`
- **Composer**: 2.5 or higher
- **Node.js**: 18.x or 20.x LTS
- **NPM**: 9.x or higher
- **Operating System**: Windows 10/11, macOS, or Linux

---

## 2. Installation Walkthrough (Windows / PowerShell)

### Step 1: Clone & Navigate
```powershell
git clone https://github.com/ihyaussunna/Quaf.git "Quaf 9.0"
cd "Quaf 9.0"
```

### Step 2: Install PHP Dependencies
```powershell
composer install
```

### Step 3: Environment Configuration
```powershell
copy .env.example .env
php artisan key:generate
```

### Step 4: Database Initialization (Zero-Config SQLite)
QUAF 09 uses SQLite by default for seamless local development:
```powershell
# Create SQLite file if missing
if (-not (Test-Path database/database.sqlite)) { New-Item database/database.sqlite -ItemType File }

# Run migrations and seed foundational roles, groups, and settings
php artisan migrate:fresh --seed
```

### Step 5: Sync Official Festival Data (144 Programs & 1,168 Students)
Run the automated commands to populate the official conclave dataset from CSV:
```powershell
# Ingest 144 official programs with criteria
php artisan app:sync-official-programs

# Ingest 1,168 official students across all 5 groups
php artisan app:sync-official-students
```

### Step 6: Install & Build Frontend Assets
> [!NOTE]
> On Windows PowerShell, running `npm.ps1` directly can trigger script execution policy restrictions. Execute via `cmd.exe /c` or use standard npm commands if your execution policy is unrestricted:
```powershell
cmd.exe /c npm install
cmd.exe /c npm run build
```

### Step 7: Launch Development Server
```powershell
php artisan serve --port=8000
```
Access the application at [http://127.0.0.1:8000](http://127.0.0.1:8000).

---

## 3. Seeded Default Accounts & Portals

| Role | Email / Identifier | Password | Access Portal |
|---|---|---|---|
| **Super Admin / Central Directorate** | `admin@quaf.fest` | `password` | `http://127.0.0.1:8000/admin` |
| **Program Committee (സമിതി)** | `samithi@quaf.fest` | `Samithi#2026@QuafFest!` | `http://127.0.0.1:8000/program-committee` |
| **Green Room Officer** | `greenroom@quaf.fest` | `password` | `http://127.0.0.1:8000/greenroom` |
| **Judge (Jury 1)** | `judge1@quaf.fest` | `password` (PIN: `1234`) | `http://127.0.0.1:8000/judge` |
| **Judge (Jury 2)** | `judge2@quaf.fest` | `password` (PIN: `5678`) | `http://127.0.0.1:8000/judge` |
| **Media / Press Desk** | `media@quaf.fest` | `Media#2026@QuafFest!` | `http://127.0.0.1:8000/media` |
| **Announcer Desk** | `announcer@quaf.fest` | `password` | `http://127.0.0.1:8000/announcer` |
| **Leader (Lumo Fikric)** | `leader.lumo@quaf.fest` | `Lumo#9482@FikricFest!26` | `http://127.0.0.1:8000/leader` |
| **Leader (Pacto Hikmic)** | `leader.pacto@quaf.fest` | `Pacto$Hikmic*8319#Q9` | `http://127.0.0.1:8000/leader` |
| **Leader (Conco Majdic)** | `leader.conco@quaf.fest` | `Majdic&Conco%6724!Apex` | `http://127.0.0.1:8000/leader` |
| **Leader (Unio Hilmic)** | `leader.unio@quaf.fest` | `Unio_5193-Hilmic@9Fest` | `http://127.0.0.1:8000/leader` |
| **Leader (Yugo Rushdic)** | `leader.yugo@quaf.fest` | `Yugo!Rushdic?3825#Shield` | `http://127.0.0.1:8000/leader` |
| **Student Competitor** | `student@quaf.fest` | `password` | `http://127.0.0.1:8000/student` |

---

## 4. Verification & Testing

### Run PHPUnit Test Suite
```powershell
vendor/bin/phpunit
# or with compact output
php artisan test --compact
```

### Run Code Formatter (Laravel Pint)
```powershell
vendor/bin/pint --format agent
```

---

## 5. Production & Deployment Notes

### Server Requirements
- PHP 8.3+ with OPcache, FPM, and required extensions.
- Nginx or Apache with URL rewriting enabled.
- SQLite or MySQL 8.0 / MariaDB 10.6+.

### Live Synchronization Helpers
- **Remote Git Deployment**: `GET /git-pull/{token}` (executes `git pull origin main` and clears route/config caches).
- **Remote Database Re-seed**: `GET /init-database/{token}` (executes fresh migration and seeding remotely).
- **Admin Dashboard 1-Click Sync**: `POST /admin/system/sync-festival-data` (idempotently re-syncs all 144 programs and 1,168 students without data loss).

---

## 6. Troubleshooting & FAQ

### Issue: `npm : File npm.ps1 cannot be loaded because running scripts is disabled`
**Fix**: Execute npm commands prefixed with `cmd.exe /c`:
```powershell
cmd.exe /c npm run build
```

### Issue: `SQLSTATE[HY000]: General error: 5 database is locked`
**Fix**: Increase SQLite timeout in `config/database.php` or ensure proper file write permissions on `database/database.sqlite` and the `database/` directory.

### Issue: `There is no active transaction` during recalculation
**Fix**: Ensure `PointsTransaction::query()->delete()` is used rather than `truncate()`, which triggers an implicit transaction commit in MySQL/SQLite.
