# QUAF Fest 09 — Data Flow & State Lifecycle
**Data Transformations, Calculations, and Cache Synchronization**

---

```mermaid
flowchart TD
    subgraph "1. Registration Ingestion"
        StudentInput["Leader Enrolls Student"] --> UniqueCheck["Unique Check (program_id + chest_number)"]
        UniqueCheck --> ProgramEntry["Record in program_entries (status: 'verified')"]
    end

    subgraph "2. Evaluation Data Flow"
        ProgramEntry --> CodeGen["Anonymization: Assign code_letter ('A', 'B', 'C'...)"]
        CodeGen --> JudgeInput["Judge Enters Criteria Scores via /judge"]
        JudgeInput --> ScoreSheet["Store in score_sheets (JSON criteria + total_score)"]
    end

    subgraph "3. Result Compilation & Placement"
        ScoreSheet --> AvgCalc["Tabulate Judge Averages & Rank Contestants"]
        AvgCalc --> ResultRecord["Create Result Record (1st, 2nd, 3rd entry_id)"]
    end

    subgraph "4. Points Cascade & Denormalization"
        ResultRecord --> PublishEvent["Admin Publishes Result"]
        PublishEvent --> WeightEngine["Calculate Points: 1st=Weight, 2nd=60%, 3rd=20%"]
        WeightEngine --> StudentCache["Update students.points_cache"]
        StudentCache --> GroupCache["Recalculate groups.points_cache (Sum of members + group events)"]
        GroupCache --> RankSort["Re-rank Academic Houses (groups.rank_cache)"]
    end

    subgraph "5. Public Delivery"
        RankSort --> PublicAPI["Public Standings Queries (0ms Execution - Cached Columns)"]
        PublishEvent --> CertGen["Generate Cryptographic QR Certificates"]
    end
```

---

## Technical Details of Data Transformations

### 1. Scorecard Serialization
Judge criteria evaluations are serialized into `score_sheets.criteria_scores` as JSON:
```json
{
  "Pronunciation": 24.5,
  "Delivery & Body Language": 23.0,
  "Content & Relevance": 25.0,
  "Time Adherence": 10.0
}
```
The composite total score is saved as a precise decimal `score_sheets.total_score = 82.50`.

---

### 2. Points Engine Formula
When `AdminResultController@publish` triggers:
```php
$firstEntry = $result->firstEntry;
$secondEntry = $result->secondEntry;
$thirdEntry = $result->thirdEntry;

$weight = $program->points_weight ?: 5.0;

$p1 = $weight;
$p2 = (int) round($weight * 0.60);
$p3 = (int) round($weight * 0.20);

// Increment student individual cache
$firstEntry->student?->increment('points_cache', $p1);
$secondEntry?->student?->increment('points_cache', $p2);
$thirdEntry?->student?->increment('points_cache', $p3);

// Invalidate and recalculate group points
foreach (Group::all() as $group) {
    $total = Student::where('group_id', $group->id)->sum('points_cache');
    $group->update(['points_cache' => $total]);
}

// Re-rank academic houses
$ranked = Group::orderByDesc('points_cache')->get();
foreach ($ranked as $index => $group) {
    $group->update(['rank_cache' => $index + 1]);
}
```

This architecture ensures that high-volume public requests on `/` and `/results` never need to execute heavy aggregation queries (`SUM()` or `JOIN` operations across thousands of entries), yielding ultra-fast sub-5-millisecond response times.
