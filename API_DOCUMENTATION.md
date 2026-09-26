# QUAF Fest 09 — Route & API Documentation
**Complete Catalogue of HTTP Endpoints, Request Parameters, and Gateways**

---

## 1. Public Routes (No Authentication Required)

| Method | Endpoint | Route Name | Description | Response |
|---|---|---|---|---|
| `GET` | `/` | `home` | Main public festival homepage with hero, standings, zones, active stages, news, and gallery | HTML (View) |
| `GET` | `/results` | `results.index` | Public festival results archive with search & zone/house/stage filters | HTML (View) |
| `GET` | `/results/{program}` | `results.show` | Complete verified score sheet and placements for a program | HTML (View) |
| `GET` | `/news` | `news.index` | Festival editorial journal articles | HTML (View) |
| `GET` | `/news/{slug}` | `news.show` | Full article content view | HTML (View) |
| `GET` | `/gallery` | `gallery.index` | Photo gallery archive with category filter | HTML (View) |
| `GET` | `/videos` | `videos.index` | Video library and live stream player | HTML (View) |
| `GET` | `/verify-certificate/{code}` | `verify-certificate` | Cryptographic QR certificate authenticity verification | HTML (View) |

---

## 2. Authentication Gateways

| Method | Endpoint | Route Name | Middleware | Description |
|---|---|---|---|---|
| `GET` | `/login` | `login` | `guest` | Unified portal login screen |
| `POST` | `/login` | `login.submit` | `guest` | Authenticates user credentials and redirects to role dashboard |
| `POST` | `/logout` | `logout` | `auth` | Invalidates active session and regenerates CSRF token |
| `GET` | `/judge/login` | `judge.login` | `guest` | 4-Digit PIN quick-login for jury members |
| `POST` | `/judge/login` | `judge.login.submit` | `guest` | Validates judge access PIN |

---

## 3. Central Administration Endpoints (`prefix: /admin`)
*Middleware: `['auth', 'role:admin,super_admin']`*

### 3.1 Core Navigation & Zones
- `GET /admin` (`admin.dashboard`) — Central festival command analytics and metrics.
- `GET /admin/zones` (`admin.zones.index`) — Dedicated Zone Management Dashboard (A Zone, B Zone, C Zone, Mix Zone with event and student listings).
- `GET /admin/profile` (`admin.profile.edit`) — Admin profile settings.

### 3.2 Academic Houses (Teams)
- `GET /admin/groups` (`admin.groups.index`) — House list with points and manager contacts.
- `GET /admin/groups/create` (`admin.groups.create`) — Add new academic house.
- `POST /admin/groups` (`admin.groups.store`) — Store house record.
- `GET /admin/groups/{group}/edit` (`admin.groups.edit`) — Edit house properties and leader assignment.
- `PUT /admin/groups/{group}` (`admin.groups.update`) — Update house record.

### 3.3 Students Master
- `GET /admin/students` (`admin.students.index`) — Complete student competitor directory.
- `GET /admin/students/create` (`admin.students.create`) — Add student form.
- `POST /admin/students` (`admin.students.store`) — Save student with chest number and QR token.
- `GET /admin/students/{student}` (`admin.students.show`) — 360-degree profile.
- `GET /admin/students/{student}/edit` (`admin.students.edit`) — Edit student details.
- `PUT /admin/students/{student}` (`admin.students.update`) — Update student record.
- `DELETE /admin/students/{student}` (`admin.students.destroy`) — Remove student.
- `GET /admin/students-wise` (`admin.students.student-wise`) — Enrolled events per student.
- `GET /admin/students/export` (`admin.students.export`) — Download CSV/Excel roster.

### 3.4 Competitions (Programs)
- `GET /admin/programs` (`admin.programs.index`) — Program directory with status and limit badges.
- `GET /admin/programs/create` (`admin.programs.create`) — Create new program.
- `POST /admin/programs` (`admin.programs.store`) — Store program record.
- `GET /admin/programs/{program}` (`admin.programs.show`) — Program details, participants, and schedule.
- `GET /admin/programs/{program}/edit` (`admin.programs.edit`) — Edit program configuration.
- `PUT /admin/programs/{program}` (`admin.programs.update`) — Update program.
- `GET /admin/programs-wise` (`admin.programs.program-wise`) — Contestants enrolled per program.
- `POST /admin/programs/{program}/criteria` (`admin.programs.criteria.update`) — Update evaluation criteria.

