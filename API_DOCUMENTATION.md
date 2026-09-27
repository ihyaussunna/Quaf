# QUAF Fest 09 — Route & API Documentation
**Complete Catalogue of HTTP Endpoints, Request Parameters, and Gateways**

---

## 1. Public Routes (Unauthenticated)

| Method | Endpoint | Route Name | Description | Response |
|---|---|---|---|---|
| `GET` | `/` | `home` | Main public festival homepage with hero, standings, zones, active stages, news, and gallery | HTML (View) |
| `GET` | `/results` | `results.index` | Public festival results archive with search & zone/group/stage filters | HTML (View) |
| `GET` | `/results/{program}` | `results.show` | Complete verified score sheet, judge marks, and placements for a program | HTML (View) |
| `GET` | `/results/{result}/poster` | `media.results.public-poster` | Public downloadable branded result poster | Image / HTML |
| `GET` | `/stages/{stage}/projector` | `stages.projector` | High-contrast big screen auditorium display of live stage status & upcoming calls | HTML (View) |
| `GET` | `/news` | `news.index` | Festival editorial journal articles archive | HTML (View) |
| `GET` | `/news/{slug}` | `news.show` | Full article content view | HTML (View) |
| `GET` | `/gallery` | `gallery.index` | Photo gallery archive with category filter | HTML (View) |
| `GET` | `/videos` | `videos.index` | Video library and live stream player | HTML (View) |
| `GET` | `/verify/certificate/{certificateNumber}` | `verify.certificate` | Cryptographic QR certificate authenticity verification | HTML (View) |
| `GET` | `/verify/student/{qrToken}` | `verify.student` | Cryptographic QR student competitor verification | HTML (View) |

---

## 2. Authentication Gateways

| Method | Endpoint | Route Name | Middleware | Description |
|---|---|---|---|---|
| `GET` | `/login` | `login` | `guest` | Unified multi-role portal login screen |
| `POST` | `/login` | `login.submit` | `guest` | Authenticates credentials and redirects to authorized dashboard |
| `GET\|POST`| `/logout` | `logout` | `auth` | Invalidates active session and regenerates CSRF token |
| `GET` | `/judge/login` | `judge.login` | `guest` | 4-Digit PIN touch-login for jury members |
| `POST` | `/judge/login` | `judge.login.submit` | `guest` | Validates judge access PIN |
| `GET` | `/student/login` | `student.login` | `guest` | Student login screen (Chest Number / Student ID) |
| `POST` | `/student/login` | `student.login.submit` | `guest` | Authenticates student credentials |

---

## 3. Central Administration Endpoints (`prefix: /admin`)
*Middleware: `['auth', 'role:admin,super_admin']`*

### 3.1 Operations Dashboard & System Sync
- `GET /admin` (`admin.dashboard`) — Central operations center analytics, stat cards, and dynamic performance chart.
- `POST /admin/system/sync-festival-data` (`admin.system.sync-festival-data`) — 1-Click idempotent sync of all 144 official programs and 1,168 students from CSV.
- `GET /admin/profile` (`admin.profile.edit`) — Admin profile settings.
- `POST /admin/profile` (`admin.profile.update`) — Update profile credentials.

### 3.2 Zones Management
- `GET /admin/zones` (`admin.zones.index`) — Dedicated Zone Management Dashboard (A Zone, B Zone, C Zone, Mix Zone with live metrics and student/event matrices).

### 3.3 Academic Groups (Teams)
- `GET /admin/groups` (`admin.groups.index`) — List of 5 groups with points, managers, and rankings.
- `GET /admin/groups/{group}` (`admin.groups.show`) — Group 360 profile with enrolled students and points breakdown.
- `GET /admin/groups/create` (`admin.groups.create`) — Add new academic group form.
- `POST /admin/groups` (`admin.groups.store`) — Save group record.
- `GET /admin/groups/{group}/edit` (`admin.groups.edit`) — Edit group properties, manager, and brand color hex.
- `PUT /admin/groups/{group}` (`admin.groups.update`) — Update group record.

