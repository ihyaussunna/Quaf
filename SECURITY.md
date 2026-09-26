# QUAF Fest 09 — Security Architecture & Guidelines
**Authentication, Authorization, Data Integrity, and Fair Play Safeguards**

---

## 1. Authentication & Role Gateways

### Multi-Guard Session Security
- User sessions are governed by Laravel session guards configured with `database` session drivers.
- All session IDs are regenerated upon successful login to neutralize Session Fixation attacks.
- Passwords are encrypted using modern `bcrypt` / Argon2 hashing algorithms.

### Role-Based Access Control (RBAC)
- Enforced at the route-middleware layer via `App\Http\Middleware\RoleMiddleware`.
- Prevents horizontal and vertical privilege escalation:
  - A Judge cannot access Admin routes or Leader registration functions.
  - A House Leader can only inspect and enroll competitors belonging strictly to their assigned academic house (`$user->group_id`).

---

## 2. Fair Play & Anonymization Engine
To ensure absolute impartiality during jury deliberation:
1. **Name & House Masking**: When a jury member opens a scorecard via `/judge/score-sheet/{program}`, the student's name, class, and academic house are strictly withheld from the payload.
2. **Code Letters**: Contestants are identified only by pseudo-random code letters (e.g. `Contestant B`).
3. **Score Locking**: Once a judge clicks **"Submit Scorecard"**, the `score_sheets.is_submitted` flag locks the record. Judges cannot modify scores after submission without Super Admin intervention.

---

## 3. Data Integrity & Concurrency Protections

### Compound Unique Constraints
- **Double-Enrollment Prevention**: Compound unique key on `program_entries(program_id, chest_number)` prevents concurrent duplicate registrations.
- **Score Duplication Prevention**: Compound unique key on `score_sheets(judge_id, program_id, entry_id)` guarantees one score per judge per competitor.

### Atomic Transactions
Critical multi-table mutations execute within database transaction boundaries:
```php
DB::transaction(function () use ($result) {
    $result->update(['status' => 'published']);
    // Update student points
    // Recalculate house points
    // Re-rank academic houses
});
```

---

## 4. Cryptographic QR Verification
- Every certificate issued produces a unique cryptographic verification token (`qr_token`) generated via `Str::random(32)` combined with the certificate serial number.
- Anyone scanning the physical certificate's QR code connects to `/verify-certificate/{code}` which looks up the database record and validates authenticity, preventing fraudulent or altered certificates.

---

## 5. Immutable Audit Logs
All state mutations in the administrative backend are recorded into `audit_logs`:
- Admin User ID
- Timestamp & Client IP Address
- Pre-mutation state (`old_values`)
- Post-mutation state (`new_values`)
- Target model and record ID
