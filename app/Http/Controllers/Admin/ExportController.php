<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Judge;
use App\Models\Program;
use App\Models\Result;
use App\Models\Student;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function index(): View
    {
        $counts = [
            'participants' => Student::count(),
            'judges' => Judge::count(),
            'competitions' => Program::count(),
            'results' => Result::where('status', 'published')->count(),
            'teams' => Group::count(),
        ];

        $recentExports = [
            [
                'id' => 1,
                'type' => 'Participants Roster',
                'summary' => 'Full participant database with chest numbers, categories, and teams',
                'status' => 'Completed',
                'duration' => '1.2s',
                'download_url' => route('admin.exports.download', 'participants'),
                'created_at' => now()->subMinutes(12)->diffForHumans(),
            ],
            [
                'id' => 2,
                'type' => 'Competitions & Stages',
                'summary' => 'Stage schedule, duration, judge assignments, and rules',
                'status' => 'Completed',
                'duration' => '0.8s',
                'download_url' => route('admin.exports.download', 'competitions'),
                'created_at' => now()->subHours(2)->diffForHumans(),
            ],
            [
                'id' => 3,
                'type' => 'Official Results CSV',
                'summary' => '1st, 2nd, and 3rd rank verdicts and awarded points',
                'status' => 'Completed',
                'duration' => '1.5s',
                'download_url' => route('admin.exports.download', 'results'),
                'created_at' => now()->subHours(4)->diffForHumans(),
            ],
            [
                'id' => 4,
                'type' => 'Judges & Jury Roster',
                'summary' => 'Jury designations, contact numbers, and assigned categories',
                'status' => 'Completed',
                'duration' => '0.5s',
                'download_url' => route('admin.exports.download', 'judges'),
                'created_at' => now()->subDays(1)->diffForHumans(),
            ],
        ];

        return view('admin.exports.index', compact('counts', 'recentExports'));
    }

    public function export(string $type): StreamedResponse
    {
        $filename = "festfloww-{$type}-".now()->format('Y-m-d_H-i').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($type) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // UTF-8 BOM

            if ($type === 'participants') {
                fputcsv($handle, ['Student ID', 'Name', 'Team / House', 'Zone', 'Class Level', 'Gender', 'Contact', 'Total Points']);

                Student::with('group')->chunk(200, function ($students) use ($handle) {
                    foreach ($students as $s) {
                        fputcsv($handle, [
                            $s->student_id,
                            $s->name,
                            $s->group?->name ?? 'N/A',
                            $s->category,
                            $s->class_level ?? 'N/A',
                            $s->gender,
                            $s->contact ?? 'N/A',
                            $s->points_cache,
                        ]);
                    }
                });
            } elseif ($type === 'judges') {
                fputcsv($handle, ['ID', 'Name', 'Designation', 'Specialization', 'Contact Number', 'Assigned Competitions Count']);

                Judge::withCount('programs')->chunk(200, function ($judges) use ($handle) {
                    foreach ($judges as $j) {
                        fputcsv($handle, [
                            $j->id,
                            $j->name,
                            $j->designation ?? 'Adjudicator',
                            $j->specialization ?? 'General',
                            $j->contact ?? 'N/A',
                            $j->programs_count,
                        ]);
                    }
                });
            } elseif ($type === 'competitions') {
                fputcsv($handle, ['Code', 'Competition Name', 'Malayalam Name', 'Type', 'Stage / Non-Stage', 'Zone', 'Participant Limit', 'Stage Venue', 'Duration (Mins)', 'Status']);

                Program::with(['category', 'stage'])->chunk(200, function ($programs) use ($handle) {
                    foreach ($programs as $p) {
                        fputcsv($handle, [
                            $p->code,
                            $p->name,
                            $p->malayalam_name ?? '',
                            ucfirst($p->type),
                            $p->is_stage ? 'Stage' : 'Non Stage',
                            $p->eligibility ?? 'A Zone',
                            $p->participant_count ?? 2,
                            $p->stage?->name ?? 'TBA',
                            $p->duration_minutes,
                            ucfirst(str_replace('_', ' ', $p->status)),
                        ]);
                    }
                });
            } elseif ($type === 'results') {
                fputcsv($handle, ['Competition Code', 'Competition Name', 'Zone', '1st Place', '1st Place Team', '2nd Place', '2nd Place Team', '3rd Place', '3rd Place Team', 'Published At']);

                Result::with(['program.category', 'firstEntry.student', 'firstEntry.group', 'secondEntry.student', 'secondEntry.group', 'thirdEntry.student', 'thirdEntry.group'])
                    ->where('is_published', true)
                    ->chunk(200, function ($results) use ($handle) {
                        foreach ($results as $r) {
                            fputcsv($handle, [
                                $r->program->code,
                                $r->program->name,
                                $r->program->eligibility ?? 'A Zone',
                                $r->firstEntry?->student?->name ?? 'Team '.$r->firstEntry?->group?->name,
                                $r->firstEntry?->group?->name ?? 'N/A',
                                $r->secondEntry?->student?->name ?? ($r->secondEntry ? 'Team '.$r->secondEntry?->group?->name : 'N/A'),
                                $r->secondEntry?->group?->name ?? 'N/A',
                                $r->thirdEntry?->student?->name ?? ($r->thirdEntry ? 'Team '.$r->thirdEntry?->group?->name : 'N/A'),
                                $r->thirdEntry?->group?->name ?? 'N/A',
                                $r->published_at?->format('Y-m-d H:i') ?? 'N/A',
                            ]);
                        }
                    });
            } elseif ($type === 'teams') {
                fputcsv($handle, ['Rank', 'Team Name', 'Team Code', 'Manager Name', 'Manager Contact', 'Total Points']);

                $groups = Group::orderByDesc('points_cache')->get();
                foreach ($groups as $idx => $g) {
                    fputcsv($handle, [
                        $idx + 1,
                        $g->name,
                        $g->code,
                        $g->manager_name ?? 'N/A',
                        $g->manager_contact ?? 'N/A',
                        $g->points_cache,
                    ]);
                }
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
