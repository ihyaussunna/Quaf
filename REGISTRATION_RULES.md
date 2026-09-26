# QUAF SEASON 09 — REGISTRATION & ELIGIBILITY ENGINE RULES

This document defines the 10-check validation engine and registration constraints for **QUAF Season 09**.

---

## 1. The 10-Check Eligibility Engine

Every registration submitted via the Admin Panel or Group Leader Panel passes through `App\Services\EligibilityService`:

1. **Student Existence & Active Status Check**: Verifies the student exists and is active.
2. **Group Assignment Check**: Verifies the student is assigned to one of the 5 official Groups (`PACTO`, `YUGO`, `CONCO`, `LUMO`, `UNIO`).
3. **Programme Existence Check**: Verifies the programme exists and is in an open state.
4. **Zone Eligibility Check**:
   - For Standard Zones (A, B, C): Student's zone must strictly match the programme's zone.
   - For Mix Zone: Open to all, unless `mix_zone_open_to_all = false` with explicit `allowed_zones` restrictions.
5. **Individual Programme Limit (MAX 5)**:
   - A student can participate in a maximum of **5 individual programmes** (own zone + Mix zone combined).
   - Registrations with `status = 'cancelled'` or `'rejected'` release the quota slot.
   - Group programmes do not consume this individual quota.
6. **Programme-Specific Overall Participant Cap (`max_participants`)**:
   - Prevents registrations beyond the festival venue/stage capacity.
7. **Group-Wise Participant Limit (`max_participants_per_group`)**:
   - Caps the number of participants from a single Group in a given programme.
8. **Duplicate Registration Check**:
   - Prevents registering the same student more than once in the same competition.
9. **Required Participant Count for Group Programmes**:
   - Enforces minimum and maximum participant counts for group entries.
10. **Gender & Class-Level Restrictions**:
    - Enforces gender or class restrictions where specified in the programme requirements.

---

## 2. Transaction Safety & Concurrency

To prevent limit bypassing under concurrent submissions:
- `lockForUpdate()` is applied on the Student record inside a database transaction during quota calculation.
- Concurrency locks guarantee that rapid, simultaneous requests cannot exceed the 5-programme ceiling.

---

## 3. Student Dashboard Quota Meter

Students viewing their dashboard (`/student`) see an live quota meter:
- **Visual Display**: `X / 5 Used (Remaining: Y)`
- **Detailed List**: Dynamic table showing all active registrations and their assigned zones.