### 3.4 Students Master & Bulk Registration
- `GET /admin/students` (`admin.students.index`) — Complete directory of all 1,168 student competitors.
- `GET /admin/students/create` (`admin.students.create`) — Add single student form.
- `POST /admin/students` (`admin.students.store`) — Store student with automatic chest number generation.
- `GET /admin/students/bulk` (`admin.students.bulk`) — Bulk student CSV/Excel import form.
- `POST /admin/students/bulk` (`admin.students.bulk-store`) — Process bulk student upload.
- `GET /admin/students/bulk-template` (`admin.students.bulk-template`) — Download sample 4-column CSV roster template.
- `GET /admin/students/next-chest-number` (`admin.students.next-chest-number`) — API endpoint returning next sequential chest number for a group.
- `GET /admin/students/{student}` (`admin.students.show`) — 360-degree competitor profile.
- `GET /admin/students/{student}/edit` (`admin.students.edit`) — Edit student record.
- `PUT /admin/students/{student}` (`admin.students.update`) — Update student record.
- `DELETE /admin/students/{student}` (`admin.students.destroy`) — Delete student record.
- `GET /admin/students-wise` (`admin.students.student-wise`) — Enrolled events per student.
- `GET /admin/students/export` (`admin.students.export`) — Download CSV competitor directory.

### 3.5 Competitions Master (Programs)
- `GET /admin/programs` (`admin.programs.index`) — Catalog of 144 programs with zone and stage badges.
- `GET /admin/programs/create` (`admin.programs.create`) — Create program form.
- `POST /admin/programs` (`admin.programs.store`) — Save program record.
- `GET /admin/programs/{program}` (`admin.programs.show`) — Program details, participants, and schedule.
- `GET /admin/programs/{program}/edit` (`admin.programs.edit`) — Edit program configuration.
- `PUT /admin/programs/{program}` (`admin.programs.update`) — Update program record.
- `DELETE /admin/programs/{program}` (`admin.programs.destroy`) — Delete program record.
- `GET /admin/programs-wise` (`admin.programs.program-wise`) — Contestants enrolled per program.
- `POST /admin/programs/{program}/criteria` (`admin.programs.criteria.update`) — Update evaluation criteria rubric.

### 3.6 Stage Venues & Scheduling
- `GET /admin/stages` (`admin.stages.index`) — Venues list with live status and stage-only program selectors.
- `POST /admin/stages` (`admin.stages.store`) — Create new stage venue.
- `GET /admin/stages/{stage}/edit` (`admin.stages.edit`) — Edit venue details.
- `PUT /admin/stages/{stage}` (`admin.stages.update`) — Update venue details.
- `POST /admin/stages/{stage}/live-status` (`admin.stages.live-status`) — Update stage status (`active`, `break`, `closed`), current program, and next program.
- `GET /admin/schedules` (`admin.schedules.index`) — Timeline timetable directory.
- `POST /admin/schedules` (`admin.schedules.store`) — Save schedule slot.

### 3.7 Code Letters & Stage Operations Forms
- `GET /admin/code-letters` (`admin.code-letters.index`) — Code letter matrix.
- `POST /admin/code-letters/{program}/save` (`admin.code-letters.save`) — Save manual letter assignments.
- `POST /admin/code-letters/{program}/auto-assign` (`admin.code-letters.auto-assign`) — Generate random code letters (`A`, `B`, `C`...).
- `GET /admin/forms/call-list` (`admin.forms.call-list`) — Printable stage call list.
- `GET /admin/forms/evaluation` (`admin.forms.evaluation`) — Printable judge scoring sheet.
- `GET /admin/idcards/chest-slips` (`admin.idcards.chest-slips`) — Printable chest badges.

