@extends('layouts.admin', ['title' => 'Top Scorers & Champions | Quaf'])

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 font-sans tracking-tight">Top Scorers & Champions</h1>
            <p class="text-xs text-slate-500 mt-1 font-sans">Individual star performers, Kalaprathibha, Kalathilakam, and special category badge winners.</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('admin.points.index') }}" class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 font-semibold text-xs hover:bg-slate-50 transition-all flex items-center gap-1.5 shadow-2xs">
                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                <span>Points System</span>
            </a>
        </div>
    </div>

    <!-- Champions Spotlight (Kalaprathibha & Kalathilakam) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Kalaprathibha -->
        <div class="rounded-2xl bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-200 p-6 shadow-2xs relative overflow-hidden">
            <div class="flex items-center justify-between mb-4">
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-[#be1e2d] text-white uppercase tracking-wider shadow-2xs flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                    <span>KALAPRATHIBHA (TOP MALE)</span>
                </span>
                <span class="text-xs font-mono text-slate-500">Festival Champion</span>
            </div>

            @if($kalaprathibha)
                <div class="flex items-center gap-4 mt-2">
                    <div class="w-16 h-16 rounded-2xl bg-white border-2 border-amber-300 flex items-center justify-center text-slate-700 shadow-2xs flex-shrink-0">
                        <svg class="w-8 h-8 text-[#be1e2d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">{{ $kalaprathibha->name }}</h2>
                        <div class="flex flex-wrap items-center gap-2 mt-1 text-xs text-slate-600">
                            <span class="px-2 py-0.5 rounded bg-white text-slate-800 font-mono font-bold border border-slate-200">#{{ $kalaprathibha->student_id }}</span>
                            <span>•</span>
                            <span class="font-bold text-[#be1e2d]">{{ $kalaprathibha->group?->name ?? 'Independent' }}</span>
                            <span>•</span>
                            <span>{{ $kalaprathibha->category }}</span>
                        </div>
                        <div class="mt-2 text-2xl font-black text-[#be1e2d] font-mono">
                            {{ number_format($kalaprathibha->points_cache) }} <span class="text-xs font-normal text-slate-400">TOTAL PTS</span>
                        </div>
                    </div>
                </div>
            @else
                <p class="text-xs text-slate-400 py-6">No male participant with points recorded yet.</p>
            @endif
        </div>

        <!-- Kalathilakam -->
        <div class="rounded-2xl bg-gradient-to-br from-purple-50 to-pink-50 border border-blue-200 p-6 shadow-2xs relative overflow-hidden">
            <div class="flex items-center justify-between mb-4">
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-purple-700 text-white uppercase tracking-wider shadow-2xs flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                    <span>KALATHILAKAM (TOP FEMALE)</span>
                </span>
                <span class="text-xs font-mono text-slate-500">Festival Champion</span>
            </div>

            @if($kalathilakam)
                <div class="flex items-center gap-4 mt-2">
                    <div class="w-16 h-16 rounded-2xl bg-white border-2 border-blue-300 flex items-center justify-center text-slate-700 shadow-2xs flex-shrink-0">
                        <svg class="w-8 h-8 text-[#005c94]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">{{ $kalathilakam->name }}</h2>
                        <div class="flex flex-wrap items-center gap-2 mt-1 text-xs text-slate-600">
                            <span class="px-2 py-0.5 rounded bg-white text-slate-800 font-mono font-bold border border-slate-200">#{{ $kalathilakam->student_id }}</span>
                            <span>•</span>
                            <span class="font-bold text-[#005c94]">{{ $kalathilakam->group?->name ?? 'Independent' }}</span>
                            <span>•</span>
                            <span>{{ $kalathilakam->category }}</span>
                        </div>
                        <div class="mt-2 text-2xl font-black text-[#005c94] font-mono">
                            {{ number_format($kalathilakam->points_cache) }} <span class="text-xs font-normal text-slate-400">TOTAL PTS</span>
                        </div>
                    </div>
                </div>
            @else
                <p class="text-xs text-slate-400 py-6">No female participant with points recorded yet.</p>
            @endif
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-2xs">
        <form method="GET" action="{{ route('admin.top-scorers.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search participant name or chest no..."
                       class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-[#be1e2d] focus:bg-white text-slate-900">
            </div>

            <div>
                <select name="category" class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-[#be1e2d] focus:bg-white text-slate-900">
                    <option value="">All Zones</option>
                    @foreach($zones as $zoneKey => $zoneLabel)
                        <option value="{{ $zoneKey }}" {{ $category === $zoneKey ? 'selected' : '' }}>{{ $zoneLabel }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="gender" class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-[#be1e2d] focus:bg-white text-slate-900">
                    <option value="">All Genders</option>
                    <option value="male" {{ $gender === 'male' ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ $gender === 'female' ? 'selected' : '' }}>Female</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 rounded-xl bg-[#be1e2d] text-white font-semibold text-xs hover:bg-[#a01624] transition-colors shadow-2xs">
                    Filter
                </button>
                <a href="{{ route('admin.top-scorers.index') }}" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Top Scorers Table (Columns: Participant Name, Special Badges, Gender, Team, Category, Offstage Points, Stage Points, Total Points) -->
    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-2xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-sans">
                <thead class="bg-slate-50 text-slate-500 uppercase border-b border-slate-200 text-[11px] font-semibold">
                    <tr>
                        <th class="px-5 py-3.5">Rank</th>
                        <th class="px-5 py-3.5">Participant Name</th>
                        <th class="px-5 py-3.5">Special Badges</th>
                        <th class="px-5 py-3.5">Gender</th>
                        <th class="px-5 py-3.5">Team</th>
                        <th class="px-5 py-3.5">Zone</th>
                        <th class="px-5 py-3.5 text-center font-mono">Offstage Points</th>
                        <th class="px-5 py-3.5 text-center font-mono">Stage Points</th>
                        <th class="px-5 py-3.5 text-right font-mono font-bold">Total Points</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($topScorers as $student)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <!-- Rank -->
                            <td class="px-5 py-3.5">
                                @if($loop->iteration === 1 && $topScorers->currentPage() === 1)
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full font-bold text-xs bg-amber-100 text-amber-900 border border-amber-300">1</span>
                                @elseif($loop->iteration === 2 && $topScorers->currentPage() === 1)
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full font-bold text-xs bg-slate-200 text-slate-800 border border-slate-300">2</span>
                                @elseif($loop->iteration === 3 && $topScorers->currentPage() === 1)
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full font-bold text-xs bg-amber-200/50 text-amber-900 border border-amber-300/60">3</span>
                                @else
                                    <span class="text-slate-400 font-mono font-bold text-xs">#{{ ($topScorers->currentPage() - 1) * $topScorers->perPage() + $loop->iteration }}</span>
                                @endif
                            </td>

                            <!-- Participant Name -->
                            <td class="px-5 py-3.5">
                                <a href="{{ route('admin.students.show', $student) }}" class="font-bold text-slate-900 hover:text-[#be1e2d] transition-colors block">
                                    {{ $student->name }}
                                </a>
                                <span class="text-[10px] text-slate-400 font-mono">#{{ $student->student_id }}</span>
                            </td>

                            <!-- Special Badges -->
                            <td class="px-5 py-3.5">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    @forelse($student->special_badges ?? [] as $badge)
                                        @if($badge === 'Vocal of the Fest')
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                                {{ $badge }}
                                            </span>
                                        @elseif($badge === 'Pen of the Fest')
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-800 border border-blue-200">
                                                {{ $badge }}
                                            </span>
                                        @elseif($badge === 'Artist of the Fest')
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-[#005c94] border border-blue-200">
                                                {{ $badge }}
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-50 text-[#be1e2d] border border-red-200">
                                                {{ $badge }}
                                            </span>
                                        @endif
                                    @empty
                                        <span class="text-slate-300 text-[11px]">—</span>
                                    @endforelse
                                </div>
                            </td>

                            <!-- Gender -->
                            <td class="px-5 py-3.5 capitalize text-slate-600">
                                {{ $student->gender }}
                            </td>

                            <!-- Team -->
                            <td class="px-5 py-3.5">
                                <span class="font-semibold text-slate-900 block">{{ $student->group?->name ?? 'Independent' }}</span>
                                <span class="text-[10px] text-slate-400 font-mono">{{ $student->group?->code ?? '' }}</span>
                            </td>

                            <!-- Category -->
                            <td class="px-5 py-3.5">
                                <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ $student->category }}
                                </span>
                            </td>

                            <!-- Offstage Points -->
                            <td class="px-5 py-3.5 text-center font-mono font-medium text-slate-700">
                                {{ number_format($student->offstage_points ?? 0) }}
                            </td>

                            <!-- Stage Points -->
                            <td class="px-5 py-3.5 text-center font-mono font-medium text-slate-700">
                                {{ number_format($student->stage_points ?? 0) }}
                            </td>

                            <!-- Total Points -->
                            <td class="px-5 py-3.5 text-right font-mono font-bold text-sm text-[#be1e2d]">
                                {{ number_format($student->points_cache) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-10 text-center text-slate-400">
                                No scorers found matching the filter criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($topScorers->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $topScorers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
