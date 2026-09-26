# QUAF SEASON 09 — FESTIVAL ADMINISTRATOR GUIDE

This guide provides end-to-end instructions for Festival Administrators using the QUAF Season 09 management system.

---

## 1. Access & Credentials

- **URL**: `/admin`
- **Default Super Admin**: `admin` / `password`
- **Role Permissions**: Full access to all modules, settings, approvals, and audits.

---

## 2. Core Operational Modules

### A. Groups Management (`/admin/groups`)
- View and manage the 5 official competition groups: **Pacto Hikmic**, **Yugo Rushdic**, **Conco Majdic**, **Lumo Fikric**, **Unio Hilmic**.
- Assign group managers and contact details.

### B. Programmes & Zones (`/admin/programs`, `/admin/zones`)
- Create and edit individual and group programmes.
- Configure Zone allocation (`A Zone`, `B Zone`, `C Zone`, `Mix Zone`).
- Set `max_participants_per_group` and Mix Zone permissions (`mix_zone_open_to_all`).

### C. Registration Management (`/admin/registrations`)
- Verify or reject registrations submitted by Group Leaders.
- Register individual students or group entries directly with automatic eligibility enforcement.
- When an entry is verified, it is automatically scheduled for the Green Room call queue.

### D. Result Approval & Publishing Workflow (`/admin/results`)
1. Review submitted marks and rankings from the Judges' evaluation sheets.
2. Select 1st, 2nd, and 3rd place winners with designated grades.
3. Click **Publish Result**.
4. The system automatically triggers `PointCalculationService` to credit position and grade points into `points_transactions` and updates group standings on the public leaderboard.

### E. Points Engine & Audit Ledger (`/admin/points`)
- View real-time Group Standings with official brand color cards.
- Inspect the auditable **Points Transaction Ledger** filtered by Group or Source Type (`POSITION` vs `GRADE`).
- Trigger manual full recalculations using the **Recalculate Leaderboard** button.