### 3.8 Marks & Results Management
- `GET /admin/mark-entry/view-marks` (`admin.mark-entry.view-marks`) — Tabulated judge marksheets.
- `GET /admin/mark-entry/handler` (`admin.mark-entry.handler`) — Administrative direct mark entry.
- `POST /admin/mark-entry/save` (`admin.mark-entry.save`) — Store administrative marks.
- `GET /admin/mark-entry/check` (`admin.mark-entry.check`) — Multi-judge variance checker.
- `GET /admin/results/declare` (`admin.results.declare`) — Podium declaration staging screen.
- `POST /admin/results` (`admin.results.store`) — Save declared result with dense ranking.
- `GET /admin/results/declared` (`admin.results.declared`) — List of declared results awaiting final sign-off.
- `POST /admin/results/{result}/publish` (`admin.results.publish`) — Final publication (updates points ledger & caches).
- `POST /admin/results/{result}/send-to-announcer` (`admin.results.send-to-announcer`) — Dispatch declared result to Announcer Desk.
- `DELETE /admin/results/{result}/undeclare` (`admin.results.undeclare`) — Rollback result declaration and reverse points transactions.
- `GET /admin/results/all` (`admin.results.all`) — Complete official results gazette.

### 3.9 Achievements & Standings
- `GET /admin/achievements/team-score` (`admin.achievements.team-score`) — Group standings scoreboard.
- `GET /admin/achievements/zone-score` (`admin.achievements.zone-score`) — Student standings filterable by Zone.
- `GET /admin/achievements/stage-score` (`admin.achievements.stage-score`) — Stage vs Off-stage leaderboards.
- `GET /admin/achievements/all-students` (`admin.achievements.all-students`) — Global student points rankings.
- `GET /admin/top-scorers` (`admin.top-scorers.index`) — Kalaprathibha, Kalathilakam, and Zone Toppers.

### 3.10 Settings Drawer
- `POST /admin/settings/mark-settings` (`admin.settings.mark-settings`) — Update program point weights.
- `POST /admin/settings/limit-settings` (`admin.settings.limit-settings`) — Update group event limits.
- `POST /admin/settings/broadcast` (`admin.settings.broadcast`) — Send emergency broadcast alert.
- `POST /admin/settings/deadline` (`admin.settings.deadline`) — Set registration cutoff countdown.

---

## 4. Program Committee Endpoints (`prefix: /program-committee`)
*Middleware: `['auth', 'role:program_committee,admin,super_admin']`*

- `GET /program-committee` (`program-committee.dashboard`) — Program Samithi dashboard with stats and quick links.
- `GET /program-committee/programs` (`program-committee.programs.index`) — Program guidelines directory.
- `GET /program-committee/programs/{program}` (`program-committee.programs.show`) — Program details and criteria view.
- `GET /program-committee/programs/{program}/rules` (`program-committee.programs.rules`) — Program rules editor (with Malayalam typography).
- `PUT /program-committee/programs/{program}/rules` (`program-committee.programs.rules.update`) — Update program rules and instructions.
- `GET /program-committee/programs/{program}/rules/print` (`program-committee.programs.rules.print`) — Printable single program rule sheet.
- `GET /program-committee/niyamavali` (`program-committee.niyamavali.index`) — Niyamavali rulebook management overview.
- `GET /program-committee/niyamavali/print-book` (`program-committee.niyamavali.print-book`) — Complete printable Niyamavali Rulebook with cover and table of contents.

---

## 5. Group Leader Endpoints (`prefix: /leader`)
*Middleware: `['auth', 'role:group_leader']`*

- `GET /leader` (`leader.dashboard`) — Group Leader dashboard with quota status and quick links.
- `GET /leader/registrations` (`leader.registrations`) — Registration management screen with slot capacity indicators.
- `POST /leader/registrations` (`leader.registrations.store`) — Enroll student in individual program (auto-verified).
- `POST /leader/registrations/group` (`leader.registrations.group.store`) — Enroll group entry with multiple students.
- `GET /leader/registrations/{entry}/edit` (`leader.registrations.edit`) — Inline edit/replace enrolled student.
- `PUT /leader/registrations/{entry}` (`leader.registrations.update`) — Save updated enrolled student.
- `DELETE /leader/registrations/{entry}` (`leader.registrations.destroy`) — Remove registered competitor entry.
- `DELETE /leader/registrations/by-program/{program}` (`leader.registrations.destroy-by-program`) — Cancel entire group registration for a program.
- `GET /leader/programs` (`leader.programs`) — Programs catalog with group slot status.
- `GET /leader/programs-wise` (`leader.programs-wise`) — Competitors enrolled per program for this group.
- `GET /leader/students` (`leader.students`) — Group roster with individual quota meters.
- `GET /leader/students-wise` (`leader.students-wise`) — Enrolled events per student for this group.

