<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\CoinTransaction;
use App\Models\Student;
use App\Services\CoinRewardService;
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
            ->with(['student.group', 'program.category', 'entry', 'program.result'])
            ->first();

        $isMediaPublished = $certificate && $certificate->program && $certificate->program->result
            && $certificate->program->result->status === 'published'
            && (bool) $certificate->program->result->is_media_published;

        $isValid = (bool) $certificate && $isMediaPublished;

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
            // Scanner page is public: only show media-published coins and achievements
            $coinSummary = $coinRewardService->getStudentCoinSummary($student, true);
            $studentAchievements = $coinRewardService->getStudentAchievements($student, true);
            $purchaseHistory = CoinTransaction::where('student_id', $student->id)
                ->where('transaction_type', 'debit')
                ->latest()
                ->get();
            $coinTransactions = CoinTransaction::where('student_id', $student->id)
                ->where(function ($q) {
                    $q->whereNull('result_id')
                        ->orWhereHas('result', fn ($rq) => $rq->where('status', 'published')->where('is_media_published', true));
                })
                ->where(function ($q) {
                    $q->whereNull('program_id')
                        ->orDoesntHave('program.result')
                        ->orWhereHas('program.result', fn ($rq) => $rq->where('status', 'published')->where('is_media_published', true));
                })
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
     * Only reveals results that have been officially published with a poster by the Media Desk.
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

        // 1. From CoinTransactions (credits with coins > 0 for media-published results)
        $credits = CoinTransaction::where('student_id', $student->id)
            ->where('transaction_type', 'credit')
            ->where('coins', '>', 0)
            ->where(function ($q) {
                $q->whereNull('result_id')
                    ->orWhereHas('result', fn ($rq) => $rq->where('status', 'published')->where('is_media_published', true));
            })
            ->where(function ($q) {
                $q->whereNull('program_id')
                    ->orDoesntHave('program.result')
                    ->orWhereHas('program.result', fn ($rq) => $rq->where('status', 'published')->where('is_media_published', true));
            })
            ->with(['program.category', 'program.result', 'result'])
            ->get();

        foreach ($credits as $credit) {
            $prog = $credit->program;
            $res = $credit->result ?? $prog?->result;
            if ($res && (! $res->is_media_published || $res->status !== 'published')) {
                continue;
            }

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

        // 2. From Entries & Group Participations (ONLY if officially published by Media Desk)
        $student->loadMissing([
            'entries.program.result',
            'entries.program.category',
            'participations.program.result',
            'participations.program.category',
            'certificates.program.category',
            'certificates.program.result',
        ]);

        $allEntries = $student->entries->merge($student->participations)->unique('id');

        foreach ($allEntries as $entry) {
            $prog = $entry->program;
            if (! $prog || ! $prog->result) {
                continue;
            }

            $res = $prog->result;
            // CRITICAL: MUST BE OFFICIALLY PUBLISHED BY MEDIA DESK
            if ($res->status !== 'published' || ! $res->is_media_published) {
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

        // 3. From Certificates (Only if program result is media published)
        foreach ($student->certificates as $cert) {
            if ($cert->program && $cert->program->result) {
                $res = $cert->program->result;
                if ($res->status !== 'published' || ! $res->is_media_published) {
                    continue;
                }

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
