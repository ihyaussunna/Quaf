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
git clone <repository-url> "Quaf 9.0"
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

# Run migrations and seed official festival data
php artisan migrate:fresh --seed
```

### Step 5: Install & Build Frontend Assets
> [!NOTE]
> On Windows PowerShell, running `npm.ps1` directly can trigger script execution policy errors. Run via `cmd.exe /c` or use standard npm commands if execution policy is unrestricted:
```powershell
cmd.exe /c npm install
cmd.exe /c npm run build
```

### Step 6: Launch Development Server
```powershell
php artisan serve --port=8000
```
Access the application at [http://127.0.0.1:8000](http://127.0.0.1:8000).

---

## 3. Seeded Default Accounts

| Role | Email | Password | Access Portal |
|---|---|---|---|
| **Super Admin** | `admin@quaf.fest` | `password` | `http://127.0.0.1:8000/admin` |
| **Green Room Officer** | `greenroom@quaf.fest` | `password` | `http://127.0.0.1:8000/greenroom` |
| **Judge (Jury 1)** | `judge1@quaf.fest` | `password` | `http://127.0.0.1:8000/judge` |
| **Judge (Jury 2)** | `judge2@quaf.fest` | `password` | `http://127.0.0.1:8000/judge` |
| **Group A Leader** | `leader.groupa@quaf.fest` | `password` | `http://127.0.0.1:8000/leader` |
| **Group B Leader** | `leader.conco@quaf.fest` | `password` | `http://127.0.0.1:8000/leader` |
| **Student** | `student@quaf.fest` | `password` | `http://127.0.0.1:8000/student` |

---

## 4. Verification & Testing

### Run PHPUnit Test Suite
```powershell
vendor/bin/phpunit
# or with compact output
php artisan test --compact
```
Expected result: **21 tests passing (118 assertions, 0 failures)**.

### Run Code Formatter (Laravel Pint)
```powershell
vendor/bin/pint --format agent
```

---

## 5. Troubleshooting & FAQ

### Issue: `npm : File npm.ps1 cannot be loaded because running scripts is disabled`
**Fix**: Execute npm commands prefixed with `cmd.exe /c`:
```powershell
cmd.exe /c npm run build
```

### Issue: `SQLSTATE[HY000]: General error: 5 database is locked`
**Fix**: SQLite WAL mode and busy timeout are configured in `config/database.php`. If file locks persist in development, execute:
```powershell
php artisan optimize:clear
```

### Issue: Vite Manifest Not Found
**Fix**: Rebuild production assets:
```powershell
cmd.exe /c npm run build
```
