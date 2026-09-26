# QUAF SEASON 09 — PROGRAMME RULES & REGULATIONS

This document outlines the official programme structure, participation limits, categories, and validation rules for **QUAF Season 09**.

---

## 1. Programme Classification

Every competition in QUAF Season 09 belongs to one of two structural participation types:

### A. Individual Programmes (`INDIVIDUAL`)
- **Participant**: Exactly one student per entry.
- **Quota Impact**: **Counts directly** toward the student's individual limit (`MAX = 5`).
- **Chest Number**: Defaults to student ID (e.g. `QF1001`) or unique allocated number.

### B. Group Programmes (`GROUP`)
- **Participant**: Multiple students competing together under a single Group entry.
- **Quota Impact**: **Does NOT count** toward any participant's individual limit (`MAX = 5`).
- **Participant Limits**:
  - `participant_count`: Minimum required participants (e.g. 2, 4, 6).
  - `max_participants`: Maximum allowed participants for the entry.
  - `max_participants_per_group`: Maximum group entries a single Group can register (default: 1 entry per group).
- **Points Awarded**: Points are credited once to the Group's aggregate score, not multiplied per student.

---

## 2. Programme Zones & Eligibility

Every programme is assigned to an official **Zone**:

| Zone Code | Zone Name | Description / Classes | Mix Zone Status |
|-----------|-----------|------------------------|-----------------|
| `A_ZONE` | A Zone | Class 1 to 4 | Standard Zone |
| `B_ZONE` | B Zone | Class 5 to 7 | Standard Zone |
| `C_ZONE` | C Zone | Class 8 to 10 | Standard Zone |
| `MIX_ZONE`| Mix Zone | General / Combined | Open to all (with exceptions) |

### Mix Zone Configuration
- `mix_zone_open_to_all = true`: Any student from A, B, or C Zone can register.
- `mix_zone_open_to_all = false`: Restricted by `eligibility_rules['allowed_zones']`. Only students belonging to the specified zones are eligible.

---

## 3. Disciplinary Categories

Disciplines are separate from Zones and include:
- Quran & Hadith
- Islamic Studies & Sharia
- Literature & Languages (Malayalam, Arabic, English, Urdu)
- Arts, Calligraphy & Design
- Stage & Cultural (Nasheed, Speech, Debate, Tableaux)

---

## 4. Participant Limits & Caps

1. **Individual Student Cap**:
   - Maximum **5 individual programmes** per student across their own Zone and eligible Mix Zone events.
   - Any attempt to register for a 6th individual programme is rejected by the backend validation engine.
2. **Group Quota Cap (`max_participants_per_group`)**:
   - Programmes can restrict how many individual students or entries a single Group can field.
   - For example, if a programme sets `max_participants_per_group = 4`, Group Pacto can only register 4 students. A 5th registration from Pacto is blocked, while other groups can still register.
3. **Overall Programme Cap (`max_participants`)**:
   - Caps total registrations across all groups combined for limited-capacity events.

---

## 5. Lifecycle Statuses

Programmes progress through strict lifecycle states:
- `upcoming`: Open for registrations and scheduling.
- `in_progress`: Green room calls and onstage evaluations active.
- `completed`: Marks entered and results compiled.
- `published`: Official results declared and points ledger updated.