---

## 6. Jury Member Endpoints (`prefix: /judge`)
*Middleware: `['auth', 'role:judge']`*

- `GET /judge` (`judge.dashboard`) — Judge dashboard listing assigned programs.
- `GET /judge/evaluate/{program}` (`judge.evaluate`) — Digital scorecard evaluation view (anonymized code letters only).
- `POST /judge/evaluate/{program}/{entry}` (`judge.evaluate.save`) — Save criteria marks and grade (A+, A, B+, B, C).

---

## 7. Green Room Endpoints (`prefix: /greenroom`)
*Middleware: `['auth', 'role:green_room_coordinator,admin,super_admin']`*

- `GET /greenroom` (`greenroom.index`) — Green room check-in slate and stage sequencing.
- `POST /greenroom/call/{entry}` (`greenroom.call`) — Dispatch call for contestant.
- `POST /greenroom/status/{call}` (`greenroom.update-status`) — Update call status (`called`, `ready`, `on_stage`, `completed`, `absent`).
- `POST /greenroom/call-next/{program}` (`greenroom.call-next`) — Advance next contestant to on-stage.
- `GET /greenroom/code-letters` (`greenroom.code-letters`) — Code letter review matrix.
- `POST /greenroom/generate-codes/{program}` (`greenroom.generate-codes`) — Generate secret code letters.

---

## 8. Announcer Desk Endpoints (`prefix: /announcer`)
*Middleware: `['auth', 'role:announcer,admin,super_admin']`*

- `GET /announcer` (`announcer.index`) — Live announcer console with incoming declared results queue.
- `POST /announcer/announce/{result}` (`announcer.announce`) — Mark result as announced over auditorium PA.
- `POST /announcer/bulk-announce` (`announcer.bulk-announce`) — Bulk mark results as announced.
- `GET /announcer/call-sheet` (`announcer.call-sheet`) — Stage call sheets for microphone callouts.
- `POST /announcer/announcement` (`announcer.create-announcement`) — Broadcast quick announcement.

---

## 9. Media & Press Desk Endpoints (`prefix: /media`)
*Middleware: `['auth', 'role:media,admin,super_admin']`*

- `GET /media` (`media.dashboard`) — Media desk hub with stats and quick links.
- `GET /media/news` (`media.news.index`) — News articles manager.
- `POST /media/news` (`media.news.store`) — Publish new festival article.
- `POST /media/news/{news}/toggle-featured` (`media.news.toggle-featured`) — Toggle featured banner.
- `GET /media/gallery` (`media.gallery.index`) — Photo gallery manager.
- `GET /media/videos` (`media.videos.index`) — Video streams manager.
- `GET /media/results` (`media.results.index`) — Results poster generation directory.
- `GET /media/results/{result}/studio` (`media.results.studio`) — Canvas Result Poster Graphics Studio.
- `POST /media/results/{result}/save-poster` (`media.results.save-poster`) — Save generated poster PNG image.
- `POST /media/results/{result}/publish` (`media.results.publish`) — Publish poster to public result view.
- `GET /media/results/templates` (`media.results.templates`) — Poster layout templates manager.

---

## 10. Student Competitor Endpoints (`prefix: /student`)
*Middleware: `['auth', 'role:student']`*

- `GET /student` (`student.dashboard`) — Personal competitor dashboard, quota meter, and event timetable.
- `GET /student/id-card` (`student.idcard`) — Digital ID Card with QR token.
- `GET /student/certificates` (`student.certificates`) — Downloadable merit and participation certificates.

---

## 11. Remote Deployment & Sync Helpers

| Method | Endpoint | Description | Security |
|---|---|---|---|
| `GET` | `/git-pull/{token}` | Executes `git pull origin main` and clears application caches | Secret URL Token |
| `GET` | `/init-database/{token}` | Executes fresh migration and re-seeding remotely | Secret URL Token |
