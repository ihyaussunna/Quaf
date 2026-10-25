@extends('layouts.admin', ['title' => 'Points Engine & Group Rankings'])

@section('content')
<div class="space-y-8 max-w-6xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-sora font-black text-slate-900">Points Engine & Standings</h1>
            <p class="text-xs font-mono text-slate-500 mt-1">Configure placement weights, group multipliers, and recalculate group standings dynamically.</p>
        </div>
        <form method="POST" action="{{ route('admin.points.recalculate') }}">
            @csrf
            <button type="submit" class="px-4 py-2.5 rounded-xl bg-[#f3bd2e] text-slate-950 font-mono font-bold text-xs uppercase tracking-wider hover:brightness-105 shadow-lg shadow-[#f3bd2e]/20 flex items-center gap-2">
                <span>⚡ Recalculate Leaderboard</span>
            </button>
        </form>
    </div>

    <!-- Official Festival Rule 11: Points & Grades Matrix Showcase -->
    <div class="rounded-2xl bg-white border border-slate-200 p-6 shadow-sm font-sora">
        <div class="border-b border-slate-200 pb-4 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <span class="text-xs font-mono font-bold tracking-widest text-[#be1e2d] uppercase">OFFICIAL FESTIVAL RULEBOOK</span>
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 mt-0.5">11. പോയിന്റ്സ് & ഗ്രേഡ്സ്</h2>
            </div>
            <span class="text-xs font-mono text-slate-500 bg-slate-100 px-3 py-1.5 rounded-lg border border-slate-200">
                Official Rulebook Matrix
            </span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- 1. Individual Competitions -->
            <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4 flex flex-col justify-between">
                <div>
                    <h4 class="font-bold text-sm text-slate-900 mb-1">മത്സരങ്ങളിൽ സ്ഥാനങ്ങൾ നേടിയവർക്ക്</h4>
                    <p class="text-[11px] font-mono text-slate-500 mb-3">Individual / Single Participant</p>
                    <table class="w-full text-xs font-mono border-collapse">
                        <thead>
                            <tr class="bg-slate-900 text-white text-center">
                                <th class="p-2 border border-slate-800">സ്ഥാനം</th>
                                <th class="p-2 border border-slate-800">ലഭിക്കുന്ന പോയിന്റ്</th>
                            </tr>
                        </thead>
                        <tbody class="text-center divide-y divide-slate-200 bg-white">
                            <tr>
                                <td class="p-2 font-bold border border-slate-200 text-amber-700 bg-amber-50/30">1st</td>
                                <td class="p-2 font-black border border-slate-200 text-slate-900 text-sm">05</td>
                            </tr>
                            <tr>
                                <td class="p-2 font-bold border border-slate-200 text-slate-700">2nd</td>
                                <td class="p-2 font-black border border-slate-200 text-slate-900 text-sm">03</td>
                            </tr>
                            <tr>
                                <td class="p-2 font-bold border border-slate-200 text-amber-900">3rd</td>
                                <td class="p-2 font-black border border-slate-200 text-slate-900 text-sm">01</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 2. Group Competitions -->
            <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4 flex flex-col justify-between">
                <div>
                    <h4 class="font-bold text-sm text-slate-900 mb-1">ഗ്രൂപ്പ് മത്സരങ്ങളിൽ സ്ഥാനങ്ങൾ</h4>
                    <p class="text-[11px] font-mono text-slate-500 mb-3">Group Competitions by Member Count</p>
                    <table class="w-full text-xs font-mono border-collapse">
                        <thead>
                            <tr class="bg-slate-900 text-white text-center">
                                <th class="p-2 border border-slate-800 text-[10px]">അംഗങ്ങൾ</th>
                                <th class="p-2 border border-slate-800 text-[10px] text-amber-300">Ist</th>
                                <th class="p-2 border border-slate-800 text-[10px]">IInd</th>
                                <th class="p-2 border border-slate-800 text-[10px]">IIIrd</th>
                            </tr>
                        </thead>
                        <tbody class="text-center divide-y divide-slate-200 bg-white">
                            <tr>
                                <td class="p-2 font-bold border border-slate-200 bg-slate-50">2</td>
                                <td class="p-2 font-bold border border-slate-200">07</td>
                                <td class="p-2 font-bold border border-slate-200">05</td>
                                <td class="p-2 font-bold border border-slate-200">03</td>
                            </tr>
                            <tr>
                                <td class="p-2 font-bold border border-slate-200 bg-slate-50">3</td>
                                <td class="p-2 font-bold border border-slate-200">10</td>
                                <td class="p-2 font-bold border border-slate-200">07</td>
                                <td class="p-2 font-bold border border-slate-200">04</td>
                            </tr>
                            <tr>
                                <td class="p-2 font-bold border border-slate-200 bg-slate-50">4, 5</td>
                                <td class="p-2 font-bold border border-slate-200">15</td>
                                <td class="p-2 font-bold border border-slate-200">10</td>
                                <td class="p-2 font-bold border border-slate-200">05</td>
                            </tr>
                            <tr class="bg-red-50/50">
                                <td class="p-2 font-bold border border-slate-200 text-slate-900 text-[11px]">ഇൻസെറ്റ്, സെന്റിനറി ഫുട്പ്രിന്റ്</td>
                                <td class="p-2 font-black border border-slate-200 text-[#be1e2d] text-sm">20</td>
                                <td class="p-2 font-black border border-slate-200 text-slate-900 text-sm">15</td>
                                <td class="p-2 font-black border border-slate-200 text-slate-900 text-sm">10</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 3. Grades & Marks -->
            <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4 flex flex-col justify-between">
                <div>
                    <h4 class="font-bold text-sm text-slate-900 mb-1">ഓരോ ഗ്രേഡിനും ലഭിക്കുന്ന പോയിന്റ്</h4>
                    <p class="text-[11px] font-mono text-slate-500 mb-3">Grade & Score Percentage Matrix</p>
                    <table class="w-full text-xs font-mono border-collapse">
                        <thead>
                            <tr class="bg-slate-900 text-white text-center">
                                <th class="p-2 border border-slate-800">ഗ്രേഡ്</th>
                                <th class="p-2 border border-slate-800">മാർക്ക് ശതമാനം</th>
                                <th class="p-2 border border-slate-800">പോയിന്റ്</th>
                            </tr>
                        </thead>
                        <tbody class="text-center divide-y divide-slate-200 bg-white">
                            <tr>
                                <td class="p-2 font-black border border-slate-200 text-emerald-700 bg-emerald-50/40">A+</td>
                                <td class="p-2 font-medium border border-slate-200">90% മുതൽ 100% വരെ</td>
                                <td class="p-2 font-black border border-slate-200 text-slate-900 text-sm">06</td>
                            </tr>
                            <tr>
                                <td class="p-2 font-black border border-slate-200 text-blue-700 bg-blue-50/40">A</td>
                                <td class="p-2 font-medium border border-slate-200">70% മുതൽ 89% വരെ</td>
                                <td class="p-2 font-black border border-slate-200 text-slate-900 text-sm">05</td>
                            </tr>
                            <tr>
                                <td class="p-2 font-black border border-slate-200 text-amber-700 bg-amber-50/40">B</td>
                                <td class="p-2 font-medium border border-slate-200">60% മുതൽ 69% വരെ</td>
                                <td class="p-2 font-black border border-slate-200 text-slate-900 text-sm">03</td>
                            </tr>
                            <tr>
                                <td class="p-2 font-black border border-slate-200 text-slate-700 bg-slate-50">C</td>
                                <td class="p-2 font-medium border border-slate-200">50% മുതൽ 59% വരെ</td>
                                <td class="p-2 font-black border border-slate-200 text-slate-900 text-sm">01</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Point Configuration Matrix Form -->
    <div class="rounded-2xl bg-white border border-slate-200 p-6 shadow-sm">
        <h3 class="font-sora font-bold text-lg text-slate-900 mb-2">Base Scoring Parameters</h3>
        <p class="text-xs font-mono text-slate-500 mb-6">These parameters govern base individual placement weights and participation points.</p>

        <form method="POST" action="{{ route('admin.points.update') }}" class="grid grid-cols-1 sm:grid-cols-5 gap-4">
            @csrf

            <div>
                <label class="block text-xs font-mono uppercase text-[#be1e2d] mb-1.5 font-bold">1st Place Points</label>
                <input type="number" name="first_place_points" value="{{ old('first_place_points', $settings->first_place_points) }}" required min="1"
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-colors">
            </div>

            <div>
                <label class="block text-xs font-mono uppercase text-slate-700 mb-1.5 font-bold">2nd Place Points</label>
                <input type="number" name="second_place_points" value="{{ old('second_place_points', $settings->second_place_points) }}" required min="1"
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-colors">
            </div>

            <div>
                <label class="block text-xs font-mono uppercase text-amber-700 mb-1.5 font-bold">3rd Place Points</label>
                <input type="number" name="third_place_points" value="{{ old('third_place_points', $settings->third_place_points) }}" required min="1"
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-colors">
            </div>

            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Participation Pts</label>
                <input type="number" name="participation_points" value="{{ old('participation_points', $settings->participation_points) }}" required min="0"
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-colors">
            </div>

            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Group Multiplier</label>
                <input type="number" step="0.5" name="group_multiplier" value="{{ old('group_multiplier', $settings->group_multiplier) }}" required min="1"
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-colors">
            </div>

            <div class="sm:col-span-5 flex justify-end pt-2">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 border border-slate-300 text-slate-800 text-xs font-mono font-bold uppercase tracking-wider transition-colors">
                    Save Rules & Recalculate
                </button>
            </div>
        </form>
    </div>

    <!-- Live Leaderboard Table -->
    <div class="rounded-2xl bg-white border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <h3 class="font-sora font-bold text-lg text-slate-900">Current Group Standings</h3>
            <span class="text-xs font-mono text-slate-500">Auto-ranked by aggregate points</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-mono">
                <thead class="bg-slate-50 text-slate-500 uppercase border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 font-semibold">Rank</th>
                        <th class="px-6 py-4 font-semibold">Group</th>
                        <th class="px-6 py-4 font-semibold">Leader</th>
                        <th class="px-6 py-4 font-semibold">Delegates</th>
                        <th class="px-6 py-4 font-semibold">Entries</th>
                        <th class="px-6 py-4 text-right font-semibold">Total Points</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @foreach($groups as $grp)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-6 py-4">
                                <span class="w-7 h-7 rounded-lg font-bold flex items-center justify-center bg-slate-100 border border-slate-200 text-slate-800 shadow-sm">
                                    #{{ $grp->rank_cache ?: $loop->iteration }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="w-3.5 h-3.5 rounded-full border border-slate-300 shadow-xs shrink-0" style="background-color: {{ $grp->color_hex }}"></span>
                                    <div>
                                        <span class="font-sora font-bold text-slate-900 text-sm block">{{ $grp->name }}</span>
                                        <span class="text-[10px] text-slate-500 font-mono">{{ $grp->code }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-600">{{ $grp->leader?->name ?? '—' }}</td>
                            <td class="px-6 py-4 text-slate-900 font-medium">{{ $grp->students_count }} students</td>
                            <td class="px-6 py-4 text-slate-900 font-medium">{{ $grp->entries_count }} entries</td>
                            <td class="px-6 py-4 text-right">
                                <span class="font-sora font-black text-2xl" style="color: {{ $grp->color_hex }}">
                                    {{ number_format($grp->points_cache) }}
                                </span>
                                <span class="text-slate-500 text-[10px]"> pts</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Auditable Points Transaction Ledger -->
    <div class="rounded-2xl bg-white border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h3 class="font-sora font-bold text-lg text-slate-900">Points Transaction Ledger</h3>
                <p class="text-xs font-mono text-slate-500 mt-0.5">Auditable record of every point awarded (Position 5/3/1, Grade A+:6 / A:5 / B:3 / C:1)</p>
            </div>

            <!-- Filter Controls -->
            <form method="GET" action="{{ route('admin.points.index') }}" class="flex flex-wrap items-center gap-2">
                <select name="group_id" onchange="this.form.submit()" class="bg-slate-50 border border-slate-300 rounded-lg px-3 py-1.5 text-xs text-slate-700 font-mono focus:outline-none focus:border-slate-500">
                    <option value="">All Groups</option>
                    @foreach($groups as $g)
                        <option value="{{ $g->id }}" {{ (string)$selectedGroupId === (string)$g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                    @endforeach
                </select>

                <select name="source_type" onchange="this.form.submit()" class="bg-slate-50 border border-slate-300 rounded-lg px-3 py-1.5 text-xs text-slate-700 font-mono focus:outline-none focus:border-slate-500">
                    <option value="">All Sources</option>
                    <option value="POSITION" {{ $sourceType === 'POSITION' ? 'selected' : '' }}>Position Points</option>
                    <option value="GRADE" {{ $sourceType === 'GRADE' ? 'selected' : '' }}>Grade Points</option>
                </select>

                @if($selectedGroupId || $sourceType)
                    <a href="{{ route('admin.points.index') }}" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg text-xs font-mono">Reset</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-mono">
                <thead class="bg-slate-50 text-slate-500 uppercase border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5 font-semibold">Date / Time</th>
                        <th class="px-6 py-3.5 font-semibold">Group</th>
                        <th class="px-6 py-3.5 font-semibold">Programme</th>
                        <th class="px-6 py-3.5 font-semibold">Type</th>
                        <th class="px-6 py-3.5 font-semibold">Student / Entry</th>
                        <th class="px-6 py-3.5 text-right font-semibold">Points</th>
                        <th class="px-6 py-3.5 font-semibold">Description</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($transactions as $tx)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-6 py-3 text-slate-500 text-[11px] whitespace-nowrap">
                                {{ $tx->created_at ? $tx->created_at->format('d M Y, h:i A') : '—' }}
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold" style="background-color: {{ $tx->group->color_hex }}15; color: {{ $tx->group->color_hex }};">
                                    <span class="w-2 h-2 rounded-full" style="background-color: {{ $tx->group->color_hex }}"></span>
                                    {{ $tx->group->name }}
                                </span>
                            </td>
                            <td class="px-6 py-3 font-medium text-slate-900 max-w-[200px] truncate" title="{{ $tx->program?->name }}">
                                {{ $tx->program?->name ?? '—' }}
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap">
                                @if($tx->source_type === 'POSITION')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">POSITION</span>
                                @elseif($tx->source_type === 'GRADE')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">GRADE</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">{{ $tx->source_type }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-slate-600 whitespace-nowrap">
                                @if($tx->student)
                                    <span class="font-medium text-slate-900">{{ $tx->student->name }}</span>
                                    <span class="text-[10px] text-slate-400">({{ $tx->student->student_id }})</span>
                                @else
                                    <span class="text-slate-400 italic">Group Entry</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-right whitespace-nowrap">
                                <span class="font-mono font-bold text-sm text-emerald-600">
                                    +{{ $tx->points }}
                                </span>
                                <span class="text-slate-400 text-[10px]">pts</span>
                            </td>
                            <td class="px-6 py-3 text-slate-500 text-[11px] max-w-[240px] truncate" title="{{ $tx->description }}">
                                {{ $tx->description }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400 font-mono text-xs">
                                No points transactions recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="px-6 py-4 border-t border-slate-200">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

