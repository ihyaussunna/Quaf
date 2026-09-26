# QUAF Fest 09 — Development Roadmap & TODO
**Future Enhancements, Optimization Backlog, and Next Priorities**

---

## 1. High Priority (Upcoming Operational Tasks)
- [ ] **Ingest Student Rosters for Remaining Houses**:
  - Group B (CONCO MAJDIC) student enrollment and chest number generation.
  - Group C (LUMO FIKRIC) student enrollment and chest number generation.
  - Group D (UNIO HILMIC) student enrollment and chest number generation.
- [ ] **Additional Program Datasets**:
  - Ingest any secondary competition guidelines issued for B Zone (Salisa) and C Zone (Oola & Sani).
- [ ] **Stage Schedule Population**:
  - Allocate exact dates, start times, and stage venues across all 25+ events.

---

## 2. Medium-Term Enhancements
- [ ] **Server-Sent Events (SSE) / WebSockets Integration**:
  - Live real-time score and leaderboard streaming for large auditorium projector screens without browser refreshes.
- [ ] **WhatsApp / SMS Gateway Integration**:
  - Automated stage-call notifications dispatched to house captains 15 minutes prior to competitor stage entry.
- [ ] **Native PDF Generation**:
  - Integrate server-side PDF generation (`barryvdh/laravel-dompdf` or `spatie/browsershot`) for offline batch certificate printing.

---

## 3. Performance & Infrastructure
- [ ] **Production Redis Caching**:
  - Transition session and cache drivers to Redis for ultra-high concurrency during public result announcements.
- [ ] **Automated SQLite Backup Daemon**:
  - Automated cron task executing `sqlite3 database.sqlite ".backup 'backups/backup.sqlite'"` every 30 minutes during active festival hours.
