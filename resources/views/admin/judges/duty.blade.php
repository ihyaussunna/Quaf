@extends('layouts.admin', ['title' => 'Judges Duty Schedule'])

@section('content')
<div class="space-y-6 max-w-7xl mx-auto font-sora">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-2xs">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-slate-900 text-white font-mono">
                    Evaluation Logistics
                </span>
                <span class="text-xs text-slate-500 font-mono">Judge Assignment Matrix</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                Judges Program Schedule & Duty Roster
            </h1>
            <p class="text-xs text-slate-600 mt-1 max-w-2xl font-mono">
                ജഡ്ജുമാർക്ക് നൽകിയിട്ടുള്ള മത്സരങ്ങളുടെ ഷെഡ്യൂൾ, സ്റ്റേജ്, സമയം എന്നിവ പരിശോധിക്കുകയും ഓരോ ജഡ്ജിനും പ്രത്യേകം പേജ് ആയോ ഒന്നിച്ചോ പ്രിന്റ് ചെയ്ത് PDF ആയി ഡൗൺലോഡ് ചെയ്യുകയും ചെയ്യാം.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
            <a href="{{ route('admin.judges.duty.print', ['mode' => 'per_page', 'judge_id' => $selectedJudgeId]) }}" target="_blank"
               class="px-4 py-2.5 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white font-mono font-bold text-xs uppercase tracking-wider transition-colors shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print All (1 Judge / Page)</span>
            </a>

            <a href="{{ route('admin.judges.duty.print', ['mode' => 'continuous', 'judge_id' => $selectedJudgeId]) }}" target="_blank"
               class="px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-mono font-bold text-xs uppercase tracking-wider transition flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Continuous List</span>
            </a>

            <a href="{{ route('admin.judges.index') }}"
               class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-mono font-bold text-xs uppercase tracking-wider transition">
                <span>&larr; Judges Roster</span>
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs">
            <div class="text-[11px] font-mono uppercase text-slate-500 font-bold">Total Judges</div>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ $stats['total_judges'] }}</div>
            <div class="text-[10px] font-mono text-slate-400 mt-0.5">Registered official evaluators</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-emerald-200 bg-emerald-50/20 shadow-2xs">
            <div class="text-[11px] font-mono uppercase text-emerald-700 font-bold">Assigned Judges</div>
            <div class="text-2xl font-black text-emerald-800 mt-1">{{ $stats['assigned_judges'] }}</div>
            <div class="text-[10px] font-mono text-emerald-600 mt-0.5">Have at least 1 program</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border {{ $stats['unassigned_judges'] > 0 ? 'border-amber-200 bg-amber-50/20' : 'border-slate-200' }} shadow-2xs">
            <div class="text-[11px] font-mono uppercase {{ $stats['unassigned_judges'] > 0 ? 'text-amber-800' : 'text-slate-500' }} font-bold">Unassigned Judges</div>
            <div class="text-2xl font-black {{ $stats['unassigned_judges'] > 0 ? 'text-amber-900' : 'text-slate-900' }} mt-1">{{ $stats['unassigned_judges'] }}</div>
            <div class="text-[10px] font-mono text-slate-400 mt-0.5">No competitions allocated yet</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs">
            <div class="text-[11px] font-mono uppercase text-slate-500 font-bold">Total Duty Allocations</div>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ $stats['total_duties'] }}</div>
            <div class="text-[10px] font-mono text-slate-400 mt-0.5">Total assigned slots</div>
        </div>
    </div>

    <!-- Filter Control Bar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs">
        <form method="GET" action="{{ route('admin.judges.duty') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <!-- Filter by Judge -->
            <div>
                <label class="block text-[10px] font-mono uppercase text-slate-500 font-bold mb-1">Select Judge</label>
                <select name="judge_id" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d] font-mono">
                    <option value="">All Judges ({{ $allJudges->count() }})</option>
                    @foreach($allJudges as $j)
                        <option value="{{ $j->id }}" {{ (string)$selectedJudgeId === (string)$j->id ? 'selected' : '' }}>
                            {{ $j->name }} ({{ $j->programs_count ?? $j->programs()->count() }} duties)
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter by Stage -->
            <div>
                <label class="block text-[10px] font-mono uppercase text-slate-500 font-bold mb-1">Filter by Stage</label>
                <select name="stage_id" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d] font-mono">
                    <option value="">All Stages</option>
                    @foreach($stages as $stg)
                        <option value="{{ $stg->id }}" {{ (string)$stageId === (string)$stg->id ? 'selected' : '' }}>
                            {{ $stg->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter by Category -->
            <div>
                <label class="block text-[10px] font-mono uppercase text-slate-500 font-bold mb-1">Category</label>
                <select name="category_id" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d] font-mono">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ (string)$categoryId === (string)$cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter by Status -->
            <div>
                <label class="block text-[10px] font-mono uppercase text-slate-500 font-bold mb-1">Allocation Status</label>
                <select name="status" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d] font-mono">
                    <option value="" {{ empty($filterStatus) ? 'selected' : '' }}>All Status</option>
                    <option value="assigned" {{ $filterStatus === 'assigned' ? 'selected' : '' }}>Assigned Duties Only</option>
                    <option value="unassigned" {{ $filterStatus === 'unassigned' ? 'selected' : '' }}>Unassigned Only</option>
                </select>
            </div>

            <!-- Search input & Reset -->
            <div>
                <label class="block text-[10px] font-mono uppercase text-slate-500 font-bold mb-1">Search Keywords</label>
                <div class="flex items-center gap-1.5">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Judge, program, code..."
                           class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d] font-mono">
                    <button type="submit" class="px-3 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-mono font-bold shrink-0">
                        Go
                    </button>
                    @if($selectedJudgeId || $stageId || $categoryId || $filterStatus || $search)
                        <a href="{{ route('admin.judges.duty') }}" class="px-2.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-mono shrink-0">
                            Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Judge Cards List -->
    @if($judges->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center shadow-xs">
            <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <h3 class="text-base font-bold text-slate-800">No Judges Found</h3>
            <p class="text-xs text-slate-500 font-mono mt-1">No judge matched your current filter criteria.</p>
            <a href="{{ route('admin.judges.duty') }}" class="inline-block mt-4 px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-mono font-bold">
                Clear Filters
            </a>
        </div>
    @else
        <div class="space-y-6">
            @foreach($judges as $judge)
                @php
                    $dutyPrograms = $judge->sorted_programs ?? $judge->programs;
                @endphp
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <!-- Judge Profile Banner -->
                    <div class="bg-slate-50 p-5 border-b border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex items-start sm:items-center gap-3.5">
                            <div class="w-12 h-12 rounded-xl bg-slate-900 text-white font-black text-lg flex items-center justify-center shrink-0 shadow-xs">
                                {{ strtoupper(substr($judge->name, 0, 2)) }}
                            </div>
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-lg font-bold text-slate-900">{{ $judge->name }}</h3>
                                    <span class="px-2 py-0.5 rounded font-mono text-[10px] font-bold bg-slate-200 text-slate-800">
                                        PIN: {{ $judge->access_code ?: '—' }}
                                    </span>
                                    <span class="px-2.5 py-0.5 rounded-full font-mono text-[10px] font-bold {{ $dutyPrograms->count() > 0 ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-amber-100 text-amber-800 border border-amber-300' }}">
                                        {{ $dutyPrograms->count() }} Competitions Assigned
                                    </span>
                                </div>
                                <div class="text-xs text-slate-500 font-mono mt-0.5 flex flex-wrap items-center gap-x-3 gap-y-1">
                                    <span>Designation: <strong class="text-slate-700">{{ $judge->designation ?: 'Official Judge' }}</strong></span>
                                    @if($judge->contact)
                                        <span>Mobile: <strong class="text-slate-700">{{ $judge->contact }}</strong></span>
                                    @endif
                                    @if($judge->specialization)
                                        <span>Specialization: <strong class="text-slate-700">{{ $judge->specialization }}</strong></span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 shrink-0">
                            <a href="{{ route('admin.judges.duty.print', ['judge_id' => $judge->id]) }}" target="_blank"
                               class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-mono font-bold text-xs flex items-center gap-1.5 transition shadow-xs">
                                <svg class="w-3.5 h-3.5 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                <span>Print Duty Slip (PDF)</span>
                            </a>
                            <a href="{{ route('admin.judges.edit', $judge->id) }}"
                               class="px-3 py-2 rounded-xl border border-slate-300 hover:bg-slate-100 text-slate-700 font-mono font-bold text-xs transition">
                                Edit Programs
                            </a>
                        </div>
                    </div>

                    <!-- Programs Table for this Judge -->
                    @if($dutyPrograms->isEmpty())
                        <div class="p-8 text-center bg-slate-50/50">
                            <p class="text-xs font-mono text-slate-500 mb-2">ഈ ജഡ്ജിന് നിലവിൽ പ്രോഗ്രാമുകൾ ഒന്നും അസൈൻ ചെയ്തിട്ടില്ല (No programs assigned).</p>
                            <a href="{{ route('admin.judges.edit', $judge->id) }}" class="inline-block px-3.5 py-1.5 bg-[#be1e2d] text-white rounded-lg text-xs font-mono font-bold">
                                + Assign Competitions Now
                            </a>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs font-mono">
                                <thead class="bg-slate-100/70 text-slate-600 uppercase border-b border-slate-200">
                                    <tr>
                                        <th class="px-5 py-3 w-12 font-bold">#</th>
                                        <th class="px-5 py-3 w-28 font-bold">Code</th>
                                        <th class="px-5 py-3 font-bold">Competition Name</th>
                                        <th class="px-5 py-3 font-bold">Category</th>
                                        <th class="px-5 py-3 font-bold">Stage / Venue</th>
                                        <th class="px-5 py-3 font-bold">Date & Time</th>
                                        <th class="px-5 py-3 text-center font-bold">Participants</th>
                                        <th class="px-5 py-3 text-center font-bold">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-slate-700">
                                    @foreach($dutyPrograms as $index => $prog)
                                        @php
                                            $progTime = $prog->scheduled_time ?? $prog->schedule?->start_time;
                                            if ($progTime && is_string($progTime)) {
                                                try {
                                                    $progTime = \Carbon\Carbon::parse($progTime);
                                                } catch (\Throwable $e) {
                                                    $progTime = null;
                                                }
                                            }
                                            $stageName = $prog->stage?->name ?? $prog->schedule?->stage?->name ?? 'Stage —';
                                        @endphp
                                        <tr class="hover:bg-slate-50/70 transition-colors">
                                            <td class="px-5 py-3.5 text-slate-400 font-bold">{{ $index + 1 }}</td>
                                            <td class="px-5 py-3.5">
                                                <span class="px-2 py-0.5 rounded font-mono text-[11px] font-bold bg-slate-100 border border-slate-200 text-slate-800">
                                                    {{ $prog->code ?: 'PROG-'.$prog->id }}
                                                </span>
                                            </td>
                                            <td class="px-5 py-3.5">
                                                <div class="font-bold text-slate-900 font-sora text-sm">{{ $prog->name }}</div>
                                                @if($prog->malayalam_name)
                                                    <div class="text-[11px] text-slate-500 font-ml">{{ $prog->malayalam_name }}</div>
                                                @endif
                                            </td>
                                            <td class="px-5 py-3.5">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">
                                                    {{ $prog->category?->name ?? 'General' }}
                                                </span>
                                            </td>
                                            <td class="px-5 py-3.5 font-bold text-slate-800">
                                                {{ $stageName }}
                                            </td>
                                            <td class="px-5 py-3.5">
                                                @if($progTime)
                                                    <div class="font-bold text-slate-900">{{ $progTime->format('d M (D)') }}</div>
                                                    <div class="text-[11px] text-[#be1e2d] font-bold">{{ $progTime->format('h:i A') }}</div>
                                                @else
                                                    <span class="text-slate-400 italic">Not scheduled</span>
                                                @endif
                                            </td>
                                            <td class="px-5 py-3.5 text-center">
                                                <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-800 font-bold text-[11px]">
                                                    {{ $prog->entries_count ?? $prog->entries()->count() }}
                                                </span>
                                            </td>
                                            <td class="px-5 py-3.5 text-center">
                                                @if($prog->status === 'completed')
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                        Completed
                                                    </span>
                                                @elseif($prog->status === 'in_progress')
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200 animate-pulse">
                                                        In Progress
                                                    </span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">
                                                        Scheduled
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
