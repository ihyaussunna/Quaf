# QUAF SEASON 09 — POINTS SYSTEM & AUDIT RULES

This document specifies the scoring rules, placement weights, grade criteria, and auditable transaction ledger for **QUAF Season 09**.

---

## 1. Handbook Official Scoring

Points are calculated and awarded automatically whenever an official programme result is **Published** by the Festival Admin.

### A. Position Points
Awarded to the top 3 placements in every competition:

| Placement | Official Points |
|:---:|:---:|
| **1st Place** | **5 points** |
| **2nd Place** | **3 points** |
| **3rd Place** | **1 point** |

### B. Grade Points
Awarded to entries based on final evaluated scores (0–100 marks):

| Grade | Mark Range | Points Awarded |
|:---:|:---:|:---:|
| **A+** | 90% – 100% | **6 points** |
| **A** | 70% – 89% | **5 points** |
| **B** | 60% – 69% | **3 points** |
| **C** | 50% – 59% | **1 point** |
| **No Grade** | Below 50% | 0 points |

*Note: Non-placed entries who attain a qualifying grade also receive grade points towards their Group score.*

---

## 2. Group Programmes Scoring Rule

For Group programmes (e.g. Choir, Group Song, Tableaux, Skit):
- Points are awarded **once per Group entry**.
- Points are **NOT multiplied** by the number of students on stage.
- Example: If Group Pacto wins 1st Place (5 pts) with Grade A (5 pts) in a 4-member group song, Group Pacto is credited exactly **10 points** in total.

---

## 3. Auditable Points Ledger (`points_transactions`)

Every point awarded in QUAF Season 09 is recorded in the `points_transactions` database table:

```sql
CREATE TABLE points_transactions (
    id INTEGER PRIMARY KEY,
    group_id INTEGER NOT NULL,
    program_id INTEGER NOT NULL,
    result_id INTEGER NOT NULL,
    entry_id INTEGER NOT NULL,
    student_id INTEGER NULL,
    source_type VARCHAR(20) NOT NULL, -- 'POSITION', 'GRADE', 'PARTICIPATION'
    points INTEGER NOT NULL,
    description TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Key Ledger Features:
1. **Full Traceability**: Every score point points to the exact Group, Programme, Entry, Result, and Student.
2. **Idempotency**: Running `PointCalculationService::recalculateAllPoints()` completely wipes and regenerates the ledger from published results, preventing ghost points or race-condition duplications.
3. **Admin Transparency**: Admins can inspect the complete ledger under `/admin/points` with filter dropdowns for Group and Source Type (`POSITION` vs `GRADE`).
