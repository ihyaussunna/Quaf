<?php

use App\Models\Group;
use App\Models\PointsTransaction;
use App\Models\Student;
use App\Services\CoinRewardService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('coin_transactions') && Schema::hasTable('students')) {
            CoinRewardService::cleanupAllDuplicateTransactions();
        }

        // Also check if any duplicate PointsTransaction records exist for the same student, program and source_type
        if (Schema::hasTable('points_transactions')) {
            try {
                $duplicatePoints = DB::table('points_transactions')
                    ->select('student_id', 'program_id', 'source_type', DB::raw('COUNT(*) as total_count'))
                    ->whereNotNull('student_id')
                    ->whereNotNull('program_id')
                    ->whereIn('source_type', ['POSITION', 'GRADE'])
                    ->groupBy('student_id', 'program_id', 'source_type')
                    ->having('total_count', '>', 1)
                    ->get();

                $affectedStudentIds = [];
                $affectedGroupIds = [];

                foreach ($duplicatePoints as $dp) {
                    $txs = PointsTransaction::where('student_id', $dp->student_id)
                        ->where('program_id', $dp->program_id)
                        ->where('source_type', $dp->source_type)
                        ->orderByDesc('points')
                        ->orderBy('id')
                        ->get();

                    $keep = $txs->first();
                    $toDelete = $txs->slice(1);

                    foreach ($toDelete as $dt) {
                        $affectedGroupIds[] = $dt->group_id;
                        $dt->delete();
                    }

                    $affectedStudentIds[] = $dp->student_id;
                }

                if (! empty($affectedStudentIds)) {
                    foreach (array_unique($affectedStudentIds) as $sId) {
                        $stu = Student::find($sId);
                        if ($stu) {
                            $totalPts = (int) PointsTransaction::where('student_id', $sId)->sum('points');
                            $stu->update(['points_cache' => $totalPts]);
                        }
                    }
                }

                if (! empty($affectedGroupIds)) {
                    foreach (array_unique(array_filter($affectedGroupIds)) as $gId) {
                        $grp = Group::find($gId);
                        if ($grp) {
                            $grpPts = (int) PointsTransaction::where('group_id', $gId)->sum('points');
                            $grp->update(['points_cache' => $grpPts]);
                        }
                    }
                }
            } catch (Throwable) {
                // Ignore if table schema or columns differ
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
