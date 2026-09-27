@extends('layouts.program-committee', ['title' => $program->name . ' - Program Details'])

@section('content')
<div class="space-y-6">

    <!-- Breadcrumb & Actions Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <a href="{{ route('program-committee.programs.index') }}" 
           class="inline-flex items-center gap-1.5 text-xs font-mono font-bold text-slate-500 hover:text-slate-900 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Back to All Programs</span>
        </a>

        <div class="flex items-center gap-2">
            <a href="{{ route('program-committee.programs.rules', $program) }}" 
               class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 rounded-xl text-xs font-mono font-bold flex items-center gap-1.5 shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Edit Rules</span>
            </a>
            <a href="{{ route('program-committee.programs.rules.print', $program) }}" target="_blank"
               class="px-3.5 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-mono font-semibold flex items-center gap-1.5 transition">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print Rules Sheet</span>
            </a>
            <a href="{{ route('program-committee.programs.edit', $program) }}" 
               class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-mono font-bold transition">
                Edit Program
            </a>
        </div>
    </div>

    <!-- Main Header Card -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <span class="px-2.5 py-0.5 rounded-lg bg-slate-900 text-white font-mono font-bold text-xs">
                        {{ $program->code }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded-lg bg-amber-100 text-amber-900 font-mono font-bold text-xs">
                        {{ $program->zone?->name ?? $program->eligibility }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded-lg text-xs font-mono font-bold uppercase {{ $program->type === 'group' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                        {{ $program->type }}
                    </span>
                    @if($program->is_stage)
                        <span class="px-2.5 py-0.5 rounded-lg bg-rose-100 text-rose-800 font-mono text-xs font-bold">
                            Stage Competition
                        </span>
                    @endif
                </div>

                <h1 class="text-3xl font-sora font-black text-slate-900">{{ $program->name }}</h1>
                @if($program->malayalam_name)
                    <h2 class="text-xl font-malayalam font-bold text-slate-600 mt-0.5">{{ $program->malayalam_name }}</h2>
                @endif
            </div>

            <!-- Program Key Numbers -->
            <div class="flex items-center gap-4 bg-slate-50 p-4 rounded-2xl border border-slate-200 text-center font-mono">
                <div>
                    <span class="text-[10px] text-slate-400 uppercase block">Duration</span>
                    <span class="text-lg font-bold text-slate-900">{{ $program->duration_minutes }} Min</span>
                </div>
                <div class="h-8 w-px bg-slate-200"></div>
                <div>
                    <span class="text-[10px] text-slate-400 uppercase block">Points</span>
                    <span class="text-lg font-bold text-brand-burgundy">{{ $program->points_weight }} Pts</span>
                </div>
                <div class="h-8 w-px bg-slate-200"></div>
                <div>
                    <span class="text-[10px] text-slate-400 uppercase block">Enrolled</span>
                    <span class="text-lg font-bold text-emerald-700">{{ $program->entries->count() }} Entries</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Niyamavali Section (Rules & Guidelines) -->
    <div class="bg-white rounded-3xl border-2 {{ !empty($program->rules) ? 'border-emerald-300' : 'border-amber-300' }} p-6 sm:p-8 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-4">
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-lg font-sora font-bold text-slate-900">Official Rules (Niyamavali)</h3>
                    @if(!empty($program->rules))
                        <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-mono font-bold">
                            Active ✓
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[10px] font-mono font-bold">
                            Pending !
                        </span>
                    @endif
                </div>
                <p class="text-[11px] font-mono text-slate-500 mt-0.5">
                    Official guidelines for participants and judges.
                </p>
            </div>

            <a href="{{ route('program-committee.programs.rules', $program) }}" 
               class="px-3.5 py-1.5 rounded-xl bg-amber-400 hover:bg-amber-500 text-slate-950 text-xs font-mono font-bold inline-flex items-center gap-1.5 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                <span>{{ !empty($program->rules) ? 'Edit Rules' : '+ Add Rules' }}</span>
            </a>
        </div>

        @if(!empty($program->rules))
            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200 text-sm leading-relaxed text-slate-800 whitespace-pre-line {{ preg_match('/[\x{0D00}-\x{0D7F}]/u', $program->rules) ? 'font-anek' : 'font-sora' }}">
{{ $program->rules }}
            </div>
        @else
            <div class="p-8 rounded-2xl bg-amber-50/60 border border-amber-200 text-center space-y-3">
                <p class="text-xs font-sora text-amber-800 font-semibold">
                    No rules provided for this competition yet.
                </p>
                <a href="{{ route('program-committee.programs.rules', $program) }}" 
                   class="inline-block px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-sora font-bold shadow-sm transition">
                    + Click here to add Rules
                </a>
            </div>
        @endif
    </div>

    <!-- Scoring Criteria Table -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div>
                <h3 class="text-lg font-sora font-bold text-slate-900">Scoring & Evaluation Criteria</h3>
                <p class="text-[11px] font-sora text-slate-500 mt-0.5">Evaluation criteria and maximum marks for judges.</p>
            </div>
            <span class="text-xs font-rockwell font-bold px-3 py-1 rounded-xl bg-slate-100 text-slate-800">
                Total: {{ $program->scoringCriteria->sum('max_marks') }} Marks
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-100 text-slate-400 uppercase font-sora">
                        <th class="py-2.5 px-3 w-16">Sl No</th>
                        <th class="py-2.5 px-3">Criterion</th>
                        <th class="py-2.5 px-3 text-right">Max Marks</th>
                        <th class="py-2.5 px-3 text-right">Weightage</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @php $totalM = max(1, $program->scoringCriteria->sum('max_marks')); @endphp
                    @forelse($program->scoringCriteria as $idx => $crit)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-3 font-bold text-slate-400 font-rockwell">{{ $idx + 1 }}</td>
                            <td class="py-3 px-3 font-bold text-slate-900 text-sm {{ preg_match('/[\x{0D00}-\x{0D7F}]/u', $crit->criterion_name) ? 'font-anek' : 'font-sora' }}">
                                {{ $crit->criterion_name }}
                            </td>
                            <td class="py-3 px-3 text-right font-bold text-brand-burgundy text-sm font-rockwell">
                                {{ $crit->max_marks }}
                            </td>
                            <td class="py-3 px-3 text-right text-slate-500 font-rockwell">
                                {{ round(($crit->max_marks / $totalM) * 100) }}%
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-slate-400">
                                No specific scoring criteria defined yet. Default evaluation will be used.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
