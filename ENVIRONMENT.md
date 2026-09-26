# QUAF Fest 09 — Environment Variables Guide (`.env`)
**Configuration Dictionary and Environment Parameters**

---

## 1. Core Application Variables

```env
# Application Identity & Mode
APP_NAME="QUAF — Season 09"
APP_ENV=local
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=true
APP_TIMEZONE=Asia/Kolkata
APP_URL=http://127.0.0.1:8000
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
```

- `APP_NAME`: Name displayed in page titles, certificate headers, and system logs.
- `APP_ENV`: `local` for active development; `production` for live deployment.
- `APP_DEBUG`: Set to `false` in production to prevent leaking sensitive stack traces.
- `APP_TIMEZONE`: Official conclave timezone (`Asia/Kolkata`).

---

## 2. Database Connection Variables

### Default Local Configuration (SQLite)
```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```
- Fully self-contained zero-config database stored locally on disk.

### Production Alternative (MySQL / MariaDB)
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=quaf_fest09
DB_USERNAME=quaf_dbuser
DB_PASSWORD=YOUR_STRONG_PASSWORD
```

---

## 3. Session, Cache & Queue Configuration

```env
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

CACHE_STORE=database
QUEUE_CONNECTION=sync
```
- `SESSION_DRIVER`: Uses `database` session storage to maintain persistent multi-role user sessions.
- `QUEUE_CONNECTION`: `sync` for immediate synchronous execution in local development; configure `database` or `redis` for production queue workers.

---

## 4. Festival Operational Settings
In addition to `.env`, dynamic festival settings are stored in the `festival_settings` database table and can be manipulated on-the-fly via the Admin Settings Drawer:

| Key | Default Value | Purpose |
|---|---|---|
| `festival_name` | `QUAF — Season 09` | Official conclave title |
| `festival_dates` | `October 24 - 28, 2026` | Conclave calendar range |
| `tagline` | `The Grand Cultural Conclave of Talents` | Official tagline |
| `organizer` | `Ihyaussunna Students Union, Markazu Saquafathi Sunniyya` | Organizing entity |
| `live_fest_mode` | `1` | Toggles live results & countdown status |
| `registration_open` | `1` | Toggles competitor registration window |
| `score_display_limit` | `All` | Limits number of declared scores visible publicly |
