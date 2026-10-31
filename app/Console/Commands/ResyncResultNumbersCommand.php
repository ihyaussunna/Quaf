<?php

namespace App\Console\Commands;

use App\Models\Result;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResyncResultNumbersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'results:resync-numbers {--force : Force re-sequencing of all declared results from 1 to N}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resync and guarantee continuous sequential result numbering (1, 2, 3... N) for all declared results.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        Result::ensureSchema();

        if ($this->option('force')) {
            $declared = DB::table('results')
                ->whereIn('status', ['send', 'delivered', 'announced', 'published'])
                ->get();

            $sorted = $declared->sort(function ($a, $b) {
                $pubA = $a->published_at ? strtotime($a->published_at) : ($a->created_at ? strtotime($a->created_at) : 0);
                $pubB = $b->published_at ? strtotime($b->published_at) : ($b->created_at ? strtotime($b->created_at) : 0);

                if ($pubA !== $pubB) {
                    return $pubA <=> $pubB;
                }

                $crtA = $a->created_at ? strtotime($a->created_at) : 0;
                $crtB = $b->created_at ? strtotime($b->created_at) : 0;

                if ($crtA !== $crtB) {
                    return $crtA <=> $crtB;
                }

                return $a->id <=> $b->id;
            })->values();

            $counter = 1;
            foreach ($sorted as $row) {
                DB::table('results')->where('id', $row->id)->update([
                    'result_number' => $counter,
                ]);
                $counter++;
            }

            $this->info("Force re-sequenced {$sorted->count()} declared results sequentially from 1 to ".($counter - 1).'.');

            return Command::SUCCESS;
        }

        Result::resyncResultNumbers();

        $count = Result::whereIn('status', ['send', 'delivered', 'announced', 'published'])->count();
        $this->info("Sequential result numbering validated and ensured for {$count} declared results.");

        return Command::SUCCESS;
    }
}
