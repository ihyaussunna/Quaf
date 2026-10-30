<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\CoinTransaction;
use App\Models\Student;
use App\Services\CoinRewardService;
use App\Services\PointCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class VerificationController extends Controller
{
    public function index(Request $request): View
    {
        return view('public.verify');
    }

    public function verifyCertificate(string $certificateNumber): View
    {
        $certificate = Certificate::where('certificate_number', trim($certificateNumber))
            ->with(['student.group', 'program.category', 'entry'])
            ->first();

        $isValid = (bool) $certificate;

        return view('public.verify-certificate', compact('certificate', 'isValid', 'certificateNumber'));
    }

    public function verifyStudent(string $qrToken, CoinRewardService $coinRewardService): View
    {
        $clean = trim($qrToken);

        $student = Student::where('qr_token', $clean)
            ->orWhere('student_id', $clean)
            ->orWhereRaw('UPPER(student_id) = ?', [strtoupper($clean)])
            ->orWhere('student_id', 'QF'.$clean)
            ->with([
                'group',
                'zone',
                'entries.program.category',
                'entries.program.stage',
                'entries.program.schedule',
                'certificates.program',
            ])
            ->first();

        $isValid = (bool) $student;
        $coinSummary = null;
        $studentAchievements = null;
        $purchaseHistory = collect();
        $coinTransactions = collect();
        $awardedPrograms = collect();

        if ($student) {
            $coinSummary = $coinRewardService->getStudentCoinSummary($student);
            $studentAchievements = $coinRewardService->getStudentAchievements($student);
            $purchaseHistory = CoinTransaction::where('student_id', $student->id)
                ->where('transaction_type', 'debit')
                ->latest()
                ->get();
            $coinTransactions = CoinTransaction::where('student_id', $student->id)
                ->latest()
                ->take(15)
                ->get();
            $awardedPrograms = $this->resolveStudentAwardedPrograms($student);
        }

        return view('public.verify-student', compact(
            'student',
            'isValid',
            'qrToken',
            'coinSummary',
            'studentAchievements',
            'purchaseHistory',
            'coinTransactions',
            'awardedPrograms'
        ));
    }

    /**
     * Resolve all competition programs where the student achieved 1st/2nd/3rd prize or a grade.
     *
     * @return Collection<int, array{
     *     program_id: int|null,
     *     name: string,
     *     code: string|null,
     *     category: string|null,
     *     position: int|null,
     *     position_title: string|null,
     *     grade: string|null
     * }>
     */
    protected function resolveStudentAwardedPrograms(Student $student): Collection
    {
        $awarded = collect();

        // 1. From CoinTransactions (credits with coins > 0)
        $credits = CoinTransaction::where('student_id', $student->id)
            ->where('transaction_type', 'credit')
            ->where('coins', '>', 0)
            ->with(['program.category'])
            ->get();

        foreach ($credits as $credit) {
            $pos = in_array((int) $credit->prize_position, [1, 2, 3], true) ? (int) $credit->prize_position : null;
            $grade = ! empty($credit->grade) && $credit->grade !== '-' ? trim($credit->grade) : null;
            if ($pos || $grade) {
                $key = $credit->program_id ?: ('tx_'.$credit->id);
                $awarded->put($key, [
                    'program_id' => $credit->program_id,
                    'name' => $credit->program?->name ?? ($credit->metadata['program_name'] ?? $credit->description),
                    'code' => $credit->program?->code,
                    'category' => $credit->program?->category?->name,
                    'position' => $pos,
                    'position_title' => $pos ? match ($pos) {
                        1 => '1st Prize', 2 => '2nd Prize', 3 => '3rd Prize', default => null
                    } : null,
                    'grade' => $grade,
                ]);
            }
        }

        // 2. From Entries & Group Participations
        $student->loadMissing([
            'entries.program.result',
            'entries.program.category',
            'entries.scoreSheets',
            'participations.program.result',
            'participations.program.category',
            'participations.scoreSheets',
            'certificates.program.category',
        ]);

        $allEntries = $student->entries->merge($student->participations)->unique('id');

        foreach ($allEntries as $entry) {
            $prog = $entry->program;
            if (! $prog || ! $prog->result) {
                continue;
            }

            $res = $prog->result;
            if (! in_array($res->status, ['verified', 'published', 'announced'], true)) {
                continue;
            }

            $pos = null;
            $grade = null;

            if ($res->first_entry_id === $entry->id) {
                $pos = 1;
                $grade = $res->first_grade;
            } elseif ($res->second_entry_id === $entry->id) {
                $pos = 2;
                $grade = $res->second_grade;
            } elseif ($res->third_entry_id === $entry->id) {
                $pos = 3;
                $grade = $res->third_grade;
            }

            if (! $grade && ! empty($entry->computed_grade) && $entry->computed_grade !== '-') {
                $grade = $entry->computed_grade;
            }

            if (! $grade && $entry->scoreSheets->where('is_submitted', true)->isNotEmpty()) {
                $avgScore = (float) $entry->scoreSheets->where('is_submitted', true)->avg('total_score');
                if ($avgScore > 0) {
                    $gInfo = PointCalculationService::getGradeFromScore($avgScore);
                    if (! empty($gInfo['grade']) && $gInfo['grade'] !== '-') {
                        $grade = $gInfo['grade'];
                    }
                }
            }

            $grade = (! empty($grade) && $grade !== '-') ? trim($grade) : null;

            if ($pos || $grade) {
                $existing = $awarded->get($prog->id, []);
                $awarded->put($prog->id, [
                    'program_id' => $prog->id,
                    'name' => $prog->name,
                    'code' => $prog->code,
                    'category' => $prog->category?->name,
                    'position' => $pos ?? ($existing['position'] ?? null),
                    'position_title' => ($pos ?? ($existing['position'] ?? null)) ? match ($pos ?? ($existing['position'] ?? null)) {
                        1 => '1st Prize', 2 => '2nd Prize', 3 => '3rd Prize', default => null
                    } : null,
                    'grade' => $grade ?? ($existing['grade'] ?? null),
                ]);
            }
        }

        // 3. From Certificates
        foreach ($student->certificates as $cert) {
            if ($cert->program) {
                $pos = in_array((int) $cert->position, [1, 2, 3], true) ? (int) $cert->position : null;
                $existing = $awarded->get($cert->program_id, []);
                if ($pos || ! empty($existing['grade'])) {
                    $awarded->put($cert->program_id, [
                        'program_id' => $cert->program_id,
                        'name' => $cert->program->name,
                        'code' => $cert->program->code,
                        'category' => $cert->program->category?->name,
                        'position' => $pos ?? ($existing['position'] ?? null),
                        'position_title' => ($pos ?? ($existing['position'] ?? null)) ? match ($pos ?? ($existing['position'] ?? null)) {
                            1 => '1st Prize', 2 => '2nd Prize', 3 => '3rd Prize', default => null
                        } : null,
                        'grade' => $existing['grade'] ?? null,
                    ]);
                }
            }
        }

        return $awarded->values();
    }
}