### 3.5 Code Letters & Stage Operations
- `GET /admin/code-letters` (`admin.code-letters.index`) — Code letter management matrix.
- `POST /admin/code-letters/{program}/save` (`admin.code-letters.save`) — Save manual letter assignments.
- `POST /admin/code-letters/{program}/auto-assign` (`admin.code-letters.auto-assign`) — Automated random code letter generator.
- `GET /admin/forms/call-list` (`admin.forms.call-list`) — Printable stage call list.
- `GET /admin/forms/evaluation` (`admin.forms.evaluation`) — Printable judge scoring sheet.
- `GET /admin/idcards/chest-slips` (`admin.idcards.chest-slips`) — Printable chest badges.

### 3.6 Marks & Results
- `GET /admin/mark-entry/view-marks` (`admin.mark-entry.view-marks`) — Tabulated judge marksheets.
- `GET /admin/mark-entry/handler` (`admin.mark-entry.handler`) — Direct administrative mark entry.
- `POST /admin/mark-entry/save` (`admin.mark-entry.save`) — Store administrative marks.
- `GET /admin/mark-entry/check` (`admin.mark-entry.check`) — Multi-judge variance checker.
- `GET /admin/results/declare` (`admin.results.declare`) — Stage for declaring pending results.
- `POST /admin/results/store` (`admin.results.store`) — Save declared result.
- `GET /admin/results/declared` (`admin.results.declared`) — List of declared results awaiting final sign-off.
- `POST /admin/results/{result}/publish` (`admin.results.publish`) — Final publication (updates points caches).
- `GET /admin/results/specified` (`admin.results.specified`) — Single competition result view.
- `GET /admin/results/all` (`admin.results.all`) — Complete official results gazette.

### 3.7 Achievements & Leaderboards
- `GET /admin/achievements/team-score` (`admin.achievements.team-score`) — House standings scoreboard.
- `GET /admin/achievements/zone-score` (`admin.achievements.zone-score`) — Student standings filterable by Zone.
- `GET /admin/achievements/stage-score` (`admin.achievements.stage-score`) — Stage vs Off-stage leaderboards.
- `GET /admin/achievements/all-students` (`admin.achievements.all-students`) — Global student points rankings.
- `GET /admin/top-scorers` (`admin.top-scorers.index`) — Kalaprathibha, Kalathilakam, and Zone Toppers.

### 3.8 Settings Drawer Endpoints
- `POST /admin/settings/mark-settings` (`admin.settings.mark-settings`) — Update program point weights.
- `POST /admin/settings/limit-settings` (`admin.settings.limit-settings`) — Update group event limits.
- `POST /admin/settings/broadcast` (`admin.settings.broadcast`) — Send emergency broadcast alert.
- `POST /admin/settings/deadline` (`admin.settings.deadline`) — Set registration cutoff countdown.
- `POST /admin/settings/score-display` (`admin.settings.score-display`) — Configure public scoreboard visibility.

---

## 4. House Leader Endpoints (`prefix: /leader`)
*Middleware: `['auth', 'role:group_leader']`*

- `GET /leader` (`leader.dashboard`) — House overview, registered entries, and points tally.
- `GET /leader/students` (`leader.students`) — House competitor roster.
- `GET /leader/students/create` (`leader.students.create`) — Register new student for house.
- `POST /leader/students` (`leader.students.store`) — Store student record.
- `GET /leader/registrations` (`leader.registrations`) — Program entry allocation portal.
- `POST /leader/registrations` (`leader.registrations.store`) — Enroll student into program.
- `DELETE /leader/registrations/{entry}` (`leader.registrations.destroy`) — Withdraw program entry.
- `GET /leader/programs` (`leader.programs`) — Eligible program directory.
- `GET /leader/programs-wise` (`leader.program-wise`) — House contestants per event.
- `GET /leader/student-wise` (`leader.student-wise`) — Events per house student.

---

## 5. Digital Jury Endpoints (`prefix: /judge`)
*Middleware: `['auth', 'role:judge']`*

- `GET /judge` (`judge.dashboard`) — Jury dashboard showing assigned competitions.
- `GET /judge/score-sheet/{program}` (`judge.score-sheet`) — Touch-optimized criteria score evaluation.
- `POST /judge/save-scores` (`judge.save-scores`) — Save draft or submit locked marksheet.

---

## 6. Green Room Endpoints (`prefix: /greenroom`)
*Middleware: `['auth', 'role:green_room_coordinator']`*

- `GET /greenroom` (`greenroom.index`) — Stage coordination console.
- `GET /greenroom/code-letters` (`greenroom.code-letters`) — Anonymized code letter generation.
- `POST /greenroom/mark-attendance` (`greenroom.attendance`) — Contestant check-in status toggle.
