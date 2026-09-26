# QUAF SEASON 09 — HANDBOOK RULE MAPPING

This document maps the official QUAF Season 09 Festival Handbook rules directly to their implementation in the code base.

---

## 1. Terminology Standard

| Handbook Section | Handbook Term | Legacy Code Term | Standardized Code Symbol / Model |
|---|---|---|---|
| Section 1.1 | **Group** | House / Team | `App\Models\Group` (5 Official Records) |
| Section 1.2 | **Zone** | Category / House | `App\Models\Zone` (`A_ZONE`, `B_ZONE`, `C_ZONE`, `MIX_ZONE`) |
| Section 1.3 | **Programme Category** | Category | `App\Models\ProgramCategory` (Disciplines) |

---

## 2. Participant Quotas & Eligibility

| Handbook Rule | Handbook Constraint | Code Implementation | Test Verification |
|---|---|---|---|
| Rule 2.1 | Maximum 5 individual events per candidate | `Student::MAX_INDIVIDUAL_PROGRAMS = 5`, `EligibilityService::checkIndividualLimit()` | `test_01`, `test_02`, `test_03` |
| Rule 2.2 | Group programmes excluded from individual cap | `ProgramEntryParticipant` pivot, `individual_limit_counted = false` | `test_10` |
| Rule 2.3 | Mix Zone open to all by default | `mix_zone_open_to_all = true` | `test_04` |
| Rule 2.4 | Mix Zone restricted exceptions | `mix_zone_open_to_all = false`, `eligibility_rules['allowed_zones']` | `test_05` |
| Rule 2.5 | Group-wise entry limits | `Program::max_participants_per_group` | `test_06`, `test_07` |
| Rule 2.6 | Prevention of duplicate registrations | `EligibilityService::checkDuplicate()` | `test_08` |
| Rule 2.7 | Quota recovery on cancellation | `ProgramEntry::ACTIVE_STATUSES` filter | `test_09` |
| Rule 2.8 | Multi-candidate group registration | `EligibilityService::registerGroup()` | `test_10` |
| Rule 2.9 | Concurrency lock protection | `lockForUpdate()` in `EligibilityService` | `test_11` |

---

## 3. Scoring & Points Ledger

| Handbook Rule | Handbook Specification | Code Implementation | Test Verification |
|---|---|---|---|
| Rule 3.1 | 1st Place = 5 points | `PointCalculationService::POSITION_POINTS[1] = 5` | `test_12` |
| Rule 3.2 | 2nd Place = 3 points | `PointCalculationService::POSITION_POINTS[2] = 3` | `test_12` |
| Rule 3.3 | 3rd Place = 1 point | `PointCalculationService::POSITION_POINTS[3] = 1` | `test_12` |
| Rule 3.4 | Grade A+ = 6 points | `PointCalculationService::GRADE_POINTS['A+'] = 6` | `test_12` |
| Rule 3.5 | Grade A = 5 points | `PointCalculationService::GRADE_POINTS['A'] = 5` | `test_12` |
| Rule 3.6 | Grade B = 3 points | `PointCalculationService::GRADE_POINTS['B'] = 3` | `test_12` |
| Rule 3.7 | Grade C = 1 point | `PointCalculationService::GRADE_POINTS['C'] = 1` | `test_12` |
| Rule 3.8 | Single award for group events | `PointsTransaction` logged once with `student_id = null` | `PointCalculationService` |
| Rule 3.9 | Auditable Points Ledger | `PointsTransaction` model & table | `test_12` |
