<?php

namespace App\Services;

use App\Models\Group;
use App\Models\Program;
use App\Models\ProgramEntry;
use Illuminate\Support\Collection;

class GroupEntryStatsService
{
    /**
     * Compute comprehensive registration and quota fulfillment statistics for each group.
     *
     * @return array{
     *     groups: Collection,
     *     programs: Collection,
     *     total_programs: int,
     *     global_full_count: int,
     *     global_partial_count: int,
     *     global_pending_count: int,
     *     global_total_entries: int,
     *     global_verified_count: int,
     *     global_pending_verif_count: int
     * }
     */
    public function buildGroupStats(?int $zoneId = null): array
    {
        $groups = Group::orderBy('name')->get();
        $programsQuery = Program::with('zone')->where('status', 'upcoming')->orderBy('name');
        if ($zoneId) {
            $programsQuery->where('zone_id', $zoneId);
        }
        $programs = $programsQuery->get();
        if ($programs->isEmpty() && ! $zoneId) {
            $programs = Program::with('zone')->orderBy('name')->get();
        }

        $allEntries = ProgramEntry::with('participants:id')
            ->whereIn('status', ProgramEntry::ACTIVE_STATUSES)
            ->get();

        $groupStats = $groups->map(function ($grp) use ($programs, $allEntries) {
            $grpEntries = $allEntries->where('group_id', $grp->id);
            $entriesByProgram = $grpEntries->groupBy('program_id');

            $fullPrograms = collect();
            $partialPrograms = collect();
            $pendingPrograms = collect();

            foreach ($programs as $p) {
                $limit = $p->limit;
                $pEntries = $entriesByProgram->get($p->id, collect());

                if ($p->type === 'group') {
                    $firstEntry = $pEntries->first();
                    $enrolled = $firstEntry ? max(1, $firstEntry->participants->count()) : 0;
                } else {
                    $enrolled = $pEntries->count();
                }

                $remaining = max(0, $limit - $enrolled);

                $programInfo = [
                    'id' => $p->id,
                    'name' => $p->name,
                    'code' => $p->code,
                    'malayalam_name' => $p->malayalam_name,
                    'zone' => $p->zone?->name ?? ($p->eligibility ?? 'Mix Zone'),
                    'type' => $p->type,
                    'limit' => $limit,
                    'enrolled' => $enrolled,
                    'remaining' => $remaining,
                ];

                if ($enrolled >= $limit) {
                    $fullPrograms->push($programInfo);
                } elseif ($enrolled > 0) {
                    $partialPrograms->push($programInfo);
                } else {
                    $pendingPrograms->push($programInfo);
                }
            }

            $totalEntries = $grpEntries->count();
            $verifiedEntries = $grpEntries->where('status', 'verified')->count();
            $pendingVerifEntries = $grpEntries->where('status', 'pending')->count();
            $totalProgramsCount = $programs->count();
            $progressPercent = $totalProgramsCount > 0
                ? (int) round(($fullPrograms->count() / $totalProgramsCount) * 100)
                : 0;

            return [
                'group' => $grp,
                'total_entries' => $totalEntries,
                'verified_entries' => $verifiedEntries,
                'pending_verif_entries' => $pendingVerifEntries,
                'full_count' => $fullPrograms->count(),
                'partial_count' => $partialPrograms->count(),
                'pending_count' => $pendingPrograms->count(),
                'progress_percent' => $progressPercent,
                'full_programs' => $fullPrograms,
                'partial_programs' => $partialPrograms,
                'pending_programs' => $pendingPrograms,
            ];
        });

        $globalFullCount = $groupStats->sum('full_count');
        $globalPartialCount = $groupStats->sum('partial_count');
        $globalPendingCount = $groupStats->sum('pending_count');
        $globalTotalEntries = $allEntries->count();
        $globalVerifiedCount = $allEntries->where('status', 'verified')->count();
        $globalPendingVerifCount = $allEntries->where('status', 'pending')->count();

        return [
            'groups' => $groupStats,
            'programs' => $programs,
            'total_programs' => $programs->count(),
            'global_full_count' => (int) $globalFullCount,
            'global_partial_count' => (int) $globalPartialCount,
            'global_pending_count' => (int) $globalPendingCount,
            'global_total_entries' => (int) $globalTotalEntries,
            'global_verified_count' => (int) $globalVerifiedCount,
            'global_pending_verif_count' => (int) $globalPendingVerifCount,
        ];
    }

    /**
     * Build the Program x Group status matrix.
     */
    public function buildMatrix(Collection $programs, Collection $groups, ?string $search = null, string $filterStatus = 'all', ?int $selectedGroupId = null): Collection
    {
        $allEntries = ProgramEntry::with('participants:id')
            ->whereIn('status', ProgramEntry::ACTIVE_STATUSES)
            ->get()
            ->groupBy(['program_id', 'group_id']);

        $matrix = $programs->map(function ($p) use ($groups, $allEntries) {
            $groupCells = [];
            foreach ($groups as $grp) {
                $pEntries = $allEntries->get($p->id)?->get($grp->id) ?? collect();
                $limit = $p->limit;
                if ($p->type === 'group') {
                    $first = $pEntries->first();
                    $enrolled = $first ? max(1, $first->participants->count()) : 0;
                } else {
                    $enrolled = $pEntries->count();
                }

                if ($enrolled >= $limit) {
                    $status = 'full';
                } elseif ($enrolled > 0) {
                    $status = 'partial';
                } else {
                    $status = 'pending';
                }

                $groupCells[$grp->id] = [
                    'status' => $status,
                    'enrolled' => $enrolled,
                    'limit' => $limit,
                    'remaining' => max(0, $limit - $enrolled),
                ];
            }

            return [
                'program' => $p,
                'groups' => $groupCells,
            ];
        });

        if ($search) {
            $matrix = $matrix->filter(function ($item) use ($search) {
                $p = $item['program'];

                return str_contains(strtolower($p->name), strtolower($search))
                    || str_contains(strtolower($p->code), strtolower($search))
                    || ($p->malayalam_name && str_contains(strtolower($p->malayalam_name), strtolower($search)));
            });
        }

        if ($filterStatus !== 'all') {
            $matrix = $matrix->filter(function ($item) use ($filterStatus, $selectedGroupId) {
                if ($selectedGroupId) {
                    return ($item['groups'][$selectedGroupId]['status'] ?? '') === $filterStatus;
                }

                foreach ($item['groups'] as $cell) {
                    if ($cell['status'] === $filterStatus) {
                        return true;
                    }
                }

                return false;
            });
        }

        return $matrix;
    }
}
