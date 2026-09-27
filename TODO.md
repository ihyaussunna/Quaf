# QUAF Fest 09 — Development Roadmap & TODO
**Future Enhancements, Optimization Backlog, and Next Priorities**

---

## 1. Completed Milestones (Season 09 Ingestion & Core Workflows)
- [x] **Ingest Student Rosters for All 5 Groups**:
  - Enrolled all 1,168 official students across Pacto Hikmic, Yugo Rushdic, Conco Majdic, Lumo Fikric, and Unio Hilmic with standard chest numbers (`QF1001`+).
- [x] **144 Official Programs Ingestion**:
  - Ingested and categorized all 144 programs (`Q9-001` to `Q9-144`) spanning A Zone, B Zone, C Zone, and Mix Zone via `php artisan app:sync-official-programs`.
- [x] **Program Committee Portal & Rulebook**:
  - Dedicated workspace with interactive Niyamavali rulebook browser and one-click printable compilation view.
- [x] **Media Result Poster Studio**:
  - Canvas/browser-based graphics editor supporting 1:1, 4:5, and 9:16 aspect ratios with rich styling and image export.
- [x] **Stage Announcer Desk**:
  - Live stage call sheet generation, call list printing, and stage-only program filtering.
- [x] **Auditorium Projector View**:
  - Full-screen live dark-mode display for stage screens with current competitor and next-up roster.
- [x] **Leader Auto-Verification & Inline Editing**:
  - Removed admin bottleneck by automatically marking registrations as verified upon submission, with inline edit capability.
- [x] **Multi-Script Typography Hierarchy**:
  - Standardized font family pairing across all views (Sora, Rockwell, JetBrains Mono, Anek Malayalam).
- [x] **Dense Ranking & Grade B+ Scoring**:
  - Integrated dense ranking logic with tied positions and Grade B+ support into `PointCalculationService`.
- [x] **Full Documentation Suite Overhaul**:
  - Synchronized all 13 core documentation files with the active codebase as single source of truth.

---

## 2. High Priority (Upcoming Festival Operations)
- [ ] **Real-Time Live Updates (WebSockets / Server-Sent Events)**:
  - Integrate Laravel Reverb or SSE for live score updates, stage transitions, and leaderboard changes without manual browser refresh.
- [ ] **Automated WhatsApp / SMS Stage Alerts**:
  - Dispatch automated stage-call alerts to group leaders 15 minutes prior to scheduled competitor stage appearances.
- [ ] **Native Server-Side PDF Certificate Engine**:
  - Integrate `barryvdh/laravel-dompdf` or `spatie/browsershot` for batch generation and printing of official merit and participation certificates with QR code verification.

---

## 3. Medium-Term Enhancements
- [ ] **Offline-Capable PWA Support**:
  - Service worker caching for Green Room coordinators and Judges operating in areas with intermittent auditorium Wi-Fi.
- [ ] **Automated Backup Daemon**:
  - Background cron task executing `sqlite3 database.sqlite ".backup 'backups/backup.sqlite'"` every 30 minutes during active festival hours with rotation.
- [ ] **Media Auto-Publishing**:
  - Webhook integration to push generated result posters directly to official festival Telegram or WhatsApp channels.

---

## 4. Performance & Infrastructure
- [ ] **Production Redis Caching**:
  - Transition session, queue, and cache drivers to Redis for ultra-high concurrency during public result announcements.
- [ ] **Database Query Optimization & Static Asset Caching**:
  - Audit eager loading (`withCount`, `with(['group', 'student', 'program'])`) across leaderboard queries and configure CDN/HTTP caching headers for font assets and logos.
